<?php
// gm-homez/about.php
error_reporting(E_ALL); ini_set('display_errors','1');
require_once __DIR__ . '/config.php';

// compute base (/gm-homez) for links & assets
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($BASE === '/' || $BASE === '\\') $BASE = '';

// hero + team image fallbacks
$HERO_IMG = $BASE . '/uploads/house-keys.jpg';
if (!file_exists(__DIR__ . '/uploads/house-keys.jpg')) {
  $HERO_IMG = 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=1600&q=80&auto=format&fit=crop';
}
$TEAM = [
  ['name' => 'GAYATHRI D N',   'role' => 'Founder & CEO',   'img' => $BASE . '/uploads/ceo.jpg'],
  ['name' => 'Rajesh Reddy .K',     'role' => 'Head of Sales',   'img' => $BASE . '/uploads/raj.jpg'],
  ['name' => 'Madhavi .M',   'role' => 'Team lead','img' => $BASE . '/uploads/mmm.jpg'],
  ['name' => 'kishor reddy',   'role' => 'Marketing','img' => $BASE . '/uploads/kis.jpg'],
   ['name' => 'Yashas ',    'role' => 'Loan & Legal',    'img' => $BASE . '/uploads/yah.jpg'],
];
foreach ($TEAM as &$t) {
  if (!file_exists(__DIR__ . '/'.ltrim(parse_url($t['img'], PHP_URL_PATH), '/'))) {
    $t['img'] = 'https://images.unsplash.com/photo-1527980965255-d3b416303d12?w=800&q=80&auto=format&fit=crop';
  }
}
unset($t);

