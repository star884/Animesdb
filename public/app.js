let currentAnime = null;
let currentStreams = [];

document.addEventListener('DOMContentLoaded', () => {
  loadCatalog();

  document.getElementById('searchBtn').addEventListener('click', handleSearch);
  document.getElementById('closeModal').addEventListener('click', closeModal);
  document.getElementById('epSelect').addEventListener('change', loadStream);
  document.getElementById('serverSelect').addEventListener('change', changeServer);
});

// Fetch automatically populated catalog from AniList API proxy
async function loadCatalog() {
  const res = await fetch('/api/catalog');
  const data = await res.json();

  if (data.trending?.media?.length > 0) {
    setupHero(data.trending.media[0]);
    renderGrid('trendingGrid', data.trending.media);
  }

  if (data.popular?.media?.length > 0) {
    renderGrid('popularGrid', data.popular.media);
  }
}

function setupHero(anime) {
  const title = anime.title.english || anime.title.romaji;
  document.getElementById('heroTitle').innerText = title;
  document.getElementById('heroDesc').innerHTML = anime.description || 'No description available.';
  document.getElementById('heroBanner').style.backgroundImage = `url(${anime.bannerImage || anime.coverImage.extraLarge})`;
  
  document.getElementById('heroPlayBtn').onclick = () => openStreamModal(anime);
}

function renderGrid(elementId, items) {
  const grid = document.getElementById(elementId);
  grid.innerHTML = items.map(item => `
    <div class="card" onclick='openStreamModal(${JSON.stringify(item).replace(/'/g, "&apos;")})'>
      <img src="${item.coverImage.extraLarge}" alt="${item.title.english || item.title.romaji}">
      <div class="card-title">${item.title.english || item.title.romaji}</div>
    </div>
  `).join('');
}

async function handleSearch() {
  const query = document.getElementById('searchInput').value;
  if (!query) return;

  const res = await fetch(`/api/search?q=${encodeURIComponent(query)}`);
  const results = await res.json();
  
  renderGrid('trendingGrid', results);
  document.querySelector('.section h2').innerText = `Search Results for: "${query}"`;
}

function openStreamModal(anime) {
  currentAnime = anime;
  const title = anime.title.english || anime.title.romaji;
  
  document.getElementById('modalTitle').innerText = title;
  document.getElementById('videoModal').style.display = 'flex';

  // Populate Episode Selector Dropdown
  const epCount = anime.episodes || 12;
  const epSelect = document.getElementById('epSelect');
  epSelect.innerHTML = Array.from({ length: epCount }, (_, i) => `<option value="${i + 1}">Episode ${i + 1}</option>`).join('');

  loadStream();
}

async function loadStream() {
  const episode = document.getElementById('epSelect').value;
  const title = currentAnime.title.english || currentAnime.title.romaji;

  const res = await fetch(`/api/stream?title=${encodeURIComponent(title)}&episode=${episode}`);
  const data = await res.json();

  currentStreams = data.streams || [];
  
  const serverSelect = document.getElementById('serverSelect');
  serverSelect.innerHTML = currentStreams.map((s, index) => `<option value="${index}">${s.quality}</option>`).join('');

  changeServer();
}

function changeServer() {
  const serverIndex = document.getElementById('serverSelect').value;
  const player = document.getElementById('videoPlayer');
  
  if (currentStreams[serverIndex]) {
    player.src = currentStreams[serverIndex].url;
    player.play();
  }
}

function closeModal() {
  const player = document.getElementById('videoPlayer');
  player.pause();
  player.src = '';
  document.getElementById('videoModal').style.display = 'none';
    }
