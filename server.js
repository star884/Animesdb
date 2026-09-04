// server.js
import express from 'express';
import cors from 'cors';
import axios from 'axios';
import path from 'path';
import { fileURLToPath } from 'url';
import NodeCache from 'node-cache';
import rateLimit from 'express-rate-limit';
import * as Consumet from '@consumet/extensions';

// Optional third-party scrapers will be detected dynamically (if you npm install them).
// Examples: 'anime-scraper' (npm) or 'zoroapi' (github projects sometimes published as npm).
// We do not require them; if present we'll wrap them as providers.

const app = express();
const PORT = process.env.PORT || 3000;
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Trust proxy so express-rate-limit can rely on X-Forwarded-For when behind Render or similar
app.set('trust proxy', 1);

app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// Small in-memory cache for playlists
const cache = new NodeCache({ stdTTL: 120, checkperiod: 30 });

// Rate limiter for /api
const apiLimiter = rateLimit({
  windowMs: 30 * 1000, // 30s
  max: 20,
  standardHeaders: true,
  legacyHeaders: false
});
app.use('/api/', apiLimiter);

// AniList GraphQL helper
const ANILIST_URL = 'https://graphql.anilist.co';
async function fetchAniList(query, variables = {}) {
  try {
    const response = await axios.post(ANILIST_URL, { query, variables }, { headers: { 'Content-Type': 'application/json' }, timeout: 10000 });
    return response.data.data;
  } catch (err) {
    console.error('AniList API Error:', err?.message ?? err);
    return null;
  }
}

// Helper: validate http/https url
function isHttpUrl(u) {
  try {
    const parsed = new URL(u);
    return parsed.protocol === 'http:' || parsed.protocol === 'https:';
  } catch (e) {
    return false;
  }
}

/**
 * Build provider factories:
 * - first, local adapters in ./providers (if you add any; they must export { name, create })
 * - second, dynamic wrappers for community packages if installed (anime-scraper, zoroapi)
 * - then, providers from @consumet/extensions (ANIME.*)
 *
 * Each factory is: { name, createInstance: () => providerInstance, source }
 * providerInstance must implement: search(q), fetchAnimeInfo(id), fetchEpisodeSources(epId)
 */
