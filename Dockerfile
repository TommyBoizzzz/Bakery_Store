# Use official PHP 8.2 image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install required system packages and PHP extensions
RUN apt-get update && apt-get install -y \
    zip unzip curl git libzip-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip \
    && docker-php-ext-enable pdo_pgsql pgsql

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Create assets folder and give Apache permissions
RUN mkdir -p /var/www/html/assets/images \
    && chown -R www-data:www-data /var/www/html/assets \
    && chmod -R 755 /var/www/html/assets

# Expose Apache port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]