// providers/template-adapter.js
export const name = 'TemplateProvider';

/**
 * create() must return an object implementing:
 * - async search(query) -> { results: [ { id, title, ... } ] }
 * - async fetchAnimeInfo(animeId) -> { id, title: { english, romaji }, episodes: [ { id, number, title }, ... ] }
 * - async fetchEpisodeSources(episodeId) -> { sources: [ { url, quality, isM3U8, server }, ... ] }
 *
 * Implementations can use axios/fetch + cheerio to scrape HTML, or call provider APIs.
 */
export function create() {
  return {
    async search(q) {
      // TODO: implement actual search
      // Example shape:
      return { results: [] };
    },

    async fetchAnimeInfo(animeId) {
      // TODO: implement actual fetch
      return { id: animeId, title: { english: null, romaji: animeId }, episodes: [] };
    },

    async fetchEpisodeSources(episodeId) {
      // TODO: implement actual fetch
      return { sources: [] };
    }
  };
}
