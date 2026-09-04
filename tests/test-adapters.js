// tests/test-adapters.js
import { loadProviderFactories } from '../providers/loader.js';

async function run() {
  const factories = await loadProviderFactories();
  console.log('Loaded provider factories:', factories.map(f => `${f.name}@${f.source || 'unknown'}`));

  for (const f of factories) {
    console.log('--- Testing', f.name);
    try {
      const inst = f.createInstance();
      if (!inst) {
        console.log('  no instance');
        continue;
      }
      // test search
      if (typeof inst.search === 'function') {
        const s = await inst.search('naruto');
        console.log('  search result length:', Array.isArray(s?.results) ? s.results.length : 'N/A');
      } else {
        console.log('  no search()');
      }
    } catch (err) {
      console.error('  provider test error:', err?.message ?? err);
    }
  }
}

run().catch(e => {
  console.error('Test runner error:', e);
  process.exit(1);
});
