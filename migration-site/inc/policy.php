<?php
/**
 * Renders a policy page from Hudson's approved wording.
 * Drop the approved file into content/policies/<slug>.html (body content only, or a full page:
 * only what is inside <body> or <main> is used). Nothing on this page is written by us.
 */
function render_policy(string $slug, string $title, string $description): void {
    global $SITE;
    $file = SITE_ROOT . "/content/policies/{$slug}.html";
    $body = is_file($file) ? file_get_contents($file) : '';
    if ($body !== '') {
        if (preg_match('~<main[^>]*>(.*)</main>~is', $body, $m) || preg_match('~<body[^>]*>(.*)</body>~is', $body, $m)) $body = $m[1];
        $body = preg_replace('~<(script|style|iframe|form)[^>]*>.*?</\1>~is', '', $body);
        $body = preg_replace('~\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)~i', '', $body);
    }
    page_start(['title' => "$title | Charis Creations", 'description' => $description, 'path' => "/{$slug}/"], '');
    ?>
    <section class="page-hero" aria-labelledby="page-title">
      <div class="wrap">
        <div class="page-hero__inner">
          <h1 id="page-title"><?= e($title) ?></h1>
        </div>
      </div>
    </section>
    <section class="section section--tight">
      <div class="wrap">
<?php if ($body !== ''): ?>
        <div class="prose"><?= $body ?></div>
<?php else: ?>
        <div class="prose">
          <p>This page is being updated. For any question about how we handle your bookings, payments or personal information, please contact us directly.</p>
          <p><a href="<?= e($SITE['whatsapp']) ?>" data-track="whatsapp">WhatsApp <?= e($SITE['phone']) ?></a> or <a href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a></p>
        </div>
<?php endif; ?>
      </div>
    </section>
<?php
    page_end();
}
