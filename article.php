<?php

declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$data = load_site_data($pdo);
$slug = trim((string) ($_GET['slug'] ?? ''));
$article = null;
if ($slug !== '' && $pdo !== null) {
    try {
        $statement = $pdo->prepare('SELECT * FROM articles WHERE slug = :slug AND is_published = 1 LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $article = $statement->fetch() ?: null;
    } catch (Throwable $exception) {}
}
if ($article === null) {
    foreach ($data['articles'] as $candidate) {
        if ((string) ($candidate['slug'] ?? '') === $slug) {
            $article = $candidate;
            break;
        }
    }
}
if ($article === null) http_response_code(404);
$settings = $data['settings'];
$footerCreditUrl = safe_href($settings['footer_credit_url'] ?? '', '');
$title = $article ? (string) $article['title'] : 'یادداشت پیدا نشد';
$articleImage = safe_image_src($article['image'] ?? '', '');
$seoTitle = trim((string) ($article['seo_title'] ?? '')) ?: ($title . ' | ' . (string) $settings['brand_name']);
$seoDescription = trim((string) ($article['seo_description'] ?? '')) ?: (string) ($article['excerpt'] ?? 'یادداشت‌های نگاه مدیا درباره برند و تبلیغات.');
$canonicalUrl = $article ? seo_absolute_url($settings, 'article/' . rawurlencode((string) $article['slug'])) : '';
$shareImage = $articleImage;
if ($shareImage !== '' && !preg_match('/^https:\/\//i', $shareImage)) $shareImage = seo_absolute_url($settings, $shareImage);
$articleSchema = '';
if ($article && $canonicalUrl !== '') {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $title,
        'description' => $seoDescription,
        'mainEntityOfPage' => $canonicalUrl,
        'publisher' => ['@type' => 'Organization', 'name' => (string) $settings['brand_name']],
    ];
    if ($shareImage !== '') $schema['image'] = [$shareImage];
    if (!empty($article['published_at'])) $schema['datePublished'] = (string) $article['published_at'];
    $articleSchema = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '';
}
$bodyText = trim((string) ($article['body'] ?? ''));
$paragraphs = preg_split('/\R\s*\R/u', $bodyText) ?: [];
$primary = safe_color($settings['theme_primary'] ?? '', '#155C5A');
$accent = safe_color($settings['theme_accent'] ?? '', '#BD5D43');
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
    <meta name="description" content="<?= e($seoDescription) ?>">
    <?php if ($article): ?><link rel="canonical" href="<?= e($canonicalUrl) ?>"><meta property="og:url" content="<?= e($canonicalUrl) ?>"><meta property="og:type" content="article"><?php else: ?><meta name="robots" content="noindex,follow"><?php endif; ?>
    <meta property="og:site_name" content="<?= e($settings['brand_name']) ?>">
    <meta property="og:title" content="<?= e($seoTitle) ?>">
    <meta property="og:description" content="<?= e($seoDescription) ?>">
    <?php if ($shareImage !== ''): ?><meta property="og:image" content="<?= e($shareImage) ?>"><meta name="twitter:card" content="summary_large_image"><?php else: ?><meta name="twitter:card" content="summary"><?php endif; ?>
    <meta name="twitter:title" content="<?= e($seoTitle) ?>"><meta name="twitter:description" content="<?= e($seoDescription) ?>">
    <?php if ($article && !empty($article['published_at'])): ?><meta property="article:published_time" content="<?= e((string) $article['published_at']) ?>"><?php endif; ?>
    <?php if ($articleSchema !== ''): ?><script type="application/ld+json"><?= $articleSchema ?></script><?php endif; ?>
    <title><?= e($seoTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(static_asset_url('assets/css/site.css')) ?>">
    <style>
        :root{--teal:<?= e($primary) ?>;--clay:<?= e($accent) ?>;--paper:<?= e($surface) ?>;}
        <?php if ($customFontPath !== ''): ?>
        @font-face{font-family:'NegaahCustom';src:url('<?= e($customFontPath) ?>') format('<?= e($customFontFormat) ?>');font-weight:100 900;font-display:swap;}
        :root{--font:'NegaahCustom','Vazirmatn',Tahoma,sans-serif;}
        <?php endif; ?>
    </style>
</head>
<body class="site-public">
    <header class="site-header"><div class="container header-inner"><a class="brand" href="index.php"><span class="brand-symbol" aria-hidden="true"><svg viewBox="0 0 46 46" fill="none"><path d="M4 23C9.6 14.9 16 10.8 23 10.8S36.4 14.9 42 23c-5.6 8.1-12 12.2-19 12.2S9.6 31.1 4 23Z" stroke="currentColor" stroke-width="1.8"/><circle cx="23" cy="23" r="5.7" fill="currentColor"/></svg></span><span class="brand-wordmark"><strong><?= e($settings['brand_name']) ?></strong><small><?= e($settings['brand_descriptor']) ?></small></span></a><a class="header-contact" href="index.php#contact"><span>ارتباط با ما</span><?= icon_svg('arrow-left') ?></a></div></header>
    <main>
        <?php if ($article): ?>
            <section class="article-hero"><div class="article-container"><a class="article-back" href="index.php#journal"><?= icon_svg('arrow-left') ?> بازگشت به دفترچه نگاه</a><div class="eyebrow" style="margin-top:26px"><span class="eyebrow-mark"></span><?= e($article['category'] ?? 'یادداشت نگاه') ?></div><h1><?= e($article['title']) ?></h1><p><?= e($article['excerpt'] ?? '') ?></p><?php if ($articleImage !== ''): ?><img class="article-cover" src="<?= e($articleImage) ?>" alt="" loading="lazy"><?php endif; ?></div></section>
            <article class="article-container article-body"><?php foreach ($paragraphs as $paragraph): if (trim($paragraph) !== ''): ?><p><?= nl2br(e(trim($paragraph))) ?></p><?php endif; endforeach; ?><a class="article-back" href="index.php#contact"><?= icon_svg('arrow-left') ?> برای گفت‌وگو دربارهٔ ایده‌تان با ما در تماس باشید</a></article>
        <?php else: ?>
            <section class="article-hero"><div class="article-container"><div class="eyebrow"><span class="eyebrow-mark"></span><?= e($settings['brand_name']) ?></div><h1>این یادداشت پیدا نشد.</h1><p>ممکن است یادداشت حذف یا از حالت انتشار خارج شده باشد.</p><a class="button button-primary" href="index.php#journal" style="margin-top:24px"><span>بازگشت به صفحهٔ اصلی</span><?= icon_svg('arrow-left') ?></a></div></section>
        <?php endif; ?>
    </main>
    <footer class="site-footer"><div class="container footer-bottom"><span>© <?= fa_num(date('Y')) ?> <?= e($settings['brand_name']) ?></span><?php if ((string) ($settings['footer_show_credit'] ?? '1') === '1'): ?><span class="footer-credit">نگاه مدیا، از خانوادهٔ <?php if ($footerCreditUrl !== ''): ?><a href="<?= e($footerCreditUrl) ?>"<?= (str_starts_with($footerCreditUrl, 'http://') || str_starts_with($footerCreditUrl, 'https://')) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><b>کیان فناوران نگاه</b></a><?php else: ?><b>کیان فناوران نگاه</b><?php endif; ?></span><?php endif; ?></div></footer>
</body>
</html>
