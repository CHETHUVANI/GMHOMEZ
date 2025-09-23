<?php
// gm-homez/admin/login.php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config.php';

$BASE = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/\\');
if ($BASE === '/' || $BASE === '\\') $BASE = '';

$redirect = $_GET['redirect'] ?? ($BASE . '/admin/index.php');
$err = $_SESSION['error'] ?? ''; unset($_SESSION['error']);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Login · GM HOMEZ</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{--bg:#0b1620;--card:#0f2a37;--line:rgba(148,163,184,.12);--text:#e6f0f6;--muted:#9fb2c0;--accent:#0ea5e9}
    body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,system-ui,Segoe UI,Arial;display:grid;place-items:center;min-height:100vh}
    .box{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:22px;min-width:320px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
    label{display:block;margin:10px 0 6px}
    input{width:100%;padding:10px 12px;border-radius:10px;border:1px solid var(--line);background:#0b1620;color:var(--text)}
    .row{display:flex;gap:10px;margin-top:12px}
    .btn{flex:1;display:inline-block;text-align:center;padding:10px 12px;border-radius:10px;border:1px solid var(--line);background:#0b1620;color:var(--text);text-decoration:none}
    .btn.primary{background:linear-gradient(135deg,#0ea5e9,#22d3ee);color:#fff;border:0}
    .err{color:#ffb4b4;margin-bottom:8px}
    a{color:#8df;text-decoration:none}
  </style>
</head>
<body>
  <form class="box" method="post" action="login_post.php" autocomplete="off">
    <h2 style="margin:0 0 10px">GM HOMEZ — Admin</h2>
    <?php if($err): ?><div class="err"><?= htmlspecialchars($err) ?></div><?php endif; ?>
    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
    <label>Username</label>
    <input name="username" required autofocus>
    <label>Password</label>
    <input name="password" type="password" required>
    <div class="row">
      <a class="btn" href="<?= $BASE ?>/index.php">← Back</a>
      <button class="btn primary" type="submit">Login</button>
    </div>
  </form>
</body>
</html>
