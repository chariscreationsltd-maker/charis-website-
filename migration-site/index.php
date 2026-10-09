<?php
require __DIR__ . '/inc/bootstrap.php';
$C = content('home');
$base = rtrim($SITE['baseUrl'], '/');

page_start($C['meta'] + [
    'path' => '/',
    'jsonld' => [[
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $SITE['legalName'],
        'url' => $base . '/',
        'image' => $base . '/assets/img/og/og-home.jpg',
        'telephone' => $SITE['phoneHref'],
        'email' => $SITE['email'],
        'foundingDate' => $SITE['founded'],
        'slogan' => $SITE['tagline'],
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Block 103 / Plot 801, Mungu Valley Road, Namugongo Sonde',
            'addressLocality' => 'Mukono',
            'postOfficeBoxNumber' => '151380',
            'addressCountry' => 'UG',
        ],
        'sameAs' => array_values(array_filter($SITE['social'], fn($v, $k) => $k[0] !== '_', ARRAY_FILTER_USE_BOTH)),
    ]],
], 'home');

$banner = active_banner();
$h = $C['hero'];
?>
<?php if ($banner): ?>
    <aside class="banner" aria-label="Seasonal offer">
      <div class="wrap">
        <p><?= e($banner['text']) ?></p>
        <?= button($banner['button'], href($banner['link']), 'text') ?>
      </div>
    </aside>
<?php endif; ?>

    <section class="hero" aria-labelledby="hero-title">
      <div class="wall" aria-hidden="true">
<?php
$rows = [range(1, 6), range(7, 12), range(13, 18)];
foreach ($rows as $ri => $row) {
    $imgs = '';
    foreach ($row as $n) {
        $f = sprintf('/assets/img/hero/wall-%02d.webp', $n);
        if (!is_file(SITE_ROOT . $f)) continue;
        [$w, $hh] = getimagesize(SITE_ROOT . $f);
        $load = ($ri === 0 && $n <= 4) ? ' fetchpriority="high"' : ' loading="lazy"';
        $imgs .= sprintf('<img src="%s" width="%d" height="%d" alt="" decoding="async"%s>', $f, $w, $hh, $load);
    }
    // Each row repeats its photos once so the drift loops without a seam.
    echo '        <div class="wall__row">' . $imgs . $imgs . "</div>\n";
}
?>
      </div>
      <div class="wrap hero__copy">
        <p class="eyebrow"><?= e($h['eyebrow']) ?></p>
        <h1 id="hero-title"><?php foreach ($h['headline'] as $line): ?><span><?= e($line) ?></span> <?php endforeach; ?></h1>
        <p class="lede"><?= e($h['support']) ?></p>
        <div class="btn-row">
          <?= button($h['primary']['label'], href($h['primary']['link']), 'primary') ?>
          <?= button($h['secondary']['label'], href($h['secondary']['link']), 'outline') ?>
        </div>
        <p class="hero__motto"><?= e($h['motto']) ?></p>
      </div>
    </section>

    <section class="trusted" aria-labelledby="trusted-title">
      <div class="wrap">
        <h2 id="trusted-title"><?= e($C['trusted']['heading']) ?></h2>
        <ul class="logos">
<?php foreach ($C['trusted']['logos'] as $l):
    $file = '/assets/img/logos/' . $l['file'];
    if (!is_file(SITE_ROOT . $file)) continue;
    [$w, $hh] = getimagesize(SITE_ROOT . $file);
    $dh = (int) ($l['h'] ?? 48); ?>
          <li><img src="<?= e($file) ?>" width="<?= (int) round($w * $dh / $hh) ?>" height="<?= $dh ?>" style="--h:<?= $dh ?>px" alt="<?= e($l['name']) ?>" loading="lazy"></li>
<?php endforeach; ?>
        </ul>
      </div>
    </section>

    <section class="section" id="work" aria-labelledby="work-title">
      <div class="wrap">
        <div class="section-head">
          <h2 id="work-title"><?= e($C['featured']['heading']) ?></h2>
        </div>
      </div>
        <ul class="work">
