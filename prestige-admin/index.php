<?php
require_once __DIR__.'/_common.php'; pg_require_login();
$DATA = pg_read_json(__DIR__.'/../data/properties.json');
// Only Prestige projects
$ROWS = array_values(array_filter($DATA, fn($p)=>($p['builder']??'')===PG_BUILDER));
?>
<!doctype html><meta charset="utf-8"><title>Prestige Admin · Projects</title>
<style>
body{background:#0b1620;color:#e6f0f6;font-family:system-ui;margin:0}
.top{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#0c1b25;border-bottom:1px solid rgba(148,163,184,.18)}
.container{max-width:1100px;margin:18px auto;padding:0 16px}
a{color:#8ddfff;text-decoration:none}
.btn{padding:8px 12px;border-radius:10px;border:1px solid rgba(148,163,184,.18);background:#102a37;color:#e6f0f6;cursor:pointer}
.table{width:100%;border-collapse:collapse}
.table th,.table td{border-bottom:1px solid rgba(148,163,184,.18);padding:10px;text-align:left}
.badge{padding:3px 8px;border:1px solid rgba(148,163,184,.18);border-radius:999px;background:#0b1c27}
</style>

<div class="top">
  <div style="font-weight:700">Prestige Admin</div>
  <div style="flex:1"></div>
  <a class="btn" href="edit.php">+ Add Prestige Project</a>
  <a class="btn" href="logout.php">Logout</a>
</div>

<div class="container">
  <table class="table">
    <tr><th>Name</th><th>City</th><th>Price</th><th>Updated</th><th></th></tr>
    <?php foreach($ROWS as $p): ?>
      <tr>
        <td><?=$p['name']??''?><div class="badge"><?=$p['id']??''?></div></td>
        <td><?=$p['city']??''?></td>
        <td><?=($p['price_min']??'').' - '.($p['price_max']??'')?></td>
        <td><?=date('Y-m-d H:i', @filemtime(__DIR__.'/../data/properties.json'))?></td>
        <td>
          <a class="btn" href="edit.php?id=<?=urlencode($p['id']??'')?>">Edit</a>
          <a class="btn" href="../project.php?id=<?=urlencode($p['id']??'')?>" target="_blank">View</a>
          <a class="btn" style="background:#4a1f1f" href="remove.php?id=<?=urlencode($p['id']??'')?>" onclick="return confirm('Delete this project?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
