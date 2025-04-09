FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    unzip \
    curl \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql zip

# Install and enable Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# ---------- INSTALL COMPOSER MANUALLY ----------
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html

# Install dependencies using Composer (including JWT)
RUN composer require firebase/php-jwt

# Copy default NGINX config
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Expose ports
EXPOSE 80
