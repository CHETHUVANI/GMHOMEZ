<?php
// gm-homez/admin/leads_export.php
error_reporting(E_ALL); ini_set('display_errors','1');

$ROOT = realpath(__DIR__ . '/..');
$DATA = $ROOT . '/data/leads.json';

$leads = [];
if (file_exists($DATA)) {
  $raw = file_get_contents($DATA);
  $arr = json_decode($raw, true);
  if (is_array($arr)) $leads = $arr;
}

// newest first
usort($leads, function($a,$b){ return strcmp($b['created'] ?? '', $a['created'] ?? ''); });

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="leads.csv"');

$fp = fopen('php://output','w');
fputcsv($fp, ['id','created','name','phone','message','source','ip','ua']);
foreach ($leads as $L) {
  fputcsv($fp, [
    $L['id'] ?? '',
    $L['created'] ?? '',
    $L['name'] ?? '',
    $L['phone'] ?? '',
    $L['message'] ?? '',
    $L['source'] ?? '',
    $L['ip'] ?? '',
    $L['ua'] ?? '',
  ]);
}
fclose($fp);
