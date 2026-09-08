<?php
require_once 'controller/common.php';
$user = require_role('admin');
require_once 'model/paymentModel.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    try {
        update_payment_record($user, (int)post('id'), post('action'), post('note'));
        flash('Manual payment record updated. No money was transferred by this application.');
        go('admin_payments.php');
    } catch (Exception $ex) {
        $error = $ex instanceof mysqli_sql_exception ? 'Could not update. Please try again.' : $ex->getMessage();
    }
}
$status = $_GET['status'] ?? '';
$payments = payment_list($status);
$totals = one("SELECT COALESCE(SUM(CASE WHEN payment_status IN ('paid','refunded') THEN amount ELSE 0 END),0) AS received,COALESCE(SUM(CASE WHEN payment_status='refunded' THEN amount ELSE 0 END),0) AS refunded FROM payment_records");
$title = 'Payments and refunds';
require 'view/header.php';
require 'view/adminNav.php';
?>
<h1>Payments & refunds</h1>
<p class="notice">Manual records only. Record a payment after it was received outside this application. Record a refund after it was returned outside this application. These buttons do not transfer money.</p>
<?php if ($error): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>
<div class="stats">
    <article class="card"><span>Recorded payments received</span><h2>৳ <?= number_format($totals['received'], 2) ?></h2></article>
    <article class="card"><span>Recorded refunds</span><h2>৳ <?= number_format($totals['refunded'], 2) ?></h2></article>
    <article class="card"><span>Recorded net amount</span><h2>৳ <?= number_format($totals['received']-$totals['refunded'], 2) ?></h2></article>
</div>
<form class="filter-row" method="get">
    <label>Filter<select name="status"><option value="">All records</option>
    <?php foreach (['unpaid'=>'Unpaid','paid'=>'Paid','refunded'=>'Refunded','refund_due'=>'Paid cancellations — refund due'] as $key=>$label): ?>
    <option value="<?= $key ?>" <?= $status===$key?'selected':'' ?>><?= e($label) ?></option>
    <?php endforeach; ?></select></label><button class="button small">Apply filter</button>
</form>
<div class="table-wrap"><table>
<thead><tr><th>Booking / passenger</th><th>Amount</th><th>Booking status</th><th>Payment status</th><th>Record history</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($payments as $payment): ?>
<tr>
    <td><?= $payment['transport_booking_id']?'ET-'.$payment['transport_booking_id']:'HT-'.$payment['hotel_booking_id'] ?><br><?= e($payment['passenger_name']) ?><br><small><?= e($payment['service_name']) ?></small></td>
    <td>৳ <?= number_format($payment['amount'], 2) ?></td>
    <td><?= e($payment['booking_status']) ?></td>
    <td><span class="tag"><?= e($payment['payment_status']) ?></span></td>
    <td class="wrap-cell">
        <?php if ($payment['paid_at']): ?>Paid: <?= e($payment['paid_at']) ?> by <?= e($payment['paid_admin']) ?><br><?= e($payment['payment_note']) ?><br><?php endif; ?>
        <?php if ($payment['refunded_at']): ?>Refunded: <?= e($payment['refunded_at']) ?> by <?= e($payment['refunded_admin']) ?><br><?= e($payment['refund_note']) ?><?php endif; ?>
        <?php if (!$payment['paid_at']): ?>No payment recorded.<?php endif; ?>
    </td>
    <td>
    <?php $action = $payment['payment_status']==='unpaid' && $payment['booking_status']==='confirmed' ? 'paid' : ($payment['payment_status']==='paid' && $payment['booking_status']==='cancelled' ? 'refunded' : ''); ?>
    <?php if ($action): ?>
        <form method="post" data-validate data-confirm="Confirm this manual record? Only proceed if the payment or refund happened outside this application.">
            <?= csrf() ?><input type="hidden" name="id" value="<?= $payment['payment_id'] ?>"><input type="hidden" name="action" value="<?= $action ?>">
            <label>Reference / note <input name="note" required minlength="3" maxlength="500" placeholder="Receipt or reference"></label>
            <button class="<?= $action==='paid'?'button small':'danger' ?>"><?= $action==='paid'?'Record payment received':'Record full refund' ?></button>
        </form>
    <?php else: ?>No action available<?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
<?php if (!$payments): ?><tr><td colspan="6">No payment records match your filter.</td></tr><?php endif; ?>
</tbody></table></div>
<?php require 'view/footer.php'; ?>
