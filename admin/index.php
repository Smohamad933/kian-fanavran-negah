<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/search_console.php';

if ($pdo === null) redirect('login.php');
try {
    $adminStatement = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE id = :id LIMIT 1');
    $adminStatement->execute(['id' => (int) ($_SESSION['admin_id'] ?? 0)]);
    $currentAdmin = $adminStatement->fetch();
} catch (Throwable $exception) {
    $currentAdmin = false;
}
if (!$currentAdmin) redirect('login.php');
$_SESSION['admin_username'] = (string) $currentAdmin['username'];

function admin_entities(PDO $pdo): array
{
    $brandOptions = [];
    try {
        foreach ($pdo->query('SELECT id, name FROM brands ORDER BY sort_order ASC, name ASC')->fetchAll() as $brandOption) {
            $brandOptions[(string) $brandOption['id']] = (string) $brandOption['name'];
        }
    } catch (Throwable $exception) {
        // Older installations can still access existing admin sections until the updated schema is imported.
    }
    return [
        'services' => [
            'table' => 'services', 'title' => 'خدمات', 'singular' => 'خدمت', 'name_field' => 'name',
            'fields' => [
                'name' => ['label' => 'نام خدمت', 'type' => 'text', 'required' => true],
                'slug' => ['label' => 'شناسهٔ انگلیسی', 'type' => 'slug', 'required' => true, 'hint' => 'فقط حروف انگلیسی و خط تیره؛ مانند brand-strategy'],
                'tagline' => ['label' => 'شعار کوتاه', 'type' => 'text'],
                'description' => ['label' => 'توضیح خدمت', 'type' => 'textarea', 'required' => true],
                'icon' => ['label' => 'نشان تصویری', 'type' => 'select', 'options' => ['compass' => 'قطب‌نما', 'spark' => 'درخشش', 'megaphone' => 'بلندگو', 'chart' => 'نمودار']],
            ],
        ],
        'projects' => [
            'table' => 'projects', 'title' => 'نمونه‌کارها', 'singular' => 'نمونه‌کار', 'name_field' => 'title',
            'fields' => [
                'title' => ['label' => 'عنوان پروژه', 'type' => 'text', 'required' => true],
                'client' => ['label' => 'نام مشتری / برند', 'type' => 'text'],
                'category' => ['label' => 'دسته‌بندی', 'type' => 'text', 'required' => true],
                'excerpt' => ['label' => 'خلاصهٔ کوتاه', 'type' => 'textarea', 'required' => true],
                'description' => ['label' => 'شرح پروژه (داخلی)', 'type' => 'textarea'],
                'image' => ['label' => 'تصویر پروژه', 'type' => 'image', 'hint' => 'تصویر افقی و کم‌حجم، حداکثر ۴ مگابایت.'],
                'visual_theme' => ['label' => 'رنگ طرح پیش‌فرض', 'type' => 'select', 'options' => ['saffron' => 'زعفرانی', 'teal' => 'فیروزه‌ای', 'coral' => 'آجری', 'ink' => 'تیره']],
                'metrics' => ['label' => 'برچسب کوچک روی تصویر', 'type' => 'text', 'hint' => 'برای محتوای نمونه از «پروژه مفهومی» استفاده کنید.'],
            ],
        ],
        'testimonials' => [
            'table' => 'testimonials', 'title' => 'دیدگاه همراهان', 'singular' => 'دیدگاه', 'name_field' => 'name',
            'fields' => [
                'name' => ['label' => 'نام', 'type' => 'text', 'required' => true],
                'role' => ['label' => 'سمت', 'type' => 'text'],
                'company' => ['label' => 'شرکت / پروژه', 'type' => 'text'],
                'quote' => ['label' => 'متن دیدگاه', 'type' => 'textarea', 'required' => true],
            ],
        ],
        'articles' => [
            'table' => 'articles', 'title' => 'یادداشت‌ها', 'singular' => 'یادداشت', 'name_field' => 'title',
            'fields' => [
                'title' => ['label' => 'عنوان یادداشت', 'type' => 'text', 'required' => true],
                'slug' => ['label' => 'پیوند انگلیسی', 'type' => 'slug', 'required' => true, 'hint' => 'یکتا و انگلیسی باشد؛ مانند brand-voice'],
                'category' => ['label' => 'موضوع', 'type' => 'text'],
                'excerpt' => ['label' => 'چکیده', 'type' => 'textarea', 'required' => true],
                'body' => ['label' => 'متن یادداشت', 'type' => 'textarea', 'hint' => 'متن ساده؛ برای جدا کردن بندها یک خط خالی بگذارید.'],
                'image' => ['label' => 'تصویر یادداشت', 'type' => 'image', 'hint' => 'تصویر شاخص، حداکثر ۴ مگابایت.'],
                'published_at' => ['label' => 'تاریخ انتشار', 'type' => 'date'],
                'seo_title' => ['label' => 'عنوان سئو', 'type' => 'text', 'max' => 180, 'hint' => 'اختیاری؛ اگر خالی باشد از عنوان یادداشت استفاده می‌شود.'],
                'seo_description' => ['label' => 'توضیحات نتیجهٔ جست‌وجو', 'type' => 'textarea', 'max' => 320, 'hint' => 'یک خلاصهٔ طبیعی و یکتا بنویسید؛ حدود ۱۲۰ تا ۱۶۰ نویسه.'],
                'focus_keywords' => ['label' => 'عبارت‌های هدف (هر خط یک عبارت)', 'type' => 'textarea', 'max' => 1000, 'hint' => 'برای راهنمای نگارش و گزارش Search Console؛ این عبارت‌ها به‌صورت متای بی‌اثر منتشر نمی‌شوند.'],
            ],
        ],
        'brands' => [
            'table' => 'brands', 'title' => 'برندهای همکار', 'singular' => 'برند', 'name_field' => 'name',
            'fields' => [
                'name' => ['label' => 'نام برند', 'type' => 'text', 'required' => true],
                'slug' => ['label' => 'پیوند انگلیسی', 'type' => 'slug', 'required' => true, 'hint' => 'برای برندهای اولیه از شناسهٔ موجود استفاده کنید.'],
                'category' => ['label' => 'دستهٔ اصلی', 'type' => 'select', 'required' => true, 'options' => brand_category_options(), 'hint' => 'هر برند را در یکی از چهار حوزهٔ اصلی قرار دهید.'],
                'logo' => ['label' => 'لوگوی برند', 'type' => 'image', 'hint' => 'برای لوگوی افقی، تصویر ۱۲۰۰×۴۰۰ پیکسل (نسبت ۳:۱) پیشنهاد می‌شود؛ فایل PNG شفاف یا WebP و حداکثر ۴ مگابایت باشد. حاشیهٔ خالی دور لوگو را ببُرید تا خود نشان بیشترِ قاب را پُر کند؛ قاب بزرگ با لوگوی ریز، در سایت هم کوچک دیده می‌شود. برای نشان مربعی، برش نزدیک ۸۰۰×۸۰۰ مناسب است.'],
                'preview_image' => ['label' => 'تصویر پیش‌نمایش کارت برند', 'type' => 'image', 'hint' => 'برای نمایش کم‌ارتفاع، تصویر افقی حدود ۳:۱ پیشنهاد می‌شود؛ حداکثر ۴ مگابایت. این تصویر جای لوگو را نمی‌گیرد.'],
                'short_description' => ['label' => 'معرفی کوتاه', 'type' => 'textarea'],
                'long_description' => ['label' => 'متن صفحهٔ برند', 'type' => 'textarea'],
                'testimonial_quote' => ['label' => 'نظر کارفرما (با تأیید ایشان)', 'type' => 'textarea', 'hint' => 'برای رعایت امانت، فقط نقل‌قول واقعی و مورد تأیید برند را منتشر کنید.'],
                'testimonial_author' => ['label' => 'نام گویندهٔ نظر', 'type' => 'text'],
                'testimonial_role' => ['label' => 'سمت یا عنوان گوینده', 'type' => 'text'],
                'seo_title' => ['label' => 'عنوان سئو', 'type' => 'text', 'max' => 180, 'hint' => 'اختیاری؛ در حالت خالی نام برند استفاده می‌شود.'],
                'seo_description' => ['label' => 'توضیحات نتیجهٔ جست‌وجو', 'type' => 'textarea', 'max' => 320, 'hint' => 'خلاصه‌ای کوتاه، یکتا و مرتبط با همین صفحه بنویسید.'],
                'focus_keywords' => ['label' => 'عبارت‌های هدف (هر خط یک عبارت)', 'type' => 'textarea', 'max' => 1000, 'hint' => 'عبارت‌های اصلی این صفحه را برای راهنمای محتوا ثبت کنید؛ این‌ها متای keywords نیستند.'],
            ],
        ],
        'brand_media' => [
            'table' => 'brand_media', 'title' => 'آرشیو رسانهٔ برندها', 'singular' => 'رسانه', 'name_field' => 'title',
            'fields' => [
                'brand_id' => ['label' => 'برند', 'type' => 'brand_select', 'required' => true, 'options' => $brandOptions],
                'title' => ['label' => 'عنوان محتوا', 'type' => 'text', 'required' => true],
                'caption' => ['label' => 'توضیح کوتاه', 'type' => 'textarea'],
                'media_type' => ['label' => 'نوع محتوا', 'type' => 'select', 'options' => ['image' => 'تصویر', 'video' => 'ویدیو']],
                'aspect_ratio' => ['label' => 'نسبت کاور', 'type' => 'select', 'options' => ['4:5' => 'پست عمودی · 4:5', '16:9' => 'افقی · 16:9', '9:16' => 'عمودی · 9:16', '1:1' => 'مربع · 1:1']],
                'media_path' => ['label' => 'کاور یا ویدیوی محتوا', 'type' => 'media', 'required' => true, 'hint' => 'تصویر تا ۴ مگابایت؛ MP4/WEBM تا ۱۰۰ مگابایت. برای پست اسلایدی، چند تصویر (حداکثر ۱۰ عدد) در ورودی پایین انتخاب کنید.'],
                'poster_path' => ['label' => 'پوستر ویدیو (اختیاری)', 'type' => 'image'],
            ],
        ],
    ];
}

function limit_admin_text(string $value, int $limit = 10000): string
{
    if (function_exists('mb_substr')) return mb_substr($value, 0, $limit, 'UTF-8');
    if (function_exists('iconv_substr')) {
        $cut = iconv_substr($value, 0, $limit, 'UTF-8');
        if ($cut !== false) return $cut;
    }
    return strlen($value) <= $limit * 4 ? $value : substr($value, 0, $limit * 4);
}

