# راهنمای ساده استقرار پروکسی PHP روی Ubuntu VPS

## مشخصات سرور
- **RAM**: 1GB
- **CPU**: 1 هسته
- **پهنای باند**: نامحدود
- **هدف**: فقط پروکسی ویدیو
- **PHP**: 8.3
- **وب سرور**: Nginx

---

## مرحله 1: اتصال و به‌روزرسانی اولیه

### اتصال به سرور:
```bash
ssh root@185.235.196.22
```

### به‌روزرسانی سیستم:
```bash
apt update && apt upgrade -y
```

### نصب پکیج‌های ضروری:
```bash
apt install -y curl wget git unzip nginx php8.3-fpm php8.3-curl php8.3-mbstring php8.3-opcache certbot python3-certbot-nginx ufw fail2ban htop iftop net-tools
```

---

## مرحله 2: پیکربندی Nginx

### حذف سایت پیش‌فرض:
```bash
rm -f /etc/nginx/sites-enabled/default
```

### ایجاد فایل سایت پروکسی:
```bash
cat > /etc/nginx/sites-available/proxy << 'EOF'
server {
    listen 80;
    server_name filmkhabar.space www.filmkhabar.space;
    root /var/www/proxy;
    index proxy.php;

    # تنظیمات فایل‌های بزرگ
    client_max_body_size 10G;
    client_body_timeout 300s;
    client_header_timeout 300s;
    proxy_connect_timeout 300s;
    proxy_send_timeout 300s;
    proxy_read_timeout 300s;

    # تنظیمات عملکرد
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;
    
    # تنظیمات امنیتی
    server_tokens off;
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;

    location / {
        try_files $uri $uri/ /proxy.php?$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index proxy.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # تنظیمات برای فایل‌های بزرگ
        fastcgi_read_timeout 300s;
        fastcgi_send_timeout 300s;
        fastcgi_connect_timeout 300s;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    # مسدود کردن دسترسی به فایل‌های حساس
    location ~ /\. {
        deny all;
    }
    
    location ~ \.(htaccess|htpasswd|ini|log|sh|sql|conf)$ {
        deny all;
    }
}
EOF
```

### فعال کردن سایت:
```bash
ln -sf /etc/nginx/sites-available/proxy /etc/nginx/sites-enabled/
```

### تست پیکربندی Nginx:
```bash
nginx -t
```

---

## مرحله 3: پیکربندی PHP برای پروکسی

### ایجاد دایرکتوری سایت:
```bash
mkdir -p /var/www/proxy
```

### تنظیم PHP برای فایل‌های بزرگ:
```bash
cat > /etc/php/8.3/fpm/conf.d/99-proxy.ini << 'EOF'
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
post_max_size = 10G
upload_max_filesize = 10G
max_file_uploads = 100
output_buffering = 4096
implicit_flush = On
EOF
```

### تنظیم PHP-FPM برای عملکرد بهتر:
```bash
sed -i 's/pm = dynamic/pm = ondemand/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/pm.max_children = 5/pm.max_children = 10/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/pm.start_servers = 2/pm.start_servers = 1/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/pm.min_spare_servers = 1/pm.min_spare_servers = 0/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/pm.max_spare_servers = 3/pm.max_spare_servers = 1/' /etc/php/8.3/fpm/pool.d/www.conf
```

### بررسی وجود socket PHP-FPM:
```bash
ls -la /var/run/php/php8.3-fpm.sock
```

---

## مرحله 4: آپلود فایل‌های پروکسی

### رفتن به دایرکتوری سایت:
```bash
cd /var/www/proxy
```

### آپلود فایل‌های پروکسی (انتخاب یکی از روش‌ها):

#### روش 1: آپلود مستقیم از GitHub (اگر فایل‌ها در GitHub هستند):
```bash
wget -O proxy.php https://raw.githubusercontent.com/your-repo/main/proxy.php
wget -O config.php https://raw.githubusercontent.com/your-repo/main/config.php
wget -O test_proxy.html https://raw.githubusercontent.com/your-repo/main/test_proxy.html
```

#### روش 2: آپلود دستی از کامپیوتر محلی:
```bash
# در ترمینال محلی خود اجرا کنید:
scp proxy.php config.php test_proxy.html root@185.235.196.22:/var/www/proxy/
```

