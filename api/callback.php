<?php
// simple JSON logger for "Get Callback"
error_reporting(0);
header('Content-Type: text/plain');

$DATA = __DIR__ . '/../data';
if (!is_dir($DATA)) mkdir($DATA, 0777, true);

$payload = [
  'ts'       => date('c'),
  'builder'  => $_POST['builder'] ?? '',
  'property' => $_POST['property'] ?? '',
  'name'     => trim($_POST['name'] ?? ''),
  'phone'    => trim($_POST['phone'] ?? ''),
  'email'    => trim($_POST['email'] ?? ''),
  'message'  => trim($_POST['message'] ?? ''),
  'ip'       => $_SERVER['REMOTE_ADDR'] ?? ''
];

$file = $DATA . '/callbacks.json';
$list = is_file($file) ? json_decode(file_get_contents($file), true) : [];
if (!is_array($list)) $list = [];
$list[] = $payload;
file_put_contents($file, json_encode($list, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

echo "ok";
