<?php
require_once __DIR__.'/lib/render.php';

$data = [
  'project'     => trim($_POST['project'] ?? ''),
  'stage'       => trim($_POST['stage'] ?? ''),
  'txn'         => trim($_POST['txn'] ?? ''),
  'source'      => trim($_POST['source'] ?? ''),
  'value'       => (float)($_POST['value'] ?? 0),
  'amount'      => (float)($_POST['amount'] ?? 0),
  'rate'        => (float)($_POST['rate'] ?? 0),
  'tenure'      => (int)($_POST['tenure'] ?? 0),
  'name'        => trim($_POST['name'] ?? ''),
  'city'        => trim($_POST['city'] ?? ''),
  'email'       => trim($_POST['email'] ?? ''),
  'phone'       => trim($_POST['phone'] ?? ''),
  'employment'  => trim($_POST['employment'] ?? ''),
  'income'      => (float)($_POST['income'] ?? 0),
  'down'        => (float)($_POST['down'] ?? 0),
  'ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
  'ts'          => date('c'),
];

$errors=[];
foreach (['project','stage','txn','source','amount','rate','tenure','name','email','phone'] as $k) {
  if (($data[$k] === '') || ($data[$k] === 0)) $errors[]=$k;
}
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[]='email';

$base = render_base_url();
if ($errors) {
  render_header('Home Loan — Error');
  echo '<div class="container" style="max-width:800px;margin:20px auto">';
  echo '<h2>Something needs your attention</h2>';
  echo '<p>Please go back and complete all required fields.</p>';
  echo '<a class="btn" style="display:inline-block;padding:10px 14px;border-radius:10px;background:#eee" href="'.h($base).'/home-loan.php">← Back to form</a>';
  echo '</div>';
  render_footer(); exit;
}

// save to data file
$dir = __DIR__.'/data';
$file = $dir.'/home_loans.json';
if (!is_dir($dir)) @mkdir($dir, 0775, true);
$all = [];
if (is_file($file)) { $raw=@file_get_contents($file); if($raw) $all=json_decode($raw,true)?:[]; }
$all[] = $data;
@file_put_contents($file, json_encode($all, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

// thank-you page
render_header('Thanks — Home Loan');
?>
<style>
  .thanks{max-width:760px;margin:28px auto;padding:24px;border-radius:16px;
    background:linear-gradient(#fff,#fff) padding-box,
               linear-gradient(135deg,#06b6d4,#7c3aed) border-box;
    border:2px solid transparent; box-shadow:0 14px 36px rgba(15,23,42,.06)}
</style>
<div class="thanks">
  <h2>Thanks! Your request is in.</h2>
  <p class="muted">Our loan specialist will reach out soon with tailored options and next steps.</p>
  <p><a href="<?= h($base) ?>/careers.php" style="text-decoration:underline">Return to Careers</a> ·
     <a href="<?= h($base) ?>/contact.php" style="text-decoration:underline">Contact support</a></p>
</div>
<?php render_footer(); ?>