async function buildProviderFactories() {
  const factories = [];

  // 1) local adapters in providers/ directory
  try {
    const providersDir = path.join(process.cwd(), 'providers');
    if (await exists(providersDir)) {
      const files = await fsReaddir(providersDir);
      for (const f of files) {
        if (!f.endsWith('.js')) continue;
        if (f === 'loader.js') continue;
        try {
          const mod = await import(pathToFileURL(path.join(providersDir, f)).toString());
          if (mod?.name && typeof mod.create === 'function') {
            factories.push({
              name: mod.name,
              createInstance: () => mod.create(),
              source: `local:${f}`
            });
          }
        } catch (err) {
          console.warn('Failed to import local provider', f, err?.message ?? err);
        }
      }
    }
  } catch (err) {
    // ignore
  }

  // 2) dynamic wrappers for community npm modules (try to use without requiring them to be in package.json)
  // 2a) anime-scraper (npm's anime-scraper package) wrapper
  try {
    const animeScraper = await dynamicImport('anime-scraper');
    if (animeScraper) {
      // anime-scraper exports a class 'Anime' or functions - attempt to detect usage
      const wrapperName = 'anime-scraper';
      factories.push({
        name: wrapperName,
        source: 'npm:anime-scraper',
        createInstance: () => {
          // Wrap the anime-scraper API into provider shape
          // Try likely API forms:
          // - anime-scraper exports { Anime } with static methods fromName/fromUrl
          // - or it exports functions
          const Anime = animeScraper.Anime ?? animeScraper.default ?? animeScraper;
          // Basic wrappers: search -> try Anime.search or Anime.fromName
          return {
            async search(q) {
              // try multiple forms
              if (typeof Anime.zoroSearch === 'function') return Anime.zoroSearch(q);
              if (typeof Anime.search === 'function') return Anime.search(q);
              if (typeof Anime.fromName === 'function') {
                try {
                  const a = await Anime.fromName(q);
                  // normalize to { results: [ { id, title, ... } ] }
                  if (a) {
                    return { results: [{ id: a.url || a.slug || a._id || a.name || q, title: a.title || q }] };
                  }
                } catch (e) { /* fallthrough */ }
              }
              throw new Error('anime-scraper: unsupported API shape for search');
            },
            async fetchAnimeInfo(idOrObj) {
              if (typeof Anime.fromUrl === 'function') {
                // if idOrObj looks like a URL, call fromUrl
                try {
                  const res = await Anime.fromUrl(idOrObj);
                  // normalize episodes
                  const eps = (res?.episodes || []).map(e => ({ id: e.id || e.url || e.epId || e.number, number: e.number || e.epNum || null, title: e.title || null }));
                  return { id: res?.id || idOrObj, title: { english: res?.title }, episodes: eps };
                } catch (e) { /* fallthrough */ }
              }
              throw new Error('anime-scraper: unsupported API shape for fetchAnimeInfo');
            },
            async fetchEpisodeSources(epId) {
              // anime-scraper often has episodic objects with streaming info
              // Not all anime-scraper implementations expose direct sources; this is a best-effort
              throw new Error('anime-scraper wrapper: fetchEpisodeSources not implemented generically; please add a local adapter using anime-scraper internals if possible');
            }
          };
        }
      });
    }
  } catch (err) {
    // module not installed — skip
  }

  // 2b) try to detect zoroapi (some community projects publish zoro wrappers)
  try {
    const zoroapi = await dynamicImport('zoroapi');
    if (zoroapi) {
      factories.push({
        name: 'zoroapi',
        source: 'npm:zoroapi',
        createInstance: () => {
          // zoroapi repo API shapes vary; attempt common methods:
          // e.g., Zoro.getAnimeInfoByName, Zoro.zoroSearch, Zoro.getEpList
          const Z = zoroapi.default ?? zoroapi;
          return {
            async search(q) {
              if (typeof Z.zoroSearch === 'function') return Z.zoroSearch(q);
              if (typeof Z.search === 'function') return Z.search(q);
              throw new Error('zoroapi: search not available');
            },
            async fetchAnimeInfo(id) {
              if (typeof Z.getAnimeInfoByName === 'function') return Z.getAnimeInfoByName(id);
              if (typeof Z.getAnimeInfo === 'function') return Z.getAnimeInfo(id);
              throw new Error('zoroapi: fetchAnimeInfo not available');
            },
            async fetchEpisodeSources(epId) {
              if (typeof Z.getEpisodeSources === 'function') return Z.getEpisodeSources(epId);
              if (typeof Z.getEpisodeStreams === 'function') return Z.getEpisodeStreams(epId);
              throw new Error('zoroapi: fetchEpisodeSources not available');
            }
          };
        }
      });
    }
  } catch (err) {
    // skip
  }

  // 3) providers from @consumet/extensions (ANIME.*)
  try {
    const ANIME = Consumet.ANIME ?? Consumet.default?.ANIME ?? null;
    const preferred = ['Gogoanime','AnimePahe','Hianime','AnimeKai','KickAssAnime','AnimeSaturn','AnimeUnity','AnimeSama'];
    if (ANIME) {
      for (const name of preferred) {
        const ctor = ANIME[name];
        if (typeof ctor === 'function') {
          factories.push({
            name,
            source: 'consumet',
            createInstance: () => new ctor()
          });
        }
      }
    }
  } catch (err) {
    console.warn('Error enumerating Consumet providers', err?.message ?? err);
  }

  return factories;
}

// Small helpers for fs operations with ESM
import fs from 'fs';
import { promisify } from 'util';
import { pathToFileURL } from 'url';
const fsReaddir = promisify(fs.readdir);
async function exists(p) { try { await fs.promises.access(p); return true; } catch (_) { return false; } }

// Dynamic import helper — returns module or null
async function dynamicImport(name) {
  try {
    // eslint-disable-next-line node/no-unsupported-features/es-syntax
    return await import(name);
  } catch (err) {
    // not installed or failed to import
    return null;
  }
}

// Build factories at startup
const providerFactories = await buildProviderFactories();
if (!providerFactories.length) {
  console.warn('No providers loaded; install @consumet/extensions and/or anime-scraper or add local adapters in /providers');
} else {
  console.log('Providers:', providerFactories.map(f => `${f.name}@${f.source}`).join(', '));
}

// Instance cache and helper to instantiate factory
const providerInstances = new Map();
function getInstance(factory) {
  const key = factory.name + '::' + (factory.source || 'unknown');
  if (providerInstances.has(key)) return providerInstances.get(key);
  const inst = factory.createInstance();
  providerInstances.set(key, inst);
  return inst;
}

