<?php
require __DIR__ . '/inc/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
$base = rtrim($SITE['baseUrl'], '/');
// Public addresses only (handover section 08). Never list CharisOS app pages here.
$pages = ['/', '/projects/', '/services/', '/creatives/', '/artistry/', '/privacy-policy/', '/terms-of-service/', '/refund-policy/'];
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($pages as $p) {
    $file = SITE_ROOT . ($p === '/' ? '/index.php' : $p . 'index.php');
    $mod = is_file($file) ? date('Y-m-d', filemtime($file)) : date('Y-m-d');
    echo "  <url><loc>" . e($base . $p) . "</loc><lastmod>$mod</lastmod></url>\n";
}
echo "</urlset>\n";
