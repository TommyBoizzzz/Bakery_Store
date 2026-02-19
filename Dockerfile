# Use official PHP 8.2 image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Copy project files to container
COPY . .

# Install mysqli extension for MySQL
RUN docker-php-ext-install mysqli

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Install PHP dependencies from composer.json
RUN composer install --no-dev --optimize-autoloader

# Enable Apache mod_rewrite (optional)
RUN a2enmod rewrite

# Expose default Apache port
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]
