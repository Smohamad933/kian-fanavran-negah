<?php

declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$data = load_site_data($pdo);
$baseUrl = seo_normalize_site_url($data['settings']['seo_site_url'] ?? '');
header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: all');
if ($baseUrl === '') {
    http_response_code(503);
    echo '<?xml version="1.0" encoding="UTF-8"?><error>Set the public site URL in the SEO admin panel.</error>';
    exit;
}

$paths = ['', 'brands'];
if ($pdo !== null) {
    try {
        foreach ($pdo->query('SELECT slug FROM brands WHERE is_published = 1 ORDER BY sort_order ASC, id ASC')->fetchAll() as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug !== '') $paths[] = 'brand/' . rawurlencode($slug);
        }
        foreach ($pdo->query('SELECT slug FROM articles WHERE is_published = 1 ORDER BY published_at DESC, id DESC')->fetchAll() as $row) {
            $slug = trim((string) ($row['slug'] ?? ''));
            if ($slug !== '') $paths[] = 'article/' . rawurlencode($slug);
        }
    } catch (Throwable $exception) {
        http_response_code(503);
        echo '<?xml version="1.0" encoding="UTF-8"?><error>Could not load published pages.</error>';
        exit;
    }
} else {
    foreach (default_brands() as $brand) {
        if (!empty($brand['is_published']) && !empty($brand['slug'])) $paths[] = 'brand/' . rawurlencode((string) $brand['slug']);
    }
    foreach (default_articles() as $article) {
        if (!empty($article['is_published']) && !empty($article['slug'])) $paths[] = 'article/' . rawurlencode((string) $article['slug']);
    }
}

$paths = array_values(array_unique($paths));
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach ($paths as $path) {
    $url = $path === '' ? $baseUrl . '/' : $baseUrl . '/' . $path;
    echo '<url><loc>' . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>';
}
echo '</urlset>';
