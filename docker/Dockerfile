# syntax=docker/dockerfile:1.7

###############################################################################
# Stackposts v10 — production image  (monorepo build)
#
# Build context = repository root. Application source lives in ./app and ships
# with vendor/ + public/build/ prebuilt (CodeCanyon "Install.zip"), so no
# Composer/Node stage runs by default. Set RUN_COMPOSER_INSTALL=true to build
# from an app/ tree without vendor/.
###############################################################################

ARG PHP_VERSION=8.3

###############################################################################
# Stage 1 (optional): composer dependencies
###############################################################################
FROM composer:2 AS vendor

WORKDIR /app
ARG RUN_COMPOSER_INSTALL=false

COPY app/composer.json app/composer.lock* ./
RUN if [ "$RUN_COMPOSER_INSTALL" = "true" ]; then \
        composer install --no-dev --no-scripts --no-interaction \
            --prefer-dist --optimize-autoloader --no-progress ; \
    else \
        mkdir -p vendor ; \
    fi

###############################################################################
# Stage 2: runtime — php-fpm + nginx + supervisor
###############################################################################
FROM php:${PHP_VERSION}-fpm-bookworm AS runtime

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1

# --- System packages ------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        procps \
        unzip \
        git \
        curl \
        ca-certificates \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libmagickwand-dev \
        default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# --- PHP extensions -----------------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        sockets \
        opcache \
    && pecl install redis imagick \
    && docker-php-ext-enable redis imagick

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- PHP / opcache config ----------------------------------------------
COPY docker/php.ini      /usr/local/etc/php/conf.d/zz-stackposts.ini
COPY docker/opcache.ini  /usr/local/etc/php/conf.d/zz-opcache.ini

# --- nginx + supervisor + entrypoint ---------------------------------
COPY docker/nginx.conf       /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/stackposts.conf
COPY docker/entrypoint.sh    /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint \
    && ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

WORKDIR /var/www/html

# vendor/ from the optional composer stage first, then the app source on top
COPY --chown=www-data:www-data --from=vendor /app/vendor ./vendor
COPY --chown=www-data:www-data app/ ./

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/stackposts.conf"]
