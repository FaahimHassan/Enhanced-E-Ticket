<form class="card" method="post" data-validate>
<h2>
<?= $id?'Edit schedule':'Add a schedule' ?>
</h2>
<?= csrf() ?>
<div class="form-grid">
<label>Transport type<select name="transport_type">
<?php
 foreach(['bus','train','air'] as $type): 
?>
<option value="<?= $type ?>" <?= ($record['transport_type']??'bus')===$type?'selected':'' ?>>
<?= ucfirst($type) ?>
</option>
<?php
 endforeach;
?>
</select>
</label>
<?php
 foreach(['source'=>'Departure city','destination'=>'Destination city','departure_time'=>'Departure time','arrival_time'=>'Arrival time','price'=>'Price (৳)','total_seats'=>'Seat capacity'] as $key=>$label): 
?>
<label>
<?= $label ?>
<input name="<?= $key ?>" type="<?= strpos($key,'time')!==false?'datetime-local':(in_array($key,['price','total_seats'])?'number':'text') ?>" value="<?= e(str_replace(' ','T',in_array($key,['departure_time','arrival_time'])?($record[$key]??''):'') ?: ($record[$key]??'')) ?>" required <?= $key==='price'?'min="0.01" max="99999999" step="0.01"':($key==='total_seats'?'min="1" max="500"':'') ?> <?= in_array($key,['source','destination'])?'maxlength="100"':'' ?>>
</label>
<?php
 endforeach;
?>
</div>
<p class="form-error" role="alert">
</p>
<button class="button">Save schedule</button> <a href="<?= $user['role']==='admin'?'admin_manage_schedules.php':'provider_dashboard.php' ?>">Back to schedules</a>
</form>
