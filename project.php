<?php
// gm-homez/project.php — View Details with tabs, floor plan, amenities, gallery, EMI, map, questions

/* ---------- bootstrap ---------- */
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); if ($BASE==='/'||$BASE==='\\') $BASE='';
error_reporting(E_ALL); ini_set('display_errors', 1);
require_once __DIR__ . '/config.php';

/* ---------- helpers (guarded) ---------- */
if (!function_exists('read_json')) {
  function read_json($file){
    if (!is_file($file)) return null;
    $j = json_decode(file_get_contents($file), true);
    return is_array($j) ? $j : null;
  }
}
if (!function_exists('url_abs')) {
  function url_abs($path, $BASE){
    if(!$path) return '';
    if (preg_match('~^https?://~i', $path)) return $path;
    $p = ltrim(str_replace('\\','/',$path),'/');
    if (stripos($path,'/uploads/')===0) return $BASE . $path;
    if (stripos($path,'uploads/')===0)  return $BASE . '/' . $p;
    if (stripos($path,'assets/')===0)   return $BASE . '/' . $p;
    return $BASE . '/' . $p;
  }
}
if (!function_exists('fmt_lakhs')) {
  function fmt_lakhs($v){
    if ($v===null || $v==='') return '—';
    $v = (float)$v; // stored in Lakhs
    return $v>=100 ? '₹ ' . number_format($v/100, 1) . ' Cr' : '₹ ' . number_format($v, 2) . ' L';
  }
}
if (!function_exists('safe')) { function safe($s){ return htmlspecialchars($s??'', ENT_QUOTES,'UTF-8'); } }

/* ---------- load project ---------- */
$all = read_json(__DIR__.'/data/properties.json') ?: [];
$id  = $_GET['id'] ?? '';
$prop = null;
foreach ($all as $p) { if (($p['id'] ?? '') === $id) { $prop = $p; break; } }
if (!$prop) { http_response_code(404); echo "<!doctype html><meta charset='utf-8'><style>body{font-family:system-ui;background:#0b1620;color:#e6f0f6;padding:40px}</style><h2>Project not found</h2>"; exit; }


// ---------- helpers (drop in once) ----------
if (!function_exists('h')) {
  function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('url')) {
  function url($path=''){
    static $BASE = null;
    if ($BASE === null) {
      $b = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
      $BASE = ($b==='/'||$b==='\\'||$b==='.') ? '' : $b;
    }
    $path = ltrim($path, '/');
    return ($BASE ? $BASE.'/' : '/').$path;
  }
}
// Normalize a single image path (unit or gallery) to a web URL
if (!function_exists('normalize_img')) {
  function normalize_img($raw){
    if (!$raw) return null;
    $s = str_replace('\\','/',$raw);
    if (preg_match('~^https?://~i',$s)) return $s;
    // Cut full disk path down to /uploads/...
    if (($pos = stripos($s, '/uploads/')) !== false) $s = substr($s,$pos);
    if ($s !== '' && $s[0] !== '/') {
      if (stripos($s,'uploads/')===0) $s = '/'.$s;
      else $s = '/uploads/'.ltrim($s,'/');
    }
    return $s;
  }
}
// Get first good image for the project
if (!function_exists('project_primary_img')) {
  function project_primary_img(array $project){
    $imgs = $project['images'] ?? $project['gallery'] ?? [];
    foreach ((array)$imgs as $im) {
      $u = normalize_img($im);
      if ($u) return $u;
    }
    return url('assets/img/placeholder.jpg');
  }
}
// Pick an image for a given unit; fallback to project image
if (!function_exists('unit_img')) {
  function unit_img(array $unit, array $project){
    $cand = $unit['img'] ?? $unit['image'] ?? null;
    $u = normalize_img($cand);
    if ($u) return $u;
    return project_primary_img($project);
  }
}




