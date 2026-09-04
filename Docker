FROM php:8.2-apache

WORKDIR /var/www/html

COPY Index.php /var/www/html/index.php
COPY app.js /var/www/html/app.js

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
