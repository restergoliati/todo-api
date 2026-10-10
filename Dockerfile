FROM php:8.2-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        xml \
        bcmath \
        zip \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]