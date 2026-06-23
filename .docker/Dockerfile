FROM php:8.5-fpm-alpine

ARG UID
ARG APP_PATH

RUN apk --update --no-cache add \
    bash \
    git \
    curl \
    wget

# Create user and some useful stuff
RUN adduser -u ${UID} --disabled-password --gecos "" appuser
RUN mkdir /home/appuser/.ssh
RUN chown -R appuser:appuser /home/appuser/
RUN echo "StrictHostKeyChecking no" >> /home/appuser/.ssh/config
RUN echo "export COLUMNS=300" >> /home/appuser/.bashrc
RUN echo "alias console='php ${APP_PATH}/bin/console'" >> /home/appuser/.bashrc


# Install PHP extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions opcache \
                           intl \
                           pdo_mysql \
                           pdo_sqlsrv \
                           gd \
                           ftp \
                           xdebug \
                           bcmath

ENV TZ=Europe/Madrid
# Alpine usa tzdata para manejar las zonas horarias
RUN apk add --no-cache tzdata && \
    cp /usr/share/zoneinfo/$TZ /etc/localtime && \
    echo $TZ > /etc/timezone

COPY ./xdebug.ini "${PHP_INI_DIR}/conf.d"

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Install and update composer
RUN curl https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer
RUN composer self-update

## Install Symfony binary
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash && \
    apk add symfony-cli

RUN rm -rf /var/cache/apk/*

COPY ./openssl.cnf /etc/ssl/openssl.cnf

RUN mkdir ${APP_PATH}

WORKDIR ${APP_PATH}

