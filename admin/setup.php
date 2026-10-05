<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';

$message = '';
$errors = [];
$adminCount = null;
if ($pdo !== null) {
    try {
        $adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
        if ($adminCount > 0) redirect('login.php');
    } catch (Throwable $exception) {
        $message = 'ساختار پایگاه داده نصب نشده است. اگر اولین نصب است، ابتدا install.php را اجرا کنید؛ برای پایگاه دادهٔ موجود نیز database/schema.sql را در MySQL Import کنید.';
    }
} else {
    $message = 'اتصال MySQL برقرار نیست. ابتدا اطلاعات app/config.local.php را تنظیم کنید.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo !== null && $adminCount === 0 && $message === '') {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirmation'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9_.-]{3,40}$/', $username)) {
        $errors[] = 'نام کاربری باید ۳ تا ۴۰ نویسه و شامل حروف انگلیسی، عدد، نقطه، خط تیره یا زیرخط باشد.';
    }
    if (strlen($password) < 12) {
        $errors[] = 'رمز عبور باید دست‌کم ۱۲ نویسه داشته باشد.';
    }
    if ($password !== $confirm) {
        $errors[] = 'تکرار رمز عبور با رمز اصلی یکسان نیست.';
    }

    if ($errors === []) {
        try {
            $statement = $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)');
            $statement->execute(['username' => $username, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $pdo->lastInsertId();
            $_SESSION['admin_username'] = $username;
            redirect('index.php');
        } catch (Throwable $exception) {
            $errors[] = 'ساخت حساب مدیر انجام نشد. اگر این نام کاربری قبلاً ثبت شده، نام دیگری انتخاب کنید.';
        }
    }
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>راه‌اندازی مدیر سایت | نگاه مدیا</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(static_asset_url('assets/admin.css')) ?>">
</head>
<body class="auth-page">
    <main class="auth-card">
        <a class="admin-brand" href="../index.php"><span class="admin-brand-mark">ن</span><span><strong>نگاه مدیا</strong><small>سامانهٔ مدیریت سایت</small></span></a>
        <div class="auth-kicker">راه‌اندازی اولیه</div>
        <h1>ساخت حساب مدیر</h1>
        <p class="auth-intro">برای شروع، نام کاربری و رمز عبور امنِ مدیر اصلی را تعیین کنید.</p>
        <?php if ($message !== ''): ?><div class="admin-alert admin-alert--warning"><?= e($message) ?></div><a class="auth-back" href="../index.php">بازگشت به سایت</a><?php endif; ?>
        <?php foreach ($errors as $error): ?><div class="admin-alert admin-alert--error"><?= e($error) ?></div><?php endforeach; ?>
        <?php if ($pdo !== null && $adminCount === 0 && $message === ''): ?>
            <form class="auth-form" method="post">
                <?= csrf_field() ?>
                <label>نام کاربری<input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" autocomplete="username" minlength="3" maxlength="40" required dir="ltr"></label>
                <label>رمز عبور<input type="password" name="password" autocomplete="new-password" minlength="12" required dir="ltr"><small>حداقل ۱۲ نویسه؛ رمز پیش‌فرضی وجود ندارد.</small></label>
                <label>تکرار رمز عبور<input type="password" name="password_confirmation" autocomplete="new-password" minlength="12" required dir="ltr"></label>
                <button class="admin-button admin-button-primary" type="submit">ساخت حساب و ورود <?= icon_svg('arrow-left') ?></button>
            </form>
        <?php endif; ?>
        <div class="auth-foot">پس از ساخت نخستین حساب، این صفحه دیگر قابل استفاده نخواهد بود.</div>
    </main>
</body>
</html>
