<?php
// contact.php — GM HOMEZ Contact Us (gradient UI)
require_once __DIR__.'/config.php';
require_once __DIR__.'/lib/render.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

$base = function_exists('render_base_url') ? render_base_url() : '';
render_header('Contact Us');

// Type → Category → Sub Category (example data; edit to your needs)
$TYPE_TREE = [
  'General' => [
    'Website Feedback' => ['Bug / Issue','Feature Request','Other'],
    'Information Request' => ['Buying','Selling','Partnership']
  ],
  'Support' => [
    'Account' => ['Login/Password','Profile Update','Delete Account'],
    'Listing' => ['Incorrect Info','Add/Remove Listing','Photos/Media']
  ],
  'Complaints' => [
    'Service' => ['Response Delay','Agent Behaviour','Other'],
    'Payment' => ['Refund','Receipt','Other']
  ],
];
?>
<style>
  :root{
    --ink:#0f172a; --muted:#64748b; --line:#e5e7eb;
    --g1:#0ea5e9; --g2:#8b5cf6;           /* cyan → violet */
    --a1:#f59e0b; --a2:#fb923c;           /* amber grad for buttons/accents */
  }
  /* page background soft gradient */
  body{
    background:
      linear-gradient(180deg, rgba(14,165,233,.06), rgba(139,92,246,.06)) fixed;
  }
  .wrap{max-width:1050px;margin:22px auto 28px;padding:0 18px}
  .lead{
    text-align:center;color:#0b1140;margin:0 0 18px;font-weight:700;
    letter-spacing:.06em
  }
  .sublead{
    text-align:center;color:var(--muted);max-width:80ch;margin:0 auto 18px
  }

  /* card with gradient border (border-image trick) */
  .card{
    border:2px solid transparent;border-radius:14px;background:#fff;
    background-origin:border-box;
    background-clip:padding-box,border-box;
    background-image:
      linear-gradient(#ffffff,#ffffff),
      linear-gradient(135deg,var(--g1),var(--g2));
    box-shadow:0 12px 30px rgba(17,24,39,.08);
  }

  .grid{display:grid;grid-template-columns:1.05fr 1fr;gap:24px}
  @media (max-width:960px){.grid{grid-template-columns:1fr}}

  .section-title{font-weight:700;margin:18px 0 8px;color:#111c45;letter-spacing:.03em}

  /* form */
  form{padding:18px}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
  @media (max-width:720px){.row{grid-template-columns:1fr}}
  label{display:block;font-size:14px;color:#334155;margin:10px 0 6px}
  select,input,textarea{
    width:100%;padding:12px 12px;border-radius:10px;border:1px solid var(--line);
    background:#fff;outline:none;transition:box-shadow .18s,border-color .18s;
  }
  textarea{min-height:110px;resize:vertical}
  /* gradient focus ring */
  select:focus,input:focus,textarea:focus{
    border-color:transparent;
    box-shadow:
      0 0 0 1px rgba(255,255,255,1) inset,
      0 0 0 3px rgba(14,165,233,.25),
      0 0 0 6px rgba(139,92,246,.15);
  }

  /* gradient button */
  .btn{
    display:inline-block;border:0;border-radius:12px;padding:12px 18px;
    background:linear-gradient(90deg,var(--a1),var(--a2));
    color:#111;font-weight:700;cursor:pointer;
    box-shadow:0 6px 18px rgba(245,158,11,.25);
  }
  .btn:disabled{opacity:.6;cursor:not-allowed}
  .note{color:#64748b;font-size:13px;margin-top:8px}

  /* info slab with gradient head */
  .info{
    overflow:hidden;border-radius:14px
  }
  .info-head{
    background:linear-gradient(135deg,var(--g1),var(--g2));
    color:#fff;padding:12px 16px;font-weight:700
  }
  .info-body{padding:16px}
  .k{
    display:grid;grid-template-columns:120px 1fr;gap:6px;margin:6px 0;color:#0f172a
  }
  .muted{color:#64748b}

  /* accordion for regional offices (optional) */
  details{background:#fff;border:1px solid var(--line);border-radius:10px;margin-top:10px}
  summary{padding:12px 14px;font-weight:600;cursor:pointer}
  details > div{padding:0 14px 14px 14px;color:#334155}
  .ok{background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:10px 12px;border-radius:10px;margin:0 0 12px}
  .err{background:#fff1f2;border:1px solid #f43f5e;color:#881337;padding:10px 12px;border-radius:10px;margin:0 0 12px}
</style>

<div class="wrap">

  <h2 class="lead">CONTACT US</h2>
  <p class="sublead">Get in touch with us for your property requirement, query, complaint or feedback. Our dedicated customer support team is happy to help.</p>

  <?php if (!empty($_GET['ok'])): ?>
    <div class="ok">Thanks! Your request has been submitted. We’ll get back to you soon.</div>
  <?php elseif (!empty($_GET['err'])): ?>
    <div class="err">Sorry, something went wrong. Please try again.</div>
  <?php endif; ?>

  <div class="grid">

    <!-- FORM -->
    <div class="card">
      <form action="<?= htmlspecialchars($base.'/contact_submit.php') ?>" method="post" id="contactForm" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

        <div class="section-title">Details of Issue</div>

        <div class="row">
          <div>
            <label for="type">Type</label>
            <select id="type" name="type" required></select>
          </div>
          <div>
            <label for="category">Category</label>
            <select id="category" name="category" required></select>
          </div>
        </div>

        <div class="row">
          <div>
            <label for="subcategory">Sub Category</label>
            <select id="subcategory" name="subcategory" required></select>
          </div>
          <div>
            <label for="comment">Comment</label>
            <textarea id="comment" name="comment" placeholder="Enter your comment here" required></textarea>
          </div>
        </div>

        <div class="section-title">Contact Info</div>

        <div class="row">
          <div>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" placeholder="your@email.com" required>
          </div>
          <div>
            <label for="phone">Phone</label>
            <div class="row" style="grid-template-columns:140px 1fr">
              <select id="cc" name="country_code" aria-label="Country code">
                <option value="+91">(+91) India</option>
                <option value="+971">(+971) UAE</option>
                <option value="+61">(+61) Australia</option>
                <option value="+1">(+1) USA/Canada</option>
                <option value="+44">(+44) UK</option>
              </select>
              <input id="phone" name="phone" type="tel" placeholder="Phone" pattern="[0-9\s\-]{7,15}" required>
            </div>
          </div>
        </div>

        <div class="note">
          By submitting, you agree to our
          <a href="<?= htmlspecialchars($base.'/privacy-policy.php') ?>">Terms & Privacy</a>.
        </div>

        <div style="margin-top:14px">
          <button class="btn" type="submit">Submit</button>
        </div>
      </form>
    </div>

    <!-- INFO BOX -->
    <div class="info card" style="background-image:linear-gradient(#fff,#fff),linear-gradient(135deg,var(--a1),var(--a2));">
      <div class="info-head">Helpdesk & Working Hours</div>
      <div class="info-body">
        <div class="k"><div class="muted">Helpline</div><div>1800-103-104-1</div></div>
        <div class="k"><div class="muted">Hours</div><div>9:00 AM to 6:00 PM · 365 days (except national holidays)</div></div>

        <details>
          <summary>Regional Offices</summary>
          <div>
            <div class="k"><div class="muted">North Zone</div><div>Bengaluru · Gurgaon · Noida</div></div>
            <div class="k"><div class="muted">West Zone</div><div>Mumbai · Pune · Ahmedabad</div></div>
            <div class="k"><div class="muted">South Zone</div><div>Chennai · Hyderabad</div></div>
            <div class="k"><div class="muted">East Zone</div><div>Kolkata</div></div>
          </div>
        </details>
      </div>
    </div>

  </div><!-- /grid -->
</div><!-- /wrap -->

<script>
// --- dependent selects (Type -> Category -> Subcategory)
const TYPE_TREE = <?= json_encode($TYPE_TREE, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) ?>;

const $type = document.getElementById('type');
const $cat  = document.getElementById('category');
const $sub  = document.getElementById('subcategory');

function fill(sel, items){
  sel.innerHTML = '';
  for(const label of items){
    const opt = document.createElement('option');
    opt.value = opt.textContent = label;
    sel.appendChild(opt);
  }
}

function onType(){
  const t = $type.value;
  const cats = Object.keys(TYPE_TREE[t] || {});
  fill($cat, cats);
  onCat();
}
function onCat(){
  const t = $type.value;
  const c = $cat.value;
  const subs = (TYPE_TREE[t] && TYPE_TREE[t][c]) ? TYPE_TREE[t][c] : [];
  fill($sub, subs);
}

// init with first type
fill($type, Object.keys(TYPE_TREE));
$type.addEventListener('change', onType);
$cat.addEventListener('change', onCat);
onType();

// basic client validation before submit
document.getElementById('contactForm').addEventListener('submit', (e)=>{
  const email = document.getElementById('email').value.trim();
  const phone = document.getElementById('phone').value.trim();
  if(!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)){
    alert('Please enter a valid email'); e.preventDefault(); return;
  }
  if(!/^[0-9\s-]{7,15}$/.test(phone)){
    alert('Please enter a valid phone number'); e.preventDefault(); return;
  }
});
</script>

<?php render_footer(); ?>
