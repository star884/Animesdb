import express from 'express';
import cors from 'cors';
import axios from 'axios';
import path from 'path';
import { fileURLToPath } from 'url';
import { ANIME } from '@consumet/extensions'; // Import Consumet Scraper

const app = express();
// Bind dynamically to process.env.PORT provided by Render
const PORT = process.env.PORT || 3000;

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Initialize Consumet Gogoanime provider instance
const gogoanime = new ANIME.Gogoanime();

app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// 1. ANILIST GRAPHQL HELPER (Automated Catalog Population)
const ANILIST_URL = 'https://graphql.anilist.co';

async function fetchAniList(query, variables = {}) {
  try {
    const response = await axios.post(
      ANILIST_URL,
      { query, variables },
      { headers: { 'Content-Type': 'application/json' } }
    );
    return response.data.data;
  } catch (error) {
    console.error('AniList API Error:', error.message);
    return null;
  }
}

// 2. ROUTE: Get Trending & Popular Catalog
app.get('/api/catalog', async (req, res) => {
  const query = `
    query {
      trending: Page(page: 1, perPage: 10) {
        media(type: ANIME, sort: TRENDING_DESC) {
          id title { english romaji } coverImage { extraLarge } bannerImage description genres episodes averageScore
        }
      }
      popular: Page(page: 1, perPage: 10) {
        media(type: ANIME, sort: POPULARITY_DESC) {
          id title { english romaji } coverImage { extraLarge } bannerImage description genres episodes averageScore
        }
      }
    }
  `;

  const data = await fetchAniList(query);
  res.json(data || { trending: { media: [] }, popular: { media: [] } });
});

// 3. ROUTE: Search Anime Library
app.get('/api/search', async (req, res) => {
  const searchQuery = req.query.q;
  const query = `
    query ($search: String) {
      Page(page: 1, perPage: 12) {
        media(type: ANIME, search: $search) {
          id title { english romaji } coverImage { extraLarge } bannerImage description genres episodes averageScore
        }
      }
    }
  `;

  const data = await fetchAniList(query, { search: searchQuery });
  res.json(data?.Page?.media || []);
});

// 4. ROUTE: Fetch Episode Video Streams
app.get('/api/stream', async (req, res) => {
  const { title, episode = 1 } = req.query;

  if (!title) {
    return res.status(400).json({ error: 'Title is required' });
  }

  try {
    // Search Gogoanime via Consumet for target title
    const searchResults = await gogoanime.search(title);

    if (searchResults.results && searchResults.results.length > 0) {
      const animeId = searchResults.results[0].id;
      
      // Fetch episode list
      const animeInfo = await gogoanime.fetchAnimeInfo(animeId);
      const targetEp = animeInfo.episodes.find(
        (ep) => ep.number === parseInt(episode, 10)
      );

      if (targetEp) {
        // Fetch raw direct streaming sources (.m3u8 / .mp4)
        const sources = await gogoanime.fetchEpisodeSources(targetEp.id);
        
        const streams = sources.sources.map((s) => ({
          quality: `${s.quality} (Live Stream)`,
          url: s.url,
          isM3U8: s.isM3U8
        }));

        return res.json({ title, episode: parseInt(episode, 10), streams });
      }
    }

    // Fallback stream sources if scraping yields no matches
    const fallbackStreams = [
      {
        quality: '1080p (Fallback Test)',
        url: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4'
      },
      {
        quality: '720p (Fallback Test)',
        url: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4'
      }
    ];

    res.json({ title, episode: parseInt(episode, 10), streams: fallbackStreams });
  } catch (err) {
    console.error('Scraper Error:', err.message);

    // Serve default fallback streams on scraping errors
    res.json({
      title,
      episode: parseInt(episode, 10),
      streams: [
        {
          quality: '1080p (Fallback)',
          url: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4'
        }
      ]
    });
  }
});

// Bind to host 0.0.0.0 so Render can properly route traffic externally
app.listen(PORT, '0.0.0.0', () => {
  console.log(`Server running on port ${PORT}`);
});
