<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
$C = content('projects');
page_start($C['meta'] + ['path' => '/projects/'], 'projects');
?>
    <section class="page-hero" aria-labelledby="page-title">
      <div class="wrap">
        <div class="page-hero__inner">
          <p class="eyebrow"><?= e($C['hero']['eyebrow']) ?></p>
          <h1 id="page-title"><?= e($C['hero']['headline']) ?></h1>
          <p class="lede"><?= e($C['hero']['support']) ?></p>
        </div>
<?php if (!empty($C['hero']['showreel'])): ?>
        <div class="reel"><iframe src="<?= e($C['hero']['showreel']) ?>" title="Charis Creations showreel" loading="lazy" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>
<?php endif; ?>
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
            <button class="trailer" type="button" data-trailer="<?= e($t['video']) ?>" aria-label="Play <?= e($t['title']) ?>">
              <video src="<?= e($t['video']) ?>#t=0.5" muted loop playsinline preload="none" aria-hidden="true"></video>
              <span class="trailer__shade" aria-hidden="true"></span>
              <span class="trailer__play" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></span>
              <span class="trailer__meta"><span class="trailer__type"><?= e($t['type']) ?></span><span class="trailer__title"><?= e($t['title']) ?></span></span>
            </button>
          </li>
<?php endforeach; ?>
        </ul>
      </div>
      <div class="trailer-modal" id="trailer-modal" hidden role="dialog" aria-modal="true" aria-label="Trailer">
        <div class="trailer-modal__box">
          <video controls playsinline></video>
          <button class="trailer-modal__close" type="button" aria-label="Close"><?= icon('close') ?></button>
        </div>
      </div>
    </section>
<?php endif; ?>

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


    <section class="section close" aria-labelledby="close-title">
      <div class="wrap">
        <h2 id="close-title"><?= e($C['close']['heading']) ?></h2>
        <?= button($C['close']['button']['label'], href($C['close']['button']['link']), 'primary') ?>
      </div>
    </section>

    <div class="lightbox" id="lightbox" hidden role="dialog" aria-modal="true" aria-label="Photo story">
      <img src="data:," alt="">
      <button class="lightbox__btn lightbox__close" type="button" aria-label="Close"><?= icon('close') ?></button>
      <button class="lightbox__btn lightbox__prev" type="button" aria-label="Previous photo"><?= icon('arrow') ?></button>
      <button class="lightbox__btn lightbox__next" type="button" aria-label="Next photo"><?= icon('arrow') ?></button>
      <p class="lightbox__count" aria-live="polite"></p>
    </div>
<?php page_end(); ?>
