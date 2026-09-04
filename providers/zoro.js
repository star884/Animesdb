// providers/zoro.js
import * as Consumet from '@consumet/extensions';
export const name = 'ZoroAdapter';

/**
 * This adapter prefers using @consumet/extensions' Zoro-like provider if present.
 * If not available, this module currently throws a clear error instructing to port a scraper.
 *
 * To port Zoro (or CloudStream's Zoro extension), replace the TODOs below with axios + parsing logic.
 */
export function create() {
  const ANIME = Consumet.ANIME ?? Consumet.default?.ANIME ?? null;

  // If consumet provides a Zoro provider (name might differ), try to find it
  const possibleNames = ['Zoro', 'Zoroanime', 'ZoroAnime', 'Zorua'];
  for (const n of possibleNames) {
    if (ANIME && typeof ANIME[n] === 'function') {
      // Wrap the consumet provider instance to normalize shapes if needed
      const inst = new ANIME[n]();
      return {
        search: (q) => inst.search(q),
        fetchAnimeInfo: (id) => inst.fetchAnimeInfo(id),
        fetchEpisodeSources: (id) => inst.fetchEpisodeSources(id)
      };
    }
  }

  // Fallback: not implemented — placeholder to port scraping code
  return {
    async search(q) {
      throw new Error('Zoro adapter not implemented locally. Please port the CloudStream Zoro extension into providers/zoro.js (see providers/template-adapter.js).');
    },
    async fetchAnimeInfo(id) {
      throw new Error('Zoro adapter not implemented locally. Please port the CloudStream Zoro extension into providers/zoro.js.');
    },
    async fetchEpisodeSources(id) {
      throw new Error('Zoro adapter not implemented locally. Please port the CloudStream Zoro extension into providers/zoro.js.');
    }
  };
}
