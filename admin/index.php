<?php

declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';

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
            ],
        ],
        'brands' => [
            'table' => 'brands', 'title' => 'برندهای همکار', 'singular' => 'برند', 'name_field' => 'name',
            'fields' => [
                'name' => ['label' => 'نام برند', 'type' => 'text', 'required' => true],
                'slug' => ['label' => 'پیوند انگلیسی', 'type' => 'slug', 'required' => true, 'hint' => 'برای برندهای اولیه از شناسهٔ موجود استفاده کنید.'],
                'logo' => ['label' => 'لوگوی برند', 'type' => 'image', 'hint' => 'PNG با زمینهٔ شفاف یا WEBP، حداکثر ۴ مگابایت.'],
                'short_description' => ['label' => 'معرفی کوتاه', 'type' => 'textarea'],
                'long_description' => ['label' => 'متن صفحهٔ برند', 'type' => 'textarea'],
                'testimonial_quote' => ['label' => 'نظر کارفرما (با تأیید ایشان)', 'type' => 'textarea', 'hint' => 'برای رعایت امانت، فقط نقل‌قول واقعی و مورد تأیید برند را منتشر کنید.'],
                'testimonial_author' => ['label' => 'نام گویندهٔ نظر', 'type' => 'text'],
                'testimonial_role' => ['label' => 'سمت یا عنوان گوینده', 'type' => 'text'],
            ],
        ],
        'brand_media' => [
            'table' => 'brand_media', 'title' => 'آرشیو رسانهٔ برندها', 'singular' => 'رسانه', 'name_field' => 'title',
            'fields' => [
                'brand_id' => ['label' => 'برند', 'type' => 'brand_select', 'required' => true, 'options' => $brandOptions],
                'title' => ['label' => 'عنوان محتوا', 'type' => 'text', 'required' => true],
                'caption' => ['label' => 'توضیح کوتاه', 'type' => 'textarea'],
                'media_type' => ['label' => 'نوع محتوا', 'type' => 'select', 'options' => ['image' => 'تصویر', 'video' => 'ویدیو']],
                'aspect_ratio' => ['label' => 'نسبت تصویر', 'type' => 'select', 'options' => ['16:9' => 'افقی · 16:9', '9:16' => 'عمودی · 9:16', '1:1' => 'مربع · 1:1']],
                'media_path' => ['label' => 'فایل تصویر یا ویدیو', 'type' => 'media', 'required' => true, 'hint' => 'تصویر تا ۴ مگابایت؛ MP4/WEBM تا ۱۰۰ مگابایت.'],
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
$validPages = ['dashboard', 'content', 'services', 'projects', 'testimonials', 'articles', 'brands', 'brand_media', 'inquiries', 'account'];
$page = (string) ($_GET['page'] ?? 'dashboard');
if (!in_array($page, $validPages, true)) $page = 'dashboard';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $returnPage = (string) ($_POST['return_page'] ?? 'dashboard');
    if (!in_array($returnPage, $validPages, true)) $returnPage = 'dashboard';

    try {
        if ($action === 'save_content') {
            $defaults = default_settings();
            $upsert = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (:setting_key, :setting_value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            foreach ($defaults as $key => $fallback) {
                if (!array_key_exists($key, $_POST['settings'] ?? [])) continue;
                $value = trim((string) $_POST['settings'][$key]);
                if ($key === 'custom_font_path') {
                    $value = safe_font_src($value);
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
            foreach ($meta['fields'] as $column => $field) {
                if ($field['type'] === 'media') {
                    $value = trim((string) ($_POST['current_' . $column] ?? ''));
                    if (!empty($_POST['remove_' . $column])) $value = '';
                    $mediaType = in_array((string) ($_POST['media_type'] ?? 'image'), ['image', 'video'], true) ? (string) $_POST['media_type'] : 'image';
                    $uploaded = upload_brand_media($_FILES[$column . '_upload'] ?? [], $mediaType);
                    if ($uploaded !== null) $value = $uploaded;
                    $oldType = (string) ($_POST['current_media_type'] ?? '');
                    if ($value !== '' && $oldType !== '' && $oldType !== $mediaType && $uploaded === null) {
                        $validationErrors[] = 'برای تغییر نوع رسانه، فایل تازهٔ همان نوع را بارگذاری کنید.';
                    }
                    if (!empty($field['required']) && $value === '') $validationErrors[] = 'فایل تصویر یا ویدیوی آرشیو الزامی است.';
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
                $values[$column] = $field['type'] === 'textarea' ? limit_admin_text($value, 12000) : limit_admin_text($value, 500);
            }

            if (isset($values['slug']) && $values['slug'] === '') {
                $validationErrors[] = 'پیوند انگلیسی را وارد کنید.';
            }
            if ($validationErrors !== []) {
                set_flash('error', implode(' ', array_unique($validationErrors)));
                redirect('index.php?page=' . rawurlencode($entity) . '&action=' . ($id > 0 ? 'edit&id=' . $id : 'new'));
            }

            $values['sort_order'] = max(-9999, min(9999, (int) ($_POST['sort_order'] ?? 0)));
            $values['is_published'] = isset($_POST['is_published']) ? 1 : 0;
            if (isset($values['published_at']) && $values['published_at'] === '') $values['published_at'] = null;

            $columns = array_keys($values);
            if ($id > 0) {
                $sets = implode(', ', array_map(static fn($column) => '`' . $column . '` = :' . $column, $columns));
                $values['id'] = $id;
                $statement = $pdo->prepare('UPDATE `' . $meta['table'] . '` SET ' . $sets . ' WHERE id = :id');
                $statement->execute($values);
                set_flash('success', 'اطلاعات ' . $meta['singular'] . ' ویرایش شد.');
            } else {
                $columnSql = implode(', ', array_map(static fn($column) => '`' . $column . '`', $columns));
                $parameterSql = implode(', ', array_map(static fn($column) => ':' . $column, $columns));
                $statement = $pdo->prepare('INSERT INTO `' . $meta['table'] . '` (' . $columnSql . ') VALUES (' . $parameterSql . ')');
                $statement->execute($values);
                set_flash('success', $meta['singular'] . ' تازه اضافه شد.');
            }
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

$pageTitles = [
    'dashboard' => 'نمای کلی', 'content' => 'محتوای صفحهٔ اصلی', 'services' => 'مدیریت خدمات',
    'projects' => 'مدیریت نمونه‌کارها', 'testimonials' => 'دیدگاه همراهان', 'articles' => 'دفترچه نگاه',
    'brands' => 'برندهای همکار', 'brand_media' => 'آرشیو رسانهٔ برندها',
    'inquiries' => 'درخواست‌های تماس', 'account' => 'حساب‌های مدیران',
];
$pageTitle = $pageTitles[$page];
$inquiryCount = (int) $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
$navItems = [
    'dashboard' => ['نمای کلی', 'compass'],
    'content' => ['محتوای سایت', 'spark'],
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
$listItems = [];
$entityTableError = '';
if ($entityMeta !== null) {
    try {
        if ($actionMode === 'edit' && $editId > 0) {
            $statement = $pdo->prepare('SELECT * FROM `' . $entityMeta['table'] . '` WHERE id = :id LIMIT 1');
            $statement->execute(['id' => $editId]);
            $editItem = $statement->fetch() ?: null;
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

function render_admin_field(string $column, array $field, array $item): void
{
    $value = $item[$column] ?? ($field['type'] === 'date' ? date('Y-m-d') : '');
    $required = !empty($field['required']) ? ' required' : '';
    $hint = !empty($field['hint']) ? '<small class="field-hint">' . e($field['hint']) . '</small>' : '';
    echo '<label class="admin-field"><span>' . e($field['label']) . (!empty($field['required']) ? ' <b>*</b>' : '') . '</span>';
    if ($field['type'] === 'textarea') {
        echo '<textarea name="' . e($column) . '" rows="5"' . $required . '>' . e($value) . '</textarea>';
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
        echo '<input type="hidden" name="current_' . e($column) . '" value="' . e($value) . '"><input type="hidden" name="current_media_type" value="' . e($mediaType) . '"><input type="file" name="' . e($column) . '_upload" accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm">';
        if ($safeMedia !== '') echo '<label class="check-label"><input type="checkbox" name="remove_' . e($column) . '" value="1"> حذف فایل فعلی</label>';
    } elseif ($field['type'] === 'image') {
        if ((string) $value !== '') echo '<span class="image-current"><img src="../' . e(safe_image_src($value, '')) . '" alt=""><span>تصویر فعلی</span></span>';
        echo '<input type="hidden" name="current_' . e($column) . '" value="' . e($value) . '"><input type="file" name="' . e($column) . '_upload" accept="image/jpeg,image/png,image/webp,image/gif">';
        if ((string) $value !== '') echo '<label class="check-label"><input type="checkbox" name="remove_' . e($column) . '" value="1"> حذف تصویر فعلی</label>';
    } else {
        $type = match ($field['type']) { 'date' => 'date', 'slug' => 'text', default => 'text' };
        $dir = $field['type'] === 'slug' ? ' dir="ltr" class="ltr-input"' : '';
        echo '<input type="' . $type . '" name="' . e($column) . '" value="' . e($value) . '"' . $dir . $required . ' maxlength="' . ($field['type'] === 'slug' ? '220' : '500') . '">';
    }
    echo $hint . '</label>';
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | نگاه مدیا</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css">
    <script defer src="assets/admin.js"></script>
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
                    <section class="admin-card quick-card"><div class="admin-card-heading"><div><span class="admin-kicker">دسترسی سریع</span><h2>از کجا شروع کنیم؟</h2></div></div><div class="quick-links"><a href="index.php?page=content"><span class="quick-icon">✳</span><span><strong>ویرایش صفحهٔ اصلی</strong><small>تیترها، رنگ‌ها و اطلاعات برند</small></span><?= icon_svg('arrow-left') ?></a><a href="index.php?page=projects&action=new"><span class="quick-icon">↗</span><span><strong>افزودن نمونه‌کار</strong><small>یک روایت تازه به ویترین اضافه کنید</small></span><?= icon_svg('arrow-left') ?></a><a href="index.php?page=articles&action=new"><span class="quick-icon">✎</span><span><strong>نوشتن یادداشت</strong><small>فکرهای تازه‌تان را منتشر کنید</small></span><?= icon_svg('arrow-left') ?></a></div></section>
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

            <?php elseif ($entityMeta !== null): ?>
                <?php if ($entityTableError !== ''): ?><div class="admin-alert admin-alert--warning"><?= e($entityTableError) ?></div><?php endif; ?>
                <div class="list-page-heading"><div><span class="admin-kicker">مدیریت محتوا</span><h2><?= e($entityMeta['title']) ?></h2><p>موارد منتشرشده در صفحهٔ اصلی نمایش داده می‌شوند. ترتیب نمایش را با عدد مرتب‌سازی تنظیم کنید.</p></div><a class="admin-button admin-button-primary" href="index.php?page=<?= e($page) ?>&action=new"><?= icon_svg('plus') ?> افزودن <?= e($entityMeta['singular']) ?></a></div>
                <?php if ($actionMode === 'new' || $editItem !== null): ?>
                    <?php $formItem = $editItem ?: ['is_published' => 1, 'sort_order' => count($listItems) + 1, 'visual_theme' => 'saffron', 'icon' => 'spark', 'published_at' => date('Y-m-d'), 'media_type' => 'image', 'aspect_ratio' => '16:9']; ?>
                    <section class="admin-card item-form-card"><div class="admin-card-heading"><div><span class="admin-kicker"><?= $editItem ? 'ویرایش مورد' : 'مورد تازه' ?></span><h2><?= $editItem ? e($formItem[$entityMeta['name_field']] ?? $entityMeta['singular']) : 'افزودن ' . e($entityMeta['singular']) ?></h2></div><a class="admin-close" href="index.php?page=<?= e($page) ?>">بستن</a></div>
                        <form class="item-form" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?><input type="hidden" name="action" value="save_item"><input type="hidden" name="return_page" value="<?= e($page) ?>"><input type="hidden" name="entity" value="<?= e($page) ?>"><input type="hidden" name="id" value="<?= e($editItem['id'] ?? 0) ?>">
                            <div class="item-fields-grid"><?php foreach ($entityMeta['fields'] as $column => $field) render_admin_field($column, $field, $formItem); ?></div>
                            <div class="item-options"><label class="admin-field"><span>ترتیب نمایش</span><input type="number" name="sort_order" min="-9999" max="9999" value="<?= e($formItem['sort_order'] ?? 0) ?>"></label><label class="check-label publish-check"><input type="checkbox" name="is_published" value="1"<?= !empty($formItem['is_published']) ? ' checked' : '' ?>> انتشار در سایت</label></div>
                            <div class="form-actions"><button class="admin-button admin-button-primary" type="submit">ذخیرهٔ <?= e($entityMeta['singular']) ?> <?= icon_svg('check') ?></button><a class="admin-button admin-button-ghost" href="index.php?page=<?= e($page) ?>">انصراف</a></div>
                        </form>
                    </section>
                <?php elseif ($actionMode === 'edit' && $editItem === null): ?><div class="admin-alert admin-alert--warning">این مورد پیدا نشد یا قبلاً حذف شده است.</div><?php endif; ?>
                <section class="admin-card table-card"><div class="admin-card-heading"><div><span class="admin-kicker">فهرست سایت</span><h2><?= fa_num((string) count($listItems)) ?> <?= e($entityMeta['singular']) ?></h2></div><span class="list-help">موارد غیرفعال در سایت دیده نمی‌شوند.</span></div>
                    <?php if ($listItems === []): ?><div class="empty-state"><span>✳</span><strong>هنوز موردی ثبت نشده</strong><p>با دکمهٔ بالا اولین <?= e($entityMeta['singular']) ?> را اضافه کنید.</p></div>
                    <?php else: ?><div class="table-scroll"><table class="admin-table"><thead><tr><th>عنوان</th><th>دسته / شناسه</th><th>ترتیب</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
                        <?php foreach ($listItems as $item): ?><tr><td><strong><?= e($item[$entityMeta['name_field']] ?? '') ?></strong><?php if (!empty($item['excerpt'])): ?><small><?= e(limit_admin_text((string) $item['excerpt'], 86)) ?></small><?php elseif (!empty($item['tagline'])): ?><small><?= e($item['tagline']) ?></small><?php endif; ?></td><td><?= e($page === 'brand_media' ? ($entities['brand_media']['fields']['brand_id']['options'][(string) ($item['brand_id'] ?? '')] ?? 'برند حذف‌شده') : ($item['category'] ?? $item['slug'] ?? $item['company'] ?? '—')) ?></td><td><?= fa_num((string) ($item['sort_order'] ?? '—')) ?></td><td><span class="status-pill <?= !empty($item['is_published']) ? 'status-published' : 'status-draft' ?>"><?= !empty($item['is_published']) ? 'منتشرشده' : 'پیش‌نویس' ?></span></td><td><div class="table-actions"><a class="table-action" href="index.php?page=<?= e($page) ?>&action=edit&id=<?= e($item['id']) ?>">ویرایش</a><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_item"><input type="hidden" name="return_page" value="<?= e($page) ?>"><input type="hidden" name="entity" value="<?= e($page) ?>"><input type="hidden" name="id" value="<?= e($item['id']) ?>"><button class="table-action" type="submit"><?= !empty($item['is_published']) ? 'پیش‌نویس' : 'انتشار' ?></button></form><form method="post" data-confirm="این مورد برای همیشه حذف شود؟"><?= csrf_field() ?><input type="hidden" name="action" value="delete_item"><input type="hidden" name="return_page" value="<?= e($page) ?>"><input type="hidden" name="entity" value="<?= e($page) ?>"><input type="hidden" name="id" value="<?= e($item['id']) ?>"><button class="table-action table-action-danger" type="submit">حذف</button></form></div></td></tr><?php endforeach; ?>
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
