#!/usr/bin/env bash
set -e

# sqlite3/pdo_sqlite ve dom/xml genelde php devcontainer imajında hazır gelir;
# yine de eksikse (Debian/Ubuntu tabanlı imaj) kurulumu tamamla.
if ! php -m | grep -qi '^sqlite3$'; then
    sudo apt-get update -y
    sudo apt-get install -y php-sqlite3 php-xml
fi

mkdir -p data
if [ ! -f data/app.sqlite ]; then
    php scripts/migrate.php
fi
