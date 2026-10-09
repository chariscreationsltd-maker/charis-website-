<?php
// The artistry page as it is on the current site (from the WordPress export), inside the
// site's own header and footer. Content: content/legacy/artistry.body.html; styles, scoped
// to .lg-artistry so they cannot leak into the header or footer: content/legacy/artistry.css.
// The untouched original is content/legacy/artistry.original.html.
require dirname(__DIR__) . '/inc/bootstrap.php';
$dir = SITE_ROOT . '/content/legacy/';
$head = '  <link rel="preconnect" href="https://fonts.googleapis.com">' . "\n"
      . '  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n"
      . '  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;900&amp;family=Playfair+Display:ital,wght@0,400;0,700;1,400&amp;display=swap">' . "\n"
      . "  <style>.page-artistry .wa-float{display:none}</style>\n"
      . "  <style>\n" . file_get_contents($dir . 'artistry.css') . "\n  </style>\n";
page_start(['title' => 'Makeup & Beauty | Charis Artistry', 'description' => 'Charis Artistry: bridal and special occasion makeup in Kampala by the Charis Creations team.', 'path' => '/artistry/', 'image' => 'og-artistry', 'head' => $head], 'artistry');
?>
    <div class="lg-artistry">
<?php readfile($dir . 'artistry.body.html'); ?>
    </div>
<?php page_end(); ?>
