<?php
/**
 * TRIANIME - Single File Anime Streaming Application
 * 
 * @version 1.0.0
 * @description Fully responsive SPA using Consumet API, Tailwind CSS, and Vanilla JS.
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
        <div id="global-loader" class="hidden absolute inset-0 z-40 bg-dark-bg/80 flex items-center justify-center">
            <div class="loader"></div>
        </div>

        <!-- View: Home -->
        <section id="view-home" class="view-section fade-in">
            <!-- Hero Slider -->
            <div id="hero-slider" class="relative h-[500px] md:h-[600px] w-full overflow-hidden group">
                <div id="hero-content" class="absolute inset-0 transition-opacity duration-1000">
                    <!-- Content injected via JS -->
                </div>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-dark-bg via-dark-bg/80 to-transparent p-8 md:p-16">
                    <div class="max-w-7xl mx-auto">
                        <h1 id="hero-title" class="text-4xl md:text-6xl font-bold text-white mb-4 drop-shadow-lg">Loading...</h1>
                        <p id="hero-synopsis" class="text-gray-300 max-w-2xl mb-6 line-clamp-3 drop-shadow-md"></p>
                        <div class="flex space-x-4">
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
                <div class="absolute bottom-4 right-8 flex space-x-2" id="hero-indicators"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <!-- Main Grid: Latest Airing -->
                    <div class="lg:col-span-2">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold border-l-4 border-accent pl-3">Latest Airing</h2>
                            <button onclick="app.router.navigate('browse', {filter: 'airing'})" class="text-sm text-accent hover:text-white transition-colors">View All <i class="fas fa-arrow-right ml-1"></i></button>
                        </div>
                        <div id="home-grid" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <!-- Cards injected here -->
                        </div>
                    </div>

                    <!-- Sidebar: Upcoming & Genres -->
                    <div class="space-y-8">
                        <!-- Genres -->
                        <div class="bg-dark-card p-4 rounded-xl border border-dark-border">
                            <h3 class="font-bold mb-4 text-lg">Genres</h3>
                            <div id="genre-tags" class="flex flex-wrap gap-2">
                                <!-- Tags injected here -->
                            </div>
                        </div>

                        <!-- Upcoming -->
                        <div>
                            <h2 class="text-2xl font-bold border-l-4 border-accent pl-3 mb-6">Upcoming</h2>
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
                                    <option value="popular">Most Popular</option>
                                    <option value="top-airing">Top Airing</option>
                                    <option value="top-upcoming">Top Upcoming</option>
                                    <option value="movies">Movies</option>
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-400 mb-2">Genre</label>
                                <div id="browse-genre-filters" class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
                                    <!-- Checkboxes injected here -->
                                </div>
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
                            <button onclick="app.browse.changePage(-1)" id="btn-prev" class="px-6 py-2 border border-gray-700 rounded-lg hover:bg-gray-800 disabled:opacity-50">Prev</button>
                            <button onclick="app.browse.changePage(1)" id="btn-next" class="px-6 py-2 border border-gray-700 rounded-lg hover:bg-gray-800 disabled:opacity-50">Next</button>
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
                            <iframe id="video-frame" class="w-full h-full" src="about:blank" frameborder="0" allowfullscreen></iframe>
                        </div>

                        <!-- Anime Info -->
                        <div class="bg-dark-card p-6 rounded-xl border border-dark-border mb-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h1 id="watch-title" class="text-3xl font-bold text-white mb-2">Anime Title</h1>
                                    <div class="flex items-center space-x-4 text-sm text-gray-400">
                                        <span class="text-accent"><i class="fas fa-star mr-1"></i> <span id="watch-score">0.0</span></span>
                                        <span><i class="fas fa-tv mr-1"></i> <span id="watch-status">Unknown</span></span>
                                        <span><i class="fas fa-calendar mr-1"></i> <span id="watch-year">2024</span></span>
                                    </div>
                                </div>
                                <button id="fav-btn" class="text-gray-400 hover:text-accent transition-colors text-2xl">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                            <p id="watch-synopsis" class="text-gray-300 leading-relaxed mb-4"></p>
                            <div class="flex flex-wrap gap-2" id="watch-genres"></div>
                        </div>
                    </div>

                    <!-- Episodes Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-dark-card rounded-xl border border-dark-border h-[600px] flex flex-col sticky top-24">
                            <div class="p-4 border-b border-dark-border">
                                <h3 class="font-bold text-lg">Episodes</h3>
                                <div class="mt-2 flex gap-2">
                                    <span id="ep-count" class="text-xs bg-gray-800 px-2 py-1 rounded text-gray-400">0 eps</span>
                                    <select id="server-select" class="bg-gray-800 text-xs rounded px-2 py-1 border-none outline-none ml-auto">
                                        <option value="vidstreaming">VidStreaming</option>
                                        <option value="megacloud">MegaCloud</option>
                                        <option value="streamsb">StreamSB</option>
                                    </select>
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
                        <input type="text" id="search-input" placeholder="Search anime, movies, characters..." 
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
            <p class="text-gray-500 text-sm">&copy; 2024 TRIANIME. Data provided by Consumet API.</p>
            <div class="flex justify-center space-x-4 mt-4">
                <a href="#" class="text-gray-600 hover:text-accent"><i class="fab fa-discord"></i></a>
                <a href="#" class="text-gray-600 hover:text-accent"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-gray-600 hover:text-accent"><i class="fab fa-github"></i></a>
            </div>
        </div>
    </footer>

    <!-- Application Logic -->
    <script>
        /**
         * Configuration & Constants
         */
        const CONFIG = {
            apiBase: 'https://api.consumet.org', // Using Consumet for Anime data + streaming links
            imageBase: 'https://jpg.dev', // Reliable image proxy
            delayBetweenRequests: 400, // ms to prevent rate limiting
        };

        const GENRES = [
            "Action", "Adventure", "Comedy", "Drama", "Fantasy", "Horror", "Mecha", 
            "Music", "Mystery", "Psychological", "Romance", "Sci-Fi", "Slice of Life", 
            "Sports", "Supernatural", "Thriller", "Isekai", "Josei", "Shonen", "Shojo"
        ];

        /**
         * State Management
         */
        const state = {
            currentRoute: 'home',
            heroData: [],
            heroIndex: 0,
            browse: {
                page: 1,
                maxPage: 10,
                filters: {
                    type: 'popular',
                    genres: [],
                    search: ''
                }
            },
            watch: {
                animeId: null,
                episodes: [],
                currentEpisodeIndex: 0
            },
            search: {
                timeout: null,
                query: ''
            },
            timer: null
        };

        /**
         * API Handler with Rate Limiting
         */
        const api = {
            async request(endpoint, params = {}) {
                const url = new URL(`${CONFIG.apiBase}${endpoint}`);
                Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
                
                const res = await fetch(url.toString());
                if (!res.ok) throw new Error(`API Error: ${res.status}`);
                return await res.json();
            },

            async sequentialFetch(requests) {
                const results = [];
                for (let i = 0; i < requests.length; i++) {
                    if (i < requests.length - 1) {
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
                return this.request('/meta/anilist/trending?page=1');
            },
            
            async getTopAiring() {
                return this.request('/meta/anilist/airing?page=1');
            },

            async getUpcoming() {
                return this.request('/meta/anilist/advanced-search?type=anime&page=1&perPage=10&status=future');
            },

            async search(query, page = 1) {
                return this.request(`/meta/anilist/search?query=${encodeURIComponent(query)}&page=${page}`);
            },

            async getAnimeInfo(id) {
                return this.request(`/meta/anilist/info/${id}`);
            },

            async getEpisodeSources(animeId, episodeNumber, server = 'vidstreaming') {
                // Consumet endpoint for episode sources
                return this.request(`/meta/anilist/watch/${animeId}?episodeId=${episodeNumber}&server=${server}`);
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
                // Attempt to improve image quality or fallback
                if(imgUrl && imgUrl.startsWith('http')) return imgUrl;
                return 'https://via.placeholder.com/300x450?text=No+Image';
            },

            createAnimeCard(anime) {
                const imgSrc = this.getAnimeImageUrl(anime.image);
                return `
                    <div class="group relative bg-dark-card rounded-lg overflow-hidden shadow-lg hover:shadow-accent/20 transition-all duration-300 hover:-translate-y-1 cursor-pointer" onclick="app.router.navigate('watch', {id: '${anime.id}'})">
                        <div class="aspect-[2/3] overflow-hidden">
                            <img src="${imgSrc}" alt="${anime.title?.english || anime.title?.romaji || 'Anime'}" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-3">
                                <span class="text-xs font-bold bg-accent text-black px-2 py-1 rounded-full">${anime.type || 'TV'}</span>
                            </div>
                        </div>
                        <div class="p-3">
                            <h3 class="text-sm font-semibold text-gray-100 truncate group-hover:text-accent transition-colors">${anime.title?.english || anime.title?.romaji || 'Unknown'}</h3>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-xs text-gray-500">${anime.releaseDate ? new Date(anime.releaseDate).getFullYear() : 'N/A'}</span>
                                <span class="text-xs text-accent"><i class="fas fa-star mr-1"></i>${anime.rating ? (anime.rating / 10).toFixed(1) : '?'}</span>
                            </div>
                        </div>
                    </div>
                `;
            },

            createEpisodeItem(ep, index) {
                return `
                    <div onclick="app.watch.selectEpisode(${index})" class="episode-item cursor-pointer p-3 rounded-lg hover:bg-gray-800 flex justify-between items-center group transition-colors border border-transparent hover:border-gray-700" data-index="${index}">
                        <div class="flex items-center">
                            <span class="text-xs font-mono text-gray-500 w-8">EP ${ep.number}</span>
                            <span class="text-sm text-gray-300 truncate ml-2">${ep.title || `Episode ${ep.number}`}</span>
                        </div>
                        <i class="fas fa-play text-xs text-gray-600 group-hover:text-accent"></i>
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
                    if(btn.dataset.route === view) btn.classList.add('text-accent');
                    else btn.classList.remove('text-accent');
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
                
                // Fetch Data Sequentially
                const [trendingRes, airingRes, upcomingRes] = await api.sequentialFetch([
                    () => api.getTrending(),
                    () => api.getTopAiring(),
                    () => api.getUpcoming()
                ]);

                app.ui.showLoader(false);

                // Handle Hero Slider
                const heroData = trendingRes.results || [];
                state.heroData = heroData;
                state.heroIndex = 0;
                home.renderHero();
                home.startSlider();

                // Render Grid
                const grid = document.getElementById('home-grid');
                if (airingRes && airingRes.results) {
                    grid.innerHTML = airingRes.results.slice(0, 12).map(a => ui.createAnimeCard(a)).join('');
                } else {
                    grid.innerHTML = '<p class="col-span-3 text-center text-gray-500">Failed to load airing anime.</p>';
                }

                // Render Upcoming
                const upcomingList = document.getElementById('upcoming-list');
                if (upcomingRes && upcomingRes.results) {
                    upcomingList.innerHTML = upcomingRes.results.slice(0, 5).map(a => `
                        <div class="flex items-center space-x-3 cursor-pointer hover:bg-gray-800 p-2 rounded transition-colors" onclick="app.router.navigate('watch', {id: '${a.id}'})">
                            <img src="${ui.getAnimeImageUrl(a.image)}" class="w-12 h-16 object-cover rounded" loading="lazy">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-200 line-clamp-1">${a.title?.english || a.title?.romaji}</h4>
                                <span class="text-xs text-gray-500">Ep ${a.episodes?.next || '?'}</span>
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
                
                document.getElementById('hero-title').innerText = anime.title?.english || anime.title?.romaji;
                document.getElementById('hero-synopsis').innerText = anime.description || 'No synopsis available.';
                document.getElementById('hero-watch-btn').onclick = () => app.router.navigate('watch', {id: anime.id});
                document.getElementById('hero-details-btn').onclick = () => app.router.navigate('watch', {id: anime.id});
                
                // Background image setup
                const container = document.getElementById('hero-content');
                container.style.backgroundImage = `linear-gradient(to right, rgba(10,10,10,0.9) 0%, rgba(10,10,10,0.4) 50%, rgba(10,10,10,0.1) 100%), url(${ui.getAnimeImageUrl(aname.image)})`;
                container.style.backgroundSize = 'cover';
                container.style.backgroundPosition = 'center';
                
                // Indicators
                const indicators = document.getElementById('hero-indicators');
                indicators.innerHTML = state.heroData.map((_, i) => `
                    <button onclick="app.home.setHero(${i})" class="w-2 h-2 rounded-full ${i === state.heroIndex ? 'bg-accent' : 'bg-gray-600'}"></button>
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
                const select = document.getElementById('browse-sort');
                const genreContainer = document.getElementById('browse-genre-filters');
                
                // Reset or set filters from params
                if(params.genre) {
                    // Add genre to filter list
                    if(!state.browse.filters.genres.includes(params.genre)) {
                        state.browse.filters.genres.push(params.genre);
                    }
                }

                // Render Genre Checkboxes
                genreContainer.innerHTML = GENRES.map(g => `
                    <label class="flex items-center space-x-2 cursor-pointer hover:text-accent">
                        <input type="checkbox" value="${g}" 
                            ${state.browse.filters.genres.includes(g) ? 'checked' : ''} 
                            onchange="app.browse.toggleGenre('${g}')"
                            class="form-checkbox h-4 w-4 text-accent rounded border-gray-600 bg-dark-bg focus:ring-offset-dark-bg">
                        <span class="text-sm">${g}</span>
                    </label>
                `).join('');

                // Initial Load
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
                const val = document.getElementById('browse-sort').value;
                state.browse.filters.type = val;
                state.browse.page = 1;
                this.fetchContent();
            },

            resetFilters() {
                state.browse.filters = { type: 'popular', genres: [], search: '' };
                state.browse.page = 1;
                document.getElementById('browse-sort').value = 'popular';
                this.init();
            },

            changePage(offset) {
                state.browse.page += offset;
                this.fetchContent();
            },

            async fetchContent() {
                app.ui.showLoader(true);
                
                let data = null;
                let type = state.browse.filters.type;
                let genres = state.browse.filters.genres;

                try {
                    if (type === 'movies') {
                        data = await api.request('/meta/anilist/advanced-search?type=movie&page=1&perPage=20');
                    } else if (type === 'top-airing') {
                        data = await api.getTopAiring();
                    } else if (type === 'top-upcoming') {
                        data = await api.getUpcoming();
                    } else {
                        // Standard search with optional genres
                        let query = '';
                        if(genres.length > 0) query = genres.join(',');
                        
                        // Consumet advanced search handles genres better
                        data = await api.request(`/meta/anilist/advanced-search?type=anime&page=${state.browse.page}&perPage=20${query ? `&genres=${query}` : ''}`);
                    }
                } catch (e) {
                    console.error(e);
                    data = { results: [] };
                }

                app.ui.showLoader(false);
                
                const grid = document.getElementById('browse-grid');
                const count = document.getElementById('browse-count');
                const items = data.results || [];

                count.innerText = `${items.length} results`;
                grid.innerHTML = items.length 
                    ? items.map(a => ui.createAnimeCard(a)).join('') 
                    : '<div class="col-span-4 text-center text-gray-500 py-10">No anime found matching criteria.</div>';

                // Pagination Logic
                document.getElementById('btn-prev').disabled = state.browse.page <= 1;
                document.getElementById('btn-next').disabled = items.length < 20; // Simple heuristic
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
                    const data = await api.getAnimeInfo(params.id);
                    this.renderPage(data);
                } catch (e) {
                    console.error(e);
                    document.getElementById('watch-title').innerText = "Error loading anime";
                }
                app.ui.showLoader(false);
            },

            renderPage(data) {
                // Basic Info
                document.getElementById('watch-title').innerText = data.title?.english || data.title?.romaji;
                document.getElementById('watch-synopsis').innerText = data.description || "No description available.";
                document.getElementById('watch-score').innerText = data.rating ? (data.rating/10).toFixed(1) : 'N/A';
                document.getElementById('watch-status').innerText = data.status;
                document.getElementById('watch-year').innerText = data.releaseDate ? new Date(data.releaseDate).getFullYear() : 'N/A';
                
                const genres = data.genres || [];
                document.getElementById('watch-genres').innerHTML = genres.map(g => 
                    `<span class="text-xs bg-gray-800 px-2 py-1 rounded text-gray-400">${g}</span>`
                ).join('');

                // Episodes
                const epList = document.getElementById('episode-list');
                state.watch.episodes = data.episodes || [];
                document.getElementById('ep-count').innerText = `${state.watch.episodes.length} Episodes`;
                
                epList.innerHTML = state.watch.episodes.map((ep, idx) => ui.createEpisodeItem(ep, idx)).join('');

                // Select first episode
                if(state.watch.episodes.length > 0) {
                    this.selectEpisode(0);
                } else {
                    document.getElementById('video-frame').src = "about:blank";
                }
            },

            async selectEpisode(index) {
                // UI Update
                document.querySelectorAll('.episode-item').forEach(el => {
                    el.classList.remove('bg-gray-800', 'border-accent');
                    if(parseInt(el.dataset.index) === index) el.classList.add('bg-gray-800', 'border-accent');
                });
                
                state.watch.currentEpisodeIndex = index;
                
                // Get Source
                const ep = state.watch.episodes[index];
                const server = document.getElementById('server-select').value;
                
                try {
                    const sourceData = await api.getEpisodeSources(state.watch.animeId, ep.number, server);
                    
                    // Consumet returns an object with `sources` array
                    // Find high quality video
                    const videoSource = sourceData.sources.find(s => s.quality === 'auto' || s.quality === '1080p' || s.quality === '720p') || sourceData.sources[0];
                    
                    if(videoSource) {
                        document.getElementById('video-frame').src = videoSource.url;
                    } else {
                        document.getElementById('video-frame').src = "about:blank";
                    }
                } catch(e) {
                    console.error("Failed to load episode", e);
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
                    }
                });
            },

            async performSearch(query) {
                if(!query) return;
                state.search.query = query;
                
                app.ui.showLoader(true);
                document.getElementById('search-loading').classList.remove('hidden');
                document.getElementById('search-results').innerHTML = '';
                document.getElementById('search-empty').classList.add('hidden');

                try {
                    const data = await api.search(query);
                    app.ui.showLoader(false);
                    document.getElementById('search-loading').classList.add('hidden');
                    
                    if(data.results && data.results.length > 0) {
                        document.getElementById('search-results').innerHTML = data.results.map(a => ui.createAnimeCard(a)).join('');
                    } else {
                        document.getElementById('search-empty').classList.remove('hidden');
                    }
                } catch (e) {
                    app.ui.showLoader(false);
                    document.getElementById('search-loading').classList.add('hidden');
                    console.error(e);
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
