# Use official PHP image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Copy all project files into the container
COPY . .

# Install mysqli extension for MySQL
RUN docker-php-ext-install mysqli

# Enable mod_rewrite (optional, only if you use URL rewriting)
RUN a2enmod rewrite

# Expose default Apache port
EXPOSE 80
