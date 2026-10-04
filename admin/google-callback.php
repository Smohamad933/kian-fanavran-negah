<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/search_console.php';

if ($pdo === null || empty($_SESSION['admin_id'])) redirect('login.php');
try {
    $adminStatement = $pdo->prepare('SELECT id FROM admins WHERE id = :id LIMIT 1');
    $adminStatement->execute(['id' => (int) $_SESSION['admin_id']]);
    if (!$adminStatement->fetchColumn()) redirect('login.php');
} catch (Throwable $exception) {
    redirect('login.php');
}

$expectedState = (string) ($_SESSION['_gsc_oauth_state'] ?? '');
$receivedState = (string) ($_GET['state'] ?? '');
unset($_SESSION['_gsc_oauth_state']);
if ($expectedState === '' || $receivedState === '' || !hash_equals($expectedState, $receivedState)) {
    set_flash('error', 'پاسخ ورود گوگل معتبر نیست یا نشست منقضی شده است؛ دوباره تلاش کنید.');
    redirect('index.php?page=seo');
}
if (!empty($_GET['error'])) {
    set_flash('error', 'اتصال Google Search Console لغو شد یا اجازهٔ دسترسی داده نشد.');
    redirect('index.php?page=seo');
}

try {
    if (!gsc_is_configured()) throw new RuntimeException('تنظیمات OAuth یا کلید رمزگذاری ناقص است.');
    $code = trim((string) ($_GET['code'] ?? ''));
    if ($code === '' || strlen($code) > 4096) throw new RuntimeException('کد بازگشتی گوگل معتبر نیست؛ دوباره اتصال را آغاز کنید.');
    $siteUrl = gsc_read_setting($pdo, 'seo_site_url');
    $redirectUri = gsc_redirect_uri($siteUrl);
    if ($redirectUri === '') throw new RuntimeException('نشانی بازگشت OAuth امن نیست یا دامنهٔ اصلی تنظیم نشده است.');
    gsc_exchange_code($pdo, $code, $redirectUri);
    set_flash('success', 'حساب Google متصل شد. حالا ویژگی Search Console سایت را انتخاب کنید.');
} catch (Throwable $exception) {
    $message = $exception instanceof RuntimeException ? $exception->getMessage() : 'ارتباط با Google برقرار نشد؛ تنظیمات OAuth و دسترسی HTTPS سرور را بررسی کنید.';
    set_flash('error', $message);
}
redirect('index.php?page=seo');
