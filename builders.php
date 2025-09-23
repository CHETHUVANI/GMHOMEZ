<?php
/**
 * GM HOMEZ — Builders/City listing page (unified)
 * - Builder mode: /builders.php?builder=Prestige Group
 * - City mode   : /builders.php?city=Bangalore   (title becomes "For Sale in Bangalore")
 */

error_reporting(E_ALL); ini_set('display_errors', 1);

// ===== bootstrap =====
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); if ($BASE==='/'||$BASE==='\\') $BASE='';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/render.php';

/* ---------------- helpers (guarded) ---------------- */
if (!function_exists('read_json')) {
  function read_json($file){
    if (!is_file($file)) return null;
    $j = json_decode(file_get_contents($file), true);
    return is_array($j) ? $j : null;
  }
}
if (!function_exists('read_properties')) {
  function read_properties(){ return read_json(__DIR__ . '/data/properties.json') ?: []; }
}
if (!function_exists('read_builders')) {
  function read_builders(){ return read_json(__DIR__ . '/data/builders.json') ?: []; }
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
if (!function_exists('normalize_project_urls')) {
  function normalize_project_urls($p, $BASE){
    if(!empty($p['gallery']) && is_array($p['gallery'])){
      foreach($p['gallery'] as $i=>$u) $p['gallery'][$i] = url_abs($u,$BASE);
    }
    if(!empty($p['details_url'])) $p['details_url'] = url_abs($p['details_url'],$BASE);
    return $p;
  }
}

/* ---------------- routing / mode ---------------- */
$builder = isset($_GET['builder']) ? trim($_GET['builder']) : null;
$city    = isset($_GET['city'])    ? trim($_GET['city'])    : null;

// ---- Category mode (from ?cat=...)
$cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$catAdminLabels = [
  'all'        => 'Properties Admin',
  'apartments' => 'Apartments Admin',
  'villas'     => 'Villas Admin',
  'new'        => 'New Apartments Admin',
  'upcoming'   => 'Upcoming Projects Admin',
  'ready'      => 'Ready to Move Admin',
];

$catTitles = [
  'all'        => 'Properties in India',
  'apartments' => 'Apartments for Sale',
  'villas'     => 'Villas in India',
  'new'        => 'New Apartments',
  'upcoming'   => 'Upcoming Projects',
  'ready'      => 'Ready to Move Homes',
];

$mode = $city ? 'city' : ($builder ? 'builder' : ($cat ? 'category' : 'builder'));

$pageTitle = $mode === 'city'
  ? ("For Sale in " . $city)
  : ($mode === 'builder' ? ($builder ?: "Projects") : ($catTitles[$cat] ?? "Properties"));

/* ---------------- data ---------------- */
$allProps = array_map(fn($p)=>normalize_project_urls($p,$BASE), read_properties());

// Filter projects for this view
if ($mode === 'builder') {
  // Builder → only this builder
  $builder = $builder ?: 'Prestige Group';
  $props   = array_values(array_filter($allProps, fn($p)=>isset($p['builder']) && strcasecmp($p['builder'],$builder)===0));

} elseif ($mode === 'city') {
  // City → all builders, only this city
  $props   = array_values(array_filter($allProps, fn($p)=>isset($p['city']) && strcasecmp($p['city'],$city)===0));

} else { // category
  $props = $allProps;
  $nowYM = intval(date('Ym'));

  $props = array_values(array_filter($props, function($p) use ($cat, $nowYM) {
    $status = strtolower(trim($p['status'] ?? ''));
    $name   = $p['name'] ?? '';
    $ym     = 0;
    if (!empty($p['possession_ym']) && preg_match('~^(\d{4})-(\d{2})$~', $p['possession_ym'], $m)) {
      $ym = intval($m[1].$m[2]); // YYYYMM
    }
    if ($cat === 'upcoming')   return in_array($status, ['ongoing'], true);
    if ($cat === 'ready')      return in_array($status, ['ready','completed'], true);
    if ($cat === 'new')        return $ym >= $nowYM;
    if ($cat === 'apartments') return preg_match('~apartment|flat~i', $name);
    if ($cat === 'villas')     return preg_match('~villa|row house|bungalow~i', $name);
    return true; // 'all' or unknown -> show everything
  }));
}


/* ---------------- builder stats / logo (builder-mode only) ---------------- */
$bstats = [];
$years_exp = null;

if ($mode === 'builder') {
  $BUILDER_SLUGS = [
    'Prestige Group'          => 'prestige-group',
    'Sobha Limited'           => 'sobha-limited',
    'Kolte Patil Developers'  => 'kolte-patil-developers',
    'Godrej Properties'       => 'godrej-properties',
    'Brigade Group'           => 'brigade-group',
  ];
  $builderName = $builder;
  $slug  = $BUILDER_SLUGS[$builderName] ?? strtolower(preg_replace('/[^a-z0-9]+/','-', $builderName));
  $JSON  = __DIR__ . '/data/builders/' . $slug . '.json';
  $DATA  = is_file($JSON) ? (json_decode(file_get_contents($JSON), true) ?: []) : [];

  // read builders.json summary if available
  $builders = read_builders();
  $bstats = $builders[$builder] ?? [
    "logo" => "$BASE/assets/$builder-logo.png",
    "founded_year"   => (int)($DATA['founded_year'] ?? 1990),
    "total_projects" => count($props),
    "ongoing_projects" => array_reduce($props, fn($a,$p)=>$a + (isset($p['status']) && strtolower($p['status'])==='ongoing' ? 1 : 0), 0)
  ];
  $bstats['logo'] = url_abs($bstats['logo'] ?? '', $BASE);
  $years_exp = (int)date('Y') - (int)($bstats['founded_year'] ?? date('Y'));
}

// Quick stats for city-mode header badges
$totalProjects   = count($props);
$buildersCovered = count(array_unique(array_map(function($p){ return strtolower($p['builder'] ?? ''); }, $props)));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=htmlspecialchars($pageTitle)?> · GM HOMEZ</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{ --bg:#0b1620; --panel:#0f2430; --muted:#9fb2c0; --line:rgba(148,163,184,.18); --brand1:#6ee7ff; --brand2:#a78bfa; }
  *{box-sizing:border-box} body{margin:0;background:var(--bg);color:#e6f0f6;font-family:Poppins,system-ui}
  a{color:#8ddfff;text-decoration:none}
  .container{max-width:1200px;margin:0 auto;padding:16px}

  /* top */
  .top{background:#0c1b25;border-bottom:1px solid var(--line)}
  .topwrap{display:flex;align-items:center;gap:14px}
  .logo{display:flex;align-items:center;gap:10px}
  .logo img{height:38px;width:auto;border-radius:8px;background:#09202a}
  .grow{flex:1}
  .search{display:flex;gap:8px;align-items:center}
  .search input{flex:1;padding:10px 12px;border-radius:10px;border:1px solid var(--line);background:#0a1922;color:#e6f0f6}
  .btn{padding:10px 14px;border-radius:12px;border:1px solid var(--line);background:#102a37;color:#e6f0f6;cursor:pointer}
  .btn.phone{font-weight:600}
  .btn.grad{border:none;background:linear-gradient(90deg,var(--brand1),var(--brand2));background-size:200% 200%;animation:movegrad 4s ease infinite}
  @keyframes movegrad{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}

  /* hero */
  .hero{border-bottom:1px solid var(--line);background:#0c1b25}
  .heroHead{display:flex;align-items:center;gap:16px}
  .heroHead img{height:54px;width:auto;border-radius:12px;background:#0a1d26}
  .heroStats{display:flex;gap:18px;flex-wrap:wrap;margin-top:10px;justify-content:center}
  .stat{background:linear-gradient(180deg,#0f2430,#0b1c27);border:1px solid var(--line);padding:10px 14px;border-radius:12px;text-align:center}
  .stat b{font-size:18px}

  /* filters */
  .filters{padding:14px 0;border-bottom:1px solid var(--line);display:grid;gap:10px}
  .row{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
  .chip{display:flex;align-items:center;background:#0f2430;border:1px solid var(--line);padding:6px 10px;border-radius:12px}
  .chip select{margin-left:8px;background:#0b1c27;color:#e6f0f6;border:1px solid var(--line);border-radius:10px;padding:8px 10px}
  .reset{padding:8px 10px;border-radius:10px;border:1px solid var(--line);background:#0f2430;color:#e6f0f6;cursor:pointer}
  .right{margin-left:auto}

  /* cards */
  .card{display:grid;grid-template-columns:340px 1fr 180px;gap:14px;background:#0f2430;border:1px solid var(--line);border-radius:14px;overflow:hidden}
  .grid{display:grid;gap:14px;margin:16px 0}

  /* carousel */
  .cover{position:relative}
  .carousel{position:relative;width:100%;height:240px;overflow:hidden}
  .carousel img{position:absolute;inset:0;width:100%;height:240px;object-fit:cover;opacity:0;transition:opacity .5s ease}
  .carousel img.show{opacity:1}
  .dots{position:absolute;right:8px;bottom:8px;display:flex;gap:6px;z-index:2}
  .dot{width:8px;height:8px;border-radius:999px;background:rgba(255,255,255,.45);cursor:pointer}
  .dot.on{background:#fff}

  .badges{position:absolute;left:8px;bottom:8px;display:flex;gap:8px;z-index:2}
  .tag{display:flex;align-items:center;gap:6px;background:rgba(0,0,0,.45);color:#fff;padding:6px 10px;border-radius:10px;font-size:12px}

  .body{padding:14px 12px}
  .loc{color:var(--muted);font-size:13px;margin:4px 0 8px}
  .pos{display:inline-block;background:#0b1c27;padding:6px 10px;border:1px dashed var(--line);border-radius:10px;color:#cfe6f3;font-size:12px;margin-bottom:10px}
  .tbl{width:100%;border-collapse:collapse;margin-top:6px;color:#dfeaf1}
  .tbl td{padding:8px 0;border-bottom:1px solid rgba(148,163,184,.12);font-size:14px}
  .tbl td:nth-child(1){width:70px;color:#cfe6f3}
  .tbl td:nth-child(2){color:#a9c3d1}
  .tbl td:nth-child(3){text-align:right;font-weight:600}
  .price{display:flex;align-items:center;justify-content:flex-end;padding:14px}
  .price .val{font-size:20px;color:#ffd0a6;font-weight:700}
  .acts{display:flex;gap:10px;justify-content:flex-end;margin-top:10px}
  .btn.ghost{background:transparent;border:1px solid var(--line)}
  .muted{color:var(--muted)}
  .empty{padding:40px;border:1px dashed var(--line);border-radius:12px;text-align:center}

  /* footer */
  .foot{margin-top:24px;border-top:1px solid var(--line);background:#0c1b25}
  .foot .wrap{max-width:1200px;margin:0 auto;padding:16px;display:flex;gap:12px;align-items:center;justify-content:space-between;flex-wrap:wrap}
  .footnav{display:flex;gap:14px;align-items:center}

  /* namespaced callback modal (avoid Bootstrap clash) */
  .gm-modal{position:fixed;inset:0;background:rgba(0,0,0,.65);display:none;align-items:center;justify-content:center;z-index:9999}
  .gm-modal.show{display:flex}
  .gm-modal .box{background:#0f2430;border:1px solid var(--line);padding:18px;border-radius:16px;min-width:320px;max-width:420px;max-height:85vh;overflow:auto}
  .gm-modal label{display:block;margin-top:10px}
  .gm-modal input,.gm-modal textarea{width:100%;margin-top:6px;padding:10px;border-radius:10px;border:1px solid var(--line);background:#0b1c27;color:#e6f0f6}
  .nav-chips{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:10px 0 14px}
  /* --- centered, gradient search --- */
.search-wrap{max-width:720px;margin:10px auto 16px;}
.search-wrap .search-row{display:flex;align-items:center;gap:10px;}
.search-wrap .search-input{
  flex:1 1 auto; min-width:0; padding:12px 16px; border-radius:999px;
  border:1px solid rgba(255,255,255,.18); color:#fff;
  background:linear-gradient(135deg,#0f2b3a 0%,#1e3a5f 35%,#354a86 70%,#6d28d9 100%);
  outline:none;
}
.search-wrap .search-input::placeholder{color:rgba(255,255,255,.75);}
.search-wrap .btn-search{
  padding:12px 18px; border:0; border-radius:999px; font-weight:700; cursor:pointer;
  background:linear-gradient(135deg,#67b3ff,#b7a6ff); color:#0b1620;
  box-shadow:0 2px 10px rgba(0,0,0,.15);
}
.search-wrap .btn-search:hover{filter:brightness(1.05);}

</style>
</head>
<body>
<div class="search-wrap">
  <form class="search-row" onsubmit="runSearch(); return false;">
    <input id="q" class="search-input"
           placeholder="Search name/location/city… (e.g., ‘Sun Crest’ or ‘Electronic City’)">
    <button type="submit" class="btn-search">Search</button>
  </form>
</div>

<div class="container nav-chips">

<?php if ($mode === 'city' && $city): ?>
  <button class="btn"
          onclick="location.href='<?=$BASE?>/admin/builder-projects.php?city=<?=urlencode($city)?>'">
    <?=htmlspecialchars($city)?> Admin
  </button>

<?php elseif ($mode === 'builder' && $builder): ?>
  <button class="btn"
          onclick="location.href='<?=$BASE?>/admin/builder-projects.php?builder=<?=urlencode($builder)?>'">
    Builder Admin
  </button>

<?php else: /* category pages */ ?>
  <button class="btn"
          onclick="location.href='<?=$BASE?>/admin/builder-projects.php'">
    <?= htmlspecialchars($catAdminLabels[$cat] ?? ($catTitles[$cat] ?? 'Properties Admin')) ?>
  </button>
<?php endif; ?>


<button class="btn" onclick="location.href='<?=$BASE?>/index.php'">Home</button>
<button class="btn grad" onclick="location.href='<?=$BASE?>/admin/login.php'">Login</button>
<button class="btn grad" onclick="location.href='<?=$BASE?>/admin/signup.php'">Sign&nbsp;Up</button>
<button class="btn phone" onclick="location.href='tel:+919999999999'">📞</button>
</div>


<!-- ===== Hero ===== -->
<div class="hero">
  <div class="container">
    <div class="heroHead">
      <?php if ($mode==='builder' && !empty($bstats['logo'])): ?>
        <img src="<?=htmlspecialchars($bstats['logo'])?>" alt="<?=htmlspecialchars($builder)?> logo" onerror="this.style.display='none'">
      <?php endif; ?>
      <div style="width:100%">
        <h1 style="margin:0;font-size:28px;text-align:center"><?=htmlspecialchars($pageTitle)?></h1>

        <div class="heroStats">
          <?php if ($mode==='builder'): ?>
            <div class="stat"><div class="muted">Years of Experience</div><b><?=$years_exp?></b></div>
            <div class="stat"><div class="muted">Total Projects</div><b><?= (int)($bstats['total_projects'] ?? 0) ?></b></div>
            <div class="stat"><div class="muted">Ongoing Projects</div><b><?= (int)($bstats['ongoing_projects'] ?? 0) ?></b></div>
          <?php else: ?>
            <div class="stat"><div class="muted">Total Projects</div><b><?=$totalProjects?></b></div>
            <div class="stat"><div class="muted">Builders Covered</div><b><?=$buildersCovered?></b></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ===== Filters & Sort ===== -->
<div class="container">
  <div class="filters">
    <div class="row">
      <label class="chip"><span>BHK</span>
        <select id="bhkSel" onchange="applyFilters()">
          <option value="">Any</option><option value="1">1 BHK</option><option value="2">2 BHK</option><option value="3">3 BHK</option><option value="4">4+ BHK</option>
        </select>
      </label>
      <label class="chip"><span>Budget</span>
        <select id="budSel" onchange="applyFilters()">
          <option value="">Any</option>
          <option value="0-50">₹0–50L</option><option value="50-75">₹50–75L</option><option value="75-100">₹75L–1Cr</option>
          <option value="100-150">₹1–1.5Cr</option><option value="150-200">₹1.5–2Cr</option><option value="200-9999">₹2Cr+</option>
        </select>
      </label>
      <label class="chip"><span>Possession</span>
        <select id="posSel" onchange="applyFilters()">
          <option value="">Any</option><option value="0">Ready to Move</option><option value="6">Within 6 months</option><option value="12">Within 12 months</option><option value="24">Within 24 months</option>
        </select>
      </label>
      <label class="chip"><span>City</span>
        <select id="citySel" onchange="applyFilters()"><option value="">All Cities</option></select>
      </label>
      <label class="chip"><span>Property-Type</span>
      <select id="proSel" onchange="applyFilters()"><option value="1">Apartments</option>
       <option value="2">Villas</option><option value="3">Plots</option><option value="4">studio</option></select>

      </label>




      <label class="chip"><span>More</span>
        <select id="moreSel" onchange="applyFilters()">
          <option value="">None</option><option value="3d">Has 3D</option><option value="vid">Has Video</option><option value="photo10">10+ Photos</option>
        </select>
      </label>
      <button class="reset" onclick="resetFilters()">Reset</button>
      <div class="right">Sort by:
        <select id="sort" onchange="applyFilters()">
          <option value="relevance">Relevance</option><option value="priceLow">Price: Low</option><option value="priceHigh">Price: High</option><option value="possession">Possession Date</option>
        </select>
      </div>
    </div>
    <div class="muted" id="countLine"></div>
  </div>

  <!-- results -->
  <div id="results" class="grid"></div>
  <?php if (empty($props)): ?>
    <div class="empty">No projects found for <?=htmlspecialchars($pageTitle)?> yet.</div>
  <?php endif; ?>
</div>

<!-- ===== Callback Modal (namespaced) ===== -->
<div id="cbModal" class="gm-modal" role="dialog" aria-modal="true" aria-hidden="true">
  <div class="box">
    <h3 style="margin:0 0 8px">Get a Callback</h3>
    <form id="cbForm" method="post" action="<?=$BASE?>/api/callback.php" onsubmit="return sendCallback(event)">
      <!-- In builder mode, include builder; in city mode leave blank (we'll send property name) -->
      <input type="hidden" name="builder" value="<?= $mode==='builder' ? htmlspecialchars($builder) : '' ?>">
      <input type="hidden" id="cbProperty" name="property" value="">
      <label>Name<input required name="name" placeholder="Your name"></label>
      <label>Phone<input required name="phone" placeholder="+91…"></label>
      <label>Email<input type="email" name="email" placeholder="you@example.com"></label>
      <label>Message<textarea name="message" rows="3" placeholder="I’m interested in this project."></textarea></label>
      <div class="acts"><button type="button" class="btn" onclick="closeModal()">Cancel</button><button class="btn grad">Submit</button></div>
      <div id="cbOk" class="muted" style="display:none;margin-top:8px">✅ Thanks! We’ll call you shortly.</div>
    </form>
  </div>
</div>

<!-- ===== Footer ===== -->
<footer class="foot">
  <div class="wrap">
    <div class="footnav">
      <a href="<?=$BASE?>/index.php">Home</a>
      <?php
        $waText = $mode==='builder' ? $builder : $pageTitle;
      ?>
      <a href="https://wa.me/917676536261?text=Hi%20GM%20HOMEZ%2C%20I%27m%20interested%20in%20<?=urlencode($waText)?>">WhatsApp</a>
      <a href="https://instagram.com/your_instagram" target="_blank" rel="noopener">Instagram</a>
    </div>
    <div class="muted">© <span id="y"></span> GM HOMEZ. All rights reserved.</div>
  </div>
</footer>

<script>
// footer year
document.getElementById('y').textContent = new Date().getFullYear();

// ===== data from PHP to JS =====
const MODE    = <?= json_encode($mode) ?>;          // "builder" | "city"
const BUILDER = <?= json_encode($builder) ?>;        // may be null in city-mode
const RAW     = <?php echo json_encode($props, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;
let LIST = RAW.slice();

// ---- helpers ----
const fmtPrice=(min,max)=>{
  const t=v=>v>=100?`₹ ${(v/100).toFixed(1)} Cr`:`₹ ${(+v||0).toFixed(2)} L`;
  if(min==null && max==null) return '—';
  if(min!=null && max!=null) return `${t(min)} - ${t(max)}`;
  return t(min!=null?min:max);
};
const monthsUntil=(ym)=>{
  if(!ym) return 9999;
  const m = String(ym).split(/[-/]/);
  if(m.length<2) return 9999;
  const Y=+m[0], M=+m[1];
  const n=new Date();
  return (Y-n.getFullYear())*12+(M-(n.getMonth()+1));
};
const uniq=a=>[...new Set((a||[]).filter(Boolean))];
function formatYM(ym){
  if(!ym) return '';
  const m = String(ym).split(/[-/]/); if(m.length<2) return ym;
  return `${m[0]}’${m[1].padStart(2,'0')}`;
}
function overlap(a1,a2,b1,b2){ const lo=Math.max(a1,b1), hi=Math.min(a2,b2); return hi>=lo; }

// build City options once
function buildCityOptions(){
  const sel=document.getElementById('citySel');
  const cities=uniq(RAW.map(p=>p.city)).sort((a,b)=>a.localeCompare(b));
  for(const c of cities){const o=document.createElement('option'); o.value=c; o.textContent=c; sel.appendChild(o);}
  // If we're in city-mode, preselect that city
  <?php if ($mode==='city'): ?>
    sel.value = <?= json_encode($city) ?>;
  <?php endif; ?>
}

// ---- CAROUSEL per card ----
function setupCarousel(root){
  const imgs = root.querySelectorAll('img');
  const dots = root.querySelectorAll('.dot');
  if (imgs.length<=1) return;
  let i = 0;
  imgs[0].classList.add('show'); dots[0]?.classList.add('on');
  const step = ()=>{
    imgs[i].classList.remove('show'); dots[i]?.classList.remove('on');
    i = (i+1)%imgs.length;
    imgs[i].classList.add('show'); dots[i]?.classList.add('on');
  };
  let t = setInterval(step, 3000);
  root.addEventListener('mouseenter', ()=>clearInterval(t));
  root.addEventListener('mouseleave', ()=>{ t = setInterval(step, 3000); });
  dots.forEach((d,idx)=>d.addEventListener('click', ()=>{ clearInterval(t); imgs[i].classList.remove('show'); dots[i]?.classList.remove('on'); i=idx; imgs[i].classList.add('show'); dots[i]?.classList.add('on'); }));
}

// ---- RENDER ----
function render(){
  const box=document.getElementById('results'); box.innerHTML='';
  document.getElementById('countLine').textContent = LIST.length + ' project(s)';

  LIST.forEach(p=>{
    try{
      const photos=(Array.isArray(p.gallery)?p.gallery.length:0)||(p.photos||0);
      const videos=p.videos||0;

      // units: prefer p.units; fallback to single top-level
      let units = Array.isArray(p.units) && p.units.length ? p.units : [];
      if (!units.length && (p.bhk || p.area_min || p.price_min)) {
        units = [{
          bhk: p.bhk || 1,
          area_min: p.area_min || 0,
          area_max: p.area_max || (p.area_min||0),
          price_min: p.price_min ?? null,
          price_max: p.price_max ?? null
        }];
      }
      const bhkRows = units.map(u=>
        `<tr><td>${u.bhk}BHK</td><td>${u.area_min||0} - ${u.area_max||0} sqft</td><td>${fmtPrice(u.price_min, u.price_max)}</td></tr>`
      ).join('');

      // gallery images (force array)
      const imgs = (Array.isArray(p.gallery) && p.gallery.length ? p.gallery : ['<?=$BASE?>/assets/back.png']);
      const dots = imgs.length>1 ? `<div class="dots">${imgs.map((_,i)=>`<div class="dot" aria-label="slide ${i+1}"></div>`).join('')}</div>` : '';

      // details url (fallback if missing id/details_url)
      const details = p.details_url || '<?=$BASE?>/project.php?id=' + encodeURIComponent(p.id || String(p.name||'').toLowerCase().replace(/\s+/g,'-'));

      // right-hand label: builder name (in city mode), or fixed builder (in builder mode)
      const rightLabel = (MODE==='builder') ? (BUILDER || '') : (p.builder || '');

      const card=document.createElement('div'); card.className='card';
      card.innerHTML = `
        <div class="cover">
          <div class="carousel">
            ${imgs.map((src,i)=>`<img src="${src}" alt="" ${i===0?'class="show"':''} onerror="this.src='<?=$BASE?>/assets/back.png'">`).join('')}
            ${dots}
          </div>
          <div class="badges"><div class="tag">🌅 ${photos}</div><div class="tag">🎬 ${videos}</div>${p.has3d?'<div class="tag">🧊 3D</div>':''}</div>
        </div>
        <div class="body">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center">
            <div>
              <div style="font-weight:700">${p.name||''}</div>
              <div class="loc">📍 ${p.location||''}</div>
              ${p.possession_ym?`<span class="pos">Possession starts from: ${formatYM(p.possession_ym)}</span>`:''}
            </div>
            <div class="muted" style="text-align:right">${rightLabel}</div>
          </div>
          ${units.length?`<table class="tbl">${bhkRows}</table>`:''}
        </div>
        <div>
          <div class="price"><span class="val">${fmtPrice(p.price_min,p.price_max)}</span></div>
          <div class="acts">
            <a class="btn ghost" href="${details}">View Details</a>
            <button class="btn grad" onclick="openModal('${(p.name||'')}')">Get Callback</button>
          </div>
        </div>`;
      box.appendChild(card);
      const car = card.querySelector('.carousel'); if (car) setupCarousel(car);
    }catch(e){
      console.error('Render error for project:', p, e);
    }
  });
}

// ---- FILTERS / SEARCH ----
function applyFilters(){
  const q = document.getElementById('q').value.trim().toLowerCase();
  const bhkSel=document.getElementById('bhkSel').value, budSel=document.getElementById('budSel').value, posSel=document.getElementById('posSel').value, citySel=document.getElementById('citySel').value, moreSel=document.getElementById('moreSel').value;

  LIST = RAW.filter(p=>{
    if(citySel && p.city!==citySel) return false;

    const wantBhk=+bhkSel||0;
    if(wantBhk){
      const set=new Set((p.units||[]).map(u=>u.bhk>=4?4:u.bhk));
      if (!set.size && p.bhk) set.add(p.bhk>=4?4:p.bhk);
      if(!set.has(wantBhk)) return false;
    }

    if(budSel){
      const [a,b]=budSel.split('-').map(Number);
      const pmin=p.price_min??0, pmax=p.price_max??99999;
      let ok= !(pmax < a || pmin > b);
      if(!ok){
        ok=(p.units||[]).some(u=>{
          const umin=u.price_min??pmin, umax=u.price_max??pmax;
          return !(umax < a || umin > b);
        });
      }
      if(!ok) return false;
    }

    if(posSel){
      const m=monthsUntil(p.possession_ym);
      if(posSel==='0'){ const ready=((p.status&&/ready/i.test(p.status))||m<=0); if(!ready) return false; }
      else if(!(m<=+posSel)) return false;
    }

    if(moreSel==='3d' && !p.has3d) return false;
    if(moreSel==='vid' && !(p.videos>0)) return false;
    if(moreSel==='photo10' && !((p.gallery?.length||0)>=10 || (p.photos||0)>=10)) return false;

    if(q){
      const hay=`${p.name||''} ${p.location||''} ${p.city||''}`.toLowerCase();
      for(const term of q.split(/\s+/).filter(Boolean)){ if(!hay.includes(term)) return false; }
    }
    return true;
  });

  const sort=document.getElementById('sort').value;
  if(sort==='priceLow') LIST.sort((a,b)=>(a.price_min||0)-(b.price_min||0));
  if(sort==='priceHigh') LIST.sort((a,b)=>(b.price_max||0)-(a.price_max||0));
  if(sort==='possession') LIST.sort((a,b)=>monthsUntil(a.possession_ym)-monthsUntil(b.possession_ym));
  render();
}
function resetFilters(){ ['bhkSel','budSel','posSel','citySel','moreSel','sort'].forEach(id=>{document.getElementById(id).value = id==='sort'?'relevance':''}); document.getElementById('q').value=''; LIST=RAW.slice(); render(); }
function runSearch(){ applyFilters(); }

// ---- modal (namespaced) ----
function openModal(prop){ document.getElementById('cbProperty').value = prop; document.getElementById('cbModal').classList.add('show'); document.body.style.overflow = 'hidden'; }
function closeModal(){ document.getElementById('cbModal').classList.remove('show'); document.body.style.overflow = ''; }
function sendCallback(e){ e.preventDefault(); const f=e.target; fetch(f.action,{method:'POST',body:new FormData(f)}).then(r=>r.ok?r.text():Promise.reject()).then(()=>{ document.getElementById('cbOk').style.display='block'; setTimeout(()=>closeModal(),1200); }).catch(()=>alert('Failed. Try again.')); return false; }

// init
document.addEventListener('DOMContentLoaded', ()=>{
  buildCityOptions();
  render();
  document.getElementById('q').addEventListener('keydown',e=>{ if(e.key==='Enter'){ e.preventDefault(); runSearch(); }});
});
</script>
<?php
$FOOTER = __DIR__ . '/partials/footer.php';   // page.php and /partials are at the same level
if (is_file($FOOTER)) include $FOOTER;
?>

</body>
</html>
