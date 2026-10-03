<?php

declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$data = load_site_data($pdo);
$slug = trim((string) ($_GET['slug'] ?? ''));
$brand = null;
$media = [];

if ($slug !== '' && $pdo !== null) {
    try {
        $statement = $pdo->prepare('SELECT * FROM brands WHERE slug = :slug AND is_published = 1 LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $brand = $statement->fetch() ?: null;
        if ($brand) {
            try {
                $mediaStatement = $pdo->prepare('SELECT * FROM brand_media WHERE brand_id = :brand_id AND is_published = 1 AND media_path <> \'\' ORDER BY sort_order ASC, id DESC');
                $mediaStatement->execute(['brand_id' => (int) $brand['id']]);
                $media = $mediaStatement->fetchAll();
            } catch (Throwable $exception) {
                $media = [];
            }
        }
    } catch (Throwable $exception) {
        $brand = null;
    }
}
if ($brand === null) {
    foreach ($data['brands'] as $candidate) {
        if ((string) ($candidate['slug'] ?? '') === $slug) {
            $brand = $candidate;
            break;
        }
    }
}
if ($brand === null) http_response_code(404);

$settings = $data['settings'];
$brandName = (string) ($brand['name'] ?? 'برند پیدا نشد');
$logo = safe_image_src($brand['logo'] ?? '', '');
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
    <meta name="description" content="<?= e($brand['short_description'] ?? ('صفحهٔ معرفی و آرشیو محتوایی ' . $brandName . ' در نگاه مدیا.')) ?>">
    <meta name="theme-color" content="<?= e($surface) ?>">
    <title><?= e($brandName) ?> | برندهای همکار نگاه مدیا</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/site.css">
    <style>
        :root{--teal:<?= e($primary) ?>;--clay:<?= e($accent) ?>;--saffron:<?= e($saffron) ?>;--paper:<?= e($surface) ?>;}
        <?php if ($customFontPath !== ''): ?>
        @font-face{font-family:'NegaahCustom';src:url('<?= e($customFontPath) ?>') format('<?= e($customFontFormat) ?>');font-weight:100 900;font-display:swap;}
        :root{--font:'NegaahCustom','Vazirmatn',Tahoma,sans-serif;}
        <?php endif; ?>
    </style>
    <script defer src="assets/js/site.js"></script>
</head>
<body>
    <div class="scroll-progress" aria-hidden="true"><span></span></div>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="index.php#partners"><span class="brand-symbol" aria-hidden="true"><svg viewBox="0 0 46 46" fill="none"><path d="M4 23C9.6 14.9 16 10.8 23 10.8S36.4 14.9 42 23c-5.6 8.1-12 12.2-19 12.2S9.6 31.1 4 23Z" stroke="currentColor" stroke-width="1.8"/><circle cx="23" cy="23" r="5.7" fill="currentColor"/></svg></span><span class="brand-wordmark"><strong><?= e($settings['brand_name']) ?></strong><small><?= e($settings['brand_descriptor']) ?></small></span></a>
            <a class="header-contact" href="index.php#partners"><span>همهٔ برندهای همکار</span><?= icon_svg('arrow-left') ?></a>
        </div>
    </header>

    <main>
        <?php if ($brand): ?>
            <section class="brand-page-hero">
                <div class="container brand-profile-grid">
                    <div class="brand-profile-mark" data-reveal>
                        <?php if ($logo !== ''): ?><img src="<?= e($logo) ?>" alt="لوگوی <?= e($brandName) ?>"><?php else: ?><span><?= e(first_char($brandName)) ?></span><?php endif; ?>
                        <i aria-hidden="true"></i>
                    </div>
                    <div class="brand-profile-copy" data-reveal data-reveal-delay="90">
                        <div class="eyebrow"><span class="eyebrow-mark"></span>برند همکار نگاه مدیا</div>
                        <h1><?= e($brandName) ?></h1>
                        <?php if (trim((string) ($brand['short_description'] ?? '')) !== ''): ?>
                            <p><?= e($brand['short_description']) ?></p>
                        <?php else: ?>
                            <p>صفحهٔ معرفی، بازخورد و آرشیو محتوایی <?= e($brandName) ?>.</p>
                        <?php endif; ?>
                        <a class="button button-primary" href="#brand-archive"><span>دیدن آرشیو محتوا</span><?= icon_svg('arrow-down') ?></a>
                    </div>
                </div>
            </section>

            <?php if (trim((string) ($brand['long_description'] ?? '')) !== ''): ?>
                <section class="brand-story-section"><div class="article-container brand-story-copy"><div class="eyebrow"><span class="eyebrow-mark"></span>روایت همکاری</div><p><?= nl2br(e($brand['long_description'])) ?></p></div></section>
            <?php endif; ?>

            <section class="brand-testimonial-section">
                <div class="container brand-testimonial-panel" data-reveal>
                    <div class="brand-testimonial-label"><span class="quote-mark">“</span><div><span class="eyebrow">از زبان کارفرما</span><small>بازخورد تأییدشدهٔ همکاری</small></div></div>
                    <?php if (trim((string) ($brand['testimonial_quote'] ?? '')) !== ''): ?>
                        <blockquote><?= e($brand['testimonial_quote']) ?></blockquote>
                        <div class="brand-testimonial-author"><strong><?= e($brand['testimonial_author'] ?: $brandName) ?></strong><?php if (trim((string) ($brand['testimonial_role'] ?? '')) !== ''): ?><span><?= e($brand['testimonial_role']) ?></span><?php endif; ?></div>
                    <?php else: ?>
                        <div class="brand-testimonial-empty">دیدگاه کارفرما پس از دریافت و تأیید، در این بخش منتشر می‌شود.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="brand-archive-section section-space" id="brand-archive">
                <div class="container">
                    <div class="section-heading section-heading-split" data-reveal>
                        <div><div class="eyebrow"><span class="eyebrow-mark"></span>آرشیو محتوایی برند</div><h2>قاب‌هایی از روایت <?= e($brandName) ?></h2></div>
                        <div class="heading-side"><p>ویدیوها و تصاویر این آرشیو با حفظ نسبت اصلی‌شان نمایش داده می‌شوند؛ افقی، عمودی یا مربعی.</p><span class="archive-count"><?= fa_num((string) count($media)) ?> محتوا</span></div>
                    </div>

                    <?php if ($media !== []): ?>
                        <div class="brand-media-columns">
                            <?php foreach ($media as $index => $item): ?>
                                <?php
                                $mediaPath = safe_media_src($item['media_path'] ?? '', '');
                                $posterPath = safe_image_src($item['poster_path'] ?? '', '');
                                if ($mediaPath === '') continue;
                                $ratio = in_array(($item['aspect_ratio'] ?? ''), ['16:9', '9:16', '1:1'], true) ? $item['aspect_ratio'] : '16:9';
                                $ratioClass = $ratio === '9:16' ? 'portrait' : ($ratio === '1:1' ? 'square' : 'landscape');
                                $extension = strtolower(pathinfo((string) (parse_url($mediaPath, PHP_URL_PATH) ?: ''), PATHINFO_EXTENSION));
                                $videoMime = $extension === 'webm' ? 'video/webm' : 'video/mp4';
                                ?>
                                <article class="brand-media-card" data-reveal data-reveal-delay="<?= e((string) (($index % 3) * 70)) ?>">
                                    <div class="brand-media-frame brand-media-frame--<?= e($ratioClass) ?>">
                                        <?php if (($item['media_type'] ?? 'image') === 'video'): ?>
                                            <video controls playsinline preload="metadata"<?= $posterPath !== '' ? ' poster="' . e($posterPath) . '"' : '' ?>><source src="<?= e($mediaPath) ?>" type="<?= e($videoMime) ?>">مرورگر شما از پخش این ویدیو پشتیبانی نمی‌کند.</video>
                                        <?php else: ?>
                                            <img src="<?= e($mediaPath) ?>" alt="<?= e($item['title'] ?? $brandName) ?>" loading="lazy">
                                        <?php endif; ?>
                                    </div>
                                    <?php if (trim((string) ($item['title'] ?? '')) !== '' || trim((string) ($item['caption'] ?? '')) !== ''): ?>
                                        <div class="brand-media-caption"><?php if (trim((string) ($item['title'] ?? '')) !== ''): ?><h3><?= e($item['title']) ?></h3><?php endif; ?><?php if (trim((string) ($item['caption'] ?? '')) !== ''): ?><p><?= e($item['caption']) ?></p><?php endif; ?></div>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="brand-archive-empty"><span class="archive-empty-mark">✳</span><strong>آرشیو این برند در حال تکمیل است.</strong><p>به‌زودی محتوای تصویری و ویدیویی این صفحه اضافه می‌شود.</p></div>
                    <?php endif; ?>
                </div>
            </section>
            <section class="brand-next-section"><div class="container"><a class="brand-next-link" href="index.php#partners">بازگشت به فهرست برندهای همکار <?= icon_svg('arrow-left') ?></a><a class="brand-next-contact" href="index.php#contact">همکاری تازه‌ای در ذهن دارید؟</a></div></section>
        <?php else: ?>
            <section class="article-hero"><div class="article-container"><div class="eyebrow"><span class="eyebrow-mark"></span><?= e($settings['brand_name']) ?></div><h1>این صفحهٔ برند پیدا نشد.</h1><p>ممکن است صفحه حذف یا از حالت انتشار خارج شده باشد.</p><a class="button button-primary" href="index.php#partners" style="margin-top:24px"><span>بازگشت به برندهای همکار</span><?= icon_svg('arrow-left') ?></a></div></section>
        <?php endif; ?>
    </main>
    <footer class="site-footer"><div class="container footer-bottom"><span>© <?= fa_num(date('Y')) ?> <?= e($settings['brand_name']) ?></span><span class="footer-credit">نگاه مدیا، از خانوادهٔ <b>کیان فناوران نگاه</b></span></div></footer>
</body>
</html>
