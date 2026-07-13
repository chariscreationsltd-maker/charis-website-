<?php
/**
 * Plugin Name: CC Projects Page Fix
 * Description: Injects CSS/JS fixes for the Projects, Creatives, and Services pages
 */

// ── TEMP LOGO SETTER (auto-removes after use) ──
add_action('init', function(){
  if(isset($_GET['cc_setlogo']) && is_user_logged_in() && current_user_can('manage_options')){
    set_theme_mod('custom_logo', 323);
    wp_redirect(home_url('/?logo_set=1')); exit;
  }
});
// ── END TEMP ──

// ── AJAX: save / load reel video URLs (Creatives page) ──────────────────
add_action('wp_ajax_cc_get_reel_urls', function(){
  wp_send_json_success(get_option('cc_reel_urls', []));
});
add_action('wp_ajax_cc_save_reel_url', function(){
  if(!check_ajax_referer('cc_reel_nonce','nonce',false)) wp_send_json_error('nonce');
  $i = intval($_POST['reel']);
  if($i < 0 || $i > 9) wp_send_json_error('invalid reel index');
  $url = esc_url_raw(wp_unslash(isset($_POST['url']) ? $_POST['url'] : ''));
  $urls = get_option('cc_reel_urls', []);
  $urls[$i] = $url;
  update_option('cc_reel_urls', $urls);
  wp_send_json_success();
});

