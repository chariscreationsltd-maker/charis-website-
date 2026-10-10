/*
 * Charis Creations website editor.
 *
 * Talks to CharisOS (Supabase) directly from the browser with the owner's own
 * sign-in: Supabase Auth for the session, the public site-content functions
 * for reading and publishing, and the site-media bucket for photos. CharisOS
 * checks the account's role on every publish and upload, so this page holds
 * no special powers of its own.
 */
(function () {
  "use strict";

  var B = document.body;
  var SB = B.getAttribute("data-sb-url");
  var KEY = B.getAttribute("data-sb-key");
  var MEDIA = B.getAttribute("data-media-prefix");
  var $ = function (id) { return document.getElementById(id); };

  var session = null;   // { access, refresh, exp, email }
  var me = null;        // { can_edit, name, role }
  var meta = null;      // { pages, view, photos }
  var cur = null;       // { page, label, view, defaults, saved, work }
  var fields = {};      // path -> refresh()
  var osReady = true;   // false until the CharisOS functions exist

  // ---------- small helpers ----------
  function el(tag, attrs, kids) {
    var n = document.createElement(tag);
    if (attrs) Object.keys(attrs).forEach(function (k) {
      var v = attrs[k];
      if (v === null || v === undefined || v === false) return;
      if (k === "text") n.textContent = v;
      else if (k === "on") Object.keys(v).forEach(function (ev) { n.addEventListener(ev, v[ev]); });
      else if (k === "class") n.className = v;
      else n.setAttribute(k, v === true ? "" : v);
    });
    (kids || []).forEach(function (c) { if (c) n.appendChild(typeof c === "string" ? document.createTextNode(c) : c); });
    return n;
  }
  function clone(o) { return JSON.parse(JSON.stringify(o)); }
  function same(a, b) { return JSON.stringify(a) === JSON.stringify(b); }
  function store(k, v) { try { if (v === null) sessionStorage.removeItem(k); else sessionStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }
  function load(k) { try { return JSON.parse(sessionStorage.getItem(k) || "null"); } catch (e) { return null; } }

  var toastTimer;
  function toast(msg, bad) {
    var t = $("toast");
    t.textContent = msg; t.hidden = false; t.classList.toggle("is-bad", !!bad);
    clearTimeout(toastTimer); toastTimer = setTimeout(function () { t.hidden = true; }, bad ? 7000 : 3500);
  }
  function notice(html) { var n = $("notice"); if (!html) { n.hidden = true; return; } n.innerHTML = html; n.hidden = false; }

  var LABELS = {
    eyebrow: "Small label above the heading", heading: "Heading", headline: "Headline", support: "Supporting text",
    lede: "Intro text", alt: "Photo description (read aloud to blind visitors)", href: "Link", url: "Link", link: "Link",
    q: "Question", a: "Answer", cta: "Button", label: "Label", photo: "Photo", photos: "Photos", video: "Video (MP4 address)",
    youtube: "YouTube video ID", meta: "Search engine listing", title: "Title", description: "Description", line: "One-line description",
    items: "Items", button: "Button", whatsapp: "WhatsApp link", phoneHref: "Phone (dialling format)", phoneAltHref: "Second phone (dialling format)",
    phoneAlt: "Second phone", siteName: "Site name", tagline: "Tagline", taglineSub: "Tagline (second line)"
  };
  function human(k) {
    if (/^\d+$/.test(k)) return "#" + (Number(k) + 1);
    if (LABELS[k]) return LABELS[k];
    var s = String(k).replace(/([a-z])([A-Z])/g, "$1 $2").replace(/[_-]+/g, " ");
    return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
  }
  function itemName(o) {
    if (!o || typeof o !== "object") return "";
    var n = o.title || o.name || o.label || o.heading || o.q || o.headline || "";
    return typeof n === "string" ? n.replace(/<[^>]+>/g, "").slice(0, 60) : "";
  }

  function getAt(obj, path) {
    var parts = path.split("."), v = obj;
    for (var i = 0; i < parts.length; i++) { if (v === null || typeof v !== "object") return undefined; v = v[parts[i]]; }
    return v;
  }
  function photoSrc(v, size) {
    if (!v) return "";
    if (v.indexOf(MEDIA) === 0) return v.replace(/-1600\.(webp|jpg)$/, "-" + size + ".$1");
    if (/^(https?:)?\/\/|^\//.test(v)) return v;
    return "/assets/img/photo/" + v + "-" + size + ".webp";
  }

  // ---------- CharisOS: sign-in and calls ----------
  function setSession(j) {
    session = { access: j.access_token, refresh: j.refresh_token, exp: Date.now() / 1000 + (j.expires_in || 3600), email: (j.user && j.user.email) || (session && session.email) || "" };
    store("charisEditor", session);
  }
  async function authCall(grant, body) {
    var r = await fetch(SB + "/auth/v1/token?grant_type=" + grant, {
      method: "POST", headers: { apikey: KEY, "Content-Type": "application/json" }, body: JSON.stringify(body)
    });
    var j = await r.json().catch(function () { return {}; });
    if (!r.ok) {
      var m = j.error_description || j.msg || j.message || "Sign-in failed";
      if (/invalid login/i.test(m)) m = "That email and password don't match a CharisOS account.";
      if (/not confirmed/i.test(m)) m = "This CharisOS account hasn't been confirmed yet.";
      throw new Error(m);
    }
    setSession(j);
  }
  async function token() {
    if (!session) throw new Error("Signed out");
    if (session.exp - 60 < Date.now() / 1000) {
      try { await authCall("refresh_token", { refresh_token: session.refresh }); }
      catch (e) { signOut(true); throw new Error("Your session ended. Please sign in again."); }
    }
    return session.access;
  }
  async function rpc(fn, args) {
    var r = await fetch(SB + "/rest/v1/rpc/" + fn, {
      method: "POST",
      headers: { apikey: KEY, Authorization: "Bearer " + (await token()), "Content-Type": "application/json", Accept: "application/json" },
      body: JSON.stringify(args || {})
    });
    var txt = await r.text(), j = null;
    try { j = txt ? JSON.parse(txt) : null; } catch (e) { j = null; }
    if (!r.ok) {
      var err = new Error((j && (j.message || j.hint)) || ("CharisOS answered " + r.status));
      err.code = j && j.code; err.status = r.status;
      if (err.code === "PGRST202" || err.code === "42883") err.missing = true;
      throw err;
    }
    return j;
  }
  async function putFile(path, blob) {
    var r = await fetch(SB + "/storage/v1/object/site-media/" + path, {
      method: "POST",
      headers: { apikey: KEY, Authorization: "Bearer " + (await token()), "Content-Type": blob.type, "cache-control": "31536000", "x-upsert": "false" },
      body: blob
    });
    if (!r.ok) {
      var j = await r.json().catch(function () { return {}; });
      var m = j.message || j.error || ("Upload failed (" + r.status + ")");
      if (/bucket not found/i.test(m)) m = "The photo store isn't set up in CharisOS yet (site-media bucket).";
      if (/row-level security|unauthorized|403/i.test(m)) m = "CharisOS didn't allow this upload. Only Owner and Admin accounts can add photos.";
      throw new Error(m);
    }
  }

  function setupMissing() {
    osReady = false;
    notice("<b>CharisOS isn't switched on for the website editor yet.</b> You can look around, but publishing and photo uploads won't work until the CharisOS developer runs <code>02_website_editor.sql</code> (in the <code>charis-os-integration</code> folder of the website repo).");
  }

  // ---------- photos: resize in the browser, upload two sizes ----------
  function canvasBlob(canvas, type, q) { return new Promise(function (res) { canvas.toBlob(res, type, q); }); }
  async function encode(src, w, h) {
    var c = document.createElement("canvas"); c.width = w; c.height = h;
    var x = c.getContext("2d"); x.imageSmoothingQuality = "high"; x.drawImage(src, 0, 0, w, h);
    var b = await canvasBlob(c, "image/webp", 0.82);
    if (!b || b.type !== "image/webp") b = await canvasBlob(c, "image/jpeg", 0.85); // Safari cannot make WebP
    return b;
  }
  async function uploadPhoto(file) {
    if (!/^image\/(jpeg|png|webp)$/.test(file.type)) throw new Error(file.name + ": use a JPG, PNG or WebP photo. (iPhone HEIC photos: share or export them as JPG first.)");
    if (file.size > 40 * 1024 * 1024) throw new Error(file.name + " is over 40 MB. Export a smaller copy first.");
    var bmp = await createImageBitmap(file);
    var w0 = bmp.width, h0 = bmp.height;
    var w = Math.round(Math.min(1600, w0, 2200 * w0 / h0)), h = Math.round(w * h0 / w0);
    var sw = Math.min(800, w), sh = Math.round(sw * h0 / w0);
    var large = await encode(bmp, w, h), small = await encode(bmp, sw, sh);
    if (bmp.close) bmp.close();
    var ext = large.type === "image/webp" ? "webp" : "jpg";
    var id = Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
    var base = cur.page + "/" + id + "_" + w + "x" + h;
    await putFile(base + "-1600." + ext, large);
    await putFile(base + "-800." + ext, small);
    return MEDIA + base + "-1600." + ext;
  }
  function pickFiles(multi) {
    return new Promise(function (res) {
      var inp = $(multi ? "file-many" : "file-one");
      inp.value = "";
      inp.onchange = function () { res(Array.prototype.slice.call(inp.files || [])); };
      inp.click();
    });
  }

  // ---------- the form ----------
  function valueAt(path) { return Object.prototype.hasOwnProperty.call(cur.work, path) ? cur.work[path] : getAt(cur.defaults, path); }
  function setValue(path, v) {
    if (same(v, getAt(cur.defaults, path))) delete cur.work[path]; else cur.work[path] = v;
    if (fields[path]) fields[path]();
    updateDirty();
  }
  function changedClass(node, path) {
    var changed = Object.prototype.hasOwnProperty.call(cur.work, path);
    node.classList.toggle("is-changed", changed);
    return changed;
  }
  function updateDirty() {
    var dirty = !same(cur.work, cur.saved);
    var n = Object.keys(cur.work).filter(function (k) { return !same(cur.work[k], cur.saved[k]); }).length +
            Object.keys(cur.saved).filter(function (k) { return !(k in cur.work); }).length;
    $("publish-btn").disabled = !dirty || !osReady || !(me && me.can_edit);
    $("discard-btn").disabled = !dirty;
    $("publish-btn").textContent = dirty ? "Publish " + n + " change" + (n === 1 ? "" : "s") : "Published";
  }

  function fieldShell(path, label, kids, opts) {
    var reset = el("button", { class: "linkbtn", type: "button", text: "Use original", on: { click: function () { setValue(path, getAt(cur.defaults, path)); } } });
    var head = el("div", { class: "field__head" }, [el("span", { class: "field__label", text: label }), el("span", { class: "badge", text: "Changed" }), reset]);
    var node = el("div", { class: "field" + (opts && opts.cls ? " " + opts.cls : ""), "data-path": path }, [head].concat(kids));
    if (opts && opts.hint) node.appendChild(el("p", { class: "field__hint", text: opts.hint }));
    return node;
  }

  function textField(path, key, label, crumbs) {
    var v = valueAt(path);
    var long = typeof v === "string" && (v.length > 90 || v.indexOf("\n") > -1);
    var id = "f-" + path.replace(/[^a-z0-9]/gi, "-");
    var input = long ? el("textarea", { id: id, rows: String(Math.min(9, Math.ceil(v.length / 72) + 1)) }) : el("input", { id: id, type: "text" });
    var hint = null;
    if (typeof v === "string" && /<a\s/i.test(v)) hint = 'Links written like <a href="https://…">text</a> are kept. Other HTML is removed.';
    if (key === "youtube") hint = "Only the ID from the YouTube link, e.g. SwDNzUg5HDo.";
    if (key === "video") hint = "Full address of an MP4 file.";
    if (key === "link") hint = "A page on this site like /projects/, a full https:// address, or a shortcut name from Site-wide › Links (for example: date).";
    var node = fieldShell(path, label, [input], { hint: hint });
    node.querySelector(".field__label").setAttribute("for", id);
    var lbl = node.querySelector(".field__label"); var real = el("label", { class: "field__label", for: id, text: label }); lbl.replaceWith(real);
    node.setAttribute("data-search", (crumbs + " " + v).toLowerCase());
    input.addEventListener("input", function () { setValue(path, input.value); });
    fields[path] = function () { var now = valueAt(path); if (input.value !== now) input.value = now; changedClass(node, path); };
    fields[path]();
    return node;
  }

  function boolField(path, label, crumbs) {
    var box = el("input", { type: "checkbox" });
    var node = fieldShell(path, label, [el("label", { class: "check" }, [box, el("span", { text: "On" })])]);
    node.setAttribute("data-search", crumbs.toLowerCase());
    box.addEventListener("change", function () { setValue(path, box.checked); });
    fields[path] = function () { box.checked = !!valueAt(path); changedClass(node, path); };
    fields[path]();
    return node;
  }

  function numberField(path, label, crumbs) {
    var inp = el("input", { type: "number", step: "any" });
    var node = fieldShell(path, label, [inp]);
    node.setAttribute("data-search", crumbs.toLowerCase());
    inp.addEventListener("input", function () { if (inp.value !== "") setValue(path, Number(inp.value)); });
    fields[path] = function () { inp.value = valueAt(path); changedClass(node, path); };
    fields[path]();
    return node;
  }

  function photoField(path, label, crumbs) {
    var img = el("img", { alt: "", loading: "lazy" });
    var empty = el("span", { class: "photo__empty", text: "No photo" });
    var status = el("span", { class: "photo__status", "aria-live": "polite" });
    var replace = el("button", { class: "btn btn--small", type: "button", text: "Upload new photo" });
    var choose = el("button", { class: "btn btn--small btn--ghost", type: "button", text: "Pick a site photo" });
    var node = fieldShell(path, label, [el("div", { class: "photo" }, [el("div", { class: "photo__frame" }, [img, empty]), el("div", { class: "photo__actions" }, [replace, choose, status])])], { cls: "field--photo" });
    node.setAttribute("data-search", (crumbs + " photo image picture").toLowerCase());
    replace.addEventListener("click", async function () {
      var files = await pickFiles(false); if (!files.length) return;
      replace.disabled = true; status.textContent = "Uploading…";
      try { setValue(path, await uploadPhoto(files[0])); status.textContent = "Uploaded. Press Publish to put it live."; }
      catch (e) { status.textContent = ""; toast(e.message, true); }
      replace.disabled = false;
    });
    choose.addEventListener("click", function () { openPicker(function (name) { setValue(path, name); }); });
    fields[path] = function () {
      var v = valueAt(path);
      if (v) { img.src = photoSrc(v, 800); img.hidden = false; empty.hidden = true; } else { img.hidden = true; empty.hidden = false; }
      changedClass(node, path);
    };
    fields[path]();
    return node;
  }

  function galleryField(path, label, crumbs) {
    var grid = el("ul", { class: "thumbs" });
    var status = el("span", { class: "photo__status", "aria-live": "polite" });
    var add = el("button", { class: "btn btn--small", type: "button", text: "Add photos" });
    var note = el("p", { class: "field__hint" });
    var node = fieldShell(path, label, [note, grid, el("div", { class: "photo__actions" }, [add, status])], { cls: "field--gallery" });
    node.setAttribute("data-search", (crumbs + " gallery photos images").toLowerCase());
    function list() { return (valueAt(path) || []).slice(); }
    function move(i, d) { var l = list(); var j = i + d; if (j < 0 || j >= l.length) return; var t = l[i]; l[i] = l[j]; l[j] = t; setValue(path, l); }
    add.addEventListener("click", async function () {
      var files = await pickFiles(true); if (!files.length) return;
      add.disabled = true;
      var l = list(), done = 0, failed = [];
      for (var i = 0; i < files.length; i++) {
        status.textContent = "Uploading " + (i + 1) + " of " + files.length + "…";
        try { l.push(await uploadPhoto(files[i])); done++; setValue(path, l.slice()); }
        catch (e) { failed.push(e.message); }
      }
      status.textContent = done ? done + " added. Press Publish to put them live." : "";
      if (failed.length) toast(failed[0] + (failed.length > 1 ? " (+" + (failed.length - 1) + " more)" : ""), true);
      add.disabled = false;
    });
    fields[path] = function () {
      var l = list();
      grid.innerHTML = "";
      note.textContent = l.length ? l.length + " photo" + (l.length === 1 ? "" : "s") + ", shown in this order. Portrait and landscape both work."
        : "Empty: this gallery still loads its photos from the old website. Add photos here to replace them.";
      l.forEach(function (p, i) {
        grid.appendChild(el("li", { class: "thumb" }, [
          el("img", { src: photoSrc(p, 800), alt: "", loading: "lazy" }),
          el("div", { class: "thumb__tools" }, [
            el("button", { type: "button", title: "Move earlier", "aria-label": "Move photo " + (i + 1) + " earlier", text: "←", on: { click: function () { move(i, -1); } } }),
            el("button", { type: "button", title: "Move later", "aria-label": "Move photo " + (i + 1) + " later", text: "→", on: { click: function () { move(i, 1); } } }),
            el("button", { type: "button", class: "danger", title: "Remove", "aria-label": "Remove photo " + (i + 1), text: "×", on: { click: function () { var x = list(); x.splice(i, 1); setValue(path, x); } } })
          ])
        ]));
      });
      changedClass(node, path);
    };
    fields[path]();
    return node;
  }

  function build(parent, value, path, key, crumbs, depth) {
    var label = human(key);
    var here = crumbs ? crumbs + " › " + label : label;
    if (key === "photos" && Array.isArray(value)) { parent.appendChild(galleryField(path, label, here)); return; }
    if (key === "photo" && typeof value === "string") { parent.appendChild(photoField(path, label, here)); return; }
    if (typeof value === "string") { parent.appendChild(textField(path, key, label, here)); return; }
    if (typeof value === "boolean") { parent.appendChild(boolField(path, label, here)); return; }
    if (typeof value === "number") { parent.appendChild(numberField(path, label, here)); return; }
    if (value === null || typeof value !== "object") return;
    var isList = Array.isArray(value);
    var keys = Object.keys(value);
    if (!keys.length) return;
    var box;
    if (depth === 0) {
      box = el("details", { class: "panel", "data-key": key }, [el("summary", {}, [el("span", { text: label }), el("small", { text: "" })])]);
    } else {
      var title = isList ? label : label;
      box = el("fieldset", { class: "group" + (isList ? " group--list" : "") }, [el("legend", { text: title })]);
    }
    var inner = el("div", { class: depth === 0 ? "panel__body" : "group__body" });
    box.appendChild(inner);
    keys.forEach(function (k) {
      var v = value[k], p = path ? path + "." + k : k;
      if (isList && v && typeof v === "object" && !Array.isArray(v)) {
        var nm = itemName(v);
        var card = el("fieldset", { class: "group group--item" }, [el("legend", { text: (Number(k) + 1) + (nm ? " · " + nm : "") })]);
        var body = el("div", { class: "group__body" }); card.appendChild(body);
        Object.keys(v).forEach(function (kk) { build(body, v[kk], p + "." + kk, kk, here + " › " + (nm || "#" + (Number(k) + 1)), depth + 2); });
        inner.appendChild(card);
      } else {
        build(inner, v, p, k, depth === 0 && !isList ? label : here, depth + 1);
      }
    });
    parent.appendChild(box);
  }

  function renderForm() {
    fields = {};
    var f = $("form"); f.innerHTML = "";
    var d = cur.defaults;
    Object.keys(d).forEach(function (k) {
      var v = d[k];
      if (v && typeof v === "object") build(f, v, k, k, "", 0);
    });
    // Top-level plain values (site-wide details) share one panel.
    var plain = Object.keys(d).filter(function (k) { return !(d[k] && typeof d[k] === "object"); });
    if (plain.length) {
      var box = el("details", { class: "panel", open: true }, [el("summary", {}, [el("span", { text: cur.page === "site" ? "Business details" : "General" })])]);
      var inner = el("div", { class: "panel__body" }); box.appendChild(inner);
      plain.forEach(function (k) { build(inner, d[k], k, k, "", 1); });
      f.insertBefore(box, f.firstChild);
    }
    // Search listing settings go last; the first real section starts open.
    var metaPanel = f.querySelector('.panel[data-key="meta"]'); if (metaPanel) f.appendChild(metaPanel);
    var panels = f.querySelectorAll(".panel");
    panels.forEach(function (p) { if (panels.length <= 3) p.open = true; });
    if (panels.length) panels[0].open = true;
    applyFilter();
    updateDirty();
  }

  function applyFilter() {
    var q = $("filter").value.trim().toLowerCase();
    document.querySelectorAll("#form .field").forEach(function (n) { n.hidden = !!q && (n.getAttribute("data-search") || "").indexOf(q) === -1; });
    document.querySelectorAll("#form .group").forEach(function (g) { g.hidden = !!q && !g.querySelector(".field:not([hidden])"); });
    document.querySelectorAll("#form .panel").forEach(function (p) {
      var any = !!p.querySelector(".field:not([hidden])");
      p.hidden = !!q && !any;
      if (q && any) p.open = true;
    });
  }

  // ---------- site photo picker ----------
  var pickerDlg = null;
  function openPicker(onPick) {
    if (!pickerDlg) {
      pickerDlg = el("dialog", { class: "picker" });
      document.body.appendChild(pickerDlg);
    }
    pickerDlg.innerHTML = "";
    var grid = el("ul", { class: "thumbs thumbs--pick" });
    (meta.photos || []).forEach(function (name) {
      grid.appendChild(el("li", {}, [el("button", { type: "button", class: "pick", title: name, on: { click: function () { pickerDlg.close(); onPick(name); } } }, [el("img", { src: photoSrc(name, 800), alt: name, loading: "lazy" })])]));
    });
    pickerDlg.appendChild(el("div", { class: "history__head" }, [el("h3", { text: "Photos already on the website" }), el("button", { class: "btn btn--ghost", type: "button", text: "Close", on: { click: function () { pickerDlg.close(); } } })]));
    pickerDlg.appendChild(grid);
    pickerDlg.showModal();
  }

  // ---------- pages ----------
  async function fetchJSON(url) { var r = await fetch(url, { cache: "no-store" }); if (!r.ok) throw new Error("Could not load " + url); return r.json(); }

  async function publishedPatch(page) {
    if (!osReady) return {};
    try {
      var r = await rpc("get_site_content", {});
      return (r && r.pages && r.pages[page]) || {};
    } catch (e) { if (e.missing) { setupMissing(); return {}; } throw e; }
  }

  async function openPage(page) {
    if (cur && !same(cur.work, cur.saved) && !window.confirm("You have changes that aren't published. Leave them?")) return;
    document.querySelectorAll("#pages button").forEach(function (b) { b.setAttribute("aria-current", b.getAttribute("data-page") === page ? "page" : "false"); });
    $("form").innerHTML = '<p class="muted loading">Loading…</p>';
    try {
      var info = await fetchJSON("/editor/api.php?page=" + encodeURIComponent(page));
      var patch = await publishedPatch(page);
      cur = { page: page, label: info.label, view: info.view, defaults: info.defaults, saved: clone(patch), work: clone(patch) };
      $("page-title").textContent = info.label;
      $("view-page").href = info.view;
      store("charisEditorPage", page);
      $("filter").value = "";
      renderForm();
    } catch (e) {
      $("form").innerHTML = "";
      toast(e.message, true);
    }
  }

  async function publish() {
    var btn = $("publish-btn");
    btn.disabled = true; btn.textContent = "Publishing…";
    try {
      await rpc("save_site_content", { p_page: cur.page, p_data: cur.work });
      cur.saved = clone(cur.work);
      var ping = function () { return fetch("/editor/refresh.php", { method: "POST" }).catch(function () {}); };
      await ping(); setTimeout(ping, 6000);
      toast("Published. The website shows it now.");
    } catch (e) {
      if (e.missing) setupMissing();
      toast(e.status === 403 || e.code === "42501" ? "CharisOS didn't allow this. Only Owner and Admin accounts can publish." : e.message, true);
    }
    updateDirty();
  }

  async function openHistory() {
    var list = $("history-list");
    list.innerHTML = '<li class="muted">Loading…</li>';
    $("history").showModal();
    try {
      var rows = (await rpc("list_site_content_history", { p_page: cur.page })) || [];
      list.innerHTML = "";
      if (!rows.length) { list.appendChild(el("li", { class: "muted", text: "Nothing published for this page yet." })); return; }
      rows.forEach(function (r, i) {
        var when = new Date(r.saved_at).toLocaleString(undefined, { dateStyle: "medium", timeStyle: "short" });
        list.appendChild(el("li", {}, [
          el("div", {}, [el("b", { text: when }), el("span", { class: "muted", text: " · " + (r.saved_by_name || "Unknown") + " · " + r.changes + " changed field" + (r.changes === 1 ? "" : "s") + (i === 0 ? " · live now" : "") })]),
          i === 0 ? null : el("button", { class: "btn btn--small btn--ghost", type: "button", text: "Restore", on: { click: async function () {
            if (!window.confirm("Put this version live again?")) return;
            try { await rpc("restore_site_content", { p_id: r.id }); fetch("/editor/refresh.php", { method: "POST" }).catch(function () {}); $("history").close(); cur.saved = cur.work = {}; await openPage(cur.page); toast("Restored."); }
            catch (e) { toast(e.message, true); }
          } } })
        ]));
      });
    } catch (e) {
      list.innerHTML = "";
      list.appendChild(el("li", { class: "muted", text: e.missing ? "History needs the CharisOS setup (02_website_editor.sql)." : e.message }));
    }
  }

  // ---------- start ----------
  function signOut(quiet) {
    session = null; me = null; store("charisEditor", null);
    $("app").hidden = true; $("signin").hidden = false;
    if (!quiet) toast("Signed out.");
  }

  async function startApp() {
    $("signin").hidden = true; $("app").hidden = false;
    osReady = true; notice(null);
    try {
      me = await rpc("site_editor_me", {});
    } catch (e) {
      if (e.missing) { setupMissing(); me = { can_edit: false, name: session.email, role: "" }; }
      else if (/session/i.test(e.message)) return;
      else { toast(e.message, true); me = { can_edit: false, name: session.email, role: "" }; }
    }
    $("who").textContent = (me.name || session.email) + (me.role ? " · " + me.role : "");
    if (osReady && !me.can_edit) notice("<b>This account can look but not publish.</b> Only Owner and Admin accounts in CharisOS can change the website.");
    if (!meta) meta = await fetchJSON("/editor/api.php");
    var nav = $("pages"); nav.innerHTML = "";
    Object.keys(meta.pages).forEach(function (k) {
      nav.appendChild(el("button", { type: "button", "data-page": k, on: { click: function () { openPage(k); } } }, [el("span", { text: meta.pages[k].split(":")[0] }), meta.pages[k].indexOf(":") > -1 ? el("small", { text: meta.pages[k].split(":")[1].trim() }) : null]));
    });
    var last = load("charisEditorPage");
    openPage(last && meta.pages[last] ? last : Object.keys(meta.pages)[1] || "site");
  }

  $("signin-form").addEventListener("submit", async function (ev) {
    ev.preventDefault();
    var f = ev.target, err = $("signin-error"), btn = f.querySelector("button");
    err.hidden = true;
    if (!KEY) { err.textContent = "This server doesn't have the CharisOS key yet (inc/charis-os-key.php)."; err.hidden = false; return; }
    if (!f.email.value || !f.password.value) { err.textContent = "Enter your email and password."; err.hidden = false; return; }
    btn.disabled = true; btn.textContent = "Signing in…";
    try { await authCall("password", { email: f.email.value.trim(), password: f.password.value }); f.password.value = ""; await startApp(); }
    catch (e) { err.textContent = e.message === "Failed to fetch" ? "Couldn't reach CharisOS. Check the connection and try again." : e.message; err.hidden = false; }
    btn.disabled = false; btn.textContent = "Sign in";
  });
  $("signout").addEventListener("click", function () {
    if (cur && !same(cur.work, cur.saved) && !window.confirm("You have changes that aren't published. Sign out anyway?")) return;
    cur = null; signOut(false);
  });
  $("publish-btn").addEventListener("click", publish);
  $("discard-btn").addEventListener("click", function () { cur.work = clone(cur.saved); renderForm(); });
  $("history-btn").addEventListener("click", openHistory);
  $("filter").addEventListener("input", applyFilter);
  window.addEventListener("beforeunload", function (e) { if (cur && !same(cur.work, cur.saved)) { e.preventDefault(); e.returnValue = ""; } });

  session = load("charisEditor");
  if (session && session.access) startApp(); else $("signin").hidden = false;
})();
