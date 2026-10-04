<?php

declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$data = load_site_data($pdo);
$baseUrl = seo_normalize_site_url($data['settings']['seo_site_url'] ?? '');
$basePath = $baseUrl !== '' ? rtrim((string) parse_url($baseUrl, PHP_URL_PATH), '/') : '';
$pathPrefix = $basePath === '' ? '' : $basePath;
header('Content-Type: text/plain; charset=UTF-8');
echo "User-agent: *\nAllow: /\nDisallow: " . $pathPrefix . "/admin/\nDisallow: " . $pathPrefix . "/install.php\n";
if ($baseUrl !== '') echo 'Sitemap: ' . $baseUrl . "/sitemap.xml\n";
