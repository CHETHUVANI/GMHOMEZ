<?php
require_once __DIR__.'/_common.php';
if ($_SERVER['REQUEST_METHOD']==='POST'){
  $pass=$_POST['password']??'';
  if ($pass===PG_ADMIN_PASSWORD){ $_SESSION['pg_ok']=true; header('Location: '.pg_url('index.php')); exit; }
  $err='Wrong password';
}
?>
<!doctype html><meta charset="utf-8"><title>Prestige Admin · Login</title>
<style>
body{background:#0b1620;color:#e6f0f6;font-family:system-ui;margin:0;display:grid;place-items:center;height:100vh}
.box{background:#0f2430;border:1px solid rgba(148,163,184,.18);padding:24px;border-radius:16px;min-width:320px}
input{width:100%;padding:12px;border-radius:10px;border:1px solid rgba(148,163,184,.18);background:#0b1c27;color:#e6f0f6}
.btn{padding:10px 14px;border-radius:12px;border:1px solid rgba(148,163,184,.18);background:#102a37;color:#e6f0f6;cursor:pointer;width:100%}
.err{color:#ff9b9b;margin-top:8px}
</style>
<div class="box">
  <h2 style="margin-top:0">Prestige Admin</h2>
  <form method="post">
    <input type="password" name="password" placeholder="Password" autofocus>
    <div style="height:10px"></div>
    <button class="btn">Enter</button>
    <?php if(!empty($err)):?><div class="err"><?=$err?></div><?php endif;?>
  </form>
</div>
