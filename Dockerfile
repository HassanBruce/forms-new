# ---- 1. Build front-end assets (Tailwind + Alpine + self-hosted font) ----
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

# ---- 2. PHP 8.4 + nginx (runs as www-data on port 8080) ----
FROM serversideup/php:8.4-fpm-nginx
USER root
RUN install-php-extensions intl bcmath
USER www-data
WORKDIR /var/www/html

COPY --chown=www-data:www-data composer.json ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY --chown=www-data:www-data . .
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build
RUN composer dump-autoload --optimize --no-dev \
 && php artisan package:discover --ansi

ENV PHP_OPCACHE_ENABLE=1 \
    AUTORUN_ENABLED=true \
    LOG_CHANNEL=stderr
