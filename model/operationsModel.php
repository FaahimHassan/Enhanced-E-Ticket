<?php
require_once __DIR__ . '/dbModel.php';

// Call inside a transaction. Every seat-changing action first locks this same row.
function lock_schedule_for_manager($user, $id) {
    $schedule = one('SELECT * FROM transport_schedules WHERE schedule_id=? FOR UPDATE', 'i', [$id]);
    if (!$schedule || !in_array($user['role'], ['admin', 'provider'], true)) {
        throw new Exception('Schedule not found or access denied.');
    }
    if ($user['role'] === 'provider' && $schedule['provider_id'] != $user['user_id']) {
        throw new Exception('You can only manage your own schedules.');
    }
    return $schedule;
}

function update_seat_inventory($user, $id, $capacity, $blocked) {
    global $conn;
    if (!ctype_digit((string)$capacity) || $capacity < 1 || $capacity > 500 || !is_array($blocked)) {
        throw new Exception('Enter a capacity between 1 and 500 seats.');
    }
    $capacity = (int)$capacity;
    foreach ($blocked as $seat) {
        if (!is_scalar($seat) || !ctype_digit((string)$seat) || $seat < 1 || $seat > $capacity) {
            throw new Exception('Blocked seats must be within the new seat capacity.');
        }
    }
    $blocked = array_unique(array_map('intval', $blocked));
    mysqli_begin_transaction($conn);
    try {
        $schedule = lock_schedule_for_manager($user, $id);
        if ($schedule['schedule_status'] !== 'active' || strtotime($schedule['departure_time']) <= time()) {
            throw new Exception('Only active future schedules can be changed.');
        }
        $booked = rows("SELECT seat_number FROM transport_bookings WHERE schedule_id=? AND booking_status='confirmed'", 'i', [$id]);
        foreach ($booked as $booking) {
            $seat = (int)$booking['seat_number'];
            if ($seat > $capacity || in_array($seat, $blocked, true)) {
                throw new Exception('A confirmed seat cannot be blocked or removed. Reload to see the latest bookings.');
            }
        }
        query('DELETE FROM blocked_seats WHERE schedule_id=?', 'i', [$id]);
        foreach ($blocked as $seat) {
            query('INSERT INTO blocked_seats(schedule_id,seat_number) VALUES(?,?)', 'ii', [$id, $seat]);
        }
        $available = $capacity - count($booked) - count($blocked);
        query('UPDATE transport_schedules SET total_seats=?,available_seats=? WHERE schedule_id=?', 'iii', [$capacity, $available, $id]);
        mysqli_commit($conn);
    } catch (Throwable $error) {
        mysqli_rollback($conn);
        throw $error;
    }
}

function cancel_schedule($user, $id, $reason) {
    global $conn;
    if (strlen($reason) < 5 || strlen($reason) > 500) {
        throw new Exception('Give a cancellation reason between 5 and 500 characters.');
    }
    mysqli_begin_transaction($conn);
    try {
        $schedule = lock_schedule_for_manager($user, $id);
        if ($schedule['schedule_status'] !== 'active' || strtotime($schedule['departure_time']) <= time()) {
            throw new Exception('Only active schedules before departure can be cancelled.');
        }
        // Keep history, cancel all confirmed tickets, and prevent further reservations.
        query("UPDATE transport_bookings SET booking_status='cancelled' WHERE schedule_id=? AND booking_status='confirmed'", 'i', [$id]);
        query("UPDATE transport_schedules SET schedule_status='cancelled',cancellation_reason=?,cancelled_at=NOW(),available_seats=0 WHERE schedule_id=?", 'si', [$reason, $id]);
        mysqli_commit($conn);
    } catch (Throwable $error) {
        mysqli_rollback($conn);
        throw $error;
    }
}
