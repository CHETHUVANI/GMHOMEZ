<?php
$TITLE='User Agreement';
require_once __DIR__.'/config.php';
if (file_exists(__DIR__.'/lib/render.php')) { require_once __DIR__.'/lib/render.php'; }

function page_top($title){ if (function_exists('render_header')) { render_header($title); } else { if (file_exists(__DIR__.'/partials/header.php')) include __DIR__.'/partials/header.php'; echo '<main class="container mx-auto px-4 py-8">'; } }
function page_bottom(){ if (function_exists('render_footer')) { render_footer(); } else { echo '</main>'; if (file_exists(__DIR__.'/partials/footer.php')) include __DIR__.'/partials/footer.php'; } }
page_top($TITLE);
?>
<h1 class="text-3xl font-bold mb-4">User Agreement</h1>
<p class="mb-4">By using GM HOMEZ, you agree to the terms below.</p>

<h2 class="text-xl font-semibold mt-6 mb-2">Use of the Website</h2>
<p>You will not misuse the site, attempt unauthorized access, or post misleading information.</p>

<h2 class="text-xl font-semibold mt-6 mb-2">Listings & Content</h2>
<p>Project data is for guidance only; verify details with the builder and RERA documentation.</p>

<h2 class="text-xl font-semibold mt-6 mb-2">Liability</h2>
<p>GM HOMEZ isn’t responsible for third-party content, outages, or decisions based on listed data.</p>

<h2 class="text-xl font-semibold mt-6 mb-2">Contact</h2>
<p>Questions? Write to <a href="mailto:legal@gmhomez.in">legal@gmhomez.in</a>.</p>
<?php page_bottom(); ?>