#### روش 3: ایجاد فایل‌ها مستقیماً روی سرور:
```bash
# ایجاد فایل proxy.php
cat > /var/www/proxy/proxy.php << 'EOF'
<?php
require_once 'config.php';

// تنظیمات اولیه
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/var/www/proxy/logs/error.log');

// بررسی درخواست
if (!isset($_GET['url'])) {
    http_response_code(400);
    die('خطا: پارامتر URL مورد نیاز است');
}

$url = $_GET['url'];

// بررسی مجوز هاست
$parsed_url = parse_url($url);
if (!in_array($parsed_url['host'], $allowed_hosts)) {
    http_response_code(403);
    die('خطا: این هاست مجاز نیست');
}

// بررسی نوع فایل
$extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
if (!in_array($extension, $allowed_extensions)) {
    http_response_code(403);
    die('خطا: این نوع فایل مجاز نیست');
}

// تنظیم headers
$headers = [
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    'Accept: */*',
    'Accept-Language: en-US,en;q=0.9',
    'Accept-Encoding: gzip, deflate',
    'Connection: keep-alive',
    'Upgrade-Insecure-Requests: 1'
];

// اضافه کردن Range header برای resume
if (isset($_SERVER['HTTP_RANGE'])) {
    $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
}

// ایجاد context
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => implode("\r\n", $headers),
        'timeout' => 300,
        'follow_location' => true,
        'max_redirects' => 5
    ]
]);

// دریافت فایل
$file_content = @file_get_contents($url, false, $context);

if ($file_content === false) {
    http_response_code(500);
    die('خطا: نتوانستیم فایل را دریافت کنیم');
}

// دریافت headers پاسخ
$response_headers = $http_response_header ?? [];

// ارسال headers
foreach ($response_headers as $header) {
    if (strpos($header, 'HTTP/') === 0) {
        continue; // Skip status line
    }
    header($header);
}

// اضافه کردن headers امنیتی
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// ارسال محتوا
echo $file_content;
EOF
```

### تنظیم مجوزها:
```bash
chown -R www-data:www-data /var/www/proxy
chmod -R 755 /var/www/proxy
chmod 644 /var/www/proxy/*.php
chmod 644 /var/www/proxy/*.html
```

### ایجاد دایرکتوری لاگ:
```bash
mkdir -p /var/www/proxy/logs
chown www-data:www-data /var/www/proxy/logs
chmod 755 /var/www/proxy/logs
```

---

## مرحله 5: نصب SSL

### دریافت گواهی SSL:
```bash
certbot --nginx -d filmkhabar.space -d www.filmkhabar.space --non-interactive --agree-tos --email your-email@example.com
```

### تنظیم تجدید خودکار:
```bash
echo "0 12 * * * /usr/bin/certbot renew --quiet" | crontab -
```

---

## مرحله 6: پیکربندی فایروال

### تنظیم UFW:
```bash
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable
```

### تنظیم Fail2ban:
```bash
cat > /etc/fail2ban/jail.local << 'EOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3

[nginx-http-auth]
enabled = true
filter = nginx-http-auth
port = http,https
logpath = /var/log/nginx/error.log
maxretry = 3
EOF
```

### راه‌اندازی Fail2ban:
```bash
systemctl enable fail2ban
systemctl start fail2ban
```

---

## مرحله 7: راه‌اندازی سرویس‌ها

### راه‌اندازی PHP-FPM:
```bash
systemctl enable php8.3-fpm
systemctl start php8.3-fpm
```

### راه‌اندازی Nginx:
```bash
systemctl enable nginx
systemctl start nginx
```

### بررسی وضعیت:
```bash
systemctl status nginx php8.3-fpm
```

---

## مرحله 8: تست و بهینه‌سازی

### تست اتصال:
```bash
curl -I http://filmkhabar.space/proxy.php
```

### تست SSL:
```bash
curl -I https://filmkhabar.space/proxy.php
```

### تست پروکسی:
```bash
curl -I "https://filmkhabar.space/proxy.php?url=https://sv1.cinetory.space/test.mp4"
```

### بررسی لاگ‌ها:
```bash
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log
tail -f /var/www/proxy/logs/error.log
```

### بررسی استفاده از منابع:
```bash
htop
```

---

## مرحله 9: بهینه‌سازی برای پهنای باند نامحدود

