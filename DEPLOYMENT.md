# Sunucuya Yayınlama (Deployment) Rehberi

## 1. Genel hazırlık

- **PHP** 8.2+, gerekli extension’lar (bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, tokenizer, xml)
- **Composer** ve **Node.js** (npm) kurulu
- **Veritabanı** (MySQL/PostgreSQL) hazır; `.env` içinde `DB_*` ayarları doğru

---

## 2. Projeyi sunucuya almak

```bash
git clone <repo> /var/www/ister
cd /var/www/ister
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

---

## 3. Ortam değişkenleri (.env) – Production

Aşağıdakileri **mutlaka** production’a göre düzenleyin:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://siteniz.com

# Veritabanı (sunucudaki gerçek bilgiler)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=ister
DB_USERNAME=...
DB_PASSWORD=...

# Oturum
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true

# Yayın (WebSocket – Reverb)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=ister
REVERB_APP_KEY=uzun-guvenli-key-uretin
REVERB_APP_SECRET=uzun-guvenli-secret-uretin
REVERB_HOST=siteniz.com
REVERB_PORT=8080
REVERB_SCHEME=https

# Frontend’in Reverb’e bağlanması için (Vite build’de kullanılır)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

- `REVERB_HOST`: Tarayıcının bağlanacağı adres (domain veya IP). **HTTPS kullanıyorsanız** `REVERB_SCHEME=https` ve genelde `REVERB_PORT=443` (veya reverse proxy’nin dinlediği port).
- `REVERB_APP_KEY` ve `REVERB_APP_SECRET`’i güçlü ve rastgele üretin (aynı değerleri `.env` ve Reverb config’te kullanın).

---

## 4. Frontend build (Vite)

Build’i **lokalde** veya **sunucuda** alabilirsiniz. Sunucuda alacaksanız Node kurulu olmalı:

```bash
npm ci
npm run build
```

Çıktılar `public/build/` altına yazılır. Bu dosyaları sunucuya commit edip atıyorsanız `npm run build`’i sunucuda çalıştırmanız gerekmez; yalnızca `.env` içindeki `VITE_REVERB_*` değerlerinin **build anında** doğru olması gerekir (production URL’i).

---

## 5. Laravel komutları

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

İzinler:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

(Web sunucunuzun kullanıcısı farklıysa `www-data` yerine onu yazın.)

---

## 6. Reverb (WebSocket) sürekli çalışsın

Reverb’ü arka planda sürekli çalıştırmak için **Supervisor** kullanmanız önerilir.

**Örnek Supervisor config** (`/etc/supervisor/conf.d/reverb-ister.conf`):

```ini
[program:reverb-ister]
process_name=%(program_name)s
command=php /var/www/ister/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/ister/storage/logs/reverb.log
stopwaitsecs=3600
```

Sonra:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb-ister
```

Reverb’ün dinlediği port (ör. 8080) firewall’da açık olmalı veya Nginx/Apache ile **reverse proxy** (aşağıda) kullanılmalı.

---

## 7. Nginx ile Reverb (WebSocket) proxy – HTTPS

Tarayıcı `wss://siteniz.com/app/reverb` gibi bir adrese bağlansın istiyorsanız, Nginx’te örnek blok:

```nginx
# HTTPS server
server {
    listen 443 ssl;
    server_name siteniz.com;
    root /var/www/ister/public;

    # ... ssl_certificate, ssl_certificate_key ...

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Reverb WebSocket proxy (Laravel Reverb 8080'de dinliyorsa)
    location /app/reverb {
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_pass http://127.0.0.1:8080;
    }
}
```

Bu durumda:

- Reverb sunucuda `php artisan reverb:start` ile **8080**’de çalışır.
- `.env`: `REVERB_HOST=siteniz.com`, `REVERB_PORT=443`, `REVERB_SCHEME=https`.
- Frontend’te bağlantı `wss://siteniz.com/app/reverb` olacak şekilde Reverb path’ini ayarlamanız gerekir (Laravel Reverb ve `config/reverb.php` / `path` ile uyumlu olmalı).

Path’i değiştirmediyseniz varsayılan `/app` olabilir; dokümana göre `REVERB_SERVER_PATH` ile oynanabilir. Bu örnekte `/app/reverb` kullanıldı.

---

## 8. Kuyruk (Queue) çalışıyorsa

Uygulama queue kullanıyorsa (ör. `QUEUE_CONNECTION=database`), worker’ı da sürekli çalıştırın (Supervisor ile):

```ini
[program:ister-worker]
process_name=%(program_name)s
command=php /var/www/ister/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/ister/storage/logs/worker.log
```

---

## 9. Özet kontrol listesi

| Adım | Yapıldı mı? |
|------|-------------|
| `.env` production (APP_ENV, APP_DEBUG, APP_URL, DB_*, REVERB_*, VITE_REVERB_*) | ☐ |
| `composer install --no-dev` | ☐ |
| `php artisan key:generate` | ☐ |
| `npm run build` (ve build’te doğru REVERB host/port/scheme) | ☐ |
| `php artisan migrate --force` | ☐ |
| `config:cache`, `route:cache`, `view:cache`, `storage:link` | ☐ |
| `storage` ve `bootstrap/cache` izinleri | ☐ |
| Reverb Supervisor ile sürekli çalışıyor | ☐ |
| Gerekirse Nginx/Apache WebSocket proxy | ☐ |
| Gerekirse queue worker (Supervisor) | ☐ |

Bunları yaptığınızda sunucuda yayınlama için gereken temel adımlar tamamlanmış olur. Reverb veya path ile ilgili özel bir senaryonuz varsa (farklı port, path, SSL) bir sonraki adımda sadece o kısmı birlikte netleştirebiliriz.
