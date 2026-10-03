<?php

declare(strict_types=1);

require_once __DIR__ . '/defaults.php';

ini_set('display_errors', '0');
ini_set('log_errors', '1');

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function app_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $local = [];
    $localFile = __DIR__ . '/config.local.php';
    if (is_file($localFile)) {
        $loaded = require $localFile;
        if (is_array($loaded)) {
            $local = $loaded;
        }
    }

    $config = array_merge([
        'db_host' => getenv('NEGAAH_DB_HOST') ?: '127.0.0.1',
        'db_port' => getenv('NEGAAH_DB_PORT') ?: '3306',
        'db_name' => getenv('NEGAAH_DB_NAME') ?: 'negaah_media',
        'db_user' => getenv('NEGAAH_DB_USER') ?: 'root',
        'db_pass' => getenv('NEGAAH_DB_PASS') !== false ? (string) getenv('NEGAAH_DB_PASS') : '',
        'db_charset' => 'utf8mb4',
        'max_upload_bytes' => 4 * 1024 * 1024,
        'max_video_upload_bytes' => 100 * 1024 * 1024,
        'max_font_upload_bytes' => 8 * 1024 * 1024,
    ], $local);

    return $config;
}

function connect_database(): PDO
{
    if (getenv('NEGAAH_DB_DISABLED') === '1') {
        throw new RuntimeException('Database connection deliberately disabled.');
    }
    $config = app_config();
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['db_host'],
        $config['db_port'],
        $config['db_name'],
        $config['db_charset']
    );

    return new PDO($dsn, (string) $config['db_user'], (string) $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function fa_num(mixed $value): string
{
    return strtr((string) $value, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
}

function text_slice(string $value, int $length): string
{
    if (function_exists('mb_substr')) return mb_substr($value, 0, $length, 'UTF-8');
    if (function_exists('iconv_substr')) {
        $cut = iconv_substr($value, 0, $length, 'UTF-8');
        if ($cut !== false) return $cut;
    }
    if (preg_match('/^(.{0,' . max(0, $length) . '})/us', $value, $matches)) return $matches[1];
    return substr($value, 0, $length);
}

function first_char(mixed $value): string
{
    return text_slice((string) $value, 1);
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = (string) ($_POST['_csrf'] ?? '');
    $known = (string) ($_SESSION['_csrf'] ?? '');
    if ($sent === '' || $known === '' || !hash_equals($known, $sent)) {
        http_response_code(419);
        exit('نشست شما منقضی شده است. صفحه را تازه‌سازی و دوباره تلاش کنید.');
    }
}

function redirect(string $url): never
{
    header('Location: ' . $url, true, 303);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $flash = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return is_array($flash) ? $flash : null;
}

function safe_color(mixed $value, string $fallback): string
{
    $value = trim((string) $value);
    return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtoupper($value) : $fallback;
}

function safe_href(mixed $value, string $fallback = '#contact'): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return $fallback;
    }
    if (str_starts_with($value, '#') || str_starts_with($value, '/') || preg_match('/^(https?:|mailto:|tel:)/i', $value)) {
        return $value;
    }
    return $fallback;
}

function safe_image_src(mixed $value, string $fallback = ''): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return $fallback;
    }
    if (str_starts_with($value, 'assets/') || str_starts_with($value, 'uploads/') || preg_match('/^https:\/\//i', $value)) {
        return $value;
    }
    return $fallback;
}

function safe_media_src(mixed $value, string $fallback = ''): string
{
    $value = trim((string) $value);
    if ($value === '') return $fallback;
    if (preg_match('#^uploads/media/[a-f0-9]{32}[.](?:jpg|png|webp|gif|mp4|webm)$#i', $value) || preg_match('/^https:\/\//i', $value)) return $value;
    return $fallback;
}

function safe_font_src(mixed $value): string
{
    $value = trim((string) $value);
    return preg_match('#^uploads/fonts/[a-f0-9]{32}[.](woff2?|ttf|otf)$#i', $value) ? $value : '';
}

