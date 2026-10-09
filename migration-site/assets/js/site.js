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

  // Client voices: a pinned deck that deals itself upward as you scroll.
  // One progress value p (0..1) from the tall track; the front card lifts and
  // tilts back off the top while the one behind rises and grows into focus.
  // The last card stays. Transforms are written straight to the DOM.
  var track = document.querySelector("[data-stack]");
  if (track && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    var cards = Array.prototype.slice.call(track.querySelectorAll(".voice"));
    var now = track.querySelector("[data-stack-now]");
    var steps = cards.length - 1, ticking = false, shown = -1;
    var lerp = function (a, b, t) { return a + (b - a) * t; };
    var render = function () {
      ticking = false;
      var r = track.getBoundingClientRect(), span = track.offsetHeight - window.innerHeight;
      if (r.bottom < -100 || r.top > window.innerHeight + 100) return;
      var p = span > 0 ? Math.min(1, Math.max(0, -r.top / span)) : 0;
      var active = steps > 0 ? Math.min(Math.floor(p * steps), steps - 1) : 0;
      var segP = steps > 0 ? p * steps - active : 0;
      if (steps > 0 && p >= 1) { active = steps - 1; segP = 1; }
      cards.forEach(function (card, i) {
        var y, rx = 0, sc = 1, o = 1;
        if (i < active) { y = -250; rx = 35; o = 0; }
        else if (i === active && steps > 0) { y = lerp(-50, -200, segP); rx = lerp(0, 35, segP); o = segP > 0.85 ? (1 - segP) / 0.15 : 1; }
        else { var b = i - active - (steps > 0 ? segP : 0); y = -50 + b * 5; sc = 1 - b * 0.075; o = b > 2.5 ? Math.max(0, 3.5 - b) : 1; }
        card.style.transform = "translate(-50%," + y.toFixed(2) + "%) rotateX(" + rx.toFixed(2) + "deg) scale(" + sc.toFixed(3) + ")";
        card.style.opacity = o.toFixed(3);
      });
      var current = Math.min(cards.length - 1, active + (segP > 0.5 ? 1 : 0));
      if (now && current !== shown) { shown = current; now.textContent = (current < 9 ? "0" : "") + (current + 1); }
    };
    var queue = function () { if (!ticking) { ticking = true; requestAnimationFrame(render); } };
    window.addEventListener("scroll", queue, { passive: true });
    window.addEventListener("resize", queue);
    render();
  }
})();
