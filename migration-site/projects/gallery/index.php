<?php
require dirname(__DIR__, 2) . '/inc/bootstrap.php';
require_once SITE_ROOT . '/inc/galleries.php';
$G = content('galleries')['galleries'];
$key = isset($_GET['c']) ? strtolower(preg_replace('/[^a-z]/i', '', (string) $_GET['c'])) : '';
if (!isset($G[$key])) { http_response_code(404); header('Location: /projects/#galleries', true, 302); exit; }
$g = $G[$key];
$photos = gallery_photos($key);
page_start([
    'title' => $g['title'] . ' | Charis Creations',
    'description' => $g['line'],
    'path' => '/projects/gallery/?c=' . $key,
    'image' => 'og-projects',
], 'projects');
?>
    <section class="page-hero collage-hero" aria-labelledby="page-title">
      <div class="wrap"><div class="page-hero__inner">
        <p><a class="link-arrow collage-back" href="/projects/#galleries"><span class="arrow" aria-hidden="true"><?= icon('arrow') ?></span> All galleries</a></p>
        <h1 id="page-title"><?= e($g['title']) ?></h1>
        <p class="lede"><?= e($g['line']) ?></p>
      </div></div>
    </section>

    <section class="collage-section" aria-label="<?= e($g['title']) ?> photos">
      <div class="wrap">
<?php if ($photos): ?>
        <ul class="collage">
<?php foreach ($photos as $i => $src): ?>
          <li class="collage__item">
            <a href="<?= e($src) ?>" data-lightbox="collage" data-alt="<?= e($g['title'] . ' photo ' . ($i + 1)) ?>" aria-label="Open photo <?= $i + 1 ?> larger">
              <img src="<?= e($src) ?>" alt="<?= e($g['title'] . ' photo ' . ($i + 1)) ?>" loading="<?= $i < 6 ? 'eager' : 'lazy' ?>" decoding="async">
            </a>
          </li>
<?php endforeach; ?>
        </ul>
<?php else: ?>
        <div class="collage-empty">
          <p class="lede">The photos for this gallery are on their way. In the meantime, ask us for a full gallery on WhatsApp.</p>
          <?= button('Chat on WhatsApp', $SITE['whatsapp'], 'primary') ?>
        </div>
<?php endif; ?>
      </div>
    </section>

    <section class="section close" aria-labelledby="close-title">
      <div class="wrap">
        <h2 id="close-title">Your story could be next.</h2>
        <?= button('Check Your Date', href('date'), 'primary') ?>
      </div>
    </section>

    <div class="lightbox" id="lightbox" hidden role="dialog" aria-modal="true" aria-label="<?= e($g['title']) ?>">
      <img src="data:," alt="">
      <button class="lightbox__btn lightbox__close" type="button" aria-label="Close"><?= icon('close') ?></button>
      <button class="lightbox__btn lightbox__prev" type="button" aria-label="Previous photo"><?= icon('arrow') ?></button>
      <button class="lightbox__btn lightbox__next" type="button" aria-label="Next photo"><?= icon('arrow') ?></button>
      <p class="lightbox__count" aria-live="polite"></p>
    </div>
<?php page_end(); ?>
