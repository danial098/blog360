FROM php:8.1-apache

# Install mysqli
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite (optional, for clean URLs)
RUN a2enmod rewrite