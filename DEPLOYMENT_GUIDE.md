# راهنمای ساده استقرار پروکسی PHP روی Ubuntu VPS

## مشخصات سرور
- **RAM**: 1GB
- **CPU**: 1 هسته
- **پهنای باند**: نامحدود
- **هدف**: فقط پروکسی ویدیو

---

## مرحله 1: اتصال و به‌روزرسانی اولیه

```bash
# اتصال به سرور
ssh root@185.235.196.22

# به‌روزرسانی سیستم
apt update && apt upgrade -y

# نصب پکیج‌های ضروری
apt install -y curl wget git unzip nginx php8.1-fpm php8.1-curl php8.1-mbstring certbot python3-certbot-nginx ufw fail2ban
```

---

## مرحله 2: پیکربندی Nginx (سبک‌تر از Apache)

```bash
# حذف سایت پیش‌فرض
rm /etc/nginx/sites-enabled/default

# ایجاد فایل سایت پروکسی
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
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
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

# فعال کردن سایت
ln -s /etc/nginx/sites-available/proxy /etc/nginx/sites-enabled/
```

---

## مرحله 3: پیکربندی PHP برای پروکسی

```bash
# ایجاد دایرکتوری سایت
mkdir -p /var/www/proxy

# تنظیم PHP برای فایل‌های بزرگ
cat > /etc/php/8.1/fpm/conf.d/99-proxy.ini << 'EOF'
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
post_max_size = 10G
upload_max_filesize = 10G
max_file_uploads = 100
output_buffering = 4096
implicit_flush = On
EOF

# تنظیم PHP-FPM برای عملکرد بهتر
sed -i 's/pm = dynamic/pm = ondemand/' /etc/php/8.1/fpm/pool.d/www.conf
sed -i 's/pm.max_children = 5/pm.max_children = 10/' /etc/php/8.1/fpm/pool.d/www.conf
sed -i 's/pm.start_servers = 2/pm.start_servers = 1/' /etc/php/8.1/fpm/pool.d/www.conf
sed -i 's/pm.min_spare_servers = 1/pm.min_spare_servers = 0/' /etc/php/8.1/fpm/pool.d/www.conf
sed -i 's/pm.max_spare_servers = 3/pm.max_spare_servers = 1/' /etc/php/8.1/fpm/pool.d/www.conf
```

---

## مرحله 4: آپلود فایل‌های پروکسی

```bash
# رفتن به دایرکتوری سایت
cd /var/www/proxy

# کپی فایل‌های پروکسی (از طریق SCP یا wget)
# اگر فایل‌ها در GitHub هستند:
wget https://raw.githubusercontent.com/your-repo/main/proxy.php
wget https://raw.githubusercontent.com/your-repo/main/config.php
wget https://raw.githubusercontent.com/your-repo/main/test_proxy.html

# یا آپلود دستی از کامپیوتر محلی:
# scp proxy.php config.php test_proxy.html root@185.235.196.22:/var/www/proxy/

# تنظیم مجوزها
chown -R www-data:www-data /var/www/proxy
chmod -R 755 /var/www/proxy
chmod 644 /var/www/proxy/*.php
chmod 644 /var/www/proxy/*.html

# ایجاد دایرکتوری لاگ
mkdir -p /var/www/proxy/logs
chown www-data:www-data /var/www/proxy/logs
chmod 755 /var/www/proxy/logs
```

---

## مرحله 5: نصب SSL

```bash
# دریافت گواهی SSL
certbot --nginx -d filmkhabar.space -d www.filmkhabar.space --non-interactive --agree-tos --email your-email@example.com

# تنظیم تجدید خودکار
echo "0 12 * * * /usr/bin/certbot renew --quiet" | crontab -
```

---

## مرحله 6: پیکربندی فایروال

```bash
# تنظیم UFW
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

# تنظیم Fail2ban
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

systemctl enable fail2ban
systemctl start fail2ban
```

---

## مرحله 7: راه‌اندازی سرویس‌ها

```bash
# راه‌اندازی PHP-FPM
systemctl enable php8.1-fpm
systemctl start php8.1-fpm

# راه‌اندازی Nginx
systemctl enable nginx
systemctl start nginx

# بررسی وضعیت
systemctl status nginx php8.1-fpm
```

---

## مرحله 8: تست و بهینه‌سازی

```bash
# تست اتصال
curl -I http://filmkhabar.space/proxy.php

# تست SSL
curl -I https://filmkhabar.space/proxy.php

# بررسی لاگ‌ها
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# بررسی استفاده از منابع
htop
```

---

## مرحله 9: بهینه‌سازی برای پهنای باند نامحدود

```bash
# تنظیمات شبکه برای عملکرد بهتر
cat >> /etc/sysctl.conf << 'EOF'
# تنظیمات TCP برای عملکرد بهتر
net.core.rmem_max = 16777216
net.core.wmem_max = 16777216
net.ipv4.tcp_rmem = 4096 87380 16777216
net.ipv4.tcp_wmem = 4096 65536 16777216
net.ipv4.tcp_congestion_control = bbr
net.core.default_qdisc = fq
EOF

# اعمال تنظیمات
sysctl -p

# تنظیمات Nginx برای عملکرد بهتر
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

```bash
# ایجاد اسکریپت نظارت
cat > /usr/local/bin/monitor-proxy.sh << 'EOF'
#!/bin/bash
echo "=== گزارش پروکسی $(date) ==="
echo "CPU: $(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)%"
echo "RAM: $(free -m | awk 'NR==2{printf "%.1f%%", $3*100/$2 }')"
echo "Disk: $(df -h | grep '/dev/vda1' | awk '{print $5}')"
echo "Connections: $(netstat -an | grep :80 | wc -l)"
echo "Bandwidth (last 5 min):"
iftop -t -s 5 -L 10
EOF

chmod +x /usr/local/bin/monitor-proxy.sh

# نصب iftop برای نظارت پهنای باند
apt install -y iftop
```

---

## دستورات مفید

```bash
# بررسی وضعیت سرویس‌ها
systemctl status nginx php8.1-fpm

# مشاهده لاگ‌های زنده
tail -f /var/log/nginx/access.log

# نظارت بر پهنای باند
iftop

# بررسی فایل‌های پروکسی
ls -la /var/www/proxy/

# تست پروکسی
curl "https://filmkhabar.space/proxy.php?url=https://sv1.cinetory.space/test.mp4"
```

---

## آدرس‌های مهم

- **پروکسی اصلی**: https://filmkhabar.space/proxy.php
- **صفحه تست**: https://filmkhabar.space/test_proxy.html
- **مثال استفاده**: `https://filmkhabar.space/proxy.php?url=https://sv1.cinetory.space/video.mp4`

---

## نکات مهم

✅ **بهینه شده برای 1GB RAM**: تنظیمات PHP و Nginx بهینه شده  
✅ **پشتیبانی از فایل‌های بزرگ**: تا 10GB  
✅ **پهنای باند نامحدود**: تنظیمات TCP بهینه شده  
✅ **امنیت پایه**: SSL، فایروال، Fail2ban  
✅ **نظارت ساده**: اسکریپت‌های نظارت  

### در صورت مشکل:
```bash
# بررسی لاگ‌ها
tail -f /var/log/nginx/error.log
tail -f /var/log/php8.1-fpm.log

# راه‌اندازی مجدد سرویس‌ها
systemctl restart nginx php8.1-fpm

# بررسی پورت‌ها
netstat -tlnp
```

سرور شما آماده است! پروکسی روی `https://filmkhabar.space/proxy.php` در دسترس خواهد بود. 