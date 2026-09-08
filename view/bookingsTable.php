<?php foreach (['transport', 'hotel'] as $kind): ?>
<h2><?= $kind === 'transport' ? 'Transport bookings' : 'Hotel bookings' ?></h2>
<div class="table-wrap"><table>
<thead><tr><th>Booking</th><th>Passenger</th><th>Journey / hotel</th><th>Dates</th><th>Seat</th><th>Status</th><?php if ($user['role']==='admin'): ?><th>Action</th><?php endif; ?></tr></thead>
<tbody>
<?php $items = $kind === 'transport' ? $transport : $stays; ?>
<?php foreach ($items as $booking): ?>
<tr>
    <td><?= $kind === 'transport' ? 'ET-' : 'HT-' ?><?= $booking['booking_id'] ?></td>
    <td><?= e($booking['name']) ?></td>
    <td><?= e($kind === 'transport' ? $booking['source'].' → '.$booking['destination'] : $booking['hotel_name']) ?></td>
    <td><?= e($kind === 'transport' ? $booking['departure_time'] : $booking['check_in'].' → '.$booking['check_out']) ?></td>
    <td><?= $kind === 'transport' ? e($booking['seat_number']) : '—' ?></td>
    <td><span class="tag"><?= e($booking['booking_status']) ?></span></td>
    <?php if ($user['role']==='admin'): ?>
    <td>
        <?php $allowed = $booking['booking_status']==='confirmed' && ($kind==='transport' ? strtotime($booking['departure_time'])>time() : $booking['check_in']>date('Y-m-d')); ?>
        <?php if ($allowed): ?>
        <form method="post" data-confirm="Cancel this booking? Its available seat or room will be restored.">
            <?= csrf() ?><input type="hidden" name="id" value="<?= $booking['booking_id'] ?>"><input type="hidden" name="kind" value="<?= $kind ?>">
            <button class="danger">Cancel booking</button>
        </form>
        <?php else: ?>—<?php endif; ?>
    </td>
    <?php endif; ?>
</tr>
<?php endforeach; ?>
<?php if (!$items): ?><tr><td colspan="<?= $user['role']==='admin'?7:6 ?>">No bookings yet.</td></tr><?php endif; ?>
</tbody></table></div>
<?php endforeach; ?>
