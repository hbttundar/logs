#!/bin/bash
export DEBIAN_FRONTEND=noninteractive
#set the timezone for tzdata in ubuntu otherwise during installation ask for select timezone
export TZ=UTC
ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ >/etc/timezone
apt-get update
apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor sqlite3 libcap2-bin libpng-dev python2 msmtp
mkdir -p ~/.gnupg
chmod 600 ~/.gnupg
echo "disable-ipv6" >>~/.gnupg/dirmngr.conf
apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys "0xE5267A6C"
apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys "0xC300EE8C"
echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu jammy main" >/etc/apt/sources.list.d/ppa_ondrej_php.list
apt-get update
apt-get install -y php8.1 php8.1-fpm php8.1-common php8.1-cli php8.1-dev \
  php8.1-pgsql php8.1-sqlite3 php8.1-gd \
  php8.1-curl \
  php8.1-imap php8.1-mysql php8.1-mbstring \
  php8.1-xml php8.1-zip php8.1-bcmath php8.1-soap \
  php8.1-intl php8.1-readline \
  php8.1-ldap php8.1-xmlreader php8.1-xmlwriter php8.1-opcache php8.1-msgpack php8.1-igbinary php8.1-redis php8.1-swoole \
  php8.1-memcached php8.1-pcov libapache2-mod-php8.1 libapache2-mod-fcgid ssl-cert openssl
php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer
curl -sL https://deb.nodesource.com/setup_$NODE_VERSION.x | bash - apt-get install -y nodejs && npm install -g npm
curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - echo "deb https://dl.yarnpkg.com/debian/ stable main" >/etc/apt/sources.list.d/yarn.list
apt-get update
apt-get install -y yarn
apt-get install -y mysql-client
apt-get install -y postgresql-client
if [ "$APP_ENV" = "dev" ]; then
  if [ "$INSTALL_XDEBUG" = "true" ]; then
    echo '-----------------------------------------------------------------------------------'
    echo '------------- you select to install x-debug ---------------------------------------'
    echo '-----------------------------------------------------------------------------------'
    apt-get install -yq --no-install-recommends php8.1-xdebug
  fi
fi
echo '-----------------------------------------------------------------------------------'
echo '--------------------------- config web server -------------------------------------'
echo '-----------------------------------------------------------------------------------'
if [ "$DEFAULT_WEB_SERVER" = "apache2" ]; then
  echo 'start to config apache2 server'
  echo -e "${YELLOW}install apache2 server and config it"
  apt-get install -yq apache2 \
    a2enmod rewrite \
    a2enmod headers \
    a2enmod expires \
    a2enmod ssl \
    a2enmod proxy \
    a2enmod proxy_http \
    a2enmod proxy_connect \
    a2enmod proxy_fcgi setenvif \
    a2enmod http2 \
    a2enmod remoteip \
    a2enmod deflate
  a2enmod php${PHP_VERSION} \
    a2enconf ssl-params
  cp /tmp/config/supervisord_apache2.conf /etc/supervisor/conf.d/supervisord.conf
  echo -e "${YELLOW}apache2 install sucessfully"
fi
if [ "$DEFAULT_WEB_SERVER" = "nginx" ]; then
  echo 'start to config nginx server'
  echo -e "${YELLOW}install nginx server and config it"
  apt-get install -yq net-tools nginx
  cp /tmp/config/supervisord_nginx.conf /etc/supervisor/conf.d/supervisord.conf
  echo -e "${YELLOW}nginx install sucessfully"
fi
echo '-----------------------------------------------------------------------------------'
echo '-------------------------- clean ubuntu container ---------------------------------'
echo '-----------------------------------------------------------------------------------'
rm -rf /tmp/config
apt-get -y autoremove && apt-get clean && rm -rf /var/lib/apt/lists/*
