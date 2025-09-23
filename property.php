<?php
// gm-homez/property.php
error_reporting(E_ALL); ini_set('display_errors', '1');

require_once __DIR__ . '/config.php';
if (file_exists(__DIR__ . '/lib/render.php')) require_once __DIR__ . '/lib/render.php';

// accept id or pid
$id = $_GET['id'] ?? $_GET['pid'] ?? null;
if ($id === null) {
  http_response_code(400);
  echo "Missing property id.";
  exit;
}

// resolve data file
$ROOT = __DIR__;
$DATA_DIR   = $DATA_DIR   ?? realpath($ROOT . '/data');
$PROPS_JSON = $PROPS_JSON ?? ($DATA_DIR ? $DATA_DIR . '/properties.json' : $ROOT . '/data/properties.json');

function read_props_fallback(string $jsonPath = null): array {
  if (!$jsonPath || !file_exists($jsonPath)) return [];
  $raw = file_get_contents($jsonPath);
  $arr = json_decode($raw, true);
  return is_array($arr) ? $arr : [];
}

// read properties
$all = function_exists('read_properties') ? read_properties() : read_props_fallback($PROPS_JSON);

// find match
$match = null;
foreach ($all as $p) {
  $pid = (string)($p['id'] ?? $p['pid'] ?? '');
  if ($pid !== '' && $pid === (string)$id) { $match = $p; break; }
}

if (!$match) {
  http_response_code(404);
  ?>
  <!doctype html>
  <meta charset="utf-8">
  <title>Property not found</title>
  <body style="font-family:system-ui;background:#0b1620;color:#e6f0f6;margin:0">
    <div style="max-width:720px;margin:48px auto;padding:24px;background:#0f2a37;border-radius:12px;border:1px solid rgba(148,163,184,.12)">
      Property not found.
      <a style="color:#8df" href="index.php">&larr; Back to home</a>
    </div>
  </body>
  <?php
  exit;
}

// If you have a dedicated renderer, use it
if (function_exists('renderPropertyDetails')) {
  echo renderPropertyDetails($match);
  exit;
}

// Fallback minimal details page
$title = $match['title'] ?? ($match['name'] ?? 'Property');
$location = trim(($match['location'] ?? '') . ((isset($match['city']) && $match['city']!=='') ? ', ' . $match['city'] : ''));
$img = $match['image'] ?? ($match['img'] ?? null);

// compute base to load /uploads/ images
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); if ($BASE === '\\' || $BASE === '/') $BASE = '';
$imgUrl = $img ? ($BASE . '/uploads/' . rawurlencode(basename($img))) : null;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($title) ?> · GM HOMEZ</title>
  <style>
    body{background:#0b1620;color:#e6f0f6;font-family:Inter,system-ui;margin:0}
    .wrap{max-width:1000px;margin:24px auto;padding:24px}
    .card{background:#0f2a37;border:1px solid rgba(148,163,184,.12);border-radius:16px;padding:20px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
    img{max-width:100%;border-radius:12px;display:block;margin:12px 0}
    a{color:#8df;text-decoration:none}
    a:hover{text-decoration:underline}
    .back{display:inline-block;margin-bottom:12px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    @media (max-width:800px){.grid{grid-template-columns:1fr}}
    .meta li{margin:6px 0}
  </style>
</head>
<body>
  <div class="wrap">
    <a class="back" href="index.php">&larr; Back</a>
    <div class="card">
      <h1 style="margin:0 0 8px"><?= htmlspecialchars($title) ?></h1>
      <?php if ($location): ?><p style="margin:0 0 10px"><?= htmlspecialchars($location) ?></p><?php endif; ?>
      <div class="grid">
        <div>
          <?php if ($imgUrl): ?><img src="<?= htmlspecialchars($imgUrl) ?>" alt="Property image"><?php endif; ?>
        </div>
        <div>
          <ul class="meta">
            <?php foreach (['price','bedrooms','bathrooms','area','status','type'] as $k): ?>
              <?php if (isset($match[$k])): ?>
                <li><strong><?= htmlspecialchars(ucfirst($k)) ?>:</strong> <?= htmlspecialchars((string)$match[$k]) ?></li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
