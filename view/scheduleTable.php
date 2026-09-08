<div class="table-wrap">
<table>
<thead>
<tr>
<th>Route</th>
<th>Type</th>
<th>Departure</th>
<th>Price</th>
<th>Seats</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php
 foreach($schedules as $s): 
?>
<tr>
<td>
<?= e($s['source'].' → '.$s['destination']) ?><br><span class="tag"><?= e($s['schedule_status']) ?></span>
</td>
<td>
<?= e(ucfirst($s['transport_type'])) ?>
</td>
<td>
<?= e($s['departure_time']) ?>
</td>
<td>৳ <?= number_format($s['price']) ?>
</td>
<td>
<?= e($s['available_seats'].' / '.$s['total_seats']) ?>
</td>
<td>
<div class="actions wrap-actions">
<?php if($s['schedule_status']==='active' && strtotime($s['departure_time'])>time()): ?>
<a href="manage_seats.php?id=<?= $s['schedule_id'] ?>">Manage seats</a>
<a href="cancel_schedule.php?id=<?= $s['schedule_id'] ?>">Cancel schedule</a>
<?php endif; ?>
<a href="<?= $user['role']==='admin'?'admin_manage_schedules.php':'provider_add_schedule.php' ?>?id=<?= $s['schedule_id'] ?>">Edit</a>
<form method="post" data-confirm="Delete this schedule? Booked schedules cannot be deleted.">
<?= csrf() ?>
<input type="hidden" name="action" value="delete">
<input type="hidden" name="kind" value="schedule">
<input type="hidden" name="id" value="<?= $s['schedule_id'] ?>">
<button class="danger">Delete</button>
</form>
</div>
</td>
</tr>
<?php
 endforeach;
?>
<?php
 if(!$schedules): 
?>
<tr>
<td colspan="6">No schedules added yet.</td>
</tr>
<?php
 endif;
?>
</tbody>
</table>
</div>