### تنظیمات شبکه برای عملکرد بهتر:
```bash
cat >> /etc/sysctl.conf << 'EOF'
# تنظیمات TCP برای عملکرد بهتر
net.core.rmem_max = 16777216
net.core.wmem_max = 16777216
net.ipv4.tcp_rmem = 4096 87380 16777216
net.ipv4.tcp_wmem = 4096 65536 16777216
net.ipv4.tcp_congestion_control = bbr
net.core.default_qdisc = fq
EOF
```

### اعمال تنظیمات:
```bash
sysctl -p
```

### تنظیمات Nginx برای عملکرد بهتر:
```bash
# Backup فایل اصلی
cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.backup

# اضافه کردن تنظیمات به nginx.conf
cat >> /etc/nginx/nginx.conf << 'EOF'
# تنظیمات worker
worker_processes auto;
worker_rlimit_nofile 65536;

events {
    worker_connections 1024;
    use epoll;
    multi_accept on;
}

http {
    # تنظیمات buffer
    client_body_buffer_size 128k;
    client_header_buffer_size 1k;
    large_client_header_buffers 4 4k;
    
    # تنظیمات timeout
    client_body_timeout 300s;
    client_header_timeout 300s;
    keepalive_timeout 65;
    send_timeout 300s;
    
    # تنظیمات sendfile
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
}
EOF
```

---

## مرحله 10: نظارت ساده

### ایجاد اسکریپت نظارت:
```bash
cat > /usr/local/bin/monitor-proxy.sh << 'EOF'
#!/bin/bash
echo "=== گزارش پروکسی $(date) ==="
echo "CPU: $(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)%"
echo "RAM: $(free -m | awk 'NR==2{printf "%.1f%%", $3*100/$2 }')"
echo "Disk: $(df -h | grep '/dev/vda1' | awk '{print $5}')"
echo "Connections: $(netstat -an | grep :80 | wc -l)"
echo "PHP-FPM Status:"
systemctl status php8.3-fpm --no-pager -l
echo "Nginx Status:"
systemctl status nginx --no-pager -l
EOF
```

### تنظیم مجوز اسکریپت:
```bash
chmod +x /usr/local/bin/monitor-proxy.sh
```

---

## دستورات مفید

### بررسی وضعیت سرویس‌ها:
```bash
systemctl status nginx php8.3-fpm
```

### مشاهده لاگ‌های زنده:
```bash
tail -f /var/log/nginx/access.log
```

### نظارت بر پهنای باند:
```bash
iftop
```

### بررسی فایل‌های پروکسی:
```bash
ls -la /var/www/proxy/
```

### تست پروکسی:
```bash
curl "https://filmkhabar.space/proxy.php?url=https://sv1.cinetory.space/test.mp4"
```

### اجرای اسکریپت نظارت:
```bash
/usr/local/bin/monitor-proxy.sh
```

### بررسی socket PHP-FPM:
```bash
ls -la /var/run/php/php8.3-fpm.sock
```

### تست پیکربندی Nginx:
```bash
nginx -t
```

---

## آدرس‌های مهم

- **پروکسی اصلی**: https://filmkhabar.space/proxy.php
- **صفحه تست**: https://filmkhabar.space/test_proxy.html
- **مثال استفاده**: `https://filmkhabar.space/proxy.php?url=https://sv1.cinetory.space/video.mp4`

---

## عیب‌یابی

### مشکل: PHP-FPM کار نمی‌کند
```bash
systemctl status php8.3-fpm
journalctl -u php8.3-fpm -f
```

### مشکل: Nginx خطا می‌دهد
```bash
nginx -t
systemctl status nginx
tail -f /var/log/nginx/error.log
```

### مشکل: فایل‌ها آپلود نمی‌شوند
```bash
ls -la /var/www/proxy/
chown -R www-data:www-data /var/www/proxy
```

### مشکل: SSL کار نمی‌کند
```bash
certbot certificates
certbot renew --dry-run
```

---

## نکات مهم

✅ **بهینه شده برای 1GB RAM**: تنظیمات PHP و Nginx بهینه شده  
✅ **پشتیبانی از فایل‌های بزرگ**: تا 10GB  
✅ **پهنای باند نامحدود**: تنظیمات TCP بهینه شده  
✅ **امنیت پایه**: SSL، فایروال، Fail2ban  
✅ **PHP 8.3**: آخرین نسخه پایدار  
✅ **نظارت خودکار**: اسکریپت‌های نظارت و لاگ‌گیری  
✅ **عیب‌یابی آسان**: دستورات مفید برای تشخیص مشکلات  