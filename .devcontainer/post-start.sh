#!/usr/bin/env bash
# Codespace her başladığında çalışır. postStartCommand içindeki basit "&" arka
# plana atma güvenilir değil (hook shell'i kapanınca süreç de gidebiliyor);
# bu yüzden setsid ile oturumdan tamamen koparılıp nohup + disown ile bırakılıyor.
set -e

if pgrep -f "php -S 0.0.0.0:8123" > /dev/null; then
    exit 0
fi

cd "$(dirname "$0")/.."
setsid nohup php -S 0.0.0.0:8123 -t public public/router.php > /tmp/php-server.log 2>&1 < /dev/null &
disown
