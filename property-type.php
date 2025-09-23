<?php
// property-type.php
error_reporting(E_ALL); ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';        // should define $BASE and (optionally) read_json(), url()
@require_once __DIR__ . '/lib/render.php';   // render_property_card($p) if available

/* ---------- Safe header/footer detection (optional) ---------- */
$HEADER = __DIR__ . '/partials/header.php';
if (!is_file($HEADER)) $HEADER = __DIR__ . '/header.php';
if (!is_file($HEADER)) $HEADER = null;

$FOOTER = __DIR__ . '/partials/footer.php';
if (!is_file($FOOTER)) $FOOTER = __DIR__ . '/footer.php';
if (!is_file($FOOTER)) $FOOTER = null;

/* ---------- Helpers / Polyfills ---------- */
if (!function_exists('h')) {
  function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('read_json')) {
  function read_json($file){
    if (!is_file($file)) return [];
    $j = json_decode(file_get_contents($file), true);
    return is_array($j) ? $j : [];
  }
}
if (!function_exists('url')) {
  function url($path=''){
    static $BASE = null;
    if ($BASE === null) {
      $b = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
      $BASE = ($b === '/' || $b === '\\' || $b === '.') ? '' : $b;
    }
    $path = ltrim($path, '/');
    return ($BASE ? $BASE.'/' : '/').$path;
  }
}
if (!function_exists('str_starts_with')) {
  function str_starts_with($haystack, $needle){
    return (string)$needle === '' ? false : strncmp($haystack, $needle, strlen($needle)) === 0;
  }
}
if (!function_exists('format_lakhs')) {
  // Price stored in Lakhs -> "₹ 11 L" or "₹ 1.2 Cr"
  function format_lakhs($lakhs){
    if ($lakhs === null || $lakhs === '' || !is_numeric($lakhs)) return 'Price on request';
    $lakhs = (float)$lakhs;
    if ($lakhs >= 100) {
      $cr = $lakhs / 100.0;
      return '₹ ' . rtrim(rtrim(number_format($cr, 2), '0'), '.') . ' Cr';
    }
    return '₹ ' . rtrim(rtrim(number_format($lakhs, 2), '0'), '.') . ' L';
  }
}
if (!function_exists('prop_img_url')) {
  function prop_img_url(array $p){
    $raw = $p['images'][0] ?? '';
    if (!$raw) return url('assets/img/placeholder.jpg');

    // normalize
    $s = str_replace('\\', '/', $raw);

    // already http(s)?
    if (preg_match('~^https?://~i', $s)) return $s;

    // if a full disk path contains /uploads/... cut from there
    if (($pos = stripos($s, '/uploads/')) !== false) {
      $s = substr($s, $pos); // -> /uploads/xxx.jpg
    }

    // candidates we’ll try on disk (document root)
    $candidates = [];

    // if starts with / assume it’s already site-root relative
    if ($s !== '' && $s[0] === '/') {
      $candidates[] = $s;
    } else {
      // plain file or "uploads/..." -> try common folders
      if (stripos($s, 'uploads/') === 0) $candidates[] = '/'.$s;
      $basename = ltrim($s, '/');
      $candidates[] = '/uploads/'.$basename;
      $candidates[] = '/uploads/properties/'.$basename; // many projects
    }

    // return the first candidate that actually exists; else a placeholder
    foreach ($candidates as $c) {
      if (is_file(__DIR__.$c)) return $c;
    }
    return url('assets/img/placeholder.jpg');
  }
}
if (!function_exists('details_href')) {
  // Auto-detect a details page present in project root
  function details_href($id){
    $candidates = ['view-details.php','property.php','project.php','details.php','view.php'];
    foreach ($candidates as $f) {
      if (is_file(__DIR__.'/'.$f)) {
        return url($f.'?id='.rawurlencode($id));
      }
    }
    return null; // none found
  }
}

/* ---------- Labels & slug parsing ---------- */
$labelMap = [
  'apartment' => 'Apartments',
  'villa'     => 'Villas',
  'plot'      => 'Plots',
  'studio'    => 'Studios',
  'senior'    => 'Senior Living',
];

