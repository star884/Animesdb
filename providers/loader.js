// providers/loader.js
import fs from 'fs';
import path from 'path';
import * as Consumet from '@consumet/extensions';

/**
 * Load provider factories from two places:
 * - local providers under ./providers (JS modules exporting { name, create })
 * - @consumet/extensions ANIME providers (if present)
 *
 * Returns an array of factories: { name, createInstance, source }
 * where createInstance is a function that returns a provider instance when called.
 */
export async function loadProviderFactories(preferredConsumet = [
  'Gogoanime','AnimePahe','Hianime','AnimeKai','AnimeSaturn','AnimeUnity','AnimeSama','KickAssAnime'
]) {
  const factories = [];

  // 1) Local providers (files in providers/ directory excluding this loader)
  const localDir = path.resolve(process.cwd(), 'providers');
  if (fs.existsSync(localDir)) {
    const files = fs.readdirSync(localDir).filter(f => f.endsWith('.js') && f !== 'loader.js');
    for (const file of files) {
      const full = path.join(localDir, file);
      try {
        // dynamic import
        const mod = await import(full);
        if (mod?.name && typeof mod.create === 'function') {
          factories.push({
            name: mod.name,
            createInstance: () => {
              try {
                return mod.create();
              } catch (err) {
                // if create throws, wrap it so caller sees an error
                throw new Error(`Local adapter ${mod.name} create() failed: ${err?.message ?? err}`);
              }
            },
            source: `local:${file}`
          });
        } else {
          // skip modules that don't match the adapter shape
        }
      } catch (err) {
        console.warn('Failed to import local provider', file, err?.message ?? err);
      }
    }
  }

  // 2) Consumet ANIME providers (if available)
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
