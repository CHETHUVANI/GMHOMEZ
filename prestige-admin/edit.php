<?php
require_once __DIR__.'/_common.php'; pg_require_login();
$DATA = pg_read_json(__DIR__.'/../data/properties.json');
$id = $_GET['id'] ?? '';
$prop = null; foreach($DATA as $p){ if(($p['id']??'')===$id){ $prop=$p; break; } }
function val($k,$d=''){ global $prop; return htmlspecialchars($prop[$k]??$d, ENT_QUOTES,'UTF-8'); }
?>
<!doctype html><meta charset="utf-8">
<title><?= $prop?'Edit':'Add' ?> Prestige Project</title>
<style>
body{background:#0b1620;color:#e6f0f6;font-family:system-ui;margin:0}
.top{display:flex;align-items:center;gap:10px;padding:12px 16px;background:#0c1b25;border-bottom:1px solid rgba(148,163,184,.18)}
.container{max-width:1100px;margin:18px auto;padding:0 16px}
.tabs{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px}
.tab{padding:8px 12px;border:1px solid rgba(148,163,184,.18);border-radius:10px;background:#102a37;cursor:pointer}
.tab.active{background:#123246}
.card{background:#0f2430;border:1px solid rgba(148,163,184,.18);border-radius:12px;padding:14px;margin-bottom:14px}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
label{display:block;margin-top:8px}
input, textarea, select{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(148,163,184,.18);background:#0b1c27;color:#e6f0f6}
.small{font-size:12px;color:#9fb2c0}
.row{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px}
.btn{padding:8px 12px;border-radius:10px;border:1px solid rgba(148,163,184,.18);background:#102a37;color:#e6f0f6;cursor:pointer}
.badge{padding:3px 8px;border:1px solid rgba(148,163,184,.18);border-radius:999px;background:#0b1c27}
</style>

<div class="top">
  <div style="font-weight:700">Prestige Admin</div>
  <div style="flex:1"></div>
  <a class="btn" href="index.php">Back</a>
</div>

<div class="container">
<form action="save.php" method="post" enctype="multipart/form-data" id="f">
<input type="hidden" name="orig_id" value="<?=val('id')?>">
<div class="tabs" id="tabs">
  <div class="tab active" data-t="basic">Overview</div>
  <div class="tab" data-t="floor">Floor Plan</div>
  <div class="tab" data-t="amen">Amenities</div>
  <div class="tab" data-t="gallery">Gallery</div>
  <div class="tab" data-t="loan">Home Loan</div>
  <div class="tab" data-t="map">Map</div>
  <div class="tab" data-t="ask">Have a Question</div>
</div>

<!-- Overview -->
<div class="card sec" data-s="basic">
  <div class="grid2">
    <div>
      <label>Project Name<input name="name" value="<?=val('name')?>"></label>
      <label><b>Builder (locked)</b><input value="<?=PG_BUILDER?>" disabled><input type="hidden" name="builder" value="<?=PG_BUILDER?>"></label>
      <label>City<input name="city" value="<?=val('city')?>"></label>
      <label>Location<input name="location" value="<?=val('location')?>"></label>
    </div>
    <div>
      <label>Project ID (slug)<input name="id" value="<?=val('id')?>" placeholder="prestige-sun-crest"></label>
      <label>Possession (YYYY-MM)<input name="possession_ym" value="<?=val('possession_ym')?>"></label>
      <label>Launch (YYYY-MM)<input name="launch_ym" value="<?=val('launch_ym')?>"></label>
      <label>Status<input name="status" value="<?=val('status')?>"></label>
    </div>
  </div>
  <div class="row">
    <label>Price Min (Lakhs)<input name="price_min" type="number" step="0.01" value="<?=val('price_min')?>"></label>
    <label>Price Max (Lakhs)<input name="price_max" type="number" step="0.01" value="<?=val('price_max')?>"></label>
    <label>Total Units<input name="total_units" type="number" value="<?=val('total_units')?>"></label>
    <label>Total Area (Acres)<input name="total_area_acres" type="number" step="0.01" value="<?=val('total_area_acres')?>"></label>
  </div>
  <div class="row">
    <label>RERA ID<input name="rera_id" value="<?=val('rera_id')?>"></label>
    <label>Resale<input name="resale" value="<?=val('resale')?>"></label>
    <label>Approved Banks (comma separated)<input name="approved_banks" value="<?= htmlspecialchars(implode(', ', $prop['approved_banks']??[]))?>"></label>
    <label>Salient Features (one per line)<textarea name="salient_features" rows="4"><?= htmlspecialchars(implode("\n",$prop['salient_features']??[]))?></textarea></label>
  </div>
  <label>About / Overview<textarea name="overview" rows="6"><?=val('overview')?></textarea></label>
</div>

<!-- Floor Plans -->
<div class="card sec" data-s="floor" style="display:none">
  <div class="small">Add rows per BHK. Price in Lakhs. Images saved under <span class="badge">/uploads/projects/&lt;id&gt;/</span></div>
  <div id="fpRows"></div>
  <button type="button" class="btn" onclick="addFp()">+ Add Floor Plan</button>
  <input type="file" name="fp_images[]" id="fpImages" multiple accept="image/*" style="margin-top:10px">
  <div class="small">Attach images for new rows (existing rows keep current image).</div>
</div>

<!-- Amenities -->
<div class="card sec" data-s="amen" style="display:none">
  <label>Amenities (one per line)<textarea name="amenities" rows="10"><?= htmlspecialchars(implode("\n",$prop['amenities']??[]))?></textarea></label>
</div>

<!-- Gallery -->
<div class="card sec" data-s="gallery" style="display:none">
  <label>Primary Video (mp4) <input type="file" name="video"></label>
  <div class="small">Current: <?= htmlspecialchars(($prop['video_url']??($prop['videos'][0]??''))) ?></div>
  <label>Gallery Images (add) <input type="file" name="gallery[]" multiple accept="image/*"></label>
  <div class="small">Existing images:</div>
  <div><?php foreach(($prop['gallery']??[]) as $g): ?><div class="badge"><?=$g?></div><?php endforeach;?></div>
</div>

<!-- Home Loan defaults -->
<div class="card sec" data-s="loan" style="display:none">
  <?php $loan=$prop['loan_defaults']??['amount_lakhs'=>$prop['price_min']??50,'tenure_years'=>5,'rate_pa'=>9]; ?>
  <div class="row">
    <label>Default Amount (Lakhs)<input name="loan_amount" type="number" step="1" value="<?=htmlspecialchars($loan['amount_lakhs'])?>"></label>
    <label>Default Tenure (Years)<input name="loan_tenure" type="number" step="1" value="<?=htmlspecialchars($loan['tenure_years'])?>"></label>
    <label>Default Rate (% p.a.)<input name="loan_rate" type="number" step="0.1" value="<?=htmlspecialchars($loan['rate_pa'])?>"></label>
  </div>
</div>

<!-- Map -->
<div class="card sec" data-s="map" style="display:none">
  <div class="row">
    <label>Latitude<input name="lat" type="number" step="0.000001" value="<?=val('lat')?>"></label>
    <label>Longitude<input name="lng" type="number" step="0.000001" value="<?=val('lng')?>"></label>
  </div>
</div>

<!-- Ask -->
<div class="card sec" data-s="ask" style="display:none">
  <label>Enable “Have a Question?”
    <select name="ask_enabled">
      <option value="1" <?= !isset($prop['ask_enabled']) || $prop['ask_enabled'] ? 'selected':'' ?>>Yes</option>
      <option value="0" <?= isset($prop['ask_enabled']) && !$prop['ask_enabled'] ? 'selected':'' ?>>No</option>
    </select>
  </label>
  <label>Ask copy (optional)<textarea name="ask_copy" rows="4"><?=val('ask_copy')?></textarea></label>
  <label>Disclaimer<textarea name="disclaimer" rows="5"><?=val('disclaimer')?></textarea></label>
</div>

<div style="display:flex;gap:10px">
  <button class="btn">Save</button>
  <?php if($prop): ?><a class="btn" href="../project.php?id=<?=urlencode($prop['id'])?>" target="_blank">Open View Page</a><?php endif; ?>
</div>

<template id="tplFp">
  <div class="row">
    <label>BHK<input name="fp_bhk[]" type="number"></label>
    <label>Carpet Area (sqft)<input name="fp_carpet[]" type="number" step="1"></label>
    <label>Builder Price (Lakhs)<input name="fp_price[]" type="number" step="0.01"></label>
    <label>Unit Label<input name="fp_label[]" placeholder="e.g., 2BHK+2T"></label>
  </div>
</template>
</form>
</div>

<script>
const tabs=document.getElementById('tabs'); const secs=[...document.querySelectorAll('.sec')];
tabs.addEventListener('click',e=>{const t=e.target.closest('.tab'); if(!t) return;
  tabs.querySelectorAll('.tab').forEach(x=>x.classList.toggle('active',x===t));
  secs.forEach(s=>s.style.display = (s.dataset.s===t.dataset.t)?'':'none';
});

const existing = <?=json_encode($prop['floor_plans']??[], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)?>;
const fpWrap=document.getElementById('fpRows'); const tpl=document.getElementById('tplFp').content;
function addFp(data){ const n=tpl.cloneNode(true); if(data){
  n.querySelector('[name="fp_bhk[]"]').value = data.bhk||'';
  n.querySelector('[name="fp_carpet[]"]').value = data.carpet_area||'';
  n.querySelector('[name="fp_price[]"]').value = data.builder_price??'';
  n.querySelector('[name="fp_label[]"]').value = data.unit_label||'';
} fpWrap.appendChild(n); }
existing.forEach(addFp);
</script>
