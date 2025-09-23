<?php
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); if ($BASE==='/'||$BASE==='\\') $BASE='';
@include __DIR__ . '/../config.php';
@include __DIR__ . '/../lib/render.php';

/* If your project already has read_properties(), we’ll use it.
   Otherwise we fall back to data/properties.json. */
if (!function_exists('read_properties')) {
  function read_properties() {
    $file = __DIR__ . '/../data/properties.json';
    if (!is_file($file)) return [];
    $j = json_decode(file_get_contents($file), true);
    return is_array($j) ? $j : [];
  }
}

if (!isset($CITY) || !$CITY) { http_response_code(404); echo 'Missing $CITY'; exit; }

$all = read_properties();

/* IMPORTANT:
   This filters across every builder. We only check the 'city' field.
   So make sure each property (no matter which builder admin created it)
   has:  { "city": "Bangalore", "builder": "Sobha", ... }
*/
$props = array_values(array_filter($all, function($p){ 
  global $CITY;
  return isset($p['city']) && strcasecmp($p['city'], $CITY) === 0;
}));

function _render_card($p){
  $name = htmlspecialchars($p['name'] ?? ($p['project_name'] ?? 'Project'));
  $img  = htmlspecialchars($p['image'] ?? '');
  $bhk  = htmlspecialchars($p['bhk'] ?? '');
  $builder = htmlspecialchars($p['builder'] ?? '');
  $slug = $p['slug'] ?? null;
  echo '<article class="card" style="background:#0f2a37;border-radius:14px;overflow:hidden">';
  if ($img) echo '<img src="'.$img.'" alt="" style="width:100%;height:180px;object-fit:cover;background:#061018">';
  echo '<div style="padding:14px">';
  echo '<h3 style="margin:0 0 6px;font-weight:700">'.$name.'</h3>';
  if ($builder) echo '<span style="display:inline-block;margin:4px 6px 0 0;padding:6px 10px;border-radius:999px;background:#0b2330;font-size:12px;color:#9fb2c0">'.$builder.'</span>';
  if ($bhk) echo '<span style="display:inline-block;margin:4px 6px 0 0;padding:6px 10px;border-radius:999px;background:#0b2330;font-size:12px;color:#9fb2c0">'.$bhk.' BHK</span>';
  if ($slug) echo '<div><a href="'.$GLOBALS['BASE'].'/project.php?slug='.urlencode($slug).'" style="display:inline-block;margin-top:8px;padding:10px 14px;border-radius:10px;background:#113245;color:#cfe8f5;text-decoration:none">View Details</a></div>';
  echo '</div></article>';
}
?><!doctype html>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Buy Property in <?= htmlspecialchars($CITY) ?> · GM HOMEZ</title>
<div style="max-width:1200px;margin:0 auto;padding:20px;font-family:system-ui;background:#07121b;color:#e6f0f6">
  <div style="position:sticky;top:0;background:#07121b;padding:10px 0;border-bottom:1px solid rgba(148,163,184,.12);margin-bottom:12px">
    <a href="<?= $BASE ?>/" style="display:inline-block;padding:8px 12px;border-radius:10px;background:#113245;color:#cfe8f5;text-decoration:none">← Back to Home</a>
  </div>
  <h1 style="margin:0 0 6px">Buy Property in <?= htmlspecialchars($CITY) ?></h1>
  <p style="color:#9fb2c0;margin:0 0 16px">All builders · All projects in this city.</p>

  <?php if (!$props): ?>
    <p style="color:#9fb2c0">No properties found for <?= htmlspecialchars($CITY) ?> yet.</p>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px">
      <?php foreach ($props as $p) { _render_card($p); } ?>
    </div>
  <?php endif; ?>
</div>
