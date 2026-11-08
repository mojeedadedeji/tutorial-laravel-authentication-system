#!/usr/bin/env bash
#
# 5050 Logistics — one-command VPS deployment
# Target: Ubuntu 24.04, domain fifyfifylogistics.com
# Usage: bash deploy.sh
#
set -euo pipefail

APP_DIR=/var/www/5050
DOMAIN=fifyfifylogistics.com
DB_NAME=laravel_auth_prod
DB_USER=laravel
DB_PASS="${DB_PASSWORD:?Set DB_PASSWORD env var before running}"

echo "==> Updating system"
apt update && apt upgrade -y
apt install -y nginx mysql-server certbot python3-certbot-nginx git unzip curl ufw

echo "==> Installing PHP 8.4"
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.4-fpm php8.4-mysql php8.4-mbstring php8.4-xml \
    php8.4-curl php8.4-zip php8.4-bcmath php8.4-intl

echo "==> Installing Composer"
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

echo "==> Configuring MySQL"
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME};
          CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
          GRANT ALL ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
          FLUSH PRIVILEGES;"

echo "==> Deploying application"
mkdir -p "${APP_DIR}"
cd "${APP_DIR}"
if [ -d .git ]; then
    git pull origin main
else
    git clone https://github.com/mojeedadedeji/tutorial-laravel-authentication-system.git .
fi
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Setting up environment"
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi
# Inject production values (edit these or export before running)
sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
sed -i "s|^APP_URL=.*|APP_URL=https://${DOMAIN}|" .env
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env
sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|" .env
sed -i "s|^DB_HOST=.*|DB_HOST=127.0.0.1|" .env
sed -i "s|^DB_PORT=.*|DB_PORT=3306|" .env
sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=database|" .env

echo "==> Running migrations and caches"
php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "==> Configuring nginx"
cat > /etc/nginx/sites-available/5050 <<EOF
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${APP_DIR}/public;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* { deny all; }
}
EOF
ln -sf /etc/nginx/sites-available/5050 /etc/nginx/sites-enabled/5050
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

echo "==> Firewall"
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

echo "==> SSL certificate"
certbot --nginx -d "${DOMAIN}" -d "www.${DOMAIN}" --non-interactive --agree-tos -m "admin@${DOMAIN}" || \
    echo "Certbot failed — ensure DNS A record points to this server, then run: certbot --nginx -d ${DOMAIN} -d www.${DOMAIN}"

echo "==> Queue worker (systemd)"
cat > /etc/systemd/system/laravel-worker.service <<EOF
[Unit]
Description=Laravel queue worker for 5050 Logistics
After=network.target mysql.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php ${APP_DIR}/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
EOF
systemctl daemon-reload
systemctl enable --now laravel-worker

echo "==> Scheduler cron"
(crontab -l 2>/dev/null | grep -v 'artisan schedule:run' || true; \
 echo "* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab -

echo ""
echo "=============================================="
echo " Deploy complete!"
echo " Site: https://${DOMAIN}"
echo ""
echo " NEXT STEPS:"
echo " 1. Edit ${APP_DIR}/.env — set MAIL_*, PAYSTACK_* keys"
echo " 2. php artisan config:cache   (after editing .env)"
echo " 3. Queue worker 'laravel-worker' is running (systemctl status laravel-worker)"
echo "=============================================="