// ── PROJECTS PAGE (ID 335) ───────────────────────────────────────────────
add_action('wp_head', function(){
  $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
  if(strpos($uri, '/projects') !== false || get_the_ID() === 335){
    ?>
<!-- CC_PROJ_MU_ACTIVE -->
<style>
#charis-logo-svg{display:none!important;}
#seg-lightbox{display:none!important;}
.glb-films{padding:28px 20px 24px;border-top:1px solid rgba(255,255,255,.07);}
.glb-films-hd{color:#f90;font-size:11px;letter-spacing:.12em;text-transform:uppercase;margin-bottom:18px;font-weight:600;}
.glb-films-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;}
.glb-film-slot{position:relative;aspect-ratio:16/9;background:#111;border-radius:6px;overflow:hidden;}
.glb-film-lbl{position:absolute;top:8px;left:10px;font-size:10px;color:rgba(255,255,255,.35);text-transform:uppercase;z-index:2;pointer-events:none;}
.glb-film-iframe{width:100%;height:100%;border:0;display:block;}
</style>
<script>
(function(){
  function injectFilms(gb){
    if(document.getElementById('glbFilms')) return;
    var slots=[1,2,3,4,5,6].map(function(i){
      return '<div class="glb-film-slot"><div class="glb-film-lbl">Film '+i+'</div>'+
             '<iframe class="glb-film-iframe" src="" data-film="'+i+'" allowfullscreen allow="autoplay;encrypted-media" frameborder="0"></iframe></div>';
    }).join('');
    var d=document.createElement('div');
    d.id='glbFilms';d.className='glb-films';
    d.innerHTML='<div class="glb-films-hd">Films</div><div class="glb-films-grid">'+slots+'</div>';
    gb.appendChild(d);
  }
  var obs=new MutationObserver(function(muts){
    muts.forEach(function(m){
      m.addedNodes.forEach(function(n){
        if(n.nodeType===1){
          if(n.id==='glb'){var gb=n.querySelector('.glb-body');if(gb)injectFilms(gb);}
          var found=n.querySelector&&n.querySelector('#glb .glb-body');
          if(found)injectFilms(found);
        }
      });
    });
  });
  obs.observe(document.body||document.documentElement,{childList:true,subtree:true});
})();
</script>
    <?php
  }
},10);

// ── CREATIVES PAGE (ID 250) ───────────────────────────────────────────────
add_action('wp_head', function(){
  $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
  if(strpos($uri, '/creatives') !== false || get_the_ID() === 250){
    $reel_urls = get_option('cc_reel_urls', []);
    $nonce     = wp_create_nonce('cc_reel_nonce');
    ?>
<style>
/* ── Footer full-width breakout ── */
.page-id-250 footer {
  position: relative;
  left: 50%;
  transform: translateX(-50%);
  width: 100vw;
}
/* ── Reel video layer ── */
.reel-item { position: relative; overflow: hidden; }
.reel-vid  { width:100%; height:100%; object-fit:cover; position:absolute; top:0; left:0; z-index:1; }
/* ── Flip card fix: backface-visibility broken by Elementor overflow:hidden ancestor.
   Use opacity toggle instead — back stays invisible until card is flipped. ── */
.flip-front-icon,
.flip-cta-hint { opacity: 1 !important; }
.flip-front {
  transition: opacity 0.25s;
}
.flip-wrap.flipped .flip-front {
  opacity: 0;
  pointer-events: none;
}
.flip-back {
  opacity: 0;
  transition: opacity 0.3s 0.3s;
}
.flip-wrap.flipped .flip-back {
  opacity: 1;
  transition: opacity 0.4s 0.25s;
}
/* ── Team section: hide all until a card is chosen ── */
#charis-section,
#creative-section { display: none !important; }
#charis-section.active,
#creative-section.active { display: block !important; }
/* ── Admin URL paste fields ── */
.reel-url-wrap {
  position: absolute; bottom: 0; left: 0; right: 0;
  background: rgba(0,0,0,.75); padding: 7px 8px;
  z-index: 20; display: flex; align-items: center; gap: 6px;
  border-top: 1px solid rgba(255,140,0,.4);
}
.reel-url-inp {
  flex: 1; background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,140,0,.45); color: #fff;
  padding: 5px 8px; font-size: 11px; border-radius: 4px; outline: none;
}
.reel-url-inp:focus { border-color: #f90; background: rgba(255,140,0,.1); }
.reel-url-inp::placeholder { color: rgba(255,255,255,.35); }
.reel-url-lbl {
  color: rgba(255,140,0,.8); font-size: 10px; white-space: nowrap;
  text-transform: uppercase; letter-spacing: .08em; font-weight: 600;
}
</style>
<script>
	(function(){
  var reelUrls = <?php echo json_encode((object)$reel_urls); ?>;
  var nonce    = '<?php echo esc_js($nonce); ?>';
  /* isAdmin checked at runtime inside initReels */

  function setReelVideo(i, url){
    var reel = document.getElementById('reel-'+i);
    if(!reel) return;
    var bg = reel.querySelector('.reel-item-bg');
    if(!bg) return;
    var old = bg.querySelector('video.reel-vid');
    if(old) old.parentNode.removeChild(old);
    if(!url) return;
    var v = document.createElement('video');
    v.className = 'reel-vid';
    v.src = url; v.autoplay = true; v.muted = true;
    v.loop = true; v.playsInline = true;
    v.setAttribute('playsinline','');
    bg.insertBefore(v, bg.firstChild);
    v.play().catch(function(){});
  }

  function initReels(){
    var row = document.getElementById('reels-row');
    if(!row){ setTimeout(initReels, 400); return; }

    /* Load saved videos for ALL visitors */
    for(var k in reelUrls){
      if(reelUrls[k]) setReelVideo(parseInt(k), reelUrls[k]);
    }

    /* Admin-only paste fields (check at runtime after DOM ready) */
    var isAdmin = !!document.getElementById('wpadminbar');
    if(!isAdmin) return;
    var reels = document.querySelectorAll('.reel-item');
    reels.forEach(function(reel, i){
      var wrap = document.createElement('div');
      wrap.className = 'reel-url-wrap';
      wrap.innerHTML = '<span class="reel-url-lbl">MP4</span>'+
        '<input class="reel-url-inp" type="url" placeholder="Paste MP4 URL and press Enter..." '+
        'data-reel="'+i+'" value="'+(reelUrls[i]||'')+'">';
      reel.appendChild(wrap);
    });
  }

  function saveReelUrl(i, url){
    var fd = new FormData();
    fd.append('action','cc_save_reel_url');
    fd.append('reel', i); fd.append('url', url); fd.append('nonce', nonce);
    fetch('/wp-admin/admin-ajax.php',{method:'POST',credentials:'include',body:fd})
      .then(function(r){ return r.json(); })
      .then(function(d){ if(d.success) reelUrls[i]=url; });
  }

  document.addEventListener('change', function(e){
    if(!e.target.classList.contains('reel-url-inp')) return;
    var i = parseInt(e.target.getAttribute('data-reel'));
    var url = e.target.value.trim();
    setReelVideo(i, url); saveReelUrl(i, url);
  });
  document.addEventListener('keydown', function(e){
    if(e.key !== 'Enter' || !e.target.classList.contains('reel-url-inp')) return;
    e.target.blur();
  });

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', initReels);
  } else {
    initReels();
  }
})();
</script>
    <?php
  }
},10);

