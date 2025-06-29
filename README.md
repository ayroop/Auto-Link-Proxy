# 🔗 پروکسی خودکار لینک - Auto Proxy Links

پروکسی PHP برای دور زدن محدودیت‌های دانلود در ایران با تبدیل خودکار لینک‌های مستقیم به لینک‌های پروکسی.

## 🌐 دامنه‌های پروژه

- **سرور پروکسی (ایران)**: `tr.modulogic.space`
- **سرور منبع (آلمان)**: `sv1.netwisehub.space`
- **IP سرور ایران**: `45.12.143.141`

## ✨ ویژگی‌ها

- 🔄 تبدیل خودکار لینک‌های مستقیم به لینک‌های پروکسی
- 📹 پشتیبانی از فایل‌های ویدیو بزرگ (تا 10GB)
- 🌍 پشتیبانی از HTTP Range requests
- 🔒 فیلترینگ بر اساس دامنه و نوع فایل
- 📊 سیستم لاگ‌گیری پیشرفته
- 🛡️ تنظیمات امنیتی
- 📱 پلاگین WordPress برای تبدیل خودکار
- ⚡ بهینه‌سازی برای فایل‌های بزرگ

## 🚀 نصب سریع

### 1. دانلود فایل‌ها

```bash
# دانلود از GitHub
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/proxy.php
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/config.php
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/test_proxy.html
```

### 2. تنظیم دامنه‌ها

فایل `config.php` را ویرایش کنید:

```php
// تنظیمات دامنه
define('PROXY_DOMAIN', 'tr.modulogic.space'); // دامنه پروکسی ایران
define('SOURCE_DOMAIN', 'sv1.netwisehub.space'); // دامنه منبع آلمان
define('PROXY_IP', '45.12.143.141'); // IP سرور ایران
```

### 3. آپلود به سرور

فایل‌ها را در پوشه `public_html` سرور آپلود کنید.

## 📖 نحوه استفاده

### تبدیل دستی لینک

```php
// لینک اصلی
$originalUrl = 'https://sv1.netwisehub.space/video.mp4';

// تبدیل به لینک پروکسی
$proxyUrl = 'https://tr.modulogic.space/proxy.php?url=' . urlencode($originalUrl);
```

### استفاده در HTML

```html
<!-- لینک اصلی -->
<a href="https://sv1.netwisehub.space/video.mp4">دانلود ویدیو</a>

<!-- لینک پروکسی -->
<a href="https://tr.modulogic.space/proxy.php?url=https%3A//sv1.netwisehub.space/video.mp4">دانلود ویدیو</a>
```

### استفاده در JavaScript

```javascript
// تبدیل خودکار لینک‌ها
const originalUrl = 'https://sv1.netwisehub.space/video.mp4';
const proxyUrl = `https://tr.modulogic.space/proxy.php?url=${encodeURIComponent(originalUrl)}`;
```

## 🔧 تنظیمات پیشرفته

### فایل‌های مجاز

```php
// پسوندهای مجاز
const ALLOWED_EXTENSIONS = [
    'mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 'm4v', // ویدیو
    'zip', 'rar', '7z', 'tar', 'gz', 'bz2', // فشرده
    'pdf', 'doc', 'docx', 'xls', 'xlsx', // اسناد
    'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp' // تصاویر
];
```

### محدودیت اندازه فایل

```php
// حداکثر اندازه فایل (10GB)
const MAX_FILE_SIZE = 10 * 1024 * 1024 * 1024;
```

### تنظیمات لاگ

```php
// فعال‌سازی لاگ
define('LOG_ENABLED', true);
define('LOG_FILE', 'proxy_log.txt');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
```

## 🎯 مثال‌های کاربردی

### مثال 1: لینک ویدیو

```
لینک اصلی:
https://sv1.netwisehub.space/movies/action.mp4

لینک پروکسی:
https://tr.modulogic.space/proxy.php?url=https%3A//sv1.netwisehub.space/movies/action.mp4
```

### مثال 2: لینک فایل فشرده

```
لینک اصلی:
https://sv1.netwisehub.space/files/archive.zip

لینک پروکسی:
https://tr.modulogic.space/proxy.php?url=https%3A//sv1.netwisehub.space/files/archive.zip
```

### مثال 3: لینک مستند

```
لینک اصلی:
https://sv1.netwisehub.space/documents/report.pdf

