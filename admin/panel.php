<?php
require_once __DIR__ . '/_auth.php';
require_login();
error_reporting(E_ALL); ini_set('display_errors', 1);
require_once __DIR__ . '/../config.php';

/* Fallbacks if config.php didn’t define these */
if (!defined('DATA_DIR'))   define('DATA_DIR',   __DIR__ . '/../data');
if (!defined('PROPS_JSON')) define('PROPS_JSON', DATA_DIR . '/properties.json');
if (!defined('TEAM_JSON'))  define('TEAM_JSON',  DATA_DIR . '/team.json');
if (!defined('UPLOAD_DIR')) define('UPLOAD_DIR', __DIR__ . '/../uploads');

$APP_BASE = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
if ($APP_BASE === '' || $APP_BASE === '/') $APP_BASE = '';
if (!defined('UPLOAD_URL')) define('UPLOAD_URL', $APP_BASE . '/uploads');

@is_dir(DATA_DIR)   || @mkdir(DATA_DIR, 0777, true);
@is_dir(UPLOAD_DIR) || @mkdir(UPLOAD_DIR, 0777, true);

$props = file_exists(PROPS_JSON) ? (json_decode(file_get_contents(PROPS_JSON), true) ?: []) : [];
$team  = file_exists(TEAM_JSON)  ? (json_decode(file_get_contents(TEAM_JSON),  true) ?: []) : [];
$propsCount = is_array($props) ? count($props) : 0;
$teamCount  = is_array($team)  ? count($team)  : 0;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin · Panel</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,Segoe UI,Arial;background:#0b1620;color:#e6f0f6;margin:0}
    .wrap{max-width:1000px;margin:24px auto;padding:0 16px}
    .card{background:#0f2a37;border:1px solid rgba(148,163,184,.18);border-radius:14px;padding:16px;margin-bottom:14px}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
    .btn{display:inline-block;background:#22d3ee;color:#062126;padding:10px 14px;border-radius:10px;text-decoration:none;font-weight:700}
    .muted{color:#9fb2c0}
    code{background:#0b1329;padding:2px 6px;border-radius:6px}
  </style>
</head>
<body>
  <div class="wrap">
    <h2>Admin · Dashboard</h2>

    <div class="grid">
      <div class="card">
        <h3>Properties</h3>
        <p class="muted">Total: <b><?= $propsCount ?></b></p>
        <a class="btn" href="<?= $APP_BASE ?>/admin/properties.php">Manage Properties →</a>
      </div>
      <div class="card">
        <h3>Team</h3>
        <p class="muted">Total: <b><?= $teamCount ?></b></p>
        <a class="btn" href="<?= $APP_BASE ?>/admin/team.php">Manage Team →</a>
      </div>
      <div class="card">
        <h3>Uploads</h3>
        <p class="muted">Folder: <code><?= htmlspecialchars(UPLOAD_DIR) ?></code><br>URL: <code><?= htmlspecialchars(UPLOAD_URL) ?></code></p>
      </div>
    </div>

    <div class="card">
      <h3>Quick Links</h3>
      <p>
        <a class="btn" href="<?= $APP_BASE ?>/index.php">← Back to Site</a>
        <a class="btn" href="<?= $APP_BASE ?>/admin/login.php">Admin Login</a>
      </p>
    </div>
  </div>
</body>
</html>
