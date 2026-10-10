<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
$C = content('projects');
page_start($C['meta'] + ['path' => '/projects/'], 'projects');
?>
    <section class="page-hero arc-hero" aria-labelledby="page-title">
<?php if (has_photo($C['hero']['photo'] ?? '')): ?>
      <div class="arc-hero__bg" aria-hidden="true">
        <?= photo($C['hero']['photo'], '', '100vw', '', false, true) ?>
<?php if (!empty($C['hero']['video'])): ?>
        <video class="arc-hero__video" data-bg-video="<?= e(media_url($C['hero']['video'])) ?>" muted loop playsinline preload="none" tabindex="-1"></video>
<?php endif; ?>
      </div>
<?php endif; ?>
      <div class="wrap arc-hero__grid">
        <div class="page-hero__inner arc-hero__copy">
          <p class="chip"><span class="chip__dot" aria-hidden="true"></span><?= e($C['hero']['eyebrow']) ?></p>
          <h1 id="page-title"><?= e($C['hero']['headline']) ?></h1>
          <p class="lede"><?= e($C['hero']['support']) ?></p>
          <p><a class="btn btn--primary" href="#galleries">Browse the galleries</a></p>
        </div>
        <div class="arc" data-arc aria-label="Gallery cards. Drag or scroll to turn the deck; choose a card to open its gallery.">
          <div class="arc__stage" data-arc-stage></div>
          <p class="arc__hint" aria-hidden="true"><span>Drag</span><i></i><span>Scroll</span></p>
          <template data-arc-cards>
<?php foreach ($C['gallery']['items'] as $i => $it): ?>
            <a class="arc-card" href="<?= e($it['link'] ?: '#galleries') ?>" aria-label="<?= e($it['title']) ?> gallery">
              <span class="arc-card__motion"><span class="arc-card__surface">
                <img src="<?= e(photo_src($it['photo'], 800)) ?>" alt="" loading="lazy" decoding="async" draggable="false">
                <span class="arc-card__shade"></span>
                <span class="arc-card__top"><span>Gallery <?= sprintf('%02d', $i + 1) ?></span><span>Charis Creations</span></span>
                <span class="arc-card__title"><?= e($it['title']) ?></span>
              </span></span>
            </a>
<?php endforeach; ?>
          </template>
        </div>
      </div>
<?php if (!empty($C['hero']['showreel'])): ?>
      <div class="wrap"><div class="reel"><iframe src="<?= e($C['hero']['showreel']) ?>" title="Charis Creations showreel" loading="lazy" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div></div>
<?php endif; ?>
    </section>


    <section class="section" id="galleries" aria-labelledby="gallery-title">
      <div class="wrap">
        <div class="section-head">
          <h2 id="gallery-title"><?= e($C['gallery']['heading']) ?></h2>
          <p class="lede"><?= e($C['gallery']['support']) ?></p>
        </div>
        <ul class="cats">
<?php foreach ($C['gallery']['items'] as $it):
    $tag = !empty($it['link']) ? 'a' : 'div';
    $attr = !empty($it['link']) ? ' href="' . e($it['link']) . '"' : ''; ?>
          <li class="reveal">
            <<?= $tag ?> class="cat"<?= $attr ?>>
              <div class="cat__media develop"><?= photo($it['photo'], $it['alt'], '(max-width: 520px) 100vw, (max-width: 1000px) 50vw, 25vw') ?></div>
              <h3><?= e($it['title']) ?></h3>
              <p><?= e($it['line']) ?></p>
<?php if (!empty($it['link'])): ?>
              <span class="link-arrow">View Gallery <span class="arrow" aria-hidden="true"><?= icon('arrow') ?></span></span>
<?php endif; ?>
            </<?= $tag ?>>
          </li>
<?php endforeach; ?>
        </ul>
      </div>
    </section>


<?php if (!empty($C['trailers']['items'])): $T = $C['trailers']; ?>
    <section class="section trailers" id="trailers" aria-labelledby="trailers-title">
      <div class="wrap">
        <div class="section-head">
          <p class="chip"><span class="chip__dot" aria-hidden="true"></span><?= e($T['eyebrow']) ?></p>
          <h2 id="trailers-title"><?= e($T['heading']) ?></h2>
        </div>
        <ul class="trailers__grid">
