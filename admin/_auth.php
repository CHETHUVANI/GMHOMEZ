<?php
// gm-homez/admin/_auth.php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

/** Base path like /gm-homez/admin */
function _admin_base(): string {
  $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
  return ($base === '/' || $base === '\\') ? '' : $base;
}

/** Redirect helper (relative to /admin by default) */
function admin_redirect(string $path): void {
  $url = str_starts_with($path, '/') ? $path : (_admin_base() . '/' . ltrim($path, '/'));
  header('Location: ' . $url, true, 302);
  exit;
}

/** Require admin login; bounce to login with ?redirect=<current> */
function require_login(): void {
  if (empty($_SESSION['gm_admin'])) {
    $target = $_SERVER['REQUEST_URI'] ?? (_admin_base() . '/panel.php');
    admin_redirect('login.php?redirect=' . urlencode($target));
  }
}
