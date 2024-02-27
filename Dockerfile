FROM composer:2.4.4 AS composer
FROM php:8.2

ENV TZ=Europe/Warsaw

WORKDIR /app
EXPOSE 80

COPY . /app
RUN apt-get update  \
    && apt-get install -q -y \
        nano \
        curl \
        git \
        libzip-dev \
        unzip \
        librabbitmq-dev \
        libssh-dev \
    && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone \
    && pecl install xdebug redis \
    && docker-php-ext-install \
        pdo_mysql \
        bcmath \
        sockets \
    && docker-php-ext-install zip \
    && docker-php-ext-enable redis && docker-php-ext-enable xdebug \
    && pecl install amqp \
    && docker-php-ext-enable amqp

HEALTHCHECK --start-period=1m --interval=10s CMD curl --fail --silent -o /dev/null http://127.0.0.1:80/ || exit 1
ENTRYPOINT [ "/app/bin/entrypoint.sh" ]