// callWithFallback implementation (tries each provider factory in order)
async function callWithFallback(methodName, args = [], options = {}) {
  const timeoutMs = options.timeoutMs ?? 15000;
  const retryPerProvider = options.retryPerProvider ?? 1;
  const errors = [];

  for (const factory of providerFactories) {
    const name = factory.name;
    let inst;
    try {
      inst = getInstance(factory);
    } catch (err) {
      errors.push({ provider: name, error: `instantiate_failed: ${err?.message ?? err}` });
      continue;
    }
    if (!inst || typeof inst[methodName] !== 'function') {
      errors.push({ provider: name, error: `method_missing:${methodName}` });
      continue;
    }

    for (let attempt = 0; attempt < retryPerProvider; attempt++) {
      try {
        const promise = inst[methodName](...args);
        const result = await Promise.race([
          promise,
          new Promise((_, rej) => setTimeout(() => rej(new Error('timeout')), timeoutMs))
        ]);
        return { result, provider: name };
      } catch (err) {
        const msg = err?.message ?? String(err);
        console.warn(`Provider ${name} method ${methodName} attempt ${attempt + 1} failed:`, msg);
        errors.push({ provider: name, attempt: attempt + 1, error: msg });
        if (attempt + 1 < retryPerProvider) await new Promise(r => setTimeout(r, 300));
      }
    }
  }

  const e = new Error('All providers failed');
  e.details = errors;
  throw e;
}

/* ---------------------
   Streaming & proxy endpoints
   --------------------- */

// RANGE-aware proxy for segments and files
app.get('/api/proxy', async (req, res) => {
  const url = req.query.url;
  if (!url || !isHttpUrl(url)) return res.status(400).send('Invalid url');

  try {
    const headers = {};
    if (req.headers.range) headers.Range = req.headers.range;
    const upstream = await axios.get(url, { responseType: 'stream', headers, timeout: 20000, maxRedirects: 5 });

    if (upstream.status) res.status(upstream.status);
    const fwd = ['content-type', 'content-length', 'accept-ranges', 'content-range', 'cache-control', 'last-modified'];
    fwd.forEach(h => { if (upstream.headers[h]) res.setHeader(h, upstream.headers[h]); });
    res.setHeader('Access-Control-Allow-Origin', '*');

    upstream.data.pipe(res);
  } catch (err) {
    console.error('Proxy error streaming', url, err?.message ?? err);
    return res.status(502).send('Unable to proxy resource');
  }
});

// Playlist proxy: rewrite m3u8 segments to /api/proxy and cache playlist
app.get('/api/proxy/playlist', async (req, res) => {
  const url = req.query.url;
  if (!url || !isHttpUrl(url)) return res.status(400).send('Invalid url');

  const cacheKey = `playlist:${url}`;
  const cached = cache.get(cacheKey);
  if (cached) {
    res.setHeader('Content-Type', 'application/vnd.apple.mpegurl');
    res.setHeader('Access-Control-Allow-Origin', '*');
    return res.send(cached);
  }

  try {
    const resp = await axios.get(url, { responseType: 'text', timeout: 15000, maxRedirects: 5 });
    const base = new URL(url);
    const hostPrefix = `${req.protocol}://${req.get('host')}`;

    const lines = resp.data.split(/\r?\n/);
    const rewritten = lines.map(line => {
      if (!line || line.startsWith('#')) return line;
      try {
        const resolved = new URL(line, base).toString();
        return `${hostPrefix}/api/proxy?url=${encodeURIComponent(resolved)}`;
      } catch (e) {
        return line;
      }
    }).join('\n');

    cache.set(cacheKey, rewritten, 120);
    res.setHeader('Content-Type', 'application/vnd.apple.mpegurl');
    res.setHeader('Access-Control-Allow-Origin', '*');
    return res.send(rewritten);
  } catch (err) {
    console.error('Playlist proxy error:', err?.message ?? err);
    return res.status(502).send('Unable to fetch playlist');
  }
});

/* ---------------------
   AniList endpoints (catalog, search)
   --------------------- */

app.get('/api/catalog', async (req, res) => {
  const query = `
    query {
      trending: Page(page: 1, perPage: 10) {
        media(type: ANIME, sort: TRENDING_DESC) {
          id
          title { english romaji }
          coverImage { extraLarge }
          bannerImage
          description
          genres
          episodes
          averageScore
        }
      }
      popular: Page(page: 1, perPage: 10) {
        media(type: ANIME, sort: POPULARITY_DESC) {
          id
          title { english romaji }
          coverImage { extraLarge }
          bannerImage
          description
          genres
          episodes
          averageScore
        }
      }
    }
  `;
  const data = await fetchAniList(query);
  res.json(data || { trending: { media: [] }, popular: { media: [] }});
});

