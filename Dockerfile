# Dockerfile
FROM php:8.1-apache

# Install dependencies for PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite
