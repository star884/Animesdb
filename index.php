<?php
/**
 * TRIANIME - Single File Anime Streaming Application
 * 
 * @version 2.0.0
 * @description Fully responsive SPA using Tenrai.net API, Tailwind CSS, and Vanilla JS.
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

        /* Skeleton Loading */
        .skeleton {
            background: linear-gradient(90deg, #1e1e1e 25%, #2a2a2a 50%, #1e1e1e 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Toast Notifications */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 20px;
            background: rgba(18, 18, 18, 0.95);
            border: 1px solid rgba(255, 186, 222, 0.3);
            border-radius: 8px;
            color: #e0e0e0;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
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
                        <button onclick="app.router.navigate('search')" class="nav-btn hover:text-accent px-3 py-2 rounded-md text-sm font-medium transition-colors" data-route="search">Search</button>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="-mr-2 flex md:hidden">
                    <button onclick="app.ui.toggleMobileMenu()" type="button" class="bg-dark-card inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="md:hidden hidden bg-dark-card border-t border-dark-border" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <button onclick="app.router.navigate('home')" class="text-gray-300 hover:text-accent block px-3 py-2 rounded-md text-base font-medium w-full text-left">Home</button>
                <button onclick="app.router.navigate('browse')" class="text-gray-300 hover:text-accent block px-3 py-2 rounded-md text-base font-medium w-full text-left">Browse</button>
                <button onclick="app.router.navigate('search')" class="text-gray-300 hover:text-accent block px-3 py-2 rounded-md text-base font-medium w-full text-left">Search</button>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="flex-grow pt-16 relative">
        
        <!-- Loader Overlay -->
        <div id="global-loader" class="hidden fixed inset-0 z-40 bg-dark-bg/80 flex items-center justify-center">
            <div class="loader"></div>
        </div>

        <!-- View: Home -->
        <section id="view-home" class="view-section fade-in">
            <!-- Hero Slider -->
            <div id="hero-slider" class="relative h-[500px] md:h-[600px] w-full overflow-hidden group">
                <div id="hero-content" class="absolute inset-0 transition-opacity duration-1000 bg-gradient-to-r from-dark-bg/90 via-dark-bg/50 to-dark-bg/20">
                    <!-- Content injected via JS -->
                </div>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-dark-bg via-dark-bg/80 to-transparent p-8 md:p-16">
                    <div class="max-w-7xl mx-auto">
                        <h1 id="hero-title" class="text-4xl md:text-6xl font-bold text-white mb-4 drop-shadow-lg">Loading...</h1>
                        <p id="hero-synopsis" class="text-gray-300 max-w-2xl mb-6 line-clamp-3 drop-shadow-md"></p>
                        <div class="flex flex-wrap gap-4">
                            <button id="hero-watch-btn" class="bg-accent text-black font-bold py-3 px-8 rounded-full hover:bg-white transition-all transform hover:scale-105 shadow-lg shadow-accent/20">
                                <i class="fas fa-play mr-2"></i> Watch Now
                            </button>
                            <button id="hero-details-btn" class="bg-gray-800/80 backdrop-blur text-white font-bold py-3 px-8 rounded-full hover:bg-gray-700 transition-all border border-gray-600">
                                <i class="fas fa-info-circle mr-2"></i> More Info
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Slider Indicators -->
                <div class="absolute bottom-4 right-8 flex space-x-2 z-10" id="hero-indicators"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <!-- Main Grid: Top Rated -->
                    <div class="lg:col-span-2">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold border-l-4 border-accent pl-3">Top Rated Anime</h2>
                            <button onclick="app.router.navigate('browse', {filter: 'top'})" class="text-sm text-accent hover:text-white transition-colors">View All <i class="fas fa-arrow-right ml-1"></i></button>
                        </div>
                        <div id="home-grid" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <!-- Cards injected here -->
                        </div>
                    </div>

                    <!-- Sidebar: Recently Updated & Genres -->
                    <div class="space-y-8">
                        <!-- Genres -->
                        <div class="bg-dark-card p-4 rounded-xl border border-dark-border">
                            <h3 class="font-bold mb-4 text-lg">Popular Genres</h3>
                            <div id="genre-tags" class="flex flex-wrap gap-2">
                                <!-- Tags injected here -->
                            </div>
                        </div>

                        <!-- Recently Updated -->
                        <div>
                            <h2 class="text-2xl font-bold border-l-4 border-accent pl-3 mb-6">Recently Updated</h2>
                            <div id="upcoming-list" class="space-y-4">
                                <!-- List items injected here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- View: Browse -->
        <section id="view-browse" class="view-section hidden fade-in pt-8 pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row gap-8">
                    <!-- Filter Sidebar -->
                    <aside class="w-full md:w-1/4 space-y-6">
                        <div class="bg-dark-card p-5 rounded-xl border border-dark-border sticky top-24">
                            <h3 class="font-bold text-lg mb-4">Filters</h3>
                            
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-400 mb-2">Sort By</label>
                                <select id="browse-sort" onchange="app.browse.updateFilters()" class="w-full bg-dark-bg border border-dark-border rounded-lg p-2 text-sm focus:ring-accent focus:border-accent outline-none">
                                    <option value="top">Top Rated</option>
                                    <option value="popular">Most Popular</option>
                                    <option value="recent">Recently Updated</option>
                                    <option value="movies">Movies</option>
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-400 mb-2">Genre</label>
                                <div id="browse-genre-filters" class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
                                    <!-- Checkboxes injected here -->
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-400 mb-2">Type</label>
                                <select id="browse-type" onchange="app.browse.updateFilters()" class="w-full bg-dark-bg border border-dark-border rounded-lg p-2 text-sm focus:ring-accent focus:border-accent outline-none">
                                    <option value="">All Types</option>
                                    <option value="TV">TV</option>
                                    <option value="Movie">Movie</option>
                                    <option value="OVA">OVA</option>
                                    <option value="Special">Special</option>
                                </select>
                            </div>
                            
                            <button onclick="app.browse.resetFilters()" class="w-full py-2 border border-gray-600 rounded-lg hover:bg-gray-800 text-sm transition-colors">Reset Filters</button>
                        </div>
                    </aside>

                    <!-- Grid -->
                    <div class="w-full md:w-3/4">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold" id="browse-title">Browse Library</h2>
                            <span class="text-sm text-gray-500" id="browse-count"></span>
                        </div>
                        
                        <div id="browse-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
                            <!-- Grid content -->
                        </div>

                        <!-- Pagination -->
                        <div class="mt-12 flex justify-center space-x-4" id="pagination">
                            <button onclick="app.browse.changePage(-1)" id="btn-prev" class="px-6 py-2 border border-gray-700 rounded-lg hover:bg-gray-800 disabled:opacity-50 transition-colors">Prev</button>
                            <span id="page-info" class="px-4 py-2 text-sm text-gray-400">Page 1</span>
                            <button onclick="app.browse.changePage(1)" id="btn-next" class="px-6 py-2 border border-gray-700 rounded-lg hover:bg-gray-800 disabled:opacity-50 transition-colors">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- View: Watch -->
        <section id="view-watch" class="view-section hidden fade-in pt-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Player & Details -->
                    <div class="lg:col-span-2">
                        <!-- Video Player -->
                        <div class="relative w-full bg-black rounded-xl overflow-hidden shadow-2xl shadow-black/50 aspect-video mb-6 group">
                            <div id="video-container" class="w-full h-full flex items-center justify-center">
                                <div class="loader"></div>
                            </div>
                        </div>

                        <!-- Anime Info -->
                        <div class="bg-dark-card p-6 rounded-xl border border-dark-border mb-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h1 id="watch-title" class="text-3xl font-bold text-white mb-2">Anime Title</h1>
                                    <div class="flex items-center space-x-4 text-sm text-gray-400">
                                        <span class="text-accent"><i class="fas fa-star mr-1"></i> <span id="watch-score">0.0</span></span>
                                        <span><i class="fas fa-tv mr-1"></i> <span id="watch-type">Unknown</span></span>
                                        <span><i class="fas fa-calendar mr-1"></i> <span id="watch-year">2024</span></span>
                                        <span><i class="fas fa-play-circle mr-1"></i> <span id="watch-episodes">0</span> eps</span>
                                    </div>
                                </div>
                                <button id="fav-btn" class="text-gray-400 hover:text-accent transition-colors text-2xl" onclick="app.watch.toggleFavorite()">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                            <p id="watch-synopsis" class="text-gray-300 leading-relaxed mb-4"></p>
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 mb-2">Genres:</p>
                                <div class="flex flex-wrap gap-2" id="watch-genres"></div>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-2">Alternative Titles:</p>
                                <p id="watch-alt-titles" class="text-sm text-gray-300"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Episodes Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-dark-card rounded-xl border border-dark-border h-[600px] flex flex-col sticky top-24">
                            <div class="p-4 border-b border-dark-border">
                                <h3 class="font-bold text-lg">Episodes</h3>
                                <div class="mt-2 flex gap-2">
                                    <span id="ep-count" class="text-xs bg-gray-800 px-2 py-1 rounded text-gray-400">0 eps</span>
                                </div>
                            </div>
                            <div id="episode-list" class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                                <!-- Episode items -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- View: Search -->
        <section id="view-search" class="view-section hidden fade-in pt-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-center mb-8">
                    <div class="relative w-full max-w-2xl">
                        <input type="text" id="search-input" placeholder="Search anime, characters, studios..." 
                            class="w-full bg-dark-card border border-dark-border rounded-full py-3 px-6 pl-12 focus:ring-2 focus:ring-accent focus:border-transparent outline-none transition-all shadow-lg">
                        <i class="fas fa-search absolute left-4 top-3.5 text-gray-500"></i>
                    </div>
                </div>
                
                <div id="search-results" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    <!-- Results -->
                </div>
                <div id="search-loading" class="hidden text-center py-20">
                    <div class="loader mx-auto mb-2"></div>
                    <p class="text-gray-500">Searching...</p>
                </div>
                <div id="search-empty" class="hidden text-center py-20">
                    <i class="fas fa-ghost text-4xl text-gray-700 mb-4"></i>
                    <p class="text-gray-500">No anime found.</p>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-dark-card border-t border-dark-border py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-gray-500 text-sm">&copy; 2024 TRIANIME. Data provided by Tenrai.net API.</p>
            <p class="text-gray-600 text-xs mt-2">A fan-made streaming application for educational purposes</p>
            <div class="flex justify-center space-x-4 mt-4">
                <a href="#" class="text-gray-600 hover:text-accent transition-colors"><i class="fab fa-discord"></i></a>
                <a href="#" class="text-gray-600 hover:text-accent transition-colors"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-gray-600 hover:text-accent transition-colors"><i class="fab fa-github"></i></a>
            </div>
        </div>
    </footer>

    <!-- Application Logic -->
    <script>
        /**
         * Configuration & Constants
         */
        const CONFIG = {
            apiBase: 'https://api.tenrai.net',
            delayBetweenRequests: 300,
            cacheExpiry: 3600000, // 1 hour in ms
        };

        const GENRES = [
            "Action", "Adventure", "Comedy", "Drama", "Fantasy", "Horror", "Mecha", 
            "Music", "Mystery", "Psychological", "Romance", "Sci-Fi", "Slice of Life", 
            "Sports", "Supernatural", "Thriller", "Isekai", "Josei", "Shonen", "Shojo"
        ];

        /**
         * Cache Management
         */
        const cache = {
            store: new Map(),
            
            set(key, value, ttl = CONFIG.cacheExpiry) {
                this.store.set(key, {
                    value,
                    expiry: Date.now() + ttl
                });
            },
            
            get(key) {
                const item = this.store.get(key);
                if (!item) return null;
                if (Date.now() > item.expiry) {
                    this.store.delete(key);
                    return null;
                }
                return item.value;
            },
            
            clear() {
                this.store.clear();
            }
        };

        /**
         * State Management
         */
        const state = {
            currentRoute: 'home',
            heroData: [],
            heroIndex: 0,
            favorites: JSON.parse(localStorage.getItem('trianime_favorites') || '[]'),
            browse: {
                page: 1,
                maxPage: 10,
                filters: {
                    type: 'top',
                    genres: [],
                    animeType: ''
                }
            },
            watch: {
                animeId: null,
                anime: null,
                episodes: [],
                currentEpisodeIndex: 0,
                currentServer: 'vide'
            },
            search: {
                timeout: null,
                query: ''
            },
            timer: null
        };

        /**
         * LocalStorage Management
         */
        const storage = {
            saveFavorites() {
                localStorage.setItem('trianime_favorites', JSON.stringify(state.favorites));
            },
            
            isFavorite(id) {
                return state.favorites.includes(id);
            },
            
            addFavorite(id) {
                if (!this.isFavorite(id)) {
                    state.favorites.push(id);
                    this.saveFavorites();
                }
            },
            
            removeFavorite(id) {
                state.favorites = state.favorites.filter(fav => fav !== id);
                this.saveFavorites();
            }
        };

        /**
         * Notification System
         */
        const notify = {
            show(message, type = 'info', duration = 3000) {
                const toast = document.createElement('div');
                toast.className = `toast bg-dark-card border`;
                
                if (type === 'success') toast.classList.add('border-green-500', 'text-green-300');
                else if (type === 'error') toast.classList.add('border-red-500', 'text-red-300');
                else toast.classList.add('border-accent/30', 'text-gray-300');
                
                toast.innerHTML = `
                    <div class="flex items-center space-x-3">
                        ${type === 'success' ? '<i class="fas fa-check-circle"></i>' : type === 'error' ? '<i class="fas fa-exclamation-circle"></i>' : '<i class="fas fa-info-circle"></i>'}
                        <span>${message}</span>
                    </div>
                `;
                
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }, duration);
            }
        };

        /**
         * API Handler with Rate Limiting & Caching
         */
        const api = {
            async request(endpoint, params = {}) {
                const cacheKey = `${endpoint}:${JSON.stringify(params)}`;
                const cached = cache.get(cacheKey);
                if (cached) return cached;

                try {
                    const url = new URL(`${CONFIG.apiBase}${endpoint}`);
                    Object.keys(params).forEach(key => {
                        if (params[key]) url.searchParams.append(key, params[key]);
                    });
                    
                    const res = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json',
                        }
                    });
                    
                    if (!res.ok) throw new Error(`API Error: ${res.status}`);
                    const data = await res.json();
                    cache.set(cacheKey, data);
                    return data;
                } catch (err) {
                    console.error('API Request Failed:', err);
                    throw err;
                }
            },

            async sequentialFetch(requests) {
                const results = [];
                for (let i = 0; i < requests.length; i++) {
                    if (i > 0) {
                        await new Promise(r => setTimeout(r, CONFIG.delayBetweenRequests));
                    }
                    try {
                        results.push(await requests[i]());
                    } catch (err) {
                        console.error(`Request ${i} failed`, err);
                        results.push(null);
                    }
                }
                return results;
            },

            async getTrending() {
                return this.request('/anime/trending');
            },
            
            async getTopRated() {
                return this.request('/anime/top-rated', { page: 1, perPage: 25 });
            },

            async getRecentlyUpdated() {
                return this.request('/anime/recent', { page: 1, perPage: 10 });
            },

            async search(query, page = 1) {
                return this.request('/anime/search', { query, page, perPage: 25 });
            },

            async getAnimeInfo(id) {
                return this.request(`/anime/${id}`);
            },

            async getAnimeEpisodes(id, page = 1) {
                return this.request(`/anime/${id}/episodes`, { page });
            },

            async getEpisodeStream(animeId, episodeId) {
                return this.request(`/anime/${animeId}/episode/${episodeId}/stream`);
            }
        };

        /**
         * UI Manager
         */
        const ui = {
            toggleMobileMenu() {
                const menu = document.getElementById('mobile-menu');
                menu.classList.toggle('hidden');
            },

            showLoader(show) {
                const loader = document.getElementById('global-loader');
                if (show) loader.classList.remove('hidden');
                else loader.classList.add('hidden');
            },

            getAnimeImageUrl(imgUrl) {
                if(!imgUrl) return 'https://via.placeholder.com/300x450?text=No+Image';
                if(imgUrl.startsWith('http')) return imgUrl;
                return `${CONFIG.apiBase}${imgUrl}`;
            },

            createAnimeCard(anime) {
                const imgSrc = this.getAnimeImageUrl(anime.image || anime.poster);
                const isFav = storage.isFavorite(anime.id);
                return `
                    <div class="group relative bg-dark-card rounded-lg overflow-hidden shadow-lg hover:shadow-accent/20 transition-all duration-300 hover:-translate-y-1 cursor-pointer">
                        <div class="aspect-[2/3] overflow-hidden">
                            <img src="${imgSrc}" alt="${anime.title?.english || anime.title?.romaji || anime.title || 'Anime'}" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3">
                                <span class="text-xs font-bold bg-accent text-black px-2 py-1 rounded-full">${anime.type || 'TV'}</span>
                            </div>
                        </div>
                        <div class="p-3">
                            <h3 class="text-sm font-semibold text-gray-100 truncate group-hover:text-accent transition-colors cursor-pointer" onclick="event.stopPropagation(); app.router.navigate('watch', {id: '${anime.id}'})">${anime.title?.english || anime.title?.romaji || anime.title || 'Unknown'}</h3>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-xs text-gray-500">${anime.year || anime.releaseDate ? new Date(anime.releaseDate || anime.year).getFullYear() : 'N/A'}</span>
                                <div class="flex space-x-2">
                                    <span class="text-xs text-accent"><i class="fas fa-star mr-1"></i>${anime.rating ? (anime.rating / 10).toFixed(1) : '?'}</span>
                                    <button onclick="event.stopPropagation(); app.ui.toggleFavoriteCard('${anime.id}', this)" class="text-gray-400 hover:text-accent transition-colors">
                                        <i class="fas fa-heart" ${isFav ? "style='color: #ffbade'" : ''}></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div onclick="app.router.navigate('watch', {id: '${anime.id}'})" class="absolute inset-0 cursor-pointer"></div>
                    </div>
                `;
            },

            toggleFavoriteCard(id, element) {
                if (storage.isFavorite(id)) {
                    storage.removeFavorite(id);
                    element.querySelector('i').style.color = '';
                    notify.show('Removed from favorites', 'info');
                } else {
                    storage.addFavorite(id);
                    element.querySelector('i').style.color = '#ffbade';
                    notify.show('Added to favorites', 'success');
                }
            },

            createEpisodeItem(ep, index) {
                return `
                    <div onclick="app.watch.selectEpisode(${index})" class="episode-item cursor-pointer p-3 rounded-lg hover:bg-gray-800 flex justify-between items-center group transition-colors border border-transparent hover:border-gray-700" data-index="${index}" data-ep-id="${ep.id}">
                        <div class="flex items-center flex-1">
                            <span class="text-xs font-mono text-gray-500 w-12 flex-shrink-0">EP ${ep.number}</span>
                            <span class="text-sm text-gray-300 truncate ml-2">${ep.title || `Episode ${ep.number}`}</span>
                        </div>
                        <i class="fas fa-play text-xs text-gray-600 group-hover:text-accent flex-shrink-0"></i>
                    </div>
                `;
            }
        };

        /**
         * Router
         */
        const router = {
            navigate(view, params = {}) {
                state.currentRoute = view;
                app.ui.toggleMobileMenu();
                
                // Hide all views
                document.querySelectorAll('.view-section').forEach(el => el.classList.add('hidden'));
                
                // Show target view
                const target = document.getElementById(`view-${view}`);
                if(target) {
                    target.classList.remove('hidden');
                    window.scrollTo(0, 0);
                }

                // Update Nav State
                document.querySelectorAll('.nav-btn').forEach(btn => {
                    btn.classList.remove('text-accent');
                    if(btn.dataset.route === view) btn.classList.add('text-accent');
                });

                // Trigger view logic
                switch(view) {
                    case 'home': app.home.init(); break;
                    case 'browse': app.browse.init(params); break;
                    case 'watch': app.watch.init(params); break;
                    case 'search': 
                        if(params.query) {
                            document.getElementById('search-input').value = params.query;
                            app.search.performSearch(params.query);
                        }
                        break;
                }
            }
        };

        /**
         * Feature: Home
         */
        const home = {
            async init() {
                app.ui.showLoader(true);
                
                const [trendingRes, topRes, recentRes] = await api.sequentialFetch([
                    () => api.getTrending(),
                    () => api.getTopRated(),
                    () => api.getRecentlyUpdated()
                ]);

                app.ui.showLoader(false);

                // Handle Hero Slider
                const heroData = trendingRes?.data || trendingRes?.results || [];
                state.heroData = heroData.slice(0, 10);
                state.heroIndex = 0;
                home.renderHero();
                home.startSlider();

                // Render Top Rated Grid
                const grid = document.getElementById('home-grid');
                const topAnime = topRes?.data || topRes?.results || [];
                if (topAnime.length) {
                    grid.innerHTML = topAnime.slice(0, 12).map(a => ui.createAnimeCard(a)).join('');
                } else {
                    grid.innerHTML = '<p class="col-span-3 text-center text-gray-500">Failed to load anime.</p>';
                }

                // Render Recently Updated
                const upcomingList = document.getElementById('upcoming-list');
                const recent = recentRes?.data || recentRes?.results || [];
                if (recent.length) {
                    upcomingList.innerHTML = recent.slice(0, 5).map(a => `
                        <div class="flex items-center space-x-3 cursor-pointer hover:bg-gray-800 p-2 rounded transition-colors" onclick="app.router.navigate('watch', {id: '${a.id}'})">
                            <img src="${ui.getAnimeImageUrl(a.image || a.poster)}" class="w-12 h-16 object-cover rounded" loading="lazy">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-200 line-clamp-1">${a.title?.english || a.title?.romaji || a.title}</h4>
                                <span class="text-xs text-gray-500">${a.type || 'TV'}</span>
                            </div>
                        </div>
                    `).join('');
                }

                // Render Genres
                const genreContainer = document.getElementById('genre-tags');
                genreContainer.innerHTML = GENRES.map(g => `
                    <button onclick="app.router.navigate('browse', {genre: '${g}'})" class="text-xs bg-dark-bg border border-gray-700 hover:border-accent hover:text-accent px-3 py-1 rounded-full transition-colors">${g}</button>
                `).join('');
            },

            renderHero() {
                if (!state.heroData.length) return;
                const anime = state.heroData[state.heroIndex];
                
                document.getElementById('hero-title').innerText = anime.title?.english || anime.title?.romaji || anime.title || 'Unknown';
                document.getElementById('hero-synopsis').innerText = anime.description || anime.synopsis || 'No synopsis available.';
                document.getElementById('hero-watch-btn').onclick = () => app.router.navigate('watch', {id: anime.id});
                document.getElementById('hero-details-btn').onclick = () => app.router.navigate('watch', {id: anime.id});
                
                const container = document.getElementById('hero-content');
                const bgImage = ui.getAnimeImageUrl(anime.image || anime.poster || anime.cover);
                container.style.backgroundImage = `linear-gradient(to right, rgba(10,10,10,0.9) 0%, rgba(10,10,10,0.4) 50%, rgba(10,10,10,0.1) 100%), url(${bgImage})`;
                container.style.backgroundSize = 'cover';
                container.style.backgroundPosition = 'center';
                
                const indicators = document.getElementById('hero-indicators');
                indicators.innerHTML = state.heroData.map((_, i) => `
                    <button onclick="app.home.setHero(${i})" class="w-2 h-2 rounded-full transition-colors ${i === state.heroIndex ? 'bg-accent' : 'bg-gray-600'} hover:bg-accent"></button>
                `).join('');
            },

            setHero(index) {
                state.heroIndex = index;
                home.renderHero();
                home.resetTimer();
            },

            startSlider() {
                home.resetTimer();
            },

            resetTimer() {
                if (state.timer) clearInterval(state.timer);
                state.timer = setInterval(() => {
                    state.heroIndex = (state.heroIndex + 1) % state.heroData.length;
                    home.renderHero();
                }, 6000);
            }
        };

        /**
         * Feature: Browse
         */
        const browse = {
            async init(params = {}) {
                const genreContainer = document.getElementById('browse-genre-filters');
                
                if(params.genre) {
                    if(!state.browse.filters.genres.includes(params.genre)) {
                        state.browse.filters.genres.push(params.genre);
                    }
                }

                genreContainer.innerHTML = GENRES.map(g => `
                    <label class="flex items-center space-x-2 cursor-pointer hover:text-accent transition-colors">
                        <input type="checkbox" value="${g}" 
                            ${state.browse.filters.genres.includes(g) ? 'checked' : ''} 
                            onchange="app.browse.toggleGenre('${g}')"
                            class="form-checkbox h-4 w-4 text-accent rounded border-gray-600 bg-dark-bg focus:ring-offset-dark-bg">
                        <span class="text-sm">${g}</span>
                    </label>
                `).join('');

                await this.fetchContent();
            },

            toggleGenre(genre) {
                if(state.browse.filters.genres.includes(genre)) {
                    state.browse.filters.genres = state.browse.filters.genres.filter(g => g !== genre);
                } else {
                    state.browse.filters.genres.push(genre);
                }
                state.browse.page = 1;
                this.fetchContent();
            },

            updateFilters() {
                state.browse.filters.type = document.getElementById('browse-sort').value;
                state.browse.filters.animeType = document.getElementById('browse-type').value;
                state.browse.page = 1;
                this.fetchContent();
            },

            resetFilters() {
                state.browse.filters = { type: 'top', genres: [], animeType: '' };
                state.browse.page = 1;
                document.getElementById('browse-sort').value = 'top';
                document.getElementById('browse-type').value = '';
                this.init();
            },

            changePage(offset) {
                state.browse.page += offset;
                if (state.browse.page < 1) state.browse.page = 1;
                this.fetchContent();
            },

            async fetchContent() {
                app.ui.showLoader(true);
                
                let data = null;
                const type = state.browse.filters.type;
                const genres = state.browse.filters.genres;
                const animeType = state.browse.filters.animeType;

                try {
                    const params = { page: state.browse.page, perPage: 20 };
                    if (animeType) params.type = animeType;
                    if (genres.length > 0) params.genres = genres.join(',');

                    if (type === 'movies') {
                        params.type = 'Movie';
                        data = await api.request('/anime/search', params);
                    } else if (type === 'popular') {
                        data = await api.request('/anime/popular', params);
                    } else if (type === 'recent') {
                        data = await api.request('/anime/recent', params);
                    } else {
                        data = await api.request('/anime/top-rated', params);
                    }
                } catch (e) {
                    console.error(e);
                    notify.show('Failed to load anime', 'error');
                    data = { data: [] };
                }

                app.ui.showLoader(false);
                
                const grid = document.getElementById('browse-grid');
                const count = document.getElementById('browse-count');
                const pageInfo = document.getElementById('page-info');
                const items = data?.data || data?.results || [];

                count.innerText = `${items.length} results`;
                pageInfo.innerText = `Page ${state.browse.page}`;
                
                grid.innerHTML = items.length 
                    ? items.map(a => ui.createAnimeCard(a)).join('') 
                    : '<div class="col-span-4 text-center text-gray-500 py-10">No anime found matching criteria.</div>';

                document.getElementById('btn-prev').disabled = state.browse.page <= 1;
                document.getElementById('btn-next').disabled = items.length < 20;
            }
        };

        /**
         * Feature: Watch
         */
        const watch = {
            async init(params) {
                if(!params.id) return;
                
                app.ui.showLoader(true);
                state.watch.animeId = params.id;
                
                try {
                    const [data, episodesRes] = await Promise.all([
                        api.getAnimeInfo(params.id),
                        api.getAnimeEpisodes(params.id)
                    ]);
                    
                    state.watch.anime = data;
                    this.renderPage(data, episodesRes);
                } catch (e) {
                    console.error(e);
                    notify.show('Error loading anime', 'error');
                    document.getElementById('watch-title').innerText = "Error loading anime";
                }
                app.ui.showLoader(false);
            },

            renderPage(data, episodesRes) {
                const anime = data.data || data;
                
                document.getElementById('watch-title').innerText = anime.title?.english || anime.title?.romaji || anime.title;
                document.getElementById('watch-synopsis').innerText = anime.description || anime.synopsis || "No description available.";
                document.getElementById('watch-score').innerText = anime.rating ? (anime.rating/10).toFixed(1) : 'N/A';
                document.getElementById('watch-type').innerText = anime.type || 'Unknown';
                document.getElementById('watch-year').innerText = anime.year || (anime.releaseDate ? new Date(anime.releaseDate).getFullYear() : 'N/A');
                document.getElementById('watch-episodes').innerText = anime.totalEpisodes || anime.episodes?.length || '0';
                
                document.getElementById('watch-alt-titles').innerText = (anime.title?.alternatives || []).join(' | ') || 'N/A';
                
                const genres = anime.genres || [];
                document.getElementById('watch-genres').innerHTML = genres.map(g => 
                    `<span class="text-xs bg-gray-800 px-2 py-1 rounded text-gray-300">${g}</span>`
                ).join('');

                // Update favorite button
                const favBtn = document.getElementById('fav-btn');
                if (storage.isFavorite(anime.id)) {
                    favBtn.querySelector('i').className = 'fas fa-heart';
                    favBtn.querySelector('i').style.color = '#ffbade';
                } else {
                    favBtn.querySelector('i').className = 'far fa-heart';
                    favBtn.querySelector('i').style.color = '';
                }

                // Episodes
                const epList = document.getElementById('episode-list');
                state.watch.episodes = episodesRes?.data || episodesRes?.results || anime.episodes || [];
                document.getElementById('ep-count').innerText = `${state.watch.episodes.length} Episodes`;
                
                epList.innerHTML = state.watch.episodes.map((ep, idx) => ui.createEpisodeItem(ep, idx)).join('');

                if(state.watch.episodes.length > 0) {
                    this.selectEpisode(0);
                }
            },

            async selectEpisode(index) {
                document.querySelectorAll('.episode-item').forEach(el => {
                    el.classList.remove('bg-gray-800', 'border-accent');
                });
                
                const selectedEl = document.querySelector(`.episode-item[data-index="${index}"]`);
                if (selectedEl) {
                    selectedEl.classList.add('bg-gray-800', 'border-accent');
                }
                
                state.watch.currentEpisodeIndex = index;
                const ep = state.watch.episodes[index];
                
                try {
                    const container = document.getElementById('video-container');
                    container.innerHTML = '<div class="loader"></div>';
                    
                    const streamRes = await api.getEpisodeStream(state.watch.animeId, ep.id);
                    const stream = streamRes.data || streamRes;
                    
                    if (stream && stream.url) {
                        container.innerHTML = `<iframe class="w-full h-full" src="${stream.url}" frameborder="0" allowfullscreen></iframe>`;
                    } else if (stream && stream.sources && stream.sources[0]) {
                        const videoSource = stream.sources.find(s => s.quality === 'auto' || s.quality === '1080p' || s.quality === '720p') || stream.sources[0];
                        container.innerHTML = `<iframe class="w-full h-full" src="${videoSource.url}" frameborder="0" allowfullscreen></iframe>`;
                    } else {
                        container.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-gray-500">Stream not available</p></div>';
                    }
                } catch(e) {
                    console.error("Failed to load episode", e);
                    notify.show('Failed to load episode stream', 'error');
                    const container = document.getElementById('video-container');
                    container.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-gray-500">Error loading stream</p></div>';
                }
            },

            toggleFavorite() {
                const animeId = state.watch.animeId;
                if (storage.isFavorite(animeId)) {
                    storage.removeFavorite(animeId);
                    notify.show('Removed from favorites', 'info');
                    document.querySelector('#fav-btn i').className = 'far fa-heart';
                    document.querySelector('#fav-btn i').style.color = '';
                } else {
                    storage.addFavorite(animeId);
                    notify.show('Added to favorites', 'success');
                    document.querySelector('#fav-btn i').className = 'fas fa-heart';
                    document.querySelector('#fav-btn i').style.color = '#ffbade';
                }
            }
        };

        /**
         * Feature: Search
         */
        const search = {
            init() {
                const input = document.getElementById('search-input');
                input.addEventListener('input', (e) => {
                    clearTimeout(state.search.timeout);
                    const query = e.target.value.trim();
                    if(query.length > 2) {
                        state.search.timeout = setTimeout(() => this.performSearch(query), 500);
                    } else if (query.length === 0) {
                        document.getElementById('search-results').innerHTML = '';
                        document.getElementById('search-empty').classList.add('hidden');
                        document.getElementById('search-loading').classList.add('hidden');
                    }
                });
            },

            async performSearch(query) {
                if(!query) return;
                state.search.query = query;
                
                document.getElementById('search-loading').classList.remove('hidden');
                document.getElementById('search-results').innerHTML = '';
                document.getElementById('search-empty').classList.add('hidden');

                try {
                    const data = await api.search(query);
                    document.getElementById('search-loading').classList.add('hidden');
                    
                    const results = data?.data || data?.results || [];
                    if(results.length > 0) {
                        document.getElementById('search-results').innerHTML = results.map(a => ui.createAnimeCard(a)).join('');
                    } else {
                        document.getElementById('search-empty').classList.remove('hidden');
                    }
                } catch (e) {
                    console.error(e);
                    document.getElementById('search-loading').classList.add('hidden');
                    notify.show('Search failed', 'error');
                }
            }
        };

        /**
         * Initialization
         */
        const app = {
            init() {
                router.navigate('home');
                search.init();
            }
        };

        // Helper Refs
        app.ui = ui;
        app.router = router;
        app.home = home;
        app.browse = browse;
        app.watch = watch;
        app.search = search;

        // Start App
        document.addEventListener('DOMContentLoaded', () => app.init());

    </script>
</body>
</html>
