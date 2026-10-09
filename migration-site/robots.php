<?php
require __DIR__ . '/inc/bootstrap.php';
header('Content-Type: text/plain; charset=utf-8');
$base = rtrim($SITE['baseUrl'], '/');
echo "User-agent: *\n";
echo "Disallow: /content/\nDisallow: /inc/\n";
echo "\nSitemap: {$base}/sitemap.xml\n";
