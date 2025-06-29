# 🚀 راهنمای استقرار پروکسی PHP

راهنمای کامل استقرار پروکسی PHP برای دور زدن محدودیت‌های دانلود در ایران.

## 🌐 دامنه‌های پروژه

- **سرور پروکسی (ایران)**: `tr.modulogic.space`
- **سرور منبع (آلمان)**: `sv1.neurobuild.space`
- **IP سرور ایران**: `45.12.143.141`

## 📋 پیش‌نیازها

### سرور
- Ubuntu 24.04 LTS
- حداقل 1GB RAM
- حداقل 10GB فضای دیسک
- دسترسی root

### دامنه
- دامنه `tr.modulogic.space` اشاره به IP سرور
- ایمیل معتبر برای گواهی SSL

## 🔧 نصب و راه‌اندازی

### مرحله 1: به‌روزرسانی سیستم

```bash
# به‌روزرسانی پکیج‌ها
sudo apt update && sudo apt upgrade -y

# نصب پکیج‌های ضروری
sudo apt install -y curl wget git unzip
```

### مرحله 2: نصب نرم‌افزارهای مورد نیاز

```bash
# نصب Nginx
sudo apt install -y nginx

# نصب PHP 8.3 و ماژول‌های مورد نیاز
sudo apt install -y php8.3-fpm php8.3-curl php8.3-mbstring php8.3-opcache php8.3-zip

# نصب Certbot برای SSL
sudo apt install -y certbot python3-certbot-nginx

# نصب فایروال
sudo apt install -y ufw fail2ban
```

### مرحله 3: دانلود فایل‌های پروکسی

```bash
# ایجاد پوشه پروکسی
sudo mkdir -p /var/www/proxy
cd /var/www/proxy

# دانلود فایل‌های اصلی
sudo wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/proxy.php
sudo wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/config.php
sudo wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/test_proxy.html
sudo wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/link_rewriter.php
sudo wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/php_settings.ini
sudo wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/.htaccess

# دانلود فایل‌های سرور
sudo wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/deploy.sh
sudo wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/deploy-ubuntu24.sh

# دانلود فایل‌های پلاگین WordPress
sudo mkdir -p /var/www/proxy/wordpress-plugin
cd /var/www/proxy/wordpress-plugin
sudo wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/wordpress-plugin/auto-proxy-links.php
sudo wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/wordpress-plugin/uninstall.php

# دانلود فایل‌های admin
sudo mkdir -p /var/www/proxy/wordpress-plugin/admin
cd /var/www/proxy/wordpress-plugin/admin
sudo wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/wordpress-plugin/admin/admin-page.php

# دانلود فایل‌های assets
sudo mkdir -p /var/www/proxy/wordpress-plugin/assets/js
cd /var/www/proxy/wordpress-plugin/assets/js
sudo wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/wordpress-plugin/assets/js/auto-proxy-links.js

# دانلود فایل‌های languages
sudo mkdir -p /var/www/proxy/wordpress-plugin/languages
cd /var/www/proxy/wordpress-plugin/languages
sudo wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/wordpress-plugin/languages/auto-proxy-links-fa_IR.po

# بازگشت به پوشه اصلی
cd /var/www/proxy
```

### مرحله 4: تنظیم مجوزها

```bash
# تنظیم مالکیت
sudo chown -R www-data:www-data /var/www/proxy

# تنظیم مجوزها
sudo chmod -R 755 /var/www/proxy
sudo chmod 644 /var/www/proxy/*.php
sudo chmod 644 /var/www/proxy/*.html
sudo chmod 644 /var/www/proxy/.htaccess
sudo chmod 644 /var/www/proxy/php_settings.ini

# ایجاد پوشه لاگ
sudo mkdir -p /var/www/proxy/logs
sudo touch /var/www/proxy/logs/proxy_log.txt
sudo chmod 755 /var/www/proxy/logs
sudo chmod 666 /var/www/proxy/logs/proxy_log.txt
```

### مرحله 5: تنظیم Nginx

```bash
# حذف سایت پیش‌فرض
sudo rm -f /etc/nginx/sites-enabled/default

# ایجاد فایل سایت پروکسی
sudo tee /etc/nginx/sites-available/proxy > /dev/null << 'EOF'
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

# فعال‌سازی سایت
sudo ln -sf /etc/nginx/sites-available/proxy /etc/nginx/sites-enabled/

# تست تنظیمات Nginx
sudo nginx -t

# راه‌اندازی مجدد Nginx
sudo systemctl restart nginx
```

### مرحله 6: تنظیم PHP

```bash
# تنظیمات PHP برای فایل‌های بزرگ
sudo tee /etc/php/8.3/fpm/conf.d/99-proxy.ini > /dev/null << 'EOF'
; PHP settings for large files
memory_limit = 2G
max_execution_time = 0
max_input_time = 300
post_max_size = 10G
upload_max_filesize = 10G
max_file_uploads = 100
default_socket_timeout = 300
EOF

# بهینه‌سازی PHP-FPM
sudo sed -i 's/pm = dynamic/pm = ondemand/' /etc/php/8.3/fpm/pool.d/www.conf
sudo sed -i 's/pm.max_children = 5/pm.max_children = 10/' /etc/php/8.3/fpm/pool.d/www.conf
sudo sed -i 's/pm.start_servers = 2/pm.start_servers = 1/' /etc/php/8.3/fpm/pool.d/www.conf
sudo sed -i 's/pm.min_spare_servers = 1/pm.min_spare_servers = 0/' /etc/php/8.3/fpm/pool.d/www.conf
sudo sed -i 's/pm.max_spare_servers = 3/pm.max_spare_servers = 1/' /etc/php/8.3/fpm/pool.d/www.conf

# راه‌اندازی مجدد PHP-FPM
sudo systemctl restart php8.3-fpm
```

