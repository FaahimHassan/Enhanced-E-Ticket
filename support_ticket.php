<?php
require_once 'controller/common.php';
$user = require_role(['passenger', 'provider', 'admin']);
require_once 'model/supportModel.php';
$id = (int)($_GET['id'] ?? 0);
$error = '';
$ticket = one('SELECT t.*,u.name AS passenger_name,p.name AS provider_name FROM support_tickets t JOIN users u ON t.user_id=u.user_id LEFT JOIN users p ON t.provider_id=p.user_id WHERE t.ticket_id=?', 'i', [$id]);
if (!can_read_ticket($user, $ticket)) {
    flash('Ticket not found or access denied.');
    go(dashboard());
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    try {
        if (post('action') === 'status') set_ticket_status($user, $id, post('status'));
        elseif (post('action') === 'reply') reply_to_ticket($user, $id, post('message'));
        else throw new Exception('Invalid action.');
        flash('Conversation updated.');
        go('support_ticket.php?id=' . $id);
    } catch (Exception $ex) {
        $error = $ex instanceof mysqli_sql_exception ? 'Could not save. Please try again.' : $ex->getMessage();
    }
}
$replies = rows('SELECT r.*,u.name,u.role FROM support_replies r JOIN users u ON r.user_id=u.user_id WHERE r.ticket_id=? ORDER BY r.reply_id', 'i', [$id]);
$back = $user['role']==='admin' ? 'admin_complaints.php' : ($user['role']==='provider' ? 'provider_feedback.php' : 'passenger_support.php');
$title = 'Conversation #' . $id;
require 'view/header.php';
?>
<a class="back" href="<?= $back ?>">← Back to conversations</a>
<div class="section-heading"><h1><?= e($ticket['subject']) ?></h1><span class="tag"><?= e($ticket['status']) ?></span></div>
<?php if ($error): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>
<article class="card message-card">
    <div class="section-heading"><strong><?= e($ticket['passenger_name']) ?></strong><small><?= e($ticket['created_at']) ?></small></div>
    <p><?= e(ucfirst($ticket['category'])) ?> · <?= e($ticket['provider_name'] ?? 'Platform admin') ?>
    <?php if ($ticket['transport_booking_id']): ?> · ET-<?= $ticket['transport_booking_id'] ?><?php endif; ?>
    <?php if ($ticket['hotel_booking_id']): ?> · HT-<?= $ticket['hotel_booking_id'] ?><?php endif; ?>
    <?php if ($ticket['rating']): ?> · Rating <?= $ticket['rating'] ?>/5<?php endif; ?></p>
    <div class="message-body"><?= nl2br(e($ticket['message'])) ?></div>
</article>
<?php foreach ($replies as $reply): ?>
<article class="card message-card">
    <div class="section-heading"><strong><?= e($reply['name']) ?> <span class="tag"><?= e($reply['role']) ?></span></strong><small><?= e($reply['created_at']) ?></small></div>
    <div class="message-body"><?= nl2br(e($reply['message'])) ?></div>
</article>
<?php endforeach; ?>
<form method="post" class="card" data-validate>
    <?= csrf() ?><input type="hidden" name="action" value="reply">
    <label>Your reply <textarea name="message" rows="4" required minlength="2" maxlength="3000"><?= e(post('message')) ?></textarea></label>
    <?php if($user['role']==='passenger'): ?><p>A follow-up reopens a resolved conversation.</p><?php endif; ?>
    <button class="button">Send reply</button>
</form>
<?php if (in_array($user['role'], ['admin', 'provider'], true)): ?>
<form method="post" class="space-top">
    <?= csrf() ?><input type="hidden" name="action" value="status">
    <input type="hidden" name="status" value="<?= $ticket['status']==='open'?'resolved':'open' ?>">
    <button class="button"><?= $ticket['status']==='open'?'Mark resolved':'Reopen ticket' ?></button>
</form>
<?php endif; require 'view/footer.php'; ?>
