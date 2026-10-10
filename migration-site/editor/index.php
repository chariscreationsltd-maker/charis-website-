<?php
/**
 * The website is edited from CharisOS (Settings > Website, Owner only).
 * There is no sign-in on the public website. api.php and refresh.php in this
 * folder are what CharisOS uses.
 */
require dirname(__DIR__) . '/inc/bootstrap.php';
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Charis Creations</title>
  <style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0a0a0a;color:rgba(244,244,242,.72);font:16px/1.5 system-ui,sans-serif;padding:24px;text-align:center}</style>
</head>
<body>
  <p>The website is edited from CharisOS.</p>
</body>
</html>
