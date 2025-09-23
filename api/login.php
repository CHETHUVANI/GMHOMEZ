<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$raw = file_get_contents('php://input');
$cred = json_decode($raw, true);
if (!$cred) { echo json_encode(['success'=>false,'error'=>'Invalid JSON']); exit; }

foreach (['email','password'] as $k) {
  if (empty($cred[$k])) { echo json_encode(['success'=>false,'error'=>"Missing field: $k"]); exit; }
}

$file = __DIR__ . '/../data/users.json';
$users = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

foreach ($users as $u) {
  if (strcasecmp($u['email'], $cred['email']) === 0 && password_verify($cred['password'], $u['password'])) {
    echo json_encode(['success'=>true,'name'=>$u['name']]); exit;
  }
}
echo json_encode(['success'=>false,'error'=>'Invalid email or password']);
