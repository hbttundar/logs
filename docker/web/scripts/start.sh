#!/bin/bash
set -e

# shellcheck disable=SC1073
if [ ! -z "$WWWUSER" ]; then
  usermod -u $WWWUSER lon
fi

if [ ! -d /.composer ]; then
  mkdir /.composer
fi

chmod -R ugo+rw /.composer

echo "start supervisord ..."
/usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
echo "supervisord started."
