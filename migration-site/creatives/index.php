<?php
// The creatives page exactly as it is on the current site (from the WordPress export),
// kept as-is while the rest of the site moves over. Tweaks go in content/legacy/creatives.html.
header('Content-Type: text/html; charset=utf-8');
readfile(dirname(__DIR__) . '/content/legacy/creatives.html');
