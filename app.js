class App {
    constructor() {
        this.apiBase = 'https://consumet-api.xyz'; // Update with your specific Consumet instance
        this.currentRoute = 'home';
        this.init();
    }

    init() {
        this.router.navigate('home');
        window.addEventListener('popstate', () => {
            this.router.handleRoute();
        });
    }

    router = {
        navigate: (route) => {
            history.pushState({}, '', `#${route}`);
            app.router.handleRoute();
        },
        handleRoute: () => {
            const hash = window.location.hash.slice(1) || 'home';
            app.currentRoute = hash;
            app.updateNav(hash);
            app.render(hash);
        },
        updateNav: (route) => {
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.classList.remove('text-accent');
                if (btn.dataset.route === route) btn.classList.add('text-accent');
            });
        }
    };

    async render(route) {
        const main = document.getElementById('main-content'); // You need this ID in your HTML
        main.innerHTML = '<div class="loader mx-auto mt-10"></div>';

        try {
            if (route === 'home') {
                const trending = await this.fetchTrending();
                this.renderHome(trending);
            } else if (route === 'browse') {
                // Add search logic here
                this.renderBrowse();
            } else if (route.startsWith('anime/')) {
                const id = route.split('/')[1];
                const animeDetails = await this.fetchAnimeDetails(id);
                this.renderDetails(animeDetails);
            }
        } catch (error) {
            main.innerHTML = '<p class="text-center text-red-500 mt-10">Failed to load data.</p>';
        }
    }

    async fetchTrending() {
        // Example using Jikan API (MyAnimeList) if Consumet isn't set up
        const res = await fetch(`${this.apiBase}/meta/anilist/trending`);
        return await res.json();
    }

    async fetchAnimeDetails(id) {
        const res = await fetch(`${this.apiBase}/meta/anilist/info/${id}`);
        return await res.json();
    }

    renderHome(trending) {
        const main = document.getElementById('main-content');
        const html = `
            <h2 class="text-2xl font-bold mb-4">Trending Now</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                ${trending.results.slice(0, 10).map(anime => `
                    <div class="bg-dark-card rounded-lg overflow-hidden cursor-pointer hover:scale-105 transition-transform" 
                         onclick="app.router.navigate('anime/${anime.id}')">
                        <img src="${anime.image}" alt="${anime.title.english || anime.title.romaji}" class="w-full h-64 object-cover">
                        <div class="p-2">
                            <h3 class="text-sm font-medium truncate">${anime.title.english || anime.title.romaji}</h3>
                            <p class="text-xs text-gray-500">${anime.type || 'TV'}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        main.innerHTML = html;
    }
    
    // Add renderBrowse and renderDetails methods similarly
    renderBrowse() {
        document.getElementById('main-content').innerHTML = '<h2 class="text-2xl font-bold">Browse</h2><p>Search functionality goes here.</p>';
    }
    
    renderDetails(anime) {
        document.getElementById('main-content').innerHTML = `
            <div class="p-4">
                <button onclick="app.router.navigate('home')" class="mb-4 text-sm hover:text-accent"><i class="fas fa-arrow-left"></i> Back</button>
                <h1 class="text-3xl font-bold">${anime.title.english || anime.title.romaji}</h1>
                <p class="mt-2 text-gray-400">${anime.description}</p>
                <!-- Add video player placeholder here -->
            </div>
        `;
    }
}

// Initialize App
const app = new App();