لینک پروکسی:
https://tr.modulogic.space/proxy.php?url=https%3A//sv1.netwisehub.space/documents/report.pdf
```

## 🛠️ پلاگین WordPress

### نصب پلاگین

1. فایل‌های پلاگین را در پوشه `wp-content/plugins/auto-proxy-links` آپلود کنید
2. پلاگین را از پنل مدیریت فعال کنید
3. تنظیمات را در `تنظیمات > پروکسی لینک` انجام دهید

### تنظیمات پلاگین

- **دامنه پروکسی**: `tr.modulogic.space`
- **دامنه منبع**: `sv1.netwisehub.space`
- **دامنه‌های مجاز**: `sv1.netwisehub.space`
- **پسوندهای مجاز**: `mp4,avi,mkv,mov,wmv,flv,webm,m4v,zip,rar,7z`

### ویژگی‌های پلاگین

- 🔄 تبدیل خودکار لینک‌ها در محتوا
- 📝 شورت‌کد برای تبدیل دستی
- ⚙️ تنظیمات پیشرفته
- 🧪 تست اتصال
- 📊 لاگ‌گیری

## 🔍 تست و عیب‌یابی

### تست اتصال

```bash
# تست HTTP
curl -I https://tr.modulogic.space/proxy.php

# تست پروکسی
curl "https://tr.modulogic.space/proxy.php?url=https://httpbin.org/status/200"
```

### بررسی لاگ‌ها

```bash
# مشاهده لاگ‌های پروکسی
tail -f proxy_log.txt

# مشاهده لاگ‌های سرور
tail -f /var/log/nginx/error.log
```

### تست صفحه

صفحه تست: `https://tr.modulogic.space/test_proxy.html`

## 📊 آمار و مانیتورینگ

### اسکریپت مانیتورینگ

```bash
# اجرای اسکریپت مانیتورینگ
/usr/local/bin/monitor-proxy.sh

# بررسی وضعیت سرویس‌ها
systemctl status nginx php8.3-fpm
```

### لاگ‌های مهم

- `proxy_log.txt` - لاگ‌های پروکسی
- `/var/log/nginx/access.log` - لاگ‌های دسترسی
- `/var/log/nginx/error.log` - لاگ‌های خطا

## 🔒 امنیت

### تنظیمات امنیتی

- فیلترینگ بر اساس دامنه
- محدودیت نوع فایل
- مسدود کردن فایل‌های اجرایی
- محدودیت اندازه فایل
- لاگ‌گیری کامل

### بهترین شیوه‌ها

1. همیشه از HTTPS استفاده کنید
2. تنظیمات فایروال را فعال کنید
3. لاگ‌ها را مرتب بررسی کنید
4. نرم‌افزارها را به‌روز نگه دارید

## 🚀 استقرار خودکار

### اسکریپت استقرار Ubuntu 24.04

```bash
# دانلود اسکریپت
wget https://raw.githubusercontent.com/ayroop/Auto-Link-Proxy/main/deploy-ubuntu24.sh

# اجرای اسکریپت
chmod +x deploy-ubuntu24.sh
sudo ./deploy-ubuntu24.sh
```

### تنظیمات پیش‌نیاز

- سرور Ubuntu 24.04
- دسترسی root
- دامنه `tr.modulogic.space` اشاره به سرور
- ایمیل معتبر برای SSL

## 📞 پشتیبانی

### آدرس‌های مهم

- **پروکسی**: https://tr.modulogic.space/proxy.php
- **صفحه تست**: https://tr.modulogic.space/test_proxy.html
- **مخزن GitHub**: https://github.com/ayroop/Auto-Link-Proxy

### دستورات مفید

```bash
# بررسی وضعیت سرویس‌ها
systemctl status nginx php8.3-fpm

# راه‌اندازی مجدد سرویس‌ها
systemctl restart nginx php8.3-fpm

# بررسی گواهی SSL
certbot certificates

# بررسی فایروال
ufw status
```

## 📝 تغییرات

### نسخه 2.0
- پشتیبانی از Ubuntu 24.04
- PHP 8.3
- بهبود عملکرد فایل‌های بزرگ
- پلاگین WordPress بهبود یافته

### نسخه 1.0
- نسخه اولیه پروکسی
- پشتیبانی از فایل‌های ویدیو
- سیستم لاگ‌گیری

## 📄 مجوز

این پروژه تحت مجوز MIT منتشر شده است.

---

**نکته**: این پروژه برای استفاده قانونی و دور زدن محدودیت‌های جغرافیایی طراحی شده است. لطفاً از آن مطابق با قوانین محلی استفاده کنید.
