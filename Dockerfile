# Multi-stage build for TRIANIME Anime Streaming Application
# Stage 1: Builder - Setup dependencies
FROM php:8.2-apache as builder

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    wget \
    gnupg \
    libssl-dev \
    libcurl4-openssl-dev \
    pkg-config \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers deflate expires ssl proxy proxy_http

# Copy PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default

# Copy application files
COPY Index.php /var/www/html/Index.php
COPY app.js /var/www/html/app.js

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod 644 /var/www/html/Index.php && \
    chmod 644 /var/www/html/app.js

# Create necessary directories
RUN mkdir -p /var/log/apache2 && \
    chown -R www-data:www-data /var/log/apache2

# Production stage
FROM php:8.2-apache as production

WORKDIR /var/www/html

# Install runtime dependencies only
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers deflate expires ssl proxy proxy_http

# Copy from builder
COPY --from=builder /usr/local/etc/php/conf.d/custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY --from=builder /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY --from=builder /var/www/html /var/www/html
COPY --from=builder /var/log/apache2 /var/log/apache2

# Enable site
RUN a2ensite 000-default

# Set environment variables
ENV APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data \
    APACHE_LOG_DIR=/var/log/apache2 \
    APACHE_LOCK_DIR=/var/run/apache2 \
    APACHE_PID_FILE=/var/run/apache2/apache2.pid

# Expose ports
EXPOSE 80 443

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start Apache
CMD ["apache2-foreground"]

---

# Development stage with debugging tools
FROM production as development

# Install development tools
RUN apt-get update && apt-get install -y --no-install-recommends \
    vim \
    nano \
    htop \
    netcat-openbsd \
    net-tools \
    telnet \
    dnsutils \
    iputils-ping \
    && rm -rf /var/lib/apt/lists/*

# Enable debug logging
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "display_errors = On" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "log_errors = On" >> /usr/local/etc/php/conf.d/custom.ini

# More verbose logging
ENV APACHE_LOG_LEVEL=debug

CMD ["apache2-foreground"]
