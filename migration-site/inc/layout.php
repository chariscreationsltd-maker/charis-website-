<?php
/**
 * Page shell: <head>, header, footer. Every page calls page_start() and page_end().
 */

/**
 * @param array $meta  title, description, path (e.g. "/services/"), image (photo set name or /path.jpg)
 * @param string $current  nav key of the current page: projects | services | team | artistry | home
 */
function page_start(array $meta, string $current = ''): void {
    global $SITE;
    $base = rtrim($SITE['baseUrl'], '/');
    $canonical = $base . ($meta['path'] ?? '/');
    $img = $meta['image'] ?? 'og-home';
    $ogImage = $base . (str_starts_with($img, '/') ? $img : "/assets/img/og/{$img}.jpg");
    $title = $meta['title'] ?? $SITE['siteName'];
    $desc = $meta['description'] ?? '';
    ?>
<!doctype html>
<html lang="en-UG"<?= !empty($SITE['analyticsId']) ? ' data-ga="' . e($SITE['analyticsId']) . '"' : '' ?>>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e($desc) ?>">
<?php if (!empty($SITE['noindex'])): ?>
  <meta name="robots" content="noindex, nofollow">
<?php endif; ?>
  <link rel="canonical" href="<?= e($canonical) ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="<?= e($SITE['siteName']) ?>">
  <meta property="og:title" content="<?= e($meta['ogTitle'] ?? $title) ?>">
  <meta property="og:description" content="<?= e($desc) ?>">
  <meta property="og:url" content="<?= e($canonical) ?>">
  <meta property="og:image" content="<?= e($ogImage) ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="theme-color" content="#0a0a0a">
  <link rel="icon" type="image/png" href="/assets/img/logo-symbol.png">
  <link rel="apple-touch-icon" href="/assets/img/logo-symbol.png">
  <link rel="preload" href="/assets/fonts/barlow-condensed-latin-800-normal.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/assets/fonts/barlow-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="<?= asset('assets/css/site.css') ?>">
<?= $meta['head'] ?? '' /* extra head markup for a page, e.g. the kept old pages */ ?>
<?php if (!empty($SITE['analyticsId'])): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(rawurlencode($SITE['analyticsId'])) ?>"></script>
<?php endif; ?>
<?php foreach ($meta['jsonld'] ?? [] as $ld): ?>
  <script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endforeach; ?>
</head>
<body class="page-<?= e($current ?: 'other') ?>">
  <a class="skip-link" href="#main">Skip to content</a>
<?php site_header($current); ?>
  <main id="main">
<?php
}

function site_header(string $current): void {
    global $SITE;
    $nav = [
        'projects' => ['Projects', '/projects/'],
        'services' => ['Services', '/services/'],
        'team'     => ['Our Team', '/creatives/'],
        'artistry' => ['Artistry', '/artistry/'],
        'contact'  => ['Contact', '/#enquiry'],
    ];
    ?>
  <header class="site-header" data-menu="closed">
    <div class="wrap site-header__inner">
      <a class="brand" href="/" aria-label="Charis Creations, home">
        <img src="/assets/img/logo-symbol.png" width="107" height="93" alt="">
        <span class="brand__word">Charis<br><b>Creations</b></span>
      </a>
      <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
        <span class="sr-only">Open menu</span><?= icon('menu', 'i-open') ?><?= icon('close', 'i-close') ?>
      </button>
      <nav class="nav" id="site-nav" aria-label="Main">
        <ul class="nav__list">
<?php foreach ($nav as $key => [$label, $url]): ?>
          <li><a href="<?= e($url) ?>"<?= $key === $current ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach; ?>
        </ul>
        <div class="book" data-open="false">
          <button class="book__toggle" type="button" aria-expanded="false" aria-controls="book-menu">Book Now <?= icon('caret') ?></button>
          <ul class="book__menu" id="book-menu">
            <li><a href="<?= e(href('book')) ?>" data-track="studio-booking"><strong>Studio Session</strong><span>Choose a time online</span></a></li>
            <li><a href="<?= e(href('builder')) ?>" data-track="package-builder"><strong>Wedding or Introduction</strong><span>Build your package</span></a></li>
            <li><a href="<?= e(href('brief')) ?>" data-track="enquiry"><strong>Corporate or Event</strong><span>Tell us about your project</span></a></li>
          </ul>
        </div>
      </nav>
    </div>
  </header>
<?php
}

