FROM php:8.2-apache

WORKDIR /var/www/html

# Install only the runtime packages we actually need.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules used by the application.
RUN a2enmod rewrite headers deflate expires

# Copy PHP configuration.
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy the Apache virtual host configuration.
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Enable the application site.
RUN a2ensite 000-default

# Copy the application entrypoint.
COPY index.php /var/www/html/index.php

# Make sure the web server can read the application.
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Render's default web-service port.
ENV PORT=10000 \
    APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data \
    APACHE_LOG_DIR=/var/log/apache2 \
    APACHE_LOCK_DIR=/var/run/apache2 \
    APACHE_PID_FILE=/var/run/apache2/apache2.pid

EXPOSE 10000

# Verify Apache can answer locally.
HEALTHCHECK --interval=30s --timeout=10s --start-period=10s --retries=3 \
    CMD curl -f http://127.0.0.1:10000/ || exit 1

# Render uses the Dockerfile CMD unless dockerCommand is explicitly set.
CMD ["apache2-foreground"]
