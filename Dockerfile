FROM php:8.2-fpm

RUN apt-get update && apt-get install -y --no-install-recommends openssl && rm -rf /var/lib/apt/lists/*

RUN apt-get update && apt-get install -y iputils-ping

RUN apt-get update && apt-get install -y \
    zlib1g-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    zip \
    curl \
    unzip \
    wget \
    htop \
    git \
    nano \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache xml \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && pecl install excimer \
    && docker-php-ext-enable excimer \
    && docker-php-source delete

RUN echo $PHP_INI_DIR
# CA sertifikası kopyala
COPY cacert.pem /usr/local/etc/php/cacert.pem
RUN echo $PHP_INI_DIR

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

RUN chown -R www-data:www-data /var/www

COPY . /var/www

RUN composer install --optimize-autoloader --no-dev
# RUN composer install --no-dev

# RUN  composer require laravel/horizon

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache


RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN sed -i 's/post_max_size = 8M/post_max_size = 100M/' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' "$PHP_INI_DIR/php.ini"

RUN sed -i "s|;curl.cainfo =|curl.cainfo = /usr/local/etc/php|" "$PHP_INI_DIR/php.ini"
RUN sed -i "s|;curl.cainfo =|curl.cainfo = /usr/local/etc/php/cacert.pem|" "$PHP_INI_DIR/php.ini-development"

# /etc/ssl/certs/ca-certificates.crt
RUN sed -i 's|;openssl.cafile=|openssl.cafile=/etc/ssl/certs/ca-certificates.crt|' "$PHP_INI_DIR/php.ini"
RUN sed -i 's|;openssl.capath=|openssl.capath=/etc/ssl/certs|' "$PHP_INI_DIR/php.ini"

#RUN sed -i 's/memory_limit = 128M/memory_limit = 256M/' "$PHP_INI_DIR/php.ini"
RUN sed -i 's/memory_limit = 128M/memory_limit = 512M/' "$PHP_INI_DIR/php.ini"


# RUN php artisan horizon:install

CMD php artisan serve --host=0.0.0.0 --port=9000
# CMD php artisan serve --host=0.0.0.0

EXPOSE 9000