// meta
$TITLE = 'About GM HOMEZ';
$DESC  = 'GM HOMEZ helps home-buyers discover verified projects with end-to-end assistance — search, site visits, loans and legal support.';
$CANON = $BASE . '/about.php';
$OGIMG = $HERO_IMG;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($TITLE) ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="<?= htmlspecialchars($DESC) ?>">
  <link rel="canonical" href="<?= htmlspecialchars($CANON) ?>">

  <!-- Open Graph / Twitter -->
  <meta property="og:title" content="<?= htmlspecialchars($TITLE) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($DESC) ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= htmlspecialchars($CANON) ?>">
  <meta property="og:image" content="<?= htmlspecialchars($OGIMG) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($TITLE) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($DESC) ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($OGIMG) ?>">

  <!-- JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "GM HOMEZ",
    "url": "<?= htmlspecialchars($BASE) ?>/",
    "logo": "<?= htmlspecialchars($OGIMG) ?>",
    "sameAs": ["https://wa.me/917676536261"]
  }
  </script>
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "About GM HOMEZ",
    "url": "<?= htmlspecialchars($CANON) ?>",
    "description": "<?= htmlspecialchars($DESC) ?>"
  }
  </script>

  <style>
    :root{
      --bg:#0b1620; --card:#0f2a37; --line:rgba(148,163,184,.12);
      --muted:#9fb2c0; --text:#e6f0f6; --accent:#0ea5e9;
    }
    *{box-sizing:border-box}
    body{background:var(--bg); color:var(--text); font-family:Inter,system-ui,Segoe UI,Arial,sans-serif; margin:0}
    a{color:#8df; text-decoration:none}
    a:hover{text-decoration:underline}
    .wrap{max-width:1200px; margin:24px auto; padding:0 16px}
    .crumbs{font-size:14px; color:var(--muted); margin:8px 0 12px}
    .crumbs a{color:#8df}
    .card{background:var(--card); border:1px solid var(--line); border-radius:16px; padding:22px; box-shadow:0 10px 30px rgba(0,0,0,.35)}
    .grid{display:grid; grid-template-columns:180px 1fr; gap:22px}
    .left .title{position:sticky; top:18px; font-weight:800; letter-spacing:.22em; color:#a7c0cf}
    .hero{border-radius:16px; overflow:hidden; border:1px solid var(--line); margin-bottom:14px}
    .hero img{display:block; width:100%; height:auto}
    .content{display:grid; grid-template-columns:1fr; gap:16px}
    .content h1{margin:10px 0 6px}
    .muted{color:var(--muted)}
    .quote{border-left:4px solid var(--accent); background:rgba(2,6,23,.4);
      border-radius:10px; padding:12px 14px; margin:8px 0; font-style:italic}
    .facts{display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-top:8px}
    .fact{background:rgba(2,6,23,.35); border:1px solid var(--line); border-radius:12px; padding:14px; text-align:center}
    .fact b{font-size:22px; display:block}
    /* Team */
    .team{margin-top:22px}
    .team-grid{display:grid; grid-template-columns:repeat(4,1fr); gap:14px}
    .member{background:rgba(2,6,23,.35); border:1px solid var(--line); border-radius:14px; overflow:hidden}
    .member img{width:100%; height:180px; object-fit:cover; display:block}
    .member .info{padding:12px}
    .member .name{font-weight:700}
    .member .role{color:var(--muted); font-size:14px}
    /* Timeline */
    .timeline{margin-top:22px}
    .tline{position:relative; margin-left:6px; padding-left:18px}
    .tline::before{content:""; position:absolute; left:6px; top:0; bottom:0; width:2px; background:rgba(148,163,184,.25)}
    .titem{position:relative; margin:12px 0; padding-left:8px}
    .titem::before{content:""; position:absolute; left:-3px; top:6px; width:10px; height:10px; border-radius:50%; background:var(--accent)}
    .titem .when{font-weight:700; color:#a7c0cf; font-size:14px}
    /* CTA */
    .cta{margin-top:22px; display:flex; gap:14px; align-items:center; flex-wrap:wrap}
    .btn{display:inline-block; padding:10px 14px; border-radius:10px; border:1px solid var(--line); background:#0b1620; color:var(--text); text-decoration:none}
    .btn.primary{background:linear-gradient(135deg,#0ea5e9,#22d3ee); color:#fff; border:none}
    .center{text-align:center}
    /* Active nav helper (if your header supports .active) */
    .nav a.active{color:#fff; text-decoration:underline}
    @media (max-width:1000px){
      .grid{grid-template-columns:1fr; gap:14px}
      .left .title{position:static; text-align:center; margin-top:-6px}
      .team-grid{grid-template-columns:repeat(2,1fr)}
      .facts{grid-template-columns:repeat(2,1fr)}
    }
    @media (max-width:560px){
      .team-grid{grid-template-columns:1fr}
      .facts{grid-template-columns:1fr}
    }
  </style>
</head>
<body>

<?php
// header include (falls back if not present)
if (file_exists(__DIR__ . '/inc/header.php')) {
  include __DIR__ . '/inc/header.php';
} else { ?>
  <header style="background:linear-gradient(180deg,rgba(2,6,23,.4),rgba(2,6,23,.7));border-bottom:1px solid var(--line)">
    <div class="wrap" style="display:flex;align-items:center;justify-content:space-between;padding:14px 0">
      <a href="<?= $BASE ?>/index.php" style="color:#8df;text-decoration:none;font-weight:700">GM HOMEZ</a>
      <nav class="nav" style="display:flex;gap:16px">
        <a href="<?= $BASE ?>/index.php">Home</a>
        <a href="<?= $BASE ?>/about.php" class="active">About</a>
      </nav>
    </div>
  </header>
<?php } ?>

<main class="wrap">
  <div class="crumbs"><a href="<?= $BASE ?>/index.php">Home</a> / About</div>

  <div class="card">
    <div class="grid">
      <!-- LEFT: sticky label -->
      <aside class="left">
        <div class="title">ABOUT&nbsp;US</div>
      </aside>

      <!-- RIGHT: hero + sections -->
      <section>
        <div class="hero"><img src="<?= htmlspecialchars($HERO_IMG) ?>" alt="GM HOMEZ"></div>

        <div class="content">
          <h1>About <span class="muted">GM</span> HOMEZ</h1>
          <p>
            Founded in 2021, GM Homez is a specialised channel partner and transaction-advisory firm in the real estate sector. We combine market intelligence, technical due diligence, and hands-on execution to help homeowners, investors, and developers transact with confidence across the entire property lifecycle. 
At GM Homez, we understand that buying or selling property is more than just a transaction – it’s a life-changing decision. Our mission is to empower our clients with the right knowledge, resources, and support to navigate the complexities of the real estate market. With a dedicated team of experts, we are committed to delivering value, transparency, and excellence in every deal.
We prioritize trust, transparency, and security. plus we don’t charge any transaction fees—our services are completely free for you.
We assist with site visits, legal verification, agreements, and resale documentation, ensuring every transaction is secure and compliant. Our team provides end-to-end financial solutions, including home loans, mortgage structuring, and dedicated loan advocacy. Additionally, we offer investment advisory, resale support, interiors, and complete transaction management for a seamless real estate journey. As a trusted partner, we work closely with developers, investors, and homeowners to provide access to the best properties and investment opportunities in the market. 

Let GM Homez be your partner in achieving your real estate goals. With our expertise, commitment, and secure services, we are here to make your experience as smooth and successful as possible.
          </p>
          <p class="muted">
            We combine local expertise with data-driven search so you can confidently shortlist projects that fit your budget,
            BHK preference, and preferred localities.
          </p>

          <div class="quote">“The right home is not just four walls — it’s sunlight, commute, community, and peace of mind after a transparent deal.”</div>

          <div class="facts">
            <div class="fact"><b>3000+</b> Buyers Guided</div>
            <div class="fact"><b>120+</b> Partner Projects</div>
            <div class="fact"><b>0%</b> Brokerage (most listings)</div>
          </div>

          <!-- TEAM -->
          <div class="team">
            <h2>Meet the Team</h2>
            <div class="team-grid">
              <?php foreach ($TEAM as $m): ?>
                <div class="member">
                  <img src="<?= htmlspecialchars($m['img']) ?>" alt="<?= htmlspecialchars($m['name']) ?>">
                  <div class="info">
                    <div class="name"><?= htmlspecialchars($m['name']) ?></div>
                    <div class="role"><?= htmlspecialchars($m['role']) ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- TIMELINE -->
          <div class="timeline">
            <h2>Our Story</h2>
            <div class="tline">
              <div class="titem">
                <div class="when">2019</div>
                <div>Started with a small team helping friends & family buy transparently.</div>
              </div>
              <div class="titem">
                <div class="when">2021</div>
                <div>Built our verified listings network and launched end-to-end assistance.</div>
              </div>
              <div class="titem">
                <div class="when">2023</div>
                <div>Introduced data-driven search, fuzzy matching and quick WhatsApp help.</div>
              </div>
              <div class="titem">
                <div class="when">2025</div>
                <div>Serving 3000+ buyers with zero-stress home discovery across top localities.</div>
              </div>
            </div>
          </div>

          <!-- CTA -->
          <div class="cta card" style="margin-top:16px">
            <div style="flex:1">
              <h2 style="margin:0 0 6px">Let’s find your home</h2>
              <div class="muted">Tell us your budget & preferred localities — we’ll shortlist and schedule site visits.</div>
            </div>
            <a class="btn primary" href="<?= $BASE ?>/index.php#assistance">Book Site Visit</a>
            <a class="btn" target="_blank" rel="noopener" href="https://wa.me/917676536261">WhatsApp Us</a>
          </div>
        </div>
      </section>
    </div>
  </div>
</main>

<?php
// footer include (falls back if not present)
if (file_exists(__DIR__ . '/inc/footer.php')) {
  include __DIR__ . '/inc/footer.php';
} else { ?>
  <footer style="margin-top:20px;border-top:1px solid var(--line);background:rgba(2,6,23,.4)">
    <div class="wrap" style="display:flex;justify-content:space-between;gap:12px;padding:16px 0">
      <div>&copy; <?= date('Y') ?> GM HOMEZ</div>
      <div class="muted">Built with care in India</div>
    </div>
  </footer>
<?php } ?>

<!-- Auto-highlight About in your real header nav -->
<script>
  (function(){
    var links = document.querySelectorAll('a[href$="about.php"]');
    links.forEach(function(a){ a.classList.add('active'); a.setAttribute('aria-current','page'); });
  })();
</script>

<!-- Keep your chatbot if you want it here too -->
<script src="<?= $BASE ?>/assets/chatbot.js"></script>
</body>
</html>
