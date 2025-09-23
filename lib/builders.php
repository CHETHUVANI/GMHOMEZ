<?php
// lib/builders.php
if (!defined('GM_BUILDERS_LIB')) {
  define('GM_BUILDERS_LIB', 1);
  if (session_status() === PHP_SESSION_NONE) session_start();

  // Map slugs to display names
  $GM_BUILDER_NAMES = [
    'prestige-group' => 'Prestige Group',
    'sobha-limited' => 'Sobha Limited',
    'kolte-patil-developers' => 'Kolte Patil Developers',
    'godrej-properties' => 'Godrej Properties',
    'brigade-group' => 'Brigade Group',
  ];

  function gm_builder_data_dir() {
    $dir = __DIR__ . '/../data/builders';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    return $dir;
  }

  function gm_builder_json_path(string $slug): string {
    return gm_builder_data_dir() . '/' . $slug . '.json';
  }

  function gm_builder_read(string $slug): array {
    $path = gm_builder_json_path($slug);
    if (!file_exists($path)) return gm_builder_default($slug);
    $json = file_get_contents($path) ?: '';
    $data = json_decode($json, true);
    if (!is_array($data)) $data = [];
    return array_replace_recursive(gm_builder_default($slug), $data);
  }

  function gm_builder_write(string $slug, array $data): bool {
    $path = gm_builder_json_path($slug);
    $tmp  = $path . '.tmp';
    $ok = (bool)file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    if ($ok) {
      $ok = @rename($tmp, $path);
      if (!$ok) $ok = (bool)file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }
    return $ok;
  }

  function gm_is_admin(): bool {
    // Reuse existing site admin session
    return !empty($_SESSION['admin']);
  }

  function gm_require_admin() {
    if (!gm_is_admin()) {
      $next = urlencode($_SERVER['REQUEST_URI'] ?? '/');
      header('Location: /admin/login.php?next=' . $next);
      exit;
    }
  }

  function gm_builder_default(string $slug): array {
    global $GM_BUILDER_NAMES;
    return [
      'slug' => $slug,
      'name' => $GM_BUILDER_NAMES[$slug] ?? ucfirst(str_replace('-', ' ', $slug)),
      'last_updated' => date('Y-m-d'),
      'stats' => [
        'total_experience' => '—',
        'total_projects' => 0,
        'ongoing_projects' => 0,
      ],
      'overview' => [
        'city' => '',
        'location' => '',
        'about' => '',
        'rera_id' => '',
        'banks_supported' => [],
        'salient_features' => [],
        'key_facts' => [
          'launch' => '',
          'possession' => '',
        ],
      ],
      'floor_plans' => [
        // each: {"bhk":"2 BHK","carpet":"1100 sq.ft","price":"₹1.2 Cr","image":"/uploads/builders/'.$slug.'/2bhk-a.png"}
      ],
      'amenities' => [],
      'gallery' => [
        'images' => [],
        'video_url' => ''
      ],
      'home_loan' => [
        'loan_amount' => '5000000',
        'interest_rate' => '8.5',
        'tenure_years' => '20'
      ],
      'map' => [
        'lat' => '',
        'lng' => ''
      ],
      'faqs' => []
    ];
  }
}
