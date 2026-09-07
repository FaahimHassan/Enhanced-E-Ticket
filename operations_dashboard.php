<?php
require_once 'controller/common.php';
$user = require_role(['admin', 'provider']);
$title = 'Live booking overview';
require 'view/header.php';
require $user['role'] === 'admin' ? 'view/adminNav.php' : 'view/providerNav.php';
?>
<div class="section-heading"><div><span class="eyebrow"><?= $user['role']==='admin'?'PLATFORM':'YOUR SERVICES' ?></span><h1>Live booking overview</h1></div><button class="button" id="refresh-overview">Refresh now</button></div>
<p>Updates every 10 seconds while this tab is visible. Ticket counts represent confirmed reservations, not payments received.</p>
<p id="overview-status" role="status">Loading the latest figures…</p>
<div class="stats" id="overview-stats"></div>
<h2>Transport schedules & seats</h2>
<div class="table-wrap"><table><thead><tr><th>Route</th><th>Departure</th><th>Status</th><th>Confirmed</th><th>Blocked</th><th>Available / total</th><th>Manage</th></tr></thead><tbody id="overview-schedules"></tbody></table></div>
<h2>Hotel room availability</h2>
<div class="table-wrap"><table><thead><tr><th>Hotel</th><th>Location</th><th>Available / total rooms</th></tr></thead><tbody id="overview-hotels"></tbody></table></div>
<script src="assets/js/overview.js" defer></script>
<?php require 'view/footer.php'; ?>
