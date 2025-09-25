<?php
require_once __DIR__ . '/_auth.php';
require_login();

error_reporting(E_ALL); ini_set('display_errors', 1);
require_once __DIR__ . '/../config.php';

/* Fallbacks if not defined in config.php */
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', __DIR__ . '/../uploads');
if (!defined('PROPS_JSON')) define('PROPS_JSON', __DIR__ . '/../data/properties.json');

/* Build UPLOAD_URL relative to app base */
if (!defined('UPLOAD_URL')) {
  $APP_BASE = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/\\');
  if ($APP_BASE === '/' || $APP_BASE === '\\') $APP_BASE = '';
  define('UPLOAD_URL', $APP_BASE . '/uploads');
}

/* Ensure folders exist */
@is_dir(dirname(PROPS_JSON)) || @mkdir(dirname(PROPS_JSON), 0777, true);
@is_dir(UPLOAD_DIR) || @mkdir(UPLOAD_DIR, 0777, true);

/* Helpers */
function load_props() {
  return file_exists(PROPS_JSON) ? (json_decode(file_get_contents(PROPS_JSON), true) ?: []) : [];
}
function save_props($arr) {
  file_put_contents(PROPS_JSON, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

/* Delete */
if (($_GET['action'] ?? '') === 'delete' && isset($_GET['id'])) {
  $id = (string)$_GET['id'];
  $props = load_props();
  $props = array_values(array_filter($props, fn($p) => (string)($p['id'] ?? '') !== $id));
  save_props($props);
  header('Location: properties.php?msg=Deleted'); exit;
}

/* Create */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title    = trim($_POST['title'] ?? '');
  $price    = (int)($_POST['price'] ?? 0);
  $location = trim($_POST['location'] ?? '');
  $beds     = (int)($_POST['beds'] ?? 0);
  $baths    = (int)($_POST['baths'] ?? 0);
  $area     = trim($_POST['area'] ?? '');
  $desc     = trim($_POST['description'] ?? '');

  $image  = '';      // cover image filename
  $images = [];      // gallery filenames

  /* Upload cover (optional) */
  if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
    $name = $_FILES['image']['name'] ?? 'upload';
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $safe = 'prop_' . bin2hex(random_bytes(4)) . ($ext ? ('.'.$ext) : '');
    $dest = rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . $safe;

    // Basic MIME gate (requires php_fileinfo)
    $ok = true;
    if (function_exists('finfo_open')) {
      $f = finfo_open(FILEINFO_MIME_TYPE);
      $mime = $f ? finfo_file($f, $_FILES['image']['tmp_name']) : null;
      if ($f) finfo_close($f);
      $ok = !$mime || preg_match('~^image/(jpeg|png|webp|gif)$~i', (string)$mime);
    }

    if ($ok && move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
      $image = $safe;
    }
  }

  /* Upload gallery images (optional) */
  if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
    $allowed = ['jpg','jpeg','png','webp','gif'];
    for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
      if (($_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;

      $name = $_FILES['images']['name'][$i] ?? 'upload';
      $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed)) continue;

      $safe = 'prop_' . bin2hex(random_bytes(5)) . ($ext ? ('.'.$ext) : '');
      $dest = rtrim(UPLOAD_DIR, '/\\') . DIRECTORY_SEPARATOR . $safe;

      if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $dest)) {
        $images[] = $safe; // store filename only; UI will prefix with UPLOAD_URL
      }
    }
  }

  /* Save */
  $props = load_props();
  $props[] = [
    'id'          => (string)(time() . rand(100,999)),
    'title'       => $title ?: 'Property',
    'price'       => $price,
    'location'    => $location,
    'beds'        => $beds,
    'baths'       => $baths,
    'area'        => $area,
    'description' => $desc,
    'image'       => $image,   // cover filename
    'images'      => $images   // gallery filenames
  ];
  save_props($props);
  header('Location: properties.php?msg=Saved'); exit;
}

