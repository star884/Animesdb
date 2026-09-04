// providers/loader.js
import fs from 'fs';
import path from 'path';
import * as Consumet from '@consumet/extensions';

/**
 * Load provider factories from:
 *  - local providers under ./providers (modules that export { name, create })
 *  - @consumet/extensions (ANIME named constructors)
 *
 * Returns array of factories: { name, createInstance: () => providerInstance, source }
 */
export async function loadProviderFactories(preferredConsumet = [
  'Gogoanime','AnimePahe','Hianime','AnimeKai','AnimeSaturn','AnimeUnity','AnimeSama','KickAssAnime'
]) {
  const factories = [];

  // Load local adapters (skip loader.js itself)
  const localDir = path.resolve(process.cwd(), 'providers');
  if (fs.existsSync(localDir)) {
    const files = fs.readdirSync(localDir).filter(f => f.endsWith('.js') && f !== 'loader.js');
    for (const file of files) {
      const full = path.join(localDir, file);
      try {
        const mod = await import(full);
        if (mod?.name && typeof mod.create === 'function') {
          factories.push({
            name: mod.name,
            createInstance: () => {
              try {
                return mod.create();
              } catch (err) {
                throw new Error(`Local adapter ${mod.name} create() failed: ${err?.message ?? err}`);
              }
            },
            source: `local:${file}`
          });
        }
      } catch (err) {
        console.warn('Failed to import local provider', file, err?.message ?? err);
      }
    }
  }

  // Load Consumet ANIME named providers
  const ANIME = Consumet.ANIME ?? Consumet.default?.ANIME ?? null;
  if (ANIME) {
    for (const name of preferredConsumet) {
      const ctor = ANIME[name];
      if (typeof ctor === 'function') {
        factories.push({
          name,
          createInstance: () => new ctor(),
          source: 'consumet'
        });
      }
    }
  }

  return factories;
}
