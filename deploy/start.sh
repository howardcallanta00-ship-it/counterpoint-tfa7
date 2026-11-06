#!/bin/sh
set -eu
: "${PORT:=10000}"
export PORT
case "$PORT" in *[!0-9]*|'') echo 'PORT must be numeric' >&2; exit 1;; esac
test "$PORT" -ge 1024 && test "$PORT" -le 65535
test -n "${APP_KEY:-}" || { echo 'APP_KEY must be set as a persistent Render secret.' >&2; exit 1; }
test "${DB_CONNECTION:-}" = 'pgsql' || { echo 'Production requires durable PostgreSQL.' >&2; exit 1; }
test "${PUBLIC_DISK_DRIVER:-}" = 's3' || { echo 'Production requires durable S3-compatible public storage.' >&2; exit 1; }
for name in DB_HOST DB_DATABASE DB_USERNAME DB_PASSWORD AWS_ACCESS_KEY_ID AWS_SECRET_ACCESS_KEY AWS_DEFAULT_REGION AWS_BUCKET AWS_ENDPOINT AWS_URL APP_URL; do
    test -n "$(printenv "$name")" || { echo "Missing required environment variable: $name" >&2; exit 1; }
done
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
exec apache2-foreground