app.get('/api/search', async (req, res) => {
  const searchQuery = req.query.q;
  if (!searchQuery) return res.status(400).json({ error: 'Search query is required' });

  const query = `
    query ($search: String) {
      Page(page: 1, perPage: 24) {
        media(type: ANIME, search: $search) {
          id
          title { english romaji }
          coverImage { extraLarge }
          bannerImage
          description
          genres
          episodes
          averageScore
        }
      }
    }
  `;
  const data = await fetchAniList(query, { search: searchQuery });
  res.json(data?.Page?.media || []);
});

/* ---------------------
   /api/anime and /api/stream using callWithFallback
   --------------------- */

app.get('/api/anime', async (req, res) => {
  const animeId = req.query.animeId;
  if (!animeId) return res.status(400).json({ error: 'animeId required' });
  if (!providerFactories.length) return res.status(502).json({ error: 'No providers available' });

  try {
    const { result: animeInfo, provider: usedProvider } = await callWithFallback('fetchAnimeInfo', [animeId], { timeoutMs: 15000, retryPerProvider: 1 });
    return res.json({ animeInfo, provider: usedProvider });
  } catch (err) {
    console.error('All providers failed fetchAnimeInfo:', err.details ?? err.message);
    return res.status(502).json({ error: 'Unable to fetch anime info', details: err.details ?? err.message });
  }
});

app.get('/api/stream', async (req, res) => {
  const { title, episode = 1, animeId } = req.query;
  const episodeNumber = Number.parseInt(episode, 10);

  if (!title && !animeId) return res.status(400).json({ error: 'title or animeId required' });
  if (!Number.isInteger(episodeNumber) || episodeNumber < 1) return res.status(400).json({ error: 'Episode must be a positive integer' });
  if (!providerFactories.length) return res.status(502).json({ error: 'No providers available' });

  try {
    let animeInfo;
    let usedProvider;
    if (animeId) {
      const infoWrap = await callWithFallback('fetchAnimeInfo', [animeId], { timeoutMs: 15000, retryPerProvider: 1 });
      animeInfo = infoWrap.result;
      usedProvider = infoWrap.provider;
    } else {
      const searchWrap = await callWithFallback('search', [title], { timeoutMs: 12000, retryPerProvider: 1 });
      const searchResults = searchWrap.result;
      usedProvider = searchWrap.provider;
      if (!searchResults?.results?.length) return res.status(404).json({ error: 'Anime not found', streams: [] });
      const foundId = searchResults.results[0].id;
      const infoWrap = await callWithFallback('fetchAnimeInfo', [foundId], { timeoutMs: 15000, retryPerProvider: 1 });
      animeInfo = infoWrap.result;
      usedProvider = infoWrap.provider;
    }

    const episodes = animeInfo?.episodes || [];
    const targetEp = episodes.find(ep => Number(ep.number) === episodeNumber) || episodes[0];
    if (!targetEp) return res.status(404).json({ error: 'Episode not found', streams: [] });

    const srcWrap = await callWithFallback('fetchEpisodeSources', [targetEp.id], { timeoutMs: 15000, retryPerProvider: 1 });
    const sources = srcWrap.result;
    usedProvider = srcWrap.provider;

    const streams = (sources?.sources || []).filter(s => s?.url).map(s => {
      const isM3U8 = Boolean(s.isM3U8) || (s.url && s.url.includes('.m3u8'));
      return {
        server: s.server || s.name || usedProvider || 'provider',
        quality: s.quality || 'auto',
        url: s.url,
        isM3U8,
        proxiedPlaylist: isM3U8 ? `/api/proxy/playlist?url=${encodeURIComponent(s.url)}` : undefined
      };
    });

    return res.json({ title: animeInfo?.title?.english || animeInfo?.title?.romaji || title, episode: episodeNumber, provider: usedProvider, animeId: animeInfo?.id || animeId, streams, episodeList: episodes.map(e => ({ id: e.id, number: e.number, title: e.title })) });
  } catch (err) {
    console.error('Stream operation failed:', err.details ?? err.message);
    return res.status(502).json({ error: 'Unable to retrieve streams', details: err.details ?? err.message, streams: [] });
  }
});

/* ---------------------
   Start
   --------------------- */
app.listen(PORT, '0.0.0.0', () => {
  console.log(`Server running on port ${PORT}`);
  if (providerFactories.length) console.log(`Providers loaded: ${providerFactories.map(p => p.name).join(', ')}`);
});
