# ────────────────────────────────────────────────────────────────
#  BAB'S RESTO — Production Dockerfile
#  Base: php:8.4-apache  (Apache runs on $PORT, default 80)
#  Tested on: Railway, local Docker Compose
# ────────────────────────────────────────────────────────────────

FROM php:8.4-apache

# ── System packages & PHP extensions ────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl \
        libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install \
        pdo pdo_mysql mbstring zip gd opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# ── PHP runtime config ───────────────────────────────────────────
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

# ── Apache VirtualHost (DocumentRoot → /public) ──────────────────
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# ── Composer ─────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── Node.js 22 (for Vite asset build) ───────────────────────────
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# ── PHP dependencies (cached layer — only invalidated by lock file) ──
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader

# ── Copy application source ──────────────────────────────────────
COPY . .

# ── Run Composer post-install scripts now that source is present ─
RUN composer dump-autoload --optimize --no-dev

# ── Frontend assets (Vite build) ─────────────────────────────────
# npm ci uses package-lock.json for reproducible installs
RUN npm ci && npm run build && rm -rf node_modules

# ── Storage & cache permissions ───────────────────────────────────
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ── Entrypoint: handles PORT, migrations, caches ─────────────────
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Railway routes external traffic → this port
EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
