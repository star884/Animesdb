// providers/zoro.js
import * as Consumet from '@consumet/extensions';
export const name = 'ZoroAdapter';

// This adapter will prefer a consumet provider if present, otherwise it indicates it's not implemented yet.
export function create() {
  const ANIME = Consumet.ANIME ?? Consumet.default?.ANIME ?? null;
  const possibleNames = ['Zoro', 'Zoroanime', 'ZoroAnime', 'ZoroProvider'];
  for (const n of possibleNames) {
    if (ANIME && typeof ANIME[n] === 'function') {
      const inst = new ANIME[n]();
      return {
        search: (q) => inst.search(q),
        fetchAnimeInfo: (id) => inst.fetchAnimeInfo(id),
        fetchEpisodeSources: (id) => inst.fetchEpisodeSources(id)
      };
    }
  }

  // Not implemented local scraper
  return {
    async search() { throw new Error('ZoroAdapter: not implemented locally. Please port CloudStream Zoro extension.'); },
    async fetchAnimeInfo() { throw new Error('ZoroAdapter: not implemented locally.'); },
    async fetchEpisodeSources() { throw new Error('ZoroAdapter: not implemented locally.'); }
  };
                   }
