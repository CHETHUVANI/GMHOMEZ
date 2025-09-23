<?php
// lib/render.php — helpers + site header/footer for GM HOMEZ

// ---- optional config (defines constants like PROPS_JSON, TEAM_JSON, UPLOAD_URL)
if (!defined('ROOT_DIR')) {
  $cfg = __DIR__ . '/../config.php';
  if (is_file($cfg)) require_once $cfg;
}

/* ===========================================================
   Small helpers (escaping, base URL, JSON, paths)
   =========================================================== */
if (!function_exists('h')) {
  function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

if (!function_exists('render_base_url')) {
  // works both in subfolder and document root; no trailing slash
  function render_base_url() {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $base = rtrim(str_replace('\\','/', dirname($script)), '/');
    return ($base === '' || $base === '/' ) ? '' : $base;
  }
}

if (!function_exists('json_load')) {
  function json_load($path) {
    if (!$path || !is_file($path)) return [];
    $s = @file_get_contents($path);
    if ($s === false || $s === '') return [];
    $d = json_decode($s, true);
    return is_array($d) ? $d : [];
  }
}
if (!function_exists('json_save')) {
  function json_save($path, $data) {
    if (!$path) return false;
    $tmp = $path . '.tmp';
    @file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
    @rename($tmp, $path);
    return true;
  }
}

/* ===========================================================
   Property helpers
   =========================================================== */
if (!function_exists('prop_image_urls')) {
  function prop_image_urls(array $p): array {
    $base = render_base_url();
    $upl  = defined('UPLOAD_URL') ? rtrim(UPLOAD_URL, '/') : ($base . '/uploads');
    $imgs = [];

    if (!empty($p['images']) && is_array($p['images'])) {
      foreach ($p['images'] as $fn) {
        $fn = basename(trim((string)$fn));
        if ($fn !== '') $imgs[] = $upl . '/' . rawurlencode($fn);
      }
    }
    if (!$imgs) {
      $file = $p['image'] ?? $p['img'] ?? '';
      $file = $file ? basename($file) : '';
      if ($file) $imgs[] = $upl . '/' . rawurlencode($file);
    }
    if (!$imgs) $imgs[] = $base . '/assets/back.png';
    return $imgs;
  }
}

if (!function_exists('read_properties')) {
  function read_properties(): array {
    $file = defined('PROPS_JSON') ? PROPS_JSON : (__DIR__ . '/../data/properties.json');
    return json_load($file);
  }
}
if (!function_exists('read_team')) {
  function read_team(): array {
    $file = defined('TEAM_JSON') ? TEAM_JSON : (__DIR__ . '/../data/team.json');
    return json_load($file);
  }
}

/* ===========================================================
   Card renderer (with simple in-image slider nav)
   =========================================================== */
if (!function_exists('renderPropertyCard')) {
  function renderPropertyCard(array $p): void {
    $base  = render_base_url();
    $imgs  = prop_image_urls($p);

    $id    = (string)($p['id'] ?? '');
    $title = $p['title'] ?? 'Property';
    $loc   = $p['location'] ?? ($p['loc'] ?? '');
    $price = isset($p['price']) && $p['price'] !== '' ? ('₹' . number_format((int)$p['price'])) : '₹—';
    $detailsUrl = $base . '/view.php?id=' . rawurlencode($id);

    echo '<article class="card g-bord">';
      echo '<div class="card-media" data-images=\'' . h(json_encode($imgs, JSON_UNESCAPED_SLASHES)) . '\'>';
        echo '<img class="card-media-img" src="' . h($imgs[0]) . '" alt="' . h($title) . '" loading="lazy">';
        echo '<button class="card-media-nav prev" aria-label="Previous image">❮</button>';
        echo '<button class="card-media-nav next" aria-label="Next image">❯</button>';
      echo '</div>';
      echo '<div class="card-body" style="padding:14px 16px 18px">';
        echo '<div class="prop-title" style="font-weight:700;font-size:18px;margin:4px 0 6px">' . h($title) . '</div>';
        echo '<div class="prop-sub muted" style="opacity:.85;margin-bottom:12px">' . h($loc) . ' • ' . h($price) . '</div>';
        echo '<a class="btn" href="' . h($detailsUrl) . '">View Details</a>';
      echo '</div>';
    echo '</article>';
  }
}

/* ===========================================================
   QUICK LINKS SIDEBAR (site-wide)
   =========================================================== */
if (!function_exists('quick_links_items')) {
  function quick_links_items(): array {
    $b = render_base_url();
    return [
      ['Home Loan',                    $b.'/home-loan.php'],
      ['Docs Required for Home Loan',  $b.'/home-loan-docs.php'],
      ['Property Buying Steps',        $b.'/buying-steps.php'],
      ['EMI Calculator',               $b.'/emi-calculator.php'],
      ['Vaastu Tips',                  $b.'/vaastu-tips.php'],
      ['Builder Partners',             $b.'/builder-partners.php'],
      ['Our Services',                 $b.'/services.php'],
      ['NRI Services',                 $b.'/nri-services.php'],
      ['GM HOMEZ in the Media',       $b.'/media.php'],
      ['My Portfolio',                 $b.'/portfolio.php'],
      ['Shortlists',                   $b.'/shortlists.php'],
      ['Recently Viewed',              $b.'/recent.php'],
      ['Enquired Properties',          $b.'/enquiries.php'],
    ];
  }
}
if (!function_exists('render_quicklinks_sidebar')) {
  function render_quicklinks_sidebar(): void {
    $items = quick_links_items(); ?>
    <aside class="sidebar">
      <div class="ql-card">
        <div class="ql-head">Quick Links</div>
        <nav class="ql-list">
          <?php foreach($items as [$label,$href]): ?>
            <a class="ql-item" href="<?= h($href) ?>"><span><?= h($label) ?></span></a>
          <?php endforeach; ?>
        </nav>
      </div>
    </aside>
  <?php }
}

/* ===========================================================
   Site-wide HEADER + FOOTER
   =========================================================== */
if (!function_exists('render_header')) {
  // NOTE: $opts['sidebar'] => true/false (default true)
  function render_header(string $title = 'GM HOMEZ', array $opts = []) {
    $base = render_base_url();
    $with_sidebar = $opts['sidebar'] ?? true;           // default ON
    $GLOBALS['__sidebar_open'] = $with_sidebar;         // used in render_footer
    ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= h($title) ?> | GM HOMEZ</title>
  <style>
    :root{ --ink:#0f172a; --muted:#64748b; --line:#e5e7eb; --brand:#f59e0b; --blue:#1d2c6b; }
    *{box-sizing:border-box} body{margin:0;font:16px/1.5 system-ui,-apple-system,Segoe UI,Roboto,Arial;color:var(--ink)}
    a{color:inherit;text-decoration:none}
    .container{max-width:1200px;margin:0 auto;padding:0 20px}

    /* HEADER */
    .site-header{position:sticky;top:0;z-index:50;background:#fff;border-bottom:1px solid var(--line)}
    .nav{display:flex;gap:22px;align-items:center;height:64px}
    .logo img{height:28px;display:block}
    .navlinks{display:flex;gap:20px;margin-left:28px}
    .navlinks a{color:#384152}
    .nav-cta{margin-left:auto;display:flex;gap:10px;align-items:center}
    .btn{padding:.55rem .9rem;border-radius:10px;border:1px solid var(--line);background:#fff}
    .btn.brand{background:var(--brand);border-color:var(--brand);color:#111;font-weight:600}
    .in{display:inline-grid;place-items:center;width:34px;height:34px;border:1px solid var(--line);border-radius:10px}
    @media (max-width:900px){.navlinks{display:none}}

    /* LAYOUT + SIDEBAR (gradient) */
    .layout{display:grid;grid-template-columns:280px 1fr;gap:24px;margin-top:18px}
    @media (max-width:980px){.layout{grid-template-columns:1fr}}
    .page-content{min-width:0}
    .ql-card{
      border:2px solid transparent;border-radius:14px;background:#fff;
      background-origin:border-box;background-clip:padding-box,border-box;
      background-image:linear-gradient(#fff,#fff),linear-gradient(135deg,#0ea5e9,#8b5cf6);
      box-shadow:0 12px 28px rgba(2,6,23,.06);position:sticky;top:84px
    }
    .ql-head{padding:14px 16px;font-weight:800;color:#111c45;border-bottom:1px solid var(--line)}
    .ql-list{display:flex;flex-direction:column}
    .ql-item{display:block;padding:14px 16px;border-bottom:1px solid #eef2f7;color:#0f172a}
    .ql-item:last-child{border-bottom:none}
    .ql-item:hover{background:linear-gradient(90deg,rgba(14,165,233,.10),rgba(139,92,246,.10))}
    .sidebar{display:block}

    /* CTA band above footer */
    .cta{background:var(--blue);color:#fff;padding:36px 0;margin-top:40px}
    .cta .grid{display:grid;grid-template-columns:1fr 260px;gap:16px;align-items:center}
    @media (max-width:900px){.cta .grid{grid-template-columns:1fr}}

    /* FOOTER */
    .site-footer{background:#0b0f1a;color:#cbd5e1;padding:28px 0 12px}
    .fcols{display:grid;grid-template-columns:1.1fr .7fr .9fr 1fr;gap:20px}
    @media (max-width:1000px){.fcols{grid-template-columns:1fr 1fr}}
    .ftitle{font-weight:700;letter-spacing:.02em;color:#fff;margin-bottom:10px}
    .fcol a{display:block;color:#cbd5e1;margin:6px 0}
    .map{display:grid;place-items:center}
    .bottom{border-top:1px solid #1f2a44;margin-top:18px;padding-top:10px;display:flex;gap:10px;align-items:center;justify-content:space-between}
    .socs a{display:inline-grid;place-items:center;width:34px;height:34px;border:1px solid #1f2a44;border-radius:10px;margin-right:6px}
  </style>
</head>
<body>
  <!-- HEADER -->
  <header class="site-header">
    <div class="container nav">
      <div class="logo" title="GM HOMEZ">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true">
          <path d="M3 11.5 12 4l9 7.5v7A1.5 1.5 0 0 1 19.5 20h-15A1.5 1.5 0 0 1 3 18.5v-7Z" stroke="currentColor" stroke-width="1.6"/>
          <path d="M9 20v-6h6v6" stroke="currentColor" stroke-width="1.6"/>
        </svg>
        <b>GM HOMEZ</b>
      </div>
      <nav class="navlinks">
        <a href="<?= h($base) ?>/careers.php">Home</a>
        <a href="<?= h($base) ?>/careers.php#why">Why join us?</a>
        <a href="<?= h($base) ?>/careers.php#life">Life at GM HOMEZ</a>
        <a href="<?= h($base) ?>/careers.php#impact">Our Impact</a>
        <a href="<?= h($base) ?>/careers.php#teams">Teams</a>
        <a href="<?= h($base) ?>/careers.php#global">Our global family</a>
      </nav>

      <div class="nav-cta">
        <a class="in" href="https://www.linkedin.com/company/gmhomez" target="_blank" rel="noopener">in</a>
        <a class="btn brand" href="<?= h($base) ?>/careers.php#apply">Join Us</a>
      </div>
    </div>
  </header>

  <main class="container" id="page">
    <?php if ($with_sidebar): ?>
      <div class="layout">
        <?php render_quicklinks_sidebar(); ?>
        <section class="page-content">
    <?php endif; ?>
<?php
  }
}

if (!function_exists('render_footer')) {
  function render_footer() {
    // close the sidebar layout if it was opened
    if (!empty($GLOBALS['__sidebar_open'])) {
      echo "</section></div>\n"; // close .page-content and .layout
    }

    $base = render_base_url();
    ?>
  </main>

  <!-- CTA band -->
  <section class="cta">
    <div class="container grid">
      <div>
        <div style="opacity:.9;font-weight:700">Ready to <u>come home?</u></div>
        <p style="opacity:.9;margin:.25rem 0 0">If you’re interested in exploring opportunities at GM HOMEZ, all you have to do is knock.</p>
      </div>
      <div style="text-align:right">
        <a class="btn brand" href="<?= h($base) ?>/careers.php#apply">Knock at our door</a>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="site-footer">
    <div class="container fcols">
      <div class="fcol">
        <div class="ftitle">ABOUT US</div>
        <p style="margin-top:0">GM HOMEZ is among India’s leading digital real-estate advisors offering a one-stop platform for buying residential property. We bring simplicity, transparency and trust to the home-buying process.</p>
        <div style="margin-top:8px">Email: <a href="mailto:careers@gmhomez.in">gayathri@gmhomez.in</a></div>
      </div>

      <div class="fcol">
        <div class="ftitle">VISIT</div>
        <a href="<?= h($base) ?>/careers.php">Home</a>
        <a href="<?= h($base) ?>/careers.php#why">Why join us?</a>
        <a href="<?= h($base) ?>/careers.php#life">Life at GM HOMEZ</a>
        <a href="<?= h($base) ?>/careers.php#impact">Our Impact</a>
        <a href="<?= h($base) ?>/careers.php#teams">Teams</a>
        <a href="<?= h($base) ?>/careers.php#global">GM HOMEZ Family</a>
      </div>

      <div class="fcol">
        <div class="ftitle">OUR HOMES</div>
        <a>Bengaluru</a><a>Chennai</a><a>Hyderabad</a><a>Kolkata</a>
        <a>Mumbai</a><a>Pune</a><a>Gurgaon</a>
      </div>

      <div class="map">
        <img src="<?= h($base) ?>/assets/image.png" alt="India map" style="max-width:220px;opacity:.9">
      </div>
    </div>

    <div class="container bottom">
      <div class="socs">
        <a href="https://www.linkedin.com/company/gmhomez" target="_blank" rel="noopener">in</a>
        <a href="https://twitter.com/" target="_blank" rel="noopener">t</a>
        <a href="https://www.youtube.com/" target="_blank" rel="noopener">▶</a>
        <a href="https://facebook.com/" target="_blank" rel="noopener">f</a>
      </div>
      <div style="opacity:.7">© <?= date('Y') ?> GM HOMEZ ·
        <a href="<?= h($base) ?>/user-agreement.php">Term of Use</a> ·
        <a href="<?= h($base) ?>/privacy-policy.php">Privacy Policy</a></div>
    </div>
  </footer>

  
</body>
</html>
<?php
  }
}

/* ===========================================================
   Optional wrapper names (legacy pages can call page_top/bottom)
   =========================================================== */
if (!function_exists('page_top'))    { function page_top($t){ render_header($t); } }
if (!function_exists('page_bottom')) { function page_bottom(){ render_footer(); } }
