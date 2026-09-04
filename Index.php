<?php
/**
 * TRIANIME - Tenrai Edition
 * 
 * @version 2.0.0
 * @description Fully responsive SPA using Tenrai API, Tailwind CSS, and Vanilla JS.
 */
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TRIANIME - Stream Unlimited</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: {
                            bg: '#0a0a0a',
                            card: '#121212',
                            border: '#1e1e1e',
                            text: '#e0e0e0'
                        },
                        accent: '#ffbade',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0a0a0a; 
        }
        ::-webkit-scrollbar-thumb {
            background: #333; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #ffbade; 
        }

        /* Glassmorphism */
        .glass {
            background: rgba(18, 18, 18, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Loader */
        .loader {
            border: 3px solid #1f2937;
            border-top: 3px solid #ffbade;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-dark-bg text-dark-text font-sans antialiased min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="glass fixed w-full z-50 top-0 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 cursor-pointer" onclick="app.router.navigate('home')">
                    <span class="text-2xl font-bold tracking-tighter text-white">TRIA<span class="text-accent">ANIME</span></span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <button onclick="app.router.navigate('home')" class="nav-btn hover:text-accent px-3 py-2 rounded-md text-sm font-medium transition-colors" data-route="home">Home</button>
                        <button onclick="app.router.navigate('browse')" class="nav-btn hover:text-accent px-3 py-2 rounded-md text-sm font-medium transition-colors" data-route="browse">Browse</button>
                        <button onclick="app.router.navigate('history')" class="nav-btn hover:text-accent px-3 py-2 rounded-md text-sm font-medium transition-colors" data-route="history">History</button>
                    </div>
                </div>

                <!-- Search & Mobile Menu -->
                <div class="flex items-center gap-4">
                    <!-- Search Bar -->
                    <div class="hidden md:flex relative">
                        <input type="text" id="search-input" placeholder="Search anime..." 
                            class="bg-dark-card border border-dark-border rounded-full py-1.5 px-4 text-sm focus:outline-none focus:border-accent w-48 focus:w-64 transition-all duration-300">
                        <button onclick="app.search.trigger()" class="absolute right-3 top-1.5 text-dark-text hover:text-accent">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <!-- Mobile Menu Button -->
                    <div class="md:hidden flex items-center">
                        <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="text-dark-text hover:text-accent">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-dark-card border-b border-dark-border">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <button onclick="app.router.navigate('home')" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium hover:bg-dark-bg hover:text-accent">Home</button>
                <button onclick="app.router.navigate('browse')" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium hover:bg-dark-bg hover:text-accent">Browse</button>
                <button onclick="app.router.navigate('history')" class="block w-full text-left px-3 py-2 rounded-md text-base font-medium hover:bg-dark-bg hover:text-accent">History</button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main id="app-container" class="flex-grow pt-20 pb-10 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
        <!-- Content injected via JS -->
        <div class="flex justify-center items-center h-64">
            <div class="loader"></div>
        </div>
    </main>

    <!-- JavaScript Application Logic -->
    <script>
        /**
         * API Configuration
         * Using the public Tenrai API endpoint (mguo.me)
         */
        const API_BASE = 'https://api.mguo.me';

        const api = {
            async fetch(endpoint, params = {}) {
                const url = new URL(`${API_BASE}${endpoint}`);
                Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
                
                try {
                    const res = await fetch(url.toString());
                    if (!res.ok) throw new Error(`API Error: ${res.status}`);
                    return await res.json();
                } catch (error) {
                    console.error("API Fetch Failed:", error);
                    return null;
                }
            },

            async search(query, page = 1, size = 12) {
                // Tenrai search endpoint
                return await api.fetch('/search', { 
                    query, 
                    page, 
                    perPage: size,
                    type: 'all' 
                });
            },

            async getAnimeDetails(id) {
                // Tenrai returns details via search with ID or specific endpoint
                // We use search with limit 1 and specific ID if available, or just search the ID
                return await api.fetch('/search', { query: id, type: 'id' });
            },
            
            // Helper to get watch page data
            async getWatchPage(id, episodeId) {
                // Tenrai provides video URLs directly in search/details, 
                // but we can also use the embed URL for the player
                return {
                    id,
                    episodeId,
                    // Standard vidsrc embedding using Tenrai data structure
                    embedUrl: `https://vidsrc.cc/embed/anime?id=${episodeId}&server=vidstream` 
                };
            }
        };

        const app = {
            state: {
                currentView: 'home',
                searchResults: [],
                watchHistory: JSON.parse(localStorage.getItem('trianime_history') || '[]'),
                currentAnime: null
            },

            init() {
                this.router.init();
                this.search.init();
                this.render.home();
            },

            router: {
                init() {
                    window.addEventListener('hashchange', () => this.handleRoute());
                    this.handleRoute();
                },

                navigate(route) {
                    window.location.hash = route;
                },

                handleRoute() {
                    const hash = window.location.hash.replace('#', '') || 'home';
                    document.querySelectorAll('.nav-btn').forEach(btn => {
                        btn.classList.remove('text-accent');
                        if(btn.dataset.route === hash) btn.classList.add('text-accent');
                    });
                    app.render[hash]();
                }
            },

            search: {
                init() {
                    const input = document.getElementById('search-input');
                    input.addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') this.trigger();
                    });
                },

                async trigger() {
                    const input = document.getElementById('search-input');
                    const query = input.value.trim();
                    if (!query) return;
                    
                    app.router.navigate('browse');
                    await app.render.searchResults(query);
                }
            },

            storage: {
                addToHistory(anime) {
                    const item = {
                        id: anime.id,
                        title: anime.title?.english || anime.title?.romaji || 'Unknown',
                        image: anime.cover,
                        episodes: anime.episodes || 0
                    };
                    
                    // Remove if exists to update timestamp
                    app.state.watchHistory = app.state.watchHistory.filter(a => a.id !== item.id);
                    app.state.watchHistory.unshift(item);
                    
                    // Limit history
                    if(app.state.watchHistory.length > 50) app.state.watchHistory.pop();
                    
                    localStorage.setItem('trianime_history', JSON.stringify(app.state.watchHistory));
                }
            },

            render: {
                home() {
                    const container = document.getElementById('app-container');
                    container.innerHTML = `
                        <div class="space-y-8 fade-in">
                            <!-- Trending Section -->
                            <div>
                                <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
                                    <i class="fas fa-fire text-accent"></i> Trending Now
                                </h2>
                                <div id="trending-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                    <!-- Loading State -->
                                    <div class="animate-pulse bg-dark-card h-64 rounded-lg"></div>
                                    <div class="animate-pulse bg-dark-card h-64 rounded-lg"></div>
                                    <div class="animate-pulse bg-dark-card h-64 rounded-lg"></div>
                                    <div class="animate-pulse bg-dark-card h-64 rounded-lg"></div>
                                    <div class="animate-pulse bg-dark-card h-64 rounded-lg"></div>
                                </div>
                            </div>
                        </div>
                    `;
                    this.loadTrending();
                },

                async loadTrending() {
                    const grid = document.getElementById('trending-grid');
                    const data = await api.search('', 1, 20); // Empty query often returns trending/all on Tenrai
                    // Fallback to specific popular query if empty query fails or returns nothing
                    let results = data?.results || [];
                    if(results.length === 0) results = (await api.search('popular', 1, 20))?.results || [];

                    grid.innerHTML = results.map(anime => `
                        <div class="group relative bg-dark-card rounded-lg overflow-hidden hover:shadow-lg hover:shadow-accent/20 transition-all cursor-pointer"
                             onclick="app.render.animeDetail('${anime.id}')">
                            <div class="aspect-[2/3] overflow-hidden">
                                <img src="${anime.cover}" alt="${anime.title?.english}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     loading="lazy">
                                <div class="absolute inset-0 bg-gradient-to-t from-dark-bg/90 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3">
                                    <span class="text-xs font-bold text-accent">
                                        <i class="fas fa-play-circle mr-1"></i> Watch Now
                                    </span>
                                </div>
                                <div class="absolute top-2 right-2 bg-dark-bg/80 px-2 py-1 rounded text-xs font-bold text-accent">
                                    ${anime.status || 'N/A'}
                                </div>
                            </div>
                            <div class="p-3">
                                <h3 class="text-sm font-medium line-clamp-2 group-hover:text-accent transition-colors">${anime.title?.english || anime.title?.romaji}</h3>
                                <p class="text-xs text-gray-500 mt-1">${anime.type} • ${anime.releaseDate || ''}</p>
                            </div>
                        </div>
                    `).join('');
                },

                browse() {
                    const container = document.getElementById('app-container');
                    container.innerHTML = `
                        <div class="space-y-6 fade-in">
                            <h2 class="text-2xl font-bold">Browse All Anime</h2>
                            <div id="browse-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                <div class="animate-pulse bg-dark-card h-64 rounded-lg"></div>
                                <div class="animate-pulse bg-dark-card h-64 rounded-lg"></div>
                                <div class="animate-pulse bg-dark-card h-64 rounded-lg"></div>
                            </div>
                            <div id="load-more-container" class="flex justify-center mt-8 hidden">
                                <button id="load-more-btn" class="bg-dark-card hover:bg-dark-border text-white px-6 py-2 rounded-full transition-colors">
                                    Load More
                                </button>
                            </div>
                        </div>
                    `;
                    this.loadBrowse();
                },

                async loadBrowse(page = 1) {
                    const grid = document.getElementById('browse-grid');
                    const loadMoreBtn = document.getElementById('load-more-btn');
                    const loadMoreContainer = document.getElementById('load-more-container');
                    
                    // If not first page, clear grid
                    if(page > 1) grid.innerHTML = '';

                    const data = await api.search('', page, 20);
                    const results = data?.results || [];
                    
                    // Hide load more if no results
                    if(results.length === 0) {
                        loadMoreContainer.classList.add('hidden');
                        return;
                    }
                    
                    loadMoreContainer.classList.remove('hidden');

                    grid.insertAdjacentHTML('beforeend', results.map(anime => `
                        <div class="group relative bg-dark-card rounded-lg overflow-hidden hover:shadow-lg hover:shadow-accent/20 transition-all cursor-pointer"
                             onclick="app.render.animeDetail('${anime.id}')">
                            <div class="aspect-[2/3] overflow-hidden">
                                <img src="${anime.cover}" alt="${anime.title?.english}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     loading="lazy">
                                <div class="absolute top-2 right-2 bg-dark-bg/80 px-2 py-1 rounded text-xs font-bold text-accent">
                                    ${anime.status || 'N/A'}
                                </div>
                            </div>
                            <div class="p-3">
                                <h3 class="text-sm font-medium line-clamp-2 group-hover:text-accent transition-colors">${anime.title?.english || anime.title?.romaji}</h3>
                                <p class="text-xs text-gray-500 mt-1">${anime.type} • ${anime.releaseDate || ''}</p>
                            </div>
                        </div>
                    `).join(''));

                    // Setup load more
                    if(loadMoreBtn) {
                        loadMoreBtn.onclick = () => this.loadBrowse(page + 1);
                    }
                },

                async searchResults(query) {
                    const container = document.getElementById('app-container');
                    container.innerHTML = `
                        <div class="space-y-6 fade-in">
                            <h2 class="text-2xl font-bold">Search: "${query}"</h2>
                            <div id="search-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                <div class="animate-pulse bg-dark-card h-64 rounded-lg"></div>
                                <div class="animate-pulse bg-dark-card h-64 rounded-lg"></div>
                            </div>
                        </div>
                    `;
                    
                    const data = await api.search(query, 1, 20);
                    const results = data?.results || [];
                    const grid = document.getElementById('search-grid');

                    if(results.length === 0) {
                        grid.innerHTML = `<div class="col-span-full text-center py-10 text-gray-500">No results found for "${query}"</div>`;
                        return;
                    }

                    grid.innerHTML = results.map(anime => `
                        <div class="group relative bg-dark-card rounded-lg overflow-hidden hover:shadow-lg hover:shadow-accent/20 transition-all cursor-pointer"
                             onclick="app.render.animeDetail('${anime.id}')">
                            <div class="aspect-[2/3] overflow-hidden">
                                <img src="${anime.cover}" alt="${anime.title?.english}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     loading="lazy">
                                <div class="absolute top-2 right-2 bg-dark-bg/80 px-2 py-1 rounded text-xs font-bold text-accent">
                                    ${anime.status || 'N/A'}
                                </div>
                            </div>
                            <div class="p-3">
                                <h3 class="text-sm font-medium line-clamp-2 group-hover:text-accent transition-colors">${anime.title?.english || anime.title?.romaji}</h3>
                                <p class="text-xs text-gray-500 mt-1">${anime.type} • ${anime.releaseDate || ''}</p>
                            </div>
                        </div>
                    `).join('');
                },

                async animeDetail(id) {
                    const container = document.getElementById('app-container');
                    
                    // Show loading state
                    container.innerHTML = `
                        <div class="animate-pulse space-y-4">
                            <div class="h-64 bg-dark-card rounded-lg w-full"></div>
                            <div class="h-8 bg-dark-card rounded w-1/2"></div>
                            <div class="h-32 bg-dark-card rounded w-full"></div>
                        </div>
                    `;

                    const data = await api.search(id, 1, 1); // Fetch specific item
                    const anime = data?.results[0];
                    
                    if(!anime) {
                        container.innerHTML = `<div class="text-center py-20">Anime not found.</div>`;
                        return;
                    }

                    app.storage.addToHistory(anime);
                    app.state.currentAnime = anime;

                    // Prepare episodes list
                    const episodes = [];
                    // Tenrai search results might contain episodes directly or just info. 
                    // We assume the main result is the anime info.
                    // If the API returns episodes inside the search result, use them.
                    // Otherwise we assume we need to fetch watch page to get episodes or infer from ID.
                    // For this implementation, we'll assume the API returns 'episodes' array or we use a standard range if missing.
                    
                    // Note: Tenrai /search often returns the anime info. 
                    // We will generate episode buttons 1 to N if not provided, or use provided list.
                    const totalEpisodes = anime.episodes || 12; 
                    
                    // Create episode list if not present in search result
                    const episodeList = anime.episodes || Array.from({length: Math.min(totalEpisodes, 50)}, (_, i) => i + 1);

                    container.innerHTML = `
                        <div class="fade-in pb-10">
                            <!-- Header -->
                            <div class="flex flex-col md:flex-row gap-6 mb-8">
                                <img src="${anime.cover}" alt="${anime.title?.english}" class="w-full md:w-64 rounded-lg shadow-lg shadow-black/50">
                                <div class="flex-1">
                                    <h1 class="text-3xl md:text-4xl font-bold mb-2">${anime.title?.english || anime.title?.romaji}</h1>
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <span class="bg-dark-card px-3 py-1 rounded-full text-xs border border-dark-border">${anime.type}</span>
                                        <span class="bg-dark-card px-3 py-1 rounded-full text-xs border border-dark-border">${anime.status}</span>
                                        ${anime.genres?.map(g => `<span class="text-accent text-xs">${g}</span>`).join('')}
                                    </div>
                                    <p class="text-gray-400 text-sm leading-relaxed mb-4">${anime.description || 'No description available.'}</p>
                                    
                                    <div class="flex gap-3">
                                        <button onclick="app.render.watchEpisode('${anime.id}', 1)" class="bg-accent hover:bg-accent/80 text-black font-bold py-2 px-6 rounded-full transition-colors flex items-center gap-2">
                                            <i class="fas fa-play"></i> Watch Now
                                        </button>
                                        <button onclick="window.history.back()" class="bg-dark-card hover:bg-dark-border border border-dark-border text-white py-2 px-6 rounded-full transition-colors">
                                            <i class="fas fa-arrow-left"></i> Back
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Episodes -->
                            <h3 class="text-xl font-bold mb-4">Episodes</h3>
                            <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-10 gap-2 mb-10">
                                ${episodeList.map(ep => `
                                    <button onclick="app.render.watchEpisode('${anime.id}', ${ep})" 
                                            class="bg-dark-card hover:bg-dark-border border border-dark-border hover:border-accent text-center py-3 rounded transition-colors">
                                        <span class="text-sm font-medium">${ep}</span>
                                    </button>
                                `).join('')}
                            </div>
                        </div>
                    `;
                },

                async watchEpisode(animeId, episode) {
                    const container = document.getElementById('app-container');
                    
                    // Find anime details again to get title
                    const data = await api.search(animeId, 1, 1);
                    const anime = data?.results[0];
                    const title = anime?.title?.english || anime?.title?.romaji || 'Anime';

                    container.innerHTML = `
                        <div class="fade-in">
                            <button onclick="window.location.hash='anime-${animeId}'" class="text-accent hover:underline mb-4 inline-block">
                                <i class="fas fa-arrow-left"></i> Back to ${title}
                            </button>
                            
                            <div class="relative w-full aspect-video bg-black rounded-lg overflow-hidden shadow-2xl shadow-black/50 mb-4">
                                <iframe src="https://vidsrc.cc/embed/anime?id=${animeId}&ep=${episode}" 
                                        frameborder="0" 
                                        allowfullscreen
                                        class="w-full h-full"
                                        title="Video Player"></iframe>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <h2 class="text-2xl font-bold">${title} - Episode ${episode}</h2>
                                <div class="flex gap-2">
                                    <button onclick="app.render.watchEpisode('${animeId}', ${episode - 1})" 
                                        ${episode === 1 ? 'disabled' : ''}
                                        class="bg-dark-card hover:bg-dark-border border border-dark-border p-2 rounded disabled:opacity-50">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button onclick="app.render.watchEpisode('${animeId}', ${parseInt(episode) + 1})" 
                                        class="bg-dark-card hover:bg-dark-border border border-dark-border p-2 rounded">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                },

                history() {
                    const container = document.getElementById('app-container');
                    const history = app.state.watchHistory;

                    container.innerHTML = `
                        <div class="space-y-6 fade-in">
                            <h2 class="text-2xl font-bold">Watch History</h2>
                            ${history.length === 0 
                                ? `<div class="text-center text-gray-500 py-10">No history yet.</div>` 
                                : `<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                    ${history.map(item => `
                                        <div class="group relative bg-dark-card rounded-lg overflow-hidden hover:shadow-lg hover:shadow-accent/20 transition-all cursor-pointer"
                                             onclick="app.render.animeDetail('${item.id}')">
                                            <div class="aspect-[2/3] overflow-hidden">
                                                <img src="${item.image}" alt="${item.title}" 
                                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                     loading="lazy">
                                            </div>
                                            <div class="p-3">
                                                <h3 class="text-sm font-medium line-clamp-2 group-hover:text-accent transition-colors">${item.title}</h3>
                                            </div>
                                        </div>
                                    `).join('')}
                                  </div>`
                            }
                        </div>
                    `;
                }
            }
        };

        // Initialize App
        document.addEventListener('DOMContentLoaded', () => {
            app.init();
        });
    </script>
</body>
</html>
