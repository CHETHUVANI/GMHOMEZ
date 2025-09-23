<?php
require_once __DIR__.'/_common.php'; pg_require_login();

$DATA = pg_read_json(__DIR__.'/../data/properties.json');

$orig_id = $_POST['orig_id'] ?? '';
$id      = trim($_POST['id'] ?? ($orig_id ?: pg_slug($_POST['name'] ?? 'project')));
$id      = pg_slug($id ?: 'project');

$dir = realpath(__DIR__.'/..')."/uploads/projects/$id";
if (!is_dir($dir)) mkdir($dir, 0775, true);

// helper to save upload
function pg_save_file($file,$dir,$id){
  $name = preg_replace('~[^a-zA-Z0-9_.-]+~','_',$file['name']);
  $name = time().'_'.$name;
  $dest = rtrim($dir,'/').'/'.$name;
  if (is_uploaded_file($file['tmp_name'])) move_uploaded_file($file['tmp_name'], $dest);
  return '/uploads/projects/'.$id.'/'.$name;
}

/* build property */
$prop = [
  'id' => $id,
  'name' => $_POST['name'] ?? '',
  'builder' => PG_BUILDER, // locked
  'city' => $_POST['city'] ?? '',
  'location' => $_POST['location'] ?? '',
  'possession_ym' => $_POST['possession_ym'] ?? '',
  'launch_ym' => $_POST['launch_ym'] ?? '',
  'status' => $_POST['status'] ?? '',
  'price_min' => $_POST['price_min'] !== '' ? (float)$_POST['price_min'] : null,
  'price_max' => $_POST['price_max'] !== '' ? (float)$_POST['price_max'] : null,
  'total_units' => $_POST['total_units'] !== '' ? (int)$_POST['total_units'] : '',
  'total_area_acres' => $_POST['total_area_acres'] !== '' ? (float)$_POST['total_area_acres'] : '',
  'rera_id' => $_POST['rera_id'] ?? '',
  'resale' => $_POST['resale'] ?? '',
  'approved_banks' => array_values(array_filter(array_map('trim', explode(',', $_POST['approved_banks'] ?? '')))),
  'salient_features' => array_values(array_filter(array_map('trim', preg_split('~\r?\n~', $_POST['salient_features'] ?? '')))),
  'overview' => $_POST['overview'] ?? '',
  'loan_defaults' => [
    'amount_lakhs' => $_POST['loan_amount'] !== '' ? (float)$_POST['loan_amount'] : 50,
    'tenure_years' => $_POST['loan_tenure'] !== '' ? (int)$_POST['loan_tenure'] : 5,
    'rate_pa'      => $_POST['loan_rate'] !== '' ? (float)$_POST['loan_rate'] : 9,
  ],
  'lat' => $_POST['lat'] !== '' ? (float)$_POST['lat'] : null,
  'lng' => $_POST['lng'] !== '' ? (float)$_POST['lng'] : null,
  'ask_enabled' => isset($_POST['ask_enabled']) ? (bool)$_POST['ask_enabled'] : true,
  'ask_copy' => $_POST['ask_copy'] ?? '',
  'disclaimer' => $_POST['disclaimer'] ?? '',
];

// Floor plans
$bhk = $_POST['fp_bhk'] ?? [];
$car = $_POST['fp_carpet'] ?? [];
$prc = $_POST['fp_price'] ?? [];
$lbl = $_POST['fp_label'] ?? [];
$prop['floor_plans'] = [];
for ($i=0; $i<count($bhk); $i++){
  if ($bhk[$i]==='') continue;
  $prop['floor_plans'][] = [
    'bhk' => (int)$bhk[$i],
    'carpet_area' => $car[$i] !== '' ? (int)$car[$i] : null,
    'builder_price' => $prc[$i] !== '' ? (float)$prc[$i] : null,
    'unit_label' => $lbl[$i] ?? '',
  ];
}

// Amenities
$prop['amenities'] = array_values(array_filter(array_map('trim', preg_split('~\r?\n~', $_POST['amenities'] ?? ''))));

// Existing (if any)
$EXIST = []; foreach ($DATA as $p){ if (($p['id']??'')===$id){ $EXIST=$p; break; } }
$gallery = $EXIST['gallery'] ?? [];
$video_url = $EXIST['video_url'] ?? ($EXIST['videos'][0] ?? null);

// Upload video
if (!empty($_FILES['video']['name'])) {
  $video_url = pg_save_file($_FILES['video'], $dir, $id);
}
$prop['video_url'] = $video_url ?: null;

// Upload gallery images
if (!empty($_FILES['gallery']['name'][0])) {
  for ($i=0; $i<count($_FILES['gallery']['name']); $i++){
    if (!$_FILES['gallery']['name'][$i]) continue;
    $file = ['name'=>$_FILES['gallery']['name'][$i],'tmp_name'=>$_FILES['gallery']['tmp_name'][$i]];
    $gallery[] = pg_save_file($file, $dir, $id);
  }
}
$prop['gallery'] = array_values(array_unique($gallery));

// Floor plan images batch
if (!empty($_FILES['fp_images']['name'][0])) {
  $idx=0;
  for ($i=0; $i<count($_FILES['fp_images']['name']); $i++){
    if (!$_FILES['fp_images']['name'][$i]) continue;
    $file = ['name'=>$_FILES['fp_images']['name'][$i],'tmp_name'=>$_FILES['fp_images']['tmp_name'][$i]];
    $url = pg_save_file($file, $dir, $id);
    // assign image to next FP row without image
    while ($idx < count($prop['floor_plans']) && !empty($prop['floor_plans'][$idx]['image'])) $idx++;
    if ($idx < count($prop['floor_plans'])) $prop['floor_plans'][$idx]['image'] = $url;
    $idx++;
  }
}

// Merge back only Prestige projects
$found=false;
foreach ($DATA as $k=>$p){
  if (($p['id']??'')===$id){
    if (($p['builder']??'')===PG_BUILDER){ $DATA[$k]=$prop; $found=true; }
  }
}
if (!$found) $DATA[]=$prop;

pg_write_json(__DIR__.'/../data/properties.json',$DATA);
header('Location: '.pg_url('index.php'));
