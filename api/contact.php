<?php
// gm-homez/api/contact.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL); ini_set('display_errors', 0);
ini_set('log_errors', 1);

$ROOT = dirname(__DIR__);
require_once $ROOT . '/config.php';

// ---------- helpers ----------
function inpt(string $k): string { return trim($_POST[$k] ?? ''); }
function digits(string $s): string { return preg_replace('/\D+/', '', $s); }
function e164(string $num): string {
  $t = trim($num);
  if ($t === '') return '';
  if ($t[0] === '+') return $t;
  $d = digits($t);
  if (!$d) return '';
  $cc = defined('DEFAULT_CC') ? DEFAULT_CC : '91';
  if (strlen($d) === 10) $d = $cc . $d;
  return '+' . $d;
}

// ---------- collect inputs ----------
$name    = inpt('name') ?: inpt('fullname') ?: inpt('full_name') ?: 'Unknown';
$email   = filter_var(inpt('email'), FILTER_SANITIZE_EMAIL);
$phone   = e164(inpt('phone'));
$message = trim($_POST['message'] ?? $_POST['msg'] ?? '');

$flow = 'Contact';
$first = strtok($message, "\n");
if ($first && stripos($first, 'Route Map:') === 0) {
  $flow = trim(substr($first, strlen('Route Map:')));
}

$payload = [
  'ts'      => date('c'),
  'flow'    => $flow,
  'name'    => $name,
  'email'   => $email,
  'phone'   => $phone,
  'message' => $message,
  'source'  => $_SERVER['HTTP_REFERER'] ?? ($_POST['source'] ?? ''),
  'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
  'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
];

// ---------- save to data/leads.json ----------
$DATA_DIR = $ROOT . '/data';
if (!is_dir($DATA_DIR)) @mkdir($DATA_DIR, 0775, true);
$leadsFile = $DATA_DIR . '/leads.json';
$leads = is_file($leadsFile) ? json_decode((string)file_get_contents($leadsFile), true) : [];
if (!is_array($leads)) $leads = [];
$leads[] = $payload;
file_put_contents($leadsFile, json_encode($leads, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE), LOCK_EX);

// ---------- email (PHPMailer if available, else mail()) ----------
function send_mail_smart(string $to, string $subj, string $body, string $replyToEmail = '', string $replyToName = '') {
  $from     = defined('SMTP_FROM') ? SMTP_FROM : (defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'no-reply@example.com');
  $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : (defined('SITE_NAME') ? SITE_NAME : 'Website');

  $autoload = dirname(__DIR__) . '/vendor/autoload.php';
  if (is_file($autoload)) {
    require_once $autoload;
    $m = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
      if (defined('SMTP_HOST') && SMTP_HOST) {
        $m->isSMTP();
        $m->Host       = SMTP_HOST;
        $m->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $m->SMTPAuth   = true;
        $m->Username   = defined('SMTP_USER') ? SMTP_USER : '';
        $m->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
        $m->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
      }
      $m->setFrom($from, $fromName);
      $m->addAddress($to);
      if ($replyToEmail) $m->addReplyTo($replyToEmail, $replyToName ?: $replyToEmail);
      $m->Subject = $subj;
      $m->Body    = $body;
      $m->send();
      return true;
    } catch (Throwable $e) { return $e->getMessage(); }
  } else {
    $hdr  = "From: $fromName <$from>\r\n";
    if ($replyToEmail) $hdr .= "Reply-To: $replyToName <$replyToEmail>\r\n";
    return mail($to, $subj, $body, $hdr) ? true : 'mail() failed';
  }
}

// ---------- SMS via Twilio (optional) ----------
function send_sms_twilio(string $to, string $body) {
  if (!defined('TWILIO_SID') || !TWILIO_SID || !defined('TWILIO_TOKEN') || !TWILIO_TOKEN || !defined('TWILIO_FROM') || !TWILIO_FROM) {
    return 'twilio_not_configured';
  }
  $autoload = dirname(__DIR__) . '/vendor/autoload.php';
  if (!is_file($autoload)) return 'twilio_sdk_missing';
  require_once $autoload;
  try {
    $client = new Twilio\Rest\Client(TWILIO_SID, TWILIO_TOKEN);
    $client->messages->create($to, ['from' => TWILIO_FROM, 'body' => $body]);
    return true;
  } catch (Throwable $e) {
    return $e->getMessage();
  }
}

// ---------- WhatsApp Cloud API (optional) ----------
function send_whatsapp_cloud_text(string $to, string $body) {
  if (!defined('WHATSAPP_TOKEN') || !WHATSAPP_TOKEN || !defined('WHATSAPP_PHONE_ID') || !WHATSAPP_PHONE_ID) {
    return 'wa_not_configured';
  }
  $url = "https://graph.facebook.com/v18.0/" . WHATSAPP_PHONE_ID . "/messages";
  $payload = [
    'messaging_product' => 'whatsapp',
    'to'   => digits($to), // WhatsApp expects digits only here
    'type' => 'text',
    'text' => ['preview_url' => false, 'body' => $body],
  ];
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
      'Authorization: Bearer ' . WHATSAPP_TOKEN,
      'Content-Type: application/json'
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 20,
  ]);
  $res = curl_exec($ch);
  $err = curl_error($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return ($err || $code >= 300) ? ($err ?: "HTTP $code: $res") : true;
}

