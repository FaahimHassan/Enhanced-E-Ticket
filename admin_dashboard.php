<?php
 require_once 'controller/common.php';
$user=require_role('admin');
$title='Admin dashboard';
require 'view/header.php';
require 'view/adminNav.php';
?>
<span class="eyebrow">PLATFORM OVERVIEW</span>
<h1>Welcome to your control centre.</h1>
<div class="stats">
<?php
 $counts=['Registered users'=>one('SELECT COUNT(*) AS n FROM users')['n'],'Pending providers'=>one("SELECT COUNT(*) AS n FROM users WHERE role='provider' AND status='pending'")['n'],'Transport bookings'=>one('SELECT COUNT(*) AS n FROM transport_bookings')['n'],'Hotel bookings'=>one('SELECT COUNT(*) AS n FROM hotel_bookings')['n']];
foreach($counts as $label=>$count): 
?>
<article class="card">
<span>
<?= $label ?>
</span>
<h2>
<?= $count ?>
</h2>
</article>
<?php
 endforeach;
?>
</div>
<div class="two-column">
<article class="card">
<h2>Service providers</h2>
<p>Review applications and approve providers to list their services.</p>
<a class="button" href="admin_manage_providers.php">Review providers →</a>
</article>
<article class="card">
<h2>Transport schedules</h2>
<p>Keep routes, times and available journeys organized.</p>
<a class="button" href="admin_manage_schedules.php">Manage schedules →</a>
</article>
</div>
<div class="two-column">
<article class="card"><h2>Customer conversations</h2><p>Read complaints and feedback, reply to passengers, and resolve issues.</p><a class="button" href="admin_complaints.php">Open inbox →</a></article>
<article class="card"><h2>Payments & refunds</h2><p>Monitor manual payment records and record refunds for paid cancellations.</p><a class="button" href="admin_payments.php">Open payment records →</a></article>
<article class="card"><h2>Live booking overview</h2><p>See booking counts and seat availability with automatic refresh.</p><a class="button" href="operations_dashboard.php">View live overview →</a></article>
<article class="card"><h2>All bookings</h2><p>Review passenger reservations and cancel eligible bookings.</p><a class="button" href="admin_view_bookings.php">Manage bookings →</a></article>
</div>
<?php
 require 'view/footer.php';
?>
