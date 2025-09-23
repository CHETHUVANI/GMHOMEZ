<?php
require_once __DIR__ . '/_auth.php';
require_login();
// gm-homez/admin/leads.php
error_reporting(E_ALL); ini_set('display_errors','1');

$ROOT = realpath(__DIR__ . '/..'); // gm-homez
$DATA = $ROOT . '/data/leads.json';

$leads = [];
if (file_exists($DATA)) {
  $raw = file_get_contents($DATA);
  $arr = json_decode($raw, true);
  if (is_array($arr)) $leads = $arr;
}

// newest first
usort($leads, function($a,$b){
  return strcmp($b['created'] ?? '', $a['created'] ?? '');
});

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Leads · GM HOMEZ</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body{background:#0b1620;color:#e6f0f6;font-family:Inter,system-ui;margin:0}
  .wrap{max-width:1100px;margin:28px auto;padding:0 16px}
  .card{background:#0f2a37;border:1px solid rgba(148,163,184,.12);border-radius:16px;padding:18px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
  h1{margin:0 0 14px}
  .topbar{display:flex;gap:10px;align-items:center;justify-content:space-between;margin-bottom:10px}
  a.btn, button.btn{display:inline-block;padding:8px 12px;border-radius:10px;border:1px solid rgba(148,163,184,.25);background:#0b1620;color:#e6f0f6;text-decoration:none}
  table{width:100%;border-collapse:collapse;margin-top:10px}
  th,td{padding:10px;border-bottom:1px solid rgba(148,163,184,.12);text-align:left;vertical-align:top;font-size:14px}
  th{color:#a7c0cf}
  .muted{color:#9fb2c0}
  .nowrap{white-space:nowrap}
</style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <div class="topbar">
        <h1 style="margin:0">Leads (<?= count($leads) ?>)</h1>
        <div>
          <a class="btn" href="leads_export.php">Export CSV</a>
          <a class="btn" href="../index.php">← Back to site</a>
        </div>
      </div>

      <?php if (!$leads): ?>
        <p class="muted">No leads yet. The chatbot will create <code>data/leads.json</code> after the first submission.</p>
      <?php else: ?>
      <table>
        <thead>
          <tr>
            <th class="nowrap">Time</th>
            <th>Name</th>
            <th>Phone</th>
            <th>Message</th>
            <th>Source</th>
            <th>Quick</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($leads as $L): ?>
            <tr>
              <td class="nowrap"><?= h($L['created'] ?? '') ?></td>
              <td><?= h($L['name'] ?? '') ?></td>
              <td>
                <?php $ph = preg_replace('/\D+/', '', $L['phone'] ?? ''); ?>
                <a style="color:#8df" href="tel:<?= h($ph) ?>"><?= h($ph) ?></a>
              </td>
              <td><?= nl2br(h($L['message'] ?? '')) ?></td>
              <td><?= h($L['source'] ?? 'chatbot') ?></td>
              <td class="nowrap">
                <?php if ($ph): ?>
                  <a class="btn" target="_blank" rel="noopener" href="https://wa.me/91<?= h($ph) ?>?text=Hi%20<?= urlencode($L['name'] ?? '') ?>,%20regarding%20your%20GM%20HOMEZ%20enquiry.">WhatsApp</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