$props = load_props();
$APP_BASE = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/\\');
if ($APP_BASE === '/' || $APP_BASE === '\\') $APP_BASE = '';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin · Properties</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,Segoe UI,Arial;background:#0b1620;color:#e6f0f6;margin:0}
    .wrap{max-width:1000px;margin:24px auto;padding:0 16px}
    .card{background:#0f2a37;border:1px solid rgba(148,163,184,.18);border-radius:14px;padding:16px;margin-bottom:14px}
    label{display:block;margin:8px 0 6px}
    input,textarea{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(148,163,184,.25);background:#0b1329;color:#e6f0f6}
    button{padding:10px 14px;border-radius:10px;border:0;background:#22d3ee;color:#062126;font-weight:700;cursor:pointer}
    table{width:100%;border-collapse:collapse;margin-top:10px}
    th,td{border-bottom:1px solid rgba(148,163,184,.18);padding:8px;text-align:left;vertical-align:middle}
    a.btn{display:inline-block;background:#22d3ee;color:#062126;padding:6px 10px;border-radius:8px;text-decoration:none}
    a.btn.danger{background:#ef4444;color:#fff}
    .muted{color:#9fb2c0}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    @media (max-width:700px){.row{grid-template-columns:1fr}}
    img.thumb{width:80px;height:60px;object-fit:cover;border-radius:8px;background:#123}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin:10px 0 16px}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="topbar">
      <h2>Admin · Properties</h2>
      <div>
        <a class="btn" href="<?= htmlspecialchars($APP_BASE) ?>/index.php">← Site</a>
      </div>
    </div>

    <?php if (!empty($_GET['msg'])): ?>
      <div class="card" style="border-color:#22d3ee;color:#b8fdff"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <div class="card">
      <h3>Add Property</h3>
      <form method="post" enctype="multipart/form-data">
        <div class="row">
          <div><label>Title</label><input name="title" required></div>
          <div><label>Price (₹)</label><input name="price" type="number" min="0" step="1"></div>
        </div>
        <div class="row">
          <div><label>Location</label><input name="location"></div>
          <div><label>Area (sq.ft)</label><input name="area"></div>
        </div>
        <div class="row">
          <div><label>Beds</label><input name="beds" type="number" min="0" step="1"></div>
          <div><label>Baths</label><input name="baths" type="number" min="0" step="1"></div>
        </div>
        <label>Description</label>
        <textarea name="description" rows="3"></textarea>

        <label>Image (optional)</label>
        <input type="file" name="image" accept="image/*">

        <label>Gallery Images (you can select many)</label>
        <input type="file" name="images[]" accept="image/*" multiple>

        <div style="margin-top:10px"><button type="submit">Save</button></div>
      </form>
    </div>

    <div class="card">
      <h3>Existing Properties</h3>
      <?php if (!$props): ?>
        <p class="muted">No properties yet.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Image</th><th>Title</th><th>Price</th><th>Location</th><th>Beds</th><th>Baths</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($props as $p):
              $cover = !empty($p['image']) ? (UPLOAD_URL . '/' . rawurlencode(basename($p['image']))) : '';
              $pid   = (string)($p['id'] ?? '');
            ?>
              <tr>
                <td><?php if ($cover): ?><img class="thumb" src="<?= htmlspecialchars($cover) ?>"><?php endif; ?></td>
                <td><?= htmlspecialchars($p['title'] ?? '') ?></td>
                <td><?= isset($p['price']) ? '₹ ' . number_format((int)$p['price']) : '' ?></td>
                <td><?= htmlspecialchars($p['location'] ?? '') ?></td>
                <td><?= (int)($p['beds'] ?? 0) ?></td>
                <td><?= (int)($p['baths'] ?? 0) ?></td>
                <td>
                  <a class="btn" href="property_edit.php?id=<?= urlencode($pid) ?>">Edit</a>
                  <a class="btn danger" href="?action=delete&id=<?= urlencode($pid) ?>" onclick="return confirm('Delete this property?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
    