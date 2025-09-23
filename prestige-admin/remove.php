<?php
require_once __DIR__.'/_common.php'; pg_require_login();
$id = $_GET['id'] ?? '';
$DATA = pg_read_json(__DIR__.'/../data/properties.json');
$DATA = array_values(array_filter($DATA, function($p) use ($id){
  if (($p['id']??'')!==$id) return true;
  return (($p['builder']??'')!==PG_BUILDER) ? true : false; // remove only if prestige
}));
pg_write_json(__DIR__.'/../data/properties.json', $DATA);
header('Location: '.pg_url('index.php'));
