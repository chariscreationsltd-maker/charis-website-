<?php
require dirname(__DIR__) . '/inc/bootstrap.php';
$C = content('projects');
page_start($C['meta'] + ['path' => '/projects/'], 'projects');
$p = $C['potm'];
$potmPhotos = array_values(array_filter($p['photos'], fn($ph) => has_photo($ph['name'])));
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

    <section class="section section--raised" id="project-of-the-month" aria-labelledby="potm-title">
      <div class="wrap">
        <div class="section-head">
          <h2 id="potm-title"><?= e($p['heading']) ?></h2>
        </div>
        <div class="potm">
<?php if ($potmPhotos): ?>
          <div class="potm__media">
<?php foreach (array_slice($potmPhotos, 0, 3) as $i => $ph): ?>
            <figure class="develop reveal"><?= photo($ph['name'], $ph['alt'], $i === 0 ? '(max-width: 900px) 66vw, 40vw' : '(max-width: 900px) 33vw, 20vw') ?></figure>
<?php endforeach; ?>
          </div>
<?php endif; ?>
          <div class="potm__copy reveal">
            <p class="potm__cat"><?= e($p['category']) ?></p>
            <h3 style="font-size:clamp(2rem,4vw,3rem)"><?= e($p['title']) ?></h3>
            <p class="body"><?= e($p['story']) ?></p>
            <dl>
<?php foreach ($p['details'] as $d): ?>
              <dt><?= e($d['label']) ?></dt><dd><?= e($d['value']) ?></dd>
<?php endforeach; ?>
            </dl>
<?php if ($potmPhotos): ?>
            <p><a class="btn btn--primary" href="/assets/img/photo/<?= e($potmPhotos[0]['name']) ?>-1600.webp" data-lightbox-open="potm"><?= e($p['button']) ?></a></p>
            <div hidden>
<?php foreach ($potmPhotos as $ph): ?>
              <a href="/assets/img/photo/<?= e($ph['name']) ?>-1600.webp" data-lightbox="potm" data-alt="<?= e($ph['alt']) ?>"></a>
<?php endforeach; ?>
            </div>
<?php endif; ?>
          </div>
        </div>
      </div>
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

<?php if (!empty($C['trailers']['items'])): ?>
    <section class="section section--raised" id="trailers" aria-labelledby="trailers-title">
      <div class="wrap">
        <div class="section-head"><h2 id="trailers-title"><?= e($C['trailers']['heading']) ?></h2></div>
        <ul class="cats" style="--cols:3">
<?php foreach ($C['trailers']['items'] as $t): ?>
          <li class="cat">
            <div class="reel" style="margin-top:0"><iframe src="<?= e($t['embed']) ?>" title="<?= e($t['title']) ?>" loading="lazy" allow="fullscreen; picture-in-picture" allowfullscreen></iframe></div>
            <h3><?= e($t['title']) ?></h3>
          </li>
<?php endforeach; ?>
        </ul>
      </div>
    </section>
<?php endif; ?>

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
