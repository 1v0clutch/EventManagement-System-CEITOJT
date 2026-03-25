<<<<<<< HEAD
FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libonig-dev libxml2-dev \
    libpq-dev libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (pgsql for Render PostgreSQL)
RUN docker-php-ext-install \
    pdo pdo_pgsql pgsql \
    mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first (layer caching)
COPY composer.json composer.lock ./

# Install dependencies (no dev, optimized)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy the rest of the app
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Run boot commands + start server
CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan migrate --force && \
    php artisan storage:link && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
=======
FROM php:8.2-apache

# Install dependencies and extensions
RUN apt-get update && apt-get install -y libzip-dev zip unzip \
    && docker-php-ext-install pdo_mysql zip \
    && a2enmod rewrite

# Setup Apache document root to point to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy the app files into the container
COPY . /var/www/html

# Install application dependencies
RUN composer install --no-dev --optimize-autoloader

# Give PHP (www-data) permission over storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Create an entrypoint script that runs migrations before starting Apache
RUN echo '#!/bin/bash\nphp artisan config:cache \nphp artisan migrate --force\napache2-foreground' > /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

CMD ["/usr/local/bin/entrypoint.sh"]
>>>>>>> 1369ecc084243a8b0b992cae321ce869b016898d