<?php foreach ($C['featured']['items'] as $i => $it):
    if (!has_photo($it['photo'])) continue;
    $meta = trim($it['type'] . ($it['place'] ? ' | ' . $it['place'] : '')); ?>
          <li class="work__item reveal">
            <a class="work__open" href="/assets/img/photo/<?= e($it['photo']) ?>-1600.webp" data-lightbox="work" data-alt="<?= e($it['alt']) ?>" data-caption="<?= e($it['title'] . ' | ' . $meta) ?>" aria-label="View <?= e($it['title']) ?> larger">
              <span class="work__media develop"><?= photo($it['photo'], $it['alt'], '(max-width: 760px) 78vw, 380px') ?></span>
            </a>
            <div>
              <h3><?= e($it['title']) ?></h3>
              <p class="work__meta"><?= e($meta) ?></p>
<?php if (!empty($it['link'])): ?>
              <p style="margin-top:10px"><?= button($it['linkLabel'] ?: 'View Photos', href($it['link']), 'text') ?></p>
<?php endif; ?>
            </div>
          </li>
<?php endforeach; ?>
        </ul>
      <div class="wrap">
        <p style="margin-top:clamp(40px,5vw,64px)"><?= button('See All Our Work', '/projects/', 'text') ?></p>
      </div>
    </section>

    <section class="section section--raised" id="create" aria-labelledby="create-title">
      <div class="wrap">
        <div class="section-head">
          <h2 id="create-title"><?= e($C['create']['heading']) ?></h2>
        </div>
        <ul class="tiles">
<?php foreach ($C['create']['items'] as $it): ?>
          <li class="reveal">
            <a class="tile develop" href="/services/#<?= e($it['anchor']) ?>">
              <?= photo($it['photo'], $it['alt'], '(max-width: 600px) 100vw, (max-width: 900px) 50vw, 33vw') ?>
              <div class="tile__body">
                <h3><?= e($it['title']) ?></h3>
                <p><?= e($it['line']) ?></p>
                <span class="link-arrow">Explore <span class="arrow" aria-hidden="true"><?= icon('arrow') ?></span></span>
              </div>
            </a>
          </li>
<?php endforeach; ?>
        </ul>
      </div>
    </section>

    <section class="section" id="story" aria-labelledby="story-title">
      <div class="wrap story">
        <div class="story__media develop reveal"><?= photo($C['story']['photo'], $C['story']['alt'], '(max-width: 860px) 100vw, 50vw') ?></div>
        <div class="story__copy reveal">
          <h2 id="story-title"><?= e($C['story']['heading']) ?></h2>
          <p class="lede"><?= e($C['story']['body']) ?></p>
          <dl class="facts">
<?php foreach ($C['story']['facts'] as $f): ?>
            <div><dt><?= e($f['label']) ?></dt><dd><?= e($f['value']) ?></dd></div>
<?php endforeach; ?>
          </dl>
          <p><?= button($C['story']['link']['label'], $C['story']['link']['url'], 'text') ?></p>
        </div>
      </div>
    </section>

    <section class="section section--raised" id="experience" aria-labelledby="experience-title">
      <div class="wrap">
        <div class="section-head">
          <h2 id="experience-title"><?= e($C['journey']['heading']) ?></h2>
          <p class="lede"><?= e($C['journey']['intro']) ?></p>
        </div>
        <ol class="journey">
<?php foreach ($C['journey']['steps'] as $i => $s): ?>
          <li class="journey__step reveal">
<?php if (!empty($s['screen']) && has_photo($s['screen'])): ?>
            <div class="journey__phone"><?= photo($s['screen'], $s['title'] . ' on a phone', '220px') ?></div>
<?php endif; ?>
            <span class="journey__num" aria-hidden="true"><?= $i + 1 ?></span>
            <h3><?= e($s['title']) ?></h3>
            <p><?= e($s['text']) ?></p>
          </li>
