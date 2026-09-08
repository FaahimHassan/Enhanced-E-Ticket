<?php
require_once 'controller/common.php';
$user=require_role('provider');
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
require 'view/providerNav.php';
if($user['role']==='admin')require 'view/adminNav.php';
?>
<h1>
<?= $user['role']==='admin'?'All platform bookings':'Bookings for your services' ?>
</h1>
<?php
 require 'view/bookingsTable.php';
require 'view/footer.php';
?>
