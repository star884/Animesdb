// providers/template-adapter.js
export const name = 'TemplateProvider';

/**
 * create() returns provider instance that implements:
 * - async search(query) -> { results: [ { id, title, ... } ] }
 * - async fetchAnimeInfo(animeId) -> { id, title: { english, romaji }, episodes: [ { id, number, title } ] }
 * - async fetchEpisodeSources(episodeId) -> { sources: [ { url, quality, isM3U8, server } ] }
 */
export function create() {
  return {
    async search(q) {
      // implement with axios/cheerio or an API
      return { results: [] };
    },
    async fetchAnimeInfo(animeId) {
      return { id: animeId, title: { english: null, romaji: animeId }, episodes: [] };
    },
    async fetchEpisodeSources(episodeId) {
      return { sources: [] };
    }
  };
}
