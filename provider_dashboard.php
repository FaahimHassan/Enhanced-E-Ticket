<?php
require_once 'controller/common.php';
$user=require_role('provider');
$kind='schedule';
require 'controller/serviceController.php';
$schedules=rows('SELECT * FROM transport_schedules WHERE provider_id=? ORDER BY departure_time DESC','i',[$user['user_id']]);
$hotels=rows('SELECT * FROM hotels WHERE provider_id=?','i',[$user['user_id']]);
$title='Provider dashboard';
require 'view/header.php';
require 'view/providerNav.php';
?>
<span class="eyebrow">SERVICE PROVIDER</span>
<div class="section-heading">
<div>
<h1>Your services, all together.</h1>
<p>Welcome, <?= e($user['name']) ?>.</p>
</div>
<a class="button" href="provider_bookings.php">View bookings →</a>
</div>
<?php
 if($error): 
?>
<p class="notice error">
<?= e($error) ?>
</p>
<?php
 endif;
?>
<div class="stats">
<div class="card">
<span>Transport schedules</span>
<h2>
<?= count($schedules) ?>
</h2>
</div>
<div class="card">
<span>Hotel listings</span>
<h2>
<?= count($hotels) ?>
</h2>
</div>
</div>
<div class="section-heading">
<h2>Transport schedules</h2>
<a class="button" href="provider_add_schedule.php">+ Add schedule</a>
</div>
<?php
 require 'view/scheduleTable.php';
?>
<div class="section-heading">
<h2>Your hotels</h2>
<a class="button" href="provider_add_hotel.php">+ Add hotel</a>
</div>
<div class="hotel-grid">
<?php
 foreach($hotels as $h): 
?>
<article class="card">
<span class="tag">
<?= e($h['location']) ?>
</span>
<h2>
<?= e($h['hotel_name']) ?>
</h2>
<p>৳ <?= number_format($h['price_per_night']) ?> / night · <?= $h['available_rooms'] ?> rooms available</p>
<div class="actions">
<a href="provider_add_hotel.php?id=<?= $h['hotel_id'] ?>">Edit listing</a>
<form method="post" data-confirm="Delete this hotel? Listings with booking history cannot be deleted.">
<?= csrf() ?>
<input type="hidden" name="action" value="delete">
<input type="hidden" name="kind" value="hotel">
<input type="hidden" name="id" value="<?= $h['hotel_id'] ?>">
<button class="danger">Delete</button>
</form>
</div>
</article>
<?php
 endforeach;
?>
<?php
 if(!$hotels): 
?>
<p class="card">No hotel listings yet.</p>
<?php
 endif;
?>
</div>
<?php
 require 'view/footer.php';
?>