### مرحله 7: تنظیم SSL

```bash
# دریافت گواهی SSL
sudo certbot --nginx -d tr.modulogic.space --non-interactive --agree-tos --email your-email@example.com --quiet

# تنظیم تجدید خودکار SSL
echo "0 12 * * * /usr/bin/certbot renew --quiet" | sudo crontab -
```

### مرحله 8: تنظیم فایروال

```bash
# تنظیم UFW
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable

# تنظیم Fail2ban
sudo tee /etc/fail2ban/jail.local > /dev/null << 'EOF'
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

sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### مرحله 9: بهینه‌سازی سیستم

```bash
# بهینه‌سازی شبکه
sudo tee -a /etc/sysctl.conf > /dev/null << 'EOF'
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

# اعمال تنظیمات
sudo sysctl -p
```

### مرحله 10: ایجاد اسکریپت مانیتورینگ

```bash
# ایجاد اسکریپت مانیتورینگ
sudo tee /usr/local/bin/monitor-proxy.sh > /dev/null << 'EOF'
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

sudo chmod +x /usr/local/bin/monitor-proxy.sh
```

## 🧪 تست استقرار

### تست HTTP

```bash
# تست اتصال HTTP
curl -I http://tr.modulogic.space/proxy.php

# تست عملکرد پروکسی
curl "http://tr.modulogic.space/proxy.php?url=https://httpbin.org/status/200"
```

### تست HTTPS

```bash
# تست اتصال HTTPS
curl -I https://tr.modulogic.space/proxy.php

# تست عملکرد پروکسی HTTPS
curl "https://tr.modulogic.space/proxy.php?url=https://httpbin.org/status/200"
```

### تست صفحه

```bash
# تست صفحه تست
curl -I https://tr.modulogic.space/test_proxy.html
```

## 📊 مانیتورینگ و نگهداری

### دستورات مفید

```bash
# بررسی وضعیت سرویس‌ها
sudo systemctl status nginx php8.3-fpm

# راه‌اندازی مجدد سرویس‌ها
sudo systemctl restart nginx php8.3-fpm

# بررسی لاگ‌ها
sudo tail -f /var/www/proxy/logs/proxy_log.txt
sudo tail -f /var/log/nginx/error.log

# اجرای اسکریپت مانیتورینگ
sudo /usr/local/bin/monitor-proxy.sh

# بررسی گواهی SSL
sudo certbot certificates

# بررسی فایروال
sudo ufw status
```

### به‌روزرسانی خودکار

```bash
# ایجاد اسکریپت به‌روزرسانی
sudo tee /usr/local/bin/update-proxy.sh > /dev/null << 'EOF'
#!/bin/bash
cd /var/www/proxy
sudo wget -O proxy.php https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/proxy.php
sudo wget -O config.php https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/config.php
sudo wget -O test_proxy.html https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/test_proxy.html
sudo chown www-data:www-data *.php *.html
sudo chmod 644 *.php *.html
sudo systemctl reload nginx
echo "Proxy updated successfully!"
EOF

sudo chmod +x /usr/local/bin/update-proxy.sh
```

## 🔧 عیب‌یابی

### مشکلات رایج

#### خطای 502 Bad Gateway
```bash
# بررسی وضعیت PHP-FPM
sudo systemctl status php8.3-fpm

# بررسی لاگ‌های PHP-FPM
sudo tail -f /var/log/php8.3-fpm.log
```

#### خطای SSL
```bash
# بررسی گواهی SSL
sudo certbot certificates

# تجدید دستی گواهی
sudo certbot renew --dry-run
```

#### خطای مجوز
```bash
# تنظیم مجدد مجوزها
sudo chown -R www-data:www-data /var/www/proxy
sudo chmod -R 755 /var/www/proxy
```

## 📞 پشتیبانی

### آدرس‌های مهم

- **پروکسی**: https://tr.modulogic.space/proxy.php
- **صفحه تست**: https://tr.modulogic.space/test_proxy.html
- **مخزن GitHub**: https://github.com/ayroop/Auto-Link-Proxy

### لاگ‌های مهم

- `/var/www/proxy/logs/proxy_log.txt` - لاگ‌های پروکسی
- `/var/log/nginx/access.log` - لاگ‌های دسترسی Nginx
- `/var/log/nginx/error.log` - لاگ‌های خطای Nginx
- `/var/log/php8.3-fpm.log` - لاگ‌های PHP-FPM

---

**نکته**: این راهنما برای استقرار کامل و امن پروکسی PHP طراحی شده است. لطفاً تمام مراحل را با دقت دنبال کنید.