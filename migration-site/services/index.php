<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
$C = content('services');
page_start($C['meta'] + ['path' => '/services/'], 'services');

/** Resolve the shorthand links used in services.json. */
function svc_url(string $u): string {
    global $SITE;
    if (str_starts_with($u, 'wa:')) return wa(substr($u, 3));
    if ($u === 'instagram') return $SITE['social']['instagram'];
    return href($u);
}

function render_buttons(array $buttons): void {
    echo '<div class="btn-row">';
    foreach ($buttons as $b) echo button($b['label'], svc_url($b['url']), $b['variant'] ?? 'outline');
    echo '</div>';
}

function render_offer_intro(array $o, string $id): void {
    // Photo and words swap sides from one service to the next.
    static $n = 0;
    $flip = !empty($o['photo']) && has_photo($o['photo']) && ($n++ % 2 === 1); ?>
        <div class="offer__intro<?= $flip ? ' offer__intro--flip' : '' ?>">
          <div class="stack reveal">
            <h2 id="<?= e($id) ?>-title"><?= e($o['headline']) ?></h2>
            <p class="lede"><?= e($o['body']) ?></p>
<?php if (!empty($o['from'])): ?>
            <p class="from">Starting from <b><?= e($o['from']) ?></b></p>
<?php endif; ?>
            <?php render_buttons($o['buttons']); ?>
          </div>
<?php if (!empty($o['photo']) && has_photo($o['photo'])): ?>
          <div class="offer__media develop reveal"><?= photo($o['photo'], $o['alt'] ?? '', '(max-width: 900px) 100vw, 45vw') ?></div>
<?php endif; ?>
        </div>
<?php }

function render_packages(array $packages): void {
    $cols = count($packages) >= 4 ? 4 : count($packages); ?>
        <ul class="packages" style="--cols:<?= min($cols, 4) === 4 ? 2 : $cols ?>">
<?php foreach ($packages as $p): ?>
          <li class="package<?= !empty($p['featured']) ? ' package--featured' : '' ?> reveal">
<?php if (!empty($p['tag'])): ?>
            <p class="package__tag"><?= e($p['tag']) ?></p>
<?php endif; ?>
            <h3><?= e($p['name']) ?></h3>
<?php if (!empty($p['price'])): ?>
            <p class="package__price"><small>UGX</small><?= e($p['price']) ?></p>
<?php endif; ?>
            <p class="package__desc"><?= e($p['desc']) ?></p>
            <div class="package__groups">
<?php foreach ($p['groups'] as $g): ?>
              <div>
                <h4><?= e($g['title']) ?></h4>
                <ul><?php foreach ($g['items'] as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul>
              </div>
<?php endforeach; ?>
            </div>
            <?= button($p['cta']['label'], svc_url($p['cta']['url']), !empty($p['featured']) ? 'primary' : 'outline') ?>
          </li>
<?php endforeach; ?>
        </ul>
<?php }

function render_addons(array $a): void { ?>
        <div class="addons reveal">
          <h3><?= e($a['heading']) ?></h3>
          <ul><?php foreach ($a['items'] as $i): ?><li><strong><?= e($i['name']) ?></strong><span><?= e($i['desc']) ?></span></li><?php endforeach; ?></ul>
        </div>
<?php }
?>
    <section class="page-hero svc-hero" aria-labelledby="page-title">
      <div class="svc-hero__media" aria-hidden="true"><?= photo($C['hero']['photo'] ?? 'feat-couple-white', '', '100vw', '', false, true) ?></div>
      <div class="wrap">
        <div class="page-hero__inner svc-hero__copy">
          <p class="chip"><span class="chip__dot" aria-hidden="true"></span><?= e($C['hero']['eyebrow'] ?? 'Our services') ?></p>
          <h1 id="page-title"><?= e($C['hero']['headline']) ?></h1>
          <p class="lede"><?= e($C['hero']['support']) ?></p>
        </div>
      </div>
    </section>

    <nav class="subnav" aria-label="Services">
      <div class="wrap">
        <ul>
<?php foreach ($C['tabs'] as $t): ?>
          <li><a href="#<?= e($t['id']) ?>"><?= e($t['label']) ?></a></li>
<?php endforeach; ?>
        </ul>
      </div>
    </nav>

    <section class="section offer" id="weddings" aria-labelledby="weddings-title">
      <div class="wrap">
<?php render_offer_intro($C['weddings'], 'weddings'); render_packages($C['weddings']['packages']); render_addons($C['weddings']['addons']); ?>
      </div>
    </section>

    <section class="section section--raised section--tight" aria-labelledby="guides-title">
      <div class="wrap">
        <div class="section-head" style="margin-bottom:28px"><h2 id="guides-title" style="font-size:clamp(1.8rem,3.4vw,2.6rem)"><?= e($C['guides']['heading']) ?></h2></div>
        <ul class="guides">
<?php foreach ($C['guides']['items'] as $g): ?>
          <li><a href="<?= e(href($g['link'])) ?>"><?= e($g['label']) ?><?= icon('arrow') ?></a></li>
<?php endforeach; ?>
        </ul>
      </div>
    </section>

    <section class="section offer" id="introductions" aria-labelledby="introductions-title">
      <div class="wrap">
<?php render_offer_intro($C['introductions'], 'introductions'); render_packages($C['introductions']['packages']); ?>
      </div>
    </section>

    <section class="section offer section--raised" id="kukyala" aria-labelledby="kukyala-title">
      <div class="wrap">
<?php render_offer_intro($C['kukyala'], 'kukyala'); render_packages($C['kukyala']['packages']); ?>
      </div>
    </section>

    <section class="section offer" id="studio" aria-labelledby="studio-title">
      <div class="wrap">
<?php render_offer_intro($C['studio'], 'studio'); render_packages($C['studio']['packages']); render_addons($C['studio']['addons']); ?>
      </div>
    </section>

<?php foreach (['corporate', 'livestream', 'podcast'] as $i => $id): ?>
    <section class="section offer<?= $i % 2 === 0 ? ' section--raised' : '' ?>" id="<?= $id ?>" aria-labelledby="<?= $id ?>-title">
      <div class="wrap">
<?php render_offer_intro($C[$id], $id); ?>
      </div>
    </section>
<?php endforeach; ?>

    <section class="section" aria-labelledby="process-title">
      <div class="wrap">
        <div class="section-head"><h2 id="process-title"><?= e($C['process']['heading']) ?></h2></div>
        <ol class="process">
<?php foreach ($C['process']['steps'] as $i => $s): ?>
          <li class="reveal"><b aria-hidden="true"><?= $i + 1 ?></b><h3><?= e($s['title']) ?></h3><p><?= e($s['text']) ?></p></li>
<?php endforeach; ?>
        </ol>
      </div>
    </section>

<?php $cl = $C['close'];
$btns = '';
foreach ($cl['buttons'] as $i => $b) $btns .= button($b['label'], svc_url($b['url']), $i === 0 ? 'primary' : ($i === 1 ? 'ivory' : 'outline'));
finale([
    'id' => 'close', 'label' => $cl['label'] ?? '', 'light' => $cl['headingLight'] ?? '', 'bold' => $cl['headingBold'] ?? '',
    'body' => $cl['body'] ?? '', 'ghost' => $cl['ghost'] ?? '', 'buttons' => $btns,
]); ?>
<?php page_end(); ?>
