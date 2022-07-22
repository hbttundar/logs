#!/bin/bash
set -e
find /tmp/scripts/ -type f -name "*.sh" -exec bash -c '
    for filename do
       chmod +x "$filename" && mv "$filename" /usr/local/bin/
    done' bash {} +
rm -rf  /tmp/scripts/
cd ~ && ls -ltra
chown -R ${WWWUSER}:www-data /var/www/html
