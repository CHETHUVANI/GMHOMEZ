<?php
// gm-homez/api/search.php
declare(strict_types=1);
error_reporting(E_ALL); ini_set('display_errors', '1');
header('Content-Type: application/json; charset=utf-8');

$ROOT = realpath(__DIR__ . '/..'); // gm-homez
if (file_exists($ROOT . '/config.php')) require_once $ROOT . '/config.php';
if (file_exists($ROOT . '/lib/render.php')) require_once $ROOT . '/lib/render.php';

// data paths (fallbacks)
$DATA_DIR   = $DATA_DIR   ?? realpath($ROOT . '/data');
$PROPS_JSON = $PROPS_JSON ?? ($DATA_DIR ? $DATA_DIR . '/properties.json' : $ROOT . '/data/properties.json');

function read_props_fallback(string $jsonPath = null): array {
  if (!$jsonPath || !file_exists($jsonPath)) return [];
  $raw = file_get_contents($jsonPath);
  $arr = json_decode($raw, true);
  return is_array($arr) ? $arr : [];
}
$all = function_exists('read_properties') ? read_properties() : read_props_fallback($PROPS_JSON);

// inputs
$q        = strtolower(trim($_GET['q'] ?? ''));
$city     = strtolower(trim($_GET['city'] ?? ''));
$locality = strtolower(trim($_GET['locality'] ?? ''));
$bhk      = trim($_GET['bhk'] ?? '');
$min      = trim($_GET['min'] ?? '');
$max      = trim($_GET['max'] ?? '');

// infer bhk/locality from q if not set
if ($bhk === '' && preg_match('/(\d+)\s*bhk/i', $q, $m)) $bhk = $m[1];
if ($locality === '' && preg_match('/\b(?:in|at)\s+([a-z][a-z\s\-]{2,})$/i', $q, $m)) $locality = strtolower(trim($m[1]));

$bhkInt   = ($bhk !== '' && is_numeric($bhk)) ? (int)$bhk : null;
$minInt   = ($min !== '' && is_numeric($min)) ? (int)$min : null;
$maxInt   = ($max !== '' && is_numeric($max)) ? (int)$max : null;

// compute web base ("/gm-homez")
$BASE = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');
if ($BASE === '\\' || $BASE === '/') $BASE = '';

// filter
$filtered = array_values(array_filter($all, function($p) use($q,$city,$locality,$bhkInt,$minInt,$maxInt) {
  $title = strtolower($p['title'] ?? ($p['name'] ?? ''));
  $loc   = strtolower($p['location'] ?? '');
  $c     = strtolower($p['city'] ?? '');
  $id    = strtolower((string)($p['id'] ?? $p['pid'] ?? ''));
  $bed   = isset($p['bedrooms']) ? (int)$p['bedrooms'] : null;

  // price: tolerate currency/commas/strings
  $priceRaw = (string)($p['price'] ?? '');
  $priceNum = is_numeric($priceRaw) ? (int)$priceRaw : (int)preg_replace('/[^\d]/', '', $priceRaw);

  $byQ   = $q === '' || str_contains($title,$q) || str_contains($loc,$q) || str_contains($c,$q) || str_contains($id,$q);
  $byC   = $city === '' || $c === $city;
  $byL   = $locality === '' || str_contains($loc, $locality) || str_contains($title, $locality);
  $byBHK = $bhkInt === null || $bed === null || $bed === $bhkInt;
  $byMin = $minInt === null || ($priceNum>0 && $priceNum >= $minInt);
  $byMax = $maxInt === null || ($priceNum>0 && $priceNum <= $maxInt);

  return $byQ && $byC && $byL && $byBHK && $byMin && $byMax;
}));

$items = array_map(function($p) use($BASE) {
  $id    = $p['id'] ?? $p['pid'] ?? null;
  $title = $p['title'] ?? ($p['name'] ?? '');
  $price = $p['price'] ?? null;
  $bed   = $p['bedrooms'] ?? null;
  return [
    'id'          => $id,
    'title'       => $title,
    'city'        => $p['city'] ?? null,
    'location'    => $p['location'] ?? null,
    'bedrooms'    => $bed,
    'price'       => $price,
    'details_url' => $id !== null ? $BASE . '/property.php?id=' . rawurlencode((string)$id) : null,
  ];
}, $filtered);

echo json_encode(['ok' => true, 'items' => $items], JSON_UNESCAPED_UNICODE);
