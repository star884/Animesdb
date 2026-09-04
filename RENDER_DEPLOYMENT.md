# TRIANIME - Render.com Deployment Guide

## 🚀 Build & Start Commands for Render

### Build Command:
```bash
chmod +x build.sh && ./build.sh
```

### Start Command:
```bash
apache2-foreground
```

---

## Step-by-Step Setup on Render

### Option 1: Using render.yaml (Recommended)

1. Go to [Render Dashboard](https://dashboard.render.com)
2. Click **"New +'** → **"Web Service"
3. Connect your GitHub repository
4. Render will automatically detect `render.yaml`
5. Click **"Create Web Service"

### Option 2: Manual Configuration

1. Go to [Render Dashboard](https://dashboard.render.com)
2. Click **"New +"** → **"Web Service"
3. Connect your GitHub repository
4. Fill in the form:
   - **Name**: `trianime`
   - **Runtime**: `Docker`
   - **Build Command**: 
     ```bash
     chmod +x build.sh && ./build.sh
     ```
   - **Start Command**: 
     ```bash
     apache2-foreground
     ```
   - **Instance Type**: Free (or upgrade to Standard for $7/month for always-on)
5. Click **"Create Web Service"

---

## Environment Variables (Auto-configured via render.yaml)

- `PORT`: 80
- `APACHE_LOG_DIR`: /var/log/apache2
- `APACHE_RUN_USER`: www-data
- `APACHE_RUN_GROUP`: www-data

---

## What Each File Does

| File | Purpose |
|------|----------|
| `Dockerfile` | Docker image definition with Apache & PHP 8.2 |
| `docker-compose.yml` | Local development environment |
| `php.ini` | PHP configuration (memory, execution time, etc) |
| `apache-config.conf` | Apache vhost with mod_rewrite & security headers |
| `build.sh` | Installation script for Render build phase |
| `render.yaml` | Render configuration (auto-detected) |

---

## Local Testing Before Deployment

```bash
# Test with Docker Compose
docker-compose up -d

# Visit http://localhost:8080
```

Or build manually:
```bash
docker build -t trianime .
docker run -p 8080:80 trianime
```

---

## After Deployment

Your application will be accessible at:
```
https://trianime.onrender.com
```

Check logs in Render Dashboard → Logs tab for any issues.

---

## Troubleshooting

### 502 Bad Gateway
- Check Render logs for errors
- Ensure `apache2-foreground` completes successfully

### Build Fails
```bash
# Verify build.sh is executable locally
chmod +x build.sh
./build.sh
```

### Static Files Not Loading
- Verify `Index.php` and `app.js` are in repo root
- Check Apache rewrite rules in `apache-config.conf`

---

## Performance Notes

- **Free Tier**: May spin down after 15 minutes of inactivity
- **Standard Tier ($7/month)**: Always-on, better performance
- **Caching**: Browser cache enabled for CSS, JS, images
- **Compression**: GZIP enabled for faster delivery

---

**Ready to deploy? Push to GitHub and create the service on Render!** 🚀