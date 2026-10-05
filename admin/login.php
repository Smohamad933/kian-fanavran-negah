<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';

if (!empty($_SESSION['admin_id'])) redirect('index.php');
$message = '';
$errors = [];
$adminCount = null;
if ($pdo !== null) {
    try {
        $adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
        if ($adminCount === 0) redirect('setup.php');
    } catch (Throwable $exception) {
        $message = 'ساختار پایگاه داده نصب نشده است؛ ابتدا database/schema.sql را اجرا کنید.';
    }
} else {
    $message = 'پایگاه داده در دسترس نیست؛ اطلاعات اتصال را در app/config.local.php بررسی کنید.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo !== null && $message === '') {
    verify_csrf();
    $now = time();
    $lockedUntil = (int) ($_SESSION['login_locked_until'] ?? 0);
    if ($lockedUntil > $now) {
        $errors[] = 'به‌دلیل چند تلاش ناموفق، ورود موقتاً محدود شده است. چند دقیقهٔ دیگر دوباره تلاش کنید.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $statement = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $username]);
        $admin = $statement->fetch();

        if ($admin && password_verify($password, (string) $admin['password_hash'])) {
            session_regenerate_id(true);
            unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['admin_username'] = (string) $admin['username'];
            redirect('index.php');
        }

        $attempts = (int) ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['login_attempts'] = $attempts;
        if ($attempts >= 5) {
            $_SESSION['login_locked_until'] = $now + 600;
            $_SESSION['login_attempts'] = 0;
        }
        $errors[] = 'نام کاربری یا رمز عبور درست نیست.';
    }
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود مدیر | نگاه مدیا</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(static_asset_url('assets/admin.css')) ?>">
</head>
<body class="auth-page">
    <main class="auth-card">
        <a class="admin-brand" href="../index.php"><span class="admin-brand-mark">ن</span><span><strong>نگاه مدیا</strong><small>سامانهٔ مدیریت سایت</small></span></a>
        <div class="auth-kicker">ناحیهٔ امن</div>
        <h1>خوش برگشتید</h1>
        <p class="auth-intro">برای مدیریت محتوای نگاه مدیا وارد حساب کاربری‌تان شوید.</p>
        <?php if ($message !== ''): ?><div class="admin-alert admin-alert--warning"><?= e($message) ?></div><a class="auth-back" href="setup.php">رفتن به راه‌اندازی مدیر</a><?php endif; ?>
        <?php foreach ($errors as $error): ?><div class="admin-alert admin-alert--error"><?= e($error) ?></div><?php endforeach; ?>
        <?php if ($pdo !== null && $message === ''): ?>
            <form class="auth-form" method="post">
                <?= csrf_field() ?>
                <label>نام کاربری<input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" autocomplete="username" required dir="ltr"></label>
                <label>رمز عبور<input type="password" name="password" autocomplete="current-password" required dir="ltr"></label>
                <button class="admin-button admin-button-primary" type="submit">ورود به مدیریت <?= icon_svg('arrow-left') ?></button>
            </form>
        <?php endif; ?>
        <div class="auth-foot"><a href="../index.php">بازگشت به سایت اصلی</a></div>
    </main>
</body>
</html>
