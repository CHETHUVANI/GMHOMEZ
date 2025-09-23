<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) { echo json_encode(['success'=>false,'error'=>'Invalid JSON']); exit; }

foreach (['name','phone','email','password'] as $k) {
  if (empty($data[$k])) { echo json_encode(['success'=>false,'error'=>"Missing field: $k"]); exit; }
}

@mkdir(__DIR__ . '/../data', 0777, true);
$file = __DIR__ . '/../data/users.json';
$users = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

foreach ($users as $u) {
  if (strcasecmp($u['email'], $data['email']) === 0) {
    echo json_encode(['success'=>false,'error'=>'Email already registered']); exit;
  }
}

$data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
$data['created_at'] = date('c');
$users[] = $data;
file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));

echo json_encode(['success'=>true]);
