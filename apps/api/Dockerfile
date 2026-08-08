FROM composer:2 AS vendor
WORKDIR /app
COPY apps/api/composer.json apps/api/composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader --prefer-dist
FROM php:8.3-apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
COPY infra/api/apache.conf /etc/apache2/conf-available/utaone-api.conf
COPY infra/php/utaone.ini /usr/local/etc/php/conf.d/utaone.ini
RUN apt-get update && apt-get install -y --no-install-recommends libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite && a2enmod rewrite headers && a2enconf utaone-api \
    && sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html
COPY apps/api/ ./
COPY --from=vendor /app/vendor ./vendor
COPY infra/api/entrypoint.sh /usr/local/bin/utaone-api-entrypoint
RUN chmod +x /usr/local/bin/utaone-api-entrypoint && chown -R www-data:www-data storage bootstrap/cache
ENTRYPOINT ["utaone-api-entrypoint"]
CMD ["apache2-foreground"]
