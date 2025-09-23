<?php
// gm-homez/api/lead.php
declare(strict_types=1);

// Capture any accidental output so it doesn't break JSON:
ob_start();
error_reporting(E_ALL);
// Do NOT echo warnings to the client; we'll capture them and include as "debug"
ini_set('display_errors','0');

header('Content-Type: application/json; charset=utf-8');

$ROOT = realpath(__DIR__ . '/..'); // gm-homez
$DATA_DIR = realpath($ROOT . '/data');
if ($DATA_DIR === false) { $DATA_DIR = $ROOT . '/data'; @mkdir($DATA_DIR, 0775, true); }
$LEADS_JSON = $DATA_DIR . '/leads.json';

// Quick ping for debugging in the browser: /api/lead.php?ping=1
if (isset($_GET['ping'])) {
  $junk = ob_get_clean();
  echo json_encode(['ok'=>true, 'pong'=>'lead', 'debug'=>$junk], JSON_UNESCAPED_UNICODE);
  exit;
}

// Read JSON body
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);
if (!is_array($in)) {
  $junk = ob_get_clean();
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Invalid JSON payload','debug'=>$junk], JSON_UNESCAPED_UNICODE);
  exit;
}

$name   = trim($in['name'] ?? '');
$phone  = preg_replace('/\D+/', '', (string)($in['phone'] ?? '')); // digits only
$msg    = trim($in['message'] ?? '');
$source = trim($in['source']  ?? 'chatbot');

if ($name === '' || strlen($phone) < 10) {
  $junk = ob_get_clean();
  http_response_code(422);
  echo json_encode(['ok'=>false,'error'=>'Name and valid phone are required','debug'=>$junk], JSON_UNESCAPED_UNICODE);
  exit;
}

$lead = [
  'id'      => (string)round(microtime(true)*1000),
  'name'    => $name,
  'phone'   => $phone,
  'message' => $msg,
  'source'  => $source,
  'created' => date('c'),
  'ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
  'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? null,
];

// Read existing leads (if any)
$leads = [];
if (file_exists($LEADS_JSON)) {
  $existing = json_decode((string)file_get_contents($LEADS_JSON), true);
  if (is_array($existing)) $leads = $existing;
}
$leads[] = $lead;

// Write back with lock
$fp = fopen($LEADS_JSON, 'c+');
if ($fp === false) {
  $junk = ob_get_clean();
  echo json_encode(['ok'=>false,'error'=>'Unable to open leads file','debug'=>$junk], JSON_UNESCAPED_UNICODE);
  exit;
}
flock($fp, LOCK_EX);
ftruncate($fp, 0);
fwrite($fp, json_encode($leads, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

// Optional alerts (only if files/keys exist)
$notify = ['email'=>null,'sms'=>null];
if (file_exists($ROOT . '/config.php')) require_once $ROOT . '/config.php';
if (file_exists($ROOT . '/lib/notify.php')) {
  require_once $ROOT . '/lib/notify.php';
  if (function_exists('notify_admin_on_lead')) {
    try { $notify = notify_admin_on_lead($lead); } catch (Throwable $e) { /* swallow */ }
  }
}

// Return JSON ONLY (plus any buffered warnings in "debug")
$junk = ob_get_clean();
echo json_encode(['ok'=>true, 'lead'=>$lead, 'notify'=>$notify, 'debug'=>$junk], JSON_UNESCAPED_UNICODE);
exit;
