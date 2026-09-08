<?php
require_once 'controller/common.php';
$user=require_role('passenger');
require_once 'model/bookingModel.php';
$id=(int)($_GET['id']??0);
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
check_csrf();
try{
book_hotel($user['user_id'],$id,post('check_in'),post('check_out'));
flash('Your hotel booking is confirmed.');
go('passenger_dashboard.php');
}
catch(Exception $ex){
$error=$ex instanceof mysqli_sql_exception?'Booking could not be completed. Please try again.':$ex->getMessage();
}
}
$h=one('SELECT * FROM hotels WHERE hotel_id=?','i',[$id]);
$title='Book a room';
require 'view/header.php';
if(!$h){
echo '<h1>Hotel not found</h1>';
require 'view/footer.php';
exit;
}
?>
<a class="back" href="hotel_search.php">← Back to stays</a>
<h1>Make yourself at home.</h1>
<div class="two-column">
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
<h2>৳ <?= number_format($h['price_per_night'],2) ?> <small>/ night</small>
</h2>
<p>
<?= e($h['available_rooms']) ?> rooms available</p>
</article>
<form method="post" class="card" data-validate>
<h2>Your stay</h2>
<?= csrf() ?>
<?php
 if($error): 
?>
<p class="notice error">
<?= e($error) ?>
</p>
<?php
 endif;
?>
<label>Check-in<input type="date" name="check_in" min="<?= date('Y-m-d') ?>" value="<?= e(post('check_in')) ?>" required>
</label>
<label>Check-out<input type="date" name="check_out" min="<?= date('Y-m-d',strtotime('+1 day')) ?>" value="<?= e(post('check_out')) ?>" required>
</label>
<p>One room per booking. Your reservation is confirmed immediately.</p>
<p class="form-error" role="alert">
</p>
<button class="button wide">Confirm reservation →</button>
</form>
</div>
<?php
 require 'view/footer.php';
?>