function upload_image(array $file): ?string
{
    if (!isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ((int) $file['error'] !== UPLOAD_ERR_OK || !isset($file['tmp_name']) || !is_uploaded_file((string) $file['tmp_name'])) {
        throw new RuntimeException('بارگذاری تصویر انجام نشد. دوباره تلاش کنید.');
    }

    $max = (int) (app_config()['max_upload_bytes'] ?? 4194304);
    if ((int) ($file['size'] ?? 0) > $max) {
        throw new RuntimeException('حجم تصویر باید کمتر از ۴ مگابایت باشد.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file((string) $file['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('فقط تصویرهای JPG، PNG، WEBP یا GIF پذیرفته می‌شوند.');
    }

    $uploadDir = dirname(__DIR__) . '/uploads';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('پوشهٔ بارگذاری تصویر در دسترس نیست.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file((string) $file['tmp_name'], $uploadDir . '/' . $filename)) {
        throw new RuntimeException('ذخیرهٔ تصویر با خطا روبه‌رو شد.');
    }

    return 'uploads/' . $filename;
}

function has_uploaded_files(array $file): bool
{
    $errors = $file['error'] ?? null;
    if (is_array($errors)) {
        foreach ($errors as $error) if ((int) $error !== UPLOAD_ERR_NO_FILE) return true;
        return false;
    }
    return $errors !== null && (int) $errors !== UPLOAD_ERR_NO_FILE;
}

function upload_image_slides(array $files): array
{
    if (!isset($files['name'], $files['error']) || !is_array($files['name']) || !is_array($files['error'])) return [];
    $selectedIndexes = [];
    foreach ($files['error'] as $index => $error) {
        if ((int) $error !== UPLOAD_ERR_NO_FILE) $selectedIndexes[] = $index;
    }
    if (count($selectedIndexes) > 10) throw new RuntimeException('هر پست اسلایدی حداکثر ۱۰ تصویر دارد.');

    $uploaded = [];
    try {
        foreach ($selectedIndexes as $index) {
            $singleFile = [];
            foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $key) {
                $singleFile[$key] = $files[$key][$index] ?? ($key === 'error' ? UPLOAD_ERR_NO_FILE : '');
            }
            $path = upload_brand_media($singleFile, 'image');
            if ($path !== null) $uploaded[] = $path;
        }
    } catch (Throwable $exception) {
        foreach ($uploaded as $path) {
            $diskPath = dirname(__DIR__) . '/' . $path;
            if (is_file($diskPath)) @unlink($diskPath);
        }
        throw $exception;
    }
    return $uploaded;
}

function upload_brand_media(array $file, string $mediaType): ?string
{
    if (!isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ((int) $file['error'] !== UPLOAD_ERR_OK || !isset($file['tmp_name']) || !is_uploaded_file((string) $file['tmp_name'])) {
        throw new RuntimeException('بارگذاری فایل رسانه‌ای انجام نشد. تنظیمات PHP و IIS را بررسی کنید.');
    }

    $isVideo = $mediaType === 'video';
    $max = (int) (app_config()[$isVideo ? 'max_video_upload_bytes' : 'max_upload_bytes'] ?? ($isVideo ? 104857600 : 4194304));
    if ((int) ($file['size'] ?? 0) > $max) {
        throw new RuntimeException($isVideo ? 'حجم ویدیو باید حداکثر ۱۰۰ مگابایت باشد.' : 'حجم تصویر باید حداکثر ۴ مگابایت باشد.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file((string) $file['tmp_name']);
    $allowed = $isVideo
        ? ['video/mp4' => 'mp4', 'video/webm' => 'webm']
        : ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException($isVideo ? 'ویدیو باید MP4 یا WEBM باشد.' : 'تصویر باید JPG، PNG، WEBP یا GIF باشد.');
    }

    $uploadDir = dirname(__DIR__) . '/uploads/media';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('پوشهٔ آرشیو رسانه‌ها در دسترس نیست.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file((string) $file['tmp_name'], $uploadDir . '/' . $filename)) {
        throw new RuntimeException('ذخیرهٔ فایل رسانه‌ای با خطا روبه‌رو شد.');
    }
    return 'uploads/media/' . $filename;
}

function upload_custom_font(array $file): ?string
{
    if (!isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ((int) $file['error'] !== UPLOAD_ERR_OK || !isset($file['tmp_name']) || !is_uploaded_file((string) $file['tmp_name'])) {
        throw new RuntimeException('بارگذاری فونت انجام نشد. دوباره تلاش کنید.');
    }

    $max = (int) (app_config()['max_font_upload_bytes'] ?? 8388608);
    if ((int) ($file['size'] ?? 0) > $max) throw new RuntimeException('حجم فونت باید حداکثر ۸ مگابایت باشد.');
    $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    if (!in_array($extension, ['woff2', 'woff', 'ttf', 'otf'], true)) {
        throw new RuntimeException('فرمت فونت باید WOFF2، WOFF، TTF یا OTF باشد.');
    }

    $handle = fopen((string) $file['tmp_name'], 'rb');
    $signature = $handle ? fread($handle, 4) : false;
    if ($handle) fclose($handle);
    $signatures = ['woff2' => 'wOF2', 'woff' => 'wOFF', 'ttf' => "\x00\x01\x00\x00", 'otf' => 'OTTO'];
    if ($signature === false || $signature !== $signatures[$extension]) {
        throw new RuntimeException('محتوای فایل با فرمت فونت انتخاب‌شده سازگار نیست.');
    }

    $uploadDir = dirname(__DIR__) . '/uploads/fonts';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('پوشهٔ فونت‌ها در دسترس نیست.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file((string) $file['tmp_name'], $uploadDir . '/' . $filename)) {
        throw new RuntimeException('ذخیرهٔ فونت با خطا روبه‌رو شد.');
    }
    return 'uploads/fonts/' . $filename;
}

function load_site_data(?PDO $pdo): array
{
    $settings = default_settings();
    $services = default_services();
    $projects = default_projects();
    $testimonials = default_testimonials();
    $articles = default_articles();
    $brands = default_brands();
    $featuredBrands = array_values(array_filter($brands, static fn(array $brand): bool => !empty($brand['is_featured'])));

    if ($pdo !== null) {
        try {
            foreach ($pdo->query('SELECT setting_key, setting_value FROM site_settings') as $row) {
                if (array_key_exists($row['setting_key'], $settings)) {
                    $settings[$row['setting_key']] = (string) $row['setting_value'];
                }
            }
            $services = $pdo->query('SELECT * FROM services WHERE is_published = 1 ORDER BY sort_order ASC, id ASC')->fetchAll();
            $projects = $pdo->query('SELECT * FROM projects WHERE is_published = 1 ORDER BY sort_order ASC, id DESC LIMIT 6')->fetchAll();
            $testimonials = $pdo->query('SELECT * FROM testimonials WHERE is_published = 1 ORDER BY sort_order ASC, id DESC LIMIT 12')->fetchAll();
            $legacyDemoReviews = [
                'سارا امینی|همراه پروژه ریشه',
                'آرمان نیک‌پی|همراه پروژه نقش',
                'مهتاب یوسفی|همراه پروژه دُرنا',
            ];
            $testimonials = array_values(array_filter($testimonials, static fn(array $review): bool => !in_array((string) ($review['name'] ?? '') . '|' . (string) ($review['company'] ?? ''), $legacyDemoReviews, true)));
            $testimonials = array_slice($testimonials, 0, 6);
            $articles = $pdo->query('SELECT * FROM articles WHERE is_published = 1 ORDER BY published_at DESC, id DESC LIMIT 3')->fetchAll();
            $brands = $pdo->query('SELECT * FROM brands WHERE is_published = 1 ORDER BY sort_order ASC, id ASC')->fetchAll();
            $featuredBrands = $pdo->query('SELECT * FROM brands WHERE is_published = 1 AND is_featured = 1 ORDER BY sort_order ASC, id ASC')->fetchAll();
        } catch (Throwable $exception) {
            // The public site remains usable while the database is being installed.
        }
    }

    return compact('settings', 'services', 'projects', 'testimonials', 'articles', 'brands', 'featuredBrands');
}

function icon_svg(string $name, string $class = ''): string
{
    $icons = [
        'compass' => '<circle cx="12" cy="12" r="8.5"/><path d="m14.8 9.2-1.9 4.1-4.1 1.9 1.9-4.1 4.1-1.9Z"/><path d="M12 3v1.5M21 12h-1.5M12 21v-1.5M3 12h1.5"/>',
        'spark' => '<path d="m12 3 1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2L12 3Z"/><path d="m19 15 .9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9L19 15Z"/>',
        'megaphone' => '<path d="m4 13 15-6v10L4 12v1Z"/><path d="M8 14.1 9.5 20H6.8l-1.4-6.2M19 10h2a2 2 0 0 1 0 4h-2"/>',
        'chart' => '<path d="M4 19.5h16M6.5 16V10m5 6V5m5 11v-4"/><path d="m5.5 7.5 5.7-3 5.3 2.1 2.1-1.4"/>',
        'arrow' => '<path d="M7 17 17 7M7 7h10v10"/>',
        'arrow-left' => '<path d="M19 12H5m7 7-7-7 7-7"/>',
        'arrow-down' => '<path d="M12 5v14m7-7-7 7-7-7"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'quote' => '<path d="M4 11h6v8H3v-7c0-4 2-7 6-9m5 8h6v8h-7v-7c0-4 2-7 6-9"/>',
        'phone' => '<path d="M7 3H4a1 1 0 0 0-1 1c0 9.4 7.6 17 17 17a1 1 0 0 0 1-1v-3l-5-2-2 2c-3-1-5-3-6-6l2-2-3-6Z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>',
        'pin' => '<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.4"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
        'linkedin' => '<path d="M5 9v10M5 5v.01M10 19v-6a4 4 0 0 1 8 0v6M10 9v10"/><rect x="3" y="3" width="18" height="18" rx="3"/>',
        'telegram' => '<path d="m21 4-6.3 16-3.6-6-6-3.5L21 4Z"/><path d="m11.1 14 4.4-4.5"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
    ];
    $body = $icons[$name] ?? $icons['spark'];
    return '<svg class="' . e($class) . '" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.55" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $body . '</svg>';
}

$pdo = null;
$db_error = null;
try {
    $pdo = connect_database();
} catch (Throwable $exception) {
    $db_error = $exception->getMessage();
}
