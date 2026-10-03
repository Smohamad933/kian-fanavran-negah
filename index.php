<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'contact') {
    verify_csrf();

    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        redirect('index.php?sent=1#contact');
    }

    $name = trim((string) ($_POST['name'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($name === '' || $phone === '' || $message === '' || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
        set_flash('error', 'نام، شماره تماس و شرح کوتاه پروژه را وارد کنید. ایمیل هم باید معتبر باشد.');
    } elseif ($pdo === null) {
        set_flash('error', 'فرم تماس هنوز به پایگاه داده متصل نشده است. لطفاً از راه‌های ارتباطی پایین صفحه با ما در تماس باشید.');
    } else {
        try {
            $statement = $pdo->prepare('INSERT INTO inquiries (name, phone, email, subject, message, status) VALUES (:name, :phone, :email, :subject, :message, :status)');
            $statement->execute([
                'name' => text_slice($name, 120),
                'phone' => text_slice($phone, 60),
                'email' => text_slice($email, 190),
                'subject' => text_slice($subject, 190),
                'message' => text_slice($message, 4000),
                'status' => 'new',
            ]);
            set_flash('success', 'پیامتان رسید. به‌زودی با شما تماس می‌گیریم. سپاس از اعتمادتان!');
        } catch (Throwable $exception) {
            set_flash('error', 'ثبت پیام با خطا روبه‌رو شد. لطفاً با ما تماس بگیرید.');
        }
    }
    redirect('index.php#contact');
}

$data = load_site_data($pdo);
extract($data, EXTR_SKIP);
$flash = take_flash();
$heroColor = safe_color($settings['theme_primary'] ?? '', '#155C5A');
$accentColor = safe_color($settings['theme_accent'] ?? '', '#BD5D43');
$saffronColor = safe_color($settings['theme_saffron'] ?? '', '#D7A84A');
$inkColor = safe_color($settings['theme_ink'] ?? '', '#1E2C2B');
$surfaceColor = safe_color($settings['theme_surface'] ?? '', '#F6F3EA');
$pageTitle = ($settings['brand_name'] ?? 'نگاه مدیا') . ' | استودیو خلاقیت و رشد';
$pageDescription = $settings['hero_description'] ?? 'استراتژی، خلاقیت و بازاریابی برای رشد برندهای ایرانی.';

define('SITE_RENDER', true);
require __DIR__ . '/views/home.php';
