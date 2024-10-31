# Use the official PHP 8.2 image with Apache
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    pkg-config \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo mbstring gd

# Install MongoDB PHP driver
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy the Laravel project files to the container
COPY . .

# Install Laravel dependencies with memory limit
RUN composer clear-cache && \
    COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader --no-interaction

# Set the proper permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy .env file to container (optional: if secrets are managed in Render)
COPY .env .env

# Generate application key if not set in .env
RUN php artisan key:generate

# Clear and cache configuration
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Configure Apache to serve from Laravel's public directory
RUN echo '<VirtualHost *:10000>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Enable the rewrite module
RUN a2enmod rewrite

# Set the ServerName to suppress the warning
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Expose port 10000 for Render
EXPOSE 10000

# Start Apache on port 10000

CMD ["apache2-foreground", "-D", "FOREGROUND", "-c", "Listen 10000"]

