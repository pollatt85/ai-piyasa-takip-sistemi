#!/usr/bin/env bash
# Kisa yol: sunucuyu baslatir (zaten calisiyorsa dokunmaz) ve son log satirlarini gosterir.
# Kullanim: bash start.sh
cd "$(dirname "$0")"
bash .devcontainer/post-start.sh
sleep 1
tail -5 /tmp/php-server.log 2>/dev/null || echo "Sunucu calisiyor: http://localhost:8123 (BAGLANTI NOKTALARI sekmesinden ac)"