<?php endforeach; ?>
        </ol>
        <p class="closing reveal"><?= e($C['journey']['closing'][0]) ?><br><span><?= e($C['journey']['closing'][1]) ?></span></p>
      </div>
    </section>

    <section class="section" id="testimonials" aria-labelledby="voices-title">
      <div class="wrap">
        <div class="section-head">
          <h2 id="voices-title"><?= e($C['voices']['heading']) ?></h2>
        </div>
        <iframe class="embed embed--testimonials" src="<?= e(href('testimonials')) ?>" loading="lazy" title="What our clients say" data-embed="hide-section"></iframe>
      </div>
    </section>

    <section class="section section--raised" id="starting-points" aria-labelledby="start-title">
      <div class="wrap start">
        <div class="section-head reveal" style="margin-bottom:0">
          <h2 id="start-title"><?= e($C['start']['heading']) ?></h2>
          <p class="lede"><?= e($C['start']['body']) ?></p>
        </div>
        <div class="reveal">
          <ul class="start__list">
<?php foreach ($C['start']['items'] as $it): ?>
            <li><span><?= e($it['label']) ?></span><?php if (!empty($it['price'])): ?> <b>From UGX <?= e($it['price']) ?></b><?php endif; ?></li>
<?php endforeach; ?>
          </ul>
          <?= button($C['start']['button']['label'], href($C['start']['button']['link']), 'outline') ?>
        </div>
      </div>
    </section>

    <section class="section" id="faq" aria-labelledby="faq-title">
      <div class="wrap faq-wrap">
        <h2 id="faq-title" class="reveal"><?= e($C['faq']['heading']) ?></h2>
        <div class="faq reveal">
<?php foreach ($C['faq']['items'] as $i => $f): ?>
          <details name="faq"<?= $i === 0 ? ' open' : '' ?>>
            <summary><?= e($f['q']) ?><?= icon('plus') ?></summary>
            <p class="faq__a"><?= rich($f['a']) ?></p>
          </details>
<?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="section section--raised" id="enquiry" aria-labelledby="enquiry-title">
      <div class="wrap enquiry">
        <div class="enquiry__aside">
          <h2 id="enquiry-title"><?= e($C['enquiry']['heading']) ?></h2>
          <p class="lede"><?= e($C['enquiry']['body']) ?></p>
          <p><?= button($C['enquiry']['whatsappLine'], $SITE['whatsapp'], 'text') ?></p>
          <ul class="enquiry__contacts">
            <li><?= icon('phone') ?><span><a href="tel:<?= e($SITE['phoneHref']) ?>"><?= e($SITE['phone']) ?></a><br><a href="tel:<?= e($SITE['phoneAltHref']) ?>"><?= e($SITE['phoneAlt']) ?></a></span></li>
            <li><?= icon('mail') ?><a href="mailto:<?= e($SITE['email']) ?>"><?= e($SITE['email']) ?></a></li>
            <li><?= icon('pin') ?><address><?= e($SITE['address']) ?></address></li>
          </ul>
        </div>
        <div>
          <iframe class="embed embed--enquiry" src="<?= e(href('enquiry')) ?>" title="Enquiry form" data-embed="fallback" data-fallback="enquiry-fallback"></iframe>
          <div class="embed-fallback" id="enquiry-fallback" hidden>
            <p class="lede">The enquiry form did not load here. You can open it on its own page or message us on WhatsApp.</p>
            <div class="btn-row">
              <?= button('Send an Enquiry', href('enquiry'), 'primary') ?>
              <?= button('Chat on WhatsApp', $SITE['whatsapp'], 'outline') ?>
            </div>
          </div>
          <p class="enquiry__note"><?= e(preg_replace('/Privacy Policy\.$/', '', $C['enquiry']['privacyLine'])) ?><a href="/privacy-policy/">Privacy Policy</a>.</p>
        </div>
      </div>
    </section>
    <div class="lightbox" id="lightbox" hidden role="dialog" aria-modal="true" aria-label="Featured work">
      <img src="data:," alt="">
      <button class="lightbox__btn lightbox__close" type="button" aria-label="Close"><?= icon('close') ?></button>
      <button class="lightbox__btn lightbox__prev" type="button" aria-label="Previous project"><?= icon('arrow') ?></button>
      <button class="lightbox__btn lightbox__next" type="button" aria-label="Next project"><?= icon('arrow') ?></button>
      <p class="lightbox__count" aria-live="polite"></p>
    </div>
<?php page_end(); ?>
