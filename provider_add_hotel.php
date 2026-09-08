<?php
require_once 'controller/common.php';
$user=require_role('provider');
$kind='hotel';
require 'controller/serviceController.php';
$record=$id?one('SELECT * FROM hotels WHERE hotel_id=? AND provider_id=?','ii',[$id,$user['user_id']]):[];
if($id&&!$record){
flash('Hotel not found.');
go('provider_dashboard.php');
}
if($_SERVER['REQUEST_METHOD']==='POST')$record=$_POST;
$title='Manage hotel';
require 'view/header.php';
require 'view/providerNav.php';
?>
<h1>
<?= $id?'Edit hotel listing':'Add a hotel listing' ?>
</h1>
<?php
 if($error): 
?>
<p class="notice error">
<?= e($error) ?>
</p>
<?php
 endif;
?>
<form class="card" method="post" data-validate>
<?= csrf() ?>
<div class="form-grid">
<?php
 foreach(['hotel_name'=>'Hotel name','location'=>'Location','price_per_night'=>'Price per night (৳)','total_rooms'=>'Total rooms'] as $key=>$label): 
?>
<label>
<?= $label ?>
<input name="<?= $key ?>" type="<?= in_array($key,['total_rooms','price_per_night'])?'number':'text' ?>" value="<?= e($record[$key]??'') ?>" required <?= $key==='price_per_night'?'min="0.01" max="99999999" step="0.01"':($key==='total_rooms'?'min="1" max="1000"':'maxlength="150"') ?>>
</label>
<?php
 endforeach;
?>
</div>
<label>Description<textarea name="description" maxlength="5000" rows="4">
<?= e($record['description']??'') ?>
</textarea>
</label>
<p class="muted">Available rooms are calculated from total rooms minus confirmed reservations.</p>
<button class="button">Save hotel</button> <a href="provider_dashboard.php">Back to dashboard</a>
</form>
<?php
 require 'view/footer.php';
?>
