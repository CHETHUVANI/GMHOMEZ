<?php
header('Content-Type: application/xml; charset=UTF-8');

$base = 'https://www.gmhomez.in'; // change if you prefer non-www

// Seed with homepage + add static pages if you have them
$urls = [
  ['loc' => $base . '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
  // Example static pages:
  // ['loc' => $base . '/about.php',   'priority' => '0.7', 'changefreq' => 'monthly'],
  // ['loc' => $base . '/contact.php', 'priority' => '0.7', 'changefreq' => 'monthly'],
];

// Add property/detail pages if present
$propsFile = __DIR__ . '/data/properties.json';
if (is_file($propsFile)) {
  $raw = file_get_contents($propsFile);
  $props = json_decode($raw, true);
  if (is_array($props)) {
    foreach ($props as $p) {
      if (!empty($p['slug'])) {
        $url = $base . '/property/' . rawurlencode($p['slug']);
      } elseif (!empty($p['id'])) {
        $url = $base . '/property.php?id=' . rawurlencode($p['id']);
      } else {
        continue;
      }
      $urls[] = ['loc' => $url, 'priority' => '0.8', 'changefreq' => 'weekly'];
    }
  }
}

// Output XML
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
  <url>
    <loc><?= htmlspecialchars($u['loc'], ENT_XML1) ?></loc>
    <changefreq><?= htmlspecialchars($u['changefreq'], ENT_XML1) ?></changefreq>
    <priority><?= htmlspecialchars($u['priority'], ENT_XML1) ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
