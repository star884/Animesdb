import express from 'express';
import cors from 'cors';
import axios from 'axios';
import path from 'path';
import { fileURLToPath } from 'url';
import * as Consumet from '@consumet/extensions';
import rateLimit from 'express-rate-limit';
import NodeCache from 'node-cache';

const app = express();
const PORT = process.env.PORT || 3000;
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Trust proxy so express-rate-limit and other middleware can rely on X-Forwarded-For when behind Render (or similar)
app.set('trust proxy', 1);

app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// Light in-memory cache for playlists / other short-lived items
const cache = new NodeCache({ stdTTL: 120, checkperiod: 30 });

// Light rate limiting for /api routes
const apiLimiter = rateLimit({
  windowMs: 30 * 1000, // 30s window
  max: 20, // limit each IP to 20 requests per windowMs
  standardHeaders: true,
  legacyHeaders: false
});
app.use('/api/', apiLimiter);

// Helper: AniList GraphQL
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

// Helper: is valid http(s) url
function isHttpUrl(u) {
  try {
    const parsed = new URL(u);
    return parsed.protocol === 'http:' || parsed.protocol === 'https:';
  } catch (e) {
    return false;
  }
}

// Provider fallback setup
const ANIME = Consumet.ANIME ?? Consumet.default?.ANIME ?? null;
const preferredProviders = ['Gogoanime','AnimePahe','Hianime','AnimeKai','AnimeSaturn','AnimeUnity','AnimeSama','KickAssAnime'];

const providerFactories = [];
if (ANIME) {
  for (const name of preferredProviders) {
    if (typeof ANIME[name] === 'function') providerFactories.push({ name, ctor: ANIME[name] });
  }
}

if (!providerFactories.length) {
  console.warn('No provider constructors found in @consumet/extensions. Streaming endpoints will be unavailable.');
} else {
  console.log('Provider constructors available:', providerFactories.map(p => p.name).join(', '));
}

// Lazy instantiate provider instances and cache them
const providerInstances = new Map();
function getProviderInstance(name, ctor) {
  if (providerInstances.has(name)) return providerInstances.get(name);
  try {
    const inst = new ctor();
    providerInstances.set(name, inst);
    return inst;
  } catch (err) {
    console.warn(`Failed to instantiate provider ${name}:`, err?.message ?? err);
    return null;
  }
}

// callWithFallback: tries methodName on each available provider in order.
// options: { timeoutMs, retryPerProvider }
// Returns { result, provider } on success or throws an Error with details on failure.
async function callWithFallback(methodName, args = [], options = {}) {
  const timeoutMs = options.timeoutMs ?? 15000;
  const retryPerProvider = options.retryPerProvider ?? 1;
  const errors = [];

  for (const { name, ctor } of providerFactories) {
    const inst = getProviderInstance(name, ctor);
    if (!inst) {
      errors.push({ provider: name, error: 'instantiate_failed' });
      continue;
    }
    if (typeof inst[methodName] !== 'function') {
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

// RANGE-aware proxy for media (for segments, mp4, etc.)
// Forwards Range header and pipes upstream response back to client (preserving status and headers when possible)
app.get('/api/proxy', async (req, res) => {
  const url = req.query.url;
  if (!url || !isHttpUrl(url)) return res.status(400).send('Invalid url');

  try {
    const headers = {};
    if (req.headers.range) headers.Range = req.headers.range;
    const upstream = await axios.get(url, { responseType: 'stream', headers, timeout: 20000, maxRedirects: 5 });

    if (upstream.status) res.status(upstream.status);
    const forwardHeaders = ['content-type', 'content-length', 'accept-ranges', 'content-range', 'cache-control', 'last-modified'];
    forwardHeaders.forEach(h => { if (upstream.headers[h]) res.setHeader(h, upstream.headers[h]); });
    res.setHeader('Access-Control-Allow-Origin', '*');

    upstream.data.pipe(res);
  } catch (err) {
    console.error('Proxy error streaming', url, err?.message ?? err);
    // If upstream returned a non-2xx, axios throws; try to return a 502
    return res.status(502).send('Unable to proxy resource');
  }
});

// Playlist proxy (m3u8): fetch text, rewrite segment urls to /api/proxy, cache short TTL
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

    cache.set(cacheKey, rewritten, 120); // 2 minutes
    res.setHeader('Content-Type', 'application/vnd.apple.mpegurl');
    res.setHeader('Access-Control-Allow-Origin', '*');
    return res.send(rewritten);
  } catch (err) {
    console.error('Playlist proxy error:', err?.message ?? err);
    return res.status(502).send('Unable to fetch playlist');
  }
});

// /api/catalog - AniList trending & popular
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

// /api/search?q=
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

// /api/anime?animeId= -> uses providers' fetchAnimeInfo via fallback
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

// /api/stream?title=...&episode=... or /api/stream?animeId=...&episode=...
app.get('/api/stream', async (req, res) => {
  const { title, episode = 1, animeId } = req.query;
  const episodeNumber = Number.parseInt(episode, 10);
  if (!title && !animeId) return res.status(400).json({ error: 'title or animeId required' });
  if (!Number.isInteger(episodeNumber) || episodeNumber < 1) return res.status(400).json({ error: 'Episode must be a positive integer' });
  if (!providerFactories.length) return res.status(502).json({ error: 'No providers available' });

  try {
    let animeInfo, usedProvider;
    if (animeId) {
      const wrap = await callWithFallback('fetchAnimeInfo', [animeId], { timeoutMs: 15000, retryPerProvider: 1 });
      animeInfo = wrap.result;
      usedProvider = wrap.provider;
    } else {
      // search
      const searchWrap = await callWithFallback('search', [title], { timeoutMs: 12000, retryPerProvider: 1 });
      const searchResults = searchWrap.result;
      usedProvider = searchWrap.provider;
      if (!searchResults?.results?.length) {
        return res.status(404).json({ error: 'Anime not found', streams: [] });
      }
      const foundId = searchResults.results[0].id;
      const infoWrap = await callWithFallback('fetchAnimeInfo', [foundId], { timeoutMs: 15000, retryPerProvider: 1 });
      animeInfo = infoWrap.result;
      usedProvider = infoWrap.provider;
    }

    const episodes = animeInfo?.episodes || [];
    const targetEp = episodes.find(ep => Number(ep.number) === episodeNumber) || episodes[0];
    if (!targetEp) return res.status(404).json({ error: 'Episode not found', streams: [] });

    // fetch episode sources using fallback (we'll prefer provider used above but callWithFallback will try each)
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

    return res.json({
      title: animeInfo?.title?.english || animeInfo?.title?.romaji || title,
      episode: episodeNumber,
      provider: usedProvider,
      animeId: animeInfo?.id || animeId,
      streams,
      episodeList: episodes.map(e => ({ id: e.id, number: e.number, title: e.title }))
    });
  } catch (err) {
    console.error('Stream operation failed:', err.details ?? err.message);
    return res.status(502).json({ error: 'Unable to retrieve streams', details: err.details ?? err.message, streams: [] });
  }
});

// Start server
app.listen(PORT, '0.0.0.0', () => {
  console.log(`Server running on port ${PORT}`);
  if (providerFactories.length) console.log(`Providers: ${providerFactories.map(p => p.name).join(', ')}`);
});