function send_whatsapp_cloud_template(string $to, string $template, string $lang, array $components = []) {
  if (!defined('WHATSAPP_TOKEN') || !WHATSAPP_TOKEN || !defined('WHATSAPP_PHONE_ID') || !WHATSAPP_PHONE_ID) {
    return 'wa_not_configured';
  }
  $url = "https://graph.facebook.com/v18.0/" . WHATSAPP_PHONE_ID . "/messages";
  $payload = [
    'messaging_product' => 'whatsapp',
    'to'   => digits($to),
    'type' => 'template',
    'template' => [
      'name' => $template,
      'language' => ['code' => $lang ?: 'en_US'],
      'components' => $components
    ],
  ];
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
      'Authorization: Bearer ' . WHATSAPP_TOKEN,
      'Content-Type: application/json'
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 20,
  ]);
  $res = curl_exec($ch);
  $err = curl_error($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return ($err || $code >= 300) ? ($err ?: "HTTP $code: $res") : true;
}

// ---------- compose messages ----------
$site = defined('SITE_NAME') ? SITE_NAME : 'GM HOMEZ';
$adminToEmail = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : '';
$adminToPhone = defined('ADMIN_PHONE') ? ADMIN_PHONE : '';

$adminSub = "$site — New $flow lead";
$adminMsg = "New $flow lead\n\nTime: " . date('r') .
            "\nName: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message\n";

$alertText = "🔔 $site: New $flow lead\nName: $name\nPhone: $phone\nEmail: $email\n— " . date('H:i');

// ---------- send email(s) ----------
$ok = true; $err = '';
if ($adminToEmail) {
  $r = send_mail_smart($adminToEmail, $adminSub, $adminMsg, $email, $name);
  if ($r !== true) { $ok = false; $err = 'Admin email failed: ' . $r; }
}
if ($email) {
  $cliSub = "We received your $flow request — $site";
  $cliMsg = "Hi $name,\n\nThanks for contacting $site. We received your request ($flow). Our team will reach out shortly.\n\nYour message:\n$message\n\n— $site";
  send_mail_smart($email, $cliSub, $cliMsg);
}

// ---------- send admin SMS / WhatsApp ----------
$alerts = [];
if ($adminToPhone) {
  // Twilio SMS
  if (defined('TWILIO_SID') && TWILIO_SID && defined('TWILIO_FROM') && TWILIO_FROM) {
    $r = send_sms_twilio(e164($adminToPhone), $alertText);
    $alerts['sms_admin'] = ($r === true) ? 'ok' : $r;
  }
  // WhatsApp Cloud
  if (defined('WHATSAPP_TOKEN') && WHATSAPP_TOKEN && defined('WHATSAPP_PHONE_ID') && WHATSAPP_PHONE_ID) {
    if (defined('WHATSAPP_TEMPLATE') && WHATSAPP_TEMPLATE) {
      // if you have a template like "lead_alert" with 3 body params: site, flow, name
      $r = send_whatsapp_cloud_template($adminToPhone, WHATSAPP_TEMPLATE, (defined('WHATSAPP_LANG')?WHATSAPP_LANG:'en_US'), [
        ['type'=>'body','parameters'=>[
          ['type'=>'text','text'=>$site],
          ['type'=>'text','text'=>$flow],
          ['type'=>'text','text'=>$name],
        ]]
      ]);
    } else {
      $r = send_whatsapp_cloud_text($adminToPhone, $alertText);
    }
    $alerts['wa_admin'] = ($r === true) ? 'ok' : $r;
  }
}

// ---------- optional: notify client by SMS/WA ----------
if ($phone && defined('SEND_CLIENT_SMS') && SEND_CLIENT_SMS) {
  $r = send_sms_twilio($phone, "Thanks $name! We received your request ($flow). — $site");
  $alerts['sms_client'] = ($r === true) ? 'ok' : $r;
}
if ($phone && defined('SEND_CLIENT_WA') && SEND_CLIENT_WA) {
  if (defined('WHATSAPP_TEMPLATE') && WHATSAPP_TEMPLATE) {
    $r = send_whatsapp_cloud_template($phone, WHATSAPP_TEMPLATE, (defined('WHATSAPP_LANG')?WHATSAPP_LANG:'en_US'), [
      ['type'=>'body','parameters'=>[
        ['type'=>'text','text'=>$site],
        ['type'=>'text','text'=>$flow],
        ['type'=>'text','text'=>$name],
      ]]
    ]);
  } else {
    $r = send_whatsapp_cloud_text($phone, "Hi $name, we received your request ($flow). Our team will reach out shortly. — $site");
  }
  $alerts['wa_client'] = ($r === true) ? 'ok' : $r;
}

// ---------- response ----------
echo json_encode($ok ? ['ok'=>true,'message'=>'Lead saved & notifications sent','flow'=>$flow,'alerts'=>$alerts]
                    : ['ok'=>false,'message'=>$err?:'Email failed','flow'=>$flow,'alerts'=>$alerts]);
