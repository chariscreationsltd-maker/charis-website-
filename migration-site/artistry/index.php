<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
$C = content('artistry');
page_start($C['meta'] + ['path' => '/artistry/'], 'artistry');

/** Every Artistry WhatsApp link goes to the Artistry number, never the main studio line. */
function art_wa(string $text): string {
    global $SITE;
    return wa($text, $SITE['artistry']['whatsapp']);
}
function art_url(string $u): string {
    return str_starts_with($u, 'wa:') ? art_wa(substr($u, 3)) : href($u);
}

function art_packages(array $block, string $id): void { ?>
    <div class="section-head reveal">
      <h2 id="<?= e($id) ?>-title"><?= e($block['heading']) ?></h2>
      <p class="lede"><?php if (!empty($block['kicker'])): ?><b><?= e($block['kicker']) ?>.</b> <?php endif; ?><?= e($block['intro']) ?></p>
    </div>
    <ul class="packages" style="--cols:<?= count($block['packages']) ?>">
<?php foreach ($block['packages'] as $p): ?>
      <li class="package<?= !empty($p['featured']) ? ' package--featured' : '' ?> reveal">
<?php if (!empty($p['tag'])): ?>
        <p class="package__tag"><?= e($p['tag']) ?></p>
<?php endif; ?>
        <h3><?= e($p['name']) ?></h3>
        <p class="package__price"><small>UGX</small><?= e($p['price']) ?></p>
        <p class="package__desc"><?= e($p['desc']) ?></p>
        <div class="package__groups"><div>
          <h4>Includes</h4>
          <ul><?php foreach ($p['items'] as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul>
        </div></div>
        <p class="package__best"><b>Best for:</b> <?= e($p['best']) ?></p>
        <?= button('Book This Package', art_wa($p['wa']), !empty($p['featured']) ? 'primary' : 'outline') ?>
      </li>
<?php endforeach; ?>
    </ul>
<?php }

$h = $C['hero'];
// The hero photo is not repeated below it. The work strip needs at least two other photos;
// with only one, that photo sits beside the combined-package block instead.
$work = array_values(array_filter($C['work']['photos'], fn($p) => has_photo($p['name']) && $p['name'] !== $h['photo']));
$showWork = count($work) > 1;
$spare = !$showWork && $work ? $work[0] : null;
// Alternate section backgrounds whichever optional sections are present.
$n = 0;
function tone(): string { global $n; return $n++ % 2 === 0 ? ' section--raised' : ''; }
?>
    <section class="page-hero page-hero--split" aria-labelledby="page-title">
      <div class="wrap">
        <div class="page-hero__inner">
          <div style="display:grid;gap:22px">
<?php if (!empty($h['eyebrow'])): ?>
            <p class="eyebrow"><?= e($h['eyebrow']) ?></p>
<?php endif; ?>
            <h1 id="page-title"><?= e($h['headline']) ?></h1>
            <p class="lede"><?= e($h['support']) ?></p>
            <div class="btn-row">
<?php foreach ($h['buttons'] as $b): ?>
              <?= button($b['label'], art_url($b['url']), $b['variant']) ?>
<?php endforeach; ?>
            </div>
          </div>
<?php if (has_photo($h['photo'])): ?>
          <div class="page-hero__media develop" style="aspect-ratio:4/5"><?= photo($h['photo'], $h['alt'], '(max-width: 860px) 100vw, 46vw', '', false, true) ?></div>
<?php endif; ?>
        </div>
      </div>
    </section>

    <section class="section<?= tone() ?>" aria-labelledby="services-title">
      <div class="wrap">
        <div class="section-head reveal">
          <h2 id="services-title"><?= e($C['services']['heading']) ?></h2>
          <p class="lede"><?= e($C['services']['body']) ?></p>
        </div>
        <ul class="beauty-list">
<?php foreach ($C['services']['items'] as $s): ?>
          <li><strong><?= e($s['name']) ?></strong><span><?= e($s['line']) ?><br><?= button($s['linkLabel'], !empty($s['wa']) ? art_wa($s['wa']) : $s['link'], 'text') ?></span></li>
<?php endforeach; ?>
        </ul>
<?php if (!empty($C['services']['note'])): ?>
        <p class="lede reveal" style="margin-top:32px;max-width:60ch"><?= e($C['services']['note']) ?></p>
<?php endif; ?>
      </div>
    </section>

<?php if ($showWork): ?>
    <section class="section<?= tone() ?>" aria-labelledby="work-title">
      <div class="wrap">
        <div class="section-head reveal">
          <h2 id="work-title"><?= e($C['work']['heading']) ?></h2>
          <p class="lede"><?= e($C['work']['support']) ?></p>
        </div>
        <div class="gallery-strip" style="--n:<?= min(count($work), 4) ?>">
<?php foreach ($work as $w): ?>
          <figure class="develop reveal"><?= photo($w['name'], $w['alt'], '(max-width: 640px) 50vw, 33vw') ?></figure>
<?php endforeach; ?>
        </div>
      </div>
    </section>
<?php endif; ?>

    <section class="section<?= tone() ?>" id="packages" aria-labelledby="wedding-title">
      <div class="wrap">
<?php art_packages($C['wedding'], 'wedding'); ?>
      </div>
    </section>

    <section class="section<?= tone() ?>" id="intro-packages" aria-labelledby="intro-title">
      <div class="wrap">
<?php art_packages($C['intro'], 'intro'); ?>
        <p class="reveal" style="margin-top:32px"><?= button('See All Makeup Packages', href('makeupPackages'), 'text') ?></p>
      </div>
    </section>

    <section class="section<?= tone() ?>" aria-labelledby="why-title">
      <div class="wrap">
        <div class="section-head reveal">
          <h2 id="why-title"><?= e($C['why']['heading']) ?></h2>
          <p class="lede"><?= e($C['why']['intro']) ?></p>
        </div>
        <ul class="advantages">
<?php foreach ($C['why']['items'] as $a): ?>
          <li class="reveal"><h3><?= e($a['title']) ?></h3><p><?= e($a['text']) ?></p></li>
<?php endforeach; ?>
        </ul>
      </div>
    </section>

    <section class="section<?= tone() ?>" aria-labelledby="combined-title">
      <div class="wrap story">
        <div class="story__copy reveal">
          <h2 id="combined-title"><?= e($C['combined']['heading']) ?></h2>
          <p class="lede"><?= e($C['combined']['body']) ?></p>
          <div class="btn-row"><?= button($C['combined']['button'], art_wa($C['combined']['wa']), 'outline') ?></div>
        </div>
<?php if ($spare): ?>
        <div class="story__media develop reveal"><?= photo($spare['name'], $spare['alt'], '(max-width: 860px) 100vw, 50vw') ?></div>
<?php endif; ?>
      </div>
    </section>

    <section class="section close<?= tone() ?>" aria-labelledby="close-title">
      <div class="wrap">
        <h2 id="close-title"><?= e($C['book']['heading']) ?></h2>
        <p class="lede"><?= e($C['book']['body']) ?></p>
        <div class="btn-row">
          <?= button('Book on WhatsApp', art_wa($C['book']['wa']), 'primary') ?>
          <?= button('See All Makeup Packages', href('makeupPackages'), 'outline') ?>
        </div>
        <p class="from" style="margin-top:20px">Artistry WhatsApp <b><a href="<?= e($SITE['artistry']['whatsapp']) ?>" data-track="whatsapp"><?= e($SITE['artistry']['whatsappNumber']) ?></a></b><?php if (!empty($SITE['artistry']['instagram'])): ?> &nbsp;|&nbsp; <b><a href="<?= e($SITE['artistry']['instagram']) ?>">Instagram</a></b><?php endif; ?></p>
      </div>
    </section>
<?php page_end(); ?>
