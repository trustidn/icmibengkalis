# syntax=docker/dockerfile:1

# ---------- base ----------
FROM php:8.3-fpm-alpine AS base
RUN apk add --no-cache icu-dev libzip-dev libpng-dev libjpeg-turbo-dev \
    libwebp-dev freetype-dev oniguruma-dev git \
 && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
 && docker-php-ext-install pdo_mysql intl zip gd bcmath opcache pcntl exif \
 && apk add --no-cache $PHPIZE_DEPS \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apk del $PHPIZE_DEPS
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini
WORKDIR /var/www/html

# ---------- dev ----------
FROM base AS dev
RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
 && pecl install xdebug \
 && docker-php-ext-enable xdebug \
 && apk del $PHPIZE_DEPS linux-headers
COPY docker/php/php-dev.ini /usr/local/etc/php/conf.d/99-dev.ini
COPY docker/php/entrypoint-dev.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

# ---------- vendor ----------
FROM base AS vendor
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

# ---------- assets ----------
# Butuh vendor/ karena resources/css/app.css meng-import vendor/livewire/flux/dist/flux.css
FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources resources
COPY public public
COPY vite.config.js ./
COPY --from=vendor /var/www/html/vendor vendor
RUN npm run build

# ---------- prod ----------
FROM base AS prod
COPY docker/php/php-prod.ini /usr/local/etc/php/conf.d/99-prod.ini
COPY --from=vendor /var/www/html/vendor vendor
COPY . .
COPY --from=assets /app/public/build public/build
RUN composer dump-autoload --optimize --no-dev \
 && php artisan storage:link || true \
 && chown -R www-data:www-data storage bootstrap/cache
COPY docker/php/entrypoint-prod.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
USER www-data
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
