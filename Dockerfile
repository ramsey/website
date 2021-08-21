FROM php:8.0-fpm

EXPOSE 8080
WORKDIR /app
ENV PATH "$PATH:/usr/local/go/bin"

# Install and customize Caddy.
RUN curl -1sLfO 'https://golang.org/dl/go1.17.linux-amd64.tar.gz' \
    && tar -C /usr/local -xzf go1.17.linux-amd64.tar.gz \
    && rm go1.17.linux-amd64.tar.gz \
    && apt-get update -qq \
    && apt-get install -qq debian-keyring debian-archive-keyring apt-transport-https \
    && curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' \
        | tee /etc/apt/trusted.gpg.d/caddy-stable.asc \
    && curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' \
        | tee /etc/apt/sources.list.d/caddy-stable.list \
    && apt-get update -qq \
    && apt-get install -qq caddy \
    && curl -1sLfO 'https://github.com/caddyserver/xcaddy/releases/download/v0.1.9/xcaddy_0.1.9_linux_amd64.deb' \
    && dpkg -i xcaddy_0.1.9_linux_amd64.deb \
    && rm xcaddy_0.1.9_linux_amd64.deb \
    && xcaddy build --with github.com/baldinof/caddy-supervisor \
    && dpkg-divert --divert /usr/bin/caddy.default --rename /usr/bin/caddy \
    && mv ./caddy /usr/bin/caddy.custom \
    && update-alternatives --install /usr/bin/caddy caddy /usr/bin/caddy.default 10 \
    && update-alternatives --install /usr/bin/caddy caddy /usr/bin/caddy.custom 50

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
RUN rm -rf /app/vendor /app/build \
    && rm /app/config/autoload/development.local.php /app/config/development.config.php \
    && mkdir -p /app/build/cache /app/build/logs \
    && composer install --no-dev --optimize-autoloader --prefer-dist --no-progress --quiet

ENTRYPOINT ["caddy", "run", "--config", "/app/resources/docker/Caddyfile"]
