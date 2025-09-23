<?php
// prestige-admin/_common.php
session_start();

// -------- Settings (change password if you want) --------
if (!defined('PG_ADMIN_PASSWORD')) define('PG_ADMIN_PASSWORD', 'admin123');
if (!defined('PG_BUILDER'))       define('PG_BUILDER', 'Prestige Group');

// -------- Paths / helpers --------
$PG_BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); if ($PG_BASE==='/'||$PG_BASE==='\\') $PG_BASE='';

function pg_url($path=''){ $root = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); return $root.'/'.ltrim($path,'/'); }
function pg_require_login(){
  if (empty($_SESSION['pg_ok'])) { header('Location: '.pg_url('login.php')); exit; }
}
if (!function_exists('pg_read_json')) {
  function pg_read_json($file){
    if (!is_file($file)) return [];
    $j = json_decode(file_get_contents($file), true);
    return is_array($j) ? $j : [];
  }
}
if (!function_exists('pg_write_json')) {
  function pg_write_json($file,$data){
    $tmp=$file.'.tmp';
    file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
    rename($tmp,$file);
  }
}
if (!function_exists('pg_slug')) {
  function pg_slug($s){ $s=strtolower(trim($s)); $s=preg_replace('~[^a-z0-9]+~','-',$s); return trim($s,'-'); }
}
