#!/bin/bash
set -e

# ── Ensure .env exists (Railway injects vars as OS env, artisan still needs the file) ──
if [ ! -f .env ]; then
    cp .env.example .env
fi

# ── Railway injects $PORT; Apache must listen on that port ─────────────────
APP_PORT="${PORT:-80}"

# Rewrite the Listen directive in Apache's ports.conf
sed -i "s/Listen 80/Listen ${APP_PORT}/" /etc/apache2/ports.conf

# Rewrite the VirtualHost port in the site config
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${APP_PORT}>/" \
    /etc/apache2/sites-available/000-default.conf

# ── Generate APP_KEY if not present ────────────────────────────────────────
if [ -z "${APP_KEY}" ]; then
    echo "[entrypoint] No APP_KEY found — generating one..."
    php artisan key:generate --force
fi

# ── Create storage symlink (public/storage → storage/app/public) ───────────
php artisan storage:link --force 2>/dev/null || true

# ── Run database migrations ─────────────────────────────────────────────────
echo "[entrypoint] Running migrations..."
php artisan migrate --force

# ── Cache config/routes/views in production ────────────────────────────────
if [ "${APP_ENV}" = "production" ]; then
    echo "[entrypoint] Caching for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

# ── Force mpm_prefork (NodeSource install re-enables mpm_event at build time) ──
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

echo "[entrypoint] Starting Apache on port ${APP_PORT}..."
exec "$@"
