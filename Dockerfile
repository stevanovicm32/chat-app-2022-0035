# Backend (Laravel) - Chat aplikacija (PHP 8.4 zbog composer.lock zavisnosti)
FROM php:8.4-cli

# Potrebne PHP ekstenzije za Laravel (libsqlite3-dev za pdo_sqlite)
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite mbstring zip exif pcntl bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Prvo samo fajlovi potrebni za composer (brži layer)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer dump-autoload --optimize

# Storage i cache moraju biti upisivi
RUN mkdir -p storage/framework/{sessions,views,cache/data} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
