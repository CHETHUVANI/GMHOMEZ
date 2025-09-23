<?php
$builder = isset($_GET['builder']) ? trim($_GET['builder']) : '';
$norm = strtolower(preg_replace('/\s+/', ' ', $builder));
$map = [
  'prestige' => '/prestige-group/',
  'prestige group' => '/prestige-group/',
  'sobha' => '/sobha-limited/',
  'sobha limited' => '/sobha-limited/',
  'kolte' => '/kolte-patil-developers/',
  'kolte patil' => '/kolte-patil-developers/',
  'kolte patil developers' => '/kolte-patil-developers/',
  'godrej' => '/godrej-properties/',
  'godrej properties' => '/godrej-properties/',
  'brigade' => '/brigade-group/',
  'brigade group' => '/brigade-group/',
];
if ($builder && isset($map[$norm])) { header('Location: ' . $map[$norm], true, 302); exit; }
http_response_code(404); echo "Builder not found";