<?php

declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

function split_install_sql(string $sql): array
{
    $statements = [];
    $current = '';
    $quote = null;
    $lineComment = false;
    $blockComment = false;
    $length = strlen($sql);

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];
        $next = $sql[$i + 1] ?? '';

        if ($lineComment) {
            if ($char === "\n") {
                $lineComment = false;
                $current .= "\n";
            }
            continue;
        }
        if ($blockComment) {
            if ($char === '*' && $next === '/') {
                $blockComment = false;
                $i++;
            }
            continue;
        }
        if ($quote !== null) {
            $current .= $char;
            if ($char === '\\' && $i + 1 < $length) {
                $current .= $sql[++$i];
            } elseif ($char === $quote) {
                if ($next === $quote) {
                    $current .= $sql[++$i];
                } else {
                    $quote = null;
                }
            }
            continue;
        }

        if (($char === '-' && $next === '-' && (($sql[$i + 2] ?? '') === '' || ctype_space($sql[$i + 2]))) || $char === '#') {
            $lineComment = true;
            if ($char === '-') $i++;
            continue;
        }
        if ($char === '/' && $next === '*') {
            $blockComment = true;
            $i++;
            continue;
        }
        if ($char === "'" || $char === '"' || $char === '`') {
            $quote = $char;
            $current .= $char;
            continue;
        }
        if ($char === ';') {
            $statement = trim($current);
            if ($statement !== '') $statements[] = $statement;
            $current = '';
            continue;
        }
        $current .= $char;
    }

    $statement = trim($current);
    if ($statement !== '') $statements[] = $statement;
    return $statements;
}

