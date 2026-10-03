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
$articleImage = safe_image_src($article['image'] ?? '', '');
$bodyText = trim((string) ($article['body'] ?? ''));
$paragraphs = preg_split('/\R\s*\R/u', $bodyText) ?: [];
$title = $article ? (string) $article['title'] : 'یادداشت پیدا نشد';
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
    <meta name="description" content="<?= e($article['excerpt'] ?? 'یادداشت‌های نگاه مدیا درباره برند و تبلیغات.') ?>">
    <title><?= e($title) ?> | <?= e($settings['brand_name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/site.css">
    <style>
        :root{--teal:<?= e($primary) ?>;--clay:<?= e($accent) ?>;--paper:<?= e($surface) ?>;}
        <?php if ($customFontPath !== ''): ?>
        @font-face{font-family:'NegaahCustom';src:url('<?= e($customFontPath) ?>') format('<?= e($customFontFormat) ?>');font-weight:100 900;font-display:swap;}
        :root{--font:'NegaahCustom','Vazirmatn',Tahoma,sans-serif;}
        <?php endif; ?>
    </style>
</head>
<body>
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
