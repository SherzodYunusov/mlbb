#!/bin/bash
set -e

echo "======================================"
echo "  MLBB Market — Server Deploy Script"
echo "======================================"

# ── 1. TIZIM YANGILASH ──
echo ""
echo "[1/9] Tizim yangilanmoqda..."
apt update -qq && apt upgrade -y -qq

# ── 2. KERAKLI PAKETLAR ──
echo ""
echo "[2/9] PHP 8.2, Nginx, MySQL o'rnatilmoqda..."
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update -qq
apt install -y nginx mysql-server \
    php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath \
    php8.2-tokenizer php8.2-intl php8.2-cli \
    unzip git curl certbot python3-certbot-nginx

# ── 3. COMPOSER ──
echo ""
echo "[3/9] Composer o'rnatilmoqda..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# ── 4. MySQL DATABASE ──
echo ""
echo "[4/9] MySQL database yaratilmoqda..."
DB_PASS=$(openssl rand -base64 20 | tr -dc 'a-zA-Z0-9' | head -c 20)
mysql -e "CREATE DATABASE IF NOT EXISTS mlbb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'mlbb_user'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON mlbb.* TO 'mlbb_user'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
echo "DB_PASS=${DB_PASS}" > /root/.mlbb_db_pass
echo "Database yaratildi. Parol: ${DB_PASS}"

# ── 5. LOYIHANI YUKLAB OLISH ──
echo ""
echo "[5/9] GitHub dan loyiha yuklanmoqda..."
mkdir -p /var/www
cd /var/www
rm -rf mlbb
git clone https://github.com/SherzodYunusov/mlbb.git mlbb
cd /var/www/mlbb

# ── 6. .env SOZLASH ──
echo ""
echo "[6/9] .env fayl yaratilmoqda..."
cat > /var/www/mlbb/.env << ENV
APP_NAME="MLBB Market"
APP_ENV=production
APP_KEY=base64:uBfMNkaA0d90bRPWMMilIx8fmAp/N4WVWUjsmnpFOW8=
APP_DEBUG=false
APP_URL=https://acc-bazar.uz

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mlbb
DB_USERNAME=mlbb_user
DB_PASSWORD=${DB_PASS}

QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=file

TELEGRAM_BOT_TOKEN=8286326182:AAHhyfTE3b1DmXqZyHO6f_2gGrVRpILZfgc
TELEGRAM_ADMIN_CHANNEL_ID=-1003993629130
TELEGRAM_ADMIN_ID=1404555107
TELEGRAM_TEST_ID=1404555107
TELEGRAM_DEALS_GROUP_ID=-1003616588260
TELEGRAM_ADMIN_USERNAME=@shyunusovv
TELEGRAM_BOT_USERNAME=@MLBB_acc_bot
ENV

# ── 7. LARAVEL SOZLASH ──
echo ""
echo "[7/9] Laravel sozlanmoqda..."
cd /var/www/mlbb
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

chown -R www-data:www-data /var/www/mlbb
chmod -R 755 /var/www/mlbb
chmod -R 775 /var/www/mlbb/storage /var/www/mlbb/bootstrap/cache

# ── 8. NGINX KONFIGURATSIYA ──
echo ""
echo "[8/9] Nginx sozlanmoqda..."
cat > /etc/nginx/sites-available/mlbb << 'NGINX'
server {
    listen 80;
    server_name acc-bazar.uz www.acc-bazar.uz;
    root /var/www/mlbb/public;
    index index.php;

    client_max_body_size 50M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINX

ln -sf /etc/nginx/sites-available/mlbb /etc/nginx/sites-enabled/mlbb
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# ── 9. SUPERVISOR (QUEUE WORKER) ──
echo ""
echo "[9/9] Queue worker va Scheduler sozlanmoqda..."
apt install -y supervisor

cat > /etc/supervisor/conf.d/mlbb-queue.conf << 'SUPERVISOR'
[program:mlbb-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/mlbb/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/mlbb/storage/logs/queue.log
stopwaitsecs=3600
SUPERVISOR

supervisorctl reread
supervisorctl update
supervisorctl start mlbb-queue:*

# Scheduler (cron)
(crontab -l 2>/dev/null; echo "* * * * * www-data php /var/www/mlbb/artisan schedule:run >> /dev/null 2>&1") | crontab -

echo ""
echo "======================================"
echo "  Asosiy sozlash TAYYOR!"
echo "======================================"
echo ""
echo "Keyingi qadam — SSL sertifikat:"
echo "  certbot --nginx -d acc-bazar.uz -d www.acc-bazar.uz --non-interactive --agree-tos -m sherzodyunusovdev@gmail.com"
echo ""
echo "Keyin webhook:"
echo "  php /var/www/mlbb/artisan app:set-webhook"
echo ""
