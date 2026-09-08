<?php
require_once 'controller/common.php';
$user=require_role('admin');
require_once 'model/bookingModel.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    check_csrf();
    try {
        $id = (int)post('id');
        $kind = post('kind');
        if ($kind === 'transport') $record = one('SELECT user_id FROM transport_bookings WHERE booking_id=?','i',[$id]);
        elseif ($kind === 'hotel') $record = one('SELECT user_id FROM hotel_bookings WHERE booking_id=?','i',[$id]);
        else throw new Exception('Invalid booking type.');
        if (!$record) throw new Exception('Booking not found.');
        cancel_booking($record['user_id'], $id, $kind);
        flash('Booking cancelled. Any paid amount can now have a manual refund recorded.');
    } catch (Exception $ex) {
        flash($ex instanceof mysqli_sql_exception ? 'Could not cancel. Please try again.' : $ex->getMessage());
    }
    go('admin_view_bookings.php');
}

$transport_sql='SELECT b.*,u.name,s.source,s.destination,s.departure_time FROM transport_bookings b JOIN users u ON b.user_id=u.user_id JOIN transport_schedules s ON b.schedule_id=s.schedule_id';
$hotel_sql='SELECT b.*,u.name,h.hotel_name FROM hotel_bookings b JOIN users u ON b.user_id=u.user_id JOIN hotels h ON b.hotel_id=h.hotel_id';
if($user['role']==='provider'){
$transport=rows($transport_sql.' WHERE s.provider_id=? ORDER BY b.booked_at DESC','i',[$user['user_id']]);
$stays=rows($hotel_sql.' WHERE h.provider_id=? ORDER BY b.booked_at DESC','i',[$user['user_id']]);
}
else{
$transport=rows($transport_sql.' ORDER BY b.booked_at DESC');
$stays=rows($hotel_sql.' ORDER BY b.booked_at DESC');
}
$title='Bookings';
require 'view/header.php';
if($user['role']==='admin')require 'view/adminNav.php';
?>
<h1>
<?= $user['role']==='admin'?'All platform bookings':'Bookings for your services' ?>
</h1>
<?php
 require 'view/bookingsTable.php';
require 'view/footer.php';
?>
