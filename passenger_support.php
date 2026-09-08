<?php
require_once 'controller/common.php';
$user = require_role('passenger');
require_once 'model/supportModel.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    try {
        $id = open_ticket($user, post('booking'), post('category'), post('subject'), post('message'), post('rating'));
        flash('Your message was submitted. You can read replies on this page.');
        go('support_ticket.php?id=' . $id);
    } catch (Exception $ex) {
        $error = $ex instanceof mysqli_sql_exception ? 'Could not submit. Please try again.' : $ex->getMessage();
    }
}
$transport = rows('SELECT b.booking_id,s.source,s.destination FROM transport_bookings b JOIN transport_schedules s ON b.schedule_id=s.schedule_id WHERE b.user_id=? ORDER BY b.booking_id DESC', 'i', [$user['user_id']]);
$hotels = rows('SELECT b.booking_id,h.hotel_name FROM hotel_bookings b JOIN hotels h ON b.hotel_id=h.hotel_id WHERE b.user_id=? ORDER BY b.booking_id DESC', 'i', [$user['user_id']]);
$status = $_GET['status'] ?? '';
$tickets = list_tickets($user, $status);
$title = 'Help and feedback';
require 'view/header.php';
?>
<span class="eyebrow">WE ARE LISTENING</span>
<h1>Help & feedback</h1>
<p>Booking messages go to the relevant provider and admin. General messages go to admin.</p>
<?php if ($error): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>
<form method="post" class="card" data-validate>
    <?= csrf() ?>
    <div class="form-grid">
        <label>About
            <select name="booking" required>
                <option value="general">General platform issue</option>
                <?php foreach ($transport as $booking): ?>
                <option value="transport:<?= $booking['booking_id'] ?>" <?= post('booking')==='transport:'.$booking['booking_id']?'selected':'' ?>>ET-<?= $booking['booking_id'] ?> · <?= e($booking['source'].' → '.$booking['destination']) ?></option>
                <?php endforeach; ?>
                <?php foreach ($hotels as $booking): ?>
                <option value="hotel:<?= $booking['booking_id'] ?>" <?= post('booking')==='hotel:'.$booking['booking_id']?'selected':'' ?>>HT-<?= $booking['booking_id'] ?> · <?= e($booking['hotel_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Message type
            <select name="category" required><option value="complaint">Complaint</option><option value="feedback" <?= post('category')==='feedback'?'selected':'' ?>>Feedback</option></select>
        </label>
        <label>Subject <input name="subject" required minlength="3" maxlength="150" value="<?= e(post('subject')) ?>"></label>
        <label>Rating (optional; feedback only)
            <select name="rating"><option value="">No rating</option><?php for ($rating=1;$rating<=5;$rating++): ?><option value="<?= $rating ?>" <?= post('rating')===(string)$rating?'selected':'' ?>><?= $rating ?> / 5</option><?php endfor; ?></select>
        </label>
    </div>
    <label>Your message <textarea name="message" required minlength="10" maxlength="3000" rows="4"><?= e(post('message')) ?></textarea></label>
    <button class="button">Submit message</button>
</form>
<h2 class="space-top">Your conversations</h2>
<?php require 'view/ticketTable.php'; require 'view/footer.php'; ?>
