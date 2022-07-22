#!/bin/bash
set -e
groupadd --force -g $WWWGROUP lon
useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u 1337 lon
usermod -a -G root lon && usermod -a -G $WWWGROUP lon
