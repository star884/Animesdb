FROM php:8.2-apache

WORKDIR /var/www/html

# Install the small set of packages needed by the application.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Enable required Apache modules.
RUN a2enmod rewrite headers deflate expires

# Configure Apache to use Render's web-service port.
RUN sed -i 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' /etc/apache2/sites-available/000-default.conf

# Replace the default Apache virtual host configuration.
RUN cat > /etc/apache2/sites-available/000-default.conf <<'APACHE'
<VirtualHost *:10000>

    ServerName localhost

    DocumentRoot /var/www/html

    DirectoryIndex index.php

    <Directory /var/www/html>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        <IfModule mod_rewrite.c>
            RewriteEngine On

            RewriteCond %{REQUEST_FILENAME} -f [OR]
            RewriteCond %{REQUEST_FILENAME} -d
            RewriteRule ^ - [L]

            RewriteRule ^ index.php [L,QSA]
        </IfModule>
    </Directory>

    <IfModule mod_headers.c>
        Header always set X-Content-Type-Options "nosniff"
        Header always set X-Frame-Options "SAMEORIGIN"
        Header always set Referrer-Policy "strict-origin-when-cross-origin"
    </IfModule>

    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html
        AddOutputFilterByType DEFLATE text/plain
        AddOutputFilterByType DEFLATE text/css
        AddOutputFilterByType DEFLATE text/javascript
        AddOutputFilterByType DEFLATE application/javascript
        AddOutputFilterByType DEFLATE application/json
        AddOutputFilterByType DEFLATE application/xml
        AddOutputFilterByType DEFLATE image/svg+xml
    </IfModule>

    <IfModule mod_expires.c>
        ExpiresActive On

        ExpiresByType text/html "access plus 0 seconds"
        ExpiresByType application/json "access plus 0 seconds"

        ExpiresByType text/css "access plus 7 days"
        ExpiresByType application/javascript "access plus 7 days"

        ExpiresByType image/jpeg "access plus 30 days"
        ExpiresByType image/png "access plus 30 days"
        ExpiresByType image/gif "access plus 30 days"
        ExpiresByType image/webp "access plus 30 days"
        ExpiresByType font/woff2 "access plus 30 days"
    </IfModule>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

</VirtualHost>
APACHE

# Verify Apache configuration during the Docker build.
RUN apache2ctl configtest

# Copy the application.
#
# IMPORTANT:
# The repository file MUST be named:
# index.php
#
# lowercase "i", not "Index.php".
COPY index.php /var/www/html/index.php

# Set safe permissions.
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Render web service port.
ENV PORT=10000

EXPOSE 10000

# Make the container answer Render health checks.
HEALTHCHECK --interval=30s --timeout=10s --start-period=15s --retries=3 \
    CMD curl -f http://127.0.0.1:10000/ || exit 1

# Start Apache.
CMD ["apache2-foreground"]
