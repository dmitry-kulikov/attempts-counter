# syntax=docker/dockerfile:1.4.2-labs

# PHP version
# examples of allowed values: 5.6-cli, 5.6-cli-alpine, 7.4-cli, 7.4-cli-alpine
# and other tags from https://hub.docker.com/_/php
ARG PHP_VERSION=5.6-cli

########################################################################################################################
FROM php:${PHP_VERSION}

# use /bin/bash instead of default /bin/sh, because /bin/sh does not support `set -o pipefail`
SHELL ["/bin/bash", "-c"]

WORKDIR /usr/src/attempts-counter

RUN set -o errexit -o nounset -o pipefail -o xtrace; \
    \
    # install system packages
    apt-get update; \
    apt-get --assume-yes --no-install-recommends install \
        gnupg2; \
    apt-key update; \
    apt-get update; \
    apt-get --assume-yes --no-install-recommends install \
        git `# for Composer and developers` \
        nano `# for developers` \
        unzip `# for Composer`; \
    \
    # install PHP extensions
    curl --silent --show-error --location --output /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/download/1.5.20/install-php-extensions; \
    chmod a+x /usr/local/bin/install-php-extensions; \
    sync; \
    install-php-extensions \
        pcntl `# for tests` \
        xdebug `# for tests`; \
    \
    # install the latest stable Composer version
    curl --silent --show-error --location https://getcomposer.org/installer | php; \
    mv composer.phar /usr/local/bin/composer; \
    \
    # clean up
    rm --force --recursive /var/lib/apt/lists/* /tmp/* /var/tmp/*

# install dependencies using Composer
COPY composer.json ./
RUN --mount=type=cache,id=composer,target=/root/.composer/cache,sharing=locked \
    set -o errexit -o nounset -o pipefail -o xtrace; \
    composer update; \
    composer clear-cache
