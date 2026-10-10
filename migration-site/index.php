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
      <div class="hero__glow" aria-hidden="true"></div>
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
      <svg class="hero__ribbon" aria-hidden="true" focusable="false" viewBox="0 0 1440 600" preserveAspectRatio="none" fill="none"><path d="M-40 420 C 260 260, 520 520, 760 330 S 1180 120, 1500 260" stroke="#f16623" stroke-width="26" stroke-linecap="round"/><path d="M-40 470 C 300 330, 560 560, 820 380 S 1200 200, 1500 320" stroke="#ff7a3a" stroke-width="8" stroke-linecap="round" opacity="0.8"/></svg>
      <div class="wrap hero__copy">
        <p class="chip"><span class="chip__dot" aria-hidden="true"></span><?= e($h['eyebrow']) ?></p>
        <h1 id="hero-title"><?php foreach ($h['headline'] as $line): ?><span><?= e($line) ?></span> <?php endforeach; ?></h1>
        <p class="lede"><?= e($h['support']) ?></p>
        <div class="btn-row">
          <?= button($h['primary']['label'], href($h['primary']['link']), 'primary') ?>
          <?= button($h['secondary']['label'], href($h['secondary']['link']), 'outline') ?>
        </div>
        <p class="hero__motto"><?= e($h['motto']) ?></p>
      </div>
      <p class="hero__ghost" aria-hidden="true">Charis</p>
    </section>

<?php
$t = $C['tagline'];
$strip = array_values(array_filter($t['strip'], fn($s) => has_photo($s['photo'])));
$half = intdiv(count($strip), 2);
$rows = [$strip, array_merge(array_slice($strip, $half), array_slice($strip, 0, $half))];
?>
    <section class="tagline" aria-labelledby="tagline-title">
      <div class="tagline__head">
        <h2 id="tagline-title" class="tagline__words">
<?php foreach ($t['words'] as $i => $w): ?>
          <?php if ($i): ?><span class="tagline__dot" aria-hidden="true">·</span><?php endif; ?><span<?= $i === count($t['words']) - 1 ? ' class="is-accent"' : '' ?>><?= e($w) ?></span>
<?php endforeach; ?>
        </h2>
        <p class="tagline__body"><?= e($t['body']) ?></p>
      </div>
      <div class="strips">
<?php foreach ($rows as $ri => $row): ?>
        <div class="strips__row strips__row--<?= $ri ? 'right' : 'left' ?>">
<?php foreach ([false, true] as $copy): foreach ($row as $s): ?>
          <a class="strips__item" href="<?= e($t['link']) ?>"<?= $copy ? ' aria-hidden="true" tabindex="-1"' : '' ?>>
            <img src="<?= e(photo_src($s['photo'], 800)) ?>" alt="" width="280" height="185" loading="lazy" decoding="async">
            <span class="strips__cap"><?= e($s['caption']) ?></span>
          </a>
<?php endforeach; endforeach; ?>
        </div>
<?php endforeach; ?>
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
<?php if (!empty($it['video'])): ?>
            <span class="work__media work__media--video">
              <video src="<?= e($it['video']) ?>" poster="<?= e(photo_src($it['photo'], 800)) ?>" autoplay muted loop playsinline preload="metadata" aria-label="<?= e($it['alt']) ?>" data-autoplay></video>
            </span>
<?php else: ?>
            <a class="work__open" href="<?= e(photo_src($it['photo'], 1600)) ?>" data-lightbox="work" data-alt="<?= e($it['alt']) ?>" data-caption="<?= e($it['title'] . ' | ' . $meta) ?>" aria-label="View <?= e($it['title']) ?> larger">
              <span class="work__media develop"><?= photo($it['photo'], $it['alt'], '(max-width: 760px) 78vw, 330px') ?></span>
            </a>
<?php endif; ?>
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
        <p class="work__all"><?= button('See All Our Work', '/projects/', 'text') ?></p>
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

    <section class="trusted" aria-labelledby="trusted-title">
      <div class="wrap">
        <h2 id="trusted-title"><?= e($C['trusted']['heading']) ?></h2>
      </div>
<?php
$logos = [];
foreach ($C['trusted']['logos'] as $l) {
    $file = '/assets/img/logos/' . $l['file'];
    if (!is_file(SITE_ROOT . $file)) continue;
    [$w, $hh] = getimagesize(SITE_ROOT . $file);
    $dh = (int) ($l['h'] ?? 48);
    $logos[] = ['src' => $file, 'name' => $l['name'], 'h' => $dh, 'w' => (int) round($w * $dh / $hh)];
}
$mid = (int) ceil(count($logos) / 2);
$logoRows = [array_slice($logos, 0, $mid), array_slice($logos, $mid)];
?>
      <div class="logo-strips">
<?php foreach ($logoRows as $ri => $row): if (!$row) continue; ?>
        <ul class="logo-strips__row logo-strips__row--<?= $ri ? 'right' : 'left' ?>">
