# 🚀 راهنمای استقرار پروکسی PHP - مرحله به مرحله

راهنمای کامل استقرار پروکسی PHP برای دور زدن محدودیت‌های دانلود در ایران.

## 🌐 دامنه‌های پروژه

- **سرور پروکسی (ایران)**: `tr.modulogic.space`
- **سرور منبع (آلمان)**: `sv1.netwisehub.space`
- **IP سرور ایران**: `45.12.143.141`

## 📋 پیش‌نیازها

- سرور Ubuntu 24.04 LTS
- حداقل 1GB RAM
- دسترسی root
- دامنه `tr.modulogic.space` اشاره به IP سرور
- ایمیل معتبر برای گواهی SSL

---

## مرحله 1: اتصال به سرور

```bash
ssh root@45.12.143.141
```

---

## مرحله 2: به‌روزرسانی سیستم

```bash
apt update && apt upgrade -y
```

```bash
apt install -y curl wget git unzip
```

---

## مرحله 3: نصب نرم‌افزارهای مورد نیاز

```bash
apt install -y nginx
```

```bash
apt install -y php8.3-fpm php8.3-curl php8.3-mbstring php8.3-opcache php8.3-zip
```

```bash
apt install -y certbot python3-certbot-nginx
```

```bash
apt install -y ufw fail2ban
```

---

## مرحله 4: ایجاد پوشه پروکسی

```bash
mkdir -p /var/www/proxy
cd /var/www/proxy
```

---

## مرحله 5: دانلود فایل‌های اصلی

```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/proxy.php
```

```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/config.php
```

```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/test_proxy.html
```

```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/link_rewriter.php
```

```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/php_settings.ini
```

```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/.htaccess
```

---

## مرحله 6: دانلود فایل‌های سرور

```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/deploy.sh
```

```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/deploy-ubuntu24.sh
```

---

## مرحله 7: دانلود پلاگین WordPress

```bash
mkdir -p /var/www/proxy/wordpress-plugin
cd /var/www/proxy/wordpress-plugin
```

```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/wordpress-plugin/auto-proxy-links.php
```

```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/wordpress-plugin/uninstall.php
```

```bash
mkdir -p /var/www/proxy/wordpress-plugin/admin
cd /var/www/proxy/wordpress-plugin/admin
```

```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/wordpress-plugin/admin/admin-page.php
```

```bash
mkdir -p /var/www/proxy/wordpress-plugin/assets/js
cd /var/www/proxy/wordpress-plugin/assets/js
```

```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/wordpress-plugin/assets/js/auto-proxy-links.js
```

```bash
mkdir -p /var/www/proxy/wordpress-plugin/languages
cd /var/www/proxy/wordpress-plugin/languages
```

```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/wordpress-plugin/languages/auto-proxy-links-fa_IR.po
```

```bash
cd /var/www/proxy
```

---

## مرحله 8: تنظیم مجوزها

```bash
chown -R www-data:www-data /var/www/proxy
```

```bash
chmod -R 755 /var/www/proxy
```

```bash
chmod 644 /var/www/proxy/*.php
```

```bash
chmod 644 /var/www/proxy/*.html
```

```bash
chmod 644 /var/www/proxy/.htaccess
```

```bash
chmod 644 /var/www/proxy/php_settings.ini
```

```bash
mkdir -p /var/www/proxy/logs
```

```bash
touch /var/www/proxy/logs/proxy_log.txt
```

```bash
chmod 755 /var/www/proxy/logs
```

```bash
chmod 666 /var/www/proxy/logs/proxy_log.txt
```

---

## مرحله 9: حذف سایت پیش‌فرض Nginx

```bash
rm -f /etc/nginx/sites-enabled/default
```

---

## مرحله 10: ایجاد فایل سایت پروکسی

```bash
cat > /etc/nginx/sites-available/proxy << 'EOF'
server {
    listen 80;
    server_name tr.modulogic.space;
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

---

## مرحله 11: فعال‌سازی سایت

```bash
ln -sf /etc/nginx/sites-available/proxy /etc/nginx/sites-enabled/
```

---

## مرحله 12: تست تنظیمات Nginx

```bash
nginx -t
```

---

## مرحله 13: تنظیم PHP برای فایل‌های بزرگ

```bash
cat > /etc/php/8.3/fpm/conf.d/99-proxy.ini << 'EOF'
; PHP settings for large files
memory_limit = 2G
max_execution_time = 0
max_input_time = 300
post_max_size = 10G
upload_max_filesize = 10G
max_file_uploads = 100
default_socket_timeout = 300
EOF
```

---

## مرحله 14: بهینه‌سازی PHP-FPM

```bash
sed -i 's/pm = dynamic/pm = ondemand/' /etc/php/8.3/fpm/pool.d/www.conf
```

```bash
sed -i 's/pm.max_children = 5/pm.max_children = 10/' /etc/php/8.3/fpm/pool.d/www.conf
```

```bash
sed -i 's/pm.start_servers = 2/pm.start_servers = 1/' /etc/php/8.3/fpm/pool.d/www.conf
```

```bash
sed -i 's/pm.min_spare_servers = 1/pm.min_spare_servers = 0/' /etc/php/8.3/fpm/pool.d/www.conf
```

```bash
sed -i 's/pm.max_spare_servers = 3/pm.max_spare_servers = 1/' /etc/php/8.3/fpm/pool.d/www.conf
```

---

## مرحله 15: راه‌اندازی مجدد PHP-FPM

```bash
systemctl restart php8.3-fpm
```

---

## مرحله 16: راه‌اندازی مجدد Nginx

```bash
systemctl restart nginx
```

---

## مرحله 17: تنظیم SSL (جایگزین your-email@example.com با ایمیل خود)

```bash
certbot --nginx -d tr.modulogic.space --non-interactive --agree-tos --email your-email@example.com --quiet
```

---

## مرحله 18: تنظیم تجدید خودکار SSL

```bash
echo "0 12 * * * /usr/bin/certbot renew --quiet" | crontab -
```

---

## مرحله 19: تنظیم فایروال

```bash
ufw default deny incoming
```

```bash
ufw default allow outgoing
```

```bash
ufw allow ssh
```

```bash
ufw allow 80/tcp
```

```bash
ufw allow 443/tcp
```

```bash
ufw --force enable
```

---

## مرحله 20: تنظیم Fail2ban

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

[nginx-botsearch]
enabled = true
filter = nginx-botsearch
port = http,https
logpath = /var/log/nginx/access.log
maxretry = 2
EOF
```

