# =====================================================================
# php-libs : PHP CLI + ekstensi yang dibutuhkan aplikasi + Composer.
# Dipakai sebagai basis stage `vendor` dan `frontend`.
# =====================================================================
FROM php:8.5-cli AS php-libs
ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        \
        libjpeg-dev \
        libonig-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install exif
RUN docker-php-ext-install gd
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install zip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# =====================================================================
# vendor : satu `composer install` dipakai oleh frontend & runtime.
# =====================================================================
FROM php-libs AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts \
    && php artisan package:discover --ansi

# =====================================================================
# frontend : build aset Vite. Plugin @laravel/vite-plugin-wayfinder
# memanggil `php artisan wayfinder:generate`, jadi perlu PHP + Node.
# =====================================================================
FROM php-libs AS frontend
ARG DEBIAN_FRONTEND=noninteractive
ENV NODE_VERSION=v24.18.0
RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        xz-utils \
    && curl -fsSLO https://nodejs.org/dist/${NODE_VERSION}/node-${NODE_VERSION}-linux-x64.tar.xz \
    && tar -xJf node-${NODE_VERSION}-linux-x64.tar.xz -C /usr/local/lib \
    && ln -sf /usr/local/lib/node-${NODE_VERSION}-linux-x64/bin/node /usr/local/bin/node \
    && ln -sf /usr/local/lib/node-${NODE_VERSION}-linux-x64/bin/npm /usr/local/bin/npm \
    && ln -sf /usr/local/lib/node-${NODE_VERSION}-linux-x64/bin/npx /usr/local/bin/npx \
    && rm node-${NODE_VERSION}-linux-x64.tar.xz \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# =====================================================================
# runtime : PHP-FPM + Nginx dalam satu image.
# =====================================================================
FROM php:8.5-fpm AS runtime
ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        \
        libjpeg-dev \
        libonig-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        nginx \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install exif
RUN docker-php-ext-install gd
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install zip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build public/build

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
RUN rm -f /etc/nginx/sites-enabled/default
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 80 9000
ENTRYPOINT ["/usr/local/bin/entrypoint"]