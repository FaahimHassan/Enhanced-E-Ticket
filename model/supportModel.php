<?php
require_once __DIR__ . '/dbModel.php';

function can_read_ticket($user, $ticket) {
    if (!$ticket) return false;
    if ($user['role'] === 'admin') return true;
    if ($user['role'] === 'provider') return $ticket['provider_id'] == $user['user_id'];
    return $ticket['user_id'] == $user['user_id'];
}

function open_ticket($user, $booking, $category, $subject, $message, $rating) {
    global $conn;
    if ($user['role'] !== 'passenger') throw new Exception('Only passengers can submit a ticket.');
    if (!in_array($category, ['complaint', 'feedback'], true) || strlen($subject) < 3 || strlen($subject) > 150 || strlen($message) < 10 || strlen($message) > 3000) {
        throw new Exception('Enter a subject of 3–150 characters and a message of 10–3000 characters.');
    }
    if ($rating !== '' && (!ctype_digit((string)$rating) || $rating < 1 || $rating > 5)) {
        throw new Exception('Choose a rating from 1 to 5.');
    }
    $transport_id = null;
    $hotel_id = null;
    $provider_id = null;
    if ($booking !== 'general') {
        $parts = explode(':', $booking);
        if (count($parts) !== 2 || !ctype_digit($parts[1])) throw new Exception('Invalid booking.');
        $id = (int)$parts[1];
        // Derive the provider from an owned booking. Never trust a provider ID from the form.
        if ($parts[0] === 'transport') {
            $record = one('SELECT s.provider_id FROM transport_bookings b JOIN transport_schedules s ON b.schedule_id=s.schedule_id WHERE b.booking_id=? AND b.user_id=?', 'ii', [$id, $user['user_id']]);
            $transport_id = $id;
        } elseif ($parts[0] === 'hotel') {
            $record = one('SELECT h.provider_id FROM hotel_bookings b JOIN hotels h ON b.hotel_id=h.hotel_id WHERE b.booking_id=? AND b.user_id=?', 'ii', [$id, $user['user_id']]);
            $hotel_id = $id;
        } else {
            throw new Exception('Invalid booking type.');
        }
        if (!$record) throw new Exception('Choose one of your own bookings.');
        $provider_id = $record['provider_id'];
    }
    query('INSERT INTO support_tickets(user_id,provider_id,transport_booking_id,hotel_booking_id,category,subject,message,rating) VALUES(?,?,?,?,?,?,?,?)', 'iiiisssi', [
        $user['user_id'], $provider_id, $transport_id, $hotel_id, $category, $subject, $message,
        $category === 'feedback' && $rating !== '' ? (int)$rating : null
    ]);
    return mysqli_insert_id($conn);
}

function reply_to_ticket($user, $id, $message) {
    global $conn;
    if (strlen($message) < 2 || strlen($message) > 3000) throw new Exception('Reply must contain 2–3000 characters.');
    mysqli_begin_transaction($conn);
    try {
        $ticket = one('SELECT * FROM support_tickets WHERE ticket_id=? FOR UPDATE', 'i', [$id]);
        if (!can_read_ticket($user, $ticket)) throw new Exception('Ticket not found or access denied.');
        query('INSERT INTO support_replies(ticket_id,user_id,message) VALUES(?,?,?)', 'iis', [$id, $user['user_id'], $message]);
        // A passenger follow-up reopens the conversation so managers see it again.
        $status = $user['role'] === 'passenger' ? 'open' : $ticket['status'];
        query('UPDATE support_tickets SET status=?,updated_at=NOW() WHERE ticket_id=?', 'si', [$status, $id]);
        mysqli_commit($conn);
    } catch (Throwable $error) {
        mysqli_rollback($conn);
        throw $error;
    }
}

function set_ticket_status($user, $id, $status) {
    if (!in_array($user['role'], ['admin', 'provider'], true) || !in_array($status, ['open', 'resolved'], true)) {
        throw new Exception('Invalid ticket action.');
    }
    $ticket = one('SELECT * FROM support_tickets WHERE ticket_id=?', 'i', [$id]);
    if (!can_read_ticket($user, $ticket)) throw new Exception('Ticket not found or access denied.');
    query('UPDATE support_tickets SET status=?,updated_at=NOW() WHERE ticket_id=?', 'si', [$status, $id]);
}

function list_tickets($user, $status) {
    if (!in_array($user['role'], ['passenger', 'provider', 'admin'], true)) throw new Exception('Access denied.');
    $sql = 'SELECT t.*,u.name AS passenger_name,p.name AS provider_name FROM support_tickets t JOIN users u ON t.user_id=u.user_id LEFT JOIN users p ON t.provider_id=p.user_id WHERE 1=1';
    $types = '';
    $values = [];
    if ($user['role'] === 'passenger') {
        $sql .= ' AND t.user_id=?';
        $types .= 'i';
        $values[] = $user['user_id'];
    } elseif ($user['role'] === 'provider') {
        $sql .= ' AND t.provider_id=?';
        $types .= 'i';
        $values[] = $user['user_id'];
    }
    if (in_array($status, ['open', 'resolved'], true)) {
        $sql .= ' AND t.status=?';
        $types .= 's';
        $values[] = $status;
    }
    return rows($sql . ' ORDER BY t.updated_at DESC,t.ticket_id DESC', $types, $values);
}
