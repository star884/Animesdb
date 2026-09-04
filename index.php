<?php
/**
 * TRIANIME - Single File Anime Streaming Application
 * @version 2.0.0
 * @description Fully responsive SPA using Tenrai.net API, Tailwind CSS, and Vanilla JS.
 * 
 * Note: For production, ensure your server allows CORS or proxy the API requests.
 * This file uses Client-Side fetching for demonstration. 
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
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Video Player Custom Controls */
        .video-container {
            position: relative;
            background: #000;
            width: 100%;
            height: 100%;
        }
        .video-container video {
            width: 100%;
            height: 100%;
        }
        .custom-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .video-container:hover .custom-controls {
            opacity: 1;
        }
        .progress-bar {
            flex-grow: 1;
            height: 4px;
            background: rgba(255,255,255,0.2);
            cursor: pointer;
            position: relative;
        }
        .progress-fill {
            height: 100%;
            background: #ffbade;
            width: 0%;
            position: relative;
        }
        .progress-fill::after {
            content: '';
            position: absolute;
            right: -6px;
            top: -4px;
            width: 12px;
            height: 12px;
            background: #fff;
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .progress-bar:hover .progress-fill::after {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-dark-bg text-dark-text font-sans min-h-screen flex flex-col">

    <!-- Header -->
    <header class="glass fixed w-full top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3 cursor-pointer" onclick="app.navigate('home')">
                <i class="fas fa-play-circle text-accent text-3xl"></i>
                <h1 class="text-2xl font-bold tracking-tight">TRIANIME</h1>
            </div>
            
            <div class="hidden md:flex items-center bg-dark-card border border-dark-border rounded-full px-4 py-2 w-1/3">
                <i class="fas fa-search text-gray-500"></i>
                <input type="text" id="searchInput" placeholder="Search anime..." class="bg-transparent border-none outline-none ml-2 w-full text-sm text-dark-text placeholder-gray-500">
            </div>

            <nav class="flex items-center gap-4">
                <button class="hover:text-accent transition-colors"><i class="fas fa-history"></i></button>
                <button class="hover:text-accent transition-colors"><i class="fas fa-bookmark"></i></button>
                <button class="bg-accent text-dark-bg px-4 py-1 rounded-full text-sm font-bold hover:bg-opacity-80 transition">Login</button>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-20 pb-10">
        
        <!-- Hero Section -->
        <section id="heroSection" class="container mx-auto px-4 mb-12">
            <div class="relative rounded-2xl overflow-hidden bg-dark-card h-[400px] md:h-[500px] shadow-2xl border border-dark-border">
                <div id="heroContent" class="absolute inset-0 flex items-center">
                    <!-- Content injected by JS -->
                    <div class="w-full h-full skeleton"></div>
                </div>
                <div id="heroOverlay" class="absolute inset-0 bg-gradient-to-t from-dark-bg via-transparent to-transparent"></div>
            </div>
        </section>

        <!-- Filters & Sort -->
        <section class="container mx-auto px-4 mb-8 flex flex-wrap gap-4 items-center justify-between">
            <div class="flex gap-2 overflow-x-auto pb-2 hide-scrollbar">
                <button class="filter-btn active px-4 py-1 rounded-full bg-accent text-dark-bg text-sm font-bold" data-genre="all">All</button>
                <button class="filter-btn px-4 py-1 rounded-full bg-dark-card border border-dark-border hover:border-accent text-sm" data-genre="action">Action</button>
                <button class="filter-btn px-4 py-1 rounded-full bg-dark-card border border-dark-border hover:border-accent text-sm" data-genre="romance">Romance</button>
                <button class="filter-btn px-4 py-1 rounded-full bg-dark-card border border-dark-border hover:border-accent text-sm" data-genre="fantasy">Fantasy</button>
                <button class="filter-btn px-4 py-1 rounded-full bg-dark-card border border-dark-border hover:border-accent text-sm" data-genre="slice-of-life">Slice of Life</button>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-400">
                <i class="fas fa-sort-amount-down"></i>
                <select id="sortSelect" class="bg-transparent border-none outline-none cursor-pointer">
                    <option value="popular">Most Popular</option>
                    <option value="recent">Recently Added</option>
                    <option value="rating">Top Rated</option>
                </select>
            </div>
        </section>

        <!-- Anime Grid -->
        <section class="container mx-auto px-4">
            <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                <i class="fas fa-fire text-accent"></i> Trending Now
            </h2>
            
            <div id="animeGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <!-- Skeleton Loaders -->
                <div class="skeleton h-[350px] rounded-xl"></div>
                <div class="skeleton h-[350px] rounded-xl"></div>
                <div class="skeleton h-[350px] rounded-xl"></div>
                <div class="skeleton h-[350px] rounded-xl"></div>
                <div class="skeleton h-[350px] rounded-xl"></div>
            </div>
        </section>

    </main>

    <!-- Video Player Modal -->
    <div id="videoModal" class="fixed inset-0 z-[60] bg-black/95 hidden flex-col justify-center items-center p-4">
        <div class="w-full max-w-6xl relative">
            <!-- Close Button -->
            <button onclick="app.closePlayer()" class="absolute -top-10 right-0 text-white hover:text-accent text-2xl z-10">
                <i class="fas fa-times"></i>
            </button>

            <!-- Player Container -->
            <div class="video-container rounded-xl overflow-hidden border border-dark-border shadow-2xl bg-black">
                <video id="videoPlayer" controls autoplay>
                    <source src="" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                
                <!-- Custom Controls Overlay -->
                <div class="custom-controls">
                    <button id="playPauseBtn" class="text-white hover:text-accent">
                        <i class="fas fa-play"></i>
                    </button>
                    <div class="progress-bar" id="progressBar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <span id="timeDisplay" class="text-xs text-gray-300">00:00 / 00:00</span>
                    <button id="fullscreenBtn" class="text-white hover:text-accent">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>

            <!-- Episode List -->
            <div class="mt-4 bg-dark-card rounded-xl p-4 max-h-[300px] overflow-y-auto border border-dark-border">
                <h3 class="text-accent font-bold mb-3">Episodes</h3>
                <div id="episodeList" class="grid grid-cols-4 md:grid-cols-8 lg:grid-cols-10 gap-2">
                    <!-- Episodes injected here -->
                </div>
            </div>
            
            <!-- Info -->
            <div class="mt-4 text-center">
                <h2 id="videoTitle" class="text-xl font-bold">Anime Title</h2>
                <p id="videoDesc" class="text-gray-400 text-sm mt-1 line-clamp-2">Description goes here...</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark-card border-t border-dark-border py-8 mt-auto">
        <div class="container mx-auto px-4 text-center">
            <div class="flex justify-center gap-6 mb-4">
                <a href="#" class="text-gray-400 hover:text-accent"><i class="fab fa-discord text-xl"></i></a>
                <a href="#" class="text-gray-400 hover:text-accent"><i class="fab fa-twitter text-xl"></i></a>
                <a href="#" class="text-gray-400 hover:text-accent"><i class="fab fa-github text-xl"></i></a>
            </div>
            <p class="text-gray-500 text-sm">© 2026 TRIANIME. All rights reserved.</p>
        </div>
    </footer>

    <script>
        /**
         * TRIANIME Core Logic
         * Handles API fetching, DOM manipulation, and Video Playback
         */
        const app = {
            state: {
                currentAnime: null,
                episodes: [],
                currentEpisode: 0,
                isLoading: false
            },
            
            // API Configuration
            apis: {
                aniList: 'https://graphql.anilist.co',
                kitsu: 'https://kitsu.io/api/edge/anime',
                shikimori: 'https://shikimori.one/api',
                anidb: 'https://api.anidb.net:9001/httpapi',
                anify: 'https://anify.eltroy.dev/graphql'
            },

            init() {
                this.bindEvents();
                this.fetchFeatured();
                this.fetchTrending();
                
                // Search Debounce
                let timeout = null;
                document.getElementById('searchInput').addEventListener('input', (e) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => this.searchAnime(e.target.value), 500);
                });

                // Filter Buttons
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        document.querySelectorAll('.filter-btn').forEach(b => {
                            b.classList.remove('bg-accent', 'text-dark-bg');
                            b.classList.add('bg-dark-card', 'text-dark-text');
                        });
                        e.target.classList.remove('bg-dark-card', 'text-dark-text');
                        e.target.classList.add('bg-accent', 'text-dark-bg');
                        this.fetchTrending(e.target.dataset.genre);
                    });
                });
            },

            bindEvents() {
                // Video Player Controls
                const video = document.getElementById('videoPlayer');
                
                video.addEventListener('timeupdate', () => {
                    const percent = (video.currentTime / video.duration) * 100;
                    document.getElementById('progressFill').style.width = `${percent}%`;
                    document.getElementById('timeDisplay').textContent = `${this.formatTime(video.currentTime)} / ${this.formatTime(video.duration || 0)}`;
                });

                document.getElementById('progressBar').addEventListener('click', (e) => {
                    const rect = e.currentTarget.getBoundingClientRect();
                    const pos = (e.clientX - rect.left) / rect.width;
                    video.currentTime = pos * video.duration;
                });

                document.getElementById('playPauseBtn').addEventListener('click', () => {
                    if (video.paused) video.play();
                    else video.pause();
                });

                document.getElementById('fullscreenBtn').addEventListener('click', () => {
                    if (!document.fullscreenElement) {
                        video.requestFullscreen();
                    } else {
                        document.exitFullscreen();
                    }
                });

                // Modal Close on Escape
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') this.closePlayer();
                });
            },

            async fetchFeatured() {
                try {
                    // Try AniList first as it's robust
                    const query = `
                        query {
                            page(perPage: 1) {
                                trendingAiring(media_type: ANIME) {
                                    id
                                    title {
                                        romaji
                                        english
                                        native
                                    }
                                    coverImage {
                                        large
                                    }
                                    description
                                    genres
                                    averageRating
                                    episodes
                                }
                            }
                        }
                    `;
                    const response = await fetch(this.apis.aniList, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ query })
                    });
                    const data = await response.json();
                    
                    if (data.data?.page?.trendingAiring?.length) {
                        this.renderHero(data.data.page.trendingAiring[0]);
                    } else {
                        this.renderHeroFallback();
                    }
                } catch (error) {
                    console.error("Featured fetch error:", error);
                    this.renderHeroFallback();
                }
            },

            renderHero(anime) {
                const hero = document.getElementById('heroContent');
                hero.innerHTML = `
                    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('${anime.coverImage.large}');"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-dark-bg via-dark-bg/80 to-transparent"></div>
                    <div class="relative z-10 p-8 md:p-12 max-w-2xl animate-fade-in">
                        <span class="inline-block px-2 py-1 bg-accent text-dark-bg text-xs font-bold rounded mb-4">TOP TRENDING</span>
                        <h2 class="text-4xl md:text-5xl font-bold mb-2 leading-tight">${anime.title.english || anime.title.romaji}</h2>
                        <div class="flex items-center gap-4 text-sm text-gray-300 mb-4">
                            <span class="flex items-center gap-1"><i class="fas fa-star text-yellow-400"></i> ${anime.averageRating}</span>
                            <span>${anime.episodes || '?'} Ep</span>
                            <span class="px-2 border border-gray-600 rounded text-xs">${anime.genres[0]}</span>
                        </div>
                        <p class="text-gray-400 line-clamp-3 mb-6 text-sm md:text-base">${anime.description.replace(/<[^>]*>/g, '')}</p>
                        <div class="flex gap-3">
                            <button onclick="app.openPlayer(${anime.id}, '${anime.title.romaji || anime.title.english}')" class="bg-accent text-dark-bg px-6 py-2 rounded-full font-bold hover:bg-opacity-90 transition flex items-center gap-2">
                                <i class="fas fa-play"></i> Watch Now
                            </button>
                            <button class="bg-dark-bg/50 border border-white/20 text-white px-6 py-2 rounded-full font-bold hover:bg-white/10 transition">
                                <i class="fas fa-plus"></i> My List
                            </button>
                        </div>
                    </div>
                `;
            },

            renderHeroFallback() {
                const hero = document.getElementById('heroContent');
                hero.innerHTML = `
                    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://via.placeholder.com/1920x1080/121212/ffbade?text=TRIANIME');"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-dark-bg via-dark-bg/80 to-transparent"></div>
                    <div class="relative z-10 p-8 md:p-12 max-w-2xl">
                        <h2 class="text-4xl md:text-5xl font-bold mb-2">Welcome to TRIANIME</h2>
                        <p class="text-gray-400 mb-6">Stream unlimited anime from multiple sources including AniList, Kitsu, and Shikimori.</p>
                        <button class="bg-accent text-dark-bg px-6 py-2 rounded-full font-bold">Explore Library</button>
                    </div>
                `;
            },

            async fetchTrending(genre = 'all') {
                const grid = document.getElementById('animeGrid');
                grid.innerHTML = Array(5).fill('<div class="skeleton h-[350px] rounded-xl"></div>').join('');

                try {
                    // Fetch from AniList for trending
                    const query = `
                        query ($page: Int, $perPage: Int, $genre: String) {
                            page(page: $page, perPage: $perPage) {
                                pageInfo { total }
                                media(genre: $genre, sort: TRENDING_DESC, type: ANIME) {
                                    id
                                    title { romaji }
                                    coverImage { large }
                                    format
                                    status
                                    episodeCount
                                    averageScore
                                }
                            }
                        }
                    `;
                    
                    const variables = { page: 1, perPage: 10, genre: genre === 'all' ? null : genre };
                    
                    const response = await fetch(this.apis.aniList, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ query, variables })
                    });
                    
                    const data = await response.json();
                    
                    if (data.data?.page?.media) {
                        this.renderGrid(data.data.page.media);
                    } else {
                        grid.innerHTML = '<p class="text-center col-span-full">No results found.</p>';
                    }
                } catch (error) {
                    console.error("Trending fetch error:", error);
                    // Fallback mock data if API fails
                    this.renderGrid(this.getMockData());
                }
            },

            renderGrid(animeList) {
                const grid = document.getElementById('animeGrid');
                grid.innerHTML = animeList.map(anime => `
                    <div class="group relative cursor-pointer fade-in" onclick="app.openPlayer(${anime.id}, '${anime.title.romaji.replace(/'/g, "\\'")}')">
                        <div class="relative overflow-hidden rounded-xl aspect-[3/4] bg-dark-card">
                            <img src="${anime.coverImage.large}" alt="${anime.title.romaji}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
                            <div class="absolute top-2 right-2 bg-dark-bg/80 backdrop-blur-sm px-2 py-1 rounded text-xs font-bold text-accent">
                                ${anime.averageScore ? Math.round(anime.averageScore / 10) : 'N/A'}
                            </div>
                            <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-dark-bg to-transparent translate-y-full group-hover:translate-y-0 transition-transform duration-300">
                                <button class="w-full bg-accent text-dark-bg py-2 rounded-lg text-xs font-bold flex items-center justify-center gap-2">
                                    <i class="fas fa-play"></i> Watch
                                </button>
                            </div>
                        </div>
                        <h3 class="mt-3 text-sm font-medium truncate group-hover:text-accent transition-colors">${anime.title.romaji}</h3>
                        <p class="text-xs text-gray-500">${anime.format || 'TV'} • ${anime.episodeCount || '?'} Ep</p>
                    </div>
                `).join('');
            },

            getMockData() {
                return Array(10).fill(null).map((_, i) => ({
                    id: 1000 + i,
                    title: { romaji: `Mock Anime ${i + 1}` },
                    coverImage: { large: `https://via.placeholder.com/300x450/121212/333?text=Anime+${i + 1}` },
                    format: 'TV',
                    episodeCount: 12,
                    averageScore: 80
                }));
            },

            async searchAnime(query) {
                if (!query) return this.fetchTrending();
                
                const grid = document.getElementById('animeGrid');
                grid.innerHTML = '<div class="col-span-full flex justify-center p-10"><div class="loader"></div></div>';

                try {
                    const response = await fetch(`https://graphql.anilist.co`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            query: `
                                query ($search: String) {
                                    page(perPage: 12) {
                                        media(search: $search, type: ANIME) {
                                            id
                                            title { romaji }
                                            coverImage { large }
                                            format
                                            episodeCount
                                            averageScore
                                        }
                                    }
                                }
                            `,
                            variables: { search: query }
                        })
                    });
                    const data = await response.json();
                    
                    if (data.data?.page?.media.length) {
                        this.renderGrid(data.data.page.media);
                    } else {
                        grid.innerHTML = '<p class="text-center col-span-full text-gray-500">No anime found matching "' + query + '"</p>';
                    }
                } catch (error) {
                    grid.innerHTML = '<p class="text-center col-span-full text-red-400">Error fetching search results.</p>';
                }
            },

            async openPlayer(id, title) {
                const modal = document.getElementById('videoModal');
                const video = document.getElementById('videoPlayer');
                const epList = document.getElementById('episodeList');
                
                document.getElementById('videoTitle').textContent = title;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                
                // Simulate loading episodes
                epList.innerHTML = Array(24).fill('<div class="skeleton h-10 rounded"></div>').join('');
                
                try {
                    // Fetch streaming data from Consumet API (Hianime/Zigzag)
                    // Note: Consumet is a proxy aggregator. 
                    const baseConsumet = 'https://consumet-api.xyz/api/hianime';
                    
                    // 1. Get ID from title (simplified lookup) or use AniList ID to find Consumet ID
                    // For this demo, we assume we can fetch by title or use a known ID structure.
                    // Let's use the AniList ID to fetch details first, then map to Consumet.
                    
                    // For this single-file demo, we will fetch episodes directly from a public Consumet instance
                    // We need the Consumet ID. Let's search hianime via Consumet
                    const searchRes = await fetch(`${baseConsumet}/meta/anilist/info?id=${id}`);
                    const searchData = await searchRes.json();
                    
                    document.getElementById('videoDesc').textContent = searchData.description || "No description available.";
                    
                    // Populate Episodes
                    if (searchData.episodes) {
                        epList.innerHTML = searchData.episodes.map(ep => `
                            <button onclick="app.playEpisode('${ep.serverUrls[0].url}', '${ep.number}')" 
                                class="bg-dark-bg hover:bg-dark-border border border-dark-border text-xs p-2 rounded hover:text-accent transition">
                                ${ep.number}
                            </button>
                        `).join('');
                        
                        // Auto-play first episode
                        if (searchData.episodes.length > 0) {
                            this.playEpisode(searchData.episodes[0].serverUrls[0].url, "1");
                        }
                    }

                } catch (error) {
                    console.error("Player fetch error:", error);
                    epList.innerHTML = '<p class="text-xs text-red-400 p-2">Failed to load episodes.</p>';
                }
            },

            playEpisode(url, epNum) {
                const video = document.getElementById('videoPlayer');
                video.src = url;
                video.play();
                showToast(`Playing Episode ${epNum}`);
            },

            closePlayer() {
                const modal = document.getElementById('videoModal');
                const video = document.getElementById('videoPlayer');
                video.pause();
                video.src = "";
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            },

            showToast(message) {
                const existing = document.querySelector('.toast');
                if (existing) existing.remove();
                
                const toast = document.createElement('div');
                toast.className = 'toast';
                toast.innerHTML = `<i class="fas fa-info-circle text-accent mr-2"></i> ${message}`;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            },

            formatTime(seconds) {
                if (isNaN(seconds)) return "00:00";
                const m = Math.floor(seconds / 60);
                const s = Math.floor(seconds % 60);
                return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            },
            
            navigate(page) {
                if (page === 'home') window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        };

        // Initialize App
        document.addEventListener('DOMContentLoaded', () => {
            app.init();
        });
    </script>
</body>
</html>
