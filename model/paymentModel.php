<?php
require_once __DIR__ . '/dbModel.php';

// Record a full manual payment/refund. No bank, card or payment API is called.
function update_payment_record($user, $id, $action, $note) {
    global $conn;
    if ($user['role'] !== 'admin') throw new Exception('Only admins can update payment records.');
    if (!in_array($action, ['paid', 'refunded'], true) || strlen($note) < 3 || strlen($note) > 500) {
        throw new Exception('Enter a reference or note between 3 and 500 characters.');
    }
    mysqli_begin_transaction($conn);
    try {
        $ref = one('SELECT transport_booking_id,hotel_booking_id FROM payment_records WHERE payment_id=?', 'i', [$id]);
        if (!$ref) throw new Exception('Payment record not found.');
        // Lock the booking before its payment so cancellations cannot race payment recording.
        if ($ref['transport_booking_id']) {
            $booking = one('SELECT booking_status FROM transport_bookings WHERE booking_id=? FOR UPDATE', 'i', [$ref['transport_booking_id']]);
        } else {
            $booking = one('SELECT booking_status FROM hotel_bookings WHERE booking_id=? FOR UPDATE', 'i', [$ref['hotel_booking_id']]);
        }
        $payment = one('SELECT * FROM payment_records WHERE payment_id=? FOR UPDATE', 'i', [$id]);
        if (!$booking) throw new Exception('Booking not found.');
        if ($action === 'paid') {
            if ($payment['payment_status'] !== 'unpaid' || $booking['booking_status'] !== 'confirmed') {
                throw new Exception('Only an unpaid confirmed booking can be marked paid.');
            }
            query("UPDATE payment_records SET payment_status='paid',payment_note=?,paid_by=?,paid_at=NOW() WHERE payment_id=?", 'sii', [$note, $user['user_id'], $id]);
        } else {
            if ($payment['payment_status'] !== 'paid' || $booking['booking_status'] !== 'cancelled') {
                throw new Exception('Only a paid cancelled booking can have a refund recorded, once.');
            }
            query("UPDATE payment_records SET payment_status='refunded',refund_note=?,refunded_by=?,refunded_at=NOW() WHERE payment_id=?", 'sii', [$note, $user['user_id'], $id]);
        }
        mysqli_commit($conn);
    } catch (Throwable $error) {
        mysqli_rollback($conn);
        throw $error;
    }
}

function payment_list($status = '', $passenger_id = 0) {
    $sql = "SELECT p.*,COALESCE(tb.user_id,hb.user_id) AS passenger_id,
        COALESCE(tu.name,hu.name) AS passenger_name,
        COALESCE(tb.booking_status,hb.booking_status) AS booking_status,
        CASE WHEN p.transport_booking_id IS NOT NULL THEN CONCAT(s.source,' → ',s.destination) ELSE h.hotel_name END AS service_name,
        paid.name AS paid_admin, refunded.name AS refunded_admin
        FROM payment_records p
        LEFT JOIN transport_bookings tb ON p.transport_booking_id=tb.booking_id
        LEFT JOIN hotel_bookings hb ON p.hotel_booking_id=hb.booking_id
        LEFT JOIN transport_schedules s ON tb.schedule_id=s.schedule_id
        LEFT JOIN hotels h ON hb.hotel_id=h.hotel_id
        LEFT JOIN users tu ON tb.user_id=tu.user_id
        LEFT JOIN users hu ON hb.user_id=hu.user_id
        LEFT JOIN users paid ON p.paid_by=paid.user_id
        LEFT JOIN users refunded ON p.refunded_by=refunded.user_id
        WHERE 1=1";
    $types = '';
    $values = [];
    if (in_array($status, ['unpaid', 'paid', 'refunded'], true)) {
        $sql .= ' AND p.payment_status=?';
        $types .= 's';
        $values[] = $status;
    } elseif ($status === 'refund_due') {
        $sql .= " AND p.payment_status='paid' AND COALESCE(tb.booking_status,hb.booking_status)='cancelled'";
    }
    if ($passenger_id) {
        $sql .= ' AND COALESCE(tb.user_id,hb.user_id)=?';
        $types .= 'i';
        $values[] = $passenger_id;
    }
    return rows($sql . ' ORDER BY p.payment_id DESC', $types, $values);
}
