<?php
// Unified Admin (Builder or City) for GM HOMEZ — JSON storage
// Modes:
//   /admin/builder-projects.php?builder=Prestige Group   (Builder Admin)
//   /admin/builder-projects.php?city=Bangalore           (City Admin)

session_start();
error_reporting(E_ALL); ini_set('display_errors',1);

// ===== PATHS / BOOT =====
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($BASE==='/' || $BASE==='\\' || $BASE==='') $BASE='';
$ROOT       = realpath(__DIR__ . '/..');                  // .../GMHOMEZ-full-package
$DATA_DIR   = $ROOT . '/data';
$UPLOAD_DIR = $ROOT . '/uploads';
@is_dir($DATA_DIR)   || @mkdir($DATA_DIR,   0777, true);
@is_dir($UPLOAD_DIR) || @mkdir($UPLOAD_DIR, 0777, true);

// ===== MODE SELECTOR =====
$builder = isset($_GET['builder']) ? trim($_GET['builder']) : null;
$city    = isset($_GET['city'])    ? trim($_GET['city'])    : null;
$MODE = $city ? 'city' : 'builder';
$adminTitle = ($MODE === 'city') ? ("City Admin → " . $city) : ("Builder Admin → " . ($builder ?: 'Select'));

// ===== HELPERS =====
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES); }
function gm_slug($s){ $s=strtolower(trim($s)); $s=preg_replace('~[^a-z0-9]+~','-',$s); return trim($s,'-'); }
function gm_read_json($f){
  if(!is_file($f)) return [];
  $raw = file_get_contents($f);
  $j = json_decode($raw, true);
  return is_array($j) ? $j : [];
}
function gm_write_json_safe($f,$arr){
  // create backup and write atomically (avoid corrupting file)
  $dir = dirname($f);
  @is_dir($dir) || @mkdir($dir,0777,true);
  $backup = $f.'.backup-'.date('Ymd-His');
  if (is_file($f)) @copy($f, $backup);
  $tmp = $f.'.tmp';
  file_put_contents($tmp, json_encode(array_values($arr), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
  // sanity: ensure JSON we wrote can be read back
  $check = json_decode(file_get_contents($tmp), true);
  if (!is_array($check)) {
    @unlink($tmp);
    throw new RuntimeException('Refusing to overwrite properties.json with invalid data.');
  }
  @rename($tmp, $f);
}
// Allowed property types (single source of truth)
$GM_PROPERTY_TYPES = [
  'apartment' => 'Apartments',
  'villa'     => 'Villas',
  'plot'      => 'Plots',
  'studio'    => 'Studios',
  'senior'    => 'Senior Living',
];

// ===== LOAD DATA =====
$PROPS_PATH = $DATA_DIR.'/properties.json';
$props = gm_read_json($PROPS_PATH);

// ===== ACTIONS =====
if($_SERVER['REQUEST_METHOD']==='POST'){
  $act = $_POST['act'] ?? 'save';

  // ---------- DELETE ----------
  if ($act === 'delete') {
    $id = $_POST['id'] ?? '';
    $props = array_values(array_filter($props, fn($p)=>($p['id']??'') !== $id));
    gm_write_json_safe($PROPS_PATH,$props);
    header("Location: " . ($MODE==='city'
      ? "/builders.php?city="   . urlencode($city)
      : "/builders.php?builder=". urlencode($builder)
    ));
    exit;
  }

  // ---------- CREATE / UPDATE ----------
  $name          = trim($_POST['name'] ?? '');
  $location      = trim($_POST['location'] ?? '');
  $status        = trim($_POST['status'] ?? '');
  $possession_ym = trim($_POST['possession_ym'] ?? ''); // YYYY-MM
  $price_min     = ($_POST['price_min'] === '' ? null : (float)$_POST['price_min']);
  $price_max     = ($_POST['price_max'] === '' ? null : (float)$_POST['price_max']);
  $details_url   = trim($_POST['details_url'] ?? '');

  // exact location fields
  $address  = trim($_POST['address']  ?? '');
  $maps_url = trim($_POST['maps_url'] ?? '');
  $lat      = trim($_POST['lat']      ?? '');
  $lng      = trim($_POST['lng']      ?? '');

  // property type
  $ptype = strtolower(trim($_POST['property_type'] ?? 'apartment'));
  if (!array_key_exists($ptype, $GM_PROPERTY_TYPES)) $ptype = 'apartment';

  // Locked fields
  $builderField  = ($MODE === 'builder') ? $builder : trim($_POST['builder'] ?? '');
  $cityField     = ($MODE === 'city')    ? $city    : trim($_POST['city'] ?? '');

  // ID (stable)
  $id = $_POST['id'] ?? '';
  if ($id === '') {
    $key = ($MODE === 'builder' ? $builder : $city) . '-' . $name;
    $id  = gm_slug($key);
    if ($id === '') $id = 'p_' . uniqid();
  }

  // ----- UNITS (bhk, area_min, area_max, price_min, price_max [, img]) -----
  $units_csv = trim($_POST['units_csv'] ?? "");
  $units = [];
  foreach (preg_split('~\r?\n~', $units_csv) as $line) {
    $line = trim($line); if (!$line) continue;
    $parts = array_map('trim', explode(',', $line));
    if (count($parts) >= 5) {
      $row = [
        "bhk"       => (int)$parts[0],
        "area_min"  => (int)$parts[1],
        "area_max"  => (int)$parts[2],
        "price_min" => ($parts[3] === '' ? null : (float)$parts[3]),
        "price_max" => ($parts[4] === '' ? null : (float)$parts[4]),
      ];
      if (!empty($parts[5])) $row['img'] = $parts[5]; // optional 6th column: image url
      $units[] = $row;
    }
  }

  // ----- Unit Image URLs textarea (per line, matches order) -----
  $unit_urls_raw = trim($_POST['unit_image_urls'] ?? '');
  if ($unit_urls_raw !== '') {
    $urls = preg_split('~\r?\n~', $unit_urls_raw);
    foreach ($urls as $i => $uurl) {
      $uurl = trim($uurl);
      if ($uurl === '' || !isset($units[$i])) continue;
      $units[$i]['img'] = $uurl;
    }
  }

  // ----- Upload directories -----
  $scopeSlug = gm_slug(($MODE === 'builder' ? $builder : $city));
  $dir = $UPLOAD_DIR . '/projects/' . $scopeSlug . '/' . $id;
  @is_dir($dir) || @mkdir($dir, 0777, true);

  // ----- Upload Unit Images (match order to $units) -----
  $unitDir = $dir . '/units';
  @is_dir($unitDir) || @mkdir($unitDir, 0777, true);

  if (!empty($_FILES['unit_images']['name'][0])) {
    $k = 0;
    foreach ($_FILES['unit_images']['name'] as $i => $fn) {
      if (($_FILES['unit_images']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
      $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION) ?: 'jpg');
      $dst = $unitDir . '/' . uniqid('unit_') . '.' . $ext;
      if (@move_uploaded_file($_FILES['unit_images']['tmp_name'][$i], $dst)) {
        @chmod($dst, 0664);
        $web = '/uploads/projects/' . $scopeSlug . '/' . $id . '/units/' . basename($dst);
        if (isset($units[$k])) $units[$k]['img'] = $web;
        $k++;
      }
    }
  }

  // ----- GALLERY UPLOADS  (save as web paths so frontend can render) -----
  $gal = null; // null = keep existing; [] = empty; array = new gallery

  if (!empty($_FILES['gallery']['name'][0])) {
    $gal = [];
    foreach ($_FILES['gallery']['name'] as $i => $fn) {
      if (($_FILES['gallery']['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION) ?: 'jpg');
        $dst = $dir . '/' . uniqid('img_') . '.' . $ext;
        @move_uploaded_file($_FILES['gallery']['tmp_name'][$i], $dst);
        @chmod($dst, 0664);
        // web path:
        $web = '/uploads/projects/' . $scopeSlug . '/' . $id . '/' . basename($dst);
        $gal[] = $web;
      }
    }
  } elseif (isset($_POST['old_gallery'])) {
    // Keep/overwrite from textarea (one per line)
    $lines = array_map('trim', explode("\n", $_POST['old_gallery']));
    $gal = array_values(array_filter($lines, fn($x) => $x !== ''));
  }

  // ----- UPSERT -----
  $found = false;
  foreach ($props as &$p) {
    if (($p['id'] ?? '') === $id) {
      $p['id']            = $id;
      $p['builder']       = $builderField;
      $p['name']          = $name;
      $p['city']          = $cityField;
      $p['location']      = $location;
      $p['status']        = $status;
      $p['possession_ym'] = $possession_ym;
      $p['price_min']     = $price_min;
      $p['price_max']     = $price_max;
      $p['details_url']   = $details_url;
      $p['type']          = $ptype;

      // exact location
      $p['address']  = $address;
      $p['maps_url'] = $maps_url;
      $p['lat']      = $lat;
      $p['lng']      = $lng;

      if ($units)         $p['units']   = $units;
      if ($gal !== null) {
        $p['gallery'] = $gal;   // admin view
        $p['images']  = $gal;   // frontend cards expect "images"
      }
      $found = true; break;
    }
  }
  unset($p);

  if (!$found) {
    $props[] = [
      "id"            => $id,
      "builder"       => $builderField,
      "name"          => $name,
      "city"          => $cityField,
      "location"      => $location,
      "status"        => $status,
      "possession_ym" => $possession_ym,
      "price_min"     => $price_min,
      "price_max"     => $price_max,
      "details_url"   => $details_url,
      "type"          => $ptype,

      // exact location
      "address"       => $address,
      "maps_url"      => $maps_url,
      "lat"           => $lat,
      "lng"           => $lng,

      "gallery"       => ($gal ?? []),
      "images"        => ($gal ?? []),
      "units"         => $units
    ];
  }

  gm_write_json_safe($PROPS_PATH, $props);

  // Redirect back to the public page for same context
  header("Location: " . ($MODE==='city'
    ? "/builders.php?city="   . urlencode($city)
    : "/builders.php?builder=". urlencode($builder)
  ));
  exit;
}

// ===== LIST FOR CURRENT CONTEXT =====
if ($MODE === 'city') {
  if ($city !== null && $city !== '') {
    $list = array_values(array_filter(
      $props,
      fn($p) => isset($p['city']) && strcasecmp((string)($p['city'] ?? ''), (string)$city) === 0
    ));
  } else {
    $list = [];
  }
} else { // builder mode (or no params)
  if ($builder !== null && $builder !== '') {
    $list = array_values(array_filter(
      $props,
      fn($p) => isset($p['builder']) && strcasecmp((string)($p['builder'] ?? ''), (string)$builder) === 0
    ));
  } else {
    $list = [];
  }
}

// ===== EDITING =====
$editId = $_GET['edit'] ?? '';
$editing = null;
if($editId){
  foreach($props as $p){ if(($p['id']??'')===$editId){ $editing=$p; break; } }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=h($adminTitle)?></title>
<style>
  body{background:#0b1620;color:#e6f0f6;font-family:Inter,system-ui;margin:0}
  .wrap{max-width:1100px;margin:0 auto;padding:16px}
  a{color:#8ddfff;text-decoration:none}
  input,select,textarea{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(148,163,184,.2);background:#0f2430;color:#e6f0f6}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .btn{padding:10px 14px;border-radius:10px;border:1px solid rgba(148,163,184,.2);background:#102a37;color:#e6f0f6;cursor:pointer}
  .card{border:1px solid rgba(148,163,184,.2);border-radius:12px;padding:12px;margin:10px 0;background:#0f2430}
  .muted{color:#9fb2c0}
</style>
</head>
<body>
<div class="wrap">
  <h2><?=h($adminTitle)?></h2>
  <p>
    <?php if ($MODE==='city'): ?>
      <a href="/builders.php?city=<?=urlencode($city)?>">← Back to For Sale in <?=h($city)?></a>
    <?php else: ?>
      <a href="/builders.php?builder=<?=urlencode($builder)?>">← Back to <?=h($builder)?></a>
    <?php endif; ?>
     · <a href="/index.php">Home</a>
  </p>

  <h3><?= $editing ? 'Edit Project' : 'Add Project' ?></h3>
  <form method="post" enctype="multipart/form-data" class="card">
    <input type="hidden" name="act" value="save">
    <input type="hidden" name="id" value="<?=h($editing['id']??'')?>">

    <?php if ($MODE==='city'): ?>
      <input type="hidden" name="city" value="<?=h($city)?>">
      <div class="muted">City: <b><?=h($city)?></b> (locked)</div>
      <div class="row" style="margin-top:8px">
        <label>Builder (e.g., Prestige, Sobha)<input name="builder" required value="<?=h($editing['builder']??'')?>"></label>
        <label>Project Name<input name="name" required value="<?=h($editing['name']??'')?>"></label>
      </div>
    <?php else: ?>
      <input type="hidden" name="builder" value="<?=h($builder)?>">
      <div class="muted">Builder: <b><?=h($builder)?></b> (locked)</div>
      <div class="row" style="margin-top:8px">
        <label>Project Name<input name="name" required value="<?=h($editing['name']??'')?>"></label>
        <label>City<input name="city" required value="<?=h($editing['city']??'')?>"></label>
      </div>
    <?php endif; ?>

    <?php
      $currentType = strtolower($editing['type'] ?? 'apartment');
      if (!isset($GM_PROPERTY_TYPES[$currentType])) $currentType = 'apartment';
    ?>
    <label>Property Type
      <select name="property_type" required>
        <?php foreach ($GM_PROPERTY_TYPES as $slug => $label): ?>
          <option value="<?= $slug ?>" <?= $slug === $currentType ? 'selected' : '' ?>>
            <?= htmlspecialchars($label) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Location<input name="location" value="<?=h($editing['location']??'')?>"></label>

    <div class="row">
      <label>Status
        <?php $st=$editing['status']??''; ?>
        <select name="status">
          <?php foreach(['published','ongoing','ready','completed','hidden'] as $opt){ $sel=$st===$opt?'selected':''; echo "<option $sel>$opt</option>"; } ?>
        </select>
      </label>
      <label>Possession (YYYY-MM)<input name="possession_ym" placeholder="2028-08" value="<?=h($editing['possession_ym']??'')?>"></label>
    </div>

    <div class="row">
      <label>Exact Address (display)<input name="address" value="<?=h($editing['address']??'')?>"></label>
      <label>Google Maps Link (optional)<input name="maps_url" value="<?=h($editing['maps_url']??'')?>"></label>
    </div>
    <div class="row">
      <label>Latitude<input name="lat" type="text" value="<?=h($editing['lat']??'')?>"></label>
      <label>Longitude<input name="lng" type="text" value="<?=h($editing['lng']??'')?>"></label>
    </div>
    <div class="muted" style="margin-top:6px">
      (In Google Maps: right-click the site → “What’s here?” → copy latitude & longitude)
    </div>

    <div class="row" style="margin-top:12px">
      <label>Price Min (Lakhs)<input name="price_min" type="number" step="0.01" value="<?=h($editing['price_min']??'')?>"></label>
      <label>Price Max (Lakhs)<input name="price_max" type="number" step="0.01" value="<?=h($editing['price_max']??'')?>"></label>
    </div>

    <label>Details URL (optional)<input name="details_url" value="<?=h($editing['details_url']??'')?>"></label>

    <label>Units (one per line: <span class="muted">bhk,area_min,area_max,price_min,price_max[,image_url]</span>)</label>
    <textarea name="units_csv" rows="4"><?php
      if(!empty($editing['units'])){ foreach($editing['units'] as $u){
        echo ($u['bhk']??'').','.$u['area_min'].','.$u['area_max'].','.$u['price_min'].','.$u['price_max'];
        if (!empty($u['img'])) echo ','.$u['img'];
        echo "\n";
      } }
    ?></textarea>

    <div class="muted" style="margin:6px 0 12px">
      Tip: you can append a 6th value in each Units line for an image URL<br>
      or paste them below / upload files (order must match the Units list).
    </div>

    <label>Unit Image URLs (one per line, matches the Units order)</label>
    <textarea name="unit_image_urls" rows="3"><?php
      if (!empty($editing['units'])) {
        foreach ($editing['units'] as $u) {
          echo ($u['img'] ?? '') . "\n";
        }
      }
    ?></textarea>

    <label>Upload Unit Images (multiple; order must match Units)</label>
    <input type="file" name="unit_images[]" accept="image/*" multiple>

    <?php if(!empty($editing['gallery'])): ?>
      <label>Existing gallery (one path per line, or clear to replace)</label>
      <textarea name="old_gallery" rows="4"><?php foreach($editing['gallery'] as $g) echo $g."\n"; ?></textarea>
    <?php endif; ?>

    <label>Upload Gallery (multiple images allowed)<br>
      <input type="file" name="gallery[]" accept="image/*" multiple>
    </label>

    <div style="margin-top:10px;display:flex;gap:10px">
      <button class="btn" type="submit"><?= $editing ? 'Save Changes' : 'Create Project' ?></button>
      <?php if($editing): ?>
        <button class="btn" type="button" onclick="if(confirm('Delete this project?')){ delProject('<?=h($editing['id'])?>'); }">Delete</button>
      <?php endif; ?>
    </div>
  </form>

  <h3>Projects (<?=count($list)?>)</h3>
  <?php if(!$list): ?><div class="muted">No projects yet.</div><?php endif; ?>
  <?php foreach($list as $p): ?>
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <div>
          <b><?=h($p['name'])?></b>
          <?php if ($MODE==='city'): ?>
            — <span class="muted"><?=h($p['builder']??'')?></span>
          <?php else: ?>
            — <span class="muted"><?=h($p['city']??'')?></span>
          <?php endif; ?>
          · ₹ <?=h($p['price_min']??'')?>L – <?=h($p['price_max']??'')?>L
        </div>
        <div>
          <?php if ($MODE==='city'): ?>
            <a class="btn" href="<?=$BASE?>/builder-projects.php?city=<?=urlencode($city)?>&edit=<?=urlencode($p['id'])?>">Edit</a>
          <?php else: ?>
            <a class="btn" href="<?=$BASE?>/builder-projects.php?builder=<?=urlencode($builder)?>&edit=<?=urlencode($p['id'])?>">Edit</a>
          <?php endif; ?>
          <button class="btn" onclick="if(confirm('Delete this project?')) delProject('<?=h($p['id'])?>')">Delete</button>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<form id="delForm" method="post" style="display:none">
  <input type="hidden" name="act" value="delete">
  <input type="hidden" name="id" value="">
</form>
<script>
function delProject(id){ const f=document.getElementById('delForm'); f.id.value=id; f.submit(); }
</script>
</body>
</html>
