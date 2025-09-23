<?php
// /admin/builders.php
session_start();
error_reporting(E_ALL); ini_set('display_errors', 1);

// --- Simple auth (change the password!) ---------------------
$ADMIN_PASS = 'change-this-password';
if (isset($_POST['do_login'])) {
  if (hash_equals($ADMIN_PASS, $_POST['password'] ?? '')) {
    $_SESSION['is_admin'] = true;
  } else {
    $err = "Wrong password";
  }
}
if (isset($_GET['logout'])) { $_SESSION['is_admin'] = false; session_destroy(); header("Location: builders.php"); exit; }
if (empty($_SESSION['is_admin'])) {
  ?><!doctype html><meta charset="utf-8">
  <title>Builders Admin</title>
  <style>body{font-family:system-ui;background:#0b1620;color:#e6f0f6;display:grid;place-items:center;height:100vh}form{background:#0f2430;border:1px solid rgba(148,163,184,.2);padding:20px;border-radius:12px}input{padding:10px;border-radius:10px;border:1px solid rgba(148,163,184,.2);background:#0b1c27;color:#e6f0f6}button{padding:10px 14px;border-radius:10px;border:1px solid rgba(148,163,184,.2);background:#102a37;color:#e6f0f6;cursor:pointer}</style>
  <form method="post">
    <h3>Builders Admin – Login</h3>
    <?php if(!empty($err)) echo "<div style='color:#ff9'>$err</div>"; ?>
    <input type="hidden" name="do_login" value="1">
    <label>Password<br><input name="password" type="password" autofocus></label>
    <div style="margin-top:10px"><button>Login</button></div>
  </form><?php
  exit;
}

// --- Helpers -------------------------------------------------
function gm_slug($s){ $s=strtolower(trim($s)); $s=preg_replace('~[^a-z0-9]+~','-',$s); return trim($s,'-'); }
function gm_read_json($f){ if(!is_file($f)) return []; $j=json_decode(@file_get_contents($f),true); return is_array($j)?$j:[]; }
function gm_write_json_safe($f,$arr){
  $dir = dirname($f); @is_dir($dir) || @mkdir($dir, 0777, true);
  $tmp = $f . '.tmp';
  file_put_contents($tmp, json_encode(array_values($arr), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
  $check = json_decode(file_get_contents($tmp), true);
  if (!is_array($check)) { @unlink($tmp); throw new RuntimeException('Invalid JSON write'); }
  @rename($tmp, $f);
}

$ROOT = realpath(__DIR__ . '/..');           // project root
$FILE = $ROOT . '/data/builders.json';
$builders = gm_read_json($FILE);

// --- Actions -------------------------------------------------
$act = $_POST['act'] ?? '';
if ($act === 'add') {
  $name = trim($_POST['name'] ?? '');
  $slug = trim($_POST['slug'] ?? '');
  if ($name !== '') {
    if ($slug === '') $slug = gm_slug($name);
    // prevent duplicates
    $exists = false;
    foreach ($builders as $b) {
      if (strcasecmp($b['name']??'', $name)===0 || strcasecmp($b['slug']??'', $slug)===0) { $exists=true; break; }
    }
    if (!$exists) {
      $builders[] = ['name'=>$name, 'slug'=>$slug];
      gm_write_json_safe($FILE, $builders);
      header("Location: builders.php?saved=1"); exit;
    } else { $msg = "Builder already exists."; }
  } else { $msg = "Name is required."; }
}
if ($act === 'delete') {
  $slug = $_POST['slug'] ?? '';
  $builders = array_values(array_filter($builders, fn($b)=>($b['slug']??'')!==$slug));
  gm_write_json_safe($FILE, $builders);
  header("Location: builders.php?deleted=1"); exit;
}

// order by name
usort($builders, fn($a,$b)=>strcasecmp($a['name']??'', $b['name']??''));
?>
<!doctype html>
<meta charset="utf-8">
<title>Builders Admin</title>
<style>
  body{background:#0b1620;color:#e6f0f6;font-family:Inter,system-ui;margin:0}
  .wrap{max-width:900px;margin:30px auto;padding:16px}
  .card{background:#0f2430;border:1px solid rgba(148,163,184,.2);border-radius:14px;padding:16px;margin:12px 0}
  input{padding:10px;border-radius:10px;border:1px solid rgba(148,163,184,.2);background:#0b1c27;color:#e6f0f6;width:100%}
  .row{display:grid;grid-template-columns:1fr 1fr auto;gap:10px}
  .btn{padding:10px 14px;border-radius:10px;border:1px solid rgba(148,163,184,.2);background:#102a37;color:#e6f0f6;cursor:pointer}
  a{color:#8ddfff;text-decoration:none}
  table{width:100%;border-collapse:collapse}
  th,td{padding:10px;border-bottom:1px solid rgba(148,163,184,.15)}
</style>
<div class="wrap">
  <div style="display:flex;justify-content:space-between;align-items:center">
    <h2>Builders Admin</h2>
    <a class="btn" href="?logout=1">Logout</a>
  </div>

  <?php if(!empty($msg)) echo "<div class='card' style='color:#ff9'>$msg</div>"; ?>

  <div class="card">
    <form method="post" class="row">
      <input type="hidden" name="act" value="add">
      <label>Name<br><input name="name" placeholder="e.g., Sun Group"></label>
      <label>Slug (optional)<br><input name="slug" placeholder="auto-generated-from-name"></label>
      <div style="align-self:end"><button class="btn">Add Builder</button></div>
    </form>
  </div>

  <div class="card">
    <h3 style="margin-top:0">Current Builders (<?=count($builders)?>)</h3>
    <?php if(!$builders): ?>
      <div class="muted">No builders yet.</div>
    <?php else: ?>
      <table>
        <tr><th>Name</th><th>Slug</th><th style="width:1%"></th></tr>
        <?php foreach($builders as $b): ?>
          <tr>
            <td><?=htmlspecialchars($b['name']??'',ENT_QUOTES)?></td>
            <td><?=htmlspecialchars($b['slug']??'',ENT_QUOTES)?></td>
            <td>
              <form method="post" onsubmit="return confirm('Delete this builder?')">
                <input type="hidden" name="act" value="delete">
                <input type="hidden" name="slug" value="<?=htmlspecialchars($b['slug']??'',ENT_QUOTES)?>">
                <button class="btn">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>

  <div class="card">
    <b>Tip:</b> After adding a builder here, you can create projects for it from your existing
    builder/city admin pages. Public links look like:
    <code>/builders.php?builder=&lt;slug&gt;</code>
  </div>
</div>
