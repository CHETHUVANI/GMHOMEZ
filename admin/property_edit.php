<?php
// admin/property_edit.php — edit a property

session_start();

// ---- load config & helpers
$cfg = __DIR__ . '/../config.php';
if (is_file($cfg)) require_once $cfg;

if (!function_exists('h')) { function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); } }
if (!function_exists('json_load')) {
  function json_load($path){ if (!$path||!is_file($path)) return []; $d=json_decode(file_get_contents($path),true); return is_array($d)?$d:[]; }
}
if (!function_exists('json_save')) {
  function json_save($path,$data){ $tmp=$path.'.tmp'; file_put_contents($tmp,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)); rename($tmp,$path); return true; }
}

$PROPS = defined('PROPS_JSON') ? PROPS_JSON : (__DIR__ . '/../data/properties.json');
$UPLOAD_DIR = defined('UPLOAD_DIR') ? rtrim(UPLOAD_DIR,'/\\') : (__DIR__ . '/../uploads');
$UPLOAD_URL = defined('UPLOAD_URL') ? rtrim(UPLOAD_URL,'/') : ('/uploads');

$id = $_GET['id'] ?? '';
$props = json_load($PROPS);

// find index
$idx = -1;
foreach ($props as $i=>$p) if ((string)($p['id'] ?? '') === (string)$id) { $idx=$i; break; }
if ($idx < 0) { http_response_code(404); echo "Not found"; exit; }
$prop = $props[$idx];

// handle POST (save)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // basic fields
  $prop['title']       = trim($_POST['title'] ?? $prop['title'] ?? 'Property');
  $prop['location']    = trim($_POST['location'] ?? $prop['location'] ?? '');
  $prop['price']       = (string)($_POST['price'] ?? $prop['price'] ?? '');
  $prop['description'] = trim($_POST['description'] ?? $prop['description'] ?? '');
  $prop['beds']        = (string)($_POST['beds'] ?? $prop['beds'] ?? '');
  $prop['baths']       = (string)($_POST['baths'] ?? $prop['baths'] ?? '');
  $prop['area']        = (string)($_POST['area'] ?? $prop['area'] ?? '');

  // remove selected gallery images
  if (!empty($_POST['remove_images']) && is_array($_POST['remove_images'])) {
    rsort($_POST['remove_images']); // remove by index
    foreach ($_POST['remove_images'] as $ri) {
      $ri = (int)$ri;
      if (isset($prop['images'][$ri])) {
        $file = basename($prop['images'][$ri]);
        @unlink($UPLOAD_DIR . DIRECTORY_SEPARATOR . $file);
        array_splice($prop['images'], $ri, 1);
      }
    }
  }

  // upload a new cover (optional)
  if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    @mkdir($UPLOAD_DIR, 0775, true);
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
      $name = 'prop_' . bin2hex(random_bytes(4)) . '.' . $ext;
      if (move_uploaded_file($_FILES['image']['tmp_name'], $UPLOAD_DIR . DIRECTORY_SEPARATOR . $name)) {
        $prop['image'] = $name; // cover stores only filename (like earlier)
      }
    }
  }

  // upload new gallery images
  if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
    @mkdir($UPLOAD_DIR, 0775, true);
    $prop['images'] = $prop['images'] ?? [];
    $allowed = ['jpg','jpeg','png','webp','gif'];
    foreach ($_FILES['images']['name'] as $i => $n) {
      if (($_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
      $ext = strtolower(pathinfo($n, PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed)) continue;
      $fname = 'prop_' . bin2hex(random_bytes(5)) . '.' . $ext;
      if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $UPLOAD_DIR . DIRECTORY_SEPARATOR . $fname)) {
        $prop['images'][] = $fname;
      }
    }
  }

  // save back
  $props[$idx] = $prop;
  json_save($PROPS, $props);
  $_SESSION['msg'] = 'Property updated.';
  header('Location: property_edit.php?id=' . rawurlencode($id));
  exit;
}