<?php foreach ($T['items'] as $i => $t): ?>
          <li>
            <button class="trailer" type="button" data-trailer="<?= e(media_url($t['video'])) ?>" aria-label="Play <?= e($t['title']) ?>">
              <video src="<?= e(media_url($t['video'])) ?>#t=0.5" muted loop playsinline preload="none" aria-hidden="true"></video>
              <span class="trailer__shade" aria-hidden="true"></span>
              <span class="trailer__play" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
              <span class="trailer__meta"><span class="trailer__type"><?= e($t['type']) ?></span><span class="trailer__title"><?= e($t['title']) ?></span></span>
            </button>
          </li>
<?php endforeach; ?>
        </ul>
      </div>
    </section>
<?php endif; ?>

<?php if (!empty($C['reels']['items'])): $R = $C['reels']; ?>
    <section class="section reels" id="reels" aria-labelledby="reels-title">
      <div class="wrap">
        <div class="section-head">
          <p class="chip"><span class="chip__dot" aria-hidden="true"></span><?= e($R['eyebrow']) ?></p>
          <h2 id="reels-title"><?= e($R['heading']) ?></h2>
          <p class="lede"><?= e($R['support']) ?></p>
        </div>
        <ul class="reels__row">
<?php foreach ($R['items'] as $r): $yt = $r['youtube'] ?? ''; ?>
          <li>
<?php if ($yt): ?>
            <button class="trailer trailer--reel" type="button" data-trailer data-youtube="<?= e($yt) ?>" aria-label="Play <?= e($r['title']) ?>">
              <img src="https://i.ytimg.com/vi/<?= e($yt) ?>/hqdefault.jpg" alt="" loading="lazy" decoding="async">
<?php else: ?>
            <button class="trailer trailer--reel" type="button" data-trailer="<?= e(media_url($r['video'])) ?>" aria-label="Play <?= e($r['title']) ?>">
              <video src="<?= e(media_url($r['video'])) ?>#t=0.5" muted loop playsinline preload="none" aria-hidden="true"></video>
<?php endif; ?>
              <span class="trailer__shade" aria-hidden="true"></span>
              <span class="trailer__play" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
              <span class="trailer__meta"><span class="trailer__type"><?= e($r['type']) ?></span><span class="trailer__title"><?= e($r['title']) ?></span></span>
            </button>
          </li>
<?php endforeach; ?>
        </ul>
      </div>
    </section>
<?php endif; ?>

    <div class="trailer-modal" id="trailer-modal" hidden role="dialog" aria-modal="true" aria-label="Film player">
      <div class="trailer-modal__box">
        <video controls playsinline></video>
        <button class="trailer-modal__close" type="button" aria-label="Close"><?= icon('close') ?></button>
      </div>
    </div>

<?php $cl = $C['close']; finale([
    'id' => 'close', 'label' => $cl['label'] ?? '', 'light' => $cl['headingLight'] ?? '', 'bold' => $cl['headingBold'] ?? '',
    'body' => $cl['body'] ?? '', 'ghost' => $cl['ghost'] ?? '',
    'buttons' => button($cl['button']['label'], href($cl['button']['link']), 'primary') . button('WhatsApp Us', $SITE['whatsapp'], 'ivory'),
]); ?>

    <div class="lightbox" id="lightbox" hidden role="dialog" aria-modal="true" aria-label="Photo story">
      <img src="data:," alt="">
      <button class="lightbox__btn lightbox__close" type="button" aria-label="Close"><?= icon('close') ?></button>
      <button class="lightbox__btn lightbox__prev" type="button" aria-label="Previous photo"><?= icon('arrow') ?></button>
      <button class="lightbox__btn lightbox__next" type="button" aria-label="Next photo"><?= icon('arrow') ?></button>
      <p class="lightbox__count" aria-live="polite"></p>
    </div>
<?php page_end(); ?>
