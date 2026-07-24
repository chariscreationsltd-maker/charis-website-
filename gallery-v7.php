<?php
/**
 * gallery-v7.php — Oryzo-correct architecture, large stage, landscape-aware
 *
 * Architecture: ONE fixed centre stage. Images slide in/out of it.
 * Side thumbnails flank it. Stage never moves.
 *
 * v7 fixes:
 *   - Stage is larger: fills most of the available screen height
 *   - Landscape images fit fully (object-fit: contain) with a blurred
 *     version of the same image filling the background behind them
 *     so there's no ugly black bars — it looks premium on any orientation
 *
 * Upload to: public_html/gallery-v7.php
 * Visit:     chariscreationsltd.com/gallery-v7.php?run=1
 * Self-deletes on success.
 */

require_once('wp-load.php');

$page_id = 335;
$raw  = get_post_meta($page_id, '_elementor_data', true);
if (!$raw) die('<b style="color:red">❌ Could not load _elementor_data</b>');
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) die('<b style="color:red">❌ JSON decode failed</b>');

$gallery_block = <<<'HTML'

<!-- ═══ CHARIS GALLERY v7 — large stage, blurred backdrop ═══ -->
<div id="charGallery">

  <!-- HEADER -->
  <div class="cg7-head">
    <div class="cg7-left">
      <span class="cg7-brand">CHARIS CREATIONS</span>
      <span class="cg7-dot">·</span>
      <span class="cg7-cat" id="cg7Cat"></span>
    </div>
    <span class="cg7-count" id="cg7Count"></span>
    <button class="cg7-close" id="cg7Close" aria-label="Close">&#x2715;</button>
  </div>

  <!-- CAROUSEL AREA -->
  <div class="cg7-area" id="cg7Area">

    <!-- LEFT SIDE: ambient title text + thumbnails in the same horizontal strip -->
    <div class="cg7-side-l" id="cg7SideL">
      <!-- Ambient text is a flex item inside the left strip, furthest from stage -->
      <div class="cg7-ambient" id="cg7Ambient">
        <span class="cg7-amb-label">CHARIS CREATIONS</span>
        <div class="cg7-amb-title" id="cg7AmbTitle">WEDDING<br>GALLERY</div>
      </div>
    </div>

    <!-- THE FIXED STAGE -->
    <div class="cg7-stage" id="cg7Stage">
      <!--
        .cg7-backdrop  — blurred copy of the current image fills the frame
                         so landscape images have no dark bars
        .cg7-img       — the actual image, object-fit:contain so nothing is cropped
        Both are injected/updated by JS. Backdrops don't animate — only .cg7-img does.
      -->
      <img class="cg7-backdrop" id="cg7Backdrop" src="" alt="">
      <!-- YouTube iframe sits on top of everything inside the stage -->
      <div class="cg7-yt" id="cg7Yt">
        <iframe id="cg7YtF" frameborder="0"
          allow="autoplay;fullscreen;encrypted-media;picture-in-picture"
          allowfullscreen></iframe>
      </div>
    </div>

    <!-- RIGHT THUMBNAILS -->
    <div class="cg7-side-r" id="cg7SideR"></div>

    <!-- SCROLL TO CONTINUE hint -->
    <div class="cg7-hint" id="cg7Hint">
      <div class="cg7-hint-circle">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </div>
      <span class="cg7-hint-lbl">SCROLL TO CONTINUE</span>
    </div>

    <!-- PREV / NEXT arrows -->
    <button class="cg7-arrow cg7-prev" id="cg7Prev">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <button class="cg7-arrow cg7-next" id="cg7Next">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 6 15 12 9 18"/></svg>
    </button>

  </div><!-- /cg7-area -->

  <!-- FOOTER -->
  <div class="cg7-foot">
    <a class="cg7-smug off" id="cg7Smug" target="_blank" rel="noopener">VIEW FULL GALLERY &rarr;</a>
  </div>

</div><!-- /charGallery -->

<style>
/* ── Shell — coffee brown background like Oryzo ── */
#charGallery {
  display: none; position: fixed; inset: 0; z-index: 99999;
  background: #1a0e07; flex-direction: column;
  font-family: 'Montserrat', sans-serif; -webkit-font-smoothing: antialiased;
  overflow: hidden;
}
#charGallery.on { display: flex; }

