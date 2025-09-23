<?php
// pages/builder_admin_save.php
require_once __DIR__ . '/../lib/builders.php';

gm_require_admin();
$slug = $_POST['slug'] ?? '';
if (!$slug) { http_response_code(400); echo 'Missing slug'; exit; }

$in = $_POST;
$data = gm_builder_read($slug);

$data['last_updated'] = trim($in['last_updated'] ?? $data['last_updated']);

// Overview
$ov = $data['overview'];
$ov['city'] = trim($in['overview']['city'] ?? $ov['city']);
$ov['location'] = trim($in['overview']['location'] ?? $ov['location']);
$ov['about'] = trim($in['overview']['about'] ?? $ov['about']);
$ov['rera_id'] = trim($in['overview']['rera_id'] ?? $ov['rera_id']);
$ov['key_facts']['launch'] = trim($in['overview']['key_facts']['launch'] ?? $ov['key_facts']['launch']);
$ov['key_facts']['possession'] = trim($in['overview']['key_facts']['possession'] ?? $ov['key_facts']['possession']);
$banks_csv = $in['banks_csv'] ?? '';
$ov['banks_supported'] = array_values(array_filter(array_map('trim', preg_split('/[,\\n]/', $banks_csv))));
$features_lines = $in['features_lines'] ?? '';
$ov['salient_features'] = array_values(array_filter(array_map('trim', preg_split("/\\r?\\n/", $features_lines))));
$data['overview'] = $ov;

// Stats
$data['stats']['total_experience'] = trim($in['stats']['total_experience'] ?? $data['stats']['total_experience']);
$data['stats']['total_projects'] = (int)($in['stats']['total_projects'] ?? $data['stats']['total_projects']);
$data['stats']['ongoing_projects'] = (int)($in['stats']['ongoing_projects'] ?? $data['stats']['ongoing_projects']);

// Floor plans
$fps = $in['floor_plans'] ?? [];
$clean = [];
foreach ($fps as $p) {
  $bhk = trim($p['bhk'] ?? '');
  $carpet = trim($p['carpet'] ?? '');
  $price = trim($p['price'] ?? '');
  $image = trim($p['image'] ?? '');
  if ($bhk || $carpet || $price || $image) $clean[] = compact('bhk','carpet','price','image');
}
$data['floor_plans'] = $clean;

// Amenities
$amen_csv = $in['amenities_csv'] ?? '';
$data['amenities'] = array_values(array_filter(array_map('trim', preg_split('/[,\\n]/', $amen_csv))));

// Gallery
$imgs_lines = $in['gallery_images'] ?? '';
$images = array_values(array_filter(array_map('trim', preg_split("/\\r?\\n/", $imgs_lines))));
$data['gallery']['images'] = $images;
$data['gallery']['video_url'] = trim($in['gallery']['video_url'] ?? $data['gallery']['video_url']);

// Home loan
$data['home_loan']['loan_amount'] = trim($in['home_loan']['loan_amount'] ?? $data['home_loan']['loan_amount']);
$data['home_loan']['interest_rate'] = trim($in['home_loan']['interest_rate'] ?? $data['home_loan']['interest_rate']);
$data['home_loan']['tenure_years'] = trim($in['home_loan']['tenure_years'] ?? $data['home_loan']['tenure_years']);

// Map
$data['map']['lat'] = trim($in['map']['lat'] ?? $data['map']['lat']);
$data['map']['lng'] = trim($in['map']['lng'] ?? $data['map']['lng']);

// FAQs
$fIn = $in['faqs'] ?? [];
$fClean = [];
foreach ($fIn as $qa) {
  $q = trim($qa['q'] ?? ''); $a = trim($qa['a'] ?? '');
  if ($q || $a) $fClean[] = ['q'=>$q,'a'=>$a];
}
$data['faqs'] = $fClean;

// Save
if (!gm_builder_write($slug, $data)) {
  http_response_code(500);
  echo 'Failed to save. Check file permissions on /data/builders';
  exit;
}

header('Location: /' . $slug . '-admin/?saved=1');
exit;
