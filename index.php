<?php
// ===== PHP BOOTSTRAP =====
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🚫 Do not include this file into itself (causes redeclare).
// require __DIR__ . '/index.php';

$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($BASE === '/' || $BASE === '\\') $BASE = '';

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/render.php';

/* Keep your existing renderer. If missing, we still render properties below. */
if (!function_exists('gm_render_property_cards')) {
  function gm_render_property_cards() {
    $props = function_exists('read_properties') ? read_properties() : [];
    if (!$props) {
      return '<p style="color:#9fb2c0">No properties yet. Add one in <a style="color:#8df" href="<?= $BASE ?>/admin/login.php">Admin</a>.</p>';
    }
    ob_start(); foreach ($props as $p) renderPropertyCard($p); return ob_get_clean();
  }
}

/* Fallback read_properties if your lib didn’t define it */
if (!function_exists('read_properties')) {
  function read_properties(){
    $f = __DIR__ . '/data/properties.json';
    if (file_exists($f)) {
      $arr = json_decode(file_get_contents($f), true);
      return is_array($arr) ? $arr : [];
    }
    return [];
  }
}

$props = read_properties();

/* TEAM FALLBACK (used only if your lib functions are absent or return empty) */
if (!function_exists('_team_fallback_card')) {
  function _team_fallback_card($t, $BASE) {
    $name = htmlspecialchars($t['name'] ?? '—');
    $role = htmlspecialchars($t['role'] ?? '');
    $phone= htmlspecialchars($t['phone']?? '');
    $img  = basename(trim($t['photo'] ?? ($t['image'] ?? '')));
    $imgUrl = $img ? ($BASE . '/uploads/team/' . rawurlencode($img)) : ($BASE . '/assets/back.png');
    ?>
    <div class="team-card gshadow gbd">
      <?php if ($imgUrl): ?>
        <img class="team-photo" src="<?= $imgUrl ?>" alt="<?= $name ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="team-photo team-fallback" style="display:none"><?= strtoupper(mb_substr($name,0,1)) ?></div>
      <?php else: ?>
        <div class="team-photo team-fallback"><?= strtoupper(mb_substr($name,0,1)) ?></div>
      <?php endif; ?>
      <div class="team-meta">
        <div class="team-name"><?= $name ?></div>
        <?php if ($role): ?><div class="team-role"><?= $role ?></div><?php endif; ?>
        <?php if ($phone): ?><div class="team-phone"><?= $phone ?></div><?php endif; ?>
      </div>
      <a class="team-cta btn btn-gb" href="<?= $phone ? ('tel:'.preg_replace('/\D+/','',$phone)) : '#' ?>">Call</a>
    </div>
    <?php
  }
}

 


if (!function_exists('_team_fallback_read')) {
  function _team_fallback_read($base = '') {
    // Minimal demo data so "Team" section still renders if no admin data yet
    return [
      ["name"=>"Agent One","role"=>"Senior Agent","photo"=>"", "phone"=>"+91 90000 00001"],
      ["name"=>"Agent Two","role"=>"Sales Lead","photo"=>"", "phone"=>"+91 90000 00002"],
      ["name"=>"Agent Three","role"=>"Advisor","photo"=>"", "phone"=>"+91 90000 00003"],
    ];
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- SEO basics for gmhomez.in -->
<title>GM HOMEZ – Buy, Sell & Rent Properties in Bangalore</title>
<meta name="description" content="GM HOMEZ helps you buy, sell, and rent properties in Bangalore with verified listings and end-to-end assistance.">
<link rel="canonical" href="https://www.gmhomez.in/">

<!-- Structured data (Organization + WebSite) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "GM HOMEZ",
  "url": "https://www.gmhomez.in/",
  "logo": "https://www.gmhomez.in/assets/logo.png",
  "sameAs": []
}
</script>
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"WebSite",
  "url":"https://www.gmhomez.in/",
  "potentialAction":{
    "@type":"SearchAction",
    "target":"https://www.gmhomez.in/search.php?q={search_term_string}",
    "query-input":"required name=search_term_string"
  }
}
</script>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

<title>GM HOMEZ — Real Estate</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/override.css?v=6">




<style>
  /* ===== Animated gradient border pill button ===== */
