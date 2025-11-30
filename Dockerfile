# Multi-stage build for TokenLite Laravel Application
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    build-base \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    vim \
    unzip \
    git \
    curl \
    oniguruma-dev \
    libxml2-dev \
    icu-dev \
    autoconf \
    g++ \
    make

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install \
    bcmath \
    mbstring \
    pdo \
    pdo_mysql \
    xml \
    gd \
    zip \
    intl \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/tokenlite_app

# Production stage - optimized for deployment
FROM base AS production

# Install jq for JSON manipulation (temporarily)
RUN apk add --no-cache jq

# Copy application files
COPY tokenlite/tokenlite_app/ .
COPY tokenlite/public/ ../public/

# Fix autoload configuration to include functions.php in production
RUN jq '.autoload.files = ["app/Helpers/functions.php"]' composer.json > composer.json.tmp && \
    mv composer.json.tmp composer.json

# Install PHP dependencies (production mode) including Predis for Redis support
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist && \
    composer require predis/predis --no-interaction

# Backup vendor directory for volume initialization
RUN cp -r vendor /tmp/vendor

# Remove jq to reduce image size
RUN apk del jq

# Set proper permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/tokenlite_app/storage \
    && chmod -R 775 /var/www/tokenlite_app/bootstrap/cache

# Create startup script
RUN echo '#!/bin/sh' > /startup.sh && \
    echo 'set -e' >> /startup.sh && \
    echo '' >> /startup.sh && \
    echo '# Copy vendor to volume if it does not exist (first run)' >> /startup.sh && \
    echo 'if [ ! -d "/var/www/tokenlite_app/vendor/bin" ]; then' >> /startup.sh && \
    echo '  echo "Copying vendor dependencies to volume..."' >> /startup.sh && \
    echo '  cp -r /tmp/vendor/* /var/www/tokenlite_app/vendor/ 2>/dev/null || true' >> /startup.sh && \
    echo 'fi' >> /startup.sh && \
    echo '' >> /startup.sh && \
    echo '# Generate APP_KEY if not set' >> /startup.sh && \
    echo 'if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then' >> /startup.sh && \
    echo '  echo "Generating application key..."' >> /startup.sh && \
    echo '  php artisan key:generate --force' >> /startup.sh && \
    echo 'fi' >> /startup.sh && \
    echo '' >> /startup.sh && \
    echo '# Wait for database to be ready' >> /startup.sh && \
    echo 'echo "Waiting for database connection..."' >> /startup.sh && \
    echo 'until php artisan migrate:status >/dev/null 2>&1; do' >> /startup.sh && \
    echo '  echo "Database not ready, waiting..."' >> /startup.sh && \
    echo '  sleep 2' >> /startup.sh && \
    echo 'done' >> /startup.sh && \
    echo '' >> /startup.sh && \
    echo '# Run database migrations' >> /startup.sh && \
    echo 'echo "Running database migrations..."' >> /startup.sh && \
    echo 'php artisan migrate --force' >> /startup.sh && \
    echo '' >> /startup.sh && \
    echo '# Clear and cache configuration for production' >> /startup.sh && \
    echo 'echo "Optimizing application..."' >> /startup.sh && \
    echo 'php artisan config:cache' >> /startup.sh && \
    echo 'php artisan route:cache' >> /startup.sh && \
    echo 'php artisan view:cache' >> /startup.sh && \
    echo '' >> /startup.sh && \
    echo '# Set proper permissions' >> /startup.sh && \
    echo 'chown -R www-data:www-data /var/www/tokenlite_app/storage /var/www/tokenlite_app/bootstrap/cache' >> /startup.sh && \
    echo 'chmod -R 775 /var/www/tokenlite_app/storage /var/www/tokenlite_app/bootstrap/cache' >> /startup.sh && \
    echo '' >> /startup.sh && \
    echo 'echo "Application setup completed. Starting PHP-FPM..."' >> /startup.sh && \
    echo 'exec "$@"' >> /startup.sh && \
    chmod +x /startup.sh

