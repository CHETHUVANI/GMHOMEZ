<?php
// gm-homez/admin/index.php
declare(strict_types=1);
require_once __DIR__ . '/_auth.php';

// If not logged in → _auth sends you to login with redirect back here
require_login();

// After login, show a tiny dashboard with the two links
$BASE = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/\\');
if ($BASE === '/' || $BASE === '\\') $BASE = '';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin · GM HOMEZ</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    :root{--bg:#0b1620;--card:#0f2a37;--line:rgba(148,163,184,.12);--text:#e6f0f6;--muted:#9fb2c0}
    body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,system-ui,Segoe UI,Arial}
    .wrap{max-width:1100px;margin:28px auto;padding:0 16px}
    .top{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
    .card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:18px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
    .grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-top:12px}
    .tile{display:block;padding:18px;border-radius:14px;border:1px solid var(--line);background:#0b1620;color:var(--text);text-decoration:none}
    .tile:hover{filter:brightness(1.05)}
    .btn{padding:8px 12px;border-radius:10px;border:1px solid var(--line);background:#0b1620;color:var(--text);text-decoration:none}
    a{color:#8df;text-decoration:none}
    @media(max-width:700px){.grid{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <h1 style="margin:0">Admin Dashboard</h1>
      <div>
        <a class="btn" href="<?= $BASE ?>/index.php">← Site</a>
        <a class="btn" href="<?= $BASE ?>/admin/logout.php">Logout</a>
      </div>
    </div>

    <div class="card">
      <div class="grid">
        <a class="tile" href="<?= $BASE ?>/admin/properties.php">
          <h3 style="margin:0 0 6px">Manage Properties</h3>
          <div class="muted">Add, edit, remove listings.</div>
        </a>
        <a class="tile" href="<?= $BASE ?>/admin/team.php">
          <h3 style="margin:0 0 6px">Manage Team</h3>
          <div class="muted">Update team member cards & roles.</div>
        </a>
      </div>
    </div>
  </div>
</body>
</html>
