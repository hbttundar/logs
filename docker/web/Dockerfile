FROM ubuntu:22.04

LABEL maintainer="Hosein Bagherzadegan Talkhouncheh"

WORKDIR /var/www/html

ARG APP_ENV
ARG PHP_VERSION
ARG INSTALL_XDEBUG
ARG DEFAULT_WEB_SERVER
# Configure non-root user.
ARG WWWGROUP
ARG WWWUSER

ENV APP_ENV=${APP_ENV}
ENV WWWGROUP=${WWWGROUP:-1000}
ENV WWWUSER=${WWWUSER:-1000}
ENV PHP_VERSION=${PHP_VERSION:-8.1}
ENV DEFAULT_WEB_SERVER=${DEFAULT_WEB_SERVER:-nginx}

#
# copy all files that we need
#
COPY self-certificate/server.crt /etc/ssl/certs/server.crt
COPY self-certificate/server.key /etc/ssl/private/server.key
COPY ${PHP_VERSION}/php/php.ini /etc/php/${PHP_VERSION}/cli/conf.d/99-interview.ini
COPY ${PHP_VERSION}/apache/supervisord.conf /tmp/config/supervisord_apache2.conf
COPY ${PHP_VERSION}/nginx/supervisord.conf /tmp/config/supervisord_nginx.conf
#
# copy all script files that we need
#
COPY ./${PHP_VERSION}/build.sh /tmp/
COPY scripts/ /tmp/scripts/
RUN chmod +x /tmp/build.sh && chmod +x /tmp/scripts/permission.sh && chmod +x /tmp/scripts/addgroup.sh
RUN bash /tmp/scripts/addgroup.sh
RUN bash /tmp/build.sh
#
# copy  web server configs
#
COPY ${PHP_VERSION}/apache/config/  /etc/apache2/
COPY ${PHP_VERSION}/nginx/config/  /etc/nginx
#
# Set the privileges for our built app executable to run on privileged ports
#
RUN update-ca-certificates
RUN setcap "cap_net_bind_service=+ep" /usr/bin/php${PHP_VERSION}

# set permissions
RUN sh /tmp/scripts/permission.sh

EXPOSE 80 443

ENTRYPOINT ["start.sh"]
