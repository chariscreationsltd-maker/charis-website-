/* Charis Creations: shared behaviour. No libraries; everything works without it too. */
(function () {
  "use strict";
  var root = document.documentElement;
  root.classList.add("js");
  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ---- Mobile menu ---- */
  var header = document.querySelector(".site-header");
  var menuBtn = document.querySelector(".menu-toggle");
  function setMenu(open) {
    if (!header || !menuBtn) return;
    header.setAttribute("data-menu", open ? "open" : "closed");
    menuBtn.setAttribute("aria-expanded", String(open));
    menuBtn.querySelector(".sr-only").textContent = open ? "Close menu" : "Open menu";
    document.body.style.overflow = open ? "hidden" : "";
  }
  if (menuBtn) {
    menuBtn.addEventListener("click", function () { setMenu(header.getAttribute("data-menu") !== "open"); });
    header.querySelectorAll(".nav__list a").forEach(function (a) { a.addEventListener("click", function () { setMenu(false); }); });
    window.matchMedia("(min-width: 961px)").addEventListener("change", function (m) { if (m.matches) setMenu(false); });
  }

  /* ---- BOOK NOW menu ---- */
  document.querySelectorAll(".book").forEach(function (book) {
    var btn = book.querySelector(".book__toggle");
    var items = book.querySelectorAll(".book__menu a");
    function set(open, focusFirst) {
      book.setAttribute("data-open", String(open));
      btn.setAttribute("aria-expanded", String(open));
      if (open && focusFirst && items[0]) items[0].focus();
    }
    btn.addEventListener("click", function (e) { e.stopPropagation(); set(book.getAttribute("data-open") !== "true"); });
    btn.addEventListener("keydown", function (e) { if (e.key === "ArrowDown") { e.preventDefault(); set(true, true); } });
    document.addEventListener("click", function (e) { if (!book.contains(e.target)) set(false); });
    book.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && book.getAttribute("data-open") === "true") { set(false); btn.focus(); }
    });
    book.addEventListener("focusout", function (e) {
      if (window.innerWidth > 960 && !book.contains(e.relatedTarget)) set(false);
    });
  });
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && header && header.getAttribute("data-menu") === "open") { setMenu(false); menuBtn.focus(); }
  });

  /* ---- FAQ: one answer open at a time ---- */
  document.querySelectorAll(".faq").forEach(function (faq) {
    var items = faq.querySelectorAll("details");
    items.forEach(function (d) {
      d.addEventListener("toggle", function () { if (d.open) items.forEach(function (o) { if (o !== d) o.open = false; }); });
    });
  });

  /* ---- Reveal and develop on scroll ---- */
  var watch = document.querySelectorAll(".reveal, .develop");
  if ("IntersectionObserver" in window && !reduceMotion) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add("is-in"); io.unobserve(en.target); } });
    }, { rootMargin: "0px 0px -12% 0px", threshold: 0.12 });
    watch.forEach(function (el) { io.observe(el); });
  } else {
    watch.forEach(function (el) { el.classList.add("is-in"); });
  }

  /* ---- CharisOS embeds: hide or fall back if they do not load ---- */
  document.querySelectorAll("iframe[data-embed]").forEach(function (frame) {
    var loaded = false;
    frame.addEventListener("load", function () { loaded = true; });
    var wait = frame.getAttribute("loading") === "lazy" ? 20000 : 12000;
    function check() {
      if (loaded) return;
      var mode = frame.getAttribute("data-embed");
      if (mode === "hide-section") {
        var section = frame.closest("section"); if (section) section.hidden = true;
      } else if (mode === "fallback") {
        var fb = document.getElementById(frame.getAttribute("data-fallback"));
        if (fb) { fb.hidden = false; frame.hidden = true; }
      }
    }
    // Start the timer only once the frame is near the screen, so lazy frames are not judged early.
    if ("IntersectionObserver" in window) {
      var fio = new IntersectionObserver(function (en) {
        if (en[0].isIntersecting) { fio.disconnect(); setTimeout(check, wait); }
      }, { rootMargin: "400px" });
      fio.observe(frame);
    } else { setTimeout(check, wait); }
  });

  /* ---- Services sub-navigation: mark the section in view ---- */
  var subLinks = document.querySelectorAll(".subnav a");
  if (subLinks.length && "IntersectionObserver" in window) {
    var map = {};
    subLinks.forEach(function (a) { map[a.getAttribute("href").slice(1)] = a; });
    var sio = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        subLinks.forEach(function (a) { a.removeAttribute("aria-current"); });
        var a = map[en.target.id];
        if (a) {
          a.setAttribute("aria-current", "true");
          // Scroll only the tab strip sideways; scrollIntoView would also move the page.
          var strip = a.closest("ul"), li = a.parentNode;
          if (strip && strip.scrollWidth > strip.clientWidth) {
            var left = li.offsetLeft - strip.offsetLeft, right = left + li.offsetWidth;
            if (left < strip.scrollLeft) strip.scrollLeft = left - 16;
            else if (right > strip.scrollLeft + strip.clientWidth) strip.scrollLeft = right - strip.clientWidth + 16;
          }
        }
      });
    }, { rootMargin: "-40% 0px -55% 0px" });
    Object.keys(map).forEach(function (id) { var s = document.getElementById(id); if (s) sio.observe(s); });
  }

  /* ---- Lightbox for photo stories ---- */
  var box = document.getElementById("lightbox");
  if (box) {
    var img = box.querySelector("img"), count = box.querySelector(".lightbox__count");
    var list = [], idx = 0, opener = null;
    function show(i) {
      idx = (i + list.length) % list.length;
      img.src = list[idx].src; img.alt = list[idx].alt;
      count.textContent = list[idx].caption || ((idx + 1) + " / " + list.length);
    }
    function open(group, i, from) {
      list = Array.prototype.map.call(document.querySelectorAll('[data-lightbox="' + group + '"]'), function (a) {
        return { src: a.getAttribute("href"), alt: a.getAttribute("data-alt") || "", caption: a.getAttribute("data-caption") || "" };
      });
      if (!list.length) return;
      opener = from; box.hidden = false; document.body.style.overflow = "hidden"; show(i);
      box.querySelector(".lightbox__close").focus();
    }
    function close() { box.hidden = true; document.body.style.overflow = ""; if (opener) opener.focus(); }
    document.addEventListener("click", function (e) {
      var a = e.target.closest("[data-lightbox], [data-lightbox-open]");
      if (!a) return;
      e.preventDefault();
      var group = a.getAttribute("data-lightbox") || a.getAttribute("data-lightbox-open");
      var all = document.querySelectorAll('[data-lightbox="' + group + '"]');
      open(group, Math.max(0, Array.prototype.indexOf.call(all, a)), a);
    });
    box.querySelector(".lightbox__close").addEventListener("click", close);
    box.querySelector(".lightbox__prev").addEventListener("click", function () { show(idx - 1); });
    box.querySelector(".lightbox__next").addEventListener("click", function () { show(idx + 1); });
    box.addEventListener("click", function (e) { if (e.target === box) close(); });
    document.addEventListener("keydown", function (e) {
      if (box.hidden) return;
      if (e.key === "Escape") close();
      if (e.key === "ArrowRight") show(idx + 1);
      if (e.key === "ArrowLeft") show(idx - 1);
    });
  }

  /* ---- Analytics events (Handover 08): WhatsApp, date checks, studio bookings, builder, enquiries ---- */
  /* Analytics: the Google tag ID comes from site.json (analyticsId). Keep Hudson's existing ID so history continues. */
  var ga = document.documentElement.getAttribute("data-ga");
  if (ga) {
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { window.dataLayer.push(arguments); };
    window.gtag("js", new Date());
    window.gtag("config", ga);
  }

  document.addEventListener("click", function (e) {
    var a = e.target.closest("[data-track]");
    if (!a) return;
    var name = a.getAttribute("data-track");
    if (typeof window.gtag === "function") window.gtag("event", name, { link_url: a.href, page_path: location.pathname });
    else (window.dataLayer = window.dataLayer || []).push({ event: name, link_url: a.href });
  });

  // Client voices: a deck that deals itself. Every few seconds the front card
  // lifts and tilts back off the top while the one behind rises and grows into
  // focus; the lifted card then rejoins the back. Hovering or focusing the deck
  // pauses it so a review can be read; it also rests while off screen.
  var deck = document.querySelector("[data-stack]");
  if (deck && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    var cards = Array.prototype.slice.call(deck.querySelectorAll(".voice"));
    var n = cards.length, now = document.querySelector("[data-stack-now]");
    var HOLD = 3200, MOVE = 1000, STEP = HOLD + MOVE;
    var clock = 0, last = 0, paused = false, visible = false, raf = 0;
    var lerp = function (a, b, t) { return a + (b - a) * t; };
    var ease = function (t) { return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2; };
    var render = function () {
      var step = Math.floor(clock / STEP), within = clock - step * STEP;
      var t = n > 1 && within > HOLD ? ease((within - HOLD) / MOVE) : 0;
      var front = step % n;
      cards.forEach(function (card, i) {
        var k = (i - front + n) % n, y, rx = 0, sc, o = 1, z;
        if (k === 0 && t > 0) { y = lerp(-50, -200, t); rx = lerp(0, 35, t); sc = 1; o = t > 0.7 ? 1 - (t - 0.7) / 0.3 : 1; z = n + 1; }
        else {
          var d = k === 0 ? 0 : k - t; // waiting cards rise one place as the front lifts
          y = -50 + d * 5; sc = 1 - d * 0.075; z = n - k;
          if (d > 2.6) o = Math.max(0, 3.6 - d);
          if (k === n - 1 && n > 2) o = Math.min(o, t); // the card rejoining the back fades in
        }
        card.style.transform = "translate(-50%," + y.toFixed(2) + "%) rotateX(" + rx.toFixed(2) + "deg) scale(" + sc.toFixed(3) + ")";
        card.style.opacity = o.toFixed(3);
        card.style.zIndex = z;
        // only the front card (and the one rising to replace it) shows its content
        card.style.setProperty("--c", k === 0 ? 1 : k === 1 ? t.toFixed(3) : 0);
        card.style.pointerEvents = k === 0 ? "auto" : "none";
      });
      var shown = (front + (t > 0.5 ? 1 : 0)) % n;
      if (now) now.textContent = (shown < 9 ? "0" : "") + (shown + 1);
    };
    var tick = function (ts) {
      if (!last) last = ts;
      if (!paused) clock += Math.min(ts - last, 100);
      last = ts;
      render();
      raf = visible ? requestAnimationFrame(tick) : 0;
    };
    var start = function () { if (!raf && visible) { last = 0; raf = requestAnimationFrame(tick); } };
    // Pause only while the pointer is on the front card being read
    deck.addEventListener("mouseover", function (e) { var c = e.target.closest(".voice"); paused = !!c && c.style.pointerEvents !== "none"; });
    deck.addEventListener("mouseleave", function () { paused = false; });
    deck.addEventListener("focusin", function () { paused = true; });
    deck.addEventListener("focusout", function () { paused = false; });
    // Touch: tap the deck to hold a card, tap again to let it play on
    deck.addEventListener("touchstart", function () { paused = !paused; }, { passive: true });
    if ("IntersectionObserver" in window) {
      new IntersectionObserver(function (es) { visible = es[0].isIntersecting; start(); }, { threshold: 0.2 }).observe(deck);
    } else { visible = true; start(); }
    render();
  }

  // Trailers: muted previews play only while on screen; a click opens the full film.
  var trailers = document.querySelectorAll("[data-trailer]");
  if (trailers.length) {
    var still = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if ("IntersectionObserver" in window) {
      var tio = new IntersectionObserver(function (es) {
        es.forEach(function (en) {
          var v = en.target.querySelector("video");
          if (!v) return;
          if (en.isIntersecting) { v.preload = "metadata"; if (!still) { var pr = v.play(); if (pr && pr.catch) pr.catch(function () {}); } }
          else { v.pause(); }
        });
      }, { threshold: 0.35 });
      trailers.forEach(function (t) { tio.observe(t); });
    }
    var modal = document.getElementById("trailer-modal");
    var mv = modal && modal.querySelector("video"), opener = null;
    var box = modal && modal.querySelector(".trailer-modal__box");
    var shut = function () {
      if (!mv) return; mv.pause(); mv.removeAttribute("src"); mv.load();
      var fr = box.querySelector("iframe"); if (fr) fr.remove();
      box.classList.remove("is-vertical"); mv.hidden = false;
      modal.hidden = true; document.body.style.overflow = ""; if (opener) opener.focus();
    };
    trailers.forEach(function (t) {
      t.addEventListener("click", function () {
        if (!modal) return;
        opener = t;
        var yt = t.getAttribute("data-youtube");
        if (yt) {
          var fr = document.createElement("iframe");
          fr.src = "https://www.youtube-nocookie.com/embed/" + encodeURIComponent(yt) + "?autoplay=1&rel=0&playsinline=1";
          fr.title = t.getAttribute("aria-label").replace(/^Play /, "");
          fr.allow = "autoplay; encrypted-media; picture-in-picture; fullscreen";
          fr.allowFullscreen = true;
          mv.hidden = true; box.classList.add("is-vertical"); box.insertBefore(fr, box.firstChild);
        } else {
          mv.src = t.getAttribute("data-trailer");
          if (t.classList.contains("trailer--reel")) box.classList.remove("is-vertical");
        }
        modal.hidden = false; document.body.style.overflow = "hidden";
        modal.querySelector(".trailer-modal__close").focus();
        if (!yt) { var pr = mv.play(); if (pr && pr.catch) pr.catch(function () {}); }
      });
    });
    if (modal) {
      modal.querySelector(".trailer-modal__close").addEventListener("click", shut);
      modal.addEventListener("click", function (e) { if (e.target === modal) shut(); });
      document.addEventListener("keydown", function (e) { if (e.key === "Escape" && !modal.hidden) shut(); });
    }
  }

  // Projects hero: gallery cards turning along one shared circle. Every card's
  // position comes from the same centre and radius (sine/cosine at 10.5 degree
  // steps); a short presentation loop runs until the visitor takes over with
  // the mouse wheel over the deck, a drag (vertical with a mouse, sideways on
  // touch so the page still scrolls), or a click. Clicking a centred card opens
  // its gallery; clicking any other card turns it to the centre first.
  var arc = document.querySelector("[data-arc]");
  if (arc) {
    var stage = arc.querySelector("[data-arc-stage]");
    var tpl = arc.querySelector("[data-arc-cards]");
    var data = Array.prototype.slice.call(tpl.content.children);
    var N = data.length, LOGICAL = 19, still2 = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var cl = function (v, a, b) { return v < a ? a : v > b ? b : v; };
    var ease = function (t) { return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2; };
    var nodes = [];
    for (var L = 0; L < LOGICAL; L++) {
      var logical = L - 7, di = ((logical % N) + N) % N;
      var el = data[di].cloneNode(true);
      el.setAttribute("data-logical", logical);
      el.setAttribute("draggable", "false");
      stage.appendChild(el);
      nodes.push({ el: el, logical: logical, motion: el.querySelector(".arc-card__motion") });
    }
    var W = stage.clientWidth || 300;
    if ("ResizeObserver" in window) new ResizeObserver(function () { W = stage.clientWidth || 300; }).observe(stage);
    var value = -1, target = -1, vel = 0, vm = 0, lastV = -1, manual = still2, dragging = false, dragged = false, sx = 0, sy = 0, sv = 0, axis = "y", lastWheel = 0, hovered = null, t0 = performance.now(), live = true;
    if (still2) { value = target = 0; }
    var place = function () {
      var r = W * 1.868, cx = -W * 1.322, cy = W * 0.812, travel = cl(vm, -1, 1);
      nodes.forEach(function (n) {
        var slot = n.logical + value;
        var th = (-20 + slot * 10.5) * Math.PI / 180;
        var x = cx + r * Math.cos(th), y = cy + r * Math.sin(th);
        var edge = Math.max(0, Math.abs(slot - 2) - 3.15), vis = slot > -2.2 && slot < 6.2;
        var side = cl(Math.abs(slot - 2) / 2.6, 0, 1), str = Math.abs(travel) * (0.34 + side * 0.66);
        n.el.style.transform = "translate3d(" + x.toFixed(1) + "px," + y.toFixed(1) + "px,0) translate(-50%,-50%) rotate(" + th.toFixed(4) + "rad)";
        n.el.style.opacity = vis ? cl(1 - edge * 0.52, 0, 1).toFixed(3) : "0";
        n.el.style.filter = edge > 0.01 ? "blur(" + (edge * (2.5 + Math.abs(travel) * 1.4)).toFixed(2) + "px)" : "none";
        n.el.style.pointerEvents = vis ? "auto" : "none";
        n.el.style.zIndex = hovered === n.el ? 1000 : Math.round((slot + 3) * 10);
        n.el.tabIndex = Math.abs(slot - 2) < 0.5 ? 0 : -1;
        n.motion.style.transform = still2 ? "" : "translate3d(" + (travel * W * 0.009 * side).toFixed(2) + "px," + (-str * W * 0.014).toFixed(2) + "px,0) rotate(" + (travel * (slot < 2 ? -1 : 1) * (0.7 + side * 1.8)).toFixed(3) + "deg) scale(" + (1 + str * 0.012).toFixed(4) + ")";
      });
    };
    var take = function () { if (!manual) { manual = true; target = value; } };
    var move = function (d) { take(); target = Math.round(target) + d; };
    var frame = function (now) {
      if (!manual) {
        var t = ((now - t0) % 5300) / 1000;
        value = t < 2.1 ? -1 + ease(t / 2.1) : t < 3.72 ? 0 : -ease((t - 3.72) / 1.58);
      } else if (!dragging) {
        var d = target - value;
        vel = (vel + d * 0.105) * 0.72; value += vel;
        if (Math.abs(d) < 0.0005 && Math.abs(vel) < 0.0005) { value = target; vel = 0; }
        if (Math.abs(value) > N) { var cyc = Math.round(value / N) * N; value -= cyc; target -= cyc; }
      }
      var fd = value - lastV; fd -= Math.round(fd / N) * N; lastV = value;
      var req = cl(fd * 28, -1, 1);
      vm += (req - vm) * (Math.abs(req) > Math.abs(vm) ? 0.38 : 0.115);
      if (Math.abs(vm) < 0.0005) vm = 0;
      place();
      if (live) requestAnimationFrame(frame);
    };
    // only animate while the hero is on screen
    if ("IntersectionObserver" in window) {
      new IntersectionObserver(function (es) { var was = live; live = es[0].isIntersecting; if (live && !was) requestAnimationFrame(frame); }, { threshold: 0 }).observe(arc);
    }
    requestAnimationFrame(frame);
    stage.addEventListener("wheel", function (e) {
      e.preventDefault();
      if (Math.abs(e.deltaY) < 4) return;
      var now = performance.now(); if (now - lastWheel < 420) return; lastWheel = now;
      move(e.deltaY > 0 ? -1 : 1);
    }, { passive: false });
    stage.addEventListener("pointerdown", function (e) {
      if (e.button !== 0) return;
      take(); dragging = true; dragged = false; sx = e.clientX; sy = e.clientY; sv = value;
      axis = e.pointerType === "mouse" || e.pointerType === "pen" ? "y" : "x";
      if (axis === "y") { stage.setPointerCapture(e.pointerId); stage.classList.add("is-dragging"); }
    });
    stage.addEventListener("pointermove", function (e) {
      if (!dragging) return;
      var dl = axis === "y" ? e.clientY - sy : e.clientX - sx;
      if (Math.abs(dl) > 5) dragged = true;
      if (dragged) { value = sv + dl / (W * (axis === "y" ? 0.34 : 0.5)); target = value; }
    });
    var end = function () { if (!dragging) return; dragging = false; target = Math.round(value); stage.classList.remove("is-dragging"); };
    stage.addEventListener("pointerup", end);
    stage.addEventListener("pointercancel", function () { dragging = false; dragged = false; target = Math.round(value); stage.classList.remove("is-dragging"); });
    stage.addEventListener("click", function (e) {
      var card = e.target.closest(".arc-card"); if (!card) return;
      if (dragged) { e.preventDefault(); dragged = false; return; }
      var slot = parseInt(card.getAttribute("data-logical"), 10) + value;
      if (Math.abs(slot - 2) > 0.5) { e.preventDefault(); move(2 - Math.round(slot)); }
    });
    stage.addEventListener("dragstart", function (e) { e.preventDefault(); });
    nodes.forEach(function (n) {
      n.el.addEventListener("mouseenter", function () { hovered = n.el; });
      n.el.addEventListener("mouseleave", function () { if (hovered === n.el) hovered = null; });
    });
    arc.addEventListener("keydown", function (e) {
      if (e.key === "ArrowDown" || e.key === "ArrowRight") { e.preventDefault(); move(-1); setTimeout(function () { var f = stage.querySelector('.arc-card[tabindex="0"]'); if (f) f.focus(); }, 450); }
      if (e.key === "ArrowUp" || e.key === "ArrowLeft") { e.preventDefault(); move(1); setTimeout(function () { var f = stage.querySelector('.arc-card[tabindex="0"]'); if (f) f.focus(); }, 450); }
    });
  }
})();