/* ---------- fields ---------- */
$builder   = $prop['builder'] ?? 'Builder';
$name      = $prop['name'] ?? 'Project';
$city      = $prop['city'] ?? '';
$location  = $prop['location'] ?? '';
$pos_ym    = $prop['possession_ym'] ?? '';
$pos_txt   = $pos_ym ? preg_replace('~[-/]~','’',$pos_ym) : ''; // 2028’08
$status    = $prop['status'] ?? '';
$launch_ym = $prop['launch_ym'] ?? '';
$total_units = $prop['total_units'] ?? '';
$total_area  = $prop['total_area_acres'] ?? ($prop['total_area'] ?? ''); // eg 17 Acres
$resale   = $prop['resale'] ?? '';
$rera_id  = $prop['rera_id'] ?? ($prop['rera'] ?? '');
$price_min = $prop['price_min'] ?? null;
$price_max = $prop['price_max'] ?? null;
$overview  = $prop['overview'] ?? ($prop['description'] ?? '');
$features  = $prop['salient_features'] ?? [];
$amenities = $prop['amenities'] ?? [];
$banks     = $prop['approved_banks'] ?? []; // names only
$disclaimer= $prop['disclaimer'] ?? 'The information and data published herein are collected from publicly available sources. Please verify independently.';
$lat       = isset($prop['lat']) ? (float)$prop['lat'] : null;
$lng       = isset($prop['lng']) ? (float)$prop['lng'] : null;

/* media */
$gallery = array_map(fn($u)=>url_abs($u,$BASE), ($prop['gallery'] ?? []));
if (!$gallery) $gallery = [$BASE.'/assets/back.png'];
$videos = [];
if (!empty($prop['videos']) && is_array($prop['videos'])) $videos = $prop['videos'];
if (!empty($prop['video_url'])) $videos[] = $prop['video_url'];
$videos = array_map(fn($u)=>url_abs($u,$BASE), $videos);
$video = $videos[0] ?? null;

/* floor plans */
$floor_plans = is_array($prop['floor_plans'] ?? null) ? $prop['floor_plans'] : []; // [{bhk, carpet_area, unit_label, image, builder_price}]
foreach ($floor_plans as &$fp) { if (!empty($fp['image'])) $fp['image'] = url_abs($fp['image'], $BASE); }

/* units (fallback to show areas/prices if no separate floor_plans) */
$units = is_array($prop['units'] ?? null) ? $prop['units'] : [];
if (!$units && (isset($prop['bhk']) || isset($prop['area_min']) || isset($prop['price_min']))) {
  $units = [[
    'bhk' => $prop['bhk'] ?? 1,
    'area_min' => $prop['area_min'] ?? 0,
    'area_max' => $prop['area_max'] ?? ($prop['area_min'] ?? 0),
    'price_min'=> $prop['price_min'] ?? null,
    'price_max'=> $prop['price_max'] ?? null,
  ]];
}

/* siblings by same builder */
$siblings = array_values(array_filter($all, fn($p)=>($p['builder']??'')===$builder && ($p['id']??'')!==$id));
foreach ($siblings as &$s) {
  if (!empty($s['gallery'][0])) $s['gallery'][0] = url_abs($s['gallery'][0], $BASE);
  $s['details_url'] = !empty($s['details_url']) ? url_abs($s['details_url'], $BASE) : $BASE.'/project.php?id='.urlencode($s['id']);
}

