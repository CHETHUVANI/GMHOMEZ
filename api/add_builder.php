<?php
// /api/add_builder.php
header('Content-Type: application/json');

// project root (../ from /api)
$ROOT = realpath(__DIR__ . '/..');
$FILE = $ROOT . '/data/builders.json';

function readj($f){
  return is_file($f) ? (json_decode(file_get_contents($f), true) ?: []) : [];
}
function writej($f, $arr){
  @is_dir(dirname($f)) || @mkdir(dirname($f), 0777, true);
  $tmp = $f . '.tmp';
  file_put_contents($tmp, json_encode(array_values($arr), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
  $check = json_decode(file_get_contents($tmp), true);
  if (!is_array($check)) { @unlink($tmp); http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Invalid JSON']); exit; }
  @rename($tmp, $f);
}

$name = trim($_POST['name'] ?? '');
$slug = trim($_POST['slug'] ?? '');

if ($name === '') { echo json_encode(['ok'=>false,'error'=>'Name required']); exit; }
if ($slug === '') {
  $slug = strtolower(trim($name));
  $slug = preg_replace('~[^a-z0-9]+~', '-', $slug);
  $slug = trim($slug, '-');
}

// read, de-dupe by name (case-insensitive)
$list = readj($FILE);
foreach ($list as $b) {
  if (strcasecmp($b['name'] ?? '', $name) === 0) {
    echo json_encode(['ok'=>false,'error'=>'Builder already exists']); exit;
  }
}

$list[] = ['name' => $name, 'slug' => $slug];
writej($FILE, $list);
echo json_encode(['ok'=>true,'item'=>['name'=>$name,'slug'=>$slug]]);
