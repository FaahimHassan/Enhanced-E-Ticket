<?php
require_once __DIR__.'/dbModel.php';
// Lock the schedule row before reading seats. Two passengers cannot book the same seat.
function book_transport($user_id,$schedule_id,$seat) {
 global $conn;
 mysqli_begin_transaction($conn);
 try {
  $s=one('SELECT * FROM transport_schedules WHERE schedule_id=? FOR UPDATE','i',[$schedule_id]);
  if(!$s || $s['schedule_status'] !== 'active' || strtotime($s['departure_time'])<=time() || $s['available_seats']<1) throw new Exception('This journey is no longer available.');
  if($s['provider_id'] && !one("SELECT user_id FROM users WHERE user_id=? AND status='active'",'i',[$s['provider_id']])) throw new Exception('This provider is currently unavailable.');
  if(!ctype_digit((string)$seat) || $seat<1 || $seat>$s['total_seats']) throw new Exception('Choose a valid seat.');
  $seat=(string)(int)$seat;
 // Normalize 05 to 5 before checking the seat.
  if(one('SELECT seat_number FROM blocked_seats WHERE schedule_id=? AND seat_number=?','ii',[$schedule_id,(int)$seat])) throw new Exception('That seat is blocked by the service provider.');
  if(one("SELECT booking_id FROM transport_bookings WHERE schedule_id=? AND seat_number=? AND booking_status='confirmed'",'is',[$schedule_id,$seat])) throw new Exception('That seat was just booked. Please choose another.');
  query('INSERT INTO transport_bookings(user_id,schedule_id,seat_number) VALUES(?,?,?)','iis',[$user_id,$schedule_id,$seat]);
  // Store the booked price, even if listing prices change later.
  $booking_id = mysqli_insert_id($conn);
  query('INSERT INTO payment_records(transport_booking_id,amount) VALUES(?,?)','id',[$booking_id,$s['price']]);
  query('UPDATE transport_schedules SET available_seats=available_seats-1 WHERE schedule_id=?','i',[$schedule_id]);
  mysqli_commit($conn);
 }
 catch (Throwable $error) {
 mysqli_rollback($conn);
 throw $error;
 }
}
// The course schema uses a simple room inventory: each confirmed reservation uses one room until cancelled.
function book_hotel($user_id,$hotel_id,$in,$out) {
 global $conn;
 if(!valid_date($in)||!valid_date($out)||$in<date('Y-m-d')||$out<=$in) throw new Exception('Choose a valid future stay, with check-out after check-in.');
 mysqli_begin_transaction($conn);
 try {
  $h=one('SELECT * FROM hotels WHERE hotel_id=? FOR UPDATE','i',[$hotel_id]);
  if(!$h || $h['available_rooms']<1) throw new Exception('No rooms are available.');
  if(!one("SELECT user_id FROM users WHERE user_id=? AND status='active'",'i',[$h['provider_id']])) throw new Exception('This provider is currently unavailable.');
  query('INSERT INTO hotel_bookings(user_id,hotel_id,check_in,check_out) VALUES(?,?,?,?)','iiss',[$user_id,$hotel_id,$in,$out]);
   $booking_id = mysqli_insert_id($conn);
  $nights = (new DateTime($in))->diff(new DateTime($out))->days;
  query('INSERT INTO payment_records(hotel_booking_id,amount) VALUES(?,?)','id',[$booking_id,$nights*$h['price_per_night']]);
  query('UPDATE hotels SET available_rooms=available_rooms-1 WHERE hotel_id=?','i',[$hotel_id]);
  mysqli_commit($conn);
 }
catch(Throwable $error){
mysqli_rollback($conn);
throw $error;
}
}
function cancel_booking($user_id, $id, $kind) {
    global $conn;
    mysqli_begin_transaction($conn);
    try {
        if ($kind === 'transport') {
            $ref = one('SELECT schedule_id FROM transport_bookings WHERE booking_id=? AND user_id=?', 'ii', [$id, $user_id]);
            if (!$ref) throw new Exception('Booking not found.');
            $schedule = one('SELECT * FROM transport_schedules WHERE schedule_id=? FOR UPDATE', 'i', [$ref['schedule_id']]);
            $booking = one('SELECT * FROM transport_bookings WHERE booking_id=? AND user_id=? FOR UPDATE', 'ii', [$id, $user_id]);
            if ($booking['booking_status'] !== 'confirmed' || $schedule['schedule_status'] !== 'active' || strtotime($schedule['departure_time']) <= time()) {
                throw new Exception('Only confirmed bookings before departure can be cancelled.');
            }
            query("UPDATE transport_bookings SET booking_status='cancelled' WHERE booking_id=?", 'i', [$id]);
            query('UPDATE transport_schedules SET available_seats=available_seats+1 WHERE schedule_id=?', 'i', [$ref['schedule_id']]);
        } elseif ($kind === 'hotel') {
            $ref = one('SELECT hotel_id FROM hotel_bookings WHERE booking_id=? AND user_id=?', 'ii', [$id, $user_id]);
            if (!$ref) throw new Exception('Booking not found.');
            one('SELECT hotel_id FROM hotels WHERE hotel_id=? FOR UPDATE', 'i', [$ref['hotel_id']]);
            $booking = one('SELECT * FROM hotel_bookings WHERE booking_id=? AND user_id=? FOR UPDATE', 'ii', [$id, $user_id]);
            if ($booking['booking_status'] !== 'confirmed' || $booking['check_in'] <= date('Y-m-d')) {
                throw new Exception('Only confirmed stays before check-in can be cancelled.');
            }
            query("UPDATE hotel_bookings SET booking_status='cancelled' WHERE booking_id=?", 'i', [$id]);
            query('UPDATE hotels SET available_rooms=available_rooms+1 WHERE hotel_id=?', 'i', [$ref['hotel_id']]);
        } else {
            throw new Exception('Invalid booking type.');
        }
        // Refund recording is a separate admin action, never an automatic money transfer.
        mysqli_commit($conn);
    } catch (Throwable $error) {
        mysqli_rollback($conn);
        throw $error;
    }
}
