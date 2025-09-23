<?php
// gm-homez/admin/login_post.php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config.php';

$redirect = $_POST['redirect'] ?? (rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/\\') . '/admin/index.php');

function back(string $msg): never {
  $_SESSION['error'] = $msg;
  header('Location: login.php'); exit;
}

$user = trim($_POST['username'] ?? '');
$pass = (string)($_POST['password'] ?? '');

if (!defined('ADMIN_USER') || (!defined('ADMIN_PASS') && !defined('ADMIN_PASS_HASH'))) {
  back('Admin credentials not configured in config.php');
}

$ok = false;
if (defined('ADMIN_PASS') && hash_equals(ADMIN_PASS, $pass)) $ok = true;
if (defined('ADMIN_PASS_HASH') && password_verify($pass, ADMIN_PASS_HASH)) $ok = true;

if ($user === ADMIN_USER && $ok) {
  $_SESSION['gm_admin'] = true;
  header('Location: ' . $redirect); exit;
}
back('Invalid username or password');