// ── SERVICES PAGE (ID 466) ────────────────────────────────────────────────
add_action('wp_head', function(){
  $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
  if(strpos($uri, '/services') !== false || get_the_ID() === 466){
    ?>
<script>
(function(){
  var WA = 'https://wa.me/256706028899';

  function q(msg){ return encodeURIComponent(msg); }

  /* ── Section HTML builders ───────────────────────────────────── */
  function card(cls, badge, name, tagline, items, waMsg){
    var itemsHtml = items.map(function(x){
      return x[0]==='@'
        ? '<div class="pkg-cat-label">'+x.slice(1)+'</div>'
        : '<div class="pkg-item">'+x+'</div>';
    }).join('');
    return '<div class="pkg-card '+cls+'">'
      +'<div class="pkg-badge">'+badge+'</div>'
      +'<div class="pkg-name">'+name+'</div>'
      +'<div class="pkg-tagline">'+tagline+'</div>'
      +'<div class="pkg-divider"></div>'
      +'<div class="pkg-items">'+itemsHtml+'</div>'
      +'<a class="pkg-book-btn" href="'+WA+'?text='+q(waMsg)+'" target="_blank">ENQUIRE NOW</a>'
      +'</div>';
  }

  function intro(rev, eyebrow, h2, desc, waMsg, waLabel){
    var cls = rev ? 'cat-intro cat-intro rev rv' : 'cat-intro rv';
    return '<div class="'+cls+'">'
      +'<div class="cat-visual rv">'
        +'<div class="cat-visual-badge">'+eyebrow+'</div>'
      +'</div>'
      +'<div class="cat-text rv d2">'
        +'<div class="sec-ey">'+eyebrow+'</div>'
        +'<h2 class="cat-h">'+h2+'</h2>'
        +'<p class="cat-desc">'+desc+'</p>'
        +'<div class="cat-cta-row">'
          +'<a href="'+WA+'?text='+q(waMsg)+'" target="_blank" class="cta-primary">'+waLabel+'</a>'
          +'<a href="/projects-2/" class="cta-ghost">See Our Work</a>'
        +'</div>'
      +'</div>'
    +'</div>';
  }

  /* ── Section data ────────────────────────────────────────────── */
  var SECS = [
    {
      id: 'livestreaming',
      rev: false,
      eyebrow: 'LIVE STREAMING',
      h2: 'YOUR EVENT,<br>SEEN BY THE WORLD<br>IN REAL TIME.',
      desc: 'Churches, conferences, product launches, and special events — we broadcast your moment live with multi-camera professionalism and crystal-clear quality. Stream to YouTube, Facebook, and beyond.',
      ctaMsg: 'Hi Charis, I need a Live Streaming package',
      ctaLabel: 'Book a Stream \u2192',
      cards: [
        {cls:'rv d1', badge:'Church & Small Event', name:'The Essential<br>Stream',
         tagline:'Clean, reliable live streaming for churches and small gatherings — get online fast.',
         items:['@Setup & Equipment','1-camera live stream setup','Professional audio feed',
                'Stream to 1 platform (YouTube or Facebook)','Up to 3 hours of streaming',
                '@Deliverables','Recorded stream file delivered','Basic graphic overlay / title card'],
         msg:'Hi Charis, I\'m interested in the Essential Stream package'},
        {cls:'featured rv d2', badge:'Conference & Service', name:'The Event Pro<br>Broadcast',
         tagline:'Multi-camera professional broadcast — the standard for conferences, church services, and product launches.',
         items:['@Setup & Equipment','2–3 camera broadcast setup','Professional audio mixing',
                'Stream to 2 platforms simultaneously','Live graphics & lower thirds',
                'Up to 6 hours of streaming',
                '@Deliverables','Full HD recorded broadcast','Edited highlights reel (48hrs)'],
         msg:'Hi Charis, I\'m interested in the Event Pro Broadcast package'},
        {cls:'rv d3', badge:'Large-Scale Events', name:'The Premier<br>Broadcast',
         tagline:'Full production-level live event coverage with a dedicated crew and director.',
         items:['@Setup & Equipment','4+ camera broadcast setup','Dedicated live director & operator',
                'Multi-platform simultaneous streaming','Branded graphics package',
                'Up to 10 hours of streaming',
                '@Deliverables','Full broadcast archive delivered','Social media cut-downs (3–5 clips)'],
         msg:'Hi Charis, I\'m interested in the Premier Broadcast package'}
      ]
    },
    {
      id: 'corporate',
      rev: true,
      eyebrow: 'CORPORATE & BRAND',
      h2: 'STORIES THAT SELL.<br>FILMS THAT<br>BUILD TRUST.',
      desc: 'From brand identity films to corporate documentation — we help organizations communicate with credibility and visual impact. Professional, polished, and built to represent you at your best.',
      ctaMsg: 'Hi Charis, I need Corporate/Brand media production',
      ctaLabel: 'Book a Consult \u2192',
      cards: [
        {cls:'rv d1', badge:'Brand Intro', name:'The Brand<br>Starter',
         tagline:'Everything you need to introduce your brand professionally — clean, credible, and compelling.',
         items:['@Video','2–3 minute brand identity film','Script & creative direction included',
                '1 day of filming',
                '@Photography','Half-day corporate photo session','30 edited corporate photos',
                'Team & facility coverage'],
         msg:'Hi Charis, I\'m interested in the Brand Starter package'},
        {cls:'featured rv d2', badge:'Corporate Suite', name:'The Brand Story<br>Suite',
         tagline:'A comprehensive brand storytelling package — film, photography, and content that powers your marketing for months.',
         items:['@Video Production','4–6 minute signature brand film',
                '2–3 short social media cuts (60–90 secs)','2 days of filming',
                'Professional voiceover (if needed)',
                '@Photography','Full-day corporate photo session','80+ edited photos',
                'Headshots, facility, and event coverage'],
         msg:'Hi Charis, I\'m interested in the Brand Story Suite package'},
        {cls:'rv d3', badge:'Premium Campaign', name:'The Premium<br>Campaign',
         tagline:'Full-scale multi-platform media campaign — built for brands ready to lead their category.',
         items:['@Production','Full brand campaign film (6–10 min)','Complete social media content set',
                '3+ days of production','Creative strategy & storyboarding',
                '@Additional Assets','150+ edited corporate photos',
                'Event documentation coverage','Motion graphics & title sequences'],
         msg:'Hi Charis, I\'m interested in the Premium Campaign package'}
      ]
    },
    {
      id: 'partnerships',
      rev: false,
      eyebrow: 'PARTNERSHIPS',
      h2: 'BUILD YOUR MEDIA<br>CAPACITY WITH A<br>TEAM THAT GETS IT.',
      desc: 'For organizations, NGOs, schools, and brands that need consistent, strategic media presence — not just a one-time shoot. We become your embedded media partner: producing, strategizing, and growing your story month by month.',
      ctaMsg: 'Hi Charis, I\'m interested in a Media Partnership',
      ctaLabel: 'Start a Partnership \u2192',
      cards: [
        {cls:'rv d1', badge:'Media Strategy', name:'The Media<br>Advisor',
         tagline:'Monthly strategy and direction — expert media guidance without a full production retainer.',
         items:['@Monthly Inclusions','2 strategy sessions per month','Content calendar planning',
                'Media review & feedback','Brand voice & messaging guidance',
                '@Support','WhatsApp & email support','Quarterly communications audit'],
         msg:'Hi Charis, I\'m interested in the Media Advisor partnership'},
        {cls:'featured rv d2', badge:'Strategic Partner', name:'The Strategic<br>Media Partner',
         tagline:'Monthly production + strategy — a dedicated media partner that keeps your story moving forward.',
         items:['@Monthly Production','2 production days per month','4–6 edited video assets',
                'Photo session (50+ edited images)','Social media content creation',
                '@Strategy & Support','Monthly strategy sessions',
                'Priority scheduling & turnaround','Communications consulting'],
         msg:'Hi Charis, I\'m interested in the Strategic Media Partner retainer'},
        {cls:'rv d3', badge:'Full Media Team', name:'The Full Media<br>Partnership',
         tagline:'Charis becomes your in-house media department — full production, strategy, and communications support.',
         items:['@Monthly Production','Unlimited production days (agreed scope)',
                'All video, photo & content needs covered','Campaign production & direction',
                '@Strategy & Leadership','Dedicated Account Director',
                'Weekly check-ins & planning','Full communications strategy support'],
         msg:'Hi Charis, I\'m interested in the Full Media Partnership'}
      ]
    }
  ];

  /* ── Build & inject ──────────────────────────────────────────── */
  function buildSection(sec){
    var el = document.createElement('section');
    el.className = 'cat-section';
    el.id = sec.id;
    var cardsHtml = sec.cards.map(function(c){
      return card(c.cls, c.badge, c.name, c.tagline, c.items, c.msg);
    }).join('');
    el.innerHTML = intro(sec.rev, sec.eyebrow, sec.h2, sec.desc, sec.ctaMsg, sec.ctaLabel)
      + '<div class="pkg-grid pkg-grid-3">'+cardsHtml+'</div>';
    return el;
  }

  function initServices(){
    var ticker = document.querySelector('.ticker-section');
    if(!ticker){ setTimeout(initServices, 300); return; }
    var parent = ticker.parentElement;

    SECS.forEach(function(sec){
      var existing = document.getElementById(sec.id);
      if(existing && document.body.contains(existing)) return; // already injected
      parent.insertBefore(buildSection(sec), ticker);
    });

    /* Fix tab button data-sec + onclick for buttons 4, 5, 6 */
    var btnMap = ['livestreaming','corporate','partnerships'];
    var btnLabels = ['LIVESTREAMING','CORPORATE &amp; BRAND','PARTNERSHIPS'];
    document.querySelectorAll('.cat-btn').forEach(function(btn, i){
      if(i >= 4 && i <= 6){
        var secId = btnMap[i-4];
        btn.setAttribute('data-sec', secId);
        btn.setAttribute('onclick', 'catNav(this,"'+secId+'")');
        if(i === 6) btn.textContent = 'PARTNERSHIPS';
      }
    });

    /* Fix lazy images inside .elementor-466 custom scroll container.
       Browser IntersectionObserver tracks main viewport — images never enter it
       since body/html have overflow:hidden. Remove lazy attr and force-reload. */
    document.querySelectorAll('.elementor-466 img[loading="lazy"]').forEach(function(img) {
      img.removeAttribute('loading');
      if (!img.complete || img.naturalWidth === 0) {
        var s = img.src; img.src = ''; img.src = s;
      }
    });

    /* Override scrollToSec — body/html have overflow:hidden so window.scrollTo is a no-op.
       The actual scroll container is .elementor-466. Look up scroller at call-time so
       timing of initServices() doesn't matter. Use getBoundingClientRect for accurate
       position and 'instant' behavior (smooth gets cancelled by Elementor's scroll handler). */
    window.scrollToSec = function(secId) {
      var sec = document.getElementById(secId);
      if(!sec) return;
      var sc = document.querySelector('.elementor-466');
      if(!sc) return;
      var target = sc.scrollTop + (sec.getBoundingClientRect().top - sc.getBoundingClientRect().top) - 120;
      sc.scrollTo({ top: Math.max(0, target), behavior: 'instant' });
    };
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', initServices);
  } else {
    initServices();
  }
})();

  // ── Mobile scroll fix: after choosing a team, scroll to the team content ──
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.flip-wrap').forEach(function(wrap) {
      wrap.addEventListener('click', function() {
        setTimeout(function() {
          var active = document.querySelector('.team-section.active');
          if (active) {
            active.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        }, 450);
      });
    });
  });
</script>
    <?php
  }
},10);
