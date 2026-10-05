<?php

declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$data = load_site_data($pdo);
$allBrands = array_values(array_filter($data['brands'], static fn(array $brand): bool => !empty($brand['is_published'])));
$categoryOptions = brand_category_options();
$categoryCounts = array_fill_keys(array_keys($categoryOptions), 0);
foreach ($allBrands as &$brand) {
    $brand['category'] = normalize_brand_category($brand['category'] ?? '');
    $categoryCounts[$brand['category']]++;
}
unset($brand);
$categoryQuery = $_GET['category'] ?? '';
$requestedCategory = is_string($categoryQuery) ? trim($categoryQuery) : '';
$selectedCategory = array_key_exists($requestedCategory, $categoryOptions) ? $requestedCategory : '';
$brands = $selectedCategory === ''
    ? $allBrands
    : array_values(array_filter($allBrands, static fn(array $brand): bool => $brand['category'] === $selectedCategory));
$settings = $data['settings'];
$directoryTitle = 'همهٔ برندهای همکار | ' . (string) $settings['brand_name'];
$directoryDescription = 'فهرست برندهای منتشرشده و صفحهٔ معرفی و آرشیو هرکدام در ' . (string) $settings['brand_name'] . '.';
$canonicalUrl = seo_absolute_url($settings, 'brands');
$footerCreditUrl = safe_href($settings['footer_credit_url'] ?? '', '');
$primary = safe_color($settings['theme_primary'] ?? '', '#155C5A');
$accent = safe_color($settings['theme_accent'] ?? '', '#BD5D43');
$saffron = safe_color($settings['theme_saffron'] ?? '', '#D7A84A');
$surface = safe_color($settings['theme_surface'] ?? '', '#F6F3EA');
$customFontPath = safe_font_src($settings['custom_font_path'] ?? '');
$customFontFormat = match (strtolower(pathinfo($customFontPath, PATHINFO_EXTENSION))) {
    'woff2' => 'woff2', 'woff' => 'woff', 'ttf' => 'truetype', 'otf' => 'opentype', default => ''
};
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="<?= e(seo_base_href($settings)) ?>">
    <meta name="description" content="<?= e($directoryDescription) ?>">
    <meta name="theme-color" content="<?= e($surface) ?>">
    <?php if ($canonicalUrl !== ''): ?><link rel="canonical" href="<?= e($canonicalUrl) ?>"><meta property="og:url" content="<?= e($canonicalUrl) ?>"><?php endif; ?>
    <meta property="og:type" content="website"><meta property="og:site_name" content="<?= e($settings['brand_name']) ?>"><meta property="og:title" content="<?= e($directoryTitle) ?>"><meta property="og:description" content="<?= e($directoryDescription) ?>"><meta name="twitter:card" content="summary">
    <title><?= e($directoryTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(static_asset_url('assets/css/site.css')) ?>">
    <style>
        :root{--teal:<?= e($primary) ?>;--clay:<?= e($accent) ?>;--saffron:<?= e($saffron) ?>;--paper:<?= e($surface) ?>;}
        <?php if ($customFontPath !== ''): ?>
        @font-face{font-family:'NegaahCustom';src:url('<?= e($customFontPath) ?>') format('<?= e($customFontFormat) ?>');font-weight:100 900;font-display:swap;}
        :root{--font:'NegaahCustom','Vazirmatn',Tahoma,sans-serif;}
        <?php endif; ?>
    </style>
    <script defer src="<?= e(static_asset_url('assets/js/site.js')) ?>"></script>
</head>
<body>
    <div class="scroll-progress" aria-hidden="true"><span></span></div>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="index.php"><span class="brand-symbol" aria-hidden="true"><svg viewBox="0 0 46 46" fill="none"><path d="M4 23C9.6 14.9 16 10.8 23 10.8S36.4 14.9 42 23c-5.6 8.1-12 12.2-19 12.2S9.6 31.1 4 23Z" stroke="currentColor" stroke-width="1.8"/><circle cx="23" cy="23" r="5.7" fill="currentColor"/></svg></span><span class="brand-wordmark"><strong><?= e($settings['brand_name']) ?></strong><small><?= e($settings['brand_descriptor']) ?></small></span></a>
            <a class="header-contact" href="index.php#partners"><span>بازگشت به صفحهٔ اصلی</span><?= icon_svg('arrow-left') ?></a>
        </div>
    </header>

    <main>
        <section class="brand-directory-hero">
            <div class="article-container" data-reveal>
                <div class="eyebrow"><span class="eyebrow-mark"></span>آرشیو برندهای همکار</div>
                <h1>همهٔ برندهای هم‌مسیر</h1>
                <p>هر برند، روایتی مستقل از یک مسیر مشترک است. برای دیدن معرفی و آرشیو هر برند، کارت آن را باز کنید.</p>
                <span class="brand-directory-count"><?= fa_num((string) count($allBrands)) ?> برند منتشرشده</span>
            </div>
        </section>
        <section class="brand-directory-section section-space" id="brand-directory" aria-label="فهرست همهٔ برندها">
            <div class="container">
                <nav class="brand-directory-filters" aria-label="دسته‌بندی برندها">
                    <a class="brand-directory-filter brand-directory-filter--all<?= $selectedCategory === '' ? ' is-active' : '' ?>" href="brands.php#brand-directory"<?= $selectedCategory === '' ? ' aria-current="page"' : '' ?>><span>همهٔ برندها</span><small class="brand-directory-filter-count"><?= fa_num((string) count($allBrands)) ?></small></a>
                    <?php foreach ($categoryOptions as $categoryKey => $categoryLabel): ?>
                        <a class="brand-directory-filter<?= $selectedCategory === $categoryKey ? ' is-active' : '' ?>" href="brands.php?category=<?= e(rawurlencode($categoryKey)) ?>#brand-directory"<?= $selectedCategory === $categoryKey ? ' aria-current="page"' : '' ?>><span><?= e($categoryLabel) ?></span><small class="brand-directory-filter-count"><?= fa_num((string) $categoryCounts[$categoryKey]) ?></small></a>
                    <?php endforeach; ?>
                </nav>
                <div class="brand-directory-results"><h2><?= e($selectedCategory === '' ? 'همهٔ برندها' : $categoryOptions[$selectedCategory]) ?></h2><span><?= fa_num((string) count($brands)) ?> برند در این فهرست</span></div>
                <?php if ($brands !== []): ?>
                    <div class="partners-grid brand-directory-grid">
                        <?php foreach ($brands as $index => $brand): ?>
                            <?php $previewImage = safe_image_src($brand['preview_image'] ?? '', ''); ?>
                            <a class="partner-card partner-card--preview" href="brand.php?slug=<?= e(rawurlencode((string) $brand['slug'])) ?>" aria-label="مشاهدهٔ صفحهٔ <?= e($brand['name']) ?>" data-reveal data-reveal-delay="<?= e((string) (($index % 4) * 45)) ?>">
                                <span class="partner-preview">
                                    <?php if ($previewImage !== ''): ?><img src="<?= e($previewImage) ?>" alt="پیش‌نمایش برند <?= e($brand['name']) ?>" loading="lazy"><?php else: ?><span class="partner-preview-empty"><small>پیش‌نمایش از پنل اضافه می‌شود</small></span><?php endif; ?>
                                </span>
                                <span class="partner-card-details"><span class="partner-card-topline"><span class="partner-number" dir="ltr">NO. <?= fa_num(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span><span class="partner-visit"><?= icon_svg('arrow-left') ?><span>مشاهده صفحه</span></span></span><span class="partner-card-copy"><small class="brand-card-category"><?= e($categoryOptions[$brand['category']]) ?></small><span class="partner-name"><?= e($brand['name']) ?></span><?php if (trim((string) ($brand['short_description'] ?? '')) !== ''): ?><small class="partner-short-description"><?= e($brand['short_description']) ?></small><?php endif; ?></span></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="partners-empty"><strong><?= $selectedCategory === '' ? 'در حال حاضر برندی برای نمایش منتشر نشده است.' : 'هنوز برندی در این دسته ثبت نشده است.' ?></strong></div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer class="site-footer"><div class="container footer-bottom"><span>© <?= fa_num(date('Y')) ?> <?= e($settings['brand_name']) ?></span><?php if ((string) ($settings['footer_show_credit'] ?? '1') === '1'): ?><span class="footer-credit">نگاه مدیا، از خانوادهٔ <?php if ($footerCreditUrl !== ''): ?><a href="<?= e($footerCreditUrl) ?>"<?= (str_starts_with($footerCreditUrl, 'http://') || str_starts_with($footerCreditUrl, 'https://')) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><b>کیان فناوران نگاه</b></a><?php else: ?><b>کیان فناوران نگاه</b><?php endif; ?></span><?php endif; ?></div></footer>
</body>
</html>
