# TRIANIME - Anime Streaming Application

A fully responsive Single Page Application (SPA) for streaming anime using the Tenrai.net API, Tailwind CSS, and Vanilla JavaScript.

## 🚀 Quick Start

### Build Command (Render):
```bash
chmod +x build.sh && ./build.sh
```

### Start Command (Render):
```bash
apache2-foreground
```

---

## 📋 What's Included

- **Index.php** - Main HTML/PHP application with Tailwind CSS
- **app.js** - Client-side routing and API logic (legacy)
- **Dockerfile** - Multi-stage Docker build
- **docker-compose.yml** - Local development setup
- **build.sh** - Automated build script
- **render.yaml** - Render.com configuration
- **php.ini** - PHP configuration
- **apache-config.conf** - Apache virtual host setup

---

## 🌟 Features

✅ **Responsive Design** - Mobile, tablet, and desktop
✅ **Anime Streaming** - Powered by Tenrai.net API
✅ **Browse & Search** - Explore thousands of anime
✅ **Dark Theme** - Eye-friendly dark interface
✅ **SPA Routing** - Smooth client-side navigation
✅ **Video Player** - Embedded video streaming
✅ **Watch History** - Track watched anime
✅ **Genre Filtering** - Filter and browse by type

---

## 🛠️ Local Development

### Using Docker Compose:
```bash
docker-compose up -d
# Visit http://localhost:8080
```

### Using Docker:
```bash
docker build -t trianime .
docker run -p 8080:80 trianime
# Visit http://localhost:8080
```

---

## 📦 Deployment Options

### Option 1: Render (Recommended)
1. Push to GitHub
2. Go to [render.com](https://render.com)
3. Create new Web Service
4. Connect GitHub repo
5. Use commands above
6. Done! 🎉

### Option 2: Docker Hub
```bash
docker build -t your-username/trianime .
docker push your-username/trianime
```

### Option 3: Self-Hosted
```bash
# On your server
docker run -d -p 80:80 trianime
```

---

## 🔧 Configuration

### PHP Configuration (`php.ini`)
- Max upload: 100MB
- Execution time: 300 seconds
- Memory limit: 256MB

### Apache Configuration (`apache-config.conf`)
- mod_rewrite enabled for SPA routing
- Compression (DEFLATE) enabled
- Security headers added
- Browser caching configured

### Render Configuration (`render.yaml`)
- Docker runtime
- Auto-deploy on push
- Health checks enabled
- Free tier by default (upgradeable)

---

## 📊 API Integration

Uses **Tenrai.net API** via `https://api.mguo.me` for anime data:
- Trending anime
- Search functionality
- Episode information
- Streaming sources

**No authentication required**

---

## 🎯 Pages

1. **Home** - Trending and featured anime
2. **Browse** - Browse all anime with pagination
3. **Search** - Real-time anime search
4. **Watch** - Video player with episode selection
5. **History** - View watch history

---

## 🚨 Troubleshooting

### Build fails
```bash
chmod +x build.sh
./build.sh
```

### Port in use
```bash
# Docker will use a random port if 8080 is taken
docker run -p 8081:80 trianime
```

### API not responding
- Check internet connection
- Tenrai API might be down
- Try again in a few moments

### Static files 404
- Verify `Index.php` and `app.js` in repo root
- Check browser cache: Ctrl+Shift+Delete

---

## 📈 Performance

- ✅ Static SPA (no database)
- ✅ Optimized API calls
- ✅ Browser caching enabled
- ✅ GZIP compression
- ✅ Lazy image loading

**Load time**: < 2 seconds on standard connection

---

## 💰 Cost (Render)

| Plan | Price | Features |
|------|-------|----------|
| Free | $0/month | 750 hrs, may spin down after 15 min idle |
| Standard | $7/month | Always-on, 2GB RAM |
| Pro | $12+/month | Always-on, 4GB RAM |

---

## 🔒 Security Features

- X-Content-Type-Options header
- X-Frame-Options header
- XSS Protection enabled
- Referrer Policy set
- HTTPS recommended (Render provides free SSL)

---

## 📞 Support

- [Render Docs](https://render.com/docs)
- [Tenrai API](https://tenrai.net)
- [Apache Docs](https://httpd.apache.org/docs)
- [PHP Manual](https://www.php.net/manual)

---

## 📝 License

This project is provided as-is for educational purposes.

---

## 🎬 Next Steps

1. ✅ Push repository to GitHub
2. ✅ Create account on [render.com](https://render.com)
3. ✅ Create new Web Service
4. ✅ Connect GitHub repository
5. ✅ Deploy with provided build/start commands
6. ✅ Visit your live URL and start streaming! 🚀

**Happy Streaming!**