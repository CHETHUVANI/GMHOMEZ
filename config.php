<?php

// --- OpenAI (for general chat replies) ---
// Read OpenAI key from environment (never hardcode in repo)
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
if (!OPENAI_API_KEY) {
  error_log('OPENAI_API_KEY is not set in environment');
}

// --- Alert config ---
define('NOTIFY_EMAIL_TO',   'dnchethu77@gmail.com');  // where alert emails should go
define('FROM_EMAIL',        'no-reply@gm-homez.local'); // verified sender in Brevo
define('FROM_NAME',         'GM HOMEZ Bot');

define('BREVO_API_KEY',     'gRVjMKGIJqNks6nO'); // https://app.brevo.com
// SMS (Textlocal India): https://control.textlocal.in/settings/apikey/
define('TEXTLOCAL_API_KEY', 'your_textlocal_api_key');      // leave blank to disable SMS
define('TEXTLOCAL_SENDER',  'GMHOME'); // 6 chars A–Z (register in Textlocal)
define('ALERT_SMS_TO',      '917676536261'); // admin mobile in E.164 (country code + number)

// ===== Core error visibility (dev)
error_reporting(E_ALL); ini_set('display_errors', 1);

// ===== Base URL helpers (works for index.php or index1.php)
$doc = str_replace('\\','/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));

$__base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($__base === '\\' || $__base === '/') $__base = '';
if (!defined('BASE_URL')) define('BASE_URL', $__base);
function url($p=''){ return BASE_URL . '/' . ltrim($p, '/'); }

// ===== Absolute paths

function url_root(){ return APP_BASE ?: ''; }
if (!defined('ROOT_DIR')) define('ROOT_DIR', __DIR__);
if (!defined('DATA_DIR')) define('DATA_DIR', ROOT_DIR . '/data');
if (!defined('UPLOADS_DIR')) define('UPLOADS_DIR', ROOT_DIR . '/uploads');
if (!defined('UPLOADS_PROPS')) define('UPLOADS_PROPS', UPLOADS_DIR . '/properties');
if (!defined('UPLOADS_TEAM'))  define('UPLOADS_TEAM',  UPLOADS_DIR . '/team');

// ===== JSON files (absolute)
if (!defined('PROPS_JSON')) define('PROPS_JSON', DATA_DIR . '/properties.json');
if (!defined('TEAM_JSON'))  define('TEAM_JSON',  DATA_DIR . '/team.json');

// Ensure folders exist
foreach ([DATA_DIR, UPLOADS_DIR, UPLOADS_PROPS, UPLOADS_TEAM] as $d) {
  if (!is_dir($d)) @mkdir($d, 0775, true);
}

// Ensure JSON files exist (never empty path!)
if (!is_file(PROPS_JSON)) file_put_contents(PROPS_JSON, "[]");
if (!is_file(TEAM_JSON))  file_put_contents(TEAM_JSON,  "[]");

// ===== Admin credentials (change these!)
if (!defined('ADMIN_USER')) define('ADMIN_USER', 'admin');
if (!defined('ADMIN_PASS')) define('ADMIN_PASS', 'admin123');
// If you prefer a hashed password, comment ADMIN_PASS above and uncomment below:
// if (!defined('ADMIN_PASS_HASH')) define('ADMIN_PASS_HASH', '$2y$10$put_your_bcrypt_hash_here');

// ===== Helpers
function read_json($path){
  $s = @file_get_contents($path);
  if ($s === false || $s === '') return [];
  $d = json_decode($s, true);
  return is_array($d) ? $d : [];
}
function save_json($path, $data){
  $tmp = $path . '.tmp';
  file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
  rename($tmp, $path);
  return true;
}
function next_id($arr){
  $max = 0; foreach($arr as $it){ $id = (int)($it['id'] ?? 0); if ($id > $max) $max = $id; }
  return $max + 1;
}
function sanitize_file($name){
  return preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
}


// === Admin contact (where alerts go) ===
define('ADMIN_PHONE', '+916362024270');   // your number in E.164 format
define('DEFAULT_CC',  '91');              // default country code for client numbers (India)

// === Twilio SMS (optional) ===
define('TWILIO_SID',    'ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
define('TWILIO_TOKEN',  'your_twilio_auth_token');
define('TWILIO_FROM',   '+1XXXXXXXXXX');   // your Twilio SMS-capable number

// === WhatsApp Cloud API (optional) ===
// Create a WhatsApp Business App → get a permanent token + phone number ID
define('WHATSAPP_TOKEN',     'EAAB...');    // Permanent access token
define('WHATSAPP_PHONE_ID',  '###########'); // e.g. 123456789012345
// If you have an *approved* template to start conversations outside 24h window:
define('WHATSAPP_TEMPLATE',  'lead_alert'); // or '' to send plain text (works inside 24h)
define('WHATSAPP_LANG',      'en_US');

// === Optional: also notify the client by SMS/WhatsApp
define('SEND_CLIENT_SMS', false);
define('SEND_CLIENT_WA',  false);
