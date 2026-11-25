#!/bin/bash
# Self-restarting Laravel dev server for the sandbox preview (port 3000).
cd "$(dirname "$0")"
while true; do
  exec 2>&1
  /home/z/bin/php artisan serve --host 0.0.0.0 --port 3000
  sleep 1
done
