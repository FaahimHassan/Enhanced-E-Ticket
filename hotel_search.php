<?php
require_once 'controller/common.php';
 $location=trim($_GET['location']??'');
$min=$_GET['min']??'0';
$max=$_GET['max']??'100000';
$error='';
$hotels=[];
if(!is_numeric($min)||!is_numeric($max)||$min<0||$max<$min||strlen($location)>150)$error='Please enter a valid location and price range.';
else $hotels=rows("SELECT h.* FROM hotels h JOIN users u ON h.provider_id=u.user_id WHERE h.location LIKE ? AND h.price_per_night BETWEEN ? AND ? AND u.status='active' ORDER BY h.price_per_night",'sdd',['%'.$location.'%',$min,$max]);
$title='Find your stay';
 require 'view/header.php';
?>
<span class="eyebrow">ROOM TO UNWIND</span>
<h1>A good day ends with a great stay.</h1>
<p>Browse hotel rooms across Bangladesh.</p>
<form class="card search-grid" method="get" data-validate>
<label>Location<input name="location" value="<?= e($location) ?>" placeholder="Any destination" maxlength="150">
</label>
<label>Minimum price (৳)<input type="number" name="min" min="0" step="0.01" value="<?= e($min) ?>" required>
</label>
<label>Maximum price (৳)<input type="number" name="max" min="0" step="0.01" value="<?= e($max) ?>" required>
</label>
<button class="button">Search stays ↗</button>
</form>
<?php
 if($error): 
?>
<p class="notice error">
<?= e($error) ?>
</p>
<?php
 endif;
?>
<div class="section-heading">
<h2>Places to stay</h2>
<span>
<?= count($hotels) ?> results</span>
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
<p>
<?= e($h['description']) ?>
</p>
<p>
<?= e($h['available_rooms']) ?> rooms available</p>
<div class="section-heading">
<strong>৳ <?= number_format($h['price_per_night']) ?> <small>/ night</small>
</strong>
<a class="button" href="hotel_booking.php?id=<?= $h['hotel_id'] ?>">View room →</a>
</div>
</article>
<?php
 endforeach;
?>
</div>
<?php
 if(!$hotels&&!$error): 
?>
<div class="card">No hotels match your filters. Try another location or price range.</div>
<?php
 endif;
?>
<?php
 require 'view/footer.php';
?>
