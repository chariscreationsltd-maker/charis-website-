<?php
/**
 * Website editor. The owner signs in with their CharisOS account; CharisOS
 * decides who may publish (Owner and Admin roles). Nothing here can change
 * code, only the text, links and photos listed in /content/*.json.
 */
require dirname(__DIR__) . '/inc/bootstrap.php';
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Website editor · Charis Creations</title>
  <link rel="icon" type="image/png" href="/assets/img/logo-symbol.png">
  <link rel="stylesheet" href="<?= asset('editor/editor.css') ?>">
</head>
<body data-sb-url="<?= e(CHARIS_OS_URL) ?>" data-sb-key="<?= e(charis_os_key()) ?>" data-media-prefix="<?= e(SITE_MEDIA_PREFIX) ?>">

  <section class="signin" id="signin" hidden>
    <form class="signin__card" id="signin-form" novalidate>
      <img src="/assets/img/logo-symbol.png" alt="" width="44" height="44">
      <h1>Website editor</h1>
      <p class="muted">Sign in with your CharisOS account. Owner and Admin accounts can publish changes.</p>
      <label>Email<input type="email" name="email" autocomplete="username" required></label>
      <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
      <p class="signin__error" id="signin-error" role="alert" hidden></p>
      <button class="btn btn--primary" type="submit">Sign in</button>
    </form>
  </section>

  <div class="app" id="app" hidden>
    <header class="bar">
      <a class="bar__brand" href="/" target="_blank" rel="noopener"><img src="/assets/img/logo-symbol.png" alt="" width="28" height="28"><span>Charis <b>Website editor</b></span></a>
      <div class="bar__user"><span id="who"></span><button class="btn btn--ghost" type="button" id="signout">Sign out</button></div>
    </header>
    <nav class="pages" id="pages" aria-label="Pages"></nav>
    <main class="main" id="main">
      <div class="notice" id="notice" hidden></div>
      <div class="pagehead">
        <div><p class="eyebrow">Editing</p><h2 id="page-title">Choose a page</h2></div>
        <div class="pagehead__actions">
          <a class="btn btn--ghost" id="view-page" href="/" target="_blank" rel="noopener">View page</a>
          <button class="btn btn--ghost" type="button" id="history-btn">History</button>
          <button class="btn btn--ghost" type="button" id="discard-btn" disabled>Discard</button>
          <button class="btn btn--primary" type="button" id="publish-btn" disabled>Publish</button>
        </div>
      </div>
      <p class="hint">Changes go live when you press <b>Publish</b>. Fields you've changed are marked in orange; <b>Use original</b> puts back the text or photo that came with the website.</p>
      <div class="filter"><input type="search" id="filter" placeholder="Find a field, e.g. headline, phone, photo" aria-label="Find a field"></div>
      <div id="form" class="form"></div>
    </main>
  </div>

  <dialog class="history" id="history">
    <form method="dialog" class="history__head"><h3>Published versions</h3><button class="btn btn--ghost" value="close">Close</button></form>
    <p class="muted">Restoring a version republishes it straight away. The version you replace stays in this list.</p>
    <ol id="history-list" class="history__list"></ol>
  </dialog>

  <div class="toast" id="toast" role="status" aria-live="polite" hidden></div>
  <input type="file" id="file-one" accept="image/jpeg,image/png,image/webp" hidden>
  <input type="file" id="file-many" accept="image/jpeg,image/png,image/webp" multiple hidden>
  <script src="<?= asset('editor/editor.js') ?>"></script>
</body>
</html>