$wa = "https://wa.me/917676536261?text=" . urlencode("Hi GM HOMEZ, I'm interested in {$name} ({$builder}).");
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=safe($name)?> by <?=safe($builder)?> · GM HOMEZ</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{ --bg:#0b1620; --panel:#0f2430; --muted:#9fb2c0; --line:rgba(148,163,184,.18); --brand1:#6ee7ff; --brand2:#a78bfa; --accent:#ffb86b; }
  *{box-sizing:border-box} body{margin:0;background:var(--bg);color:#e6f0f6;font-family:Poppins,system-ui}
  a{color:#8ddfff;text-decoration:none}
  .container{max-width:1200px;margin:0 auto;padding:16px}
  .btn{padding:10px 14px;border-radius:12px;border:1px solid var(--line);background:#102a37;color:#e6f0f6;cursor:pointer}
  .btn.grad{border:none;background:linear-gradient(90deg,var(--brand1),var(--brand2));background-size:200% 200%;animation:movegrad 4s ease infinite}
  @keyframes movegrad{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
  .top{background:#0c1b25;border-bottom:1px solid var(--line)}
  .topwrap{display:flex;align-items:center;gap:14px}
  .grow{flex:1}

  /* page head */
  .title{font-size:24px;font-weight:800;margin:10px 0 6px}
  .muted{color:var(--muted)}
  .panel{background:#0f2430;border:1px solid var(--line);border-radius:14px}
  .pad{padding:14px}

  /* sticky section tabs */
  .tabs{position:sticky;top:0;background:rgba(12,27,37,.9);backdrop-filter:blur(6px);z-index:20;border-bottom:1px solid var(--line)}
  .tabs .wrap{max-width:1200px;margin:0 auto;display:flex;gap:18px;overflow:auto}
  .tabs a{display:block;padding:12px 4px;color:#cfe6f3;border-bottom:2px solid transparent;white-space:nowrap}
  .tabs a.active{color:#fff;border-color:#6ee7ff}

  /* hero (video first) */
  .hero{display:grid;grid-template-columns:1fr 360px;gap:16px;margin-top:12px}
  .heroMedia{position:relative;height:420px;overflow:hidden;border-radius:14px}
  .heroMedia video,.heroMedia img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;background:#000}
  .arrow{position:absolute;top:50%;transform:translateY(-50%);width:38px;height:38px;border-radius:999px;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;cursor:pointer;user-select:none;z-index:3}
  .arrow.left{left:8px} .arrow.right{right:8px}

  .kv{display:grid;grid-template-columns:140px 1fr;gap:8px;margin:8px 0}
  .chip{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;border:1px solid var(--line);background:#0b1c27;margin:4px 6px 0 0}
  .price{font-size:22px;font-weight:800;color:#ffd0a6}

  /* sections */
  .section{margin-top:20px}
  .secTitle{font-weight:700;margin:0 0 10px}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  .divider{height:1px;background:var(--line);margin:12px 0}

  /* floor plan */
  .bhkTabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
  .bhkTabs .tab{padding:8px 12px;border:1px solid var(--line);border-radius:10px;background:#0b1c27;cursor:pointer}
  .bhkTabs .tab.active{background:#123246}
  .fpRow{display:grid;grid-template-columns:220px 1fr 160px 140px;gap:12px;align-items:center;padding:10px 0;border-bottom:1px solid rgba(148,163,184,.12)}
  .fpRow img{width:220px;height:140px;object-fit:cover;border-radius:10px}

  /* amenities */
  .amenGrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:18px}
  .amenItem{text-align:center}
  .amenIcon{font-size:26px;margin-bottom:6px;opacity:.9}

  /* gallery */
  .galleryGrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px}
  .galleryGrid img{width:100%;height:160px;object-fit:cover;border-radius:10px}

  /* EMI */
  .emiWrap{display:grid;grid-template-columns:1fr 360px;gap:18px}
  .formRow{margin:10px 0}
  .formRow input[type="range"]{width:100%}
  .pie{width:240px;height:240px;border-radius:50%;background:conic-gradient(#6ee7ff var(--pct,0%), #a78bfa 0);margin:auto}
  .emiBox{background:#0b1c27;border:1px dashed var(--line);border-radius:12px;padding:12px}

  /* map */
  .map{width:100%;height:360px;border:0;border-radius:12px}

  /* more cards */
  .more{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px}
  .card{background:#0f2430;border:1px solid var(--line);border-radius:12px;overflow:hidden}
  .card img{width:100%;height:160px;object-fit:cover;display:block}
  .card .pad{padding:10px}

  /* bottom ask */
  .ask{background:#0f2430;border:1px solid var(--line);border-radius:16px;padding:20px;text-align:center}

  /* sticky CTA */
  .sticky{position:sticky;bottom:0;background:rgba(12,27,37,.9);backdrop-filter:blur(6px);border-top:1px solid var(--line);padding:10px 0;margin-top:24px}
  .sticky .row{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}

  /* modal (namespaced) */
  .gm-modal{position:fixed;inset:0;background:rgba(0,0,0,.65);display:none;align-items:center;justify-content:center;z-index:9999}
  .gm-modal.show{display:flex}
  .gm-modal .box{background:#0f2430;border:1px solid var(--line);padding:18px;border-radius:16px;min-width:320px;max-width:420px;max-height:85vh;overflow:auto}
  .gm-modal input,.gm-modal textarea{width:100%;margin-top:6px;padding:10px;border-radius:10px;border:1px solid var(--line);background:#0b1c27;color:#e6f0f6}
</style>
</head>
<body>

<!-- Top mini bar -->
<div class="top">
  <div class="container topwrap">
    <div style="font-weight:700">GM HOMEZ</div>
    <div class="grow"></div>
    <button class="btn" onclick="location.href='<?=$BASE?>/builders.php?builder=<?=urlencode($builder)?>'">Back to <?=safe($builder)?></button>
    <button class="btn" onclick="location.href='<?=$BASE?>/index.php'">Home</button>
     <a class="btn" href="<?=$BASE?>/builders.php?builder=<?=urlencode($builder)?>">Prestige Admin</a>

     
  </div>
</div>

<div class="container">
  <div class="title"><?=safe($name)?></div>
  <div class="muted">📍 <?=safe($location)?></div>

  <!-- Tabs -->
  <nav class="tabs">
    <div class="wrap">
      <a href="#overview" class="active">Overview</a>
      <a href="#floorplan">Floor Plan</a>
      <a href="#amenities">Amenities</a>
      <a href="#gallery">Gallery</a>
      <a href="#emi">Home Loan</a>
      <a href="#map">Map</a>
      <a href="#ask">Have a Question?</a>
    </div>
  </nav>

  <!-- Head: media + key facts -->
  <div class="hero">
    <div class="panel">
      <div class="heroMedia" id="hero">
        <?php if ($video): ?>
          <video id="heroVideo" controls playsinline preload="metadata" poster="<?=safe($gallery[0] ?? ($BASE.'/assets/back.png'))?>"></video>
          <script>
            (function(){
              const v=document.getElementById('heroVideo'); const s=document.createElement('source');
              s.src=<?=json_encode($video)?>; s.type='video/mp4'; v.appendChild(s);
            })();
          </script>
        <?php else: ?>
          <img src="<?=safe($gallery[0])?>" alt="" onerror="this.src='<?=$BASE?>/assets/back.png'">
        <?php endif; ?>
        <?php if (count($gallery)>1 || $video): ?>
          <div class="arrow left"  onclick="mediaStep(-1)">‹</div>
          <div class="arrow right" onclick="mediaStep(1)">›</div>
        <?php endif; ?>
      </div>
    </div>
    <div class="panel pad">
      <div class="kv"><div class="muted">Builder</div><div><?=safe($builder)?></div></div>
      <?php if ($pos_txt): ?><div class="kv"><div class="muted">Possession</div><div>Starts from: <?=safe($pos_txt)?></div></div><?php endif; ?>
      <?php if ($status): ?><div class="kv"><div class="muted">Status</div><div><?=safe($status)?></div></div><?php endif; ?>
      <?php if ($total_area): ?><div class="kv"><div class="muted">Total Area</div><div><?=safe($total_area)?><?=is_numeric($total_area)?' Acres':''?></div></div><?php endif; ?>
      <?php if ($total_units): ?><div class="kv"><div class="muted">Total Units</div><div><?=safe($total_units)?></div></div><?php endif; ?>
      <?php if ($launch_ym): ?><div class="kv"><div class="muted">Launch Date</div><div><?=safe(preg_replace('~[-/]~','’',$launch_ym))?></div></div><?php endif; ?>
      <?php if ($resale!==''): ?><div class="kv"><div class="muted">Resale</div><div><?=safe($resale)?></div></div><?php endif; ?>
      <?php if ($rera_id): ?><div class="kv"><div class="muted">RERA</div><div><?=safe($rera_id)?></div></div><?php endif; ?>
      <div class="kv"><div class="muted">Price</div><div class="price"><?=fmt_lakhs($price_min)?><?=($price_max!==null?' - '.fmt_lakhs($price_max):'')?></div></div>
      <?php if ($banks): ?>
        <div class="divider"></div>
        <div class="secTitle">Approved by Banks</div>
        <?php foreach($banks as $b): ?><span class="chip"><?=safe($b)?></span><?php endforeach; ?>
      <?php endif; ?>
      <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap">
        <a class="btn" href="<?=$wa?>" target="_blank" rel="noopener">Chat on WhatsApp</a>
        <button class="btn grad" onclick="openModal('<?=safe($name)?>')">Get Callback</button>
      </div>
    </div>
  </div>

  <!-- Overview -->
  <section id="overview" class="section panel pad">
    <div class="secTitle">Overview</div>
    <?php if ($features): ?>
      <div class="grid2">
        <div>
          <div class="secTitle" style="margin-top:0">Salient Features</div>
          <ul style="margin:0 0 8px 18px;line-height:1.6">
            <?php foreach($features as $f): ?><li><?=safe($f)?></li><?php endforeach; ?>
          </ul>
        </div>
        <div>
          <div class="secTitle" style="margin-top:0">About <?=safe($name)?></div>
          <div class="muted" style="white-space:pre-wrap;line-height:1.6"><?=nl2br(safe($overview))?></div>
        </div>
      </div>
    <?php else: ?>
      <div class="muted" style="white-space:pre-wrap;line-height:1.6"><?=nl2br(safe($overview))?></div>
    <?php endif; ?>
  </section>

  <!-- Floor Plan -->
  <section id="floorplan" class="section panel pad">
    <div class="secTitle"><?=safe($name)?> Floor Plans</div>
    <?php
      // group floor plans by BHK
      $bybhk = [];
      foreach ($floor_plans as $fp) { $k = (int)($fp['bhk'] ?? 0); if(!$k) continue; $bybhk[$k][] = $fp; }
      ksort($bybhk);
    ?>
    <?php if ($bybhk): ?>
      <div class="bhkTabs" id="bhkTabs">
        <?php $i=0; foreach($bybhk as $k=>$arr): ?>
          <div class="tab <?= $i===0?'active':'' ?>" data-bhk="<?=$k?>"><?=$k?> BHK</div>
        <?php $i++; endforeach; ?>
      </div>
      <div id="fpList">
        <?php $firstKey = array_key_first($bybhk); foreach($bybhk as $k=>$arr): ?>
          <div class="fpGroup" data-bhk="<?=$k?>" style="<?= $k===$firstKey?'':'display:none' ?>">
            <div class="fpRow" style="font-weight:600">
              <div>Floor Plan</div><div>Carpet Area</div><div>Builder Price</div><div></div>
            </div>
            <?php foreach($arr as $fp): ?>
              <div class="fpRow">
                <img src="<?=safe($fp['image'] ?? ($BASE.'/assets/back.png'))?>" onerror="this.src='<?=$BASE?>/assets/back.png'">
                <div>
                  <div><?=safe($fp['carpet_area'] ?? '')?> sq ft</div>
                  <div class="muted"><?=safe($fp['unit_label'] ?? ($k.'BHK'))?></div>
                </div>
                <div><?=fmt_lakhs($fp['builder_price'] ?? '')?></div>
                <div><button class="btn grad" onclick="openModal('<?=safe($name)?> - <?=$k?>BHK')">Enquire Now</button></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <?php if ($units): ?>
        <div class="fpRow" style="font-weight:600">
          <div>Type</div><div>Area (sqft)</div><div>Price</div><div></div>
        </div>
        <?php foreach($units as $u): ?>
          <div class="fpRow">
            <img src="<?=$BASE?>/assets/back.png" alt="">
            <div><?=safe(($u['area_min'] ?? 0).' - '.($u['area_max'] ?? 0))?></div>
            <div><?=fmt_lakhs($u['price_min'] ?? null)?><?=isset($u['price_max'])?' - '.fmt_lakhs($u['price_max']):''?></div>
            <div><button class="btn grad" onclick="openModal('<?=safe($name)?> - <?=safe($u['bhk'])?>BHK')">Enquire Now</button></div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="muted">Floor plan details will be updated soon.</div>
      <?php endif; ?>
    <?php endif; ?>
  </section>

  <!-- Amenities -->
  <section id="amenities" class="section panel pad">
    <div class="secTitle"><?=safe($name)?> Amenities</div>
    <?php if ($amenities): ?>
      <div class="amenGrid">
        <?php foreach($amenities as $a): ?>
          <div class="amenItem">
            <div class="amenIcon">🏢</div>
            <div><?=safe($a)?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="muted">Amenities will be updated soon.</div>
    <?php endif; ?>
  </section>

  <!-- Gallery -->
  <section id="gallery" class="section panel pad">
    <div class="secTitle">Gallery</div>
    <?php if ($videos): ?>
      <div class="secTitle" style="font-weight:600;margin-top:0">Videos</div>
      <video controls playsinline style="width:100%;max-height:520px;background:#000;border-radius:12px;margin-bottom:12px">
        <source src="<?=safe($videos[0])?>" type="video/mp4">
      </video>
    <?php endif; ?>
    <div class="secTitle" style="font-weight:600;margin-top:0">Elevation</div>
    <div class="galleryGrid">
      <?php foreach($gallery as $gi=>$src): if ($gi===0 && $video) continue; ?>
        <img src="<?=safe($src)?>" onerror="this.src='<?=$BASE?>/assets/back.png'">
      <?php endforeach; ?>
    </div>
  </section>

  <!-- EMI -->
  <section id="emi" class="section panel pad">
    <div class="secTitle">Home Loan & EMI Calculator</div>
    <div class="emiWrap">
      <div>
        <div class="formRow">
          <label>Loan Amount (₹ Lakhs)</label>
          <input id="loan" type="range" min="10" max="200" step="1" value="<?= $price_min ? max(10, min(200,(int)$price_min)) : 50 ?>">
          <div><span id="loanVal"></span></div>
        </div>
        <div class="formRow">
          <label>Loan Tenure (Years)</label>
          <input id="tenure" type="range" min="1" max="30" step="1" value="5">
          <div><span id="tenureVal"></span></div>
        </div>
        <div class="formRow">
          <label>Interest Rate (p.a. %)</label>
          <input id="rate" type="range" min="6" max="15" step="0.1" value="9">
          <div><span id="rateVal"></span></div>
        </div>
        <div class="emiBox" style="margin-top:12px">
          <div>Monthly EMI: <b id="emiVal">—</b></div>
          <div class="muted">Total Amount Payable: <span id="totalVal">—</span></div>
        </div>
      </div>
      <div class="emiBox" style="text-align:center">
        <div class="pie" id="pie"></div>
        <div style="margin-top:12px">
          <div><span style="display:inline-block;width:12px;height:12px;background:#6ee7ff;border-radius:3px;margin-right:6px"></span> Principal: <b id="prinVal">—</b></div>
          <div><span style="display:inline-block;width:12px;height:12px;background:#a78bfa;border-radius:3px;margin-right:6px"></span> Interest: <b id="intVal">—</b></div>
        </div>
      </div>
    </div>
  </section>

  <!-- Map -->
 <!-- Map -->
<section id="map" class="section panel pad">
  <div class="secTitle">Know more about <?=safe($location ?: $city)?></div>
  <div id="proj-map" class="map"></div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var el = document.getElementById('proj-map');
  if (!el) return;

  var map = L.map('proj-map');
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  <?php if ($lat && $lng): ?>
    var lat = <?= json_encode((float)$lat) ?>, lng = <?= json_encode((float)$lng) ?>;
    map.setView([lat, lng], 16);
    L.marker([lat, lng]).addTo(map)
      .bindPopup(<?= json_encode(($prop['name'] ?? 'Project') . (!empty($prop['address']) ? '<br>'.htmlspecialchars($prop['address'], ENT_QUOTES) : '')) ?>);
  <?php else: ?>
    var mapsUrl = <?= json_encode($prop['maps_url'] ?? '') ?>;
    var addr    = <?= json_encode(($prop['address'] ?? '') ?: ($prop['location'] ?? (($prop['name'] ?? '').' '.($prop['city'] ?? '')))) ?>;

    var lat=null,lng=null,m=null;
    if (mapsUrl) {
      m = mapsUrl.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/) || mapsUrl.match(/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/);
      if (m) { lat = parseFloat(m[1]); lng = parseFloat(m[2]); }
    }

    if (lat!=null && lng!=null) {
      map.setView([lat,lng],16); L.marker([lat,lng]).addTo(map);
    } else if (addr) {
      fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(addr),
        { headers: { 'Accept': 'application/json' }})
        .then(r=>r.json()).then(a=>{
          if (a && a[0]) {
            lat=parseFloat(a[0].lat); lng=parseFloat(a[0].lon);
            map.setView([lat,lng],16); L.marker([lat,lng]).addTo(map);
          } else { map.setView([20.5937,78.9629],5); }
        }).catch(()=>map.setView([20.5937,78.9629],5));
    } else {
      map.setView([20.5937,78.9629],5);
    }
  <?php endif; ?>
});
</script>


  <!-- More projects -->
  <?php if ($siblings): ?>
  <section class="section">
    <div class="secTitle">More projects by <?=safe($builder)?></div>
    <div class="more">
      <?php foreach($siblings as $s): ?>
        <a class="card" href="<?=safe($s['details_url'])?>">
          <img src="<?=safe($s['gallery'][0] ?? ($BASE.'/assets/back.png'))?>" onerror="this.src='<?=$BASE?>/assets/back.png'">
          <div class="pad">
            <div style="font-weight:700"><?=safe($s['name']??'')?></div>
            <div class="muted" style="font-size:13px"><?=safe($s['location']??'')?></div>
            <div class="muted" style="margin-top:6px"><?=fmt_lakhs($s['price_min']??null)?><?=isset($s['price_max'])?' - '.fmt_lakhs($s['price_max']):''?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Ask -->
  <section id="ask" class="section ask">
    <h2 style="margin:0 0 8px">Have any Questions?</h2>
    <div class="muted">Connect with our property expert!</div>
    <form id="askForm" method="post" action="<?=$BASE?>/api/callback.php" onsubmit="return sendCallback(event)" style="max-width:640px;margin:14px auto 0">
      <input type="hidden" name="builder" value="<?=safe($builder)?>">
      <input type="hidden" name="property" value="<?=safe($name)?>">
      <div style="display:grid;grid-template-columns:1fr 140px 1fr;gap:10px">
        <input required name="name" placeholder="Name" style="padding:12px;border-radius:10px;border:1px solid var(--line);background:#0b1c27;color:#e6f0f6">
        <input value="+91" disabled style="padding:12px;border-radius:10px;border:1px solid var(--line);background:#0b1c27;color:#e6f0f6;text-align:center">
        <input required name="phone" placeholder="Mobile No." style="padding:12px;border-radius:10px;border:1px solid var(--line);background:#0b1c27;color:#e6f0f6">
      </div>
      <label style="display:block;margin-top:10px"><input type="checkbox" checked disabled> <span class="muted">I agree to be contacted</span></label>
      <div style="margin-top:10px"><button class="btn grad">GET CALL BACK</button></div>
      <div id="askOk" class="muted" style="display:none;margin-top:8px">✅ Thanks! We’ll call you shortly.</div>
    </form>
  </section>

  <!-- Disclaimer -->
  <section class="section panel pad">
    <div class="secTitle">Disclaimer</div>
    <div class="muted" style="line-height:1.6"><?=nl2br(safe($disclaimer))?></div>
  </section>

  <!-- Sticky CTA -->
  <div class="sticky">
    <div class="row container">
      <div><b><?=safe($name)?></b> — <span class="muted"><?=safe($location)?></span></div>
      <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <div class="price"><?=fmt_lakhs($price_min)?><?=($price_max!==null?' - '.fmt_lakhs($price_max):'')?></div>
        <a class="btn" href="<?=$wa?>" target="_blank" rel="noopener">WhatsApp</a>
        <button class="btn grad" onclick="openModal('<?=safe($name)?>')">Get Callback</button>
      </div>
    </div>
  </div>
</div>

<!-- Callback Modal -->
<div id="cbModal" class="gm-modal" role="dialog" aria-modal="true" aria-hidden="true">
  <div class="box">
    <h3 style="margin:0 0 8px">Get a Callback</h3>
    <form id="cbForm" method="post" action="<?=$BASE?>/api/callback.php" onsubmit="return sendCallback(event)">
      <input type="hidden" name="builder" value="<?=safe($builder)?>">
      <input type="hidden" id="cbProperty" name="property" value="<?=safe($name)?>">
      <label>Name<input required name="name" placeholder="Your name"></label>
      <label>Phone<input required name="phone" placeholder="+91…"></label>
      <label>Email><input type="email" name="email" placeholder="you@example.com"></label>
      <label>Message><textarea name="message" rows="3" placeholder="I’m interested in <?=safe($name)?>."></textarea></label>
      <div style="display:flex;gap:10px;margin-top:10px">
        <button type="button" class="btn" onclick="closeModal()">Cancel</button>
        <button class="btn grad">Submit</button>
      </div>
      <div id="cbOk" class="muted" style="display:none;margin-top:8px">✅ Thanks! We’ll call you shortly.</div>
    </form>
  </div>
</div>

<script>
// ----- tabs active on scroll -----
const tabLinks=[...document.querySelectorAll('.tabs a')];
const ids=tabLinks.map(a=>a.getAttribute('href'));
function setActive(){
  let fromTop=window.scrollY+80;
  let current=ids[0];
  ids.forEach(id=>{
    const el=document.querySelector(id);
    if(el && el.offsetTop<=fromTop) current=id;
  });
  tabLinks.forEach(a=>a.classList.toggle('active', a.getAttribute('href')===current));
}
window.addEventListener('scroll', setActive); setActive();

// ----- media carousel (video first) -----
const frames=[];
<?php if ($video): ?>frames.push({type:'video',src:<?=json_encode($video)?>,poster:<?=json_encode($gallery[0] ?? ($BASE.'/assets/back.png'))?>});<?php endif; ?>
<?php foreach ($gallery as $src): ?>frames.push({type:'img',src:<?=json_encode($src)?>});<?php endforeach; ?>
let mIdx=0; const hero=document.getElementById('hero');
function showMedia(i){
  mIdx=(i+frames.length)%frames.length;
  hero.querySelectorAll('video,img').forEach(n=>n.remove());
  const f=frames[mIdx];
  if(f.type==='video'){
    const v=document.createElement('video'); v.id='heroVideo'; v.controls=true; v.playsInline=true; v.preload='metadata'; v.poster=f.poster||'';
    const s=document.createElement('source'); s.src=f.src; s.type='video/mp4'; v.appendChild(s); hero.prepend(v);
  } else {
    const im=document.createElement('img'); im.src=f.src; im.onerror=()=>{im.src='<?=$BASE?>/assets/back.png'}; hero.prepend(im);
  }
}
function mediaStep(d){ showMedia(mIdx+d); }
showMedia(0);

// ----- floor plan tabs -----
const bhkTabs=document.getElementById('bhkTabs');
if (bhkTabs){
  bhkTabs.addEventListener('click', e=>{
    const t=e.target.closest('.tab'); if(!t) return;
    const bhk=t.dataset.bhk;
    document.querySelectorAll('.bhkTabs .tab').forEach(x=>x.classList.toggle('active',x===t));
    document.querySelectorAll('.fpGroup').forEach(g=>g.style.display = g.dataset.bhk===bhk ? '' : 'none');
  });
}

// ----- EMI calculator -----
const loan=document.getElementById('loan'), tenure=document.getElementById('tenure'), rate=document.getElementById('rate');
const loanVal=document.getElementById('loanVal'), tenureVal=document.getElementById('tenureVal'), rateVal=document.getElementById('rateVal');
const emiVal=document.getElementById('emiVal'), totalVal=document.getElementById('totalVal'), prinVal=document.getElementById('prinVal'), intVal=document.getElementById('intVal'), pie=document.getElementById('pie');

function formatMoneyL(v){ return '₹ ' + (v>=100? (v/100).toFixed(1)+' Cr' : v.toFixed(2)+' L'); }
function compute(){
  const P = parseFloat(loan.value);     // in Lakhs
  const n = parseInt(tenure.value)*12;  // months
  const r = parseFloat(rate.value)/1200;// monthly
  const emi = r ? P * (r*(1+r)**n) / ((1+r)**n - 1) : P/n;
  const total = emi*n;
  const interest = total - P;
  loanVal.textContent = formatMoneyL(P);
  tenureVal.textContent = tenure.value + ' years';
  rateVal.textContent = rate.value + '%';
  emiVal.textContent = formatMoneyL(emi);
  totalVal.textContent = formatMoneyL(total);
  prinVal.textContent = formatMoneyL(P);
  intVal.textContent = formatMoneyL(interest);
  const pct = Math.round(P/total*100);
  pie.style.setProperty('--pct', pct + '%');
}
[loan,tenure,rate].forEach(el=>el.addEventListener('input',compute)); compute();

// ----- modal -----
function openModal(prop){ document.getElementById('cbProperty').value = prop; document.getElementById('cbModal').classList.add('show'); document.body.style.overflow='hidden'; }
function closeModal(){ document.getElementById('cbModal').classList.remove('show'); document.body.style.overflow=''; }
function sendCallback(e){
  e.preventDefault();
  const f=e.target;
  fetch(f.action,{method:'POST',body:new FormData(f)})
    .then(r=>r.ok?r.text():Promise.reject())
    .then(()=>{ (document.getElementById('cbOk')||document.getElementById('askOk')).style.display='block'; setTimeout(()=>closeModal(),1200); })
    .catch(()=>alert('Failed. Try again.'));
  return false;
}
</script>

<?php
$FOOTER = __DIR__ . '/partials/footer.php';   // page.php and /partials are at the same level
if (is_file($FOOTER)) include $FOOTER;
?>

</body>
</html>