# PHP-FPM configuration
RUN echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini
RUN echo 'max_execution_time = 300' >> /usr/local/etc/php/conf.d/docker-php-maxtime.ini
RUN echo 'upload_max_filesize = 50M' >> /usr/local/etc/php/conf.d/docker-php-upload.ini
RUN echo 'post_max_size = 50M' >> /usr/local/etc/php/conf.d/docker-php-upload.ini

# OpCache configuration for production
RUN echo 'opcache.memory_consumption=128' >> /usr/local/etc/php/conf.d/opcache-recommended.ini \
    && echo 'opcache.interned_strings_buffer=8' >> /usr/local/etc/php/conf.d/opcache-recommended.ini \
    && echo 'opcache.max_accelerated_files=4000' >> /usr/local/etc/php/conf.d/opcache-recommended.ini \
    && echo 'opcache.revalidate_freq=2' >> /usr/local/etc/php/conf.d/opcache-recommended.ini \
    && echo 'opcache.fast_shutdown=1' >> /usr/local/etc/php/conf.d/opcache-recommended.ini \
    && echo 'opcache.enable_cli=1' >> /usr/local/etc/php/conf.d/opcache-recommended.ini

# Expose port 9000 and start php-fpm server
EXPOSE 9000
ENTRYPOINT ["/startup.sh"]
CMD ["php-fpm"]

# Development image
FROM base AS development

# Install Node.js and npm for development, plus linux-headers for xdebug
RUN apk add --no-cache \
    nodejs \
    npm \
    linux-headers

COPY tokenlite/tokenlite_app/ .
COPY tokenlite/public/ ../public/

# Install PHP dependencies (including dev dependencies) and Predis
RUN composer install --optimize-autoloader --no-interaction --no-progress --prefer-dist && \
    composer require predis/predis --no-interaction

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/tokenlite_app/storage \
    && chmod -R 775 /var/www/tokenlite_app/bootstrap/cache

# Install Node.js dependencies
RUN if [ -f "package.json" ]; then npm install; fi

# Create startup script for development
RUN echo '#!/bin/sh' > /startup.sh && \
    echo 'set -e' >> /startup.sh && \
    echo '' >> /startup.sh && \
    echo '# Generate APP_KEY if not set' >> /startup.sh && \
    echo 'if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then' >> /startup.sh && \
    echo '  echo "Generating application key..."' >> /startup.sh && \
    echo '  php artisan key:generate --force' >> /startup.sh && \
    echo 'fi' >> /startup.sh && \
    echo '' >> /startup.sh && \
    echo '# Set proper permissions' >> /startup.sh && \
    echo 'chown -R www-data:www-data /var/www/tokenlite_app/storage /var/www/tokenlite_app/bootstrap/cache' >> /startup.sh && \
    echo 'chmod -R 775 /var/www/tokenlite_app/storage /var/www/tokenlite_app/bootstrap/cache' >> /startup.sh && \
    echo '' >> /startup.sh && \
    echo 'echo "Development setup completed. Starting PHP-FPM..."' >> /startup.sh && \
    echo 'exec "$@"' >> /startup.sh && \
    chmod +x /startup.sh

# Development PHP configuration
RUN echo 'memory_limit = 1G' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini
RUN echo 'max_execution_time = 0' >> /usr/local/etc/php/conf.d/docker-php-maxtime.ini
RUN echo 'upload_max_filesize = 100M' >> /usr/local/etc/php/conf.d/docker-php-upload.ini
RUN echo 'post_max_size = 100M' >> /usr/local/etc/php/conf.d/docker-php-upload.ini

# Note: Xdebug installation commented out due to PECL repository issues
# To enable Xdebug for debugging, uncomment and modify as needed:
# RUN pecl install xdebug && docker-php-ext-enable xdebug

EXPOSE 9000
ENTRYPOINT ["/startup.sh"]
CMD ["php-fpm"]