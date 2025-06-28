<?php
/**
 * link_rewriter.php
 *
 * اسکریپت بازنویسی خودکار لینک‌ ها برای تبدیل مستقیم لینک‌ های sv1.cinetory.space
 * به لینک‌ های پروکسی از طریق filmkhabar.space
 * 
 * نحوه استفاده: این کد را در ابتدای فایل‌های PHP سایت خود قرار دهید
 */

// بارگذاری تنظیمات مرکزی
require_once __DIR__ . '/config.php';

// =======================
// === شروع بافر خروجی ===
// =======================

ob_start(function(string $html): string {
    // الگوی پیدا کردن URLهای http/https روی دامنه‌های مجاز
    $pattern = '#\bhttps?://(' . implode('|', array_map('preg_quote', ALLOWED_HOSTS)) . ')([^\s"\'<]*)#i';
    
    return preg_replace_callback($pattern, function($matches) {
        $originalUrl = $matches[0];
        $host = $matches[1];
        $path = $matches[2];
        
        // بررسی اینکه آیا این لینک در href یا src است
        $context = getContext($originalUrl, $html);
        
        // فقط لینک‌های دانلود را تبدیل کن (فایل‌های ویدیو، صوتی، و غیره)
        if (isDownloadableFile($path)) {
            return getProxyUrl($originalUrl);
        }
        
        return $originalUrl;
    }, $html);
});

// =======================
// === توابع کمکی ===
// =======================

/**
 * بررسی اینکه آیا فایل قابل دانلود است
 */
function isDownloadableFile(string $path): bool {
    $downloadableExtensions = [
        '.mp4', '.avi', '.mkv', '.mov', '.wmv', '.flv', '.webm',
        '.mp3', '.wav', '.flac', '.aac', '.ogg',
        '.zip', '.rar', '.7z', '.tar', '.gz',
        '.pdf', '.doc', '.docx', '.xls', '.xlsx',
        '.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp'
    ];
    
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return in_array('.' . $extension, $downloadableExtensions);
}

/**
 * دریافت زمینه لینک (href, src, و غیره)
 */
function getContext(string $url, string $html): string {
    // این تابع می‌تواند برای تشخیص بهتر زمینه لینک استفاده شود
    // فعلاً ساده نگه داشته شده
    return 'href';
}

// =======================
// === مثال استفاده ===
// =======================

/*
// در فایل‌های PHP سایت خود، ابتدای فایل این را اضافه کنید:

<?php
require_once 'link_rewriter.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>سایت من</title>
</head>
<body>
    <!-- این لینک‌ها به صورت خودکار به پروکسی تبدیل می‌شوند -->
    <a href="https://sv1.cinetory.space/h2/movie/sv1/tt1780967/Seberg.2019.480p.HardSub.SerFil.mp4">دانلود فیلم</a>
    <a href="https://sv1.cinetory.space/h2/series/sv4/tt13819960/s03/And.Just.Like.That.S03E01.720p.WEB.DL.HardSub.SerFil.mp4">دانلود سریال</a>
</body>
</html>

<?php
// پایان بافر خروجی
ob_end_flush();
?>
*/

// =======================
// === نسخه JavaScript ===
// =======================

/*
اگر نمی‌توانید PHP را تغییر دهید، این اسکریپت JavaScript را در انتهای <body> قرار دهید:

<script>
document.addEventListener('DOMContentLoaded', function() {
    // پیدا کردن همه لینک‌های دانلود
    const links = document.querySelectorAll('a[href*="sv1.cinetory.space"]');
    
    links.forEach(function(link) {
        const originalHref = link.href;
        
        // بررسی اینکه آیا فایل قابل دانلود است
        const downloadableExtensions = ['.mp4', '.avi', '.mkv', '.mov', '.zip', '.rar', '.pdf'];
        const isDownloadable = downloadableExtensions.some(ext => 
            originalHref.toLowerCase().includes(ext)
        );
        
        if (isDownloadable) {
            // استفاده از تنظیمات مرکزی
            const proxyAddress = <?php echo USE_IP_INSTEAD_OF_DOMAIN ? '"' . PROXY_IP . '"' : '"' . PROXY_DOMAIN . '"'; ?>;
            link.href = 'https://' + proxyAddress + '/proxy.php?url=' + encodeURIComponent(originalHref);
        }
    });
});
</script>
*/ 