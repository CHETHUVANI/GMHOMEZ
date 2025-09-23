<?php
require_once __DIR__ . '/_auth.php';
require_login();

// admin/team.php
session_start();
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['gm_admin'])) { header('Location: login.php'); exit; }

// Load current data (TEAM_JSON is guaranteed non-empty + file exists by config.php)
$team = read_json(TEAM_JSON);

$act = $_POST['act'] ?? '';
if ($act === 'create' || $act === 'update') {
  $id    = $act === 'update' ? (int)($_POST['id'] ?? 0) : next_id($team);
  $name  = trim($_POST['name'] ?? '');
  $role  = trim($_POST['role'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $photo = null;

  // Handle photo upload (optional)
  if (!empty($_FILES['photo']['name'])) {
    $fn  = sanitize_file($_FILES['photo']['name']);
    $dst = UPLOADS_TEAM . '/' . $fn;
    if (is_uploaded_file($_FILES['photo']['tmp_name'])) {
      @move_uploaded_file($_FILES['photo']['tmp_name'], $dst);
      $photo = $fn;
    }
  }

  $entry = ['id'=>$id, 'name'=>$name, 'role'=>$role, 'phone'=>$phone];
  if ($photo) $entry['photo'] = $photo;

  $found = false;
  foreach ($team as &$t) {
    if ((int)($t['id'] ?? 0) === $id) { $t = array_merge($t, $entry); $found=true; break; }
  }
  if (!$found) $team[] = $entry;

  save_json(TEAM_JSON, $team);
  header('Location: team.php'); exit;
}

if ($act === 'delete') {
  $id = (int)($_POST['id'] ?? 0);
  $team = array_values(array_filter($team, fn($t)=>(int)($t['id'] ?? 0) !== $id));
  save_json(TEAM_JSON, $team);
  header('Location: team.php'); exit;
}

function img_url($t){
  $fn = $t['photo'] ?? '';
  return $fn ? url('uploads/team/' . $fn) : url('assets/back.png');
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin • Team</title>
<style>
body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,sans-serif;background:#0a0f1a;color:#e6f1ff;margin:20px}
h1{font-size:1.4rem;margin-bottom:10px}
.card{background:#0b1220;border-radius:12px;padding:12px;margin-top:18px;position:relative}
.g-bord{position:relative;border-radius:12px}
.g-bord::before{content:"";position:absolute;inset:0;padding:1px;border-radius:12px;background:linear-gradient(135deg,#ff4d6d,#6a11cb,#2575fc);-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;pointer-events:none}
.grid{display:grid;gap:12px}
.grid2{grid-template-columns:repeat(2,minmax(0,1fr))}
input,button{background:#0f172a;color:#e6f1ff;border:1px solid #1e293b;border-radius:10px;padding:.55rem .7rem;width:100%}
label{font-size:.9rem;color:#9fb2c0}
.btn{padding:.5rem .8rem;border-radius:10px;background:#0f172a;color:#e6f1ff;text-decoration:none;border:none;cursor:pointer}
.row{display:grid;grid-template-columns:60px 1fr 1fr 1fr 210px;gap:12px;align-items:center;background:#0b1220;border-radius:12px;padding:12px;margin:10px 0}
.row img{width:60px;height:60px;object-fit:cover;border-radius:10px}
form.inline{display:inline-flex;gap:6px;flex-wrap:wrap;align-items:center}
</style>
</head>
<body>
  <h1>Team Admin</h1>

  <div class="card g-bord">
    <h3 style="margin:6px 0 10px;">Add Team Member</h3>
    <form method="post" enctype="multipart/form-data" class="grid grid2">
      <input type="hidden" name="act" value="create">
      <div><label>Name<br><input name="name" required></label></div>
      <div><label>Role<br><input name="role" required></label></div>
      <div><label>Phone<br><input name="phone" placeholder="e.g. 9876543210"></label></div>
      <div><label>Photo<br><input type="file" name="photo" accept="image/*"></label></div>
      <div><button class="btn">Add Member</button></div>
    </form>
  </div>

  <div style="margin-top:16px">
    <?php foreach ($team as $t): ?>
      <div class="row g-bord">
        <img src="<?= img_url($t) ?>" alt="">
        <div><?= htmlspecialchars($t['name'] ?? '') ?></div>
        <div><?= htmlspecialchars($t['role'] ?? '') ?></div>
        <div><?= htmlspecialchars($t['phone'] ?? '') ?></div>
        <div style="display:flex;gap:8px;justify-content:flex-end">
          <!-- inline edit -->
          <form class="inline" method="post" enctype="multipart/form-data">
            <input type="hidden" name="act" value="update">
            <input type="hidden" name="id" value="<?= (int)($t['id'] ?? 0) ?>">
            <input name="name" value="<?= htmlspecialchars($t['name'] ?? '') ?>" placeholder="Name" required>
            <input name="role" value="<?= htmlspecialchars($t['role'] ?? '') ?>" placeholder="Role" required>
            <input name="phone" value="<?= htmlspecialchars($t['phone'] ?? '') ?>" placeholder="Phone">
            <input type="file" name="photo" accept="image/*">
            <button class="btn">Save</button>
          </form>
          <!-- delete -->
          <form class="inline" method="post" onsubmit="return confirm('Delete this member?')">
            <input type="hidden" name="act" value="delete">
            <input type="hidden" name="id" value="<?= (int)($t['id'] ?? 0) ?>">
            <button class="btn">Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div style="margin-top:24px">
    <a class="btn" href="<?= url('index.php') ?>">← Back to site</a>
  </div>
</body>
</html>
