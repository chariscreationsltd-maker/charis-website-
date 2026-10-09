<?php
// The creatives page as it is on the current site (from the WordPress export), inside the
// site's own header and footer. Content: content/legacy/creatives.body.html; styles, scoped
// to .lg-creatives so they cannot leak into the header or footer: content/legacy/creatives.css.
// The untouched original is content/legacy/creatives.original.html.
require dirname(__DIR__) . '/inc/bootstrap.php';
$dir = SITE_ROOT . '/content/legacy/';
$head = '  <link rel="preconnect" href="https://fonts.googleapis.com">' . "\n"
      . '  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n"
      . '  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@300;400;700;900&amp;family=Barlow:wght@300;400;500&amp;display=swap">' . "\n"
      . ""
      . "  <style>\n" . file_get_contents($dir . 'creatives.css') . "\n  </style>\n";
page_start(['title' => 'Meet the Creators | Charis Creations', 'description' => 'Meet the Charis Creations team: photographers, cinematographers, editors and storytellers in Kampala.', 'path' => '/creatives/', 'image' => 'og-team', 'head' => $head], 'team');
?>
    <div class="lg-creatives">
<?php readfile($dir . 'creatives.body.html'); ?>
    </div>
<?php page_end(); ?>
