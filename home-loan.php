<?php
require_once __DIR__.'/lib/render.php';
render_header('Start your Home Loan'); // sidebar is ON by default
$base = render_base_url();
?>
<style>
  :root{
    --ink:#0f172a; --muted:#64748b; --line:#e5e7eb;
    --g1:#06b6d4; --g2:#7c3aed;                  /* cyan → violet (brand grad) */
    --btn1:#f59e0b; --btn2:#fb923c;               /* amber grad for CTAs */
    --card:#ffffff;
  }
  .loan-wrap{display:grid;grid-template-columns:1.1fr 360px;gap:24px;margin:14px 0 26px}
  @media (max-width:1080px){.loan-wrap{grid-template-columns:1fr}}
  .card{
    border:2px solid transparent;border-radius:16px;background:var(--card);
    background-origin:border-box;background-clip:padding-box,border-box;
    background-image:linear-gradient(#fff,#fff),
      linear-gradient(135deg,var(--g1),var(--g2));
    box-shadow:0 14px 36px rgba(15,23,42,.06);
  }
  .pad{padding:18px}
  h1{font-size:26px;margin:6px 0 14px}
  .muted{color:var(--muted)}

  /* steps header */
  .steps{display:flex;gap:24px;margin:6px 0 18px}
  .step{display:flex;align-items:center;gap:10px;color:#334155}
  .step .ico{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;
    border:1px solid var(--line); background:#fff}
  .step.active .ico{
    border-color:transparent;
    background:linear-gradient(135deg,var(--g1),var(--g2)); color:#fff;
  }
  .hr{height:1px;background:#eef2f7;margin:6px 0 16px}

  /* inputs */
  label{display:block;font-size:14px;margin:12px 0 6px;color:#334155}
  input,select,textarea{
    width:100%;padding:12px 12px;border-radius:12px;border:1px solid var(--line);outline:none;
    transition:border-color .18s, box-shadow .18s}
  input:focus,select:focus,textarea:focus{
    border-color:transparent;
    box-shadow:0 0 0 1px #fff inset, 0 0 0 3px rgba(6,182,212,.28), 0 0 0 6px rgba(124,58,237,.16);
  }
  .row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  @media (max-width:720px){.row{grid-template-columns:1fr}}

  /* pill buttons */
  .pills{display:flex;flex-wrap:wrap;gap:10px}
  .pill{
    padding:10px 14px;border-radius:999px;border:1px solid var(--line);cursor:pointer;
    background:#fff; transition:all .18s; font-weight:600; color:#0f172a
  }
  .pill.on{
    color:#111; border-color:transparent;
    background:linear-gradient(#fff,#fff) padding-box,
               linear-gradient(135deg,var(--g1),var(--g2)) border-box;
    border:2px solid transparent;
  }

  /* cta */
  .cta{display:flex;gap:10px;justify-content:flex-end;margin-top:14px}
  .btn{
    border:0;border-radius:12px;padding:12px 16px;font-weight:700;cursor:pointer;
    background:linear-gradient(90deg,var(--btn1),var(--btn2)); color:#111;
    box-shadow:0 8px 22px rgba(245,158,11,.28);
  }
  .btn.secondary{
    background:#fff;border:1px solid var(--line);box-shadow:none;color:#0f172a
  }

  /* right side: progress + advantages */
  .side .head{padding:14px 16px;border-bottom:1px solid #eef2f7;font-weight:800;color:#111c45}
  .side .body{padding:16px}
  .ring{
    --p:0; /* % filled; set via JS */
    width:160px;height:160px;border-radius:50%;
    background:
      conic-gradient(var(--btn1) calc(var(--p)*1%), #e5e7eb 0),
      radial-gradient(#fff 62%, transparent 63% 100%);
    margin:14px auto 8px; display:grid;place-items:center;font-weight:800;color:#0b1140
  }
  .adv{display:grid;gap:14px;margin-top:8px}
  .adv .it{display:grid;grid-template-columns:40px 1fr;gap:10px;align-items:start}
  .adv svg{width:32px;height:32px}

  /* hide/show steps */
  .screen{display:none}
  .screen.show{display:block}
</style>

<div class="loan-wrap">

  <!-- left: form -->
  <section class="card">
    <div class="pad">
      <h1>Start your Home Loan</h1>
      <div class="muted">A quick 3-step flow to share your needs. We’ll do the heavy lifting.</div>

      <div class="steps">
        <div class="step active" id="s1tag"><div class="ico">🏠</div><div>Property snapshot</div></div>
        <div class="step" id="s2tag"><div class="ico">₹</div><div>Loan preferences</div></div>
        <div class="step" id="s3tag"><div class="ico">👤</div><div>Applicant info</div></div>
      </div>
      <div class="hr"></div>

      <form id="loanForm" action="<?= h($base) ?>/home-loan-submit.php" method="post" novalidate>

        <!-- STEP 1 -->
        <div class="screen show" id="step1">
          <label for="project">Project / Property name</label>
          <input id="project" name="project" placeholder="e.g., Sunrise Residency, Tower B" required>

          <label>Construction stage</label>
          <div class="pills" data-name="stage">
            <button type="button" class="pill">Soft Launch</button>
            <button type="button" class="pill">Launch</button>
            <button type="button" class="pill">Under Construction</button>
            <button type="button" class="pill">Ready</button>
          </div>

          <label>Transaction type</label>
          <div class="pills" data-name="txn">
            <button type="button" class="pill">Purchase</button>
            <button type="button" class="pill">Balance Transfer</button>
            <button type="button" class="pill">Refinance</button>
          </div>

          <label>Purchasing from</label>
          <div class="pills" data-name="source">
            <button type="button" class="pill">Builder</button>
            <button type="button" class="pill">Resale</button>
            <button type="button" class="pill">Self Construction</button>
          </div>

          <!-- hidden mirrors for pills -->
          <input type="hidden" name="stage" required>
          <input type="hidden" name="txn" required>
          <input type="hidden" name="source" required>

          <div class="cta"><button type="button" class="btn" id="to2">Next</button></div>
        </div>

        <!-- STEP 2 -->
        <div class="screen" id="step2">
          <div class="row">
            <div>
              <label for="value">Property value (₹)</label>
              <input id="value" name="value" type="number" min="0" step="10000" placeholder="e.g., 80,00,000" required>
            </div>
            <div>
              <label for="amount">Loan amount (₹)</label>
              <input id="amount" name="amount" type="number" min="0" step="10000" placeholder="e.g., 60,00,000" required>
            </div>
          </div>

          <div class="row">
            <div>
              <label for="rate">Interest rate (% p.a.)</label>
              <input id="rate" name="rate" type="number" min="5" max="20" step="0.05" value="8.50" required>
            </div>
            <div>
              <label for="tenure">Tenure (years)</label>
              <input id="tenure" name="tenure" type="number" min="1" max="30" value="20" required>
            </div>
          </div>

          <div class="row">
            <div>
              <label>Estimated EMI</label>
              <input id="emi" readonly placeholder="₹ —" style="font-weight:700;background:#f9fafb">
            </div>
            <div>
              <label>Approx. monthly interest at start</label>
              <input id="mi" readonly placeholder="₹ —" style="background:#f9fafb">
            </div>
          </div>

          <div class="cta">
            <button type="button" class="btn secondary" id="back1">Back</button>
            <button type="button" class="btn" id="to3">Next</button>
          </div>
        </div>

        <!-- STEP 3 -->
        <div class="screen" id="step3">
          <div class="row">
            <div>
              <label for="name">Full name</label>
              <input id="name" name="name" required>
            </div>
            <div>
              <label for="city">City</label>
              <input id="city" name="city" placeholder="e.g., Bengaluru" required>
            </div>
          </div>

          <div class="row">
            <div>
              <label for="email">Email</label>
              <input id="email" name="email" type="email" placeholder="you@email.com" required>
            </div>
            <div>
              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="tel" pattern="[0-9\s\-]{7,15}" placeholder="10-digit mobile" required>
            </div>
          </div>

          <label>Employment type</label>
          <div class="pills" data-name="employment">
            <button type="button" class="pill">Salaried</button>
            <button type="button" class="pill">Self-employed</button>
          </div>
          <input type="hidden" name="employment" required>

          <div class="row">
            <div>
              <label for="income">Monthly income (₹)</label>
              <input id="income" name="income" type="number" min="0" step="1000" placeholder="e.g., 1,20,000" required>
            </div>
            <div>
              <label for="down">Planned down payment (₹)</label>
              <input id="down" name="down" type="number" min="0" step="10000" placeholder="optional">
            </div>
          </div>

          <div class="cta">
            <button type="button" class="btn secondary" id="back2">Back</button>
            <button type="submit" class="btn">Submit</button>
          </div>
        </div>

      </form>
    </div>
  </section>

  <!-- right: progress + advantages -->
  <aside class="card side">
    <div class="head">Hello,<br><span class="muted">tell us a bit about you</span></div>
    <div class="body">
      <div class="ring" id="ring"><div id="pct">0%</div></div>
      <div class="muted" style="text-align:center">Profile completion</div>

      <div style="margin-top:14px;font-weight:700">Why GM HOMEZ Loans</div>
      <div class="adv">
        <div class="it">
          <svg viewBox="0 0 24 24" fill="none" stroke="#06b6d4" stroke-width="1.6"><path d="M12 2l3 7h7l-5.5 4.5L18 21l-6-4-6 4 1.5-7.5L2 9h7z"/></svg>
          <div><b>Guided choices</b><div class="muted">Compare options with an expert by your side.</div></div>
        </div>
        <div class="it">
          <svg viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="1.6"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
          <div><b>Paperwork, simplified</b><div class="muted">We prioritise speed and convenience.</div></div>
        </div>
        <div class="it">
          <svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M12 8v5l3 2"/></svg>
          <div><b>Transparent updates</b><div class="muted">Track each milestone—no surprises.</div></div>
        </div>
      </div>
    </div>
  </aside>

</div>

<script>
// step controls
const s1=document.getElementById('step1'), s2=document.getElementById('step2'), s3=document.getElementById('step3');
const tags=[document.getElementById('s1tag'),document.getElementById('s2tag'),document.getElementById('s3tag')];
const ring=document.getElementById('ring'), pct=document.getElementById('pct');
const show = n => {
  [s1,s2,s3].forEach((el,i)=>el.classList.toggle('show', i===n));
  tags.forEach((t,i)=>t.classList.toggle('active', i<=n));
  const p=[20,60,100][n]; ring.style.setProperty('--p', p); pct.textContent=p+'%';
};
document.getElementById('to2').onclick = ()=>{ if(validateStep1()) show(1); };
document.getElementById('to3').onclick = ()=>{ if(validateStep2()) show(2); };
document.getElementById('back1').onclick = ()=>show(0);
document.getElementById('back2').onclick = ()=>show(1);

// pill behaviour (single-select per group)
document.querySelectorAll('.pills').forEach(group=>{
  group.addEventListener('click',e=>{
    const b=e.target.closest('.pill'); if(!b) return;
    group.querySelectorAll('.pill').forEach(x=>x.classList.remove('on'));
    b.classList.add('on');
    const name=group.getAttribute('data-name');
    const hidden=document.querySelector(`input[name="${name}"]`);
    if(hidden) hidden.value=b.textContent.trim();
  });
});

// EMI calc (simple)
const amount=document.getElementById('amount'),
      rate=document.getElementById('rate'),
      tenure=document.getElementById('tenure'),
      emi=document.getElementById('emi'),
      mi=document.getElementById('mi');

function recalc(){
  const P=+amount.value||0, r=(+rate.value||0)/1200, n=(+tenure.value||0)*12;
  if(P>0 && r>0 && n>0){
    const E = P*r*Math.pow(1+r,n)/(Math.pow(1+r,n)-1);
    emi.value = '₹ ' + Math.round(E).toLocaleString('en-IN');
    mi.value  = '₹ ' + Math.round(P*r).toLocaleString('en-IN');
  }else{
    emi.value=''; mi.value='';
  }
}
[amount,rate,tenure].forEach(i=>i.addEventListener('input',recalc));

function validateStep1(){
  // ensure pills picked
  const need=['stage','txn','source'];
  for(const n of need){
    const v=document.querySelector(`input[name="${n}"]`).value.trim();
    if(!v){ alert('Please select '+n.replace('_',' ')); return false; }
  }
  if(!document.getElementById('project').value.trim()){ alert('Please enter project / property name.'); return false; }
  return true;
}
function validateStep2(){
  if(!(amount.value&&rate.value&&tenure.value)){ alert('Please fill loan amount, rate and tenure.'); return false; }
  recalc(); return true;
}
</script>

<?php render_footer(); ?>
