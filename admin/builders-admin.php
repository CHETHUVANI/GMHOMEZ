<?php
// Simple Builders Admin (JSON based)
session_start();
error_reporting(E_ALL); ini_set('display_errors', 1);

$ROOT = realpath(__DIR__ . '/..');           // project root
$DATA = $ROOT . '/data';
@is_dir($DATA) || @mkdir($DATA, 0777, true);
$FILE = $DATA . '/builders.json';

function gm_read_json($f){
  if (!is_file($f)) return [];
  $j = json_decode(@file_get_contents($f), true);
  return is_array($j) ? $j : [];
}
function gm_write_json($f, $arr){
  $tmp = $f.'.tmp';
  file_put_contents($tmp, json_encode(array_values($arr), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
  $check = json_decode(file_get_contents($tmp), true);
  if (!is_array($check)) { @unlink($tmp); throw new RuntimeException('Invalid JSON write'); }
  @rename($tmp, $f);
}

$builders = gm_read_json($FILE);

// Add builder
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['act'] ?? '')==='add'){
  $name = trim($_POST['name'] ?? '');
  $slug = trim($_POST['slug'] ?? '');
  if ($name === '') { $err = 'Name is required.'; }
  else {
    if ($slug === '') $slug = $name; // default behavior
    $builders[] = ['name'=>$name, 'slug'=>$slug];
    gm_write_json($FILE, $builders);
    header("Location: builders-admin.php?ok=1"); exit;
  }
}

// Delete builder (by index)
if (($_GET['del'] ?? '') !== ''){
  $i = (int)$_GET['del'];
  if (isset($builders[$i])){
    array_splice($builders, $i, 1);
    gm_write_json($FILE, $builders);
  }
  header("Location: builders-admin.php?ok=1"); exit;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Builders Admin</title>
<style>
  body{font-family:system-ui,Segoe UI,Roboto,Arial;background:#0b1620;color:#e6f0f6;margin:0}
  .wrap{max-width:900px;margin:0 auto;padding:20px}
  h1{margin:0 0 14px}
  .card{background:#0f2430;border:1px solid rgba(148,163,184,.2);border-radius:12px;padding:16px;margin:12px 0}
  label{display:block;margin:8px 0}
  input{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(148,163,184,.2);background:#0b1c27;color:#e6f0f6}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .btn{padding:10px 14px;border-radius:10px;border:1px solid rgba(148,163,184,.2);background:#102a37;color:#e6f0f6;cursor:pointer}
  table{width:100%;border-collapse:collapse}
  th,td{padding:10px;border-bottom:1px solid rgba(148,163,184,.15)}
  a.btn{display:inline-block;text-decoration:none}
  .muted{color:#9fb2c0}
</style>
</head>
<body>
<div class="wrap">
  <h1>Builders Admin</h1>
  <p class="muted">Add or remove builders. These will appear in the footer and anywhere you render from <code>data/builders.json</code>.</p>

  <div class="card">
    <h3 style="margin-top:0">Add Builder</h3>
    <?php if (!empty($err)): ?><div style="color:#ff9c9c;margin-bottom:10px"><?=$err?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="act" value="add">
      <div class="row">
        <label>Builder Name
          <input name="name" placeholder="e.g., Sun Group">
        </label>
        <label>Slug (for URL) <span class="muted">(defaults to name)</span>
          <input name="slug" placeholder="e.g., Sun Group">
        </label>
      </div>
      <div style="margin-top:10px"><button class="btn">Add Builder</button></div>
    </form>
  </div>

  <div class="card">
    <h3 style="margin-top:0">Current Builders (<?=count($builders)?>)</h3>
    <?php if (!$builders): ?>
      <div class="muted">No builders yet.</div>
    <?php else: ?>
      <table>
        <thead><tr><th style="width:40px">#</th><th>Name</th><th>Slug</th><th style="width:100px">Actions</th></tr></thead>
        <tbody>
        <?php foreach($builders as $i=>$b): ?>
          <tr>
            <td><?=$i+1?></td>
            <td><?=htmlspecialchars($b['name']??'',ENT_QUOTES)?></td>
            <td class="muted"><?=htmlspecialchars($b['slug']??'',ENT_QUOTES)?></td>
            <td><a class="btn" href="?del=<?=$i?>" onclick="return confirm('Delete this builder?')">Delete</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
