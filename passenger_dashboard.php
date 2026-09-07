<?php
require_once 'controller/common.php';
$user=require_role('passenger');
require_once 'model/bookingModel.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
check_csrf();
try{
cancel_booking($user['user_id'],(int)post('id'),post('kind'));
flash('Booking cancelled. Availability has been restored.');
}
catch(Exception $ex){
flash($ex instanceof mysqli_sql_exception?'Please try again.':$ex->getMessage());
}
go('passenger_dashboard.php');
}
$transport=rows('SELECT b.*,s.source,s.destination,s.departure_time,s.price,s.cancellation_reason FROM transport_bookings b JOIN transport_schedules s ON b.schedule_id=s.schedule_id WHERE b.user_id=? ORDER BY b.booked_at DESC','i',[$user['user_id']]);
$stays=rows('SELECT b.*,h.hotel_name,h.price_per_night FROM hotel_bookings b JOIN hotels h ON b.hotel_id=h.hotel_id WHERE b.user_id=? ORDER BY b.booked_at DESC','i',[$user['user_id']]);
$title='My bookings';
require 'view/header.php';
?>
<span class="eyebrow">YOUR TRAVEL SPACE</span>
<div class="section-heading">
<div>
<h1>Hello, <?= e($user['name']) ?>.</h1>
<p>Every journey and every stay, all together.</p>
</div>
<a class="button" href="index.php">Book a journey ↗</a>
</div>
<h2>Transport bookings</h2>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Booking</th>
<th>Journey</th>
<th>Departure</th>
<th>Seat</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php
 foreach($transport as $b): 
?>
<tr>
<td>ET-<?= $b['booking_id'] ?>
</td>
<td>
<?= e($b['source'].' → '.$b['destination']) ?>
<?php if($b['cancellation_reason']): ?><p class="muted">Cancelled by operator: <?= e($b['cancellation_reason']) ?></p><?php endif; ?>
</td>
<td>
<?= e($b['departure_time']) ?>
</td>
<td>
<?= e($b['seat_number']) ?>
</td>
<td>
<span class="tag">
<?= e($b['booking_status']) ?>
</span>
</td>
<td>
<?php
 if($b['booking_status']==='confirmed'&&strtotime($b['departure_time'])>time()): 
?>
<form method="post" data-confirm="Cancel this ticket?">
<?= csrf() ?>
<input type="hidden" name="id" value="<?= $b['booking_id'] ?>">
<input type="hidden" name="kind" value="transport">
<button class="danger">Cancel</button>
</form>
<?php
 else: 
?>—<?php
 endif;
?>
</td>
</tr>
<?php
 endforeach;
?>
<?php
 if(!$transport): 
?>
<tr>
<td colspan="6">No transport bookings yet. <a href="index.php">Find a journey</a>.</td>
</tr>
<?php
 endif;
?>
</tbody>
</table>
</div>
<h2>Hotel bookings</h2>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Booking</th>
<th>Hotel</th>
<th>Check-in</th>
<th>Check-out</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php
 foreach($stays as $b): 
?>
<tr>
<td>HT-<?= $b['booking_id'] ?>
</td>
<td>
<?= e($b['hotel_name']) ?>
</td>
<td>
<?= e($b['check_in']) ?>
</td>
<td>
<?= e($b['check_out']) ?>
</td>
<td>
<span class="tag">
<?= e($b['booking_status']) ?>
</span>
</td>
<td>
<?php
 if($b['booking_status']==='confirmed'&&$b['check_in']>date('Y-m-d')): 
?>
<form method="post" data-confirm="Cancel this hotel reservation?">
<?= csrf() ?>
<input type="hidden" name="id" value="<?= $b['booking_id'] ?>">
<input type="hidden" name="kind" value="hotel">
<button class="danger">Cancel</button>
</form>
<?php
 else: 
?>—<?php
 endif;
?>
</td>
</tr>
<?php
 endforeach;
?>
<?php
 if(!$stays): 
?>
<tr>
<td colspan="6">No hotel bookings yet. <a href="hotel_search.php">Find a stay</a>.</td>
</tr>
<?php
 endif;
?>
</tbody>
</table>
</div>
<p class="muted">Tickets can be cancelled before departure. Hotel reservations can be cancelled before the check-in date.</p>
<?php
require_once 'model/paymentModel.php';
$payments = payment_list('', $user['user_id']);
?>
<div class="section-heading"><h2>Payment records</h2><a class="button small" href="passenger_support.php">Send feedback / complaint</a></div>
<p class="muted">Manual payment and refund records are updated by admin. Bookings are confirmed without an online payment step.</p>
<div class="table-wrap"><table><thead><tr><th>Booking</th><th>Booked amount</th><th>Payment status</th><th>Refund status</th></tr></thead><tbody>
<?php foreach($payments as $payment): ?>
<tr><td><?= $payment['transport_booking_id']?'ET-'.$payment['transport_booking_id']:'HT-'.$payment['hotel_booking_id'] ?></td>
<td>৳ <?= number_format($payment['amount'],2) ?></td><td><?= e($payment['payment_status']) ?></td>
<td><?= $payment['payment_status']==='refunded'?'Refund recorded':($payment['payment_status']==='paid' && $payment['booking_status']==='cancelled'?'Awaiting admin refund record':'—') ?></td></tr>
<?php endforeach; ?>
<?php if(!$payments): ?><tr><td colspan="4">No payment records yet.</td></tr><?php endif; ?>
</tbody></table></div>
<?php
 require 'view/footer.php';
?>
