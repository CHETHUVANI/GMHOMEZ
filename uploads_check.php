<?php
header('Content-Type: text/plain');
$root = __DIR__;
$dir  = $root . '/uploads';
echo "Docroot: $root\n";
echo "Uploads exists: " . (is_dir($dir) ? 'YES' : 'NO') . "\n\n";
if (!is_dir($dir)) exit;

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
  $p = str_replace($root . '/', '', $f->getPathname());
  echo $p . "\n";
}
