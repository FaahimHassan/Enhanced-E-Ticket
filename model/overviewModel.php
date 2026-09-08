<?php
require_once __DIR__ . '/dbModel.php';

function operations_overview($user) {
    if (!in_array($user['role'], ['admin', 'provider'], true)) throw new Exception('Access denied.');
    $provider = $user['role'] === 'provider';
    $types = $provider ? 'i' : '';
    $values = $provider ? [$user['user_id']] : [];
    $schedule_filter = $provider ? ' WHERE s.provider_id=?' : '';
    $hotel_filter = $provider ? ' WHERE h.provider_id=?' : '';
    // Separate subqueries avoid counting each booking twice in joined summaries.
    $schedules = rows("SELECT s.schedule_id,s.source,s.destination,s.transport_type,s.departure_time,s.schedule_status,
        s.total_seats,s.available_seats,
        (SELECT COUNT(*) FROM blocked_seats x WHERE x.schedule_id=s.schedule_id) AS blocked,
        (SELECT COUNT(*) FROM transport_bookings b WHERE b.schedule_id=s.schedule_id AND b.booking_status='confirmed') AS confirmed
        FROM transport_schedules s" . $schedule_filter . ' ORDER BY s.departure_time DESC', $types, $values);
    $hotels = rows('SELECT h.hotel_id,h.hotel_name,h.location,h.total_rooms,h.available_rooms FROM hotels h' . $hotel_filter . ' ORDER BY h.hotel_name', $types, $values);
    $transport = one("SELECT COUNT(*) AS total,COALESCE(SUM(b.booking_status='confirmed'),0) AS confirmed,COALESCE(SUM(b.booking_status='cancelled'),0) AS cancelled FROM transport_bookings b JOIN transport_schedules s ON b.schedule_id=s.schedule_id" . $schedule_filter, $types, $values);
    $stays = one("SELECT COUNT(*) AS total,COALESCE(SUM(b.booking_status='confirmed'),0) AS confirmed FROM hotel_bookings b JOIN hotels h ON b.hotel_id=h.hotel_id" . $hotel_filter, $types, $values);
    $support = one("SELECT COUNT(*) AS n FROM support_tickets WHERE status='open'" . ($provider ? ' AND provider_id=?' : ''), $types, $values)['n'];
    $available = 0;
    foreach ($schedules as $schedule) {
        if ($schedule['schedule_status'] === 'active' && strtotime($schedule['departure_time']) > time()) {
            $available += (int)$schedule['available_seats'];
        }
    }
    return [
        'updated_at' => date('Y-m-d H:i:s') . ' (Bangladesh)',
        'summary' => [
            'confirmed_tickets' => (int)$transport['confirmed'],
            'cancelled_tickets' => (int)$transport['cancelled'],
            'available_seats' => $available,
            'confirmed_stays' => (int)$stays['confirmed'],
            'open_conversations' => (int)$support
        ],
        'schedules' => $schedules,
        'hotels' => $hotels
    ];
}
