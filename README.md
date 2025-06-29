# 🎬 پروکسی ویدیو - filmkhabar.space

یک سیستم کامل پروکسی برای عبور از محدودیت‌های دانلود فایل‌های ویدیو و سریال از طریق سرور ایرانی

## 📋 فهرست مطالب

- [معرفی](#معرفی)
- [ویژگی‌ها](#ویژگی‌ها)
- [🚀 استقرار خودکار (یک دستور)](#-استقرار-خودکار-یک-دستور)
- [نصب و راه‌اندازی](#نصب-و-راه‌اندازی)
- [تنظیمات](#تنظیمات)
- [استفاده](#استفاده)
- [WordPress Plugin](#wordpress-plugin)
- [امنیت](#امنیت)
- [عیب‌یابی](#عیب‌یابی)
- [پشتیبانی](#پشتیبانی)

## 🎯 معرفی

این پروژه یک سیستم کامل پروکسی است که فایل‌های ویدیو و سریال را از سرور خارجی `sv1.neurobuild.space` از طریق سرور ایرانی `filmkhabar.space` با IP اختصاصی `45.12.143.141` پروکسی می‌کند تا کاربران ایرانی بتوانند بدون محدودیت دانلود کنند.

### 🔧 اجزای سیستم

- **`proxy.php`**: اسکریپت اصلی پروکسی
- **`config.php`**: تنظیمات مرکزی
- **`link_rewriter.php`**: بازنویسی خودکار لینک‌ها
- **WordPress Plugin**: پلاگین خودکار برای وردپرس
- **فایل‌های تست**: برای بررسی عملکرد

## ✨ ویژگی‌ها

### 🚀 عملکرد
- **پشتیبانی از فایل‌های بزرگ**: تا 10GB
- **Resume دانلود**: پشتیبانی کامل از HTTP Range
- **سرعت بالا**: بافر بهینه و انتقال مستقیم
- **کش هوشمند**: کاهش بار سرور

### 🛡️ امنیت
- **فیلتر دامنه**: فقط دامنه‌های مجاز
- **فیلتر پسوند**: مسدود کردن فایل‌های خطرناک
- **لاگ کامل**: ثبت تمام فعالیت‌ها
- **محدودیت اندازه**: جلوگیری از سوء استفاده

### 🔗 یکپارچگی
- **WordPress Plugin**: تبدیل خودکار لینک‌ها
- **شورت‌کد**: استفاده آسان در محتوا
- **AJAX**: تست اتصال بدون reload
- **Responsive**: سازگار با همه دستگاه‌ها

## 🚀 استقرار خودکار (یک دستور)

### ⚡ نصب سریع با یک دستور

برای استقرار خودکار روی سرور Ubuntu VPS، فقط یک دستور کافی است:

```bash
curl -sSL https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/deploy.sh | sudo bash
```

### 📝 مراحل قبل از اجرا

**1. اتصال به سرور:**
```bash
ssh root@your-server-ip
```

**2. دانلود اسکریپت:**
```bash
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/deploy.sh
```

**3. تنظیم دامنه خود:**
```bash
sed -i 's/DOMAIN="filmkhabar.space"/DOMAIN="your-domain.com"/' deploy.sh
```

**4. تنظیم ایمیل خود:**
```bash
sed -i 's/EMAIL="your-email@example.com"/EMAIL="your-actual-email@example.com"/' deploy.sh
```

**5. اجرای استقرار:**
```bash
sudo bash deploy.sh
```

### 🎯 مثال کامل

```bash
# اتصال به سرور
ssh root@45.12.143.141

# دانلود اسکریپت
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/deploy.sh

# تنظیم دامنه (جایگزین کنید)
sed -i 's/DOMAIN="filmkhabar.space"/DOMAIN="myproxy.com"/' deploy.sh

# تنظیم ایمیل (جایگزین کنید)
sed -i 's/EMAIL="your-email@example.com"/EMAIL="admin@myproxy.com"/' deploy.sh

# اجرای استقرار
sudo bash deploy.sh
```

### 🔧 تنظیمات پیشرفته (اختیاری)

**تنظیم مسیر پروکسی:**
```bash
sed -i 's|PROXY_DIR="/var/www/proxy"|PROXY_DIR="/var/www/myproxy"|' deploy.sh
```

**تنظیم اندازه فایل حداکثر:**
```bash
sed -i 's/memory_limit = 2G/memory_limit = 4G/' deploy.sh
```

**تنظیم timeout:**
```bash
sed -i 's/max_execution_time = 0/max_execution_time = 600/' deploy.sh
```

### 🎯 آنچه به صورت خودکار نصب می‌شود

#### 🌐 سرویس‌های وب
- ✅ **Nginx** - وب سرور بهینه شده
- ✅ **PHP 8.1** - با تنظیمات فایل‌های بزرگ
- ✅ **SSL Certificate** - گواهی امنیتی خودکار
- ✅ **Firewall** - محافظت امنیتی

#### 🔧 تنظیمات بهینه
- ✅ **PHP-FPM** - بهینه شده برای 1GB RAM
- ✅ **TCP Optimizations** - برای پهنای باند نامحدود
- ✅ **File Size Support** - تا 10GB
- ✅ **Caching** - کش ویدیو فایل‌ها

#### 📁 فایل‌های پروژه
- ✅ **proxy.php** - اسکریپت اصلی
- ✅ **config.php** - تنظیمات مرکزی
- ✅ **test_proxy.html** - صفحه تست
- ✅ **link_rewriter.php** - بازنویسی لینک
- ✅ **php_settings.ini** - تنظیمات PHP

#### 🛡️ امنیت
- ✅ **UFW Firewall** - فایروال ساده
- ✅ **Fail2ban** - محافظت از حملات
- ✅ **SSL/TLS** - رمزگذاری کامل
- ✅ **CORS Headers** - برای استریم ویدیو

### 📊 نظارت و مدیریت

#### دستورات مفید پس از نصب:
```bash
# بررسی وضعیت سیستم
/usr/local/bin/monitor-proxy.sh

# مشاهده لاگ‌ها
tail -f /var/www/proxy/logs/proxy_log.txt

# بررسی سرویس‌ها
systemctl status nginx php8.1-fpm

# بررسی فایروال
ufw status
```

#### تست عملکرد:
```bash
# تست پروکسی
curl "https://your-domain.com/proxy.php?url=https://sv1.cinetory.space/test.mp4"

# تست صفحه
curl -I https://your-domain.com/test_proxy.html
```

### 🔄 به‌روزرسانی خودکار

اسکریپت شامل:
- ✅ **SSL Auto-renewal** - تمدید خودکار گواهی
- ✅ **System Updates** - به‌روزرسانی خودکار سیستم
- ✅ **Log Rotation** - مدیریت خودکار لاگ‌ها

### ⚠️ نکات مهم

1. **حداقل مشخصات سرور:**
   - RAM: 1GB
   - CPU: 1 هسته
   - فضای ذخیره: 20GB
   - پهنای باند: نامحدود

2. **پیش‌نیازها:**
   - Ubuntu 18.04 یا بالاتر
   - دسترسی root
   - دامنه فعال
   - ایمیل معتبر

3. **پس از نصب:**
   - فایل `config.php` را ویرایش کنید
   - دامنه‌های مجاز را تنظیم کنید
   - تست با فایل‌های واقعی انجام دهید

### 🆘 عیب‌یابی سریع

```bash
# اگر سرویس‌ها کار نمی‌کنند:
systemctl restart nginx php8.1-fpm

# اگر SSL مشکل دارد:
certbot --nginx -d your-domain.com

# اگر فایروال مسدود کرده:
ufw allow 80/tcp && ufw allow 443/tcp

# بررسی لاگ‌های خطا:
tail -f /var/log/nginx/error.log
```

---

## 📦 نصب و راه‌اندازی (روش دستی)

### پیش‌نیازها

```bash
# PHP 7.4 یا بالاتر
php -v

# افزونه‌های PHP مورد نیاز
- curl
- openssl
- mbstring
```

### 1. آپلود فایل‌ها

```bash
# آپلود فایل‌های اصلی
proxy.php
config.php
link_rewriter.php
test_proxy.html

# آپلود تنظیمات PHP
php_settings.ini
.htaccess
```

### 2. تنظیم مجوزها

   ```bash
# تنظیم مجوز فایل لاگ
chmod 666 proxy_log.txt

# تنظیم مجوز دایرکتوری
chmod 755 /path/to/proxy/
```

### 3. تست اولیه

```bash
# تست اتصال
curl -I https://filmkhabar.space/proxy.php

# تست فایل نمونه
curl -I https://filmkhabar.space/proxy.php/test.mp4
```

## ⚙️ تنظیمات

### فایل `config.php`

```php
// تنظیمات دامنه
define('SOURCE_DOMAIN', 'sv1.neurobuild.space');
define('PROXY_DOMAIN', 'filmkhabar.space');
define('PROXY_IP', '45.12.143.141');

// تنظیمات فایل‌های بزرگ
define('MAX_FILE_SIZE', 10 * 1024 * 1024 * 1024); // 10GB
define('CHUNK_SIZE', 1024 * 1024); // 1MB
define('BUFFER_SIZE', 8192); // 8KB

// تنظیمات timeout
define('REQUEST_TIMEOUT', 300); // 5 دقیقه
define('STREAM_TIMEOUT', 600); // 10 دقیقه
```

### فایل `php_settings.ini`

```ini
; تنظیمات PHP برای فایل‌های بزرگ
memory_limit = 2G
max_execution_time = 0
max_input_time = 0
post_max_size = 10G
upload_max_filesize = 10G
max_file_uploads = 100

; تنظیمات بافر
output_buffering = 4096
implicit_flush = On
```

### فایل `.htaccess`

```apache
# تنظیمات Apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^proxy\.php/(.*)$ proxy.php?path=$1 [L,QSA]
</IfModule>

# تنظیمات CORS
<IfModule mod_headers.c>
    Header always set Access-Control-Allow-Origin "*"
    Header always set Access-Control-Allow-Methods "GET, HEAD, OPTIONS"
    Header always set Access-Control-Allow-Headers "Range, If-Range"
</IfModule>
```

## 🎯 استفاده

### روش 1: URL مستقیم

```
لینک اصلی:
https://sv1.neurobuild.space/h2/movie/sv1/tt1780967/Seberg.2019.480p.HardSub.SerFil.mp4

لینک پروکسی:
https://filmkhabar.space/proxy.php/h2/movie/sv1/tt1780967/Seberg.2019.480p.HardSub.SerFil.mp4
```

### روش 2: پارامتر URL

```
https://filmkhabar.space/proxy.php?url=https://sv1.neurobuild.space/path/to/video.mp4
```

### روش 3: بازنویسی خودکار

```php
// استفاده از link_rewriter.php
$content = "لینک: https://sv1.neurobuild.space/video.mp4";
$rewritten = rewriteContent($content);
// نتیجه: لینک: https://filmkhabar.space/proxy.php/video.mp4
```

## 🔌 WordPress Plugin

### نصب پلاگین

1. آپلود پوشه `wordpress-plugin` به `/wp-content/plugins/`
2. فعال‌سازی پلاگین از پنل مدیریت
3. تنظیمات در `تنظیمات > پروکسی لینک`

### ویژگی‌های پلاگین

- **تبدیل خودکار**: لینک‌ها در پست‌ها، صفحات و ویجت‌ها
- **شورت‌کد**: `[proxy_link url="..." text="دانلود"]`
- **تنظیمات پیشرفته**: کنترل کامل روی تبدیل
- **لاگ فعالیت**: ثبت تمام تبدیل‌ها
- **تست اتصال**: بررسی وضعیت سرور

### مثال شورت‌کد

```php
[proxy_link url="https://sv1.neurobuild.space/movie.mp4" text="دانلود فیلم" class="download-btn"]
```

## 🛡️ امنیت

### محدودیت‌های امنیتی

```php
// دامنه‌های مجاز
define('ALLOWED_HOSTS', ['sv1.neurobuild.space', 'filmkhabar.space', '45.12.143.141']);

// پسوندهای مسدود
define('BLOCKED_EXTENSIONS', ['php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp', 'exe', 'bat', 'cmd']);
```

### لاگ‌گیری

```php
// فعال‌سازی لاگ
define('LOG_ENABLED', true);
define('LOG_FILE', 'proxy_log.txt');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
```

### نمونه لاگ

```
[2024-01-15 14:30:25] [INFO] درخواست دریافت شد: /proxy.php/movie.mp4
[2024-01-15 14:30:26] [INFO] URL منبع: https://sv1.neurobuild.space/movie.mp4
[2024-01-15 14:30:30] [INFO] فایل با موفقیت ارسال شد: /movie.mp4
```

## 🔧 عیب‌یابی

### مشکلات رایج

#### 1. خطای 404
```bash
# بررسی وجود فایل
curl -I https://sv1.neurobuild.space/test.mp4

# بررسی تنظیمات .htaccess
cat .htaccess
```

#### 2. خطای timeout
```php
// افزایش timeout در config.php
define('REQUEST_TIMEOUT', 600); // 10 دقیقه
define('STREAM_TIMEOUT', 1200); // 20 دقیقه
```

#### 3. خطای memory
```ini
; افزایش memory در php_settings.ini
memory_limit = 4G
```

### تست عملکرد

```bash
# تست سرعت
curl -o /dev/null -s -w "%{speed_download} bytes/sec\n" https://filmkhabar.space/proxy.php/test.mp4

# تست Resume
curl -H "Range: bytes=1000-2000" https://filmkhabar.space/proxy.php/test.mp4

# تست فایل بزرگ
curl -I https://filmkhabar.space/proxy.php/large-file.mp4
```

### لاگ‌های مفید

```bash
# مشاهده لاگ‌های اخیر
tail -f proxy_log.txt

# جستجو در لاگ‌ها
grep "ERROR" proxy_log.txt
grep "WARNING" proxy_log.txt
```

## 📊 مانیتورینگ

### آمار استفاده

```php
// اضافه کردن به proxy.php
function logStats($filePath, $fileSize, $duration) {
    $stats = [
        'timestamp' => time(),
        'file' => $filePath,
        'size' => $fileSize,
        'duration' => $duration,
        'ip' => $_SERVER['REMOTE_ADDR']
    ];
    
    file_put_contents('proxy_stats.json', json_encode($stats) . "\n", FILE_APPEND);
}
```

### گزارش‌گیری

  ```bash
# آمار روزانه
grep "$(date +%Y-%m-%d)" proxy_log.txt | wc -l

# فایل‌های محبوب
grep "فایل با موفقیت ارسال شد" proxy_log.txt | cut -d' ' -f8 | sort | uniq -c | sort -nr
```

## 🚀 بهینه‌سازی

### تنظیمات سرور

```apache
# Apache - mod_expires
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType video/* "access plus 1 month"
</IfModule>

# Apache - mod_deflate
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml
</IfModule>
```

### تنظیمات PHP

```ini
; بهینه‌سازی برای فایل‌های بزرگ
realpath_cache_size = 4096K
realpath_cache_ttl = 600
opcache.enable = 1
opcache.memory_consumption = 128
```

## 📞 پشتیبانی

### اطلاعات تماس

- **وب‌سایت**: https://ayrop.com

### گزارش مشکلات

لطفاً هنگام گزارش مشکل، اطلاعات زیر را ارسال کنید:

1. نسخه PHP
2. نسخه Apache/Nginx
3. محتوای فایل لاگ
4. خطای دقیق
5. مراحل تکرار مشکل

### مشارکت

برای مشارکت در توسعه:

1. Fork کردن repository
2. ایجاد branch جدید
3. اعمال تغییرات
4. ارسال Pull Request

## 📄 لایسنس

این پروژه تحت لایسنس MIT منتشر شده است. برای جزئیات بیشتر فایل `LICENSE` را مطالعه کنید.

## 🔄 تغییرات

### نسخه 1.0.0
- ✅ راه‌اندازی اولیه سیستم پروکسی
- ✅ پشتیبانی از فایل‌های بزرگ (تا 10GB)
- ✅ WordPress Plugin کامل
- ✅ سیستم لاگ‌گیری
- ✅ امنیت بالا
- ✅ بهینه‌سازی عملکرد

---

**🎬 با filmkhabar.space، دانلود بدون محدودیت!**
