FROM php:8.0-fpm

EXPOSE 8080
WORKDIR /app

RUN curl -1sLfO 'https://golang.org/dl/go1.17.linux-amd64.tar.gz' \
    && tar -C /usr/local -xzf go1.17.linux-amd64.tar.gz \
    && rm go1.17.linux-amd64.tar.gz

ENV PATH "$PATH:/usr/local/go/bin"

RUN apt update \
    && apt install -y debian-keyring debian-archive-keyring apt-transport-https \
    && curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' \
        | tee /etc/apt/trusted.gpg.d/caddy-stable.asc \
    && curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' \
        | tee /etc/apt/sources.list.d/caddy-stable.list \
    && apt update \
    && apt install -y caddy \
    && curl -1sLfO 'https://github.com/caddyserver/xcaddy/releases/download/v0.1.9/xcaddy_0.1.9_linux_amd64.deb' \
    && dpkg -i xcaddy_0.1.9_linux_amd64.deb \
    && rm xcaddy_0.1.9_linux_amd64.deb

RUN xcaddy build \
        --with github.com/baldinof/caddy-supervisor \
    && dpkg-divert --divert /usr/bin/caddy.default --rename /usr/bin/caddy \
    && mv ./caddy /usr/bin/caddy.custom \
    && update-alternatives --install /usr/bin/caddy caddy /usr/bin/caddy.default 10 \
    && update-alternatives --install /usr/bin/caddy caddy /usr/bin/caddy.custom 50

COPY . /app

RUN /app/resources/docker/composer-install.sh \
    && mv composer.phar /usr/bin/composer \
    && composer install --no-dev --optimize-autoloader

ENTRYPOINT ["caddy", "run", "--config", "/app/resources/docker/Caddyfile"]