```bash
systemctl enable fail2ban
```

```bash
systemctl start fail2ban
```

---

## مرحله 21: بهینه‌سازی شبکه

```bash
cat >> /etc/sysctl.conf << 'EOF'
# TCP optimizations for unlimited bandwidth
net.core.rmem_max = 16777216
net.core.wmem_max = 16777216
net.ipv4.tcp_rmem = 4096 87380 16777216
net.ipv4.tcp_wmem = 4096 65536 16777216
net.ipv4.tcp_congestion_control = bbr
net.core.default_qdisc = fq
net.core.netdev_max_backlog = 5000
net.ipv4.tcp_max_syn_backlog = 2048
EOF
```

```bash
sysctl -p
```

---

## مرحله 22: ایجاد اسکریپت مانیتورینگ

```bash
cat > /usr/local/bin/monitor-proxy.sh << 'EOF'
#!/bin/bash
echo "=== Auto-Link-Proxy Status Report $(date) ==="
echo "CPU Usage: $(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)%"
echo "Memory Usage: $(free -m | awk 'NR==2{printf "%.1f%%", $3*100/$2 }')"
echo "Disk Usage: $(df -h | grep '/dev/vda1' | awk '{print $5}')"
echo "Active Connections: $(netstat -an | grep :80 | wc -l)"
echo "Nginx Status: $(systemctl is-active nginx)"
echo "PHP-FPM Status: $(systemctl is-active php8.3-fpm)"
echo "SSL Certificate: $(certbot certificates | grep -c 'VALID')"
echo "Firewall Status: $(ufw status | grep -c 'active')"
echo ""
echo "Recent Proxy Logs:"
tail -5 /var/www/proxy/logs/proxy_log.txt 2>/dev/null || echo "No logs found"
echo ""
echo "Recent Nginx Errors:"
tail -5 /var/log/nginx/error.log 2>/dev/null || echo "No errors found"
EOF
```

```bash
chmod +x /usr/local/bin/monitor-proxy.sh
```

---

## مرحله 23: تست استقرار

```bash
sleep 5
```

```bash
curl -I http://tr.modulogic.space/proxy.php
```

```bash
curl "http://tr.modulogic.space/proxy.php?url=https://httpbin.org/status/200"
```

```bash
curl -I http://tr.modulogic.space/test_proxy.html
```

---

## مرحله 24: تست HTTPS

```bash
curl -I https://tr.modulogic.space/proxy.php
```

```bash
curl "https://tr.modulogic.space/proxy.php?url=https://httpbin.org/status/200"
```

```bash
curl -I https://tr.modulogic.space/test_proxy.html
```

---

## ✅ استقرار کامل شد!

### آدرس‌های مهم:
- **پروکسی**: https://tr.modulogic.space/proxy.php
- **صفحه تست**: https://tr.modulogic.space/test_proxy.html

### دستورات مفید:

```bash
# بررسی وضعیت سرویس‌ها
systemctl status nginx php8.3-fpm

# راه‌اندازی مجدد سرویس‌ها
systemctl restart nginx php8.3-fpm

# بررسی لاگ‌ها
tail -f /var/www/proxy/logs/proxy_log.txt
tail -f /var/log/nginx/error.log

# اجرای اسکریپت مانیتورینگ
/usr/local/bin/monitor-proxy.sh

# بررسی گواهی SSL
certbot certificates

# بررسی فایروال
ufw status
```

### مثال استفاده:
```
لینک اصلی: https://sv1.netwisehub.space/video.mp4
لینک پروکسی: https://tr.modulogic.space/proxy.php?url=https%3A//sv1.netwisehub.space/video.mp4
```

---

**نکته**: تمام دستورات را مرحله به مرحله اجرا کنید. در صورت بروز خطا، لاگ‌ها را بررسی کنید.