<?php
// careers.php — GM HOMEZ Careers

$TITLE = 'Careers at GM HOMEZ';
require_once __DIR__.'/config.php';
require_once __DIR__.'/lib/render.php';

$BASE = function_exists('render_base_url') ? render_base_url() : '';

/* ---------- ASSETS (CHANGE THESE PATHS TO YOUR FILES) ---------- */
$ASSETS = [
  // hero & sections
  'hero_people' => $BASE.'/assets/careers/home.jpg',
  'dream_img'   => $BASE.'/assets/careers/header.jpg',
  'found_img'   => $BASE.'/assets/careers/awards3.jpg',

  // why join
  'why1' => $BASE.'/assets/careers/why1.jpg',
  'why2' => $BASE.'/assets/careers/why2.jpg',
  'why3' => $BASE.'/assets/careers/why3.jpg',
  'why4' => $BASE.'/assets/careers/why4.jpg',

  // teams
  'team1' => $BASE.'/assets/careers/team1.jpg',
  'team2' => $BASE.'/assets/careers/team2.jpg',
  'team3' => $BASE.'/assets/careers/team3.jpg',
  'team4' => $BASE.'/assets/careers/team_hr.jpg',

  // GLOBAL NETWORK logos + map
  'logo_rea_group' => $BASE.'/assets/logos/rera.png',
  'logo_rea_india' => $BASE.'/assets/logos/ind.png',
  'logo_housing'   => $BASE.'/assets/logos/hou.png',
  'logo_gmhomez'   => $BASE.'/assets/logos/hou.png',
  'world_map'      => $BASE.'/assets/illustrations/global.png',

  // “Our global family” grid (add/remove freely)
  'logos' => [
    $BASE.'/assets/logos/images7.png',
    $BASE.'/assets/logos/images.png',
    $BASE.'/assets/logos/images1.png',
    $BASE.'/assets/logos/images2.png',
    $BASE.'/assets/logos/images3.png',
    $BASE.'/assets/logos/images4.png',
    $BASE.'/assets/logos/images5.png',
    $BASE.'/assets/logos/images6.png',
  ],
];

/* ---------- Video playlist ---------- */
$playlist = [
  ['id'=>'landing','title'=>'How I landed at GM HOMEZ','src'=>$BASE.'/assets/careers/video_landed.mp4'],
  ['id'=>'learning','title'=>'Learning is for everyone','src'=>$BASE.'/assets/careers/video_learning.mp4'],
  ['id'=>'bi_from_ground','title'=>'We build from ground reality','src'=>$BASE.'/assets/careers/video_ground.mp4'],
  ['id'=>'global_group','title'=>'What it means to be global','src'=>$BASE.'/assets/careers/video_global.mp4'],
];
$vid = $_GET['video'] ?? $playlist[1]['id'];
$active = $playlist[0]; foreach($playlist as $v){ if($v['id']===$vid){ $active=$v; break; } }

render_header($TITLE);
?>

