FROM php:8.2-apache

WORKDIR /var/www/html

# Copy all files into container
COPY . .

# Install dependencies
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

# Enable Apache rewrite
RUN a2enmod rewrite

# Explicitly set DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80
CMD ["apache2-foreground"]