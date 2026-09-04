// providers/nineanime.js
import * as Consumet from '@consumet/extensions';
export const name = 'NineAnimeAdapter';

export function create() {
  const ANIME = Consumet.ANIME ?? Consumet.default?.ANIME ?? null;

  const possibleNames = ['NineAnime', 'Nineanime', '9anime', 'Nine']; // try common variants
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
    async search(q) {
      throw new Error('9anime adapter not implemented locally. Please port the CloudStream 9anime extension into providers/nineanime.js (see providers/template-adapter.js).');
    },
    async fetchAnimeInfo(id) {
      throw new Error('9anime adapter not implemented locally. Please port the CloudStream 9anime extension into providers/nineanime.js.');
    },
    async fetchEpisodeSources(id) {
      throw new Error('9anime adapter not implemented locally. Please port the CloudStream 9anime extension into providers/nineanime.js.');
    }
  };
}
