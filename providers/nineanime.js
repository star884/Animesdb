// providers/nineanime.js
import * as Consumet from '@consumet/extensions';
export const name = 'NineAnimeAdapter';

export function create() {
  const ANIME = Consumet.ANIME ?? Consumet.default?.ANIME ?? null;
  const possibleNames = ['NineAnime', 'Nineanime', 'Nine', 'AnimeNine', 'Nine9'];
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

  return {
    async search() { throw new Error('NineAnimeAdapter: not implemented locally.'); },
    async fetchAnimeInfo() { throw new Error('NineAnimeAdapter: not implemented locally.'); },
    async fetchEpisodeSources() { throw new Error('NineAnimeAdapter: not implemented locally.'); }
  };
}
