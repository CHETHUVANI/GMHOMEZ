<?php
// --- Load builders list robustly ---
$builders = [];
$path = __DIR__ . '/../data/builders.json';   // partials/.. points to project root

$raw = @file_get_contents($path);
if ($raw !== false) {
  $data = json_decode($raw, true);
  if (is_array($data)) {
    // Support both shapes: array-of-builders OR { "builders": [...] }
    if (isset($data['builders']) && is_array($data['builders'])) {
      $builders = $data['builders'];
    } elseif (array_values($data) === $data) { // list-style array
      $builders = $data;
    }
  }
}

// small helper: return name if slug missing
function gm_val($arr, $keyA, $keyB) {
  return isset($arr[$keyA]) ? $arr[$keyA] : (isset($arr[$keyB]) ? $arr[$keyB] : '');
}
?>

<div class="footer-column">
  <h4>Builders</h4>
  <?php if (!$builders): ?>
    <div class="muted">No builders yet.</div>
  <?php else: ?>
    <ul class="footer-list">
      <?php foreach ($builders as $b): 
        $name = gm_val($b, 'name', 'title');
        $slug = gm_val($b, 'slug', 'name'); // fallback: use name as slug
        // keep using `?builder=<value>` because your builder pages expect that
        $href = $BASE . '/builders.php?builder=' . urlencode($slug);
      ?>
        <li><a href="<?= htmlspecialchars($href) ?>"><?= htmlspecialchars($name) ?></a></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