<style>
  :root{ --ink:#0f172a; --muted:#475569; --bg:#fff; --panel:#111c45; --panel-ink:#ecf0ff; --brand:#f59e0b; --line:#e2e8f0; }
  h2.section{font-size:28px; text-align:center; margin:26px 0 6px}
  .sub{color:var(--muted); text-align:center; margin-bottom:16px}
  .btn{display:inline-block; padding:.65rem 1rem; border-radius:10px; background:var(--brand); color:#111; text-decoration:none; border:0}
  .btn.secondary{background:#ffffff; border:1px solid var(--line); color:#111}
  .grid2{display:grid; grid-template-columns:1fr 1fr; gap:24px}
  @media (max-width:900px){.grid2{grid-template-columns:1fr}}
  .card{border:1px solid var(--line); border-radius:14px; padding:16px; background:#fff}

  /* HERO */
  .hero{display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:center; min-height:380px}
  .hero-copy h1{font-size:44px; line-height:1.05; margin:0 0 12px; color:#1f2937}
  .hero-art{position:relative}
  .blob{
    display:none; /* hide the orange blob; set to block to show */
    position:absolute; width:340px; height:240px; right:10%; top:12%;
    background:radial-gradient(ellipse at center, #ffb84d 0%, #f59e0b 60%, rgba(245,158,11,0) 70%);
    filter:blur(12px); border-radius:50%;
  }
  .hero-art img.people{max-width:100%; height:auto; display:block;}

  /* BLUE BAND */
  .band{background:var(--panel); color:var(--panel-ink); border-radius:12px; padding:24px; margin-top:22px}
  .band .cols{display:grid; grid-template-columns:1fr 1fr; gap:18px}
  @media (max-width:900px){.band .cols{grid-template-columns:1fr}}
  .dot{width:9px;height:9px;background:var(--brand); display:inline-block; border-radius:2px; margin-right:8px}

  /* FOUNDATION */
  .f-cards .item{background:#1c2a6b; color:#fff; border-radius:12px; padding:14px; margin-bottom:12px}
  .f-cards .label{font-weight:700; margin-bottom:6px}

  /* IMPACT */
  .impact{background:#f4f6fb; border:1px solid var(--line); border-radius:12px; padding:18px}
  .impact .grid{display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:center}
  @media (max-width:900px){.impact .grid{grid-template-columns:1fr}}
  .house{display:grid; place-items:center; width:150px; height:140px; border-radius:14px; background:#fff; border:1px solid #e6e8f2; box-shadow:0 1px 0 #e6e8f2 inset}
  .house b{font-size:22px; display:block; margin-top:6px}
  .house svg{height:42px; width:42px}

  /* WHY JOIN */
  .why-list{display:grid; grid-template-columns:repeat(4,1fr); gap:16px}
  @media (max-width:1100px){.why-list{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:640px){.why-list{grid-template-columns:1fr}}
  .why-card{border:1px solid var(--line); border-radius:12px; overflow:hidden; background:#fff}
  .why-card img{width:100%; height:170px; object-fit:cover}
  .why-card .body{padding:14px}
  .why-card .title{font-size:22px; line-height:1.1}

  /* TEAMS */
  .teams{background:var(--panel); color:var(--panel-ink); border-radius:12px; padding:24px}
  .rail{display:flex; gap:14px; overflow:auto; scroll-snap-type:x mandatory; padding-bottom:6px}
  .tile{min-width:320px; scroll-snap-align:start; border-radius:12px; overflow:hidden; background:#0f1a49; border:1px solid rgba(255,255,255,.15)}
  .tile img{width:100%; height:160px; object-fit:cover}
  .tile .tcap{padding:12px; font-weight:600}
  .rail-controls{display:flex;justify-content:center; gap:10px; margin-top:10px}
  .rail-controls button{border:0; background:#fff; color:#111; padding:6px 10px; border-radius:8px}

  /* GLOBAL FAMILY */
  .global{background:var(--panel); color:var(--panel-ink); border-radius:12px; padding:22px}
  .logos{display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:12px; margin-top:12px}
  .logos .logo{background:#fff; border-radius:10px; display:grid; place-items:center; padding:10px}
  .logos .logo img{max-width:120px; max-height:40px}

  /* TESTIMONIALS */
  .testi-rail{display:flex; gap:16px; overflow:auto; scroll-snap-type:x mandatory}
  .tcard{min-width:420px; scroll-snap-align:start; background:#eef0fb; border:1px solid #dfe3f4; border-radius:16px; padding:18px}
  .tcard .name{margin-top:10px; font-weight:700}

  /* VIDEO PLAYLIST */
  .vsec{background:var(--panel); color:var(--panel-ink); border-radius:12px; padding:18px}
  .vgrid{display:grid; grid-template-columns:1fr 360px; gap:16px}
  @media (max-width:1000px){.vgrid{grid-template-columns:1fr}}
  .chap{display:grid; gap:10px}
  .chap button{display:flex; align-items:center; gap:8px; text-align:left; border:1px solid rgba(255,255,255,.2); background:transparent; color:#fff; border-radius:10px; padding:10px; cursor:pointer}
  .chap button.active{background:#f59e0b; color:#111; border-color:#f59e0b}

  /* GLOBAL NETWORK (the new section) */
  .network{margin-top:24px}
  .network h3{letter-spacing:.06em;color:#6b7280;font-weight:700;text-align:center;margin:0 0 6px}
  .network .lead{font-size:26px;text-align:center;margin:0 0 8px}
  .network .sublead{color:#64748b;text-align:center;max-width:70ch;margin:0 auto 14px}
  .org-card{border:1px solid #e5e7eb;border-radius:12px;padding:18px;background:#fff;max-width:880px;margin:0 auto}
  .org-row{display:flex;gap:18px;justify-content:center;align-items:center;flex-wrap:wrap}
  .org{display:grid;place-items:center;gap:6px;padding:10px 14px;border:1px solid #eaecef;border-radius:10px;background:#fff;min-width:170px}
  .org img{max-width:140px;max-height:38px}
  .org small{color:#6b7280;text-align:center}
  .connector{height:20px;display:flex;align-items:center;justify-content:center;color:#f59e0b}
  .connector:before,.connector:after{content:'';display:inline-block;width:120px;height:2px;background:#f59e0b;opacity:.7}
  .connector svg{margin:0 8px}
  .children .org{min-width:260px}
  .children p{color:#6b7280;font-size:14px;margin:6px 0 0;text-align:center}
  .world{position:relative;max-width:980px;margin:18px auto 0}
  .world img{width:100%;height:auto;display:block;opacity:.95}
  .world .tag{position:absolute;background:#f59e0b;color:#111;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700}
</style>

<!-- 1) HERO -->
<section class="hero">
  <div class="hero-copy">
    <h1>Come home</h1>
    <p style="color:var(--muted);max-width:46ch">Come home to a place where comfort meets elegance. Our thoughtfully designed spaces are created to give you the lifestyle you’ve 
        always dreamed of. From modern amenities to serene surroundings, every detail is crafted to make you feel at home the moment you step in. Discover the joy of living in a home that truly reflects your aspirations and creates lasting memories for you and your family.</p>
    <div style="margin-top:14px"><a class="btn" href="#vsec">Join Us</a></div>
  </div>
  <div class="hero-art">
    <div class="blob" aria-hidden="true"></div>
    <img class="people" src="<?= h($ASSETS['hero_people']) ?>" alt="GM HOMEZ team">
  </div>
</section>

<!-- 2) BLUE BAND -->
<section class="band" style="margin-top:20px">
  <div style="font-size:20px; font-weight:700; margin-bottom:6px">GM HOMEZ is not just a workplace. It is your professional home away from home.</div>
  <div class="cols">
    <div><a href="/" style="color:#fff;text-decoration:underline">Learn more about GM HOMEZ</a></div>
    <div>
      <div><span class="dot"></span>We are building a team empowered to turn ideas into real(ty) and own outcomes.</div>
      <div style="margin-top:6px"><span class="dot"></span>Openness, camaraderie, collaboration — every perspective heard and valued.</div>
    </div>
  </div>
</section>

<!-- 3) DREAM -->
<h2 class="section">From dream to reality — <u>through you</u></h2>
<p class="sub">What will your work mean at GM HOMEZ?</p>
<section class="card">
  <div class="grid2">
    <img src="<?= h($ASSETS['dream_img']) ?>" alt="Team at work" style="width:100%; border-radius:10px">
    <div class="card" style="background:#fff">
      <div style="display:grid; gap:12px">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px">
          <div class="card" style="border-color:#f2f2f2"><div><span class="dot"></span>Open doors for home buyers with exceptional end-to-end service.
        Come home to a place where warmth and welcome greet you at every step. Designed to enhance your lifestyle, our homes bring together the perfect balance of modern 
        living, peaceful surroundings, and luxurious comfort. </div></div>
          <div class="card" style="border-color:#f2f2f2"><div><span class="dot"></span>Help builders and partners connect with the right audience.</div></div>
        </div>
        <div><a class="btn secondary" href="/">Visit GMHOMEZ.com</a></div>
      </div>
    </div>
  </div>
</section>

<!-- 4) FOUNDATION -->
<h2 class="section">The foundation</h2>
<p class="sub">Our vision, mission and values</p>
<section class="grid2">
  <div class="f-cards">
    <div class="item"><div class="label">Vision</div><div>Change the way India experiences property.</div></div>
    <div class="item"><div class="label">Mission</div><div>Be the first choice for discovering, financing and buying a home — enabled by data, design, technology and the passion of our people.</div></div>
    <div class="item"><div class="label">Values</div>
      <ul style="columns:2; margin:6px 0 0 14px; list-style:square">
        <li>Excellence</li><li>Ownership</li><li>Respect</li><li>Innovation</li><li>Trust & Integrity</li><li>Boundarylessness</li>
      </ul>
    </div>
  </div>
  <img src="<?= h($ASSETS['found_img']) ?>" alt="Workshop" style="width:100%; border-radius:12px">
</section>

<!-- 5) IMPACT -->
<section class="impact" style="margin-top:18px">
  <div class="grid">
    <div>
      <div style="letter-spacing:.06em; color:#6b7280; font-weight:700">IMPACT</div>
      <h3 style="font-size:26px; margin:6px 0 8px">Make a positive impact in the world, <u>one home at a time.</u></h3>
      <p class="sub" style="text-align:left">We serve <b>1.5L+</b> homebuyers every month.</p>
      <a class="btn secondary" href="#why">Read More</a>
    </div>
    <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:12px; justify-items:center">
      <div class="house"><svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M3 11l9-8 9 8"/><path d="M9 22V12h6v10"/></svg><b>32K+</b><div class="sub" style="text-align:center">homes found owners</div></div>
      <div class="house"><svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M3 11l9-8 9 8"/><path d="M9 22V12h6v10"/></svg><b>₹12,000 Cr+</b><div class="sub" style="text-align:center">worth properties sold</div></div>
      <div class="house"><svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M3 11l9-8 9 8"/><path d="M9 22V12h6v10"/></svg><b>350+</b><div class="sub" style="text-align:center">Relationship Managers</div></div>
      <div class="house"><svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M3 11l9-8 9 8"/><path d="M9 22V12h6v10"/></svg><b>8</b><div class="sub" style="text-align:center">cities across India</div></div>
    </div>
  </div>
</section>

<!-- 6) WHY JOIN -->
<h2 id="why" class="section">Why join?</h2>
<section class="why-list">
  <div class="why-card"><img src="<?= h($ASSETS['why1']) ?>" alt="Global family"><div class="body"><div class="title">Come home to your global family</div><p class="sub" style="text-align:left">Be part of a leading network in real estate.</p></div></div>
  <div class="why-card"><img src="<?= h($ASSETS['why2']) ?>" alt="Fulfil dreams"><div class="body"><div class="title">Come home to fulfil dreams</div><p class="sub" style="text-align:left">Change how India experiences property.</p></div></div>
  <div class="why-card"><img src="<?= h($ASSETS['why3']) ?>" alt="Learn and grow"><div class="body"><div class="title">Come home to learn and grow</div><p class="sub" style="text-align:left">A fast-paced environment that fosters growth.</p></div></div>
  <div class="why-card"><img src="<?= h($ASSETS['why4']) ?>" alt="Belong"><div class="body"><div class="title">Come home to where you belong</div><p class="sub" style="text-align:left">Openness, collaboration and camaraderie.</p></div></div>
</section>
<div style="text-align:center; margin-top:14px"><a class="btn secondary" href="#teams">Read More</a></div>

<!-- 6.5) OUR GLOBAL NETWORK -->
<section class="network" id="global">
  <h3>OUR GLOBAL NETWORK</h3>
  <div class="lead">The world is <u>our backyard</u>.</div>
  <p class="sublead">GM HOMEZ is part of a larger ecosystem that’s reimagining what’s possible in real estate.</p>

  <div class="org-card">
    <div class="org-row">
      <div class="org"><img src="<?= h($ASSETS['logo_rea_group']) ?>" alt="REA Group"><small>Parent</small></div>
      <div class="connector" aria-hidden="true">➜</div>
      <div class="org"><img src="<?= h($ASSETS['logo_rea_india']) ?>" alt="REA India"><small>India</small></div>
    </div>

    <div class="connector" aria-hidden="true">
      <svg width="22" height="18" viewBox="0 0 22 18" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M1 17c6-10 14-10 20 0" stroke="#f59e0b" stroke-width="2" fill="none" stroke-linecap="round"/>
      </svg>
    </div>

    <div class="org-row children">
      <div class="org">
        <img src="<?= h($ASSETS['logo_housing']) ?>" alt="HOUSING.com">
        <p>An innovative online real-estate platform offering a large selection of property listings.</p>
      </div>
      <div class="org">
        <img src="<?= h($ASSETS['logo_gmhomez']) ?>" alt="GM HOMEZ">
        <p>Digital real-estate advisory offering a one-stop platform for buying residential real estate.</p>
      </div>
    </div>
  </div>

  <div class="world">
    <img src="<?= h($ASSETS['world_map']) ?>" alt="Global map">
    <span class="tag" style="left:26%; top:40%;">NORTH AMERICA</span>
    <span class="tag" style="left:59%; top:48%;">ASIA</span>
    <span class="tag" style="left:72%; top:75%;">AUSTRALIA</span>
  </div>
</section>

<!-- 7) TEAMS -->
<h2 id="teams" class="section" style="margin-top:26px">Teams</h2>
<section class="teams">
  <div style="text-align:center; opacity:.9">Find your squad amongst the best of the best in the biz</div>
  <div class="rail" id="rail">
    <div class="tile"><img src="<?= h($ASSETS['team1']) ?>" alt="Business"><div class="tcap">Business</div></div>
    <div class="tile"><img src="<?= h($ASSETS['team2']) ?>" alt="Growth & Marketing"><div class="tcap">Growth & Marketing</div></div>
    <div class="tile"><img src="<?= h($ASSETS['team3']) ?>" alt="Centre of Excellence"><div class="tcap">Centre of Excellence</div></div>
    <div class="tile"><img src="<?= h($ASSETS['team4']) ?>" alt="HR & L&D"><div class="tcap">HR & L&D</div></div>
  </div>
  <div class="rail-controls">
    <button onclick="rail.scrollBy({left:-340,behavior:'smooth'})">◀</button>
    <button onclick="rail.scrollBy({left:340,behavior:'smooth'})">▶</button>
  </div>
  <div style="text-align:center; margin-top:12px"><a class="btn secondary" href="#vsec">Read More</a></div>
</section>

<!-- 8) OUR GLOBAL FAMILY -->
<h2 class="section">Our global family</h2>
<section class="global">
  <div style="text-align:center; max-width:70ch; margin:0 auto">Spanning <b>3 continents</b>, <b>6 countries</b> and more than <b>3000 people</b>, GM HOMEZ partners with companies reimagining what’s possible in real estate.</div>
  <div class="logos">
    <?php foreach($ASSETS['logos'] as $lg): ?>
      <div class="logo"><img src="<?= h($lg) ?>" alt="Partner"></div>
    <?php endforeach; ?>
  </div>
  <div style="text-align:center; margin-top:14px"><a class="btn secondary" href="#testi">Read More</a></div>
</section>

<!-- 9) TESTIMONIALS -->
<h2 id="testi" class="section">Testimonials</h2>
<p class="sub">Many paths. Many stories. One place to call home.</p>
<div class="testi-rail">
  <div class="tcard"><div style="font-weight:700; font-size:18px">I came home to turn insights into action</div><div style="font-size:42px; line-height:0; color:#888">“</div><p>Work from home is contributing to growth in property demand in tier-2 and 3 cities. We adapt to structural changes and keep customers at the centre.</p><div class="name">Ankita · Research</div></div>
  <div class="tcard" style="background:#23306e; color:#fff; border-color:#23306e"><div style="font-weight:700; font-size:18px">I came home to the future of real estate</div><div style="font-size:42px; line-height:0; color:#f5f5f5">“</div><p>I’m excited because we’re always creating something new while keeping customer needs at the centre.</p><div class="name">Manish · Product</div></div>
  <div class="tcard"><div style="font-weight:700; font-size:18px">I came home to a culture of recognition</div><div style="font-size:42px; line-height:0; color:#888">“</div><p>Monthly ‘League of Legends’ recognises outstanding work. We celebrate success together.</p><div class="name">Ashlesha · HR</div></div>
</div>

<!-- 10) VIDEO PLAYLIST -->
<h2 id="vsec" class="section" style="margin-top:28px">Everything you ever wanted in a career</h2>
<section class="vsec">
  <div class="vgrid">
    <video id="mainvid" controls style="width:100%; border-radius:12px; background:#000" poster="">
      <source src="<?= h($active['src']) ?>" type="video/mp4">
    </video>
    <div class="chap">
      <?php foreach($playlist as $v): $is = $v['id']===$active['id'] ? 'active' : ''; ?>
        <button class="<?= $is ?>" data-id="<?= h($v['id']) ?>">▶ <span><?= h($v['title']) ?></span></button>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
  // playlist buttons
  document.querySelectorAll('.chap button').forEach(btn=>{
    btn.addEventListener('click',()=>{
      const id = btn.getAttribute('data-id');
      const url = new URL(location.href);
      url.searchParams.set('video', id);
      location.href = url.toString();
    });
  });
  // teams rail handle
  const rail = document.getElementById('rail');
</script>

<?php render_footer(); ?>
