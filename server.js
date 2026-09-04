import express from 'express';
import cors from 'cors';
import axios from 'axios';
import path from 'path';
import { fileURLToPath } from 'url';
// import { ANIME } from '@consumet/extensions';
import * as Consumet from '@consumet/extensions';

const app = express();
const PORT = process.env.PORT || 3000;

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Correct ESM initialization for @consumet/extensions
// Try the common export shapes:
// 1) named: { ANIME } where ANIME.Gogoanime is the constructor
// 2) top-level class: { Gogoanime }
// 3) default export containing ANIME: default.ANIME
const ANIME = Consumet.ANIME ?? Consumet.default?.ANIME ?? null;
const GogoConstructor =
  Consumet.Gogoanime ??
  ANIME?.Gogoanime ??
  Consumet.default?.Gogoanime ??
  null;

if (typeof GogoConstructor !== 'function') {
  console.error(
    'Unable to find Gogoanime constructor in @consumet/extensions. Available top-level keys:',
    Object.keys(Consumet),
    'ANIME keys:',
    ANIME ? Object.keys(ANIME) : '(no ANIME)'
  );
  // Exit so the deploy fails fast with a clear log
  process.exit(1);
}

const gogoanime = new GogoConstructor();

app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// AniList GraphQL
const ANILIST_URL = 'https://graphql.anilist.co';

async function fetchAniList(query, variables = {}) {
  try {
    const response = await axios.post(
      ANILIST_URL,
      { query, variables },
      {
        headers: {
          'Content-Type': 'application/json'
        }
      }
    );

    return response.data.data;
  } catch (error) {
    console.error('AniList API Error:', error.message);
    return null;
  }
}

// Catalog
app.get('/api/catalog', async (req, res) => {
  const query = `
    query {
      trending: Page(page: 1, perPage: 10) {
        media(type: ANIME, sort: TRENDING_DESC) {
          id
          title {
            english
            romaji
          }
          coverImage {
            extraLarge
          }
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
          title {
            english
            romaji
          }
          coverImage {
            extraLarge
          }
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

  res.json(
    data || {
      trending: {
        media: []
      },
      popular: {
        media: []
      }
    }
  );
});

// Search
app.get('/api/search', async (req, res) => {
  const searchQuery = req.query.q;

  if (!searchQuery) {
    return res.status(400).json({
      error: 'Search query is required'
    });
  }

  const query = `
    query ($search: String) {
      Page(page: 1, perPage: 12) {
        media(type: ANIME, search: $search) {
          id
          title {
            english
            romaji
          }
          coverImage {
            extraLarge
          }
          bannerImage
          description
          genres
          episodes
          averageScore
        }
      }
    }
  `;

  const data = await fetchAniList(query, {
    search: searchQuery
  });

  res.json(data?.Page?.media || []);
});

// Stream sources
app.get('/api/stream', async (req, res) => {
  const { title, episode = 1 } = req.query;

  const episodeNumber = Number.parseInt(episode, 10);

  if (!title) {
    return res.status(400).json({
      error: 'Title parameter is required'
    });
  }

  if (!Number.isInteger(episodeNumber) || episodeNumber < 1) {
    return res.status(400).json({
      error: 'Episode must be a positive integer'
    });
  }

  try {
    const searchResults = await gogoanime.search(title);

    if (!searchResults?.results?.length) {
      return res.status(404).json({
        error: 'Anime not found',
        title,
        episode: episodeNumber,
        streams: []
      });
    }

    const animeId = searchResults.results[0].id;

    const animeInfo = await gogoanime.fetchAnimeInfo(animeId);

    const episodes = animeInfo?.episodes || [];

    const targetEp = episodes.find(
      (ep) => Number(ep.number) === episodeNumber
    );

    if (!targetEp) {
      return res.status(404).json({
        error: 'Episode not found',
        title,
        episode: episodeNumber,
        streams: []
      });
    }

    const sources = await gogoanime.fetchEpisodeSources(
      targetEp.id
    );

    const streams = (sources?.sources || [])
      .filter((source) => source?.url)
      .map((source) => ({
        quality: source.quality || 'Unknown',
        url: source.url,
        isM3U8:
          Boolean(source.isM3U8) ||
          source.url.includes('.m3u8')
      }));

    return res.json({
      title,
      episode: episodeNumber,
      streams
    });
  } catch (error) {
    console.error(
      'Stream provider error:',
      error
    );

    return res.status(502).json({
      error: 'Unable to retrieve stream sources',
      title,
      episode: episodeNumber,
      streams: []
    });
  }
});

// Render requires the server to listen on 0.0.0.0
app.listen(PORT, '0.0.0.0', () => {
  console.log(`Server running on port ${PORT}`);
});