$entities = admin_entities($pdo);
$validPages = ['dashboard', 'content', 'seo', 'services', 'projects', 'testimonials', 'articles', 'brands', 'brand_media', 'inquiries', 'account'];
$page = (string) ($_GET['page'] ?? 'dashboard');
if (!in_array($page, $validPages, true)) $page = 'dashboard';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $returnPage = (string) ($_POST['return_page'] ?? 'dashboard');
    if (!in_array($returnPage, $validPages, true)) $returnPage = 'dashboard';

    try {
        if ($action === 'save_seo') {
            $rawSiteUrl = trim((string) ($_POST['seo_site_url'] ?? ''));
            $siteUrl = $rawSiteUrl === '' ? '' : seo_normalize_site_url($rawSiteUrl);
            if ($rawSiteUrl !== '' && $siteUrl === '') throw new RuntimeException('نشانی سایت باید یک URL کامل HTTPS معتبر مثل https://example.com باشد؛ بدون مسیر فایل یا پارامتر.');
            $verification = trim((string) ($_POST['google_site_verification'] ?? ''));
            if ($verification !== '' && !preg_match('/^[A-Za-z0-9_-]{1,200}$/', $verification)) {
                throw new RuntimeException('فقط مقدار content از متای تأیید گوگل را وارد کنید؛ خود تگ HTML را نچسبانید.');
            }
            $seoValues = [
                'seo_site_url' => $siteUrl,
                'seo_home_title' => limit_admin_text(trim((string) ($_POST['seo_home_title'] ?? '')), 180),
                'seo_home_description' => limit_admin_text(trim((string) ($_POST['seo_home_description'] ?? '')), 320),
                'seo_home_keywords' => limit_admin_text(trim((string) ($_POST['seo_home_keywords'] ?? '')), 1000),
                'google_site_verification' => $verification,
            ];
            foreach ($seoValues as $key => $value) gsc_setting($pdo, $key, $value);
            set_flash('success', 'تنظیمات پایهٔ سئو ذخیره شد. نشانی canonical و sitemap از این پس بر پایهٔ دامنهٔ واردشده ساخته می‌شوند.');
            redirect('index.php?page=seo');
        }

        if ($action === 'connect_search_console') {
            if (!gsc_is_configured()) throw new RuntimeException('برای اتصال، Client ID، Client Secret و کلید رمزگذاری را در app/config.local.php تنظیم کنید.');
            $siteUrl = gsc_read_setting($pdo, 'seo_site_url');
            $redirectUri = gsc_redirect_uri($siteUrl);
            if ($redirectUri === '') throw new RuntimeException('ابتدا نشانی HTTPS سایت را در تنظیمات سئو ذخیره کنید؛ نشانی بازگشت برای Google OAuth از همان ساخته می‌شود.');
            $state = bin2hex(random_bytes(32));
            $_SESSION['_gsc_oauth_state'] = $state;
            redirect(gsc_authorization_url($state, $redirectUri));
        }

        if ($action === 'disconnect_search_console') {
            gsc_revoke_and_remove_token($pdo);
            gsc_setting($pdo, 'gsc_property_url', '');
            unset($_SESSION['gsc_report'], $_SESSION['_gsc_oauth_state']);
            set_flash('success', 'اتصال Search Console از پنل قطع شد.');
            redirect('index.php?page=seo');
        }

        if ($action === 'save_gsc_property') {
            $selectedProperty = trim((string) ($_POST['gsc_property_url'] ?? ''));
            $availableSites = gsc_list_sites($pdo);
            $isAvailable = false;
            foreach ($availableSites as $availableSite) {
                if (($availableSite['siteUrl'] ?? '') === $selectedProperty) $isAvailable = true;
            }
            if (!$isAvailable) throw new RuntimeException('این ویژگی در فهرست حساب متصل‌شده پیدا نشد؛ دوباره فهرست را بارگذاری کنید.');
            gsc_setting($pdo, 'gsc_property_url', $selectedProperty);
            unset($_SESSION['gsc_report']);
            set_flash('success', 'ویژگی Search Console انتخاب شد.');
            redirect('index.php?page=seo');
        }

        if ($action === 'fetch_gsc_report') {
            $selectedProperty = gsc_read_setting($pdo, 'gsc_property_url');
            if ($selectedProperty === '') throw new RuntimeException('ابتدا از فهرست، ویژگی Search Console سایت را انتخاب کنید.');
            $availableSites = gsc_list_sites($pdo);
            $isAvailable = false;
            foreach ($availableSites as $availableSite) {
                if (($availableSite['siteUrl'] ?? '') === $selectedProperty) $isAvailable = true;
            }
            if (!$isAvailable) throw new RuntimeException('حساب Google دیگر به ویژگی ذخیره‌شده دسترسی ندارد؛ ویژگی را دوباره انتخاب کنید.');
            $endDate = date('Y-m-d', strtotime('-3 days'));
            $startDate = date('Y-m-d', strtotime('-30 days'));
            $rows = gsc_search_analytics($pdo, $selectedProperty, $startDate, $endDate);
            $_SESSION['gsc_report'] = ['property' => $selectedProperty, 'rows' => $rows, 'start' => $startDate, 'end' => $endDate, 'fetched_at' => time()];
            set_flash('success', 'گزارش جست‌وجو از Search Console دریافت شد.');
            redirect('index.php?page=seo');
        }

        if ($action === 'save_content') {
            $defaults = default_settings();
            $upsert = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (:setting_key, :setting_value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            foreach ($defaults as $key => $fallback) {
                if (!array_key_exists($key, $_POST['settings'] ?? [])) continue;
                $value = trim((string) $_POST['settings'][$key]);
                if ($key === 'custom_font_path') {
                    $value = safe_font_src($value);
                } elseif (in_array($key, ['footer_show_admin_login', 'footer_show_source_link', 'footer_show_credit'], true)) {
                    $value = $value === '1' ? '1' : '0';
                } elseif (str_starts_with($key, 'theme_')) {
                    $value = safe_color($value, (string) $fallback);
                } elseif (str_ends_with($key, '_url') || str_starts_with($key, 'social_')) {
                    if ($value !== '') $value = safe_href($value, (string) $fallback);
                } else {
                    $value = limit_admin_text($value, in_array($key, ['hero_description', 'services_description', 'about_description', 'process_description', 'contact_description', 'footer_description'], true) ? 1500 : 600);
                }
                $upsert->execute(['setting_key' => $key, 'setting_value' => $value]);
            }
            $heroUpload = upload_image($_FILES['hero_upload'] ?? []);
            if ($heroUpload !== null) {
                $upsert->execute(['setting_key' => 'hero_image', 'setting_value' => $heroUpload]);
            }
            $fontUpload = upload_custom_font($_FILES['font_upload'] ?? []);
            if (!empty($_POST['remove_custom_font'])) {
                $upsert->execute(['setting_key' => 'custom_font_path', 'setting_value' => '']);
            } elseif ($fontUpload !== null) {
                $upsert->execute(['setting_key' => 'custom_font_path', 'setting_value' => $fontUpload]);
            }
            set_flash('success', 'تغییرات محتوا با موفقیت ذخیره شد.');
            redirect('index.php?page=content');
        }

        if ($action === 'save_item') {
            $entity = (string) ($_POST['entity'] ?? '');
            if (!isset($entities[$entity])) throw new RuntimeException('بخش انتخاب‌شده معتبر نیست.');
            $meta = $entities[$entity];
            $id = max(0, (int) ($_POST['id'] ?? 0));
            $values = [];
            $validationErrors = [];
            $uploadedSlides = [];
            $uploadedMedia = null;
            $orderedSlides = [];
            $slideUploadConflict = false;
            if ($entity === 'brand_media' && has_uploaded_files($_FILES['slides_upload'] ?? [])) {
                $mediaTypeInput = (string) ($_POST['media_type'] ?? 'image');
                $requestedMediaType = in_array($mediaTypeInput, ['image', 'video'], true) ? $mediaTypeInput : 'image';
                if ($requestedMediaType !== 'image') {
                    $validationErrors[] = 'اسلایدهای این پست باید تصویر باشند؛ برای ویدیو، فایل تکی را انتخاب کنید.';
                    $slideUploadConflict = true;
                }
                if (has_uploaded_files($_FILES['media_path_upload'] ?? [])) {
                    $validationErrors[] = 'برای هر پست، یا یک فایل ویدیو انتخاب کنید یا مجموعهٔ تصاویر اسلایدی را؛ هر دو را هم‌زمان بارگذاری نکنید.';
                    $slideUploadConflict = true;
                }
                if (!$slideUploadConflict) $uploadedSlides = upload_image_slides($_FILES['slides_upload']);
            }
            foreach ($meta['fields'] as $column => $field) {
                if ($field['type'] === 'media') {
                    $value = trim((string) ($_POST['current_' . $column] ?? ''));
                    if (!empty($_POST['remove_' . $column])) $value = '';
                    $mediaTypeInput = (string) ($_POST['media_type'] ?? 'image');
                    $mediaType = in_array($mediaTypeInput, ['image', 'video'], true) ? $mediaTypeInput : 'image';
                    $uploaded = $slideUploadConflict ? null : upload_brand_media($_FILES[$column . '_upload'] ?? [], $mediaType);
                    $uploadedMedia = $uploaded;
                    if ($uploaded !== null) $value = $uploaded;
                    if ($uploadedSlides !== []) $value = $uploadedSlides[0];
                    $oldType = (string) ($_POST['current_media_type'] ?? '');
                    if ($value !== '' && $oldType !== '' && $oldType !== $mediaType && $uploaded === null && $uploadedSlides === []) {
                        $validationErrors[] = 'برای تغییر نوع رسانه، فایل تازهٔ همان نوع را بارگذاری کنید.';
                    }
                    if (!empty($field['required']) && $value === '' && $uploadedSlides === []) $validationErrors[] = 'فایل تصویر یا ویدیوی آرشیو الزامی است.';
                    $values[$column] = safe_media_src($value, '');
                    continue;
                }
                if ($field['type'] === 'image') {
                    $value = trim((string) ($_POST['current_' . $column] ?? ''));
                    if (!empty($_POST['remove_' . $column])) $value = '';
                    $uploaded = upload_image($_FILES[$column . '_upload'] ?? []);
                    if ($uploaded !== null) $value = $uploaded;
                    $values[$column] = safe_image_src($value, '');
                    continue;
                }

                $value = trim((string) ($_POST[$column] ?? ''));
                if (!empty($field['required']) && $value === '') {
                    $validationErrors[] = 'فیلد «' . $field['label'] . '» الزامی است.';
                }
                if ($field['type'] === 'slug') {
                    $value = strtolower($value);
                    $value = preg_replace('/[^a-z0-9-]+/', '-', $value) ?? '';
                    $value = trim($value, '-');
                    if ($value !== '' && !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
                        $validationErrors[] = 'پیوند انگلیسی معتبر نیست.';
                    }
                }
                if (in_array($field['type'], ['select', 'brand_select'], true)) {
                    if ($field['options'] === []) {
                        $validationErrors[] = 'ابتدا یک برند همکار بسازید.';
                        $value = '';
                    } elseif (!array_key_exists($value, $field['options'])) {
                        $value = (string) array_key_first($field['options']);
                    }
                }
                if ($field['type'] === 'date' && $value !== '') {
                    $date = DateTime::createFromFormat('Y-m-d', $value);
                    if (!$date || $date->format('Y-m-d') !== $value) $validationErrors[] = 'تاریخ انتشار معتبر نیست.';
                }
                $fieldLimit = (int) ($field['max'] ?? ($field['type'] === 'textarea' ? 12000 : ($field['type'] === 'slug' ? 220 : 500)));
                $values[$column] = limit_admin_text($value, $fieldLimit);
            }

            if ($entity === 'brand_media' && $id > 0 && $uploadedSlides === [] && $uploadedMedia === null && array_key_exists('slides_order', $_POST)) {
                $postedSlideIds = [];
                $slideOrderInput = $_POST['slides_order'];
                if (!is_array($slideOrderInput)) {
                    $validationErrors[] = 'ترتیب اسلایدها معتبر نیست.';
                } else {
                    foreach ($slideOrderInput as $slideId) {
                        $slideId = (string) $slideId;
                        if ($slideId === '' || !ctype_digit($slideId) || (int) $slideId < 1) {
                            $validationErrors[] = 'ترتیب اسلایدها معتبر نیست.';
                            break;
                        }
                        $postedSlideIds[] = (int) $slideId;
                    }
                    if ($validationErrors === []) {
                        $slideOrderStatement = $pdo->prepare('SELECT id, image_path, sort_order FROM brand_media_slides WHERE brand_media_id = :brand_media_id ORDER BY sort_order ASC, id ASC');
                        $slideOrderStatement->execute(['brand_media_id' => $id]);
                        $currentSlideRows = $slideOrderStatement->fetchAll();
                        $currentSlideIds = array_map(static fn($slide) => (int) $slide['id'], $currentSlideRows);
                        $postedSortedIds = $postedSlideIds;
                        $currentSortedIds = $currentSlideIds;
                        sort($postedSortedIds, SORT_NUMERIC);
                        sort($currentSortedIds, SORT_NUMERIC);
                        if (count($postedSlideIds) !== count(array_unique($postedSlideIds)) || $postedSortedIds !== $currentSortedIds) {
                            $validationErrors[] = 'فهرست اسلایدها تغییر کرده است؛ صفحه را تازه کنید و دوباره ترتیب را تنظیم کنید.';
                        } else {
                            $slidesById = [];
                            foreach ($currentSlideRows as $slideRow) $slidesById[(int) $slideRow['id']] = $slideRow;
                            foreach ($postedSlideIds as $slideId) $orderedSlides[] = $slidesById[$slideId];
                        }
                    }
                }
            }
            if ($orderedSlides !== [] && $uploadedSlides === [] && $uploadedMedia === null && ($values['media_type'] ?? 'image') === 'image') {
                $values['media_path'] = safe_media_src($orderedSlides[0]['image_path'] ?? '', '');
                if ($values['media_path'] === '') $validationErrors[] = 'تصویر نخست اسلایدها معتبر نیست.';
            }

            if (isset($values['slug']) && $values['slug'] === '') {
                $validationErrors[] = 'پیوند انگلیسی را وارد کنید.';
            }
            if ($validationErrors !== []) {
                foreach ($uploadedSlides as $path) {
                    $diskPath = dirname(__DIR__) . '/' . $path;
                    if (is_file($diskPath)) @unlink($diskPath);
                }
                set_flash('error', implode(' ', array_unique($validationErrors)));
                redirect('index.php?page=' . rawurlencode($entity) . '&action=' . ($id > 0 ? 'edit&id=' . $id : 'new'));
            }

            $values['sort_order'] = max(-9999, min(9999, (int) ($_POST['sort_order'] ?? 0)));
            $values['is_published'] = isset($_POST['is_published']) ? 1 : 0;
            if ($entity === 'brands') $values['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
            if (isset($values['published_at']) && $values['published_at'] === '') $values['published_at'] = null;

            $columns = array_keys($values);
            $ownsSlideTransaction = $entity === 'brand_media' && ($uploadedSlides !== [] || $uploadedMedia !== null || $orderedSlides !== []) && !$pdo->inTransaction();
            if ($ownsSlideTransaction) $pdo->beginTransaction();
            try {
                if ($id > 0) {
                    $sets = implode(', ', array_map(static fn($column) => '`' . $column . '` = :' . $column, $columns));
                    $values['id'] = $id;
                    $statement = $pdo->prepare('UPDATE `' . $meta['table'] . '` SET ' . $sets . ' WHERE id = :id');
                    $statement->execute($values);
                    $savedId = $id;
                } else {
                    $columnSql = implode(', ', array_map(static fn($column) => '`' . $column . '`', $columns));
                    $parameterSql = implode(', ', array_map(static fn($column) => ':' . $column, $columns));
                    $statement = $pdo->prepare('INSERT INTO `' . $meta['table'] . '` (' . $columnSql . ') VALUES (' . $parameterSql . ')');
                    $statement->execute($values);
                    $savedId = (int) $pdo->lastInsertId();
                }

                if ($entity === 'brand_media' && ($uploadedSlides !== [] || $uploadedMedia !== null)) {
                    $deleteSlides = $pdo->prepare('DELETE FROM brand_media_slides WHERE brand_media_id = :brand_media_id');
                    $deleteSlides->execute(['brand_media_id' => $savedId]);
                    if ($uploadedSlides !== []) {
                        $insertSlide = $pdo->prepare('INSERT INTO brand_media_slides (brand_media_id, image_path, sort_order) VALUES (:brand_media_id, :image_path, :sort_order)');
                        foreach ($uploadedSlides as $index => $slidePath) {
                            $insertSlide->execute(['brand_media_id' => $savedId, 'image_path' => $slidePath, 'sort_order' => $index]);
                        }
                    }
                } elseif ($entity === 'brand_media' && $orderedSlides !== []) {
                    $updateSlideOrder = $pdo->prepare('UPDATE brand_media_slides SET sort_order = :sort_order WHERE id = :id AND brand_media_id = :brand_media_id');
                    foreach ($orderedSlides as $index => $slide) {
                        $updateSlideOrder->execute(['sort_order' => $index, 'id' => (int) $slide['id'], 'brand_media_id' => $savedId]);
                    }
                }
                if ($ownsSlideTransaction) $pdo->commit();
            } catch (Throwable $exception) {
                if ($ownsSlideTransaction && $pdo->inTransaction()) $pdo->rollBack();
                foreach ($uploadedSlides as $path) {
                    $diskPath = dirname(__DIR__) . '/' . $path;
                    if (is_file($diskPath)) @unlink($diskPath);
                }
                throw $exception;
            }
            $successMessage = $uploadedSlides !== [] ? 'محتوای آرشیو و اسلایدهای آن ذخیره شد.' : ($orderedSlides !== [] ? 'محتوا و ترتیب اسلایدهای آن ذخیره شد.' : ($id > 0 ? 'اطلاعات ' . $meta['singular'] . ' ویرایش شد.' : $meta['singular'] . ' تازه اضافه شد.'));
            set_flash('success', $successMessage);
            redirect('index.php?page=' . rawurlencode($entity));
        }

        if ($action === 'delete_item' || $action === 'toggle_item') {
            $entity = (string) ($_POST['entity'] ?? '');
            if (!isset($entities[$entity])) throw new RuntimeException('بخش انتخاب‌شده معتبر نیست.');
            $id = max(0, (int) ($_POST['id'] ?? 0));
            if ($id < 1) throw new RuntimeException('شناسهٔ ردیف معتبر نیست.');
            if ($action === 'delete_item') {
                $statement = $pdo->prepare('DELETE FROM `' . $entities[$entity]['table'] . '` WHERE id = :id');
                $statement->execute(['id' => $id]);
                set_flash('success', 'مورد انتخاب‌شده حذف شد.');
            } else {
                $statement = $pdo->prepare('UPDATE `' . $entities[$entity]['table'] . '` SET is_published = 1 - is_published WHERE id = :id');
                $statement->execute(['id' => $id]);
                set_flash('success', 'وضعیت انتشار به‌روزرسانی شد.');
            }
            redirect('index.php?page=' . rawurlencode($entity));
        }

        if ($action === 'update_inquiry') {
            $id = max(0, (int) ($_POST['id'] ?? 0));
            $status = (string) ($_POST['status'] ?? 'new');
            if (!in_array($status, ['new', 'contacted', 'closed'], true)) $status = 'new';
            $statement = $pdo->prepare('UPDATE inquiries SET status = :status WHERE id = :id');
            $statement->execute(['status' => $status, 'id' => $id]);
            set_flash('success', 'وضعیت درخواست تماس تغییر کرد.');
            redirect('index.php?page=inquiries');
        }

        if ($action === 'delete_inquiry') {
            $statement = $pdo->prepare('DELETE FROM inquiries WHERE id = :id');
            $statement->execute(['id' => max(0, (int) ($_POST['id'] ?? 0))]);
            set_flash('success', 'درخواست تماس حذف شد.');
            redirect('index.php?page=inquiries');
        }

        if ($action === 'change_profile') {
            $username = trim((string) ($_POST['username'] ?? ''));
            $currentPassword = (string) ($_POST['current_password'] ?? '');
            $newPassword = (string) ($_POST['new_password'] ?? '');
            $confirmPassword = (string) ($_POST['new_password_confirmation'] ?? '');
            if (!preg_match('/^[a-zA-Z0-9_.-]{3,40}$/', $username)) throw new RuntimeException('نام کاربری باید ۳ تا ۴۰ نویسهٔ انگلیسی، عدد، نقطه، خط تیره یا زیرخط باشد.');
            if (!password_verify($currentPassword, (string) $currentAdmin['password_hash'])) throw new RuntimeException('رمز فعلی درست نیست.');
            if ($newPassword !== '' && strlen($newPassword) < 12) throw new RuntimeException('رمز تازه باید دست‌کم ۱۲ نویسه داشته باشد.');
            if ($newPassword !== $confirmPassword) throw new RuntimeException('تکرار رمز تازه با آن یکسان نیست.');

            if ($newPassword !== '') {
                $statement = $pdo->prepare('UPDATE admins SET username = :username, password_hash = :password_hash WHERE id = :id');
                $statement->execute(['username' => $username, 'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT), 'id' => (int) $currentAdmin['id']]);
            } else {
                $statement = $pdo->prepare('UPDATE admins SET username = :username WHERE id = :id');
                $statement->execute(['username' => $username, 'id' => (int) $currentAdmin['id']]);
            }
            $_SESSION['admin_username'] = $username;
            set_flash('success', 'مشخصات حساب شما به‌روز شد.');
            redirect('index.php?page=account');
        }

        if ($action === 'add_admin') {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            if (!preg_match('/^[a-zA-Z0-9_.-]{3,40}$/', $username)) throw new RuntimeException('نام کاربری مدیر باید ۳ تا ۴۰ نویسهٔ معتبر داشته باشد.');
            if (strlen($password) < 12) throw new RuntimeException('رمز مدیر تازه باید دست‌کم ۱۲ نویسه داشته باشد.');
            $statement = $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)');
            $statement->execute(['username' => $username, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
            set_flash('success', 'حساب مدیر جدید ایجاد شد.');
            redirect('index.php?page=account');
        }

        if ($action === 'delete_admin') {
            $id = max(0, (int) ($_POST['id'] ?? 0));
            if ($id === (int) $currentAdmin['id']) throw new RuntimeException('برای جلوگیری از قفل شدن سامانه، حسابی که با آن وارد شده‌اید قابل حذف نیست.');
            $count = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
            if ($count <= 1) throw new RuntimeException('آخرین حساب مدیر قابل حذف نیست.');
            $statement = $pdo->prepare('DELETE FROM admins WHERE id = :id');
            $statement->execute(['id' => $id]);
            set_flash('success', 'حساب مدیر حذف شد.');
            redirect('index.php?page=account');
        }
    } catch (Throwable $exception) {
        if (isset($uploadedSlides) && is_array($uploadedSlides)) {
            foreach ($uploadedSlides as $path) {
                $diskPath = dirname(__DIR__) . '/' . $path;
                if (is_file($diskPath)) @unlink($diskPath);
            }
        }
        $message = ($exception instanceof RuntimeException && !($exception instanceof PDOException)) ? $exception->getMessage() : 'عملیات انجام نشد. اطلاعات را بررسی کنید و دوباره تلاش کنید.';
        set_flash('error', $message);
        redirect('index.php?page=' . rawurlencode($returnPage));
    }
}

$flash = take_flash();
$settings = default_settings();
try {
    foreach ($pdo->query('SELECT setting_key, setting_value FROM site_settings') as $row) {
        if (array_key_exists($row['setting_key'], $settings)) $settings[$row['setting_key']] = (string) $row['setting_value'];
    }
} catch (Throwable $exception) {}

$seoSiteUrl = seo_normalize_site_url($settings['seo_site_url'] ?? '');
$seoSitemapUrl = $seoSiteUrl !== '' ? seo_absolute_url($settings, 'sitemap.xml') : '';
$seoRobotsUrl = $seoSiteUrl !== '' ? seo_absolute_url($settings, 'robots.txt') : '';
$gscConfigured = gsc_is_configured();
$gscConnected = false;
$gscSites = [];
$gscError = '';
$gscRedirectUri = gsc_redirect_uri((string) ($settings['seo_site_url'] ?? ''));
$selectedGscProperty = (string) ($settings['gsc_property_url'] ?? '');
$gscReport = $_SESSION['gsc_report'] ?? null;
$seoMissingMetadata = ['articles' => null, 'brands' => null];
if ($page === 'seo') {
    try {
        $seoMissingMetadata['articles'] = (int) $pdo->query("SELECT COUNT(*) FROM articles WHERE is_published = 1 AND (TRIM(seo_title) = '' OR TRIM(seo_description) = '')")->fetchColumn();
        $seoMissingMetadata['brands'] = (int) $pdo->query("SELECT COUNT(*) FROM brands WHERE is_published = 1 AND (TRIM(seo_title) = '' OR TRIM(seo_description) = '')")->fetchColumn();
    } catch (Throwable $exception) {}
}
if (is_array($gscReport) && ($gscReport['property'] ?? '') !== $selectedGscProperty) $gscReport = null;
if ($page === 'seo' && $gscConfigured) {
    try {
        $storedGscToken = gsc_read_token($pdo);
        if ($storedGscToken !== null) {
            $gscConnected = true;
            $gscSites = gsc_list_sites($pdo);
        }
    } catch (Throwable $exception) {
        $gscError = $exception->getMessage();
    }
}

$pageTitles = [
    'dashboard' => 'نمای کلی', 'content' => 'محتوای صفحهٔ اصلی', 'seo' => 'سئو و گوگل', 'services' => 'مدیریت خدمات',
    'projects' => 'مدیریت نمونه‌کارها', 'testimonials' => 'دیدگاه همراهان', 'articles' => 'دفترچه نگاه',
    'brands' => 'برندهای همکار', 'brand_media' => 'آرشیو رسانهٔ برندها',
    'inquiries' => 'درخواست‌های تماس', 'account' => 'حساب‌های مدیران',
];
$pageTitle = $pageTitles[$page];
$inquiryCount = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
$navItems = [
    'dashboard' => ['نمای کلی', 'compass'],
    'content' => ['محتوای سایت', 'spark'],
    'seo' => ['سئو و گوگل', 'chart'],
    'services' => ['خدمات', 'compass'],
    'projects' => ['نمونه‌کارها', 'chart'],
    'testimonials' => ['دیدگاه‌ها', 'quote'],
    'articles' => ['دفترچه نگاه', 'spark'],
    'brands' => ['برندهای همکار', 'spark'],
    'brand_media' => ['آرشیو رسانه', 'chart'],
    'inquiries' => ['درخواست‌های تماس', 'mail'],
    'account' => ['حساب مدیران', 'plus'],
];

$entityMeta = $entities[$page] ?? null;
$editId = max(0, (int) ($_GET['id'] ?? 0));
$actionMode = (string) ($_GET['action'] ?? '');
$editItem = null;
$editSlides = [];
$listItems = [];
$entityTableError = '';
if ($entityMeta !== null) {
    try {
        if ($actionMode === 'edit' && $editId > 0) {
            $statement = $pdo->prepare('SELECT * FROM `' . $entityMeta['table'] . '` WHERE id = :id LIMIT 1');
            $statement->execute(['id' => $editId]);
            $editItem = $statement->fetch() ?: null;
            if ($page === 'brand_media' && $editItem) {
                $slideStatement = $pdo->prepare('SELECT id, image_path, sort_order FROM brand_media_slides WHERE brand_media_id = :brand_media_id ORDER BY sort_order ASC, id ASC');
                $slideStatement->execute(['brand_media_id' => $editId]);
                $editSlides = $slideStatement->fetchAll();
            }
        }
        $listItems = $pdo->query('SELECT * FROM `' . $entityMeta['table'] . '` ORDER BY sort_order ASC, id DESC')->fetchAll();
    } catch (Throwable $exception) {
        $entityTableError = 'جدول این بخش در پایگاه داده پیدا نشد. برای نصب یا به‌روزرسانی، database/schema.sql را اجرا کنید.';
    }
}

$stats = [];
if ($page === 'dashboard') {
    foreach (['services' => 'خدمت', 'projects' => 'نمونه‌کار', 'testimonials' => 'دیدگاه', 'articles' => 'یادداشت'] as $table => $label) {
        $stats[] = ['count' => (int) $pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn(), 'label' => $label, 'page' => $table];
    }
    $recentInquiries = $pdo->query('SELECT id, name, phone, subject, status, created_at FROM inquiries ORDER BY created_at DESC LIMIT 5')->fetchAll();
}

function render_admin_field(string $column, array $field, array $item, array $slides = []): void
{
    $value = $item[$column] ?? ($field['type'] === 'date' ? date('Y-m-d') : '');
    $required = !empty($field['required']) ? ' required' : '';
    $hint = !empty($field['hint']) ? '<small class="field-hint">' . e($field['hint']) . '</small>' : '';
    $wrapperTag = $field['type'] === 'media' ? 'div' : 'label';
    $maxLength = (int) ($field['max'] ?? ($field['type'] === 'textarea' ? 12000 : ($field['type'] === 'slug' ? 220 : 500)));
    echo '<' . $wrapperTag . ' class="admin-field"><span>' . e($field['label']) . (!empty($field['required']) ? ' <b>*</b>' : '') . '</span>';
    if ($field['type'] === 'textarea') {
        echo '<textarea name="' . e($column) . '" rows="' . e((string) ($field['rows'] ?? 5)) . '" maxlength="' . e((string) $maxLength) . '"' . $required . '>' . e($value) . '</textarea>';
    } elseif (in_array($field['type'], ['select', 'brand_select'], true)) {
        echo '<select name="' . e($column) . '">';
        foreach ($field['options'] as $optionValue => $optionLabel) {
            echo '<option value="' . e($optionValue) . '"' . ((string) $value === (string) $optionValue ? ' selected' : '') . '>' . e($optionLabel) . '</option>';
        }
        echo '</select>';
    } elseif ($field['type'] === 'media') {
        $safeMedia = safe_media_src($value, '');
        $mediaType = (string) ($item['media_type'] ?? 'image');
        if ($safeMedia !== '') {
            $mediaUrl = preg_match('/^https:\/\//i', $safeMedia) ? $safeMedia : '../' . $safeMedia;
            if ($mediaType === 'video') {
                echo '<span class="image-current"><video src="' . e($mediaUrl) . '" controls preload="metadata"></video><span>ویدیوی فعلی</span></span>';
            } else {
                echo '<span class="image-current"><img src="' . e($mediaUrl) . '" alt=""><span>تصویر فعلی</span></span>';
            }
        }
        if ($slides !== []) {
            echo '<div class="slide-preview-list" data-slide-sortable aria-label="برای تغییر ترتیب اسلایدها، آن‌ها را بکشید و رها کنید">';
            foreach ($slides as $index => $slide) {
                $slideUrl = safe_image_src((string) ($slide['image_path'] ?? ''), '');
                if ($slideUrl === '') continue;
                echo '<div class="slide-preview-item" data-slide-sort-item><input type="hidden" name="slides_order[]" value="' . e((string) ((int) ($slide['id'] ?? 0))) . '"><img src="../' . e($slideUrl) . '" alt=""><span class="slide-preview-index">اسلاید ' . fa_num((string) ($index + 1)) . '</span><span class="slide-drag-handle" data-slide-drag-handle role="button" tabindex="0" aria-label="جابجایی اسلاید ' . fa_num((string) ($index + 1)) . '" title="بکشید تا جابه‌جا شود">↕</span></div>';
            }
            echo '</div><small class="field-hint">برای تغییر ترتیب، اسلاید را با ماوس یا لمس بکشید؛ ترتیب جدید پس از ذخیره اعمال می‌شود.</small>';
        }
        echo '<input type="hidden" name="current_' . e($column) . '" value="' . e($value) . '"><input type="hidden" name="current_media_type" value="' . e($mediaType) . '"><input type="file" name="' . e($column) . '_upload" accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm" aria-label="کاور تصویر یا ویدیوی محتوا">';
        if ($column === 'media_path') echo '<small class="field-hint">تصویرهای اسلایدی — به‌جای فایل تکی بالا (حداکثر ۱۰ تصویر)</small><input type="file" name="slides_upload[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple aria-label="تصاویر اسلایدی">';
        if ($safeMedia !== '') echo '<label class="check-label"><input type="checkbox" name="remove_' . e($column) . '" value="1"> حذف فایل فعلی</label>';
    } elseif ($field['type'] === 'image') {
        if ((string) $value !== '') echo '<span class="image-current"><img src="../' . e(safe_image_src($value, '')) . '" alt=""><span>تصویر فعلی</span></span>';
        echo '<input type="hidden" name="current_' . e($column) . '" value="' . e($value) . '"><input type="file" name="' . e($column) . '_upload" accept="image/jpeg,image/png,image/webp,image/gif">';
        if ((string) $value !== '') echo '<label class="check-label"><input type="checkbox" name="remove_' . e($column) . '" value="1"> حذف تصویر فعلی</label>';
    } else {
        $type = match ($field['type']) { 'date' => 'date', 'slug' => 'text', default => 'text' };
        $dir = $field['type'] === 'slug' ? ' dir="ltr" class="ltr-input"' : '';
        echo '<input type="' . $type . '" name="' . e($column) . '" value="' . e($value) . '"' . $dir . $required . ' maxlength="' . e((string) $maxLength) . '">';
    }
    echo $hint . '</' . $wrapperTag . '>';
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | نگاه مدیا</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(static_asset_url('assets/admin.css')) ?>">
    <script defer src="<?= e(static_asset_url('assets/admin.js')) ?>"></script>
</head>
<body class="admin-page">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="../index.php"><span class="admin-brand-mark">ن</span><span><strong>نگاه مدیا</strong><small>مدیریت محتوا</small></span></a>
        <div class="sidebar-caption">فضای کاری شما</div>
        <nav class="admin-nav" aria-label="فهرست مدیریت">
            <?php foreach ($navItems as $key => [$label, $icon]): ?>
                <a class="<?= $page === $key ? 'is-active' : '' ?>" href="index.php?page=<?= e($key) ?>"><?= icon_svg($icon) ?><span><?= e($label) ?></span><?php if ($key === 'inquiries' && ($inquiryCount ?? 0) > 0): ?><b class="nav-count"><?= fa_num((string) $inquiryCount) ?></b><?php endif; ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-bottom"><span class="sidebar-bottom-mark">✳</span><p>نگاه متفاوت،<br><b>ساختنِ ممکن‌ها.</b></p><a href="../index.php" target="_blank" rel="noopener">مشاهدهٔ وب‌سایت <?= icon_svg('arrow') ?></a></div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div><span class="admin-breadcrumb">مدیریت سایت <i>/</i> <?= e($pageTitle) ?></span><h1><?= e($pageTitle) ?></h1></div>
            <div class="admin-user"><span class="admin-user-avatar"><?= e(first_char($currentAdmin['username'])) ?></span><span><strong><?= e($currentAdmin['username']) ?></strong><small>مدیر سایت</small></span><a class="logout-link" href="logout.php" title="خروج" aria-label="خروج"><?= icon_svg('arrow-left') ?></a></div>
        </header>
        <div class="admin-content">
            <?php if ($flash): ?><div class="admin-alert admin-alert--<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div><?php endif; ?>

            <?php if ($page === 'dashboard'): ?>
                <section class="welcome-banner"><div><span class="admin-kicker">به فضای کاری نگاه خوش آمدید</span><h2>سلام، <?= e($currentAdmin['username']) ?> <span>✳</span></h2><p>از همین‌جا می‌توانید محتوای سایت را به‌روز کنید، درخواست‌های جدید را ببینید و روایت برند را شکل دهید.</p></div><div class="welcome-shape" aria-hidden="true"><i></i><b>ن</b></div></section>
                <div class="stats-grid">
                    <?php foreach ($stats as $index => $stat): ?>
                        <a class="admin-stat-card" href="index.php?page=<?= e($stat['page']) ?>"><span class="admin-stat-index">۰<?= fa_num((string) ($index + 1)) ?></span><strong><?= fa_num((string) $stat['count']) ?></strong><small><?= e($stat['label']) ?> ثبت‌شده</small><span class="stat-card-arrow"><?= icon_svg('arrow-left') ?></span></a>
                    <?php endforeach; ?>
                    <a class="admin-stat-card admin-stat-card-accent" href="index.php?page=inquiries"><span class="admin-stat-index">۰۵</span><strong><?= fa_num((string) $inquiryCount) ?></strong><small>درخواست تماس تازه</small><span class="stat-card-arrow"><?= icon_svg('arrow-left') ?></span></a>
                </div>
                <div class="dashboard-columns">
                    <section class="admin-card recent-card"><div class="admin-card-heading"><div><span class="admin-kicker">پیام‌های تازه</span><h2>آخرین درخواست‌ها</h2></div><a href="index.php?page=inquiries" class="admin-text-link">همهٔ درخواست‌ها <?= icon_svg('arrow-left') ?></a></div>
                        <?php if ($recentInquiries === []): ?><div class="empty-state"><span>✳</span><strong>هنوز پیامی ندارید</strong><p>پیام‌های فرم تماس سایت، اینجا نمایش داده می‌شوند.</p></div>
                        <?php else: ?><div class="recent-list"><?php foreach ($recentInquiries as $inquiry): ?><a href="index.php?page=inquiries" class="recent-row"><span class="recent-dot <?= e($inquiry['status']) ?>"></span><span class="recent-name"><strong><?= e($inquiry['name']) ?></strong><small><?= e($inquiry['subject'] ?: 'درخواست مشاوره') ?></small></span><span class="recent-phone" dir="ltr"><?= e($inquiry['phone']) ?></span><span class="recent-date"><?= e(date('Y/m/d', strtotime((string) $inquiry['created_at']))) ?></span><?= icon_svg('arrow-left') ?></a><?php endforeach; ?></div><?php endif; ?>
                    </section>
                    <section class="admin-card quick-card"><div class="admin-card-heading"><div><span class="admin-kicker">دسترسی سریع</span><h2>از کجا شروع کنیم؟</h2></div></div><div class="quick-links"><a href="index.php?page=content"><span class="quick-icon">✳</span><span><strong>ویرایش صفحهٔ اصلی</strong><small>تیترها، رنگ‌ها و اطلاعات برند</small></span><?= icon_svg('arrow-left') ?></a><a href="index.php?page=projects&action=new"><span class="quick-icon">↗</span><span><strong>افزودن نمونه‌کار</strong><small>یک روایت تازه به ویترین اضافه کنید</small></span><?= icon_svg('arrow-left') ?></a><a href="index.php?page=articles&action=new"><span class="quick-icon">✎</span><span><strong>نوشتن یادداشت</strong><small>فکرهای تازه‌تان را منتشر کنید</small></span><?= icon_svg('arrow-left') ?></a><a href="index.php?page=brands"><span class="quick-icon">◎</span><span><strong>مدیریت برندهای همکار</strong><small>دسته‌بندی، لوگو و نمایش در خانه</small></span><?= icon_svg('arrow-left') ?></a><a href="index.php?page=seo"><span class="quick-icon">◎</span><span><strong>بررسی سئو و Google</strong><small>دامنه، توضیحات و گزارش جست‌وجو</small></span><?= icon_svg('arrow-left') ?></a></div></section>
                </div>
                <div class="admin-note"><span>!</span><p><strong>پیش از انتشار:</strong> نمونه‌کارهای اولیه مفهومی‌اند. دیدگاه مشتری را فقط با متن واقعی و اجازهٔ انتشار وارد کنید؛ دیدگاه‌های نمایشی قدیمی در سایت عمومی پنهان شده‌اند.</p></div>

            <?php elseif ($page === 'content'): ?>
                <div class="content-intro"><div><span class="admin-kicker">همه‌چیز در یک نگاه</span><h2>خانهٔ نگاه را به زبان خودتان بنویسید.</h2><p>متن هر بخش، پیوندها، راه‌های تماس و رنگ‌های سایت را از همین‌جا تغییر دهید. برای محتوای تکرارشونده از بخش‌های جداگانهٔ خدمات، نمونه‌کار و یادداشت‌ها استفاده کنید.</p></div><span class="content-intro-mark">ن</span></div>
                <form class="content-editor" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?><input type="hidden" name="action" value="save_content"><input type="hidden" name="return_page" value="content">
                    <?php foreach (content_groups() as $groupTitle => $fields): ?>
                        <section class="admin-card content-group"><div class="admin-card-heading"><div><span class="admin-kicker">تنظیمات صفحه</span><h2><?= e($groupTitle) ?></h2></div><span class="group-count"><?= fa_num((string) count($fields)) ?> فیلد</span></div>
                            <div class="settings-grid">
                                <?php foreach ($fields as $field): $value = $settings[$field['key']] ?? ''; ?>
                                    <label class="admin-field <?= $field['type'] === 'textarea' ? 'field-wide' : '' ?> <?= $field['type'] === 'color' ? 'color-field' : '' ?>">
                                        <span><?= e($field['label']) ?></span>
                                        <?php if ($field['type'] === 'textarea'): ?><textarea name="settings[<?= e($field['key']) ?>]" rows="3"><?= e($value) ?></textarea>
                                        <?php elseif ($field['type'] === 'color'): ?><span class="color-control"><input type="color" name="settings[<?= e($field['key']) ?>]" value="<?= e(safe_color($value, '#155C5A')) ?>"><input type="text" value="<?= e(safe_color($value, '#155C5A')) ?>" readonly dir="ltr"></span>
                                        <?php elseif ($field['type'] === 'toggle'): ?><span class="admin-toggle-setting"><input type="hidden" name="settings[<?= e($field['key']) ?>]" value="0"><input type="checkbox" name="settings[<?= e($field['key']) ?>]" value="1"<?= (string) $value === '1' ? ' checked' : '' ?>><small>پس از ذخیره اعمال می‌شود</small></span>
                                        <?php elseif ($field['type'] === 'font'): ?>
                                            <?php $fontValue = safe_font_src($value); ?>
                                            <input type="hidden" name="settings[<?= e($field['key']) ?>]" value="<?= e($fontValue) ?>">
                                            <span class="upload-setting"><small><?= $fontValue !== '' ? 'فونت اختصاصی فعال است: ' . e(pathinfo($fontValue, PATHINFO_EXTENSION)) : 'بدون فونت اختصاصی؛ فونت پیش‌فرض سایت استفاده می‌شود.' ?></small><input type="file" name="font_upload" accept=".woff2,.woff,.ttf,.otf, font/woff2,font/woff,font/ttf,font/otf"></span>
                                            <?php if ($fontValue !== ''): ?><span class="check-label"><input type="checkbox" name="remove_custom_font" value="1"> حذف فونت اختصاصی</span><?php endif; ?>
                                        <?php else: ?><input type="text" name="settings[<?= e($field['key']) ?>]" value="<?= e($value) ?>"<?= str_ends_with($field['key'], '_url') || str_starts_with($field['key'], 'social_') ? ' dir="ltr"' : '' ?>><?php endif; ?>
                                        <?php if ($field['key'] === 'hero_image'): ?><span class="upload-setting"><small>یا تصویر اصلی را از رایانه بارگذاری کنید</small><input type="file" name="hero_upload" accept="image/jpeg,image/png,image/webp,image/gif"></span><?php endif; ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                    <div class="sticky-save"><span>تغییرها تا زمان ذخیره در سایت اعمال نمی‌شوند.</span><button class="admin-button admin-button-primary" type="submit">ذخیرهٔ تغییرات <?= icon_svg('check') ?></button></div>
                </form>

            <?php elseif ($page === 'seo'): ?>
                <div class="content-intro seo-intro"><div><span class="admin-kicker">راهنمای رشد در جست‌وجو</span><h2>سئو را با چند تنظیم روشن شروع کنید.</h2><p>دامنه، توضیحات یکتا و عبارت‌های هدف را ثبت کنید؛ سپس Search Console را فقط‌خواندنی متصل کنید تا عبارت‌ها، کلیک‌ها و جایگاه میانگین را از دادهٔ واقعی ببینید.</p></div><span class="content-intro-mark">SEO</span></div>
                <div class="seo-status-grid">
                    <div class="seo-status-card <?= $seoSiteUrl !== '' ? 'is-ready' : 'is-pending' ?>"><small>دامنهٔ canonical</small><strong><?= $seoSiteUrl !== '' ? 'تنظیم شده' : 'نیاز به تنظیم' ?></strong><span><?= $seoSiteUrl !== '' ? e($seoSiteUrl) : 'نشانی اصلی سایت را وارد کنید.' ?></span></div>
                    <div class="seo-status-card <?= trim((string) ($settings['seo_home_title'] ?? '')) !== '' ? 'is-ready' : 'is-pending' ?>"><small>عنوان و توضیح خانه</small><strong><?= trim((string) ($settings['seo_home_title'] ?? '')) !== '' && trim((string) ($settings['seo_home_description'] ?? '')) !== '' ? 'آماده' : 'پیشنهاد می‌شود کامل شود' ?></strong><span>برای صفحهٔ اصلی متن یکتا بنویسید.</span></div>
                    <div class="seo-status-card <?= trim((string) ($settings['google_site_verification'] ?? '')) !== '' ? 'is-ready' : 'is-pending' ?>"><small>تأیید HTML گوگل</small><strong><?= trim((string) ($settings['google_site_verification'] ?? '')) !== '' ? 'کد ثبت شده' : 'در انتظار کد' ?></strong><span>پس از ثبت کد، در Search Console روی تأیید بزنید.</span></div>
                    <div class="seo-status-card <?= $gscConnected ? 'is-ready' : 'is-pending' ?>"><small>گزارش Search Console</small><strong><?= $gscConnected ? 'متصل' : 'هنوز متصل نیست' ?></strong><span><?= $gscConnected ? fa_num((string) count($gscSites)) . ' ویژگی در دسترس' : 'اتصال فقط‌خواندنی و اختیاری است.' ?></span></div>
                </div>
                <div class="seo-incomplete-links">
                    <a href="index.php?page=brands"><span><small>صفحه‌های برند</small><strong><?= is_int($seoMissingMetadata['brands']) ? ($seoMissingMetadata['brands'] === 0 ? 'عنوان و توضیح سئوی همهٔ صفحه‌های منتشرشده تکمیل است.' : fa_num((string) $seoMissingMetadata['brands']) . ' صفحه بدون عنوان یا توضیح سئو') : 'برای بررسی، database/schema.sql را اجرا کنید.' ?></strong></span><?= icon_svg('arrow-left') ?></a>
                    <a href="index.php?page=articles"><span><small>یادداشت‌ها</small><strong><?= is_int($seoMissingMetadata['articles']) ? ($seoMissingMetadata['articles'] === 0 ? 'عنوان و توضیح سئوی همهٔ یادداشت‌های منتشرشده تکمیل است.' : fa_num((string) $seoMissingMetadata['articles']) . ' یادداشت بدون عنوان یا توضیح سئو') : 'برای بررسی، database/schema.sql را اجرا کنید.' ?></strong></span><?= icon_svg('arrow-left') ?></a>
                </div>

                <section class="admin-card seo-card"><div class="admin-card-heading"><div><span class="admin-kicker">مرحلهٔ ۱ · مشخصات جست‌وجو</span><h2>دامنه و صفحهٔ اصلی</h2></div><span class="seo-step-number">۰۱</span></div>
                    <form class="seo-settings-form" method="post">
                        <?= csrf_field() ?><input type="hidden" name="action" value="save_seo"><input type="hidden" name="return_page" value="seo">
                        <div class="item-fields-grid seo-fields-grid">
                            <label class="admin-field field-wide"><span>نشانی اصلی سایت (Canonical Base URL)</span><input type="url" name="seo_site_url" value="<?= e($settings['seo_site_url'] ?? '') ?>" placeholder="https://example.com" dir="ltr" maxlength="300"><small class="field-hint">نشانی عمومی و نهایی سایت را با https وارد کنید؛ از همین دامنه برای canonical، sitemap و نشانی بازگشت OAuth استفاده می‌شود. اگر سایت در زیرپوشه نصب شده، مسیر آن را هم بنویسید.</small></label>
                            <label class="admin-field"><span>عنوان سئوی صفحهٔ اصلی</span><input type="text" name="seo_home_title" value="<?= e($settings['seo_home_title'] ?? '') ?>" maxlength="180" placeholder="خالی بماند، عنوان پیش‌فرض سایت استفاده می‌شود"></label>
                            <label class="admin-field"><span>عبارت‌های هدف صفحهٔ اصلی</span><textarea name="seo_home_keywords" rows="4" maxlength="1000" placeholder="مثلاً هر عبارت را در یک خط بنویسید"><?= e($settings['seo_home_keywords'] ?? '') ?></textarea><small class="field-hint">این فهرست برای برنامه‌ریزی محتواست؛ Google متای keywords را برای رتبه‌بندی به‌کار نمی‌برد.</small></label>
                            <label class="admin-field field-wide"><span>توضیحات نتیجهٔ جست‌وجوی صفحهٔ اصلی</span><textarea name="seo_home_description" rows="3" maxlength="320" placeholder="خلاصه‌ای روشن و یکتا از خدمات و مزیت شما؛ حدود ۱۲۰ تا ۱۶۰ نویسه."><?= e($settings['seo_home_description'] ?? '') ?></textarea></label>
                            <label class="admin-field field-wide"><span>کد تأیید مالکیت Google Search Console</span><input type="text" name="google_site_verification" value="<?= e($settings['google_site_verification'] ?? '') ?>" maxlength="200" dir="ltr" placeholder="مقدار داخل content از متای google-site-verification"><small class="field-hint">در Search Console روش HTML tag را انتخاب کنید و فقط مقدار content را اینجا بگذارید. ذخیره کنید، سپس در Search Console روی Verify بزنید؛ این پنل تگ را در صفحهٔ اصلی قرار می‌دهد.</small></label>
                        </div>
                        <div class="form-actions"><button class="admin-button admin-button-primary" type="submit">ذخیرهٔ تنظیمات سئو <?= icon_svg('check') ?></button></div>
                    </form>
                </section>

                <div class="seo-url-cards">
                    <a class="seo-url-card" href="<?= e($seoSitemapUrl !== '' ? $seoSitemapUrl : '../sitemap.xml') ?>" target="_blank" rel="noopener"><span><small>نقشهٔ سایت خودکار</small><strong><?= e($seoSitemapUrl !== '' ? $seoSitemapUrl : 'sitemap.xml · پس از ثبت دامنه فعال می‌شود') ?></strong></span><?= icon_svg('arrow') ?></a>
                    <a class="seo-url-card" href="<?= e($seoRobotsUrl !== '' ? $seoRobotsUrl : '../robots.txt') ?>" target="_blank" rel="noopener"><span><small>فایل robots.txt</small><strong><?= e($seoRobotsUrl !== '' ? $seoRobotsUrl : 'robots.txt · مسیر مدیریت از خزش کنار گذاشته می‌شود') ?></strong></span><?= icon_svg('arrow') ?></a>
                </div>

                <section class="admin-card seo-card"><div class="admin-card-heading"><div><span class="admin-kicker">مرحلهٔ ۲ · دادهٔ واقعی گوگل</span><h2>اتصال به Google Search Console</h2></div><span class="seo-step-number">۰۲</span></div>
                    <p class="seo-explainer">تأیید مالکیت سایت و ورود OAuth دو مرحلهٔ جدا هستند: ابتدا متای بالا را در Search Console تأیید کنید؛ سپس با حساب Google دارای دسترسی، اتصال فقط‌خواندنی را انجام دهید. API به‌تنهایی مالکیت دامنه را ایجاد نمی‌کند.</p>
                    <?php if (!$gscConfigured): ?>
                        <div class="admin-alert admin-alert--warning">برای فعال‌کردن اتصال، Client ID، Client Secret و کلید رمزگذاری را در فایل محلی <code>app/config.local.php</code> قرار دهید و افزونهٔ PHP OpenSSL و تنظیم <code>allow_url_fopen=On</code> را فعال کنید؛ این فایل نباید وارد Git شود.</div>
                        <pre class="seo-config-example"><code>'gsc_client_id' =&gt; '...apps.googleusercontent.com',
'gsc_client_secret' =&gt; 'مقدار محرمانه از Google Cloud',
'gsc_token_encryption_key' =&gt; 'کلید تصادفی حداقل ۳۲ نویسه‌ای',</code></pre>
                    <?php else: ?>
                        <div class="seo-connection-row"><span class="status-pill <?= $gscConnected ? 'status-published' : 'status-draft' ?>"><?= $gscConnected ? 'حساب متصل است' : 'حساب متصل نیست' ?></span><?php if ($gscConnected): ?><form method="post" data-confirm="اتصال Search Console از این پنل قطع شود؟"><?= csrf_field() ?><input type="hidden" name="action" value="disconnect_search_console"><input type="hidden" name="return_page" value="seo"><button class="table-action table-action-danger" type="submit">قطع اتصال</button></form><?php endif; ?></div>
                        <p class="field-hint">در Google Cloud، Search Console API را فعال و یک OAuth Web client بسازید. Authorized redirect URI باید دقیقاً این باشد:</p>
                        <code class="seo-callback-uri" dir="ltr"><?= e($gscRedirectUri !== '' ? $gscRedirectUri : 'ابتدا URL امن سایت را در مرحلهٔ ۱ ذخیره کنید.') ?></code>
                        <?php if ($gscError !== ''): ?><div class="admin-alert admin-alert--error"><?= e($gscError) ?></div><?php endif; ?>
                        <?php if (!$gscConnected && $gscRedirectUri !== ''): ?><form method="post" class="seo-connect-form"><?= csrf_field() ?><input type="hidden" name="action" value="connect_search_console"><input type="hidden" name="return_page" value="seo"><button class="admin-button admin-button-primary" type="submit">ورود و اتصال حساب Google <?= icon_svg('arrow-left') ?></button><small>دسترسی درخواستی فقط خواندن گزارش Search Console است.</small></form><?php endif; ?>
                        <?php if ($gscConnected && $gscSites !== []): ?>
                            <form method="post" class="seo-property-form"><?= csrf_field() ?><input type="hidden" name="action" value="save_gsc_property"><input type="hidden" name="return_page" value="seo"><label class="admin-field"><span>ویژگی سایت در Search Console</span><select name="gsc_property_url" required><?php foreach ($gscSites as $site): $siteUrl = (string) ($site['siteUrl'] ?? ''); if ($siteUrl === '') continue; ?><option value="<?= e($siteUrl) ?>"<?= $selectedGscProperty === $siteUrl ? ' selected' : '' ?>><?= e($siteUrl) ?> · <?= e((string) ($site['permissionLevel'] ?? 'دسترسی')) ?></option><?php endforeach; ?></select></label><button class="admin-button admin-button-ghost" type="submit">ذخیرهٔ ویژگی</button></form>
                            <?php if ($selectedGscProperty !== ''): ?><form method="post" class="seo-report-form"><?= csrf_field() ?><input type="hidden" name="action" value="fetch_gsc_report"><input type="hidden" name="return_page" value="seo"><button class="admin-button admin-button-secondary" type="submit">دریافت گزارش ۲۸ روز کامل اخیر <?= icon_svg('chart') ?></button><small>برای کامل‌ترشدن داده‌ها، گزارش تا سه روز قبل را می‌خواند.</small></form><?php endif; ?>
                        <?php elseif ($gscConnected): ?>
                            <div class="admin-alert admin-alert--warning">این حساب هیچ ویژگی Search Console در دسترس ندارد. مطمئن شوید سایت در Search Console ثبت شده و همین حساب حداقل دسترسی لازم را دارد.</div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (is_array($gscReport) && is_array($gscReport['rows'] ?? null)): ?>
                        <div class="seo-report-heading"><div><span class="admin-kicker">عبارت‌ها و صفحه‌های واقعی</span><h3>گزارش <?= fa_num((string) ($gscReport['start'] ?? '')) ?> تا <?= fa_num((string) ($gscReport['end'] ?? '')) ?></h3></div><small>دریافت‌شده در <?= fa_num(date('Y/m/d H:i', (int) ($gscReport['fetched_at'] ?? time()))) ?></small></div>
                        <?php if ($gscReport['rows'] === []): ?><div class="empty-state"><strong>برای این بازه ردیفی برنگشت.</strong><p>ممکن است سایت تازه تأیید شده باشد یا دادهٔ جست‌وجوی کافی نداشته باشد.</p></div>
                        <?php else: ?><div class="table-scroll"><table class="admin-table seo-report-table"><thead><tr><th>عبارت جست‌وجو</th><th>صفحه</th><th>کلیک</th><th>نمایش</th><th>CTR</th><th>جایگاه</th><th>راهنما</th></tr></thead><tbody><?php foreach ($gscReport['rows'] as $row): $query = (string) ($row['keys'][0] ?? ''); $resultUrl = (string) ($row['keys'][1] ?? ''); $resultScheme = strtolower((string) parse_url($resultUrl, PHP_URL_SCHEME)); $safeResultUrl = filter_var($resultUrl, FILTER_VALIDATE_URL) && in_array($resultScheme, ['http', 'https'], true) ? $resultUrl : ''; $impressions = (int) ($row['impressions'] ?? 0); $clicks = (int) ($row['clicks'] ?? 0); $ctr = (float) ($row['ctr'] ?? 0); $position = (float) ($row['position'] ?? 0); $opportunity = $impressions >= 10 && $position >= 4 && $position <= 20 && $ctr < .05; ?><tr><td><strong><?= e($query) ?></strong></td><td><?php if ($safeResultUrl !== ''): ?><a href="<?= e($safeResultUrl) ?>" target="_blank" rel="noopener" dir="ltr" class="seo-result-url"><?= e($safeResultUrl) ?></a><?php else: ?><span class="list-help">—</span><?php endif; ?></td><td><?= fa_num((string) $clicks) ?></td><td><?= fa_num((string) $impressions) ?></td><td><?= fa_num((string) round($ctr * 100, 1)) ?>٪</td><td><?= fa_num(number_format($position, 1)) ?></td><td><?= $opportunity ? '<span class="status-pill status-new">بررسی عنوان/محتوا</span>' : '<span class="list-help">—</span>' ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
                        <p class="field-hint seo-report-note">ردیف‌های دارای نمایش و جایگاه ۴ تا ۲۰، سرنخ بررسی‌اند نه تضمین رتبه؛ عنوان، محتوا و نیت جست‌وجوی همان صفحه را با دقت بازبینی کنید.</p>
                    <?php endif; ?>
                </section>
                <div class="admin-note"><span>!</span><p><strong>نکتهٔ مهم:</strong> ثبت عبارت هدف یا متا به‌تنهایی رتبه را تضمین نمی‌کند. برای هر صفحه عنوان و توضیح یکتا بنویسید، همان عبارت را طبیعی در محتوای مفید پوشش دهید و نتیجه را با دادهٔ Search Console بسنجید. Google متای keywords را نادیده می‌گیرد.</p></div>

            <?php elseif ($entityMeta !== null): ?>
                <?php if ($entityTableError !== ''): ?><div class="admin-alert admin-alert--warning"><?= e($entityTableError) ?></div><?php endif; ?>
                <div class="list-page-heading"><div><span class="admin-kicker">مدیریت محتوا</span><h2><?= e($entityMeta['title']) ?></h2><p><?= $page === 'brands' ? 'برای هر برند یکی از چهار دستهٔ اصلی را تعیین کنید. همهٔ برندهای منتشرشده صفحهٔ مستقل دارند؛ فقط برندهایی که «نمایش در صفحهٔ خانه» برایشان فعال است در خانه دیده می‌شوند.' : 'موارد منتشرشده در سایت نمایش داده می‌شوند. ترتیب نمایش را با عدد مرتب‌سازی تنظیم کنید.' ?></p></div><a class="admin-button admin-button-primary" href="index.php?page=<?= e($page) ?>&action=new"><?= icon_svg('plus') ?> افزودن <?= e($entityMeta['singular']) ?></a></div>
                <?php if ($actionMode === 'new' || $editItem !== null): ?>
                    <?php $formItem = $editItem ?: ['is_published' => 1, 'sort_order' => count($listItems) + 1, 'visual_theme' => 'saffron', 'icon' => 'spark', 'published_at' => date('Y-m-d'), 'media_type' => 'image', 'aspect_ratio' => '4:5', 'is_featured' => 0, 'category' => 'companies']; ?>
                    <section class="admin-card item-form-card"><div class="admin-card-heading"><div><span class="admin-kicker"><?= $editItem ? 'ویرایش مورد' : 'مورد تازه' ?></span><h2><?= $editItem ? e($formItem[$entityMeta['name_field']] ?? $entityMeta['singular']) : 'افزودن ' . e($entityMeta['singular']) ?></h2></div><a class="admin-close" href="index.php?page=<?= e($page) ?>">بستن</a></div>
                        <form class="item-form" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?><input type="hidden" name="action" value="save_item"><input type="hidden" name="return_page" value="<?= e($page) ?>"><input type="hidden" name="entity" value="<?= e($page) ?>"><input type="hidden" name="id" value="<?= e($editItem['id'] ?? 0) ?>">
                            <?php $brandAdvancedFields = ['long_description', 'testimonial_quote', 'testimonial_author', 'testimonial_role', 'seo_title', 'seo_description', 'focus_keywords']; ?>
                            <?php if ($page === 'brands'): ?><div class="admin-form-guide"><strong>برای نمایش یک برند کافی است:</strong><span>نام، پیوند و دسته را تکمیل کنید؛ در صورت نیاز لوگو و تصویر کارت را بارگذاری کنید؛ سپس انتشار و نمایش در صفحهٔ خانه را انتخاب کنید.</span></div><?php endif; ?>
                            <div class="item-fields-grid"><?php foreach ($entityMeta['fields'] as $column => $field): if ($page === 'brands' && in_array($column, $brandAdvancedFields, true)) continue; render_admin_field($column, $field, $formItem, $column === 'media_path' ? $editSlides : []); endforeach; ?></div>
                            <?php if ($page === 'brands'): ?><details class="admin-advanced-fields"><summary>تنظیمات اختیاری: معرفی کامل، دیدگاه و سئو</summary><p>این بخش را فقط برای تکمیل صفحهٔ مستقل برند باز کنید؛ خالی‌گذاشتن آن مشکلی برای ثبت برند ایجاد نمی‌کند.</p><div class="item-fields-grid"><?php foreach ($entityMeta['fields'] as $column => $field): if (!in_array($column, $brandAdvancedFields, true)) continue; render_admin_field($column, $field, $formItem); endforeach; ?></div></details><?php endif; ?>
                            <div class="item-options"><label class="admin-field"><span>ترتیب نمایش</span><input type="number" name="sort_order" min="-9999" max="9999" value="<?= e($formItem['sort_order'] ?? 0) ?>"></label><label class="check-label publish-check"><input type="checkbox" name="is_published" value="1"<?= !empty($formItem['is_published']) ? ' checked' : '' ?>> انتشار صفحهٔ مستقل</label><?php if ($page === 'brands'): ?><label class="check-label publish-check"><input type="checkbox" name="is_featured" value="1"<?= !empty($formItem['is_featured']) ? ' checked' : '' ?>> نمایش در صفحهٔ خانه</label><?php endif; ?></div>
                            <div class="form-actions"><button class="admin-button admin-button-primary" type="submit">ذخیرهٔ <?= e($entityMeta['singular']) ?> <?= icon_svg('check') ?></button><a class="admin-button admin-button-ghost" href="index.php?page=<?= e($page) ?>">انصراف</a></div>
                        </form>
                    </section>
                <?php elseif ($actionMode === 'edit' && $editItem === null): ?><div class="admin-alert admin-alert--warning">این مورد پیدا نشد یا قبلاً حذف شده است.</div><?php endif; ?>
                <section class="admin-card table-card"><div class="admin-card-heading"><div><span class="admin-kicker">فهرست سایت</span><h2><?= fa_num((string) count($listItems)) ?> <?= e($entityMeta['singular']) ?></h2></div><span class="list-help">موارد غیرفعال در سایت دیده نمی‌شوند.</span></div>
                    <?php if ($listItems === []): ?><div class="empty-state"><span>✳</span><strong>هنوز موردی ثبت نشده</strong><p>با دکمهٔ بالا اولین <?= e($entityMeta['singular']) ?> را اضافه کنید.</p></div>
                    <?php else: ?><div class="admin-list-tools" data-admin-list-tools>
                        <label class="admin-list-filter"><span>جست‌وجوی سریع</span><input type="search" data-admin-list-search placeholder="نام، عنوان یا پیوند را بنویسید" autocomplete="off"></label>
                        <label class="admin-list-filter"><span>وضعیت انتشار</span><select data-admin-list-status><option value="">همهٔ موارد</option><option value="1">منتشرشده</option><option value="0">پیش‌نویس</option></select></label>
                        <?php if ($page === 'brands'): ?><label class="admin-list-filter"><span>دستهٔ برند</span><select data-admin-list-category><option value="">همهٔ دسته‌ها</option><?php foreach (brand_category_options() as $categoryKey => $categoryLabel): ?><option value="<?= e($categoryKey) ?>"><?= e($categoryLabel) ?></option><?php endforeach; ?></select></label><?php endif; ?>
                        <span class="admin-list-count" data-admin-list-count aria-live="polite">نمایش <?= fa_num((string) count($listItems)) ?> از <?= fa_num((string) count($listItems)) ?> مورد</span>
                    </div><div class="table-scroll"><table class="admin-table"><thead><tr><th>عنوان</th><th>دسته / شناسه</th><th>ترتیب</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
                        <?php foreach ($listItems as $item): ?><tr data-admin-list-row data-category="<?= e($page === 'brands' ? normalize_brand_category($item['category'] ?? '') : '') ?>" data-published="<?= !empty($item['is_published']) ? '1' : '0' ?>"><td><strong><?= e($item[$entityMeta['name_field']] ?? '') ?></strong><?php if (!empty($item['excerpt'])): ?><small><?= e(limit_admin_text((string) $item['excerpt'], 86)) ?></small><?php elseif (!empty($item['tagline'])): ?><small><?= e($item['tagline']) ?></small><?php endif; ?></td><td><?= e($page === 'brand_media' ? ($entities['brand_media']['fields']['brand_id']['options'][(string) ($item['brand_id'] ?? '')] ?? 'برند حذف‌شده') : ($page === 'brands' ? (brand_category_options()[normalize_brand_category($item['category'] ?? '')] . (!empty($item['is_featured']) ? ' · منتخب صفحهٔ خانه' : '')) : ($item['category'] ?? $item['slug'] ?? $item['company'] ?? '—'))) ?></td><td><?= fa_num((string) ($item['sort_order'] ?? '—')) ?></td><td><span class="status-pill <?= !empty($item['is_published']) ? 'status-published' : 'status-draft' ?>"><?= !empty($item['is_published']) ? 'منتشرشده' : 'پیش‌نویس' ?></span></td><td><div class="table-actions"><a class="table-action" href="index.php?page=<?= e($page) ?>&action=edit&id=<?= e($item['id']) ?>">ویرایش</a><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_item"><input type="hidden" name="return_page" value="<?= e($page) ?>"><input type="hidden" name="entity" value="<?= e($page) ?>"><input type="hidden" name="id" value="<?= e($item['id']) ?>"><button class="table-action" type="submit"><?= !empty($item['is_published']) ? 'پیش‌نویس' : 'انتشار' ?></button></form><form method="post" data-confirm="این مورد برای همیشه حذف شود؟"><?= csrf_field() ?><input type="hidden" name="action" value="delete_item"><input type="hidden" name="return_page" value="<?= e($page) ?>"><input type="hidden" name="entity" value="<?= e($page) ?>"><input type="hidden" name="id" value="<?= e($item['id']) ?>"><button class="table-action table-action-danger" type="submit">حذف</button></form></div></td></tr><?php endforeach; ?>
                        <tr data-admin-list-empty hidden><td colspan="5">با این فیلترها موردی پیدا نشد.</td></tr>
                    </tbody></table></div><?php endif; ?>
                </section>

            <?php elseif ($page === 'inquiries'): ?>
                <?php $inquiries = $pdo->query('SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 250')->fetchAll(); ?>
                <div class="list-page-heading"><div><span class="admin-kicker">گفت‌وگوهای تازه</span><h2>درخواست‌های تماس</h2><p>پیام‌هایی که از فرم تماس سایت می‌رسند، در این بخش نگهداری می‌شوند.</p></div><span class="inquiry-total"><?= fa_num((string) count($inquiries)) ?> پیام</span></div>
                <section class="admin-card table-card"><div class="admin-card-heading"><div><span class="admin-kicker">صندوق ورودی</span><h2>پیام‌های بازدیدکنندگان</h2></div><span class="list-help">حداکثر ۲۵۰ پیام آخر</span></div>
                    <?php if ($inquiries === []): ?><div class="empty-state"><span>✳</span><strong>صندوق ورودی خالی است</strong><p>با دریافت نخستین درخواست تماس، اطلاعات در اینجا نمایش داده می‌شود.</p></div>
                    <?php else: ?><div class="inquiry-list"><?php foreach ($inquiries as $inquiry): ?><article class="inquiry-card"><div class="inquiry-head"><div class="inquiry-person"><span class="inquiry-avatar"><?= e(first_char($inquiry['name'])) ?></span><span><strong><?= e($inquiry['name']) ?></strong><small><?= e($inquiry['subject'] ?: 'درخواست مشاوره') ?> · <?= e(date('Y/m/d H:i', strtotime((string) $inquiry['created_at']))) ?></small></span></div><span class="status-pill status-<?= e($inquiry['status']) ?>"><?= ['new' => 'تازه', 'contacted' => 'پیگیری‌شده', 'closed' => 'بسته‌شده'][$inquiry['status']] ?? 'تازه' ?></span></div><div class="inquiry-contacts"><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $inquiry['phone'])) ?>" dir="ltr"><?= e($inquiry['phone']) ?></a><?php if ($inquiry['email'] !== ''): ?><a href="mailto:<?= e($inquiry['email']) ?>" dir="ltr"><?= e($inquiry['email']) ?></a><?php endif; ?></div><details class="inquiry-message"><summary>مشاهدهٔ پیام</summary><p><?= nl2br(e($inquiry['message'])) ?></p></details><div class="inquiry-actions"><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="update_inquiry"><input type="hidden" name="return_page" value="inquiries"><input type="hidden" name="id" value="<?= e($inquiry['id']) ?>"><select name="status" aria-label="وضعیت پیام"><option value="new"<?= $inquiry['status'] === 'new' ? ' selected' : '' ?>>تازه</option><option value="contacted"<?= $inquiry['status'] === 'contacted' ? ' selected' : '' ?>>پیگیری شد</option><option value="closed"<?= $inquiry['status'] === 'closed' ? ' selected' : '' ?>>بسته شد</option></select><button class="table-action" type="submit">ثبت وضعیت</button></form><form method="post" data-confirm="این پیام حذف شود؟"><?= csrf_field() ?><input type="hidden" name="action" value="delete_inquiry"><input type="hidden" name="return_page" value="inquiries"><input type="hidden" name="id" value="<?= e($inquiry['id']) ?>"><button class="table-action table-action-danger" type="submit">حذف پیام</button></form></div></article><?php endforeach; ?></div><?php endif; ?>
                </section>

            <?php elseif ($page === 'account'): ?>
                <?php $admins = $pdo->query('SELECT id, username, created_at FROM admins ORDER BY id ASC')->fetchAll(); ?>
                <div class="content-intro"><div><span class="admin-kicker">دسترسی امن</span><h2>مدیران و مشخصات حساب</h2><p>نام کاربری یا رمز خود را تغییر دهید. برای همکارانی که به سامانه نیاز دارند، حساب جداگانه بسازید.</p></div><span class="content-intro-mark">◌</span></div>
                <div class="dashboard-columns account-columns">
                    <section class="admin-card"><div class="admin-card-heading"><div><span class="admin-kicker">حساب فعلی</span><h2>تغییر اطلاعات ورود</h2></div></div><form class="account-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="change_profile"><input type="hidden" name="return_page" value="account"><label class="admin-field"><span>نام کاربری تازه</span><input type="text" name="username" value="<?= e($currentAdmin['username']) ?>" required dir="ltr" maxlength="40"></label><label class="admin-field"><span>رمز فعلی</span><input type="password" name="current_password" autocomplete="current-password" required dir="ltr"></label><label class="admin-field"><span>رمز تازه <small>(برای تغییر رمز؛ حداقل ۱۲ نویسه)</small></span><input type="password" name="new_password" autocomplete="new-password" minlength="12" dir="ltr"></label><label class="admin-field"><span>تکرار رمز تازه</span><input type="password" name="new_password_confirmation" autocomplete="new-password" minlength="12" dir="ltr"></label><button class="admin-button admin-button-primary" type="submit">ذخیرهٔ اطلاعات <?= icon_svg('check') ?></button></form></section>
                    <div class="account-side"><section class="admin-card"><div class="admin-card-heading"><div><span class="admin-kicker">همکاران</span><h2>حساب‌های مدیر</h2></div></div><div class="account-admin-list"><?php foreach ($admins as $administrator): ?><div class="account-admin-row"><span class="admin-user-avatar"><?= e(first_char($administrator['username'])) ?></span><span><strong><?= e($administrator['username']) ?></strong><small>عضویت از <?= e(date('Y/m/d', strtotime((string) $administrator['created_at']))) ?></small></span><?php if ((int) $administrator['id'] !== (int) $currentAdmin['id']): ?><form method="post" data-confirm="حساب این مدیر حذف شود؟"><?= csrf_field() ?><input type="hidden" name="action" value="delete_admin"><input type="hidden" name="return_page" value="account"><input type="hidden" name="id" value="<?= e($administrator['id']) ?>"><button class="table-action table-action-danger" type="submit">حذف</button></form><?php else: ?><span class="self-badge">شما</span><?php endif; ?></div><?php endforeach; ?></div></section>
                        <section class="admin-card"><div class="admin-card-heading"><div><span class="admin-kicker">دسترسی تازه</span><h2>افزودن مدیر</h2></div></div><form class="account-form" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="add_admin"><input type="hidden" name="return_page" value="account"><label class="admin-field"><span>نام کاربری</span><input type="text" name="username" required minlength="3" maxlength="40" dir="ltr"></label><label class="admin-field"><span>رمز عبور موقت <small>(حداقل ۱۲ نویسه)</small></span><input type="password" name="password" required minlength="12" dir="ltr"></label><button class="admin-button admin-button-secondary" type="submit">ساخت حساب مدیر <?= icon_svg('plus') ?></button></form></section></div>
                </div>
                <div class="admin-note"><span>!</span><p>همهٔ مدیران دسترسی کامل به محتوا و پیام‌های تماس دارند. رمزها به‌شکل هش‌شده نگهداری می‌شوند؛ برای هر همکار حساب جدا بسازید.</p></div>
            <?php endif; ?>

            <footer class="admin-footer"><span>نگاه مدیا · سامانهٔ مدیریت</span><span>ساخته‌شده با رنگ و نقش ایرانی <b>✳</b></span></footer>
        </div>
    </main>
</div>
</body>
</html>
