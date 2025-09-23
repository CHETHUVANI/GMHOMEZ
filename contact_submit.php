<?php
// contact_submit.php — handles Contact Us form POST
require_once __DIR__.'/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// CSRF
if (empty($_POST['csrf']) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
  header('Location: '.(function_exists('render_base_url')?render_base_url():'').'/contact.php?err=csrf'); exit;
}

// collect + sanitize
$payload = [
  'type'        => trim($_POST['type'] ?? ''),
  'category'    => trim($_POST['category'] ?? ''),
  'subcategory' => trim($_POST['subcategory'] ?? ''),
  'comment'     => trim($_POST['comment'] ?? ''),
  'email'       => trim($_POST['email'] ?? ''),
  'country_code'=> trim($_POST['country_code'] ?? '+91'),
  'phone'       => preg_replace('~[^0-9\- ]~','', $_POST['phone'] ?? ''),
  'ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
  'ua'          => $_SERVER['HTTP_USER_AGENT'] ?? '',
  'ts'          => date('c'),
];

// validate
$errors = [];
if ($payload['type']==='')        $errors[]='type';
if ($payload['category']==='')    $errors[]='category';
if ($payload['subcategory']==='') $errors[]='subcategory';
if ($payload['comment']==='')     $errors[]='comment';
if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) $errors[]='email';
if (!preg_match('~^[0-9\- ]{7,15}$~', $payload['phone'])) $errors[]='phone';

$base = function_exists('render_base_url') ? render_base_url() : '';
if ($errors) {
  header('Location: '.$base.'/contact.php?err=1'); exit;
}

// persist to file (simple queue/log)
$dir  = __DIR__.'/data';
$file = $dir.'/contact_submissions.json';
if (!is_dir($dir)) @mkdir($dir, 0775, true);

$all = [];
if (is_file($file)) {
  $raw = @file_get_contents($file);
  if ($raw !== false && $raw !== '') $all = json_decode($raw, true) ?: [];
}
$all[] = $payload;
@file_put_contents($file, json_encode($all, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

// OPTIONAL: send an email (uncomment & set your address)
// $to = 'careers@gmhomez.in';
// $sub = 'New Contact submission';
// $msg = "Type: {$payload['type']}\nCategory: {$payload['category']}\nSub: {$payload['subcategory']}\nComment: {$payload['comment']}\nEmail: {$payload['email']}\nPhone: {$payload['country_code']} {$payload['phone']}\nTime: {$payload['ts']}";
// @mail($to, $sub, $msg, "From: noreply@gmhomez.in");

// done
header('Location: '.$base.'/contact.php?ok=1'); exit;
