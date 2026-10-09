<?php
require __DIR__ . '/inc/bootstrap.php';
http_response_code(404);
page_start([
    'title' => 'Page not found | Charis Creations',
    'description' => 'The page you were looking for may have a new home.',
    'path' => '/404/',
], '');
?>
    <section class="notfound" aria-labelledby="page-title">
      <div class="wrap">
        <h1 id="page-title">This page has moved.</h1>
        <p class="lede">The page you were looking for may have a new home. You can find our work or return to the homepage.</p>
        <div class="btn-row">
          <?= button('Go Home', '/', 'primary') ?>
          <?= button('See Our Work', '/projects/', 'outline') ?>
        </div>
      </div>
    </section>
<?php page_end(); ?>
