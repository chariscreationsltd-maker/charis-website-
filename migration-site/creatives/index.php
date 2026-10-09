<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
$C = content('team');
page_start($C['meta'] + ['path' => '/creatives/'], 'team');
$h = $C['hero'];
$people = array_values(array_filter($C['people']['items'], fn($p) => !empty($p['name'])));
$bts = array_values(array_filter($C['behind']['photos'], fn($p) => has_photo($p['name'])));
?>
    <section class="page-hero page-hero--split" aria-labelledby="page-title">
      <div class="wrap">
        <div class="page-hero__inner">
          <div style="display:grid;gap:22px">
            <p class="eyebrow"><?= e($h['eyebrow']) ?></p>
            <h1 id="page-title"><?= e($h['headline']) ?></h1>
            <p class="lede"><?= e($h['support']) ?></p>
          </div>
          <div class="page-hero__media develop" style="aspect-ratio:3/4"><?= photo($h['photo'], $h['alt'], '(max-width: 860px) 100vw, 46vw', '', false, true) ?></div>
        </div>
      </div>
    </section>

    <section class="section section--raised" aria-labelledby="origin-title">
      <div class="wrap story" style="grid-template-columns:minmax(0,0.8fr) minmax(0,1.2fr)">
        <h2 id="origin-title" class="reveal"><?= e($C['origin']['heading']) ?></h2>
        <div class="story__copy reveal">
          <p class="lede"><?= e($C['origin']['body']) ?></p>
          <dl class="facts">
<?php foreach ($C['origin']['facts'] as $f): ?>
            <div><dt><?= e($f['label']) ?></dt><dd><?= e($f['value']) ?></dd></div>
<?php endforeach; ?>
          </dl>
        </div>
      </div>
    </section>

<?php if ($people): ?>
    <section class="section" aria-labelledby="people-title">
      <div class="wrap">
        <div class="section-head"><h2 id="people-title"><?= e($C['people']['heading']) ?></h2></div>
        <ul class="people">
<?php foreach ($people as $p): ?>
          <li class="person reveal">
<?php if (has_photo($p['photo'] ?? '')): ?>
            <div class="person__media develop"><?= photo($p['photo'], $p['name'], '(max-width: 960px) 50vw, 25vw') ?></div>
<?php endif; ?>
            <h3><?= e($p['name']) ?></h3>
            <p class="person__role"><?= e($p['role']) ?></p>
            <p><?= e($p['line']) ?></p>
          </li>
<?php endforeach; ?>
        </ul>
      </div>
    </section>
<?php endif; ?>

<?php if ($bts): ?>
    <section class="section<?= $people ? ' section--raised' : '' ?>" aria-labelledby="behind-title">
      <div class="wrap story">
        <div class="story__copy reveal">
          <h2 id="behind-title"><?= e($C['behind']['heading']) ?></h2>
          <p class="lede"><?= e($C['behind']['support']) ?></p>
        </div>
        <div class="story__media develop reveal"><?= photo($bts[0]['name'], $bts[0]['alt'], '(max-width: 860px) 100vw, 50vw') ?></div>
      </div>
    </section>
<?php endif; ?>

    <section class="section close section--raised" aria-labelledby="close-title">
      <div class="wrap">
        <h2 id="close-title"><?= e($C['close']['heading']) ?></h2>
        <?= button($C['close']['button']['label'], href($C['close']['button']['link']), 'primary') ?>
      </div>
    </section>
<?php page_end(); ?>
