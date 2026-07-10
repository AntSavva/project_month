<?php

require __DIR__ . '/app/config.php';
require __DIR__ . '/app/storage.php';
require __DIR__ . '/app/auth.php';
require __DIR__ . '/app/helpers.php';

$site = site_read();
$path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$path = preg_replace('#^index\.php/?#', '', $path);

if ($path === 'admin') {
    header('X-Robots-Tag: noindex, nofollow', true);
    require __DIR__ . '/app/admin.php';
    exit;
}

if ($path === 'lead' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    handle_lead_submit();
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET' || $method === 'HEAD') {
    $siteDataPath = root_path('data/site.json');
    $etag = '"' . sha1_file($siteDataPath) . '"';
    header('Cache-Control: public, max-age=300, s-maxage=300, stale-while-revalidate=60');
    header('ETag: ' . $etag);

    if (trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
        http_response_code(304);
        exit;
    }
}

if ($path === 'robots.txt') {
    render_robots($site);
    exit;
}

if ($path === 'sitemap.xml') {
    render_sitemap($site);
    exit;
}

if ($path === '') {
    render_home($site);
    exit;
}

if ($path === 'service') {
    header('Location: /nalichniki', true, 301);
    exit;
}

if ($path === 'about') {
    render_about($site);
    exit;
}

if ($path === 'reviews') {
    render_reviews_page($site);
    exit;
}

if ($path === 'contacts') {
    render_contacts_page($site);
    exit;
}

$page = site_find_page($site, $path);

if ($page) {
    render_page($site, $page);
    exit;
}

http_response_code(404);
header('Cache-Control: no-store');
header_remove('ETag');
render_not_found($site);
