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

app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// Light in-memory cache
const cache = new NodeCache({ stdTTL: 120, checkperiod: 30 });

// Light rate limiting to protect free tier
const apiLimiter = rateLimit({
  windowMs: 30 * 1000, // 30s
  max: 20,
  standardHeaders: true,
  legacyHeaders: false
});
app.use('/api/', apiLimiter);

// AniList helper
const ANILIST_URL = 'https://graphql.anilist.co';
async function fetchAniList(query, variables = {}) {
  try {
    const response = await axios.post(ANILIST_URL, { query, variables }, { headers: { 'Content-Type': 'application/json' } });
    return response.data.data;
  } catch (err) {
    console.error('AniList API Error:', err?.message ?? err);
    return null;
  }
}

// Provider selection (try to pick the provider present in the installed @consumet/extensions)
const ANIME = Consumet.ANIME ?? Consumet.default?.ANIME ?? null;
const preferredProviders = ['Gogoanime','AnimePahe','Hianime','AnimeKai','AnimeSaturn','AnimeUnity','AnimeSama','KickAssAnime'];
let provider = null;
let providerName = null;

if (ANIME) {
  for (const name of preferredProviders) {
    if (typeof ANIME[name] === 'function') {
      try {
        provider = new ANIME[name]();
        providerName = name;
        break;
      } catch (err) {
        console.warn(`Failed initializing provider ${name}:`, err?.message ?? err);
      }
    }
  }
}
if (!provider) {
  console.warn('No streaming provider available from @consumet/extensions. /api/stream will return 502 until one is available.');
} else {
  console.log(`Using provider: ${providerName}`);
}

// Helpers
function isHttpUrl(u) { try { const parsed = new URL(u); return parsed.protocol === 'http:' || parsed.protocol === 'https:'; } catch (e) { return false; } }

// RANGE-aware proxy for media (MP4 segments, ts segments, etc.)
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
    return res.status(502).send('Unable to proxy resource');
  }
});

// Playlist proxy (m3u8): fetch playlist text, rewrite segment/playlist URLs to point at your /api/proxy
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
    const rewrittenLines = lines.map(line => {
      if (!line || line.startsWith('#')) return line;
      try {
        const resolved = new URL(line, base).toString();
        return `${hostPrefix}/api/proxy?url=${encodeURIComponent(resolved)}`;
      } catch (e) {
        return line;
      }
    });
    const rewritten = rewrittenLines.join('\n');
    cache.set(cacheKey, rewritten, 120);
    res.setHeader('Content-Type', 'application/vnd.apple.mpegurl');
    res.setHeader('Access-Control-Allow-Origin', '*');
    return res.send(rewritten);
  } catch (err) {
    console.error('Playlist proxy error:', err?.message ?? err);
    return res.status(502).send('Unable to fetch playlist');
  }
});

// /api/catalog and /api/search
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

// /api/anime?animeId=
app.get('/api/anime', async (req, res) => {
  const animeId = req.query.animeId;
  if (!animeId) return res.status(400).json({ error: 'animeId required' });
  if (!provider) return res.status(502).json({ error: 'No provider available' });

  try {
    const animeInfo = await provider.fetchAnimeInfo(animeId);
    return res.json(animeInfo || {});
  } catch (err) {
    console.error('fetchAnimeInfo error:', err?.message ?? err);
    return res.status(502).json({ error: 'Unable to fetch anime info' });
  }
});

// /api/stream?title=...&episode=...
app.get('/api/stream', async (req, res) => {
  const { title, episode = 1, animeId } = req.query;
  const episodeNumber = Number.parseInt(episode, 10);
  if (!title && !animeId) return res.status(400).json({ error: 'title or animeId required' });
  if (!Number.isInteger(episodeNumber) || episodeNumber < 1) return res.status(400).json({ error: 'Episode must be a positive integer' });
  if (!provider) return res.status(502).json({ error: 'No provider available' });

  try {
    let animeInfo, animeSearchId;
    if (animeId) {
      animeInfo = await provider.fetchAnimeInfo(animeId);
      animeSearchId = animeId;
    } else {
      const searchResults = await provider.search(title);
      if (!searchResults?.results?.length) return res.status(404).json({ error: 'Anime not found', streams: [] });
      animeSearchId = searchResults.results[0].id;
      animeInfo = await provider.fetchAnimeInfo(animeSearchId);
    }

    const episodes = animeInfo?.episodes || [];
    const targetEp = episodes.find(ep => Number(ep.number) === episodeNumber) || episodes[0];
    if (!targetEp) return res.status(404).json({ error: 'Episode not found', streams: [] });

    const sources = await provider.fetchEpisodeSources(targetEp.id);
    const streams = (sources?.sources || []).filter(s => s?.url).map(s => {
      const isM3U8 = Boolean(s.isM3U8) || (s.url && s.url.includes('.m3u8'));
      return {
        server: s.server || s.name || providerName || 'provider',
        quality: s.quality || 'auto',
        url: s.url,
        isM3U8,
        proxiedPlaylist: isM3U8 ? `/api/proxy/playlist?url=${encodeURIComponent(s.url)}` : undefined
      };
    });

    return res.json({ title: animeInfo?.title?.english || animeInfo?.title?.romaji || title, episode: episodeNumber, provider: providerName, animeId: animeSearchId, streams, episodes: episodes.map(e => ({ id: e.id, number: e.number, title: e.title })) });
  } catch (err) {
    console.error('Stream provider error:', err?.message ?? err);
    return res.status(502).json({ error: 'Unable to retrieve streams', streams: [] });
  }
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`Server running on port ${PORT}` + (providerName ? ` using ${providerName}` : ' (no provider)'));
});
