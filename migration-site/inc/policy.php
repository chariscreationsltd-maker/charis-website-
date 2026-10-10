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
        // The page hero already shows the title; take the file's own <h1> out of the body.
        $body = preg_replace('~<h1[^>]*>.*?</h1>~is', '', $body, 1);
        // The "Last updated" line moves up under the title.
        if (preg_match('~<(div|p)[^>]*class="updated"[^>]*>(.*?)</\1>~is', $body, $u)) {
            $updated = trim(strip_tags($u[2]));
            $body = str_replace($u[0], '', $body);
        }
        // Links between the policy files point at this site's pages.
        $body = preg_replace('~href="(privacy-policy|terms-of-service|refund-policy)\.html"~', 'href="/$1/"', $body);
    }
    page_start(['title' => "$title | Charis Creations", 'description' => $description, 'path' => "/{$slug}/"], '');
    ?>
    <section class="page-hero policy-hero" aria-labelledby="page-title">
      <div class="wrap">
        <div class="page-hero__inner">
          <h1 id="page-title"><?= e($title) ?></h1>
<?php if (!empty($updated)): ?>
          <p class="policy-updated"><?= e($updated) ?></p>
<?php endif; ?>
        </div>
      </div>
    </section>
    <section class="section section--tight policy-body">
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