// helper: turn stored filenames into URLs
$images = [];
if (!empty($prop['images']) && is_array($prop['images'])) {
  foreach ($prop['images'] as $fn) {
    $fn = basename($fn);
    if ($fn) $images[] = [$fn, $UPLOAD_URL . '/' . rawurlencode($fn)];
  }
}
$coverUrl = '';
if (!empty($prop['image'])) $coverUrl = $UPLOAD_URL . '/' . rawurlencode(basename($prop['image']));
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Property · <?=h($prop['title'] ?? 'Property')?></title>
<style>
  body{background:#0b1620;color:#e6f0f6;font-family:Inter,system-ui;margin:0}
  .wrap{max-width:1000px;margin:24px auto;padding:0 16px}
  .card{background:#0f2a37;border:1px solid rgba(148,163,184,.18);border-radius:16px;padding:18px}
  input,textarea{width:100%;background:#0b1620;color:#e6f0f6;border:1px solid #2a3b4a;border-radius:10px;padding:10px}
  label{display:block;margin:10px 0 6px}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  @media (max-width:900px){.row{grid-template-columns:1fr}}
  .thumbs{display:flex;flex-wrap:wrap;gap:8px}
  .thumbs img{width:120px;height:90px;object-fit:cover;border-radius:10px;border:1px solid #334155}
  .actions{display:flex;gap:10px;margin-top:14px}
  .btn{background:#111827;color:#e6f0f6;border:none;border-radius:12px;padding:10px 16px;cursor:pointer;text-decoration:none;display:inline-block}
</style>
</head>
<body>
<div class="wrap">
  <h2>Edit Property</h2>
  <?php if (!empty($_SESSION['msg'])){ echo '<div class="card" style="margin-bottom:10px">'.h($_SESSION['msg']).'</div>'; unset($_SESSION['msg']); } ?>
  <form class="card" method="post" enctype="multipart/form-data">
    <div class="row">
      <div>
        <label>Title</label>
        <input name="title" value="<?=h($prop['title'] ?? '')?>">
      </div>
      <div>
        <label>Location</label>
        <input name="location" value="<?=h($prop['location'] ?? '')?>">
      </div>
      <div>
        <label>Price (number)</label>
        <input name="price" value="<?=h($prop['price'] ?? '')?>">
      </div>
      <div>
        <label>Beds</label>
        <input name="beds" value="<?=h($prop['beds'] ?? '')?>">
      </div>
      <div>
        <label>Baths</label>
        <input name="baths" value="<?=h($prop['baths'] ?? '')?>">
      </div>
      <div>
        <label>Area (sqft)</label>
        <input name="area" value="<?=h($prop['area'] ?? '')?>">
      </div>
    </div>

    <label>Description</label>
    <textarea name="description" rows="5"><?=h($prop['description'] ?? '')?></textarea>

    <hr style="border-color:#233">

    <label>Cover Image (optional – replaces current)</label>
    <?php if ($coverUrl): ?><div class="thumbs" style="margin:6px 0 10px"><img src="<?=h($coverUrl)?>"></div><?php endif; ?>
    <input type="file" name="image" accept="image/*">

    <label style="margin-top:14px">Gallery Images (add more)</label>
    <input type="file" name="images[]" accept="image/*" multiple>

    <?php if ($images): ?>
      <div style="margin-top:10px">
        <div>Existing Gallery (tick to remove)</div>
        <div class="thumbs" style="margin-top:6px">
          <?php foreach ($images as $i=>$pair): [$fn,$url] = $pair; ?>
            <label style="display:inline-flex;flex-direction:column;align-items:center;gap:6px">
              <img src="<?=h($url)?>">
              <input type="checkbox" name="remove_images[]" value="<?=$i?>"> remove
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="actions">
      <button class="btn" type="submit">Save Changes</button>
      <a class="btn" href="./properties.php">Back</a>
    </div>
  </form>
</div>
</body>
</html>
