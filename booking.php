<?php
require_once 'controller/common.php';
 $user=require_role('passenger');
 require_once 'model/bookingModel.php';
$id=(int)($_GET['id']??0);
 $error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
check_csrf();
try{
book_transport($user['user_id'],$id,post('seat'));
flash('Your transport booking is confirmed.');
go('passenger_dashboard.php');
}
catch(Exception $ex){
$error=$ex instanceof mysqli_sql_exception?'Booking could not be completed. Please try again.':$ex->getMessage();
}
}
$s=one('SELECT * FROM transport_schedules WHERE schedule_id=?','i',[$id]);
if(!$s){
http_response_code(404);
$title='Journey not found';
require 'view/header.php';
echo '<h1>Journey not found</h1>';
require 'view/footer.php';
exit;
}
$booked=array_column(rows("SELECT seat_number FROM transport_bookings WHERE schedule_id=? AND booking_status='confirmed'",'i',[$id]),'seat_number');
$booked = array_merge($booked, array_column(rows('SELECT seat_number FROM blocked_seats WHERE schedule_id=?','i',[$id]), 'seat_number'));
$title='Choose your seat';
 require 'view/header.php';
?>
<a class="back" href="index.php">← Back to journeys</a>
<h1>Choose your seat</h1>
<div class="two-column">
<section class="card">
<span class="eyebrow">
<?= e($s['transport_type']) ?>
</span>
<h2>
<?= e($s['source']) ?> → <?= e($s['destination']) ?>
</h2>
<p>
<?= e($s['departure_time']) ?>
</p>
<p>Arrival: <?= e($s['arrival_time']) ?>
</p>
<h2>৳ <?= number_format($s['price'],2) ?> <small>per seat</small>
</h2>
<p>Select one available seat to confirm your ticket.</p>
</section>
<section class="card">
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
<h2>Seat selection</h2>
<span class="muted">Grey seats are booked or blocked</span>
</div>
<form method="post" data-validate>
<?= csrf() ?>
<div class="seat-grid">
<?php
 for($i=1;$i<=$s['total_seats'];$i++): 
?>
<label class="seat <?= in_array((string)$i,$booked)?'taken':'' ?>">
<input type="radio" name="seat" value="<?= $i ?>" required <?= in_array((string)$i,$booked)?'disabled':'' ?>>
<span>
<?= $i ?>
</span>
</label>
<?php
 endfor;
?>
</div>
<?php if($s['schedule_status']==='active' && strtotime($s['departure_time'])>time() && $s['available_seats']>0): ?>
<button class="button wide">Confirm booking →</button>
<?php else: ?><p class="notice error">This journey is unavailable. <?= e($s['cancellation_reason']??'') ?></p><?php endif; ?>
</form>
</section>
</div>
<?php
 require 'view/footer.php';
?>
