<?php
require_once 'controller/common.php';
$user = require_role(['admin', 'provider']);
require_once 'model/operationsModel.php';
$id = (int)($_GET['id'] ?? 0);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    try {
        update_seat_inventory($user, $id, post('total_seats'), $_POST['blocked'] ?? []);
        flash('Seat capacity and blocked seats updated.');
        go('manage_seats.php?id=' . $id);
    } catch (Exception $ex) {
        $error = $ex instanceof mysqli_sql_exception ? 'Could not save. Please reload and try again.' : $ex->getMessage();
    }
}
$schedule = one('SELECT * FROM transport_schedules WHERE schedule_id=?', 'i', [$id]);
if (!$schedule || ($user['role'] === 'provider' && $schedule['provider_id'] != $user['user_id'])) {
    flash('Schedule not found or access denied.');
    go(dashboard());
}
$booked = array_map('intval', array_column(rows("SELECT seat_number FROM transport_bookings WHERE schedule_id=? AND booking_status='confirmed'", 'i', [$id]), 'seat_number'));
$blocked = array_map('intval', array_column(rows('SELECT seat_number FROM blocked_seats WHERE schedule_id=?', 'i', [$id]), 'seat_number'));
$title = 'Manage seat availability';
require 'view/header.php';
?>
<a class="back" href="<?= e(dashboard()) ?>">← Back to dashboard</a>
<h1>Manage seat availability</h1>
<p><?= e($schedule['source'] . ' → ' . $schedule['destination']) ?> · <?= e($schedule['departure_time']) ?></p>
<?php if ($error): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>
<?php if ($schedule['schedule_status'] !== 'active' || strtotime($schedule['departure_time']) <= time()): ?>
<p class="notice">This schedule is cancelled or has departed. Seat changes are closed.</p>
<?php else: ?>
<form class="card" method="post" data-validate>
    <?= csrf() ?>
    <label>Total seat capacity <input type="number" name="total_seats" min="1" max="500" required value="<?= $schedule['total_seats'] ?>"></label>
    <p><?= count($booked) ?> confirmed · <?= count($blocked) ?> blocked · <?= $schedule['available_seats'] ?> available</p>
    <p>Tick an unbooked seat to block it. Untick to make it bookable. Save a larger capacity first to show new seat numbers.</p>
    <div class="inventory-grid">
    <?php for ($seat = 1; $seat <= $schedule['total_seats']; $seat++): ?>
        <label class="inventory-seat <?= in_array($seat, $booked, true) ? 'taken' : '' ?>">
            <input type="checkbox" name="blocked[]" value="<?= $seat ?>" <?= in_array($seat, $blocked, true) ? 'checked' : '' ?> <?= in_array($seat, $booked, true) ? 'disabled' : '' ?>>
            Seat <?= $seat ?> <?= in_array($seat, $booked, true) ? '(booked)' : '' ?>
        </label>
    <?php endfor; ?>
    </div>
    <button class="button">Save availability</button>
</form>
<?php endif; require 'view/footer.php'; ?>
