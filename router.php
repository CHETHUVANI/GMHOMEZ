<?php
$uri  = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$root = __DIR__;

// serve real files (css/js/images) directly
if ($uri !== '/' && file_exists($root . $uri)) {
  return false;
}




// send everything else to index.php
require $root . '/index.php';