function page_end(): void {
    global $SITE;
    $s = $SITE['social'];
    ?>
  </main>

  <footer class="site-footer">
    <div class="wrap">
      <div class="site-footer__grid">
        <div class="site-footer__brand">
          <a class="brand" href="/" aria-label="Charis Creations, home">
            <img src="/assets/img/logo-symbol.png" width="107" height="93" alt="">
            <span class="brand__word">Charis<br><b>Creations</b></span>
          </a>
          <p class="site-footer__tagline"><?= e($SITE['tagline']) ?><br><span><?= e($SITE['taglineSub']) ?></span></p>
          <ul class="socials" aria-label="Charis Creations on social media">
            <li><a href="<?= e($s['instagram']) ?>" aria-label="Instagram"><?= icon('instagram') ?></a></li>
            <li><a href="<?= e($s['tiktok']) ?>" aria-label="TikTok"><?= icon('tiktok') ?></a></li>
            <li><a href="<?= e($s['youtube']) ?>" aria-label="YouTube"><?= icon('youtube') ?></a></li>
            <li><a href="<?= e($s['facebook']) ?>" aria-label="Facebook"><?= icon('facebook') ?></a></li>
            <li><a href="<?= e($s['x']) ?>" aria-label="X"><?= icon('x') ?></a></li>
          </ul>
        </div>
        <nav class="site-footer__col" aria-labelledby="f-work">
          <h2 id="f-work">Work</h2>
          <ul>
            <li><a href="/projects/">Projects</a></li>
            <li><a href="/services/#weddings">Weddings</a></li>
            <li><a href="/services/#introductions">Introductions</a></li>
            <li><a href="/services/#studio">Studio</a></li>
            <li><a href="/services/#corporate">Corporate</a></li>
          </ul>
        </nav>
        <nav class="site-footer__col" aria-labelledby="f-plan">
          <h2 id="f-plan">Plan With Us</h2>
          <ul>
            <li><a href="<?= e(href('date')) ?>" data-track="date-check">Check Your Date</a></li>
            <li><a href="<?= e(href('invest')) ?>">Our Prices</a></li>
            <li><a href="<?= e(href('gift')) ?>">Gift a Session</a></li>
          </ul>
        </nav>
        <nav class="site-footer__col" aria-labelledby="f-pol">
          <h2 id="f-pol">Policies</h2>
          <ul>
            <li><a href="/privacy-policy/">Privacy Policy</a></li>
            <li><a href="/terms-of-service/">Terms of Service</a></li>
            <li><a href="/refund-policy/">Refund &amp; Cancellation</a></li>
          </ul>
        </nav>
        <div class="site-footer__col">
          <h2>Contact</h2>
          <ul class="site-footer__contact">
            <li><a href="<?= e($SITE['whatsapp']) ?>" data-track="whatsapp"><?= e($SITE['phone']) ?></a></li>
            <li><a href="tel:<?= e($SITE['phoneAltHref']) ?>"><?= e($SITE['phoneAlt']) ?></a></li>
            <li><a href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a></li>
            <li><address><?= e($SITE['address']) ?></address></li>
          </ul>
        </div>
      </div>
      <p class="site-footer__legal">&copy; 2013&ndash;<?= date('Y') ?> <?= e($SITE['legalName']) ?>. All rights reserved.</p>
    </div>
  </footer>

  <a class="wa-float" href="<?= e($SITE['whatsapp']) ?>" data-track="whatsapp" aria-label="Chat with Charis on WhatsApp"><?= icon('whatsapp') ?></a>
  <script src="<?= asset('assets/js/site.js') ?>" defer></script>
</body>
</html>
<?php
}
