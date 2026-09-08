<?php
require_once 'controller/common.php';
$user = require_role(['admin', 'provider']);
require_once 'model/operationsModel.php';
$id = (int)($_GET['id'] ?? 0);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    try {
        cancel_schedule($user, $id, post('reason'));
        flash('Schedule and its confirmed tickets were cancelled. Paid bookings are now eligible for admin refund recording.');
        go(dashboard());
    } catch (Exception $ex) {
        $error = $ex instanceof mysqli_sql_exception ? 'Could not cancel. Please reload and try again.' : $ex->getMessage();
    }
}
$schedule = one('SELECT * FROM transport_schedules WHERE schedule_id=?', 'i', [$id]);
if (!$schedule || ($user['role'] === 'provider' && $schedule['provider_id'] != $user['user_id'])) {
    flash('Schedule not found or access denied.');
    go(dashboard());
}
$count = one("SELECT COUNT(*) AS n FROM transport_bookings WHERE schedule_id=? AND booking_status='confirmed'", 'i', [$id])['n'];
$title = 'Cancel schedule';
require 'view/header.php';
?>
<h1>Cancel this schedule</h1>
<?php if ($error): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>
<section class="card">
    <h2><?= e($schedule['source'] . ' → ' . $schedule['destination']) ?></h2>
    <p><?= e($schedule['departure_time']) ?> · <?= e($schedule['schedule_status']) ?></p>
    <?php if ($schedule['schedule_status'] === 'active' && strtotime($schedule['departure_time']) > time()): ?>
    <p>This will cancel <?= $count ?> confirmed ticket(s) and close this journey to new bookings. Booking history is kept.</p>
    <form method="post" data-validate data-confirm="Cancel this schedule and all its confirmed tickets? This cannot be undone.">
        <?= csrf() ?>
        <label>Reason <textarea name="reason" required minlength="5" maxlength="500" rows="4"><?= e(post('reason')) ?></textarea></label>
        <button class="danger">Cancel schedule and tickets</button>
        <a href="<?= e(dashboard()) ?>">Go back</a>
    </form>
    <?php else: ?><p><?= e($schedule['cancellation_reason'] ?? 'This schedule has already departed.') ?></p><?php endif; ?>
</section>
<?php require 'view/footer.php'; ?>
