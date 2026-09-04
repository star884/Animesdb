#!/bin/bash
# Build script for TRIANIME on Render

set -e

echo "🚀 Building TRIANIME Anime Streaming Application..."

# Update system packages
apt-get update
apt-get install -y --no-install-recommends \
    apache2 \
    php8.2 \
    php8.2-cli \
    php8.2-common \
    php8.2-curl \
    libapache2-mod-php8.2 \
    curl \
    wget

# Enable Apache modules
a2enmod rewrite
a2enmod headers
a2enmod deflate
a2enmod expires

echo "✅ Build complete!"