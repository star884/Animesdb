// Initialize Modular HTML5 Open Source Player Context
const player = new Plyr('#player', {
    controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen']
});

// UI Target Elements Mapping
const tmdbKeyInput = document.getElementById('tmdb-key');
const searchInput = document.getElementById('search-input');
const searchBtn = document.getElementById('search-btn');
const mediaLibraryGrid = document.getElementById('media-library-grid');
const sectionHeading = document.getElementById('section-heading');

const playerContainer = document.getElementById('player-container');
const controlPanel = document.getElementById('control-panel');
const activeTitle = document.getElementById('active-title');
const activeOverview = document.getElementById('active-overview');
const activeDate = document.getElementById('active-date');
const activeRating = document.getElementById('active-rating');

const scrapeStreamBtn = document.getElementById('scrape-stream-btn');
const torrentStreamBtn = document.getElementById('torrent-stream-btn');
const magnetInput = document.getElementById('magnet-input');

let currentActiveMedia = null;

// Primary App Initialization Pipeline
document.addEventListener('DOMContentLoaded', () => {
    // Check if user has a previously saved developer token in current session
    const cachedKey = localStorage.getItem('oss_tmdb_key');
    if (cachedKey) {
        tmdbKeyInput.value = cachedKey;
        loadAutopopulatedLibrary(cachedKey);
    } else {
        // Fallback layout when API credentials are unassigned
        mediaLibraryGrid.innerHTML = `
            <div class="col-span-full bg-slate-900 border border-slate-800 rounded-xl p-8 text-center text-sm text-slate-400">
                ⚠️ Enter a free <strong>TMDB API Key</strong> above to automatically populate this library.
            </div>`;
    }
});

// Cache developer tokens to streamline workspace state across reloads
tmdbKeyInput.addEventListener('change', (e) => {
    const key = e.target.value.trim();
    if(key) {
        localStorage.setItem('oss_tmdb_key', key);
        loadAutopopulatedLibrary(key);
    }
});

// Index Database Autopopulation Pipeline via TMDB API Discovery
async function loadAutopopulatedLibrary(apiKey) {
    try {
        sectionHeading.innerText = "Trending Media Content Showcase";
        const res = await fetch(`https://themoviedb.org{apiKey}`);
        const data = await res.json();
        renderLibraryGrid(data.results);
    } catch (err) {
        console.error('Failed to populate streaming library catalog view:', err);
    }
}

// Media Catalog Searching Event Triggers
searchBtn.addEventListener('click', executionSearchQuery);
searchInput.addEventListener('keypress', (e) => { if(e.key === 'Enter') executionSearchQuery(); });

async function executionSearchQuery() {
    const apiKey = tmdbKeyInput.value.trim();
    const query = searchInput.value.trim();

    if(!apiKey) { alert('Please supply a valid TMDB Dev Key configuration parameter.'); return; }
    if(!query) return;

    try {
        sectionHeading.innerText = `Search Results matches for: "${query}"`;
        const res = await fetch(`https://themoviedb.org{apiKey}&query=${encodeURIComponent(query)}`);
        const data = await res.json();
        renderLibraryGrid(data.results);
    } catch (err) {
        console.error('Database catalog lookups threw execution error:', err);
    }
}

// Render dynamic DOM components onto grid containers
function renderLibraryGrid(items) {
    mediaLibraryGrid.innerHTML = '';
    if (!items || items.length === 0) {
        mediaLibraryGrid.innerHTML = `<div class="col-span-full text-center text-sm text-slate-500">No media entities cataloged under criteria layout.</div>`;
        return;
    }

    items.forEach(item => {
        const card = document.createElement('div');
        card.className = "group bg-slate-900 border border-slate-800/60 rounded-xl overflow-hidden cursor-pointer hover:border-indigo-500 transition-all duration-300 hover:-translate-y-1 shadow-md shadow-black/40";
        card.innerHTML = `
            <div class="relative aspect-[2/3] overflow-hidden bg-slate-950">
                <img src="${item.poster_path ? 'https://tmdb.org' + item.poster_path : 'https://placehold.co'}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="Poster">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3">
                    <span class="bg-indigo-600 text-white text-[10px] font-black px-2 py-1 rounded shadow">VIEW MEDIA</span>
                </div>
            </div>
            <div class="p-3 space-y-1">
                <h4 class="text-xs font-bold truncate tracking-wide">${item.title}</h4>
                <div class="flex justify-between items-center text-[10px] font-medium text-slate-400">
                    <span>${item.release_date ? item.release_date.split('-')[0] : 'N/A'}</span>
                    <span class="text-amber-500 font-bold">★ ${item.vote_average ? item.vote_average.toFixed(1) : '0.0'}</span>
                </div>
            </div>
        `;
        card.addEventListener('click', () => selectMediaElement(item));
        mediaLibraryGrid.appendChild(card);
    });
}

// Map selected model attributes into view state configurations
function selectMediaElement(media) {
    currentActiveMedia = media;
    controlPanel.classList.remove('hidden');
    
    activeTitle.innerText = media.title;
    activeOverview.innerText = media.overview || 'No context database logs compiled for this asset entity file.';
    activeDate.innerText = `Released: ${media.release_date || 'Unknown Date'}`;
    activeRating.innerText = `★ ${media.vote_average ? media.vote_average.toFixed(1) : '0'}/10 User Score`;
    
    // Auto-scroll layout canvas viewport focus smoothly
    controlPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// ROUTER ACTION 1: Run Free Open-Source Embed Scraper Framework Pipeline
scrapeStreamBtn.addEventListener('click', () => {
    if(!currentActiveMedia) return;
    
    // Leverage the open public structural APIs mapping directly over the TMDB identifier variables 
    const vidsrcEmbedUrl = `https://vidsrc.to{currentActiveMedia.id}`;
    
    // Swap the internal HTML5 player with a responsive external iFrame mapping interface
    playerContainer.innerHTML = `<iframe src="${vidsrcEmbedUrl}" class="w-full h-full border-0" allowfullscreen allow="autoplay; encrypted-media"></iframe>`;
    playerContainer.classList.remove('hidden');
    playerContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

// ROUTER ACTION 2: Fire Sequential WebTorrent In-Memory Buffer Pipeline
torrentStreamBtn.addEventListener('click', () => {
    const magnetURI = magnetInput.value.trim();
    if (!magnetURI) { alert('Please pass a valid infoHash or torrent Magnet stream source address.'); return; }

    // Reconstruct core HTML5 element tree properties in case clean canvas toggles were rewritten by iFrame embeds
    playerContainer.innerHTML = `<video id="player" playsinline controls class="w-full h-full"><source src="" type="video/mp4" /></video>`;
    const freshPlayer = new Plyr('#player', { controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'] });

    const targetEndpointPath = `/api/stream?torrent=${encodeURIComponent(magnetURI)}`;
    const nativeVideoDomElement = document.querySelector('#player');
    
    playerContainer.classList.remove('hidden');
    nativeVideoDomElement.src = targetEndpointPath;
    
    freshPlayer.play();
    playerContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
});
