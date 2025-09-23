<?php
// view.php — simple property details page

// ---- config fallbacks (work even if config.php is minimal)
if (!defined('ROOT_DIR')) {
  $cfg = __DIR__ . '/config.php';
  if (is_file($cfg)) require_once $cfg;
}
if (!function_exists('h')) {
  function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('json_load')) {
  function json_load($path){
    if (!$path || !is_file($path)) return [];
    $s = @file_get_contents($path);
    $d = json_decode($s, true);
    return is_array($d) ? $d : [];
  }
}
function base_url(){
  if (function_exists('url')) return rtrim(url(''), '/');
  $b = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
  return ($b === '/' || $b === '\\') ? '' : $b;
}
$BASE = base_url();
$PROPS = defined('PROPS_JSON') ? PROPS_JSON : __DIR__ . '/data/properties.json';
$UPLOAD_URL = defined('UPLOAD_URL') ? rtrim(UPLOAD_URL, '/') : ($BASE . '/uploads');

// ---- find the property
$id = $_GET['id'] ?? '';
$props = json_load($PROPS);
$prop = null;
foreach ($props as $p) { if ((string)($p['id'] ?? '') === (string)$id) { $prop = $p; break; } }
if (!$prop) {
  http_response_code(404);
  echo "<!doctype html><meta charset='utf-8'><body style='font-family:system-ui;background:#0b1620;color:#fff;display:grid;place-items:center;height:100vh'>
  <div><h1>Not Found</h1><p>No property with id <code>".h($id)."</code>.</p><p><a href='".h($BASE)."/' style='color:#8ab4ff'>← Back to Home</a></p></div>";
  exit;
}

// ---- build gallery URLs
$imgs = [];
if (!empty($prop['images']) && is_array($prop['images'])) {
  foreach ($prop['images'] as $fn) {
    $fn = basename($fn);
    if ($fn) $imgs[] = $UPLOAD_URL . '/' . rawurlencode($fn);
  }
}
if (!$imgs) {
  $cover = $prop['image'] ?? '';
  if ($cover) $imgs[] = $UPLOAD_URL . '/' . rawurlencode(basename($cover));
}
if (!$imgs) $imgs[] = $BASE . '/assets/back.png';

// fields
$title = $prop['title'] ?? 'Property';
$loc   = $prop['location'] ?? '';
$price = isset($prop['price']) && $prop['price'] !== '' ? ('₹' . number_format((int)$prop['price'])) : '₹—';
$desc  = $prop['description'] ?? '';

?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=h($title)?> · GM HOMEZ</title>
<style>
  body{margin:0;background:#0b1620;color:#e6f0f6;font-family:Inter,system-ui}
  .wrap{max-width:1100px;margin:24px auto;padding:0 16px}
  a.btn{display:inline-block;background:#111827;color:#e6f0f6;padding:10px 16px;border-radius:12px;text-decoration:none}
  .grid{display:grid;grid-template-columns:1.1fr .9fr;gap:20px}
  @media (max-width:900px){ .grid{grid-template-columns:1fr} }
  .hero{position:relative;border-radius:16px;overflow:hidden;height:420px;background:#0f172a}
  .hero img{width:100%;height:100%;object-fit:cover;display:block;transition:opacity .2s ease}
  .nav{position:absolute;top:50%;transform:translateY(-50%);width:40px;height:40px;border:none;border-radius:50%;
       display:grid;place-items:center;background:rgba(15,23,42,.7);color:#fff;font-size:20px;cursor:pointer}
  .nav:hover{background:rgba(15,23,42,.9)}
  .nav.prev{left:10px} .nav.next{right:10px}
  .thumbs{display:flex;gap:8px;margin-top:10px;overflow:auto;padding-bottom:8px}
  .thumbs img{width:90px;height:70px;object-fit:cover;border-radius:10px;opacity:.7;cursor:pointer;border:1px solid #233}
  .thumbs img.active{opacity:1;outline:2px solid #5eead4}
  .card{background:#0f2a37;border-radius:16px;padding:16px;border:1px solid rgba(148,163,184,.18)}
  .h1{font-weight:800;font-size:24px;margin:0 0 6px}
  .muted{opacity:.8}
  .mt12{margin-top:12px}.mt16{margin-top:16px}
</style>
</head>
<body>
  <div class="wrap">
    <p><a class="btn" href="<?=h($BASE)?>/">← Back</a></p>
    <div class="grid">
      <div>
        <div class="hero" id="hero" data-images='<?=h(json_encode($imgs,JSON_UNESCAPED_SLASHES))?>'>
          <img id="heroImg" src="<?=h($imgs[0])?>" alt="<?=h($title)?>">
          <button class="nav prev" aria-label="Prev">❮</button>
          <button class="nav next" aria-label="Next">❯</button>
        </div>
        <?php if (count($imgs) > 1): ?>
          <div class="thumbs" id="thumbs">
            <?php foreach ($imgs as $i => $u): ?>
              <img src="<?=h($u)?>" data-i="<?=$i?>" class="<?=$i===0?'active':''?>">
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div>
        <div class="card">
          <div class="h1"><?=h($title)?></div>
          <div class="muted"><?=h($loc)?> • <?=h($price)?></div>
          <?php if($desc): ?><p class="mt16"><?=nl2br(h($desc))?></p><?php endif; ?>
          <!-- add more sections (Overview, Amenities, etc.) later -->
        </div>
      </div>
    </div>
  </div>

<script>
(function(){
  var box = document.getElementById('hero');
  if (!box) return;
  var data = box.getAttribute('data-images') || '[]';
  var imgs = [];
  try { imgs = JSON.parse(data) || []; } catch(e){ imgs = []; }
  if (imgs.length < 2) return;

  var imgEl = document.getElementById('heroImg');
  var prev  = box.querySelector('.prev');
  var next  = box.querySelector('.next');
  var idx   = 0;

  function show(i){
    idx = (i + imgs.length) % imgs.length;
    var pre = new Image();
    pre.onload = function(){ imgEl.style.opacity = .3; requestAnimationFrame(function(){ imgEl.src = imgs[idx]; imgEl.style.opacity = 1; }); };
    pre.src = imgs[idx];
    var thumbs = document.getElementById('thumbs');
    if (thumbs){
      thumbs.querySelectorAll('img').forEach(function(t,ti){ t.classList.toggle('active', ti===idx); });
    }
  }

  function reset(){ clearInterval(t); t = setInterval(function(){ show(idx+1); }, 3000); }
  prev && prev.addEventListener('click', function(){ show(idx-1); reset(); });
  next && next.addEventListener('click', function(){ show(idx+1); reset(); });

  var thumbs = document.getElementById('thumbs');
  if (thumbs){
    thumbs.addEventListener('click', function(e){
      var t = e.target.closest('img[data-i]');
      if (!t) return;
      show(parseInt(t.dataset.i,10)||0);
      reset();
    });
  }

  box.addEventListener('mouseenter', function(){ clearInterval(t); });
  box.addEventListener('mouseleave', function(){ reset(); });

  show(0);
  var t = setInterval(function(){ show(idx+1); }, 3000);
})();
</script>


</body>
</html>