/* ── Header ── */
.cg7-head {
  flex-shrink: 0; display: flex; align-items: center;
  justify-content: space-between; gap: 12px;
  padding: 14px 24px;
  background: rgba(20,9,2,.96);
  border-bottom: 1px solid rgba(255,255,255,.07); z-index: 20;
}
.cg7-left { display: flex; align-items: center; gap: 10px; overflow: hidden; min-width: 0; }
.cg7-brand {
  font-size: 10px; font-weight: 800; letter-spacing: .30em;
  text-transform: uppercase; color: #f16623; white-space: nowrap; flex-shrink: 0;
}
.cg7-dot  { color: rgba(255,255,255,.15); flex-shrink: 0; }
.cg7-cat  {
  font-size: 10px; font-weight: 600; letter-spacing: .18em;
  text-transform: uppercase; color: rgba(255,255,255,.42);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.cg7-count {
  font-size: 11px; font-weight: 500; letter-spacing: .10em;
  color: rgba(255,255,255,.25); white-space: nowrap; flex-shrink: 0;
}
.cg7-close {
  flex-shrink: 0; width: 34px; height: 34px; border-radius: 50%;
  background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.10);
  color: rgba(255,255,255,.55); cursor: pointer; font-size: 13px;
  display: flex; align-items: center; justify-content: center;
  transition: background .2s, border-color .2s, color .2s; padding: 0; line-height: 1;
}
.cg7-close:hover { background: #f16623; border-color: #f16623; color: #fff; }

/* ── Carousel area — flex row: thumbs | STAGE | thumbs ── */
.cg7-area {
  flex: 1; min-height: 0;
  display: flex; align-items: center; justify-content: center;
  position: relative; overflow: hidden;
  padding: 20px 0 56px;
}

/* ─────────────────────────────────────────────────────────────────
   THE FIXED STAGE — one frame, images enter/exit through it.
   Sized large — fills most of the available vertical space.
───────────────────────────────────────────────────────────────── */
.cg7-stage {
  flex-shrink: 0;
  position: relative;
  /* Wide enough to be dominant, responsive so it doesn't crowd thumbnails */
  width: clamp(280px, 38vw, 480px);
  /* Fill available vertical space generously */
  height: 100%;
  max-height: 640px;
  min-height: 300px;
  border-radius: 8px;
  overflow: hidden;
  background: #130a04;
  box-shadow:
    0 0 0 1px rgba(255,255,255,.08),
    0 40px 110px rgba(0,0,0,.80);
  z-index: 5;
}

/* ── BLURRED BACKDROP
   Fills the entire stage behind the main image.
   This means landscape images have NO dark bars — the blurred version
   of the same photo fills the frame beautifully. ── */
.cg7-backdrop {
  position: absolute;
  inset: -8%;              /* slightly oversized to cover blur edge artifacts */
  width: 116%; height: 116%;
  object-fit: cover;
  object-position: center;
  filter: blur(22px) brightness(0.38) saturate(1.4);
  z-index: 0;
  pointer-events: none;
  user-select: none;
  display: block;
}

/* ── Main images inside the stage
   object-fit: CONTAIN — shows the full image, never crops it
   Landscape photos show fully. Portrait photos fill height.
   The blurred backdrop fills any remaining space. ── */
.cg7-stage .cg7-img {
  position: absolute; inset: 0;
  width: 100%; height: 100%;
  object-fit: contain;
  object-position: center;
  display: block; user-select: none;
  will-change: transform;
  z-index: 2;
}
/* Slide states */
.cg7-stage .cg7-img.entering-right { transform: translateX(100%); }
.cg7-stage .cg7-img.entering-left  { transform: translateX(-100%); }
.cg7-stage .cg7-img.active         { transform: translateX(0); }
.cg7-stage .cg7-img.exiting-left   { transform: translateX(-100%); }
.cg7-stage .cg7-img.exiting-right  { transform: translateX(100%); }
.cg7-stage .cg7-img.sliding {
  transition: transform .55s cubic-bezier(.4,0,.2,1);
}

/* ── YouTube overlay inside stage ── */
.cg7-yt {
  position: absolute; inset: 0; z-index: 10; display: none; background: #000;
}
.cg7-yt.on { display: block; }
.cg7-yt iframe { width: 100%; height: 100%; border: none; display: block; }

/* ─────────────────────────────────────────────────────────────────
   SIDE THUMBNAILS — small squares flanking the stage
───────────────────────────────────────────────────────────────── */
.cg7-side-l, .cg7-side-r {
  flex-shrink: 1;
  display: flex; align-items: center; gap: 12px;
  padding: 0 18px;
  overflow: hidden;
  min-width: 0;
}
/* Left strip: closest thumb is rightmost, nearest the stage */
.cg7-side-l { flex-direction: row-reverse; }

.cg7-thumb {
  flex-shrink: 0;
  width: 130px; height: 130px;
  border-radius: 5px; overflow: hidden;
  cursor: pointer; background: #1e1008;
  transition: opacity .35s ease, transform .28s ease;
}
.cg7-thumb img {
  width: 100%; height: 100%; object-fit: cover;
  object-position: center 20%; display: block; user-select: none; pointer-events: none;
}
/* Opacity: closest thumb most visible, furthest nearly invisible */
.cg7-thumb:nth-child(1) { opacity: .78; }
.cg7-thumb:nth-child(2) { opacity: .48; }
.cg7-thumb:nth-child(3) { opacity: .24; }
.cg7-thumb:hover        { opacity: .96 !important; transform: scale(1.05); }

/* Mask so thumbnails fade out toward screen edges */
.cg7-side-l {
  -webkit-mask-image: linear-gradient(to right, transparent, #000 45%);
  mask-image: linear-gradient(to right, transparent, #000 45%);
}
.cg7-side-r {
  -webkit-mask-image: linear-gradient(to left, transparent, #000 45%);
  mask-image: linear-gradient(to left, transparent, #000 45%);
}

/* ── SCROLL TO CONTINUE hint ── */
.cg7-hint {
  position: absolute; bottom: 18px; left: 50%; transform: translateX(-50%);
  display: flex; flex-direction: column; align-items: center; gap: 6px;
  pointer-events: none; z-index: 20;
  animation: cg7Bob 2s ease-in-out infinite;
  transition: opacity .5s ease;
}
.cg7-hint.hidden { opacity: 0; }
.cg7-hint-circle {
  width: 30px; height: 30px; border-radius: 50%;
  border: 1px solid rgba(255,255,255,.22);
  display: flex; align-items: center; justify-content: center;
  color: rgba(255,255,255,.40);
}
.cg7-hint-lbl {
  font-size: 8px; font-weight: 700; letter-spacing: .30em;
  text-transform: uppercase; color: rgba(255,255,255,.28); white-space: nowrap;
}
@keyframes cg7Bob {
  0%,100% { transform: translateX(-50%) translateY(0); }
  50%      { transform: translateX(-50%) translateY(6px); }
}

/* ── Arrow buttons — tucked close to the stage ── */
.cg7-arrow {
  position: absolute; top: 50%; transform: translateY(-50%);
  background: rgba(0,0,0,.65); border: 1px solid rgba(255,255,255,.12);
  color: rgba(255,255,255,.75);
  width: 42px; height: 42px; border-radius: 50%;
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: background .2s, border-color .2s, transform .2s;
  z-index: 20; padding: 0;
}
.cg7-arrow:hover { background: #f16623; border-color: #f16623; transform: translateY(-50%) scale(1.1); }
/* Position arrows just outside the stage edges */
.cg7-prev { left: calc(50% - clamp(140px, 19vw, 240px) - 52px); }
.cg7-next { right: calc(50% - clamp(140px, 19vw, 240px) - 52px); }

/* ── Ambient title text
   Lives INSIDE .cg7-side-l as a flex item — same horizontal strip as thumbs.
   cg7-side-l uses row-reverse so ambient (last child) appears at the far left.
   Thumbs are prepended in front of it, sitting between text and the stage. ── */
.cg7-ambient {
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-self: center;
  padding-right: 16px;
  pointer-events: none;
  user-select: none;
}
.cg7-amb-label {
  font-size: 8px; font-weight: 800; letter-spacing: .28em;
  text-transform: uppercase;
  color: rgba(255,200,160,.30);
  margin-bottom: 10px;
  display: block; white-space: nowrap;
}
.cg7-amb-title {
  font-size: clamp(20px, 2.8vw, 38px);
  font-weight: 900;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: rgba(255,200,160,.18);
  line-height: 1.05;
}

/* ── Footer ── */
.cg7-foot {
  flex-shrink: 0; display: flex; align-items: center; justify-content: center;
  padding: 13px 24px 16px;
  border-top: 1px solid rgba(255,255,255,.05);
  background: rgba(20,9,2,.96); z-index: 20;
}
.cg7-smug {
  display: inline-block; background: #f16623; color: #fff; text-decoration: none;
  font-family: 'Montserrat', sans-serif; font-weight: 700;
  font-size: 10px; letter-spacing: .22em; text-transform: uppercase;
  padding: 10px 28px; border-radius: 2px;
  transition: opacity .2s, transform .2s;
}
.cg7-smug:hover { opacity: .85; transform: translateY(-1px); color: #fff; }
.cg7-smug.off { display: none; }

/* ── Mobile ── */
@media (max-width: 900px) {
  .cg7-stage { width: clamp(240px, 52vw, 360px); }
  .cg7-thumb { width: 100px; height: 100px; }
  .cg7-side-l, .cg7-side-r { padding: 0 10px; gap: 8px; }
  .cg7-amb-title { font-size: 18px; }
}
@media (max-width: 600px) {
  .cg7-brand, .cg7-dot { display: none; }
  .cg7-stage { width: clamp(220px, 68vw, 300px); }
  .cg7-thumb { width: 80px; height: 80px; }
  .cg7-thumb:nth-child(n+3) { display: none; }
  .cg7-side-l, .cg7-side-r { max-width: 100px; padding: 0 6px; }
  .cg7-prev { left: calc(50% - 34vw - 48px); }
  .cg7-next { right: calc(50% - 34vw - 48px); }
  .cg7-area { padding: 14px 0 52px; }
}
</style>

<script>
(function(){
'use strict';

/* ── YouTube IDs per category — fill in when ready ── */
var YT = {
  wedding:[], introduction:[], documentary:[], kukyala:[],
  podcasts:[], commercial:[], branding:[], corporate:[], clientstories:[]
};
var SMUG = {
  wedding:'https://chariscreations.smugmug.com',
  introduction:'https://chariscreations.smugmug.com',
  documentary:'https://chariscreations.smugmug.com',
  kukyala:'https://chariscreations.smugmug.com',
  podcasts:'',
  commercial:'https://chariscreations.smugmug.com',
  branding:'https://chariscreations.smugmug.com',
  corporate:'https://chariscreations.smugmug.com',
  clientstories:'https://chariscreations.smugmug.com'
};
var NAMES = {
  wedding:'Wedding Films', introduction:'Introduction · Kwanjula',
  documentary:'Documentary', kukyala:'Kukyala',
  podcasts:'Podcast Productions', commercial:'Commercial',
  branding:'Brand & Identity', corporate:'Corporate & Events',
  clientstories:'Client Stories'
};
/* Ambient large text (split on | for line break) */
var AMBIENT = {
  wedding:       'WEDDING\nGALLERY',
  introduction:  'INTRODUCTION\nKWANJULA',
  documentary:   'DOCUMENTARY\nFILMS',
  kukyala:       'KUKYALA\nGALLERY',
  podcasts:      'PODCAST\nPRODUCTIONS',
  commercial:    'COMMERCIAL\nWORK',
  branding:      'BRAND &\nIDENTITY',
  corporate:     'CORPORATE\nEVENTS',
  clientstories: 'CLIENT\nSTORIES'
};

/* ── State ── */
var items = [], idx = 0, busy = false;
var hintGone = false, transitioning = false;

/* ── Update blurred backdrop (no animation — instant swap behind main image) ── */
function updateBackdrop(src){
  var bd = document.getElementById('cg7Backdrop');
  if (bd && src) bd.src = src;
}

/* ── Counter ── */
function updateCounter(){
  var cnt = document.getElementById('cg7Count');
  if (cnt) cnt.textContent =
    String(idx+1).padStart(2,'0') + ' / ' + String(items.length).padStart(2,'0');
}

/* ── Render side thumbnails ── */
function buildThumbs(){
  var sL = document.getElementById('cg7SideL');
  var sR = document.getElementById('cg7SideR');
  if (!sL || !sR || !items.length) return;

  /* Left side: remove old thumbs only (preserve the .cg7-ambient element) */
  sL.querySelectorAll('.cg7-thumb').forEach(function(el){ el.parentNode.removeChild(el); });
  /* Thumbs are prepended so they appear between ambient text and the stage
     (row-reverse means first child = rightmost = closest to stage) */
  for (var li = 3; li >= 1; li--){
    var ti = ((idx - li) % items.length + items.length) % items.length;
    var div = document.createElement('div');
    div.className = 'cg7-thumb';
    var img = document.createElement('img');
    img.src = items[ti].thumb || items[ti].src;
    img.alt = '';
    div.appendChild(img);
    (function(ii){ div.addEventListener('click', function(){ show(ii, ii > idx ? 1 : -1, true); }); })(ti);
    sL.insertBefore(div, sL.firstChild); /* prepend — closest to stage comes first */
  }

  /* Right side: items after current. First child = closest to stage. */
  sR.innerHTML = '';
  for (var ri = 1; ri <= 3; ri++){
    var si = (idx + ri) % items.length;
    var divR = document.createElement('div');
    divR.className = 'cg7-thumb';
    var imgR = document.createElement('img');
    imgR.src = items[si].thumb || items[si].src;
    imgR.alt = '';
    divR.appendChild(imgR);
    (function(ii){ divR.addEventListener('click', function(){ show(ii, 1, true); }); })(si);
    sR.appendChild(divR);
  }
}

/* ─────────────────────────────────────────────────────────────────
   SHOW — slide images through the fixed stage
   dir: +1 = forward (exits left, enters from right)
        -1 = backward (exits right, enters from left)
───────────────────────────────────────────────────────────────── */
function show(i, dir, userAction){
  if (!items.length) return;
  if (i < 0) i = items.length - 1;
  if (i >= items.length) i = 0;
  if (i === idx && items.length > 1) return;
  if (transitioning) return;

  idx = i;
  updateCounter();
  buildThumbs();

  /* Dismiss hint on first user navigation */
  if (userAction && !hintGone){
    hintGone = true;
    var hint = document.getElementById('cg7Hint');
    if (hint) hint.classList.add('hidden');
  }

  var item = items[i];

  /* YouTube video? Show in iframe, skip image transition */
  var yt  = document.getElementById('cg7Yt');
  var ytF = document.getElementById('cg7YtF');
  if (item.type === 'video'){
    if (ytF) ytF.src = 'https://www.youtube.com/embed/' + item.ytId
      + '?autoplay=1&rel=0&modestbranding=1&playsinline=1';
    if (yt) yt.classList.add('on');
    return;
  } else {
    if (ytF) ytF.src = '';
    if (yt)  yt.classList.remove('on');
  }

  /* Update backdrop instantly (no transition — it's behind the sliding image) */
  updateBackdrop(item.thumb || item.src);

  /* Slide transition */
  var stage = document.getElementById('cg7Stage');
  if (!stage) return;

  /* Remove any stuck mid-transition images */
  stage.querySelectorAll('.cg7-img:not(.active)').forEach(function(el){
    if (el.parentNode) el.parentNode.removeChild(el);
  });

  var current = stage.querySelector('.cg7-img.active');

  var newImg = document.createElement('img');
  newImg.className = 'cg7-img ' + (dir >= 0 ? 'entering-right' : 'entering-left');
  newImg.src = item.src;
  newImg.alt = '';
  /* Insert BEHIND current (z-index handles layering, but insert order matters for same z) */
  if (current) stage.insertBefore(newImg, current);
  else         stage.insertBefore(newImg, stage.firstChild);

  transitioning = true;

  requestAnimationFrame(function(){
    requestAnimationFrame(function(){
      newImg.classList.add('sliding');
      newImg.classList.remove('entering-right', 'entering-left');
      newImg.classList.add('active');

      if (current){
        current.classList.add('sliding');
        current.classList.remove('active');
        current.classList.add(dir >= 0 ? 'exiting-left' : 'exiting-right');
        setTimeout(function(){
          if (current.parentNode) current.parentNode.removeChild(current);
          transitioning = false;
        }, 580);
      } else {
        transitioning = false;
      }
    });
  });
}

/* ── Set initial image — no slide animation ── */
function setInitial(){
  var stage = document.getElementById('cg7Stage');
  if (!stage) return;
  stage.querySelectorAll('.cg7-img').forEach(function(el){ el.parentNode.removeChild(el); });
  if (!items.length) return;
  var item = items[0];
  updateBackdrop(item.thumb || item.src);
  var img = document.createElement('img');
  img.className = 'cg7-img active';
  img.src = item.src;
  img.alt = '';
  stage.insertBefore(img, stage.firstChild);
}

/* ── Open gallery ── */
function open(key){
  var ov = document.getElementById('charGallery');
  if (!ov) return;
  ov.classList.add('on');

  hintGone = false; transitioning = false;
  var hint = document.getElementById('cg7Hint');
  if (hint) hint.classList.remove('hidden');

  var cat = document.getElementById('cg7Cat');
  if (cat) cat.textContent = NAMES[key] || key;

  /* Update ambient large title text */
  var ambTitle = document.getElementById('cg7AmbTitle');
  if (ambTitle){
    var lines = (AMBIENT[key] || key.toUpperCase()).split('\n');
    ambTitle.innerHTML = lines.join('<br>');
  }

  var smug = document.getElementById('cg7Smug');
  if (smug){
    var url = SMUG[key] || '';
    if (url){ smug.href = url; smug.classList.remove('off'); }
    else    { smug.classList.add('off'); }
  }

  var newItems = [];
  (YT[key] || []).forEach(function(ytId){
    newItems.push({ type:'video', ytId:ytId,
      src:'https://img.youtube.com/vi/'+ytId+'/maxresdefault.jpg',
      thumb:'https://img.youtube.com/vi/'+ytId+'/mqdefault.jpg' });
  });
  idx = 0; items = newItems;
  setInitial(); updateCounter(); buildThumbs();

  if (busy) return;
  busy = true;
  fetch('/gallery-serve.php?cat=' + encodeURIComponent(key))
    .then(function(r){ return r.json(); })
    .then(function(d){
      busy = false;
      if (!d.images || !d.images.length) return;
      var base = items.filter(function(it){ return it.type==='video'; });
      d.images.forEach(function(u){ base.push({ type:'image', src:u, thumb:u }); });
      items = base; idx = 0;
      setInitial(); updateCounter(); buildThumbs();
    })
    .catch(function(){ busy = false; });
}

/* ── Close ── */
function close(){
  var ov = document.getElementById('charGallery');
  if (ov) ov.classList.remove('on');
  var ytF = document.getElementById('cg7YtF');
  if (ytF) ytF.src = '';
  var yt = document.getElementById('cg7Yt');
  if (yt) yt.classList.remove('on');
  transitioning = false;
}

/* ══ SCROLL NAVIGATION ══════════════════════════════════════════ */
var scrollTick = false, scrollLocked = false, scrollAccum = 0;
function onWheel(e){
  var ov = document.getElementById('charGallery');
  if (!ov || !ov.classList.contains('on')) return;
  e.preventDefault(); e.stopPropagation();
  if (scrollLocked) return;
  scrollAccum += (e.deltaY || e.deltaX || 0);
  if (scrollTick) return;
  scrollTick = true;
  setTimeout(function(){
    var dir = scrollAccum > 0 ? 1 : -1;
    scrollAccum = 0; scrollTick = false;
    scrollLocked = true;
    var next = (idx + dir + items.length) % (items.length || 1);
    show(next, dir, true);
    setTimeout(function(){ scrollLocked = false; }, 580);
  }, 40);
}
document.addEventListener('wheel', onWheel, { passive: false, capture: true });

/* ── Wire controls ── */
function wire(){
  var cl = document.getElementById('cg7Close');
  var pv = document.getElementById('cg7Prev');
  var nx = document.getElementById('cg7Next');
  if (cl) cl.addEventListener('click', close);
  if (pv) pv.addEventListener('click', function(){
    var n = (idx - 1 + items.length) % (items.length || 1);
    show(n, -1, true);
  });
  if (nx) nx.addEventListener('click', function(){
    show((idx + 1) % (items.length || 1), 1, true);
  });

  document.addEventListener('keydown', function(e){
    var ov = document.getElementById('charGallery');
    if (!ov || !ov.classList.contains('on')) return;
    if (e.key==='ArrowLeft'||e.key==='ArrowUp'){
      var n=(idx-1+items.length)%(items.length||1); show(n,-1,true);
    }
    if (e.key==='ArrowRight'||e.key==='ArrowDown'){
      show((idx+1)%(items.length||1),1,true);
    }
    if (e.key==='Escape') close();
  });

  var area = document.getElementById('cg7Area');
  var sx=0, sy=0;
  if (area){
    area.addEventListener('touchstart',function(e){ sx=e.touches[0].clientX; sy=e.touches[0].clientY; },{passive:true});
    area.addEventListener('touchend',function(e){
      var dx=sx-e.changedTouches[0].clientX, dy=sy-e.changedTouches[0].clientY;
      if (Math.abs(dx)>Math.abs(dy)&&Math.abs(dx)>40){
        var d=dx>0?1:-1, n=(idx+d+items.length)%(items.length||1);
        show(n,d,true);
      }
    },{passive:true});
  }
}

/* ── Hijack card onclick after page load ── */
function hijackCards(){
  try {
    Object.defineProperty(window,'openGallery',{
      get:function(){ return function(){}; }, set:function(){}, configurable:false
    });
  } catch(e){ window.openGallery=function(){}; }
  document.querySelectorAll('[onclick*="openGallery"]').forEach(function(card){
    var m = card.getAttribute('onclick').match(/'([^']+)'/);
    if (!m) return;
    var key = m[1];
    card.removeAttribute('onclick');
    card.addEventListener('click', function(e){
      e.preventDefault(); e.stopImmediatePropagation(); open(key);
    }, true);
  });
}

wire();
if (document.readyState==='complete') hijackCards();
else window.addEventListener('load', hijackCards);

})();
</script>
HTML;

/* ═══ PATCH ═══════════════════════════════════════════════════════ */
$patched = 0;

function patch_v7(&$nodes, $block, &$patched){
  foreach ($nodes as &$node){
    if (!empty($node['settings']['html'])){
      $h = $node['settings']['html'];
      if (strpos($h, 'ccOverlay') !== false){
        foreach (['<!-- ═══ CHARIS GALLERY v4','<!-- ═══ CHARIS GALLERY v5',
                  '<!-- ═══ CHARIS GALLERY v6','<!-- ═══ CHARIS GALLERY v7'] as $marker){
          $pos = strpos($h, $marker);
          if ($pos !== false) $h = substr($h, 0, $pos);
        }
        $node['settings']['html'] = rtrim($h) . "\n" . $block;
        $patched++;
      }
    }
    if (!empty($node['elements'])) patch_v7($node['elements'], $block, $patched);
  }
}

patch_v7($data, $gallery_block, $patched);

if ($patched === 0){
  echo '<b style="color:orange">⚠️ Widget with ccOverlay not found.</b>';
  die();
}

$encoded = wp_slash(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
$saved   = update_post_meta($page_id, '_elementor_data', $encoded);
if (class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance->files_manager))
  \Elementor\Plugin::$instance->files_manager->clear_cache();

@unlink(__FILE__);

echo '<div style="font-family:monospace;padding:24px;background:#0d1b2a;color:#e2e8f0;line-height:2">';
echo $saved===false
  ? '<span style="color:#f59e0b">⚠️ DB returned false — may already be current.</span><br>'
  : '<span style="color:#34d399">✅ Gallery v7 deployed.</span><br>';
echo '<b>Widgets patched: '.$patched.'</b><br><br>';
echo '── What v7 does ──────────────────────────────────────────<br>';
echo '• ONE fixed centre stage, large — images slide in and out of it<br>';
echo '• BLURRED BACKDROP: landscape images never have dark bars<br>';
echo '  Each image has a blurred copy behind it filling the frame<br>';
echo '• object-fit:contain — no cropping, every image shows fully<br>';
echo '• Stage: 38vw wide (up to 480px), fills available screen height<br>';
echo '• Side thumbnails fade out toward screen edges<br>';
echo '• SCROLL TO CONTINUE with circle + chevron<br>';
echo '<br>';
echo 'Hard-refresh Projects page (Ctrl+Shift+R) then click any card.<br>';
echo 'This file has self-deleted.<br>';
echo '</div>';
