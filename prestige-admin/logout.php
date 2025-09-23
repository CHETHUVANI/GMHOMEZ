<?php
require_once __DIR__.'/_common.php';
$_SESSION=[]; session_destroy();
header('Location: '.pg_url('login.php'));
