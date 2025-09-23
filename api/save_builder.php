<?php
// api/save_builder.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

function gm_slug($s){
  $s = strtolower(trim($s));
  $s = preg_replace('~[^a-z0-9]+~', '-', $s);
  return trim($s, '-');
}
function gm_read_json($f){
  if (!is_file($f)) return [];
  $j = json_decode(@file_get_contents($f), true);
  return is_array($j) ? $j : [];
}
function gm_write_json_safe($f, $arr){
  $dir = dirname($f);
  if (!is_dir($dir)) @mkdir($dir, 0777, true);
  $tmp = $f . '.tmp';
  file_put_contents($tmp, json_encode(array_values($arr), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
  $check = json_decode(@file_get_contents($tmp), true);
  if (!is_array($check)) { @unlink($tmp); http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Invalid JSON write']); exit; }
  @rename($tmp, $f);
}

$ROOT = realpath(__DIR__ . '/..'); // project root
$file = $ROOT . '/data/builders.json';

$name = trim($_POST['name'] ?? '');
if ($name === '') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Missing name']); exit; }

$slug = gm_slug($name);
if ($slug === '') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Bad name']); exit; }

$list = gm_read_json($file);

// De-dupe (case-insensitive)
foreach ($list as $b) {
  if (strcasecmp($b['name'] ?? '', $name) === 0 || strcasecmp($b['slug'] ?? '', $slug) === 0) {
    echo json_encode(['ok'=>true, 'name'=>$b['name'], 'slug'=>$b['slug']]);
    exit;
  }
}

$list[] = ['name' => $name, 'slug' => $name]; // store plain name; builders.php uses urlencode
gm_write_json_safe($file, $list);

echo json_encode(['ok'=>true, 'name'=>$name, 'slug'=>$name]);
