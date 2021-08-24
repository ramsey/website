FROM php:8.0-apache

EXPOSE 8080
WORKDIR /app

# Configure Apache.
COPY resources/docker/website.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod headers rewrite deflate

# Configure PHP and install Composer.
COPY resources/docker/composer-install.sh /usr/bin/
RUN apt-get update -qq \
    && apt-get install -qq libzip-dev unzip \
    && docker-php-ext-install zip \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && /usr/bin/composer-install.sh \
    && mv composer.phar /usr/bin/composer \
    && rm /usr/bin/composer-install.sh

# Copy the app to the container, clean up any local dev files, and install dependencies.
COPY . /app/
RUN mkdir -p /app/build/cache /app/build/logs \
    && composer install --no-dev --optimize-autoloader --prefer-dist --no-progress --quiet
