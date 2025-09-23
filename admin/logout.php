<?php
// gm-homez/admin/logout.php
declare(strict_types=1);
session_start();
$_SESSION = [];
session_destroy();

$ADMIN_BASE = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
if ($ADMIN_BASE === '/' || $ADMIN_BASE === '\\') $ADMIN_BASE = '';
header('Location: ' . $ADMIN_BASE . '/login.php');
exit;
