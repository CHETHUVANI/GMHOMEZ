<?php
// nri-services.php — GM HOMEZ : NRI Services
$TITLE = 'NRI Services';
require_once __DIR__.'/config.php';
require_once __DIR__.'/lib/render.php';
render_header($TITLE);
$base = function_exists('render_base_url') ? render_base_url() : '';
?>
<style>
  :root{
    --ink:#0f172a; --muted:#64748b; --line:#e5e7eb;
    --brand:#f59e0b; --blue:#1d2c6b; --grad1:#4f46e5; --grad2:#9333ea; --grad3:#0ea5e9; --grad4:#22c55e;
  }
  /* page scaffold */
  .nri-hero{
    margin-top:14px; border-radius:18px; padding:38px 26px;
    color:#fff;
    background:
      radial-gradient(1200px 500px at 85% -50%, rgba(255,255,255,.08), transparent 60%),
      linear-gradient(135deg, #1e293b 0%, #0b1226 35%, #0f172a 65%, #0b1226 100%);
    position:relative; overflow:hidden;
  }
  .nri-hero .blob{
    position:absolute; right:-80px; top:-80px; width:360px; height:360px; border-radius:50%;
    background:radial-gradient(closest-side, rgba(245,158,11,.55), rgba(245,158,11,.08) 65%, transparent 70%);
    filter: blur(6px);
  }
  .nri-hero h1{font-size:44px; line-height:1.05; margin:0 0 10px}
  .nri-hero p{opacity:.9; max-width:70ch}
  .btn{display:inline-block; padding:.7rem 1.1rem; border-radius:12px; border:1px solid transparent; text-decoration:none; font-weight:600}
  .btn.brand{background:var(--brand); color:#111}
  .btn.light{background:rgba(255,255,255,.1); border-color:rgba(255,255,255,.16); color:#fff}

  /* benefit cards */
  .grid{display:grid; gap:16px}
  .grid-4{grid-template-columns:repeat(4,1fr)}
  @media (max-width:1100px){ .grid-4{grid-template-columns:repeat(2,1fr)} }
  @media (max-width:640px){ .grid-4{grid-template-columns:1fr} }
  .card{
    border:1px solid var(--line); background:#fff; border-radius:16px; padding:16px;
    box-shadow:0 .5px 0 rgba(15,23,42,.08);
  }
  .gcard{
    border-radius:16px; padding:18px; color:#fff; position:relative; overflow:hidden;
  }
  .g1{ background:linear-gradient(135deg, var(--grad1), var(--grad3)); }
  .g2{ background:linear-gradient(135deg, #ef4444, #f59e0b); }
  .g3{ background:linear-gradient(135deg, var(--grad3), #14b8a6); }
  .g4{ background:linear-gradient(135deg, #8b5cf6, #ec4899); }
  .gcard .cap{font-size:18px; font-weight:700; margin-top:8px}
  .gcard .mut{opacity:.9}

  /* steps (kept 1,2,6; we’ll drive the rest via FAQ) */
  .step{border:1px dashed #dbe2f0; border-radius:14px; padding:16px; background:#fbfcff}
  .step .ttl{font-weight:800; font-size:20px; margin-bottom:6px}
  .chip{display:inline-block; font-size:12px; padding:.25rem .5rem; border-radius:999px; background:#eef2ff; color:#3730a3; margin-right:8px}

  /* band */
  .band{
    margin-top:20px; border-radius:18px; padding:20px;
    background:linear-gradient(100deg, #0b1024, #16204b 55%, #0b1024);
    color:#ecf0ff; border:1px solid rgba(255,255,255,.08);
  }
  .flow{display:flex; gap:18px; align-items:center; justify-content:space-between; flex-wrap:wrap}
  .node{
    flex:1 1 160px; min-width:160px; text-align:center; padding:14px 10px; border-radius:18px;
    background:radial-gradient(120px 90px at 50% 0%, rgba(255,255,255,.08), transparent 55%),
               linear-gradient(135deg, rgba(255,255,255,.12), rgba(255,255,255,.04));
    border:1px solid rgba(255,255,255,.1);
  }
  .node i{font-style:normal; font-size:28px; display:block}
  .node b{display:block; margin-top:6px}

  /* consult form */
  .consult{
    margin-top:22px; border-radius:18px; padding:20px;
    background: radial-gradient(800px 300px at -20% -40%, rgba(99,102,241,.22), transparent 60%),
                linear-gradient(145deg, #0b1226 0%, #111c45 100%);
    color:#fff; border:1px solid rgba(255,255,255,.08);
  }
  .fgrid{display:grid; grid-template-columns:1fr 1fr; gap:12px}
  .fgrid input, .fgrid textarea{
    width:100%; padding:12px 14px; border-radius:12px; border:1px solid rgba(255,255,255,.18);
    background:rgba(255,255,255,.06); color:#fff; outline:none;
  }
  .fgrid textarea{min-height:110px; resize:vertical}
  @media (max-width:900px){ .fgrid{grid-template-columns:1fr} }

  /* FAQ accordion */
  .faq{margin-top:24px}
  .faq h2{margin-bottom:8px}
  .acc{border:1px solid var(--line); border-radius:14px; overflow:hidden; background:#fff}
  .acc-item + .acc-item{border-top:1px solid var(--line)}
  .acc-btn{
    width:100%; text-align:left; padding:16px; background:#fff; border:0; cursor:pointer;
    display:flex; align-items:flex-start; gap:10px; font-weight:700
  }
  .acc-btn .q{flex:1}
  .acc-btn .icon{width:28px; height:28px; display:grid; place-items:center; border-radius:8px; background:#eef2ff; color:#3730a3; font-weight:800}
  .acc-panel{display:none; padding:0 16px 16px; color:var(--muted)}
  .acc-item.open .acc-panel{display:block}
  .acc-item.open .acc-btn .icon{background:#3730a3;color:#fff}

  /* tiny helpers */
  .muted{color:var(--muted)}
  .center{text-align:center}
  .mb8{margin-bottom:8px}
  .mb14{margin-bottom:14px}
  .mb20{margin-bottom:20px}
</style>

<section class="nri-hero">
  <div class="blob" aria-hidden="true"></div>
  <h1>NRI Services – <span style="opacity:.9">End-to-end help to invest back home</span></h1>
  <p>From discovery to documentation, banking, compliance and possession—our experts guide you at
     every step with transparent, unbiased advice. Zero brokerage to customers.</p>
  <div style="margin-top:12px">
    <a class="btn brand" href="#consult">Book Free Consultation</a>
    <a class="btn light" href="#faq">Read NRI FAQs</a>
  </div>
</section>

<!-- Benefits -->
<h2 class="mb8" style="margin-top:18px">Why GM HOMEZ for NRIs?</h2>
<p class="muted mb14">Specialised support for Non-Resident Indians & PIOs across time-zones.</p>
<div class="grid grid-4">
  <div class="gcard g1">
    <div>🌏</div>
    <div class="cap">Virtual, end-to-end advisory</div>
    <div class="mut">Video consults, property shortlists, live site updates, paperwork & possession.</div>
  </div>
  <div class="gcard g2">
    <div>✅</div>
    <div class="cap">Title, RERA & legal checks</div>
    <div class="mut">Project due-diligence, document vetting and clear escalation paths.</div>
  </div>
  <div class="gcard g3">
    <div>🏦</div>
    <div class="cap">NRI home-loan assistance</div>
    <div class="mut">Tie-ups with major Indian banks; application to disbursal handled.</div>
  </div>
  <div class="gcard g4">
    <div>💬</div>
    <div class="cap">Zero brokerage</div>
    <div class="mut">We do not charge customers any service fee or commission.</div>
  </div>
</div>

<!-- Steps (1,2,6 kept; rest moved to FAQ) -->
<h2 class="mb8" style="margin-top:22px">How NRIs & PIOs can start investing</h2>
<div class="grid" style="grid-template-columns:1fr 1fr 1fr; gap:14px">
  <div class="step">
    <div class="ttl">1) Know what you can invest in</div>
    <div class="muted">
      <span class="chip">Allowed</span> Apartments, villas, plots, shops.<br>
      <span class="chip" style="background:#fee2e2;color:#991b1b">Not Allowed</span>
      Agricultural land / plantation / farmhouse unless specially permitted by RBI.
    </div>
  </div>
  <div class="step">
    <div class="ttl">2) Set up your NRI bank accounts</div>
    <div class="muted">Open an NRE (Non-Resident External) or NRO (Non-Resident Ordinary) account.
      All payments, EMIs & resale proceeds must flow through these in INR.</div>
  </div>
  <div class="step">
    <div class="ttl">3) Understand tax & repatriation</div>
    <div class="muted">Rental income is taxable in India. Long-term capital gains ~20%. Repatriation
      up to USD 1M/yr via NRE/NRO after tax compliance.</div>
  </div>
</div>

<!-- Process band -->
<section class="band">
  <div class="flow">
    <div class="node"><i>🏙️</i><b>Search & Shortlist</b><span class="muted" style="display:block">Curated options</span></div>
    <div class="node"><i>🚗</i><b>Site Visit</b><span class="muted" style="display:block">Virtual / in-person</span></div>
    <div class="node"><i>💰</i><b>Loan Assistance</b><span class="muted" style="display:block">Banks & paperwork</span></div>
    <div class="node"><i>📄</i><b>Legal & RERA</b><span class="muted" style="display:block">Diligence</span></div>
    <div class="node"><i>🔑</i><b>Unit Booking</b><span class="muted" style="display:block">Handover</span></div>
  </div>
</section>

<!-- Consultation -->
<section id="consult" class="consult">
  <h2 class="mb8">Book a Free Online Consultation</h2>
  <p class="muted mb14">Tell us a few details and our NRI desk will get back within 24 hours.</p>
  <form onsubmit="event.preventDefault(); alert('Thanks! Our NRI team will contact you shortly.');">
    <div class="fgrid">
      <input type="text" name="name" placeholder="Your Full Name" required>
      <input type="email" name="email" placeholder="Your Email Address" required>
      <input type="tel" name="phone" placeholder="Your Phone Number (with country code)" required>
      <input type="text" name="city" placeholder="Preferred City / Budget (optional)">
      <textarea name="notes" placeholder="Your property preferences / questions"></textarea>
      <div style="display:grid; place-items:start">
        <button class="btn brand" type="submit">Book Expert Consultation</button>
      </div>
    </div>
  </form>
</section>

<!-- FAQs (your custom list) -->
<section id="faq" class="faq">
  <h2>FAQs for NRIs</h2>
  <p class="muted mb14">Answers to the most common questions our NRI customers ask.</p>

  <div class="acc">

    <div class="acc-item">
      <button class="acc-btn"><span class="icon">?</span><span class="q">How do I verify if a property is RERA-approved and check its project status?</span></button>
      <div class="acc-panel">
        Visit the official <a href="https://rera.karnataka.gov.in" target="_blank" rel="noopener">Karnataka RERA</a>
        website and search by <b>project name</b>, <b>builder/developer</b> or the <b>RERA registration number</b>.
        The portal shows registration, approvals and project progress updates.
      </div>
    </div>

    <div class="acc-item">
      <button class="acc-btn"><span class="icon">?</span><span class="q">What documents should I check before buying a property?</span></button>
      <div class="acc-panel">
        Ensure the property has: <b>Sale Deed</b>, <b>Khata Certificate</b>, <b>Occupancy Certificate (OC)</b>,
        <b>Encumbrance Certificate (EC)</b>, <b>Approved Building Plan</b>, and applicable
        <b>No-Objection Certificates (NOCs)</b> from relevant authorities.
      </div>
    </div>

    <div class="acc-item">
      <button class="acc-btn"><span class="icon">?</span><span class="q">Can I buy property near riverbeds or lakes in Bangalore?</span></button>
      <div class="acc-panel">
        No. As per NGT and Karnataka High Court guidelines, construction is restricted within buffer zones:
        about <b>75m from rivers</b>, <b>50m from lakes</b>, and <b>30m from primary stormwater drains</b>.
        Buying in these zones is risky and may face demolition or legal disputes.
      </div>
    </div>

    <div class="acc-item">
      <button class="acc-btn"><span class="icon">?</span><span class="q">What happens if a builder delays possession beyond the promised time?</span></button>
      <div class="acc-panel">
        Under RERA, builders are liable to pay <b>interest/compensation for delay</b>. You may file a complaint
        with <a href="https://rera.karnataka.gov.in" target="_blank" rel="noopener">RERA Karnataka</a> for redressal.
      </div>
    </div>

    <div class="acc-item">
      <button class="acc-btn"><span class="icon">?</span><span class="q">Is GST applicable on property purchases in Bangalore?</span></button>
      <div class="acc-panel">
        <ul>
          <li><b>Under-construction</b>: 5% GST (1% for affordable housing) – without ITC.</li>
          <li><b>Ready-to-move with OC</b>: GST <b>not</b> applicable.</li>
        </ul>
      </div>
    </div>

    <div class="acc-item">
      <button class="acc-btn"><span class="icon">?</span><span class="q">How can I check, protect, or convert my Khata into an e-Khata?</span></button>
      <div class="acc-panel">
        Verify your e-Khata on the <b>BBMP Sakala / e-Swathu</b> portals or at your BBMP office. e-Khata is safer
        (digitised & linked to tax accounts). To convert manual Khata, submit title deed, latest tax receipt,
        old Khata certificate/extract and OC (if applicable) at BBMP. Ensure e-Khata details match your documents.
      </div>
    </div>

    <div class="acc-item">
      <button class="acc-btn"><span class="icon">?</span><span class="q">What is the expected return on investment (ROI)?</span></button>
      <div class="acc-panel">
        Investors usually assess: <b>area appreciation potential</b>, <b>rental yields</b>, and
        <b>upcoming infrastructure</b> (metro, road widening, IT parks). We’ll share micro-market data during your consult.
      </div>
    </div>

    <div class="acc-item">
      <button class="acc-btn"><span class="icon">?</span><span class="q">What is the carbon footprint or sustainability rating of the building?</span></button>
      <div class="acc-panel">
        Check if the project is <b>IGBC/LEED</b> certified and whether it includes
        <b>rainwater harvesting</b>, <b>solar</b>, <b>STP</b>, waste management and energy-efficient design.
      </div>
    </div>

    <div class="acc-item">
      <button class="acc-btn"><span class="icon">?</span><span class="q">Is the area prone to flooding or water-logging during heavy rains?</span></button>
      <div class="acc-panel">
        Review <b>drainage plans</b>, <b>plot elevation</b>, nearby <b>SWDs</b> and historical flood data.
        Our team checks contour & flood-risk before shortlisting.
      </div>
    </div>

    <div class="acc-item">
      <button class="acc-btn"><span class="icon">?</span><span class="q">Is the apartment Vastu-compliant?</span></button>
      <div class="acc-panel">
        Many buyers prefer units with suitable <b>facing directions</b>, <b>kitchen/toilet placement</b> and
        ample <b>natural light & ventilation</b>. Share your priorities—we’ll filter accordingly.
      </div>
    </div>

    <div class="acc-item">
      <button class="acc-btn"><span class="icon">?</span><span class="q">Do I need to pay you anything as commission or service charge?</span></button>
      <div class="acc-panel">
        No. As a trusted channel partner, <b>GM HOMEZ does not charge customers any service fee</b>.
        We’re compensated by developers—so you get unbiased advice at zero cost.
      </div>
    </div>

  </div>
</section>

<script>
  // simple accordion
  document.querySelectorAll('.acc-item .acc-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const item = btn.closest('.acc-item');
      item.classList.toggle('open');
    });
  });
</script>

<?php render_footer(); ?>
