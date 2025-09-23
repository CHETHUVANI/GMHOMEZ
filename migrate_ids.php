<?php
require_once __DIR__ . '/config.php';
$items = json_decode(@file_get_contents($PROPS_JSON), true) ?: [];
$changed = false;
foreach ($items as &$it) {
  if (empty($it['id'])) { $it['id'] = uniqid('prop_'); $changed = true; }
}
if ($changed) {
  file_put_contents($PROPS_JSON, json_encode($items, JSON_PRETTY_PRINT));
  echo "IDs added.\n";
} else {
  echo "All items already have IDs.\n";
}