// accept ptype as slug or plural
$raw  = strtolower(trim($_GET['ptype'] ?? ''));
$slug = rtrim($raw, 's');
if ($slug === 'apartments') $slug = 'apartment';
if (!isset($labelMap[$slug])) $slug = 'apartment';

$title = $labelMap[$slug] . ' — GM HOMEZ';

/* ---------- Load + Filter (JSON variant) ---------- */
$props = read_json(__DIR__.'/data/properties.json');
$filtered = array_values(array_filter($props, function($p) use ($slug){
  $t = strtolower($p['type'] ?? '');
  $syn = [
    'flat' => 'apartment', 'flats' => 'apartment',
    'plots'=> 'plot',
    'studios' => 'studio',
    'senior living' => 'senior', 'senior-living' => 'senior'
  ];
  if (isset($syn[$t])) $t = $syn[$t];
  return $t === $slug;
}));
$count = count($filtered);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?></title>

  <!-- Load your global site CSS if present -->
  <link rel="stylesheet" href="<?= url('assets/css/site.css') ?>">

  <!-- Minimal fallback styles -->
  <style>
    body{font:15px/1.6 system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;color:#0b1220;background:#ffffff}
    a{color:#0ea5e9;text-decoration:none} a:hover{text-decoration:underline}
    .wrap{max-width:1200px;margin:0 auto;padding:24px}
    .crumbs{color:#64748b;margin:6px 0 14px}
    .h1{font-size:28px;font-weight:800;margin:6px 0 16px}
    .grid{display:grid;gap:18px}
    .card{background:#0b1424;color:#e2e8f0;border-radius:20px;padding:18px;display:flex;gap:18px;align-items:center}
    .card img{width:320px;height:200px;object-fit:cover;border-radius:14px;background:#0f172a}
    .btn{display:inline-block;padding:10px 14px;border-radius:12px;background:#111827;color:#e5e7eb;border:1px solid #1f2937}
    .btn:hover{transform:translateY(-1px)}
    .muted{color:#94a3b8}
  </style>
</head>
<body>

  <?php if ($HEADER) include $HEADER; ?>

  <main class="wrap">
    <div class="crumbs"><a href="<?= url('index.php') ?>">Home</a> › <?= h($labelMap[$slug]) ?></div>
    <h1 class="h1"><?= h($labelMap[$slug]) ?> <small style="font-weight:500;color:#94a3b8">— <?= $count ?> project(s)</small></h1>

    <?php if ($count === 0): ?>
      <p>No <?= h(strtolower($labelMap[$slug])) ?> available right now. Try other types or cities.</p>
    <?php else: ?>
      <section class="grid">
        <?php
          // Prefer your site’s component if available
          if (function_exists('render_property_card')) {
            foreach ($filtered as $p) render_property_card($p);
          } else {
            // Fallback card renderer
            foreach ($filtered as $p){
              $title = $p['title'] ?? $p['name'] ?? 'Project';
              $city  = $p['city']  ?? '';
              $id    = $p['id']    ?? '';
              $imgUrl = prop_img_url($p);

              $priceMin = isset($p['price_min']) ? format_lakhs($p['price_min']) : null;
              $priceMax = isset($p['price_max']) ? format_lakhs($p['price_max']) : null;
              $priceStr = ($priceMin && $priceMax) ? ($priceMin.' - '.$priceMax)
                        : ($priceMin ?: ($priceMax ?: 'Price on request'));

              $href = $id ? details_href($id) : null;
              ?>
              <article class="card">
                <img src="<?= h($imgUrl) ?>" alt="<?= h($title) ?>">
                <div style="flex:1">
                  <h3 style="margin:0 0 6px;color:#e2e8f0"><?= h($title) ?></h3>
                  <?php if ($city): ?><div class="muted" style="margin-bottom:8px"><?= h($city) ?></div><?php endif; ?>
                  <div style="font-weight:700"><?= h($priceStr) ?></div>
                </div>
                <?php if ($href): ?>
                  <a class="btn" href="<?= h($href) ?>">View Details</a>
                <?php else: ?>
                  <span class="btn" style="opacity:.6;pointer-events:none">Details Unavailable</span>
                <?php endif; ?>
              </article>
              <?php
            }
          }
        ?>
      </section>
    <?php endif; ?>
  </main>

  <?php if ($FOOTER) include $FOOTER; ?>
</body>
</html>
