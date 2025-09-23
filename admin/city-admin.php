<?php
// admin/city-admin.php — city-scoped properties admin (JSON storage)
// Security note: this is a lightweight demo admin. Protect this route behind your auth.
error_reporting(E_ALL); ini_set('display_errors', 1);
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); if ($BASE==='/'||$BASE==='\\') $BASE='';

$CITY = $_GET['city'] ?? null;
if (!$CITY) { http_response_code(400); echo "Missing ?city=..."; exit; }

$DATA_FILE = __DIR__ . '/../data/properties.json';

// ---------- storage helpers ----------
function load_all($file){
  if (!is_file($file)) return [];
  $j = json_decode(file_get_contents($file), true);
  return is_array($j) ? $j : [];
}
function save_all($file, $arr){
  file_put_contents($file, json_encode(array_values($arr), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
}

// ---------- actions ----------
$all = load_all($DATA_FILE);

// id = numeric or slug-like; we’ll use uniqid if not provided
function next_id(){ return uniqid("p_", true); }

$action = $_POST['action'] ?? $_GET['action'] ?? '';
if ($action === 'create' && $_SERVER['REQUEST_METHOD']==='POST') {
  // build new property (city is forced)
  $p = [
    'id'         => next_id(),
    'name'       => trim($_POST['name'] ?? ''),
    'builder'    => trim($_POST['builder'] ?? ''),
    'city'       => $CITY,
    'location'   => trim($_POST['location'] ?? ''),
    'bhk'        => trim($_POST['bhk'] ?? ''),
    'price_min'  => ($_POST['price_min'] === '' ? null : (float)$_POST['price_min']),
    'price_max'  => ($_POST['price_max'] === '' ? null : (float)$_POST['price_max']),
    'area_min'   => ($_POST['area_min'] === '' ? null : (int)$_POST['area_min']),
    'area_max'   => ($_POST['area_max'] === '' ? null : (int)$_POST['area_max']),
    'possession_ym' => trim($_POST['possession_ym'] ?? ''), // yyyy-mm
    'status'     => trim($_POST['status'] ?? 'published'),
    'image'      => trim($_POST['image'] ?? ''),             // cover image
    'gallery'    => array_values(array_filter(array_map('trim', explode("\n", $_POST['gallery'] ?? '')))),
    'slug'       => trim($_POST['slug'] ?? ''),
    'details_url'=> trim($_POST['details_url'] ?? ''),
  ];
  $all[] = $p;
  save_all($DATA_FILE, $all);
  header("Location: {$BASE}/admin/city-admin.php?city=" . urlencode($CITY)); exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD']==='POST') {
  $id = $_POST['id'] ?? '';
  foreach ($all as &$p) {
    if (($p['id'] ?? '') === $id) {
      // keep city locked
      $p['name']       = trim($_POST['name'] ?? $p['name']);
      $p['builder']    = trim($_POST['builder'] ?? $p['builder']);
      $p['location']   = trim($_POST['location'] ?? ($p['location'] ?? ''));
      $p['bhk']        = trim($_POST['bhk'] ?? ($p['bhk'] ?? ''));
      $p['price_min']  = ($_POST['price_min'] === '' ? null : (float)$_POST['price_min']);
      $p['price_max']  = ($_POST['price_max'] === '' ? null : (float)$_POST['price_max']);
      $p['area_min']   = ($_POST['area_min'] === '' ? null : (int)$_POST['area_min']);
      $p['area_max']   = ($_POST['area_max'] === '' ? null : (int)$_POST['area_max']);
      $p['possession_ym'] = trim($_POST['possession_ym'] ?? ($p['possession_ym'] ?? ''));
      $p['status']     = trim($_POST['status'] ?? ($p['status'] ?? 'published'));
      $p['image']      = trim($_POST['image'] ?? ($p['image'] ?? ''));
      $p['gallery']    = array_values(array_filter(array_map('trim', explode("\n", $_POST['gallery'] ?? ''))));
      $p['slug']       = trim($_POST['slug'] ?? ($p['slug'] ?? ''));
      $p['details_url']= trim($_POST['details_url'] ?? ($p['details_url'] ?? ''));
      break;
    }
  }
  unset($p);
  save_all($DATA_FILE, $all);
  header("Location: {$BASE}/admin/city-admin.php?city=" . urlencode($CITY)); exit;
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD']==='POST') {
  $id = $_POST['id'] ?? '';
  $all = array_values(array_filter($all, fn($p)=>($p['id'] ?? '') !== $id));
  save_all($DATA_FILE, $all);
  header("Location: {$BASE}/admin/city-admin.php?city=" . urlencode($CITY)); exit;
}

// Filter by city for view
$list = array_values(array_filter($all, fn($p)=>isset($p['city']) && strcasecmp($p['city'], $CITY)===0));
usort($list, fn($a,$b)=>strcasecmp($a['name']??'', $b['name']??''));

function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES); }
?>
<!doctype html>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>City Admin — <?=h($CITY)?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<style>
  :root{--bg:#07121b;--panel:#0f2a37;--line:rgba(148,163,184,.18);--muted:#9fb2c0;}
  *{box-sizing:border-box} body{margin:0;background:var(--bg);color:#e6f0f6;font-family:Inter,system-ui}
  .wrap{max-width:1100px;margin:0 auto;padding:18px}
  .row{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
  .btn{display:inline-block;padding:8px 12px;border-radius:10px;background:#123248;color:#cfe8f5;text-decoration:none;border:1px solid var(--line)}
  h1{margin:4px 0 12px}
  table{width:100%;border-collapse:collapse;background:var(--panel);border:1px solid var(--line);border-radius:12px;overflow:hidden}
  th,td{padding:10px;border-bottom:1px solid var(--line);vertical-align:top}
  th{background:#0c1b25;text-align:left}
  tr:last-child td{border-bottom:none}
  input,textarea,select{width:100%;background:#0b1c27;color:#e6f0f6;border:1px solid var(--line);border-radius:10px;padding:8px}
  .muted{color:var(--muted)}
  .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  @media(max-width:900px){.grid{grid-template-columns:1fr}}
</style>

<div class="wrap">
  <div class="row">
    <h1 style="margin-right:auto">City Admin → <?=h($CITY)?></h1>
    <a class="btn" href="<?=$BASE?>/index.php">← Back to Site</a>
  </div>

  <!-- Add New -->
  <details open>
    <summary class="btn" style="display:inline-block;margin:10px 0">+ Add New Property in <?=h($CITY)?></summary>
    <form method="post" style="margin-top:10px">
      <input type="hidden" name="action" value="create">
      <div class="grid">
        <div>
          <label>Project Name<input required name="name"></label>
          <label style="margin-top:8px">Builder (e.g., Prestige, Sobha)<input name="builder"></label>
          <label style="margin-top:8px">Location<input name="location"></label>
          <label style="margin-top:8px">BHK (e.g., 2-3)<input name="bhk"></label>
          <label style="margin-top:8px">Status
            <select name="status"><option>published</option><option>ongoing</option><option>ready</option><option>hidden</option></select>
          </label>
        </div>
        <div>
          <label>Price Min (Lacs)<input name="price_min" type="number" step="0.01"></label>
          <label style="margin-top:8px">Price Max (Lacs)<input name="price_max" type="number" step="0.01"></label>
          <label style="margin-top:8px">Area Min (sqft)<input name="area_min" type="number"></label>
          <label style="margin-top:8px">Area Max (sqft)<input name="area_max" type="number"></label>
          <label style="margin-top:8px">Possession (YYYY-MM)<input name="possession_ym" placeholder="2027-06"></label>
        </div>
      </div>
      <div class="grid" style="margin-top:8px">
        <div><label>Cover Image URL<input name="image" placeholder="uploads/properties/cover.jpg"></label></div>
        <div><label>Gallery (one URL per line)<textarea name="gallery" rows="4" placeholder="uploads/properties/p1.jpg&#10;uploads/properties/p2.jpg"></textarea></label></div>
      </div>
      <div class="grid" style="margin-top:8px">
        <div><label>Slug<input name="slug" placeholder="project-slug"></label></div>
        <div><label>Details URL (optional)<input name="details_url" placeholder="<?=$BASE?>/project.php?slug=..."></label></div>
      </div>
      <div style="margin-top:10px"><button class="btn">Save</button> <span class="muted">City is locked to <?=h($CITY)?>.</span></div>
    </form>
  </details>

  <!-- List / Edit -->
  <h3 style="margin:18px 0 8px">Projects in <?=h($CITY)?> (<?=count($list)?>)</h3>
  <?php if (!$list): ?>
    <div class="muted">No properties yet in this city.</div>
  <?php else: ?>
    <table>
      <tr><th>Name</th><th>Builder</th><th>Price</th><th>Area</th><th>Actions</th></tr>
      <?php foreach ($list as $p): ?>
        <tr>
          <td>
            <strong><?=h($p['name']??'')?></strong>
            <div class="muted"><?=h($p['location']??'')?> · <?=h($p['bhk']??'')?> BHK</div>
          </td>
          <td><?=h($p['builder']??'')?></td>
          <td><?=h(($p['price_min']??'').' - '.($p['price_max']??''))?> L</td>
          <td><?=h(($p['area_min']??'').' - '.($p['area_max']??''))?> sqft</td>
          <td style="width:260px">
            <!-- inline editor -->
            <details>
              <summary class="btn">Edit</summary>
              <form method="post" style="margin-top:8px">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?=h($p['id']??'')?>">
                <div class="grid">
                  <div>
                    <label>Name<input name="name" value="<?=h($p['name']??'')?>"></label>
                    <label style="margin-top:6px">Builder<input name="builder" value="<?=h($p['builder']??'')?>"></label>
                    <label style="margin-top:6px">Location<input name="location" value="<?=h($p['location']??'')?>"></label>
                    <label style="margin-top:6px">BHK<input name="bhk" value="<?=h($p['bhk']??'')?>"></label>
                    <label style="margin-top:6px">Status
                      <select name="status">
                        <?php foreach (['published','ongoing','ready','hidden'] as $s): ?>
                          <option <?=$s===($p['status']??'published')?'selected':''?>><?=$s?></option>
                        <?php endforeach; ?>
                      </select>
                    </label>
                  </div>
                  <div>
                    <label>Price Min (L)<input name="price_min" type="number" step="0.01" value="<?=h($p['price_min']??'')?>"></label>
                    <label style="margin-top:6px">Price Max (L)<input name="price_max" type="number" step="0.01" value="<?=h($p['price_max']??'')?>"></label>
                    <label style="margin-top:6px">Area Min (sqft)<input name="area_min" type="number" value="<?=h($p['area_min']??'')?>"></label>
                    <label style="margin-top:6px">Area Max (sqft)<input name="area_max" type="number" value="<?=h($p['area_max']??'')?>"></label>
                    <label style="margin-top:6px">Possession (YYYY-MM)<input name="possession_ym" value="<?=h($p['possession_ym']??'')?>"></label>
                  </div>
                </div>
                <div class="grid" style="margin-top:6px">
                  <div><label>Cover Image URL<input name="image" value="<?=h($p['image']??'')?>"></label></div>
                  <div><label>Gallery (one URL per line)
                    <textarea name="gallery" rows="3"><?=h(implode("\n", (array)($p['gallery']??[])))?></textarea>
                  </label></div>
                </div>
                <div class="grid" style="margin-top:6px">
                  <div><label>Slug<input name="slug" value="<?=h($p['slug']??'')?>"></label></div>
                  <div><label>Details URL<input name="details_url" value="<?=h($p['details_url']??'')?>"></label></div>
                </div>
                <div style="margin-top:8px"><button class="btn">Update</button></div>
              </form>
            </details>
            <!-- delete -->
            <form method="post" onsubmit="return confirm('Delete this property?')" style="display:inline">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?=h($p['id']??'')?>">
              <button class="btn" style="background:#3a1b1b;border-color:#6a2a2a;margin-top:6px">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</div>
