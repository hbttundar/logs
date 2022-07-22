FROM composer:latest
LABEL maintainer="Hossein Bagherzadegan Talkhouncheh"

WORKDIR /var/www/html
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN apk add --no-cache  --update  autoconf pcre-dev ${PHPIZE_DEPS}
RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis
CMD [ "install", "--ignore-platform-reqs"]
