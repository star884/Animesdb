// public/app.js
let currentAnime = null;
let currentStreams = [];
let videoPlayer = null;

document.addEventListener('DOMContentLoaded', () => {
  loadCatalog();
  document.getElementById('searchBtn').addEventListener('click', handleSearch);
  document.getElementById('closeModal').addEventListener('click', closeModal);
  document.getElementById('epSelect').addEventListener('change', loadStream);
  document.getElementById('serverSelect').addEventListener('change', changeServer);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });
});

async function loadCatalog() {
  try {
    const res = await fetch('/api/catalog');
    const data = await res.json();
    if (data.trending?.media?.length > 0) {
      setupHero(data.trending.media[0]);
      renderGrid('trendingGrid', data.trending.media);
    }
    if (data.popular?.media?.length > 0) {
      renderGrid('popularGrid', data.popular.media);
    }
  } catch (e) {
    console.error('Failed to load catalog', e);
  }
}

function setupHero(anime) {
  const title = anime.title?.english || anime.title?.romaji || 'Unknown';
  document.getElementById('heroTitle').innerText = title;
  document.getElementById('heroDesc').innerHTML = anime.description ? anime.description.replace(/<\/?[^>]+(>|$)/g, "") : 'No description available.';
  document.getElementById('heroBanner').style.backgroundImage = `url(${anime.bannerImage || anime.coverImage?.extraLarge || ''})`;
  document.getElementById('heroPlayBtn').onclick = () => openStreamModal(anime);
}

function renderGrid(elementId, items) {
  const grid = document.getElementById(elementId);
  grid.innerHTML = '';
  items.forEach(item => {
    const card = document.createElement('div');
    card.className = 'card';
    card.tabIndex = 0;
    card.setAttribute('role', 'button');
    card.dataset.title = item.title?.english || item.title?.romaji;
    card.dataset.animeId = item.id;
    card.innerHTML = `
      <img loading="lazy" src="${item.coverImage?.extraLarge || ''}" alt="${item.title?.english || item.title?.romaji || 'Cover'}" />
      <div class="card-title">${item.title?.english || item.title?.romaji}</div>
    `;
    card.addEventListener('click', () => openStreamModal(item));
    card.addEventListener('keypress', (e) => { if (e.key === 'Enter') openStreamModal(item); });
    grid.appendChild(card);
  });
}

async function handleSearch() {
  const query = document.getElementById('searchInput').value.trim();
  if (!query) return;
  try {
    const res = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
    const results = await res.json();
    document.getElementById('trendingTitle').innerText = `Search Results for: "${query}"`;
    renderGrid('trendingGrid', results);
  } catch (e) {
    console.error('Search failed', e);
  }
}

async function openStreamModal(anime) {
  currentAnime = anime;
  const title = anime.title?.english || anime.title?.romaji || 'Unknown';
  document.getElementById('modalTitle').innerText = title;
  document.getElementById('videoModal').style.display = 'flex';
  document.getElementById('videoModal').setAttribute('aria-hidden', 'false');

  // Populate episodes using server-side anime info when available
  const epSelect = document.getElementById('epSelect');
  epSelect.innerHTML = '';
  const epCount = anime.episodes || 0;
  if (anime.id) {
    try {
      const infoRes = await fetch(`/api/anime?animeId=${encodeURIComponent(anime.id)}`);
      const info = await infoRes.json();
      const eps = info?.episodes || [];
      if (eps.length) {
        eps.forEach(ep => {
          const o = document.createElement('option');
          o.value = ep.number;
          o.textContent = `Episode ${ep.number}${ep.title ? ' - ' + ep.title : ''}`;
          epSelect.appendChild(o);
        });
      } else if (epCount) {
        for (let i = 1; i <= epCount; i++) {
          const o = document.createElement('option');
          o.value = i;
          o.textContent = `Episode ${i}`;
          epSelect.appendChild(o);
        }
      } else {
        for (let i = 1; i <= 12; i++) {
          const o = document.createElement('option');
          o.value = i;
          o.textContent = `Episode ${i}`;
          epSelect.appendChild(o);
        }
      }
    } catch (e) {
      console.warn('Failed to load episodes from /api/anime', e);
      for (let i = 1; i <= Math.max(12, epCount || 12); i++) {
        const o = document.createElement('option');
        o.value = i;
        o.textContent = `Episode ${i}`;
        epSelect.appendChild(o);
      }
    }
  } else {
    for (let i = 1; i <= (epCount || 12); i++) {
      const o = document.createElement('option');
      o.value = i;
      o.textContent = `Episode ${i}`;
      epSelect.appendChild(o);
    }
  }

  // Initialize videojs player if needed
  if (!videoPlayer) {
    videoPlayer = videojs('videoPlayer', {
      controls: true,
      fluid: true,
      autoplay: false
    });
  }

  loadStream();
}

async function loadStream() {
  const episode = document.getElementById('epSelect').value;
  const animeId = currentAnime?.id;
  const title = currentAnime?.title?.english || currentAnime?.title?.romaji || '';

  try {
    const query = animeId ? `/api/stream?animeId=${encodeURIComponent(animeId)}&episode=${episode}` : `/api/stream?title=${encodeURIComponent(title)}&episode=${episode}`;
    const res = await fetch(query);
    const data = await res.json();
    currentStreams = data.streams || [];

    const serverSelect = document.getElementById('serverSelect');
    serverSelect.innerHTML = '';
    currentStreams.forEach((s, idx) => {
      const o = document.createElement('option');
      o.value = idx;
      o.textContent = `${s.server || 'server'} — ${s.quality || 'auto'}`;
      serverSelect.appendChild(o);
    });

    if (currentStreams.length) {
      changeServer();
    } else {
      console.warn('No streams available', data);
      alert('No streams available for this episode.');
    }
  } catch (e) {
    console.error('Failed to load streams', e);
    alert('Failed to load streams. Try again later.');
  }
}

function changeServer() {
  const serverIndex = document.getElementById('serverSelect').value;
  const stream = currentStreams[serverIndex];
  if (!stream || !videoPlayer) return;

  videoPlayer.pause();

  if (stream.isM3U8 && stream.proxiedPlaylist) {
    videoPlayer.src({ src: stream.proxiedPlaylist, type: 'application/x-mpegURL' });
  } else {
    videoPlayer.src({ src: stream.url, type: 'video/mp4' });
  }
  videoPlayer.play().catch(() => {});
}

function closeModal() {
  document.getElementById('videoModal').style.display = 'none';
  document.getElementById('videoModal').setAttribute('aria-hidden', 'true');
  if (videoPlayer) {
    videoPlayer.pause();
    videoPlayer.src({ src: '', type: '' });
  }
    }