:root{
  --btn-radius: 999px;           /* pill */
  --btn-border: 2px;             /* border thickness */
  --btn-bg: #1b2633;             /* inner fill color */
  --btn-text: #e6f0f6;           /* text color */
  --spin-speed: 4s;              /* animation speed */
}
<style id="bg-override">
  html, body {
    min-height: 100%;
    background: #ffffff !important;   /* pure white */
    color: #111111;                    /* dark text for readability */
  }
  :root { --bs-body-bg: #ffffff; --bs-body-color: #111111; }


/* allow animating an angle */
@property --angle {
  syntax: '<angle>';
  inherits: false;
  initial-value: 0deg;
}

.btn-glow {
  --angle: 0deg;
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: .5rem;
  padding: .70rem 1.1rem;
  font: 600 14px/1.1 system-ui, -apple-system, "Segoe UI", Roboto, Inter, sans-serif;
  color: var(--btn-text);
  border-radius: var(--btn-radius);
  border: var(--btn-border) solid transparent;

  /* two backgrounds: inner fill + animated conic gradient for the border */
  background:
    linear-gradient(var(--btn-bg), var(--btn-bg)) padding-box,
    conic-gradient(from var(--angle),
      #22d3ee, #22c55e, #eab308, #f97316, #ef4444, #8b5cf6, #22d3ee) border-box;

  background-origin: border-box;
  background-clip: padding-box, border-box;

  text-decoration: none;
  cursor: pointer;
  transition: transform .15s ease, filter .15s ease;
  animation: spin var(--spin-speed) linear infinite;
}

.btn-glow:hover { transform: translateY(-1px); filter: drop-shadow(0 0 8px rgba(99,102,241,.35)); }
.btn-glow:active { transform: translateY(0); }
.btn-glow:focus-visible { outline: 3px solid #60a5fa; outline-offset: 2px; }

@keyframes spin { to { --angle: 360deg; } }

/* optional: slow/stop for reduced-motion users */
@media (prefers-reduced-motion: reduce) {
  .btn-glow { animation: none; }
}

/* size variants if you need */
.btn-sm { padding: .55rem .9rem; font-size: 13px; }
.btn-lg { padding: .85rem 1.25rem; font-size: 15px; }

  :root{
    --bg:#0b1226; --muted:#9aa5b1; --text:#e7eaee;
    --brand:#10b981; --brand2:#22d3ee; --accent1:#f59e0b; --accent2:#fb923c;
    --radius:18px; --maxw:1200px;
  }
  *{box-sizing:border-box}
  html,body{margin:0;background:linear-gradient(180deg,#0b1226,#0f172a 35%,#0b1226);color:var(--text);font-family:Poppins,system-ui,Segoe UI,Roboto,Arial,sans-serif}
  a{color:inherit;text-decoration:none}
  img{max-width:100%;display:block}
  .container{max-width:var(--maxw);margin:0 auto;padding:0 16px}

  
  /* ===== Featured grid ===== */
  .featured-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}

  /* ===== Button ===== */
  .gm-btn{background:#e11d48;color:#fff;border:0;border-radius:10px;padding:10px 14px;font-weight:600;cursor:pointer}
  .gm-btn:disabled{opacity:.6;cursor:not-allowed}

  /* ===== Modal ===== */
  .gm-modal{position:fixed;inset:0;display:none;z-index:60}
  .gm-modal.is-open{display:block}
  .gm-modal__backdrop{position:absolute;inset:0;background:rgba(0,0,0,.6)}
  .gm-modal__panel{
    position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);
    width:min(1100px,92vw);max-height:88vh;overflow:auto;border-radius:14px;
    background:#0f2a37;color:#e6f0f6; /* matches your dark theme */
    box-shadow:0 20px 60px rgba(0,0,0,.5)
  }
  .gm-modal__header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid rgba(148,163,184,.2)}
  .gm-modal__content{padding:18px}
  .gm-modal__close{background:transparent;color:#e6f0f6;border:0;font-size:22px;line-height:1;cursor:pointer}



  /* ===== Gradient Border Utilities ===== */
  .gbd{border:1px solid transparent; background:
       linear-gradient(#0b1329,#0b1329) padding-box,
       linear-gradient(135deg,var(--brand2),var(--brand)) border-box;
       border-radius:var(--radius)}
  .gbd-warm{border:1px solid transparent; background:
       linear-gradient(#0b1329,#0b1329) padding-box,
       linear-gradient(135deg,var(--accent1),var(--accent2)) border-box;
       border-radius:var(--radius)}
  .gbd-pill{border-radius:9999px}
  .gfill{background:linear-gradient(135deg,var(--brand),var(--brand2))}
  .gshadow{box-shadow: 0 10px 20px rgba(34,211,238,.10), 0 20px 40px rgba(16,185,129,.08)}
  .gtint{background:linear-gradient(135deg, rgba(34,211,238,0.18), rgba(16,185,129,0.18))}

  /* NAV */
  .nav{position:sticky;top:0;z-index:40;background:rgba(11,18,38,.75);backdrop-filter:blur(10px);border-bottom:1px solid rgba(148,163,184,.16);height:72px}
  .nav-inner{display:grid;grid-template-columns:auto 1fr auto;align-items:center;height:100%}
  .btn{padding:10px 14px;border-radius:12px;border:1px solid rgba(148,163,184,.22);background:#0b1329;color:var(--text);cursor:pointer}
  .btn-primary{background:linear-gradient(135deg,var(--brand),var(--brand2));border:none;color:#06121e;font-weight:700}
  .btn-gb{border:1px solid transparent; background:
       linear-gradient(#0b1329,#0b1329) padding-box,
       linear-gradient(135deg,var(--brand2),var(--brand)) border-box;
       color:#e7eaee}
  .btn-gb:hover{filter:brightness(1.08)}
  .btn-ghost{background:transparent;border:1px solid rgba(148,163,184,.22);color:#e7eaee;border-radius:12px;padding:8px 12px}
  .logo{display:flex;gap:8px;align-items:center;margin-left:12px}

  /* DRAWER */
  .drawer{position:fixed;inset:0 auto 0 0;width:280px;transform:translateX(-100%);transition:.25s;z-index:50;padding:14px;border-right:0}
  .drawer.open{transform:translateX(0)}
  .drawer-inner{height:100%;background:#0f2a37;border-radius:0 var(--radius) var(--radius) 0}
  .drawer.gbd{padding:0}
  .drawer a{display:block;color:#cfe1ee;padding:10px 12px;border-radius:10px;margin:4px 8px}
  .drawer a:hover{background:#0b2230}
  .backdrop{position:fixed;inset:0;background:rgba(2,6,23,.6);backdrop-filter:blur(2px);display:none;z-index:40}
  .backdrop.show{display:block}

  /* HERO */
  .hero-landing{position:relative;min-height:64vh;background:linear-gradient(180deg,rgba(5,12,22,.55),rgba(5,12,22,.70)),url("<?= $BASE ?>/assets/back.png") center/cover no-repeat}
  .hero-overlay{max-width:1200px;margin:0 auto;padding:80px 16px 40px}
  .hero-title{font-size:clamp(28px,4.5vw,44px);font-weight:800;margin:10px 0 18px}
  .hero-search{display:grid;grid-template-columns:220px 1fr 64px;gap:10px;border-radius:16px;padding:10px;backdrop-filter:blur(6px)}
  .hero-search.gbd{background:
       linear-gradient(rgba(2,6,23,.65),rgba(2,6,23,.65)) padding-box,
       linear-gradient(135deg,var(--brand2),var(--brand)) border-box}
  .input{width:100%;padding:12px 14px;border-radius:12px;border:1px solid rgba(148,163,184,.22);background:#0b1329;color:#e7eaee;outline:none}
  .hero-go{border:0;border-radius:12px;font-size:22px;cursor:pointer;color:#062c2f;background:linear-gradient(135deg,var(--accent1),var(--accent2))}
  .hero-kpis{display:flex;gap:24px;flex-wrap:wrap;margin-top:18px}
  .kpi{display:flex;flex-direction:column;border-radius:16px;padding:14px}
  .kpi.gbd{background:linear-gradient(135deg,rgba(2,6,23,.80),rgba(15,23,42,.80))}
  .kpi .num{font-weight:800;font-size:22px;background:linear-gradient(135deg,var(--brand2),var(--brand));-webkit-background-clip:text;background-clip:text;color:transparent}
  .kpi .lbl{color:#9aa5b1}

  
/* ===== Hero header background image ===== */
.hero-landing{
  position: relative;
  /* the photo */
  background-image: url('<?= url("uploads/header.jpg") ?>');
  background-size: cover;          /* fill the box */
  background-position: center;     /* keep hands+house centered */
  background-repeat: no-repeat;
  border-radius: 20px;
  overflow: hidden;

  /* spacing/height – adjust to your taste */
  padding: 64px 18px 34px;
  min-height: clamp(360px, 58vh, 640px);
  color: #fff;
}

/* dark overlay so text/search stays readable */
.hero-landing::after{
  content: "";
  position: absolute; inset: 0;
  border-radius: inherit;
  background: linear-gradient(180deg, rgba(2,6,23,.55), rgba(2,6,23,.72));
}

/* keep inner content above the overlay */
.hero-landing .hero-overlay{ position: relative; z-index: 1; }

/* (optional) tighten the image crop on small screens */
@media (max-width: 640px){
  .hero-landing{ background-position: center 35%; padding-top: 48px; }
}





  /* SECTIONS */
  section{padding:40px 0}
  .section-head{display:flex;justify-content:space-between;gap:10px;align-items:flex-end;margin-bottom:14px}
  .section-head h2{margin:0;font-size:clamp(22px,3vw,30px)}
  .muted{color:#9aa5b1}

  /* PROPERTIES GRID */
  .cards{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
  @media (max-width:1050px){.cards{grid-template-columns:repeat(2,1fr)}}
  @media (max-width:700px){.cards{grid-template-columns:1fr}}
  /* TRY to beautify unknown property-card markup */
  .cards > *{border-radius:18px}
  .cards > *:not(.gbd){border:1px solid transparent; background:
       linear-gradient(#0d1627,#0d1627) padding-box,
       linear-gradient(135deg,rgba(34,211,238,.35),rgba(16,185,129,.35)) border-box}
  .cards > *{box-shadow: 0 10px 22px rgba(0,0,0,.35), 0 0 0 1px rgba(148,163,184,.12) inset}
  .cards .np-link, .cards .js-view-details, .cards a[href^="tel:"]{
    display:inline-block; padding:8px 12px; border-radius:12px; margin:4px 6px 0 0;
    border:1px solid transparent; background:
      linear-gradient(#0b1329,#0b1329) padding-box,
      linear-gradient(135deg,var(--brand2),var(--brand)) border-box
  }

  /* TEAM */
  .team-card{display:flex;align-items:center;gap:14px;padding:14px 16px;border-radius:18px;background:linear-gradient(180deg,#0d2435,#092031)}
  .team-photo{width:64px;height:64px;border-radius:9999px;object-fit:cover;background:#0b1520;border:1px solid rgba(255,255,255,.06)}
  .team-fallback{display:flex;align-items:center;justify-content:center;font-weight:700;color:#7dd3fc}
  .team-meta{flex:1 1 auto;min-width:0}
  .team-name{font-weight:700}
  .team-role{color:#9fb2c0;font-size:14px}
  .team-phone{color:#b6ffea;font-size:13px}
  .team-cta{padding:10px 16px;border-radius:9999px}

  /* ===== End-to-End Assistance background ===== */
.assist-hero{
  position: relative;
  border-radius: 20px;
  padding: 44px 18px;           /* adjust spacing as you like */
  min-height: clamp(320px, 52vh, 560px);
  color: #fff;

  background-image: url('<?= url("uploads/house-keys.jpg") ?>');
  background-size: cover;       /* fill area (crops edges if needed) */
  background-position: center;  /* keeps subject centered */
  background-repeat: no-repeat;
  overflow: hidden;
}

/* dark overlay for contrast */
.assist-hero::before{
  content: "";
  position: absolute; inset: 0;
  border-radius: inherit;
  background: linear-gradient(180deg, rgba(2,6,23,.55), rgba(2,6,23,.72));
}

/* ensure inner content is above overlay */
.assist-hero > *{ position: relative; z-index: 1; }

/* on small screens, show a touch higher crop */
@media (max-width: 640px){
  .assist-hero{ background-position: center 35%; padding-top: 32px; }
}


  /* MODALS (generic) */
  .gx-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;z-index:4000}
  .gx-modal.show{display:flex!important}
  .gx-backdrop{position:absolute;inset:0;background:rgba(2,6,23,.6)}
  .gx-sheet{position:relative;z-index:2;border-radius:14px;width:min(720px,92vw);padding:16px;
            border:1px solid transparent; background:
              linear-gradient(#0d1729,#0a1426) padding-box,
              linear-gradient(135deg,var(--brand2),var(--brand)) border-box}
  .gx-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
  .gx-close{cursor:pointer;border:0;background:transparent;color:#cbd5e1;font-size:20px}

  /* Center the property modal nicely */
  #npModal .gx-sheet{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);max-height:90vh;overflow:auto}

  /* FAQ */
  .faq{display:grid;gap:10px}
  .faq-item{border-radius:14px;overflow:hidden; border:1px solid transparent; background:
      linear-gradient(#0d1b2a,#0a1222) padding-box,
      linear-gradient(135deg,var(--brand2),var(--brand)) border-box}
  .faq-q{padding:14px 16px;display:flex;justify-content:space-between;align-items:center;cursor:pointer}
  .faq-a{max-height:0;overflow:hidden;padding:0 16px;transition:max-height .25s ease,padding .25s ease}
  .faq-item.open .faq-a{max-height:320px;padding:0 16px 14px}

  /* Footer */
  footer{padding:26px 0;border-top:1px solid rgba(148,163,184,.16);color:var(--muted);text-align:center}
  .socials{display:flex;gap:12px;justify-content:center;margin-top:8px}
  .socials a{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:9999px}
  .socials a.gbd{background:linear-gradient(#0b1329,#0b1329) padding-box,
                 linear-gradient(135deg,var(--brand2),var(--brand)) border-box}

/* Extra styles used later (as in your file) */
:root{
  --g1:#ff4d6d; --g2:#6a11cb; --g3:#2575fc;
  --radius:16px; --pad:1px;
}
/* Gradient border */
.g-bord{ position:relative; border-radius:var(--radius); }
.g-bord::before{
  content:""; position:absolute; inset:0; padding:var(--pad); border-radius:var(--radius);
  background:linear-gradient(135deg,var(--g1),var(--g2),var(--g3));
  -webkit-mask:
    linear-gradient(#000 0 0) content-box, 
    linear-gradient(#000 0 0);
  -webkit-mask-composite:xor; mask-composite:exclude;
  pointer-events:none;
}
/* Hover-only gradient glow */
.g-glow-hover{ position:relative; }
.g-glow-hover::after{
  content:""; position:absolute; inset:-6px; border-radius:calc(var(--radius) + 6px);
  background:linear-gradient(135deg,var(--g1),var(--g2),var(--g3));
  filter:blur(16px); opacity:0; transition:opacity .25s ease;
  z-index:-1;
}
.g-glow-hover:hover::after{ opacity:0.9; }

/* Buttons with gradient border + hover glow */
.btn{ display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
  padding:.65rem 1rem; border-radius:12px; background:#0f172a; color:#e6f1ff;
  text-decoration:none; cursor:pointer; position:relative; }
.btn.g-bord{ background:#0b1220; }
.btn:hover{ filter:brightness(1.06); }

/* Property card */
.card{ background:#0b1220; border-radius:18px; padding:14px; overflow:hidden; }
.card.g-bord{ background:#0b1220; }
.card .prop-img{ width:100%; height:220px; object-fit:cover; border-radius:12px; display:block; }

/* Hide shadow until hover */
.card.g-glow-hover::after{ opacity:0; }
.card.g-glow-hover:hover::after{ opacity:1; }

/* Key numbers (inner boxes gradient) */
.key-box{ background:#0b1220; border-radius:16px; padding:16px; text-align:center; }
.key-box .inner{ border-radius:12px; padding:10px;
  background:linear-gradient(135deg, rgba(255,77,109,.15), rgba(38,115,252,.15)); }

/* Modals with gradient shells */
.modal{ position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:90; }
.modal.open{ display:flex; }
.modal .dlg{ width:min(680px,92vw); border-radius:18px; position:relative; }
.modal .dlg .shell{ position:absolute; inset:0; border-radius:18px; background:linear-gradient(135deg,var(--g1),var(--g2),var(--g3)); filter:blur(18px); opacity:.35; }
.modal .dlg .body{ position:relative; background:#0b1220; border-radius:18px; padding:20px; }

.grid{ display:grid; gap:16px; }
.grid.props{ grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); }

/* Clean white form modals */
.form-modal .gx-sheet{background:#fff;color:#0f172a;border:1px solid rgba(2,6,23,.08)}
.form-modal .gx-head{color:#0f172a}
.form-modal .gx-body{padding:6px}
.simple-grid{display:grid;gap:12px}
.simple-grid .two{display:grid;gap:10px;grid-template-columns:1fr 1fr}
@media (max-width:600px){ .simple-grid .two{grid-template-columns:1fr} }
.input-clean{width:100%;padding:10px 6px;border:0;outline:none;border-bottom:1px solid #e2e8f0;background:transparent;color:#0f172a}
.input-clean:focus{border-bottom-color:#22d3ee}
.choice{display:flex; gap:16px; padding:6px 0}
.row-actions{display:flex; gap:10px; align-items:center}
.btn-primary-light{background:#f59e0b;color:#fff;border:0;border-radius:10px;padding:10px 14px;font-weight:700;cursor:pointer}



/* === Top Links section === */
.toplinks-wrap{
  background:#0b1620;
  border-top:1px solid rgba(148,163,184,.16);
  border-bottom:1px solid rgba(148,163,184,.12);
  padding:44px 0;
}
.toplinks{
  max-width:1200px; margin:0 auto; padding:0 16px;
}
.toplinks h3{
  margin:0 0 20px; text-align:center; font-size:clamp(18px,3vw,22px);
}
.toplinks .grid{
  display:grid; gap:22px;
  grid-template-columns:repeat(4,minmax(180px,1fr));
}
@media (max-width:1100px){ .toplinks .grid{grid-template-columns:repeat(3,1fr)} }
@media (max-width:760px){  .toplinks .grid{grid-template-columns:repeat(2,1fr)} }
@media (max-width:520px){  .toplinks .grid{grid-template-columns:1fr} }

.tgroup{ padding:16px; border-radius:14px; background:#0f2a37 }
.tgroup h4{ margin:0 0 10px; font-size:16px; font-weight:800 }
.tgroup a{
  display:block; color:#cbd5e1; text-decoration:none; padding:4px 0;
}
.tgroup a:hover{ color:#fff; text-decoration:underline }

/* follow row */
.follow-row{
  display:flex; gap:12px; align-items:center; flex-wrap:wrap;
  margin:26px auto 0; padding:14px; border-radius:16px; background:#0f2a37;
}
.follow-row .title{ font-weight:800; margin-right:4px }
.grad-pill{
  display:inline-flex; align-items:center; gap:8px;
  padding:10px 14px; border-radius:999px; font-weight:700;
  color:#062126; background:linear-gradient(135deg,#22d3ee,#10b981);
}
.grad-pill svg{ width:18px; height:18px }

/* === Awards/Certificates slider (fixed height, no crop) === */
.awards-sec{padding:36px 0;border-top:1px solid rgba(148,163,184,.14)}
.awards-head{display:flex;justify-content:space-between;align-items:flex-end;margin:0 0 16px}

.awards-slider{position:relative; padding:0 28px;} /* room for arrows */
.aw-view{
  overflow:hidden; border-radius:16px;
  /* give the box a stable height */
  height: clamp(240px, 36vw, 420px);
}
.awards-track{display:flex; transition:transform .45s ease;}
.award-slide{flex:0 0 100%; display:flex;}
.award-card{
  flex:1 1 auto;
  display:flex; 
  align-items:center;
  justify-content:center;
  height:100%;
  border-radius:18px; 
  padding:18px;
  background:linear-gradient(160deg,#f6d58a 0%, #f8e6b8 40%, #ffffff 100%);
}
.award-card img{
  /* show whole photo inside the box */
  max-width:100%; max-height:100%;
  width:auto; height:auto;           /* important: avoid stretching */
  object-fit:contain; object-position:center;
  display:block;
}

/* arrows */
.aw-btn{
  position:absolute; top:50%; transform:translateY(-50%);
  width:44px; height:44px; border:0; border-radius:999px; cursor:pointer;
  display:grid; place-items:center; z-index:10;
  color:#062126; font-weight:900;
  background:linear-gradient(135deg,#22d3ee,#10b981);
  box-shadow:0 6px 18px rgba(0,0,0,.25);
}
.aw-btn.prev{left:6px}
.aw-btn.next{right:6px}

@media (max-width:700px){
  .aw-view{height: clamp(200px, 42vw, 320px);}
  .aw-btn{width:38px; height:38px}
}

<style>
  /* Section box */
  .gm-featured-box{
    background:#0f1c2a;
    border:1px solid rgba(141,223,255,.18);
    border-radius:16px;
    padding:18px 18px 22px;
    width:min(1200px,96vw);
    margin:0 auto 24px;
  }
  .gm-featured-head{
    display:flex;align-items:center;justify-content:space-between;
    margin-bottom:14px
  }

  /* Force 3 columns (desktop) */
  .gm-featured-grid-3{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr)) !important; /* 3 per row */
    gap:16px; align-items:stretch;
  }
  /* Make every card equal height */
  .gm-featured-grid-3 .gm-card-wrap{ display:flex }
  .gm-featured-grid-3 .gm-card-wrap > *{ width:100% }

  /* Modal grid can be auto-fill */
  .gm-featured-grid-more{
    display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px;
  }

  /* Button */
  .gm-btn{background:#e11d48;color:#fff;border:0;border-radius:10px;padding:10px 14px;font-weight:600;cursor:pointer}
  .gm-btn:disabled{opacity:.6;cursor:not-allowed}

  /* Modal (unchanged) */
  .gm-modal{position:fixed;inset:0;display:none;z-index:60}
  .gm-modal.is-open{display:block}
  .gm-modal__backdrop{position:absolute;inset:0;background:rgba(0,0,0,.6)}
  .gm-modal__panel{
    position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);
    width:min(1100px,92vw);max-height:88vh;overflow:auto;border-radius:14px;
    background:#0f2a37;color:#e6f0f6;box-shadow:0 20px 60px rgba(0,0,0,.5)
  }
  .gm-modal__header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid rgba(148,163,184,.2)}
  .gm-modal__content{padding:18px}
  .gm-modal__close{background:transparent;color:#e6f0f6;border:0;font-size:22px;line-height:1;cursor:pointer}

  /* Optional responsive fallbacks */
  @media (max-width:900px){ .gm-featured-grid-3{ grid-template-columns:repeat(2,1fr) !important } }
  @media (max-width:600px){ .gm-featured-grid-3{ grid-template-columns:1fr !important } }
</style>

<style>
/* === FIX: normalize card image height + center arrow buttons === */
.card-media{
  position: relative !important;
  height: 240px;                 /* same height for all cards */
  line-height: 0;                /* remove inline-gap */
  border-radius: 12px;
  overflow: hidden;
  background: #0f172a;
}

/* make media fill the box */
.card-media > img{
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* in case any card uses iframe/video, make it behave like the image */
.card-media iframe,
.card-media video{
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  border: 0;
  object-fit: cover;
}

/* arrows: overlay + perfectly centered vertically */
.card-media-nav{
  position: absolute !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
  z-index: 5;
  width: 38px;
  height: 38px;
  border: 0;
  border-radius: 50%;
  background: rgba(2,6,23,.65);
  color: #fff;
  font-weight: 800;
  display: grid;
  place-items: center;
  cursor: pointer;
  user-select: none;
}
.card-media-nav:hover{ background: rgba(2,6,23,.85); }
.card-media-nav.prev{ left: 8px; }
.card-media-nav.next{ right: 8px; }
</style>

<style>
/* play button overlay (centered) */
.card-media-play{
  position:absolute; inset:auto auto 50% 50%;
  transform:translate(-50%,-50%);
  z-index:6;
  width:56px; height:56px;
  border:0; border-radius:50%;
  display:grid; place-items:center;
  background:rgba(2,6,23,.72);
  color:#fff; font-size:20px; font-weight:800;
  cursor:pointer; user-select:none;
  box-shadow:0 6px 18px rgba(0,0,0,.35);
}
.card-media-play:hover{ background:rgba(2,6,23,.88); }
/* Drawer submenu (Builder Admin) */
.drawer details.submenu { padding: 2px 12px; border-radius: 10px; }
.drawer details.submenu summary {
  cursor: pointer; list-style: none; padding: 10px 8px; border-radius: 10px;
  background: rgba(148,163,184,.06); transition: background .2s;
}
.drawer details.submenu[open] summary { background: rgba(148,163,184,.12); }
.drawer details.submenu summary::-webkit-details-marker { display:none; }
.drawer .submenu-links { display:grid; gap:6px; padding:10px 6px 6px 12px; }
.drawer .submenu-links a { display:block; padding:8px 10px; border-radius:8px; background: rgba(148,163,184,.06); }
.drawer .submenu-links a:hover { background: rgba(148,163,184,.12); }

/* Make side drawer scroll instead of cutting off the bottom */
.drawer { overflow: hidden; } /* keep backdrop/rounded edges clean */
.drawer .drawer-inner {
  max-height: calc(100vh - 24px);
  overflow-y: auto;
  padding-bottom: 18px;
  display: block; /* ensure it can scroll */
}

/* Optional: nicer spacing for submenu links */
.drawer .submenu-links a { margin-bottom: 8px; }


/* Drawer submenu */
.drawer details.submenu { padding: 2px 12px; border-radius: 10px; }
.drawer details.submenu summary {
  cursor:pointer; list-style:none; padding:10px 8px; border-radius:10px;
  background:rgba(148,163,184,.06); transition:background .2s;
}
.drawer details.submenu[open] summary { background:rgba(148,163,184,.12) }
.drawer details.submenu summary::-webkit-details-marker{display:none}
.drawer .submenu-links{display:grid; gap:6px; padding:10px 6px 6px 12px}
.drawer .submenu-links a{display:block; padding:8px 10px; border-radius:8px; background:rgba(148,163,184,.06)}
.drawer .submenu-links a:hover{background:rgba(148,163,184,.12)}
.drawer .drawer-inner{max-height:calc(100vh - 24px); overflow-y:auto; padding-bottom:18px; display:block}

.footer-builders{
  background:#0f2430;border:1px solid rgba(148,163,184,.15);
  border-radius:12px;padding:18px;margin:16px 0 12px;
}
.footer-builders__title{margin:0 0 10px;font-weight:700}
.builder-tags{display:flex;flex-wrap:wrap;gap:10px;list-style:none;margin:0;padding:0}
.builder-tags a{
  display:inline-block;padding:8px 12px;border:1px solid rgba(148,163,184,.2);
  border-radius:9999px;background:#0b1c27;color:#e6f0f6;text-decoration:none
}
.builder-tags a:hover{transform:translateY(-1px)}
.builder-tags{
  display:flex; gap:10px; flex-wrap:wrap; margin:10px 0;
  list-style:none; padding:0;
}
.builder-tags li a{
  display:inline-block; padding:10px 14px; border-radius:999px;
  border:1px solid rgba(148,163,184,.2);
  background:#0b1c27; color:#e6f0f6; text-decoration:none;
}
.builder-tags li a.pill-add{
  border:none; font-weight:600;
  background:linear-gradient(90deg,#6ee7ff,#a78bfa);
}


/* ============ Variables & base ============ */
:root{
  --bg:#0b1620; --panel:#0f2430; --line:rgba(148,163,184,.18);
  --text:#e6f0f6; --muted:#9fb2c0; --brand:#6ee7ff; --brand2:#a78bfa;
  --radius:14px; --container:1200px;
}
html {scroll-behavior:smooth;}
body {background:var(--bg); color:var(--text); -webkit-text-size-adjust:100%;}

/* Fluid type */
:root{
  --fs-xs: clamp(12px, 1.2vw, 13px);
  --fs-sm: clamp(13px, 1.3vw, 14px);
  --fs-md: clamp(14px, 1.6vw, 16px);
  --fs-lg: clamp(16px, 2.0vw, 20px);
  --fs-xl: clamp(18px, 3vw, 24px);
}
body, input, button {font-size:var(--fs-md); line-height:1.5;}
h1,.title{font-size:var(--fs-xl); line-height:1.2}

/* Container & panels */
.container{max-width:var(--container); margin:0 auto; padding:16px;}
.panel{background:var(--panel); border:1px solid var(--line); border-radius:var(--radius);}
.pad{padding:14px}

/* Images */
img, video{max-width:100%; height:auto; display:block}
img{object-fit:cover}

/* Buttons */
.btn{display:inline-flex; align-items:center; gap:8px; padding:.7rem 1rem;
  border-radius:12px; border:1px solid var(--line); background:#102a37; color:#e6f0f6}
.btn.grad{border:none; background:linear-gradient(90deg,var(--brand),var(--brand2));}

/* Cards grid (sibling projects, gallery, listings) */
.grid-cards{display:grid; gap:12px; grid-template-columns:repeat(3,minmax(0,1fr))}
@media (max-width:1024px){ .grid-cards{grid-template-columns:repeat(2,1fr)} }
@media (max-width:640px){ .grid-cards{grid-template-columns:1fr} }

/* Gallery thumbnails */
.galleryGrid{display:grid; gap:10px; grid-template-columns:repeat(4,minmax(0,1fr))}
.galleryGrid img{height:180px; border-radius:10px}
@media (max-width:1024px){ .galleryGrid{grid-template-columns:repeat(3,1fr)} }
@media (max-width:768px){ .galleryGrid{grid-template-columns:repeat(2,1fr)} }
@media (max-width:480px){ .galleryGrid{grid-template-columns:1fr} .galleryGrid img{height:220px} }

/* Hero media */
.hero{display:grid; grid-template-columns:1fr 360px; gap:16px}
.hero .heroMedia{height:420px; border-radius:14px; overflow:hidden}
@media (max-width:1024px){
  .hero{grid-template-columns:1fr}
  .hero .heroMedia{height:56vw; max-height:520px}
}

/* Floor plan rows -> become stacked on mobile */
.fpRow{
  display:grid;
  grid-template-columns:220px 1fr 160px 140px;
  gap:12px; align-items:center;
  padding:10px 0; border-bottom:1px solid rgba(148,163,184,.12)
}
.fpRow img{width:220px; height:140px; border-radius:10px; object-fit:cover}
@media (max-width:960px){
  .fpRow{grid-template-columns:160px 1fr}
  .fpRow > :nth-child(3), .fpRow > :nth-child(4){grid-column:1/-1}
  .fpRow img{width:100%; height:34vw; max-height:200px}
}
@media (max-width:520px){
  .fpRow{grid-template-columns:1fr}
  .fpRow img{height:48vw; max-height:240px}
}

/* EMI layout */
.emiWrap{display:grid; grid-template-columns:1fr 360px; gap:18px}
@media (max-width:1024px){ .emiWrap{grid-template-columns:1fr} }

/* Map */
.map{width:100%; height:360px; border:0; border-radius:12px}
@media (max-width:768px){ .map{height:300px}}
@media (max-width:480px){ .map{height:260px}}

/* Sticky bottom bar spacing on mobile */
.sticky .row{gap:12px}

/* Footer columns -> 2 cols tablet, 1 col mobile */
.footer-cols{display:grid; gap:16px; grid-template-columns:repeat(4,minmax(0,1fr))}
@media (max-width:1100px){ .footer-cols{grid-template-columns:repeat(3,1fr)} }
@media (max-width:820px){ .footer-cols{grid-template-columns:repeat(2,1fr)} }
@media (max-width:560px){ .footer-cols{grid-template-columns:1fr} }

/* “Builder tags” chips in footer */
.builder-tags{display:flex; flex-wrap:wrap; gap:10px; margin-top:8px}
.builder-tags a{
  display:inline-block; padding:.5rem .85rem; border-radius:999px;
  border:1px solid var(--line); background:#0b1c27; color:var(--text); white-space:nowrap
}

/* Tabs bar scroll on small screens */
.tabs .wrap{display:flex; gap:18px; overflow:auto; -webkit-overflow-scrolling:touch}
.tabs a{white-space:nowrap}

</style>

</style>
</head>
<body>

<!-- NAV -->
<nav class="nav">
  <div class="container nav-inner">
    <button class="btn-glow" id="openMenu" aria-label="Open menu">☰</button>
    <div></div>
    <div style="display:flex;gap:10px;align-items:center">
      <button class="btn-glow" id="btnLogin">Login</button>
      <button class="btn-glow" id="btnSignup">Sign Up</button>
      <div class="logo" title="GM HOMEZ">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" aria-hidden="true">
          <path d="M3 11.5 12 4l9 7.5v7A1.5 1.5 0 0 1 19.5 20h-15A1.5 1.5 0 0 1 3 18.5v-7Z" stroke="currentColor" stroke-width="1.6"/>
          <path d="M9 20v-6h6v6" stroke="currentColor" stroke-width="1.6"/>
        </svg>
        <b>GM HOMEZ</b>
      </div>
    </div>
  </div>
</nav>

<!-- DRAWER -->
<aside id="drawer" class="drawer gbd" aria-hidden="true">
  <div class="drawer-inner">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px">
      <strong>Menu</strong><button id="closeMenu" class="btn-ghost" aria-label="Close">✕</button>
    </div>
    <a href="#properties">Properties</a>
    <a href="#agents">Team</a>
    <a href="#faq">FAQ</a>
    <a href="#contact">Contact</a>
    <a href="#top to links">Top Links to Search your Home</a>
    <hr style="border-color:rgba(148,163,184,.16)">
    <a href="<?= $BASE ?>/admin/properties.php">Admin → Properties (Add/Edit/Delete)</a>

<!-- City Pages (open the public “For Sale in …” page) -->
<details class="submenu">
  <summary>Buy Property</summary> <!-- (you can rename to “Buy Property” if you prefer) -->
  <div class="submenu-links">
    <a href="<?= $BASE ?>/builders.php?city=Bangalore">Bangalore</a>
    <a href="<?= $BASE ?>/builders.php?city=Mumbai">Mumbai</a>
    <a href="<?= $BASE ?>/builders.php?city=Pune">Pune</a>
    <a href="<?= $BASE ?>/builders.php?city=Gurgaon">Gurgaon</a>
    <a href="<?= $BASE ?>/builders.php?city=Hyderabad">Hyderabad</a>
  </div>
</details>




   <!-- Builder Admin (expand/collapse) -->
<details class="submenu" style="margin:6px 0 4px">
  <summary>Builders</summary>
  <div class="submenu-links">
<a href="<?= $BASE ?>/builders.php?builder=Prestige%20Group">Prestige Group</a>
  <a href="<?= $BASE ?>/builders.php?builder=Sobha%20Limited">Sobha Limited</a>
  <a href="<?= $BASE ?>/builders.php?builder=Kolte%20Patil%20Developers">Kolte Patil Developers</a>
  <a href="<?= $BASE ?>/builders.php?builder=Godrej%20Properties">Godrej Properties</a>
  <a href="<?= $BASE ?>/builders.php?builder=Brigade%20Group">Brigade Group</a>
  </div>
</details>


    <a href="<?= $BASE ?>/admin/team.php">Admin → Team (Add/Edit/Delete)</a>
    <a href="<?= $BASE ?>/admin/login.php">Admin Login</a>
  </div>
</aside>
<div id="drawerBackdrop" class="backdrop"></div>

<!-- HERO -->
<header class="hero-landing">
  <div class="hero-overlay">
    <div class="hero-title">0% Brokerage · 100% Happiness</div>
    <div class="hero-search gbd gshadow" role="search">
      <select class="input" aria-label="Select city">
        <option value="">All Cities</option><option>Bangalore</option><option>Mysuru</option><option>Hyderabad</option><option>Chennai</option>
      </select>
      <input id="heroQuery" class="input" placeholder="Enter a locality, builder or project" aria-label="Search query">
      <button id="heroGo" class="hero-go" aria-label="Search">➜</button>
    </div>
    <!-- Advanced filters (drop this near your hero search) -->
<div id="heroFilters" style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px">
  <input id="heroLocality" placeholder="Locality (e.g., Koramangala)" style="padding:8px 10px;border-radius:10px;border:1px solid rgba(148,163,184,.25);background:#0f2a37;color:#e6f0f6">
  <select id="heroBhk" style="padding:8px 10px;border-radius:10px;border:1px solid rgba(148,163,184,.25);background:#0f2a37;color:#e6f0f6">
    <option value="">BHK</option>
    <option>1</option><option>2</option><option>3</option><option>4</option><option>5</option>
  </select>
  <input id="heroPriceMin" type="number" placeholder="Min Price" style="padding:8px 10px;border-radius:10px;border:1px solid rgba(148,163,184,.25);background:#0f2a37;color:#e6f0f6">
  <input id="heroPriceMax" type="number" placeholder="Max Price" style="padding:8px 10px;border-radius:10px;border:1px solid rgba(148,163,184,.25);background:#0f2a37;color:#e6f0f6">
</div>

    <div class="hero-kpis" aria-label="Key metrics">
      <div class="kpi gbd gshadow"><span class="num" data-count="30000">30000+</span><span class="lbl">Happy Customers</span></div>
      <div class="kpi gbd gshadow"><span class="num" data-count="20000">2000000</span><span class="lbl">₹ Cr Worth Homes Sold</span></div>
      <div class="kpi gbd gshadow"><span class="num" data-count="350">250</span><span class="lbl">Relationship Managers</span></div>
      <div class="kpi gbd gshadow"><span class="num" data-count="500">300+</span><span class="lbl">Verified Projects</span></div>
    </div>
  </div>
</header>


<!-- ABOUT + KEY NUMBERS -->
<section id="about">
  <div class="container" style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div class="card gbd gshadow gtint" style="padding:18px">
      <div class="section-head"><h2>Our Company</h2>
      <div class="btn-glow">
        <a href="about.php" class="btn-link">About GM HOMEZ</a>
      </div>
      
    </div>
      <p class="muted">We simplify buying, renting, and listing with data-backed pricing and expert guidance.</p>
      <h3>Our Vision</h3><p class="muted">Help everyone discover the right home at the right price—confidently.</p>
      <h3>Our Mission</h3><p class="muted">Verified listings, transparent terms, and stellar support across the journey.</p>
    </div>
    <div class="card gbd gshadow gtint" style="padding:18px">
      <div class="section-head"><h2>Key Numbers</h2><div class="muted">Live counters</div></div>
      <div class="stats" id="statsBox" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px">
        <div class="stat gbd gshadow" style="padding:16px"><div class="num" data-count="1250">0</div><div class="lbl">Apartments Sold</div></div>
        <div class="stat gbd gshadow" style="padding:16px"><div class="num" data-count="980">0</div><div class="lbl">Clients</div></div>
        <div class="stat gbd gshadow" style="padding:16px"><div class="num" data-count="730">0</div><div class="lbl">Houses Rented</div></div>
        <div class="stat gbd gshadow" style="padding:16px"><div class="num" data-count="312">0</div><div class="lbl">Listed Properties</div></div>
      </div>
    </div>
  </div>
</section>

<?php
  $props = function_exists('read_properties') ? (read_properties() ?: []) : [];
  $VISIBLE = 6; // 3 + 3
  $total   = count($props);
  $visible = array_slice($props, 0, $VISIBLE);
  $more    = array_slice($props, $VISIBLE);
?>

<section id="featured" style="padding:28px 0">
  <div class="gm-featured-box">
    <div class="gm-featured-head">
      <h2 style="margin:0;font-size:22px">Featured Properties</h2>
      <?php if ($total > $VISIBLE): ?>
        <button id="gm-open-more" class="gm-btn">View more (<?= htmlspecialchars($total - $VISIBLE) ?>)</button>
      <?php endif; ?>
    </div>

    <!-- exactly 6 cards: 3 columns x 2 rows -->
    <div class="gm-featured-grid-3">
      <?php foreach ($visible as $p): ?>
        <div class="gm-card-wrap"><?php renderPropertyCard($p); ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($total > $VISIBLE): ?>
  <!-- Popup with the remaining properties -->
  <div id="gm-more-modal" class="gm-modal" aria-hidden="true" role="dialog" aria-labelledby="gmModalTitle">
    <div class="gm-modal__backdrop" data-close></div>
    <div class="gm-modal__panel" role="document" tabindex="-1">
      <div class="gm-modal__header">
        <h3 id="gmModalTitle" style="margin:0;font-size:18px">More Featured Properties</h3>
        <div style="display:flex;gap:10px;align-items:center">
          <a href="<?= ($BASE ?? '') ?>/properties.php" style="color:#8df;text-decoration:none">Open full page</a>
          <button class="gm-modal__close" title="Close" aria-label="Close" data-close>&times;</button>
        </div>
      </div>
      <div class="gm-modal__content">
        <div class="gm-featured-grid-more">
          <?php foreach ($more as $p): ?>
            <div class="gm-card-wrap"><?php renderPropertyCard($p); ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>



<!-- ===== End-To-End Assistance (Route Map) ===== -->
<section id="assistance" aria-labelledby="assistTitle"
  style="
    position:relative;
    padding:100px 0 220px;
    color:#f4f5f8;
    border-top:1px solid rgba(148,163,184,.12);
    border-bottom:1px solid rgba(148,163,184,.12);
    border-radius:20px;
    overflow:hidden;
    /* BG image + dark overlay for contrast */
    background:
      linear-gradient(180deg, rgba(2,6,23,.55), rgba(2,6,23,.78)),
      url('<?= url("uploads/house-keys.jpg") ?>') center/cover no-repeat;
  ">
  <div class="container">
    <div style="max-width:1100px;margin:0 auto;text-align:center">
      <h2 id="assistTitle" style="margin:0 0 8px;font-size:clamp(24px,4vw,36px)">End To End Assistance</h2>
      <div style="height:2px;background:rgba(22,3,129,.28);border-radius:999px;margin:22px 0"></div>

      <div class="btn-glow" style="display:flex;justify-content:space-between;gap:12px">
        <?php
        $steps = [
          ['🏢','Search & Shortlisting','Lacs of verified listings from 500+ reputed developers','Get in touch','contactModal'],
          ['🚗','Site Visit','Guided site visits with our relationship managers','Book site visit','siteVisitModal'],
          ['💰','Home Loan Assistance','Home loan facilitation through major banks across India','Apply Home Loan','loanModal'],
          ['📄','Legal Advice','Title checks & documentation support by legal partners','Talk to legal team','legalModal'],
          ['🔑','Unit Booking','Assistance with offers, negotiation and final booking','Book your unit','bookingModal'],
        ];
        foreach ($steps as $s): ?>
          <div class="rm-step" style="position:relative;flex:1;min-width:120px">
            <div class="rm-dot gbd gshadow" style="width:58px;height:58px;border-radius:50%;margin:0 auto 10px;display:grid;place-items:center;background:rgba(10,28,40,.75);color:#ffd;box-shadow:0 4px 18px rgba(0,0,0,.35);font-size:22px"><?= $s[0] ?></div>
            <div class="rm-label" style="font-weight:800;line-height:1.2"><?= str_replace(' ','<br>',htmlspecialchars($s[1])) ?></div>

            <div class="rm-pop gbd gshadow" style="position:absolute;top:calc(100% + 14px);left:50%;transform:translateX(-50%);display:none;width:min(420px,92vw);color:#e7eaee;padding:18px;z-index:80;text-align:center;background:#0b1329;border-radius:14px;border:1px solid rgba(148,163,184,.35)">
              <h4 style="margin:0 0 8px;font-size:20px;color:#fff"><?= htmlspecialchars($s[1]) ?></h4>
              <p style="margin:0 0 12px;color:#dbeafe"><?= htmlspecialchars($s[2]) ?></p>
              <a href="#" class="rm-cta btn btn-gb" data-open="<?= htmlspecialchars($s[4]) ?>"><?= htmlspecialchars($s[3]) ?></a>
              <span style="content:'';position:absolute;top:-7px;left:50%;transform:translateX(-50%) rotate(45deg);width:14px;height:14px;background:#0b1329;border-left:1px solid rgba(148,163,184,.35);border-top:1px solid rgba(148,163,184,.35)"></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>


<!-- LEAD MODAL (kept for Route Map CTAs only) -->
<div class="gx-modal" id="leadModal" role="dialog" aria-modal="true" aria-labelledby="leadTitle">
  <div class="gx-backdrop" data-close="1"></div>
  <div class="gx-sheet">
    <div class="gx-head"><h3 id="leadTitle" style="margin:0">Connect With GM HOMEZ</h3><button class="gx-close" data-close="1">✕</button></div>
    <form id="leadForm" class="gx-body">
      <div style="display:grid;gap:10px;grid-template-columns:1fr 1fr">
        <div style="grid-column:1/-1"><label>Name</label><input class="input" name="name" required></div>
        <div><label>Country Code</label><input class="input" name="code" value="+91" required></div>
        <div><label>Mobile No.</label><input class="input" name="phone" required></div>
      </div>
      <label style="margin-top:6px"><input type="checkbox" required> I agree to be contacted by GM HOMEZ via WhatsApp, SMS, Phone, Email etc.</label>
      <div style="margin-top:12px;display:flex;gap:10px;align-items:center">
        <button class="btn btn-gb" type="submit">GET CALL BACK</button>
        <span id="leadStatus" class="muted"></span>
      </div>
    </form>
  </div>
</div>

<!-- FAQ -->
<section id="faq">
  <div class="container">
    <div class="section-head"><h2>Frequently Asked Questions</h2><div class="muted">Clear answers to common doubts</div></div>
    <div class="faq">
      <div class="faq-item"><div class="faq-q"><span>1) Am I ready to be a homeowner?</span><span>➕</span></div><div class="faq-a"><p class="muted">Check savings (down payment), stable income, debts, emergency fund, credit score.</p></div></div>
      <div class="faq-item"><div class="faq-q"><span>2) Is renting or buying better?</span><span>➕</span></div><div class="faq-a"><p class="muted">Depends on horizon, EMI vs rent, tax, appreciation. 5+ years often favors buying.</p></div></div>
      <div class="faq-item"><div class="faq-q"><span>3) What is the lender's formula?</span><span>➕</span></div><div class="faq-a"><p class="muted">Total EMIs typically ≤ 40–50% of net monthly income.</p></div></div>
      <div class="faq-item"><div class="faq-q"><span>4) Am I ready to rent?</span><span>➕</span></div><div class="faq-a"><p class="muted">Budget deposit + setup costs, commute, amenities, lock-in/notice.</p></div></div>
      <div class="faq-item">
  <div class="faq-q"><span>5) What is RERA and why does it matter?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">RERA registration increases accountability—verify project ID, promised carpet area, timelines, and escrow safeguards before booking.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>6) Carpet vs Built-up vs Super Built-up?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">Carpet = usable inside walls. Built-up = carpet + walls/balcony. Super built-up = built-up + share of common areas (lobby, stairs, etc.).</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>7) What closing costs should I budget for?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">Stamp duty, registration, legal & due-diligence fees, loan processing, insurance, society membership, maintenance/security deposit, move-in costs.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>8) How much down payment is typical?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">Usually 10–25% of the price. Banks finance the rest based on LTV and your credit/income profile.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>9) What documents are needed for a home loan?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">KYC, income proofs (salary slips/ITR), bank statements, credit report, sale agreement/allotment letter, sanctioned plan, NOC/approvals as applicable.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>10) Under-construction vs Ready-to-move?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">Under-construction: lower entry price, phased payments, timeline risk. Ready: see before you buy, faster move-in, generally higher price.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>11) Freehold vs Leasehold?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">Freehold = full ownership of land/unit. Leasehold = right to use for a term; check remaining lease, ground rent, and conversion options.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>12) What should I check on a site visit?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">Sunlight & ventilation, water pressure, network signal, noise, seepage, fire exits, parking, lift count, approach roads, nearby social infra.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>13) Key legal checks before buying?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">Clear title, encumbrance search, approvals/sanctions, OC/CC for completed projects, society NOC, utility NOCs, property tax receipts.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>14) What is a token/booking amount?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">A small upfront payment to reserve the unit. Take a receipt and note the refund/forfeiture policy and deadline for executing the agreement.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>15) Agreement to Sale vs Sale Deed?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">Agreement to Sale sets terms/conditions and payment schedule. Sale Deed transfers ownership on full/defined consideration—registered with the sub-registrar.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>16) How do property tax & maintenance work?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">Owners pay annual/half-yearly property tax to the local body. Societies/associations charge monthly maintenance for common areas & services.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>17) What should landlords screen for?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">KYC of tenants, employment proof, references, clear rent agreement with lock-in, notice, upkeep rules, and security deposit terms.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>18) How to price my property for sale/rent?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">Use recent comparable deals, yield targets (rent ÷ price), locality demand, unit condition, and add realistic negotiation room.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>19) NRI purchase—anything special?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">Check FEMA rules, use NRE/NRO accounts for payments, keep PAN ready, and understand applicable TDS/compliance at purchase/resale.</p></div>
</div>

<div class="faq-item">
  <div class="faq-q"><span>20) What is a snag list (handover)?</span><span>➕</span></div>
  <div class="faq-a"><p class="muted">A checklist of defects to be fixed before possession: doors/windows, tiles, plumbing leaks, electrical points, paint, appliances, and fittings.</p></div>
</div>

    </div>


<!-- CONTACT -->
<div class="gx-modal form-modal" id="contactModal" role="dialog" aria-modal="true" aria-labelledby="contactTitle">
  <div class="gx-backdrop" data-close="1"></div>
  <div class="gx-sheet">
    <div class="gx-head"><h3 id="contactTitle">Get in touch</h3><button class="gx-close" data-close="1">✕</button></div>
    <form id="contactForm2" class="gx-body simple-grid">
      <label>Full Name<input class="input-clean" name="name" required></label>
      <label>Phone<input class="input-clean" name="phone" required></label>
      <label>Email<input class="input-clean" type="email" name="email"></label>
      <label>Message<textarea class="input-clean" name="message" rows="3"></textarea></label>
      <div class="row-actions"><button class="btn-primary-light" type="submit">Send</button><span class="status muted"></span></div>
    </form>
  </div>
</div>

<!-- SITE VISIT -->
<div class="gx-modal form-modal" id="siteVisitModal" role="dialog" aria-modal="true" aria-labelledby="visitTitle">
  <div class="gx-backdrop" data-close="1"></div>
  <div class="gx-sheet">
    <div class="gx-head"><h3 id="visitTitle">Book a site visit</h3><button class="gx-close" data-close="1">✕</button></div>
    <form id="visitForm" class="gx-body simple-grid">
      <label>Name<input class="input-clean" name="name" required></label>
      <label>Phone<input class="input-clean" name="phone" required></label>
      <label>Date<input class="input-clean" type="date" name="date" required></label>
      <label>Preferred Time<input class="input-clean" type="time" name="time"></label>
      <label>City<select class="input-clean" name="city"><option>Bangalore</option><option>Mysuru</option><option>Hyderabad</option></select></label>
      <div class="row-actions"><button class="btn-primary-light" type="submit">Book</button><span class="status muted"></span></div>
    </form>
  </div>
</div>

<!-- HOME LOAN -->
<div class="gx-modal form-modal" id="loanModal" role="dialog" aria-modal="true" aria-labelledby="loanTitle">
  <div class="gx-backdrop" data-close="1"></div>
  <div class="gx-sheet">
    <div class="gx-head"><h3 id="loanTitle">Apply for home loan</h3><button class="gx-close" data-close="1">✕</button></div>
    <form id="loanForm" class="gx-body simple-grid">
      <label>Applicant Name<input class="input-clean" name="name" required></label>
      <div class="two">
        <label>Country<select class="input-clean" name="code"><option>India +91</option><option>+1</option><option>+44</option></select></label>
        <label>Phone Number<input class="input-clean" name="phone" required></label>
      </div>
      <label>Email Address<input class="input-clean" type="email" name="email"></label>
      <label>City of Property<select class="input-clean" name="city"><option>All Cities</option><option>Bangalore</option><option>Mysuru</option><option>Hyderabad</option></select></label>
      <label>Identified the property?
        <div class="choice">
          <label><input type="radio" name="identified" value="Yes" checked> Yes</label>
          <label><input type="radio" name="identified" value="No"> No</label>
        </div>
      </label>
      <label>Occupation Type<select class="input-clean" name="occ"><option>Private</option><option>Government</option><option>Self Employed</option></select></label>
      <label>Loan Amount (₹ lacs)<input class="input-clean" type="number" min="0" step="1" name="amount"></label>
      <div class="row-actions"><button class="btn-primary-light" type="submit">Apply Now</button><span class="status muted"></span></div>
    </form>
  </div>
</div>

<!-- LEGAL ADVICE -->
<div class="gx-modal form-modal" id="legalModal" role="dialog" aria-modal="true" aria-labelledby="legalTitle">
  <div class="gx-backdrop" data-close="1"></div>
  <div class="gx-sheet">
    <div class="gx-head"><h3 id="legalTitle">Request legal advice</h3><button class="gx-close" data-close="1">✕</button></div>
    <form id="legalForm" class="gx-body simple-grid">
      <label>Name<input class="input-clean" name="name" required></label>
      <label>Phone<input class="input-clean" name="phone" required></label>
      <label>Question / Details<textarea class="input-clean" name="msg" rows="3"></textarea></label>
      <div class="row-actions"><button class="btn-primary-light" type="submit">Request Callback</button><span class="status muted"></span></div>
    </form>
  </div>
</div>

<!-- UNIT BOOKING -->
<div class="gx-modal form-modal" id="bookingModal" role="dialog" aria-modal="true" aria-labelledby="bookTitle">
  <div class="gx-backdrop" data-close="1"></div>
  <div class="gx-sheet">
    <div class="gx-head"><h3 id="bookTitle">Book your unit</h3><button class="gx-close" data-close="1">✕</button></div>
    <form id="bookingForm" class="gx-body simple-grid">
      <label>Name<input class="input-clean" name="name" required></label>
      <label>Phone<input class="input-clean" name="phone" required></label>
      <label>Project / Unit<input class="input-clean" name="unit"></label>
      <div class="row-actions"><button class="btn-primary-light" type="submit">Book</button><span class="status muted"></span></div>
    </form>
  </div>
</div>



  </div>
</section>

<!-- CONTACT -->
<section id="contact">
  <div class="container">
    <div class="section-head"><h2>Get in Touch</h2><div class="muted">Send a message to our team</div></div>
    <div style="display:grid;grid-template-columns:1.2fr .8fr;gap:14px">
      <div class="card gbd gtint gshadow" style="padding:18px">
        <form id="contactForm">
          <div style="display:flex;gap:10px;flex-wrap:wrap">
            <div style="flex:1 1 240px"><label>Name</label><input class="input" name="name" required></div>
            <div style="flex:1 1 240px"><label>Email</label><input class="input" name="email" type="email" required></div>
          </div>
          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px">
            <div style="flex:1 1 240px"><label>Phone</label><input class="input" name="phone" required></div>
            <div style="flex:1 1 240px"><label>Address</label><input class="input" name="address" required></div>
          </div>
          <div style="margin-top:10px"><label>Message</label><textarea class="input" name="message" rows="4"></textarea></div>
          <div style="margin-top:12px;display:flex;gap:10px;align-items:center">
            <button class="btn btn-gb" type="submit">Submit</button>
            <span id="formStatus" class="muted"></span>
          </div>
        </form>
      </div>
      <div class="card gbd gtint gshadow" style="padding:18px">
        <h3>Google mapEmbed </h3>
    
        <iframe
  loading="lazy"
  allowfullscreen
  referrerpolicy="no-referrer-when-downgrade"
  src="https://www.google.com/maps?q=4th+Floor%2C+111%2C+27th+Main+Rd%2C+Sector+2%2C+HSR+Layout%2C+Bengaluru%2C+Karnataka+560102&z=16&output=embed"
  title="GM HOMEZ - HSR Layout, Bengaluru"
  style="border:0;border-radius:14px;width:100%;min-height:330px"
></iframe>

      </div>
    </div>
  </div>
</section>

<!-- TEAM -->
<section id="agents">
  <div class="container">
    <div class="section-head"><h2>Meet Our Team</h2><div class="muted">Manage in Admin → Team</div></div>
    <div class="card" id="agentList" style="display:grid;gap:12px;border-radius:18px;padding:18px">
      <?php
        $team = [];
        if (function_exists('read_team')) $team = read_team();
        if (!$team) $team = _team_fallback_read();
        if (!$team) {
          echo '<p class="muted">No team members yet. Add some in <a href="<?= $BASE ?>/admin/team.php">Admin → Team</a>.</p>';
        } else {
          foreach ($team as $t) {
            if (function_exists('renderTeamRow')) renderTeamRow($t);
            else _team_fallback_card($t, $BASE);
          }
        }
      ?>
    </div>
  </div>
</section>

<!-- PROPERTY DETAILS MODAL -->
<div id="npModal" class="gx-modal" role="dialog" aria-modal="true" aria-labelledby="npModalTitle">
  <div class="gx-backdrop" data-close="1"></div>
  <div class="gx-sheet">
    <div class="gx-head">
      <h3 id="npModalTitle" style="margin:0">Property</h3>
      <button class="gx-close" data-close="1" aria-label="Close">✕</button>
    </div>
    <div class="gx-body" style="display:grid;gap:12px;grid-template-columns:320px 1fr">
      <img id="npImg" src="" alt="Property image" style="width:100%;height:220px;object-fit:cover;border-radius:12px;background:#0d1627"/>
      <div class="np-meta">
        <div style="margin:10px 0;border-bottom:1px solid rgba(148,163,184,.15)"></div>
        <div><b style="color:#9bdcff">Price:</b> <span id="npPrice">—</span></div>
        <div><b style="color:#9bdcff">Location:</b> <span id="npLoc">—</span></div>
        <div><b style="color:#9bdcff">Beds:</b> <span id="npBeds">—</span> &nbsp; <b style="color:#9bdcff">Baths:</b> <span id="npBaths">—</span></div>
        <div><b style="color:#9bdcff">Area:</b> <span id="npArea">—</span> sq.ft</div>
        <div style="margin:10px 0;border-bottom:1px solid rgba(148,163,184,.15)"></div>
        <div id="npDesc" class="muted"></div>
      </div>
    </div>
  </div>
</div>

<!-- Video Modal -->
<div class="gx-modal" id="videoModal" role="dialog" aria-modal="true" aria-labelledby="vidTitle">
  <div class="gx-backdrop" data-close="1"></div>
  <div class="gx-sheet" style="width:min(900px,95vw);padding:0;background:#000;color:#fff">
    <div class="gx-head" style="padding:10px 14px;border:0;background:#0b1329;color:#e6f0f6">
      <h3 id="vidTitle" style="margin:0;font-size:16px">Video</h3>
      <button class="gx-close" data-close="1" aria-label="Close">✕</button>
    </div>
    <div class="gx-body" style="padding:0">
      <div style="position:relative;padding-top:56.25%;background:#000">
        <iframe id="gmVideo" src="" allow="autoplay; fullscreen; picture-in-picture"
                allowfullscreen
                style="position:absolute;inset:0;width:100%;height:100%;border:0"></iframe>
      </div>
    </div>
  </div>
</div>

<!-- CALL CONFIRM MODAL -->
<div id="callModal" class="gx-modal" role="dialog" aria-modal="true" aria-labelledby="callTitle">
  <div class="gx-backdrop" data-close="1"></div>
  <div class="gx-sheet">
    <div class="gx-head"><h3 id="callTitle" style="margin:0">Call Agent</h3><button class="gx-close" data-close="1">✕</button></div>
    <div class="gx-body">
      <p class="muted">You're about to call <b id="callNumber">—</b>. Continue?</p>
      <div style="display:flex;gap:10px">
        <a id="callNow" class="btn btn-gb gbd-pill" href="#">Call now</a>
        <button class="btn" data-close="1">Cancel</button>
      </div>
    </div>
  </div>
</div>


<!-- ===== Awards / Certificates (carousel) ===== -->
<section id="awards" class="awards-sec">
  <div class="container">
    <div class="awards-head">
      <h2>Awards & Certificates</h2>
      <div class="muted">Swipe or use arrows</div>
    </div>

<?php
  // use exactly these 4 slides
  $awUrl  = url('uploads/awards');
  $slides = [
    ['finance-growth.jpg', 'Rising returns'],
    ['time-invest.jpg',    'Time & Returns'],
    ['house-keys.jpg',     'Keys & Paperwork'],
    ['living-room.jpg',    'Modern Interiors'],
  ];
?>
<div class="awards-slider">
  <button class="aw-btn prev" type="button" aria-label="Previous">‹</button>
  <div class="aw-view bord-grad">
    <div class="awards-track">
      <?php foreach ($slides as $s): [$f,$alt] = $s; ?>
        <div class="award-slide">
          <div class="award-card">
            <img src="<?= htmlspecialchars($awUrl . '/' . rawurlencode($f)) ?>"
                 alt="<?= htmlspecialchars($alt) ?>">
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <button class="aw-btn next" type="button" aria-label="Next">›</button>
</div>



<!-- ============== Top Links to Search your Home ============== -->
<section class="toplinks-wrap" id="top-links">
  <div class="toplinks">
    <h3>Top Links to Search your Home</h3>

    <div class="grid">
      <!-- Builders -->
    <div class="tgroup bord-grad">
  <h4>Builders</h4>
  <a href="<?= $BASE ?>/builders.php?builder=Prestige%20Group">Prestige Group</a>
  <a href="<?= $BASE ?>/builders.php?builder=Sobha%20Limited">Sobha Limited</a>
  <a href="<?= $BASE ?>/builders.php?builder=Kolte%20Patil%20Developers">Kolte Patil Developers</a>
  <a href="<?= $BASE ?>/builders.php?builder=Godrej%20Properties">Godrej Properties</a>
  <a href="<?= $BASE ?>/builders.php?builder=Brigade%20Group">Brigade Group</a>
</div>


      <!-- Real Estate in India -->
      <div class="tgroup bord-grad">
        <h4>Real Estate in India</h4>
     <a href="<?= $BASE ?>/builders.php?city=Bangalore">Property in Bangalore</a>
       <a href="<?= $BASE ?>/builders.php?city=Mumbai">Property in Mumbai</a>
      <a href="<?= $BASE ?>/builders.php?city=Pune">Property in Pune</a>
        <a href="<?= $BASE ?>/builders.php?city=Gurgaon">Property in Gurgaon</a>
        <a href="<?= $BASE ?>/builders.php?city=Bangalore">Cities in India</a> <!-- (or point wherever you prefer) -->
      </div>

      <!-- Properties in India -->
      <div class="tgroup bord-grad">
        <h4>Properties in India</h4>
        <a href="<?= $BASE ?>/builders.php?cat=apartments">Apartments for Sale</a>
        <a href="<?= $BASE ?>/builders.php?cat=villas">Villas in India</a>
        <a href="<?= $BASE ?>/builders.php?cat=new">New Apartments</a>
        <a href="<?= $BASE ?>/builders.php?cat=upcoming">Upcoming Projects</a>
        <a href="<?= $BASE ?>/builders.php?cat=ready">Ready to Move Homes</a>
      </div>

      <!-- Buy Property -->
      <div class="tgroup bord-grad">
        <h4>Buy Property</h4>
            <a href="<?= $BASE ?>/builders.php?city=Bangalore">Bangalore</a>
    <a href="<?= $BASE ?>/builders.php?city=Mumbai">Mumbai</a>
    <a href="<?= $BASE ?>/builders.php?city=Pune">Pune</a>
    <a href="<?= $BASE ?>/builders.php?city=Gurgaon">Gurgaon</a>
    <a href="<?= $BASE ?>/builders.php?city=Hyderabad">Hyderabad</a>
      </div>

      <!-- About -->
      <div class="tgroup bord-grad">
        <h4>About GM Homez</h4>
        <a href="about.php" class="btn-link">About GM HOMEZ</a>
         <a href="/careers.php">Careers</a>
         <a href="/contact.php">Contact us</a>
         <a href="/privacy-policy.php">Privacy policy</a>
         <a href="/user-agreement.php">User Agreement</a>
      </div>

      <!-- Property Type -->
      <div class="tgroup bord-grad">
        <h4>Property Type</h4>
<a href="<?= url('property-type.php?ptype=apartment') ?>">Apartments</a>
<a href="<?= url('property-type.php?ptype=villa') ?>">Villas</a>
<a href="<?= url('property-type.php?ptype=plot') ?>">Plots</a>
<a href="<?= url('property-type.php?ptype=studio') ?>">Studios</a>
<a href="<?= url('property-type.php?ptype=senior') ?>">Senior Living</a>

      </div>

      <!-- Quick Links -->
      <div class="tgroup bord-grad">
        <h4>Quick Links</h4>
        <a href="<?= url('') ?>">Home</a>
        <a href="home-loan.php" class="btn-link">Home Loan</a>
       <a href="NRIservices.php" class="btn-link">NRI Services</a>
       <a href=""id="assistance" class="btn-link" >How to Buy</a>

        <a href="<?= url('sell') ?>">Sell with us</a>
      </div>

      <!-- Resources / Network -->
      <div class="tgroup bord-grad">
        <h4>Resources</h4>
        <a href="<?= url('blog') ?>">Blog</a>
        <a href="<?= url('guides') ?>">Buyer Guides</a>
        <a href="<?= url('news') ?>">Market News</a>
        <h4 style="margin-top:12px">Network Sites</h4>
        <a href="https://realtor.com" target="_blank" rel="noopener">Realtor.com</a>
        <a href="https://housing.com" target="_blank" rel="noopener">Housing.com</a>
      </div>
    </div>

<?php
// ---------- helpers ----------
if (!function_exists('gm_read_json')) {
  function gm_read_json($f){
    if (!is_file($f)) return [];
    $j = json_decode(@file_get_contents($f), true);
    return is_array($j) ? $j : [];
  }
}

// Because this code is IN index.php (project root):
$ROOT = __DIR__;

// If you need a base URL for links:
$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($BASE==='/' || $BASE==='\\' || $BASE==='.') $BASE = '';

// 1) Preferred: explicit builder list
$builders = gm_read_json($ROOT . '/data/builders.json');

// 2) Fallback: derive unique builders from properties.json
if (!$builders) {
  $props = gm_read_json($ROOT . '/data/properties.json');
  $seen = [];
  foreach ($props as $p) {
    $name = trim($p['builder'] ?? '');
    if ($name !== '' && !isset($seen[strtolower($name)])) {
      $builders[] = ['name' => $name, 'slug' => $name];
      $seen[strtolower($name)] = true;
    }
  }
}

// sort by name
usort($builders, fn($a,$b)=>strcasecmp($a['name']??'', $b['name']??''));
?>
<section class="footer-builders">
  <div class="container">
    <h4 class="footer-builders__title">Builders</h4>
    <?php if ($builders): ?>
      <ul class="builder-tags">
        <?php foreach ($builders as $b): ?>
          <?php
            $name = htmlspecialchars($b['name'] ?? '', ENT_QUOTES, 'UTF-8');
            $slug = urlencode($b['slug'] ?? ($b['name'] ?? ''));
          ?>
          <li><a href="/builders.php?builder=<?=$slug?>"><?=$name?></a></li>
        <?php endforeach; ?>

        <!-- 👇 Add this button (only visible when ?admin=1) -->
        <?php if (isset($_GET['admin'])): ?>
          <li><a class="pill-add" href="/admin/builders.php">+ Add builder</a></li>
        <?php endif; ?>

      </ul>
    <?php else: ?>
      <div class="muted">No builders yet.</div>
    <?php endif; ?>
  </div>
</section>
<div style="margin-top:10px">
  <button><a href="builders.php"class="btn" id="btnAddBuilder">+ Add builder</a> </button>
  
</div>

<!-- Small modal (re-uses your gm-modal styles) -->
<div id="builderModal" class="gm-modal" aria-hidden="true">
  <div class="box">
    <h3 style="margin:0 0 8px">Add builder</h3>
    <form id="builderForm">
      <label>Builder Name
        <input name="name" placeholder="e.g., Sun Group" required>
      </label>
      <label>Slug (optional)
        <input name="slug" placeholder="sun-group">
      </label>
      <div style="display:flex;gap:10px;margin-top:10px">
        <button type="button" class="btn" id="cancelBuilder">Cancel</button>
        <button class="btn grad">Save</button>
      </div>
    </form>
  </div>
</div>




    <!-- Follow row -->
    <div class="follow-row bord-grad" style="margin-top:28px">
      <div class="title">Follow GM Homez</div>

      <!-- WhatsApp Channel -->
      <a class="grad-pill" href="https://whatsapp.com/channel/0029VaFiGEE77qVNh1E8ez1w" target="_blank" rel="noopener">
        <!-- WA icon -->
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2a9.9 9.9 0 0 0-8.47 15.11L2 22l4.99-1.52A9.93 9.93 0 1 0 12.04 2Zm5.75 14.31c-.24.68-1.18 1.25-1.92 1.41-.51.11-1.17.2-3.41-.73-2.86-1.19-4.7-4.09-4.85-4.29-.14-.19-1.16-1.54-1.16-2.94s.72-2.09.97-2.38c.24-.29.63-.43 1.05-.43.13 0 .25 0 .36.01.32.01.49.03.7.54.24.57.82 1.97.89 2.12.07.14.11.31.02.5-.09.19-.13.31-.27.48-.14.17-.29.37-.41.5-.14.14-.29.3-.12.58.17.29.75 1.22 1.62 1.98 1.12.99 2.06 1.3 2.36 1.45.29.14.47.12.65-.07.2-.22.74-.86.94-1.16.2-.29.41-.25.68-.15.27.1 1.71.81 2 .96.29.14.48.22.55.35.07.13.07.75-.17 1.43Z"/></svg>
        WhatsApp Channel
      </a>

      <!-- Instagram -->
      <a class="grad-pill" href="https://www.instagram.com/gofy.in?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank" rel="noopener">
        <!-- Instagram icon -->
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3.5a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11Zm0 2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7ZM18 6.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"/></svg>
        Instagram
      </a>
    </div>
  </div>
</section>


<footer>
  © <span id="year"></span> GM HOMEZ · All rights reserved.
  <div class="socials">
    <a class="gbd" href="https://www.instagram.com/gofy.in?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" target="_blank" rel="noopener">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" stroke="currentColor"/><circle cx="12" cy="12" r="4" stroke="currentColor"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor"/></svg>
      <span>Instagram</span>
    </a>
    <a class="gbd" href="https://whatsapp.com/channel/0029VaFiGEE77qVNh1E8ez1w" target="_blank" rel="noopener">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3a9 9 0 0 0-7.8 13.5L3 21l4.6-1.2A9 9 0 1 0 12 3Z" stroke="currentColor"/><path d="M8.5 9.5c0 4 5 6.5 6.5 6.5.6 0 1.7-.6 2-1.2.2-.6.5-1.5 0-2-.4-.5-1.2-.7-1.7-.6-.5.1-.8.8-1 .9-.1.1-.4.1-.6 0-1.2-.6-2.2-1.6-2.8-2.8-.1-.2-.1-.5 0-.6.1-.2.8-.5.9-1 .1-.5-.1-1.3-.6-1.7-.5-.5-1.4-.2-2 .1-.6.3-1.2 1.4-1.2 2Z" stroke="currentColor"/></svg>
      <span>WhatsApp Channel</span>
    </a>
  </div>
</footer>

<script>
/* ---------- Utilities & Drawer ---------- */
const $=(q,r=document)=>r.querySelector(q); const $$=(q,r=document)=>Array.from(r.querySelectorAll(q));
$('#year').textContent=new Date().getFullYear();

const drawer = $('#drawer'), drawerBackdrop = $('#drawerBackdrop');
$('#openMenu')?.addEventListener('click', ()=>{ drawer.classList.add('open'); drawerBackdrop.classList.add('show'); drawer.setAttribute('aria-hidden','false'); });
$('#closeMenu')?.addEventListener('click', ()=>{ drawer.classList.remove('open'); drawerBackdrop.classList.remove('show'); drawer.setAttribute('aria-hidden','true'); });
drawerBackdrop?.addEventListener('click', ()=>{ drawer.classList.remove('open'); drawerBackdrop.classList.remove('show'); drawer.setAttribute('aria-hidden','true'); });

/* ---------- KPIs count up & hero scroll ---------- */
const runCount=el=>{const t=Number(el.dataset.count||0);const s=performance.now(),d=1200;const step=n=>{const p=Math.min(1,(n-s)/d);el.textContent=Math.floor(p*t).toLocaleString('en-IN');if(p<1)requestAnimationFrame(step)};requestAnimationFrame(step)};
const statObs=new IntersectionObserver(es=>{es.forEach(e=>{if(e.isIntersecting){ $$('.num',e.target).forEach(runCount); statObs.unobserve(e.target);}})},{threshold:.4});
statObs.observe($('#statsBox')||document.body);
$('#heroGo')?.addEventListener('click',()=>$('#properties').scrollIntoView({behavior:'smooth'}));

/* ---------- FAQ accordion ---------- */
$$('.faq-item').forEach(item=>{ const q=item.querySelector('.faq-q'), a=item.querySelector('.faq-a'); if(!q||!a) return; q.addEventListener('click', ()=> item.classList.toggle('open')); });

/* ---------- Modals: show/hide helpers ---------- */
const showM = el => { if(!el) return; el.classList.add('show'); };
const hideM = el => { if(!el) return; el.classList.remove('show'); };
const closeAll = () => ['leadModal','loginModal','signupModal','npModal','callModal'].forEach(id=> hideM(document.getElementById(id)));
document.addEventListener('click', e => {
  if (e.target.matches('[data-close]')) hideM(e.target.closest('.gx-modal'));
  if (e.target.classList.contains('gx-backdrop')) hideM(e.target.closest('.gx-modal'));
}, true);
window.addEventListener('keydown', e => { if (e.key === 'Escape') closeAll(); });

/* ---------- Login / Signup ---------- */
document.body.insertAdjacentHTML('beforeend', `
  <div class="gx-modal" id="loginModal" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
    <div class="gx-backdrop" data-close="1"></div>
    <div class="gx-sheet">
      <div class="gx-head"><h3 id="loginTitle" style="margin:0">Login</h3><button class="gx-close" data-close="1">✕</button></div>
      <form id="loginForm" class="gx-body">
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <div style="flex:1 1 240px"><label>Email</label><input class="input" name="email" type="email" required></div>
          <div style="flex:1 1 240px"><label>Password</label><input class="input" name="password" type="password" minlength="6" required></div>
        </div>
        <div style="margin-top:12px;display:flex;gap:10px;align-items:center">
          <button class="btn btn-gb" type="submit">Login</button>
          <span id="loginStatus" class="muted"></span>
        </div>
      </form>
    </div>
  </div>
  <div class="gx-modal" id="signupModal" role="dialog" aria-modal="true" aria-labelledby="signupTitle">
    <div class="gx-backdrop" data-close="1"></div>
    <div class="gx-sheet">
      <div class="gx-head"><h3 id="signupTitle" style="margin:0">Create an Account</h3><button class="gx-close" data-close="1">✕</button></div>
      <form id="signupForm" class="gx-body">
        <div style="display:flex;gap:10px;flex-wrap:wrap">
          <div style="flex:1 1 240px"><label>Full Name</label><input class="input" name="name" required></div>
          <div style="flex:1 1 240px"><label>Phone</label><input class="input" name="phone" required></div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px">
          <div style="flex:1 1 240px"><label>Email</label><input class="input" name="email" type="email" required></div>
          <div style="flex:1 1 240px"><label>Password</label><input class="input" name="password" type="password" minlength="6" required></div>
        </div>
        <div style="margin-top:12px;display:flex;gap:10px;align-items:center">
          <button class="btn btn-gb" type="submit">Sign Up</button>
          <span id="signupStatus" class="muted"></span>
        </div>
      </form>
    </div>
  </div>
`);
$('#btnLogin')?.addEventListener('click', e => { e.preventDefault(); closeAll(); showM($('#loginModal')); }, true);
$('#btnSignup')?.addEventListener('click', e => { e.preventDefault(); closeAll(); showM($('#signupModal')); }, true);
$('#loginForm')?.addEventListener('submit', e => { e.preventDefault(); const s=$('#loginStatus'); if(s){s.textContent='Logged in (demo)'; s.style.color='#22d3ee';} setTimeout(closeAll, 600); }, true);
$('#signupForm')?.addEventListener('submit', e => { e.preventDefault(); const s=$('#signupStatus'); if(s){s.textContent='Registered (demo)'; s.style.color='#22d3ee';} setTimeout(()=>{ hideM($('#signupModal')); showM($('#loginModal')); }, 600); e.target.reset(); }, true);

/* ---------- Property Details (supports .np-link and .js-view-details) ---------- */
window.GM_PROPS = <?php echo json_encode($props ?? [], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE); ?>;
const GM_BY_ID = Object.fromEntries((window.GM_PROPS||[]).map(p=>[String(p.id||''),p]));
function openProp(p){
  const BASE = "<?= $BASE ?>";
  const img = p.image ? (BASE + '/uploads/' + p.image)
            : (p.images && p.images[0] ? (BASE + '/uploads/' + p.images[0]) : (BASE + '/assets/back.png'));
  const fmt = n => { try { return Number(n||0).toLocaleString('en-IN'); } catch(_) { return n; } };
  $('#npModalTitle').textContent = p.title || 'Property';
  $('#npImg').src   = img;
  $('#npPrice').textContent = p.price ? ('₹ ' + fmt(p.price)) : '—';
  $('#npLoc').textContent   = p.location || '—';
  $('#npBeds').textContent  = (p.beds ?? '—');
  $('#npBaths').textContent = (p.baths ?? '—');
  $('#npArea').textContent  = p.area || '—';
  $('#npDesc').textContent  = p.description || '';
  showM($('#npModal'));
}
document.addEventListener('click', e => {
  const a = e.target.closest('.np-link, .js-view-details'); if (!a) return;
  e.preventDefault(); e.stopPropagation();
  if (a.dataset.prop) {
    try { openProp(JSON.parse(a.dataset.prop)); return; } catch(_){}
  }
  const id = String(a.dataset.id || '');
  const p = GM_BY_ID[id];
  if (p) openProp(p);
}, true);

/* ---------- Call confirm for tel links / .js-call ---------- */
const callModal = $('#callModal');
const callNumberSpan = $('#callNumber');
const callNow = $('#callNow');
let pendingTel = '';
document.addEventListener('click', e => {
  const tel = e.target.closest('.js-call, a[href^="tel:"]'); if(!tel) return;
  const href = tel.getAttribute('href') || '';
  const number = href.startsWith('tel:') ? href.replace('tel:','') : (tel.dataset.phone||'');
  if (!number) return; e.preventDefault();
  pendingTel = 'tel:'+number; callNumberSpan.textContent = number; callNow.setAttribute('href', pendingTel); showM(callModal);
}, true);

/* ---------- Route map popovers (hover/tap) ---------- */
(function(){
  const section = document.getElementById('assistance'); if(!section) return;
  const steps = section.querySelectorAll('.rm-step');
  function closeAllPop(){ steps.forEach(s=>{ const p=s.querySelector('.rm-pop'); if(p) p.style.display='none'; }); }
  function openFor(step){ const pop = step.querySelector('.rm-pop'); if(!pop) return; steps.forEach(s=>{ const p=s.querySelector('.rm-pop'); if(p && p!==pop) p.style.display='none'; }); pop.style.display='block'; }
  steps.forEach(step=>{ step.addEventListener('mouseenter', ()=>openFor(step)); step.addEventListener('mouseleave', closeAllPop); step.addEventListener('click', e=>{ if(e.target.closest('.rm-cta')) return; const pop=step.querySelector('.rm-pop'); const open = pop && pop.style.display==='block'; closeAllPop(); if(!open) openFor(step); }); });
  window.addEventListener('scroll', closeAllPop);
})();

/* open any modal via [data-open="modalId"] */
document.addEventListener('click', e => {
  const opener = e.target.closest('[data-open]');
  if (!opener) return;
  e.preventDefault();
  const modal = document.getElementById(opener.dataset.open);
  if (modal) showM(modal);
}, true);

/* assistance forms submit */
['contactForm2','visitForm','loanForm','legalForm','bookingForm'].forEach(id=>{
  const f = document.getElementById(id);
  if (!f) return;
  f.addEventListener('submit', e => {
    e.preventDefault();
    const s = f.querySelector('.status');
    if (s){ s.textContent = "Submitted! We'll get back to you shortly."; s.style.color = '#16a34a'; }
    setTimeout(()=>{ hideM(f.closest('.gx-modal')); f.reset(); if(s) s.textContent=''; }, 800);
  });
});

</script>
<script>
// Contact form AJAX → /gm-homez/api/contact.php
(() => {
  const form = document.querySelector('#contactMain, form[data-contact="1"]');
  if (!form) return;

  const status = document.createElement('div');
  status.className = 'muted';
  status.style.marginTop = '10px';
  form.appendChild(status);

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    status.textContent = 'Sending...';
    try {
      const fd  = new FormData(form);
      const url = "<?= $BASE ?>/api/contact.php";   // <-- hardcoded path, avoids BASE issues
      const res = await fetch(url, { method: 'POST', body: fd });

      let out;
      try {
        out = await res.json();
      } catch (err) {
        const text = await res.text();
        throw new Error(`HTTP ${res.status}: ${text.slice(0,300)}`);
      }

      status.textContent = out.ok ? 'Thanks! We will contact you shortly.' : (out.message || 'Failed to send.');
      if (out.ok) form.reset();
    } catch (err) {
      status.textContent = (err && err.message) ? err.message : 'Network error. Please try again.';
    }
  });
})();
</script>

<script src="<?= $BASE ?>/assets/js/contact.js"></script>


<script>
(() => {
  const sec   = document.getElementById('awards');
  if (!sec) return;
  const track = sec.querySelector('.awards-track');
  const prev  = sec.querySelector('.aw-btn.prev');
  const next  = sec.querySelector('.aw-btn.next');
  const slides= Array.from(track.children);
  if (!slides.length) return;

  let i = 0;
  const go = (dir) => { i = (i + dir + slides.length) % slides.length;
    track.style.transform = `translateX(${-i * 100}%)`; };
  prev.addEventListener('click', () => go(-1));
  next.addEventListener('click', () => go( 1));

  // swipe on mobile
  let sx = null;
  track.addEventListener('pointerdown', e => { sx = e.clientX; track.setPointerCapture(e.pointerId); });
  track.addEventListener('pointerup',   e => {
    if (sx == null) return;
    const dx = e.clientX - sx;
    if (dx < -40) go(1); else if (dx > 40) go(-1);
    sx = null;
  });

  // optional autoplay
  setInterval(() => go(1), 5000);
  go(0);
})();
</script>

<script>
  (function(){
    const openBtn = document.getElementById('gm-open-more');
    const modal   = document.getElementById('gm-more-modal');
    if (!modal) return;

    const panel = modal.querySelector('.gm-modal__panel');
    const closeEls = modal.querySelectorAll('[data-close]');

    const open = ()=>{
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden','false');
      document.body.style.overflow='hidden';
      panel.focus();
    };
    const close = ()=>{
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden','true');
      document.body.style.overflow='';
    };

    openBtn && openBtn.addEventListener('click', open);
    closeEls.forEach(el => el.addEventListener('click', close));
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('is-open')) close(); });
  })();
</script>

<script>
(() => {
  const API = "<?= $BASE ?>/api/contact.php"; // relative to /gm-homez/

  const postToContact = async (form, buildMessage) => {
    const status = form.querySelector('.status') || (() => {
      const s = document.createElement('span');
      s.className = 'status muted';
      (form.querySelector('.row-actions') || form).appendChild(s);
      return s;
    })();

    status.textContent = 'Sending…';

    try {
      const fd = new FormData(form);

      // phone normalization
      const phoneEl = form.querySelector('[name="phone"]');
      const codeEl  = form.querySelector('[name="code"]');
      if (phoneEl) {
        const rawPhone = (phoneEl.value || '').replace(/\D+/g, '');
        const rawCode  = codeEl ? (codeEl.value || '').replace(/\D+/g, '') : '';
        fd.set('phone', (rawCode ? rawCode : '') + rawPhone);
      }

      // message builder per form
      const msg = buildMessage?.(form) || '';
      if (msg && !fd.get('message')) fd.set('message', msg);

      const res = await fetch(API, { method: 'POST', body: fd });

      // ✅ read body ONCE
      const raw = await res.text();
      let out;
      try { out = JSON.parse(raw); }
      catch { out = { ok: res.ok, message: raw?.slice(0,200) || 'Bad response' }; }

      status.textContent = out.ok ? 'Thanks! We’ll contact you shortly.' : (out.message || 'Failed to send.');
      if (out.ok) form.reset();
    } catch (err) {
      status.textContent = err?.message || 'Network error. Please try again.';
    }
  };

  // forms wiring (same as before)
  const contactForm2 = document.querySelector('#contactForm2');
  contactForm2?.addEventListener('submit', (e) => {
    e.preventDefault();
    postToContact(contactForm2, (f) => {
      const userMsg = f.querySelector('[name="message"]')?.value?.trim() || '';
      return `Route Map: Get in touch\n${userMsg}`;
    });
  });

  const visitForm = document.querySelector('#visitForm');
  visitForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    postToContact(visitForm, (f) => {
      const d = f.querySelector('[name="date"]')?.value || '';
      const t = f.querySelector('[name="time"]')?.value || '';
      const c = f.querySelector('[name="city"]')?.value || '';
      return `Route Map: Site Visit\nPreferred date: ${d}\nPreferred time: ${t}\nCity: ${c}`;
    });
  });

  const loanForm = document.querySelector('#loanForm');
  loanForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    postToContact(loanForm, (f) => {
      const city = f.querySelector('[name="city"]')?.value || '';
      const ident= f.querySelector('[name="identified"]:checked')?.value || '';
      const occ  = f.querySelector('[name="occupation"]')?.value || '';
      const amt  = f.querySelector('[name="amount"]')?.value || '';
      return `Route Map: Home Loan\nCity of property: ${city}\nIdentified property: ${ident}\nOccupation: ${occ}\nLoan Amount (₹ lacs): ${amt}`;
    });
  });

  const legalForm = document.querySelector('#legalForm');
  legalForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    postToContact(legalForm, (f) => {
      const q = f.querySelector('[name="msg"]')?.value || '';
      return `Route Map: Legal Advice\n${q}`;
    });
  });

  const bookingForm = document.querySelector('#bookingForm');
  bookingForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    postToContact(bookingForm, (f) => {
      const unit = f.querySelector('[name="unit"]')?.value || '';
      return `Route Map: Unit Booking\nProject/Unit: ${unit}`;
    });
  });

  const leadForm = document.querySelector('#leadForm');
  leadForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    postToContact(leadForm, () => `Route Map: Quick callback request`);
  });
})();
</script>


<!-- gm-homez/assets/search.js -->
<script>
(() => {
  const citySel   = document.getElementById('heroCity');
  const qInput    = document.getElementById('heroQuery');
  const goBtn     = document.getElementById('heroGo');

  const locInput  = document.getElementById('heroLocality');
  const bhkSel    = document.getElementById('heroBhk');
  const minInput  = document.getElementById('heroPriceMin');
  const maxInput  = document.getElementById('heroPriceMax');

  if (!qInput || !goBtn) return;

  // ---------- UI helpers ----------
  function showMsg(html) {
    let box = document.getElementById('heroMsg');
    if (!box) {
      box = document.createElement('div');
      box.id = 'heroMsg';
      box.style.marginTop = '10px';
      box.style.padding = '10px 12px';
      box.style.borderRadius = '10px';
      box.style.fontSize = '14px';
      box.style.lineHeight = '1.3';
      box.style.border = '1px solid rgba(148,163,184,.25)';
      box.style.background = 'rgba(15,42,55,.7)';
      box.style.color = '#e6f0f6';
      const container = (goBtn.closest('.hero-search') || qInput.parentElement);
      container && container.insertAdjacentElement('afterend', box);
    }
    box.innerHTML = html;
    box.style.display = html ? 'block' : 'none';
  }
  function clearMsg(){ showMsg(''); }

  // ---------- URL helpers (direct linking) ----------
  function computeBase() {
    const path = location.pathname;
    return path.endsWith('/') ? path.slice(0, -1) : path.replace(/\/[^\/]*$/, '');
  }
  function readParams() {
    const p = new URLSearchParams(location.search);
    return {
      q: p.get('q') || '',
      city: p.get('city') || '',
      locality: p.get('locality') || '',
      bhk: p.get('bhk') || '',
      min: p.get('min') || '',
      max: p.get('max') || ''
    };
  }
  function writeParams(params) {
    const p = new URLSearchParams();
    for (const [k,v] of Object.entries(params)) {
      if (v !== '' && v != null) p.set(k, v);
    }
    const url = location.pathname + (p.toString() ? '?' + p.toString() : '');
    history.replaceState(null, '', url);
  }

  // ---------- fuzzy helpers ----------
  const norm = s => String(s||'').toLowerCase().normalize('NFKD')
    .replace(/[^\w\s]/g,' ')
    .replace(/\s+/g,' ')
    .trim();

  function levenshtein(a, b) {
    a = norm(a); b = norm(b);
    const m = a.length, n = b.length;
    if (!m) return n; if (!n) return m;
    const dp = new Array(n+1);
    for (let j=0; j<=n; j++) dp[j] = j;
    for (let i=1; i<=m; i++){
      let prev = dp[0]; dp[0] = i;
      for (let j=1; j<=n; j++){
        const tmp = dp[j];
        dp[j] = Math.min(
          dp[j] + 1,
          dp[j-1] + 1,
          prev + (a[i-1] === b[j-1] ? 0 : 1)
        );
        prev = tmp;
      }
    }
    return dp[n];
  }
  function ratio(a,b){
    a=norm(a); b=norm(b);
    if (!a || !b) return 0;
    const d = levenshtein(a,b);
    return 1 - d / Math.max(a.length, b.length);
  }
  function tokenSetRatio(a,b){
    const A = new Set(norm(a).split(' ').filter(Boolean));
    const B = new Set(norm(b).split(' ').filter(Boolean));
    if (!A.size || !B.size) return 0;
    let inter = 0;
    for (const t of A) if (B.has(t)) inter++;
    return inter / Math.max(A.size, B.size);
  }
  function extractBhk(s){
    const m = String(s||'').match(/(\d+)\s*bhk/i);
    return m ? parseInt(m[1],10) : null;
  }
  function extractLocality(s){
    const m = String(s||'').match(/\b(?:in|at)\s+([a-z][a-z\s\-]{2,})$/i);
    return m ? m[1].trim() : null;
  }

  // final fuzzy score for property against query
  function scoreProperty(q, prop){
    const title = prop.title || '';
    const city  = prop.city || '';
    const loc   = prop.location || '';
    const bhk   = prop.bedrooms != null ? String(prop.bedrooms) + ' bhk' : '';

    const hay   = `${title} ${loc} ${city} ${bhk}`;

    const s1 = ratio(q, hay);          // edit distance similarity
    const s2 = tokenSetRatio(q, hay);  // token overlap
    const s3 = hay.includes(norm(q)) ? 1 : 0; // hard contains after norm

    // weighted mix
    return 0.55*s1 + 0.35*s2 + 0.10*s3;
  }

  // ---------- search main ----------
  async function runSearch(fromUserAction = true) {
    const q      = (qInput.value || '').trim();
    const city   = citySel ? (citySel.value || '').trim() : '';
    const loc    = (locInput?.value || '').trim();
    const bhk    = (bhkSel?.value || '').trim();
    const min    = (minInput?.value || '').trim();
    const max    = (maxInput?.value || '').trim();

    // also parse intents from q (e.g., "2 bhk in koramangala")
    const qBhk = extractBhk(q);
    const qLoc = extractLocality(q);

    // sync URL (direct link)
    writeParams({
      q, city,
      locality: loc || qLoc || '',
      bhk: bhk || (qBhk ?? ''),
      min, max
    });

    if (!q && !bhk && !loc && !min && !max && !city) {
      showMsg('Type a query or set filters to search.');
      return;
    }

    const BASE = computeBase();
    const params = new URLSearchParams();
    if (q) params.set('q', q);
    if (city) params.set('city', city);
    if (loc || qLoc) params.set('locality', (loc || qLoc));
    if (bhk || qBhk!=null) params.set('bhk', (bhk || String(qBhk)));
    if (min) params.set('min', min);
    if (max) params.set('max', max);

    const url = `${BASE}/api/search.php?${params.toString()}`;

    showMsg('Searching…');

    try {
      const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
      const raw = await res.text();
      if (!res.ok) { showMsg(`Error ${res.status}: ${raw.slice(0,120)}…`); return; }

      let out; try { out = JSON.parse(raw); } catch { showMsg(`Server did not return JSON: ${raw.slice(0,120)}…`); return; }
      if (!out || !out.ok || !Array.isArray(out.items)) { showMsg(out?.error || 'Unexpected response.'); return; }

      if (out.items.length === 0) { showMsg('No results. Try adjusting filters.'); return; }

      // Fuzzy rank
      const withScores = out.items.map(it => ({...it, _score: scoreProperty(q, it)}))
                                  .sort((a,b)=>b._score - a._score);
      const top = withScores[0], second = withScores[1];
      const topScore = top?._score ?? 0, secondScore = second?._score ?? 0;

      // strong confidence → go directly
      if (top && (topScore >= 0.82 && (topScore - secondScore >= 0.12 || !second))) {
        location.href = `${BASE}/property.php?id=${encodeURIComponent(top.id)}`;
        return;
      }

      // else: show top suggestions (clickable deep links)
      const list = withScores.slice(0,5).map(it => {
        const sub = [it.location, it.city].filter(Boolean).join(', ');
        return `<li style="margin:6px 0">
          <a href="${BASE}/property.php?id=${encodeURIComponent(it.id)}" style="color:#8df">${escapeHtml(it.title || 'Property')}</a>
          ${sub ? `<span class="muted"> — ${escapeHtml(sub)}</span>` : ''}
        </li>`;
      }).join('');
      showMsg(`<div>We found multiple matches. Try one:</div><ul style="margin:8px 0 0 18px;padding:0">${list}</ul>`);
    } catch(e) {
      showMsg(`Network error: ${e.message}`);
    }
  }

  function escapeHtml(s){ return String(s).replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }

  // events
  goBtn.addEventListener('click', (e) => { e.preventDefault(); runSearch(true); });
  qInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); runSearch(true); }});

  // On load: hydrate from URL & optionally auto-run
  const P = readParams();
  if (P.q) qInput.value = P.q;
  if (citySel && P.city) citySel.value = P.city;
  if (locInput && P.locality) locInput.value = P.locality;
  if (bhkSel && P.bhk) bhkSel.value = P.bhk;
  if (minInput && P.min) minInput.value = P.min;
  if (maxInput && P.max) maxInput.value = P.max;

  if (P.q || P.city || P.locality || P.bhk || P.min || P.max) {
    // auto-run for shared links
    runSearch(false);
  }
})();
</script>



<script src="assets/search.js"></script>
<script src="assets/chatbot.js"></script>





<!-- gm: drawer link close + smooth scroll -->
<script>
(function(){
  const drawer = document.getElementById('drawer');
  const backdrop = document.getElementById('drawerBackdrop');
  function closeDrawer(){
    if (!drawer) return;
    drawer.classList.remove('open');
    if (backdrop) backdrop.classList.remove('show');
    drawer.setAttribute('aria-hidden','true');
  }
  document.querySelectorAll('#drawer a').forEach(a => {
    a.addEventListener('click', (e) => {
      const href = a.getAttribute('href') || '';
      if (href.startsWith('#')) {
        e.preventDefault();
        const el = document.querySelector(href);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
      closeDrawer();
    });
  });
})();
</script>

</body></html>

<!-- HOME CARD SLIDER: 3s autoplay -->
<script>
(function(){ 
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.card-media').forEach(function(box){
      var data = box.getAttribute('data-images') || '[]';
      var imgs = [];
      try { imgs = JSON.parse(data) || []; } catch(e){ imgs = []; }
      // Need at least 2 to rotate
      if (!Array.isArray(imgs) || imgs.length < 2) return;

      var imgEl = box.querySelector('.card-media-img');
      var prev  = box.querySelector('.card-media-nav.prev');
      var next  = box.querySelector('.card-media-nav.next');
      var idx   = 0, t = null;

      function norm(u){ return (u && u.charAt(0) === '/') ? u : '/' + u; }
      function show(i){
        idx = (i + imgs.length) % imgs.length;
        var nextUrl = norm(imgs[idx]);
        var pre = new Image();
        pre.onload = function(){ 
          if (!imgEl) return;
          imgEl.style.opacity = .35; 
          requestAnimationFrame(function(){
            imgEl.src = nextUrl; 
            imgEl.style.opacity = 1; 
          });
        };
        pre.src = nextUrl;
      }
      function reset(){ clearInterval(t); t = setInterval(function(){ show(idx+1); }, 3000); }

      prev && prev.addEventListener('click', function(e){ e.preventDefault(); show(idx-1); reset(); });
      next && next.addEventListener('click', function(e){ e.preventDefault(); show(idx+1); reset(); });

      box.addEventListener('mouseenter', function(){ clearInterval(t); });
      box.addEventListener('mouseleave', function(){ reset(); });

      show(0);
      t = setInterval(function(){ show(idx+1); }, 3000);
    });
  });
})();
</script>
<!-- HOME CARD SLIDER: 3s autoplay -->
<script>
(function(){ 
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.card-media').forEach(function(box){
      var data = box.getAttribute('data-images') || '[]';
      var imgs = []; try { imgs = JSON.parse(data) || []; } catch(e){ imgs = []; }
      if (!Array.isArray(imgs) || imgs.length < 2) return;

      var imgEl = box.querySelector('.card-media-img');
      var prev  = box.querySelector('.card-media-nav.prev');
      var next  = box.querySelector('.card-media-nav.next');
      var idx   = 0, t = null;

      function norm(u){ return (u && u.charAt(0) === '/') ? u : '/' + u; }
      function show(i){
        idx = (i + imgs.length) % imgs.length;
        var nextUrl = norm(imgs[idx]);
        var pre = new Image();
        pre.onload = function(){
          imgEl.style.opacity = .35;
          requestAnimationFrame(function(){
            imgEl.src = nextUrl;
            imgEl.style.opacity = 1;
          });
        };
        pre.src = nextUrl;
      }
      function reset(){ clearInterval(t); t = setInterval(function(){ show(idx+1); }, 3000); }

      prev && prev.addEventListener('click', function(e){ e.preventDefault(); show(idx-1); reset(); });
      next && next.addEventListener('click', function(e){ e.preventDefault(); show(idx+1); reset(); });

      box.addEventListener('mouseenter', function(){ clearInterval(t); });
      box.addEventListener('mouseleave', function(){ reset(); });

      show(0);
      t = setInterval(function(){ show(idx+1); }, 3000);
    });
  });
})();
</script>

<script>
(function(){
  const modal = document.getElementById('videoModal');
  const iframe = document.getElementById('gmVideo');
  if (!modal || !iframe) return;

  function show(url){
    // autoplay query if YouTube/Vimeo
    let src = url;
    if (/youtube\.com\/embed/.test(src)) {
      src += (src.includes('?') ? '&' : '?') + 'autoplay=1&rel=0';
    } else if (/player\.vimeo\.com\/video/.test(src)) {
      src += (src.includes('?') ? '&' : '?') + 'autoplay=1';
    }
    iframe.src = src;
    modal.classList.add('show');
  }
  function hide(){
    modal.classList.remove('show');
    iframe.src = '';
  }

  document.addEventListener('click', function(e){
    const btn = e.target.closest('.card-media-play');
    if (btn) {
      e.preventDefault();
      const src = btn.getAttribute('data-src');
      if (src) show(src);
    }
    if (e.target.matches('#videoModal .gx-backdrop,[data-close]')) hide();
  });

  window.addEventListener('keydown', (e)=>{ if (e.key === 'Escape' && modal.classList.contains('show')) hide(); });
})();
script>
// ------------- inline "add builder" -------------
const addBtn = document.getElementById('btnAddBuilder');
const modal  = document.getElementById('builderModal');
const form   = document.getElementById('builderForm');
const cancel = document.getElementById('cancelBuilder');

function openBuilderModal(){ modal.classList.add('show'); document.body.style.overflow='hidden'; }
function closeBuilderModal(){ modal.classList.remove('show'); document.body.style.overflow=''; }

addBtn?.addEventListener('click', openBuilderModal);
cancel?.addEventListener('click', closeBuilderModal);
modal?.addEventListener('click', (e)=>{ if(e.target===modal) closeBuilderModal(); });

form?.addEventListener('submit', function(e){
  e.preventDefault();
  const fd = new FormData(form);
  fetch('<?= htmlspecialchars($BASE ?? '', ENT_QUOTES) ?>/api/add_builder.php', { method:'POST', body: fd })
    .then(r => r.json())
    .then(j => {
      if (!j.ok) { alert(j.error || 'Failed'); return; }
      // Append to the list immediately
      const ul = document.querySelector('.builder-tags');
      if (ul) {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = '<?= htmlspecialchars($BASE ?? '', ENT_QUOTES) ?>/builders.php?builder=' + encodeURIComponent(j.item.slug);
        a.textContent = j.item.name;
        li.appendChild(a);
        ul.appendChild(li);
      }
      closeBuilderModal();
      form.reset();
    })
    .catch(()=> alert('Network error'));
});
</script>
</script>

<script>
(function () {
  function closeDrawer() {
    const d = document.getElementById('drawer');
    const b = document.getElementById('drawerBackdrop');
    if (!d) return;
    d.setAttribute('aria-hidden', 'true');
    d.classList.remove('open');
    b && b.classList.remove('show');
  }
  // Works for any link inside the drawer (event delegation)
  document.addEventListener('click', function (e) {
    const link = e.target.closest('#drawer .submenu-links a, #drawer a');
    if (link) closeDrawer();
  }, true);
})();
</script>


<?php
$FOOTER = __DIR__ . '/partials/footer.php';   // page.php and /partials are at the same level
if (is_file($FOOTER)) include $FOOTER;
?>
<script>
document.getElementById('add-builder-btn')?.addEventListener('click', async (e) => {
  e.preventDefault();

  const name = prompt("New builder name:");
  if (!name) return;

  try {
    const res = await fetch('/api/save_builder.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ name })
    });

    const j = await res.json().catch(() => ({}));
    if (!res.ok || !j.ok) {
      alert(j.error || 'Failed to add builder');
      return;
    }

    // Go straight to the builder page
    location.href = '/builders.php?builder=' + encodeURIComponent(j.slug);
  } catch (err) {
    console.error(err);
    alert('Network error while adding builder');
  }
});
</script>

</body></html>