<?php foreach ([false, true, true, true] as $copy): foreach ($row as $l): ?>
          <li<?= $copy ? ' aria-hidden="true"' : '' ?>><img src="<?= e($l['src']) ?>" width="<?= $l['w'] ?>" height="<?= $l['h'] ?>" style="--h:<?= $l['h'] ?>px" alt="<?= $copy ? '' : e($l['name']) ?>" loading="lazy"></li>
<?php endforeach; endforeach; ?>
        </ul>
<?php endforeach; ?>
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

<?php
require_once SITE_ROOT . '/inc/testimonials.php';
$voices = live_testimonials();
// Until approved reviews arrive from CharisOS, show the studio's own lines (never as client reviews).
$studio = !$voices;
if ($studio) $voices = array_map(fn($c) => ['quote' => $c['quote'], 'name' => $c['name'], 'event' => $c['kicker'], 'date' => '', 'rating' => 0, 'link' => $c['link'] ?? ''], $C['voices']['placeholders']);
$tones = ['ember', 'ivory', 'graphite', 'burnt'];
// Photo column. Real reviews show only the client's own photo (sent by CharisOS
// with their consent) or no photo at all. The studio's own lines, shown until
// reviews arrive, use portfolio images as decoration.
$voicePhotos = array_values(array_filter(array_map(fn($x) => $x['photo'], $C['tagline']['strip']), 'has_photo'));
?>
    <section class="voices" id="testimonials" aria-labelledby="voices-title">
      <div class="wrap voices__head">
        <h2 id="voices-title"><?= e($C['voices']['heading']) ?></h2>
        <p class="voices__count" aria-hidden="true"><span data-stack-now>01</span> / <?= sprintf('%02d', count($voices)) ?></p>
      </div>
      <ol class="voices__deck" data-stack aria-label="Client reviews">
<?php foreach ($voices as $i => $v):
    $len = mb_strlen($v['quote']);
    $size = $len > 360 ? ' is-long' : ($len > 180 ? ' is-mid' : '');
    $ph = $studio ? ($voicePhotos ? photo_src($voicePhotos[$i % count($voicePhotos)], 800) : '') : ($v['photo'] ?? ''); ?>
        <li class="voice voice--<?= $tones[$i % count($tones)] ?><?= $size ?><?= $ph ? '' : ' voice--nophoto' ?>" style="z-index: <?= count($voices) - $i ?>">
          <figure class="voice__inner">
            <div class="voice__copy">
              <p class="voice__kicker"><?= e(implode(' · ', array_filter([$v['event'], $v['date']]))) ?: 'Charis client' ?></p>
              <?php if ($studio): ?><p class="voice__quote voice__quote--studio"><?= e($v['quote']) ?></p><?php else: ?><blockquote class="voice__quote"><p><?= e($v['quote']) ?></p></blockquote><?php endif; ?>
              <figcaption class="voice__by">
<?php if ($v['rating']): ?>
                <span class="voice__stars" role="img" aria-label="<?= $v['rating'] ?> out of 5 stars"><?= str_repeat('<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6L2.5 9.4l6.6-.8z"/></svg>', $v['rating']) ?></span>
<?php endif; ?>
                <cite class="voice__name"><?= e($v['name']) ?></cite>
<?php if (!empty($v['link'])): ?>
                <a class="btn btn--ivory voice__cta" href="<?= e($SITE['whatsapp']) ?>" data-track="whatsapp"><?= e($v['link']) ?></a>
<?php endif; ?>
              </figcaption>
            </div>
<?php if ($ph): ?>
            <div class="voice__media"><img src="<?= e($ph) ?>" alt="<?= $studio ? '' : e('Photo from ' . $v['name'] . '’s ' . ($v['event'] ?: 'event')) ?>" loading="lazy" decoding="async" referrerpolicy="no-referrer"></div>
<?php endif; ?>
          </figure>
        </li>
<?php endforeach; ?>
      </ol>
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
<?php $cl = $C['closing']; finale([
    'id' => 'finale', 'label' => $cl['label'], 'light' => $cl['headingLight'], 'bold' => $cl['headingBold'], 'body' => $cl['body'],
    'ghost' => $cl['ghost'] ?? 'Together', 'socials' => true,
    'buttons' => button($cl['primary']['label'], href($cl['primary']['link']), 'primary') . button($cl['secondary']['label'], $SITE['whatsapp'], 'ivory'),
]); ?>
    <div class="lightbox" id="lightbox" hidden role="dialog" aria-modal="true" aria-label="Featured work">
      <img src="data:," alt="">
      <button class="lightbox__btn lightbox__close" type="button" aria-label="Close"><?= icon('close') ?></button>
      <button class="lightbox__btn lightbox__prev" type="button" aria-label="Previous project"><?= icon('arrow') ?></button>
      <button class="lightbox__btn lightbox__next" type="button" aria-label="Next project"><?= icon('arrow') ?></button>
      <p class="lightbox__count" aria-live="polite"></p>
    </div>
<?php page_end(); ?>
