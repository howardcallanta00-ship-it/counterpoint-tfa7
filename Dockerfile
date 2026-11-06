FROM php:8.3-apache-bookworm AS base
RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev libsqlite3-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libonig-dev libzip-dev unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd exif mbstring pdo_pgsql pdo_mysql pdo_sqlite zip opcache \
    && a2enmod rewrite headers remoteip \
    && truncate -s 0 /etc/apache2/ports.conf \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --no-dev --optimize \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache /var/run/apache2 /var/lock/apache2 /var/log/apache2 \
    && chmod +x deploy/start.sh
COPY deploy/apache.conf /etc/apache2/sites-available/000-default.conf
COPY deploy/php.ini /usr/local/etc/php/conf.d/production.ini
ENV APP_ENV=production APP_DEBUG=false LOG_CHANNEL=stderr PORT=10000
USER www-data
EXPOSE 10000
CMD ["sh", "deploy/start.sh"]