$lockFile = __DIR__ . '/app/installed.lock';
$alreadyInstalled = is_file($lockFile);
$errors = [];
$installedNow = false;
$host = trim((string) ($_POST['db_host'] ?? '127.0.0.1'));
$port = trim((string) ($_POST['db_port'] ?? '3306'));
$dbName = trim((string) ($_POST['db_name'] ?? 'negaah_media'));
$dbUser = trim((string) ($_POST['db_user'] ?? 'negaah_user'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyInstalled) {
    verify_csrf();
    $dbPassword = (string) ($_POST['db_pass'] ?? '');

    if (!preg_match('/^[A-Za-z0-9.-]{1,190}$/', $host)) $errors[] = 'نام میزبان دیتابیس معتبر نیست.';
    if (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) $errors[] = 'شماره پورت معتبر نیست.';
    if (!preg_match('/^[A-Za-z0-9_]{1,64}$/', $dbName)) $errors[] = 'نام پایگاه داده باید فقط شامل حروف انگلیسی، عدد و زیرخط باشد.';
    if ($dbUser === '' || strlen($dbUser) > 128 || str_contains($dbUser, "\0")) $errors[] = 'نام کاربری دیتابیس را بررسی کنید.';
    if (strlen($dbPassword) > 1024 || str_contains($dbPassword, "\0")) $errors[] = 'رمز دیتابیس معتبر نیست.';

    if ($errors === []) {
        try {
            $charset = 'utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $serverDsn = sprintf('mysql:host=%s;port=%d;charset=%s', $host, (int) $port, $charset);
            try {
                $serverPdo = new PDO($serverDsn, $dbUser, $dbPassword, $options);
                $serverPdo->exec('CREATE DATABASE IF NOT EXISTS `' . $dbName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            } catch (Throwable $databaseCreateException) {
                // Some shared hosts only allow connecting to a database that was created in their control panel.
            }

            $databaseDsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, (int) $port, $dbName, $charset);
            $installPdo = new PDO($databaseDsn, $dbUser, $dbPassword, $options);
            $schemaPath = __DIR__ . '/database/schema.sql';
            $schema = is_file($schemaPath) ? file_get_contents($schemaPath) : false;
            if ($schema === false) throw new RuntimeException('فایل ساختار دیتابیس پیدا نشد. فایل database/schema.sql را بررسی کنید.');

            foreach (split_install_sql($schema) as $statement) {
                $installPdo->exec($statement);
            }

            $localConfig = [
                'db_host' => $host,
                'db_port' => (string) (int) $port,
                'db_name' => $dbName,
                'db_user' => $dbUser,
                'db_pass' => $dbPassword,
                'db_charset' => 'utf8mb4',
                'max_upload_bytes' => 4 * 1024 * 1024,
                'max_video_upload_bytes' => 100 * 1024 * 1024,
                'max_font_upload_bytes' => 8 * 1024 * 1024,
            ];
            $configBody = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($localConfig, true) . ";\n";
            $configPath = __DIR__ . '/app/config.local.php';
            if (file_put_contents($configPath, $configBody, LOCK_EX) === false) {
                throw new RuntimeException('پایگاه داده آماده شد اما فایل تنظیمات ذخیره نشد. به پوشهٔ app دسترسی نوشتن بدهید و دوباره نصب را اجرا کنید.');
            }
            @chmod($configPath, 0640);
            if (file_put_contents($lockFile, 'installed ' . date(DATE_ATOM), LOCK_EX) === false) {
                throw new RuntimeException('پایگاه داده و اتصال ذخیره شد، اما قفل نصب ایجاد نشد. دسترسی نوشتن پوشهٔ app را بررسی کنید.');
            }
            @chmod($lockFile, 0640);
            $alreadyInstalled = true;
            $installedNow = true;
        } catch (PDOException $exception) {
            error_log('Negaah installer database error: ' . $exception->getMessage());
            $errors[] = 'اتصال یا ساخت جدول‌ها انجام نشد. میزبان، نام پایگاه داده و دسترسی کاربر را بررسی کنید؛ در هاست اشتراکی ابتدا دیتابیس را از کنترل‌پنل هاست بسازید.';
        } catch (Throwable $exception) {
            error_log('Negaah installer error: ' . $exception->getMessage());
            $errors[] = $exception instanceof RuntimeException ? $exception->getMessage() : 'نصب کامل نشد. دسترسی نوشتن پوشهٔ app و فایل‌های نصب را بررسی کنید.';
        }
    }
}

?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>نصب نگاه مدیا | اتصال پایگاه داده</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(static_asset_url('admin/assets/admin.css')) ?>">
</head>
<body class="auth-page">
    <main class="auth-card install-card">
        <a class="admin-brand" href="index.php"><span class="admin-brand-mark">ن</span><span><strong>نگاه مدیا</strong><small>نصب و راه‌اندازی سایت</small></span></a>
        <div class="auth-kicker">راه‌اندازی امن و مرحله‌ای</div>
        <h1><?= $alreadyInstalled ? ($installedNow ? 'پایگاه داده آماده است' : 'سامانه قبلاً نصب شده') : 'اتصال به پایگاه داده' ?></h1>
        <p class="auth-intro">مشخصات MySQL را وارد کنید. نصب‌کننده جدول‌ها و محتوای آغازین را می‌سازد و تنظیم اتصال را در فایلی محافظت‌شده ذخیره می‌کند.</p>

        <?php foreach ($errors as $error): ?><div class="admin-alert admin-alert--error"><?= e($error) ?></div><?php endforeach; ?>

        <?php if ($alreadyInstalled): ?>
            <div class="admin-alert admin-alert--success">نصب دیتابیس انجام شده و صفحهٔ نصب قفل است.</div>
            <div class="install-next"><strong>گام بعدی</strong><p>حساب مدیر اصلی را بسازید. این کار فقط یک‌بار امکان‌پذیر است.</p><a class="admin-button admin-button-primary" href="admin/setup.php">ساخت حساب مدیر <?= icon_svg('arrow-left') ?></a></div>
            <div class="auth-foot"><a href="admin/login.php">رفتن به ورود مدیر</a></div>
        <?php else: ?>
            <div class="install-notice"><b>پیش‌نیاز:</b> کاربر دیتابیس باید اجازهٔ ساخت جدول داشته باشد. اگر هاست شما اجازهٔ ساخت دیتابیس نمی‌دهد، ابتدا آن را در کنترل‌پنل هاست بسازید.</div>
            <form class="auth-form install-form" method="post" autocomplete="on">
                <?= csrf_field() ?>
                <label>میزبان MySQL<input type="text" name="db_host" value="<?= e($host) ?>" placeholder="127.0.0.1" required dir="ltr" autocomplete="url"></label>
                <div class="install-form-row">
                    <label>پورت<input type="number" name="db_port" value="<?= e($port) ?>" min="1" max="65535" required dir="ltr"></label>
                    <label>نام دیتابیس<input type="text" name="db_name" value="<?= e($dbName) ?>" required maxlength="64" dir="ltr"></label>
                </div>
                <label>نام کاربری دیتابیس<input type="text" name="db_user" value="<?= e($dbUser) ?>" required maxlength="128" autocomplete="username" dir="ltr"></label>
                <label>رمز عبور دیتابیس<input type="password" name="db_pass" autocomplete="new-password" dir="ltr"></label>
                <button class="admin-button admin-button-primary" type="submit">اتصال و نصب جدول‌ها <?= icon_svg('arrow-left') ?></button>
            </form>
            <div class="auth-foot">پس از نصب، فایل تنظیمات اتصال محافظت می‌شود و این صفحه قفل خواهد شد.</div>
        <?php endif; ?>
    </main>
</body>
</html>
