<?php
// gm-homez/api/ai.php
declare(strict_types=1);
session_start();

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors','0');

$ROOT = realpath(__DIR__ . '/..');
if (!file_exists($ROOT . '/config.php')) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'config.php missing']); exit;
}
require_once $ROOT . '/config.php';

if (!defined('OPENAI_API_KEY') || OPENAI_API_KEY === '') {
  echo json_encode(['ok'=>false,'error'=>'AI not configured']); exit;
}

$in = json_decode(file_get_contents('php://input'), true);
$q  = trim((string)($in['message'] ?? ''));
if ($q === '') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Empty message']); exit; }

$model = defined('OPENAI_MODEL') && OPENAI_MODEL ? OPENAI_MODEL : 'gpt-4o-mini';

// Lightweight system guidance to keep answers useful for your site
$instructions =
  "You are GM HOMEZ website assistant. Be clear, accurate and concise (<=120 words unless asked for detail). ".
  "If the user asks about GM HOMEZ listings or budgets, explain how to use the site search; DO NOT invent property data. ".
  "For generic questions, answer normally. Use short lists where helpful.";

$payload = [
  'model' => $model,
  'input' => "Instructions: {$instructions}\n\nUser: {$q}",
];

// Let OpenAI keep the conversation context for us
if (!empty($_SESSION['ai_prev'])) {
  $payload['previous_response_id'] = $_SESSION['ai_prev'];
}

$ch = curl_init('https://api.openai.com/v1/responses');
curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_HTTPHEADER     => [
    'Content-Type: application/json',
    'Authorization: Bearer ' . OPENAI_API_KEY,
  ],
  CURLOPT_POSTFIELDS     => json_encode($payload),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT        => 25,
]);
$raw  = curl_exec($ch);
$err  = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($raw === false) { echo json_encode(['ok'=>false,'error'=>'Network error: '.$err]); exit; }

$out = json_decode($raw, true);
if (!is_array($out) || $code >= 400) {
  $msg = $out['error']['message'] ?? ('HTTP '.$code);
  echo json_encode(['ok'=>false,'error'=>$msg]); exit;
}

// Extract first text chunk (Responses API shape)
$text = '';
if (!empty($out['output']) && is_array($out['output'])) {
  foreach ($out['output'] as $block) {
    if (!empty($block['content']) && is_array($block['content'])) {
      foreach ($block['content'] as $c) {
        if (!empty($c['text'])) { $text = (string)$c['text']; break 2; }
      }
    }
  }
}
if ($text === '' && !empty($out['output_text'])) { $text = (string)$out['output_text']; }

// Save response id for continuation
if (!empty($out['id'])) $_SESSION['ai_prev'] = $out['id'];

echo json_encode(['ok'=>true, 'text'=>$text, 'id'=>$out['id'] ?? null]);
