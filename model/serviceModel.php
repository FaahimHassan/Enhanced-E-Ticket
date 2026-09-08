<?php
require_once __DIR__.'/dbModel.php';
// Admins manage all schedules; providers can only change records they own.
function save_schedule($user,$id,$data) {
 global $conn;
 $type=$data['transport_type'];
$source=$data['source'];
$destination=$data['destination'];
 $departure=str_replace('T',' ',$data['departure_time']);
$arrival=str_replace('T',' ',$data['arrival_time']);
 $price=$data['price'];
$capacity=$data['total_seats'];
 if(!in_array($type,['bus','train','air'],true)||$source===''||$destination===''||strlen($source)>100||strlen($destination)>100||strcasecmp($source,$destination)===0||!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/',$departure)||!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/',$arrival)||!valid_date(substr($departure,0,10))||!valid_date(substr($arrival,0,10))||date('H:i',strtotime($departure)?:0)!==substr($departure,11,5)||date('H:i',strtotime($arrival)?:0)!==substr($arrival,11,5)||!strtotime($departure)||strtotime($departure)<=time()||strtotime($arrival)<=strtotime($departure)||!is_numeric($price)||$price<=0||$price>99999999||!ctype_digit((string)$capacity)||$capacity<1||$capacity>500) throw new Exception('Enter different cities, valid future times, a positive price, and 1–500 seats.');
 mysqli_begin_transaction($conn);
 try {
  if($id){
   $s=one('SELECT * FROM transport_schedules WHERE schedule_id=? FOR UPDATE','i',[$id]);
   if(!$s||($user['role']!=='admin' && $s['provider_id']!=$user['user_id']))throw new Exception('Schedule not found or access denied.');
   if($s['schedule_status'] !== 'active') throw new Exception('Cancelled schedules cannot be edited.');
   if(one('SELECT booking_id FROM transport_bookings WHERE schedule_id=? LIMIT 1','i',[$id]))throw new Exception('Use Manage seats for capacity changes. Route, time and price cannot be edited after booking.');
   if(one('SELECT seat_number FROM blocked_seats WHERE schedule_id=? LIMIT 1','i',[$id])) throw new Exception('Unblock seats in Manage seats before editing the route details.');
   query('UPDATE transport_schedules SET transport_type=?,source=?,destination=?,departure_time=?,arrival_time=?,price=?,total_seats=?,available_seats=? WHERE schedule_id=?','sssssdiii',[$type,$source,$destination,$departure,$arrival,$price,$capacity,$capacity,$id]);
  }
else query('INSERT INTO transport_schedules(provider_id,transport_type,source,destination,departure_time,arrival_time,price,total_seats,available_seats) VALUES(?,?,?,?,?,?,?,?,?)','isssssdii',[$user['role']==='admin'?null:$user['user_id'],$type,$source,$destination,$departure,$arrival,$price,$capacity,$capacity]);
  mysqli_commit($conn);
 }
catch(Throwable $ex){
mysqli_rollback($conn);
throw $ex;
}
}
function save_hotel($user,$id,$data){
 global $conn;
 $name=$data['hotel_name'];
$location=$data['location'];
$price=$data['price_per_night'];
$description=$data['description'];
$capacity=$data['total_rooms'];
 if($name===''||strlen($name)>150||$location===''||strlen($location)>150||strlen($description)>5000||!is_numeric($price)||$price<=0||$price>99999999||!ctype_digit((string)$capacity)||$capacity<1||$capacity>1000)throw new Exception('Enter a hotel name, location, positive price and 1–1000 rooms.');
 mysqli_begin_transaction($conn);
 try{
  if($id){
   $h=one('SELECT * FROM hotels WHERE hotel_id=? AND provider_id=? FOR UPDATE','ii',[$id,$user['user_id']]);
   if(!$h)throw new Exception('Hotel not found or access denied.');
   $used=$h['total_rooms']-$h['available_rooms'];
   if($capacity<$used)throw new Exception('Room capacity cannot be less than the number of confirmed reservations.');
   query('UPDATE hotels SET hotel_name=?,location=?,price_per_night=?,description=?,total_rooms=?,available_rooms=? WHERE hotel_id=?','ssdsiii',[$name,$location,$price,$description,$capacity,$capacity-$used,$id]);
  }
else query('INSERT INTO hotels(provider_id,hotel_name,location,price_per_night,description,total_rooms,available_rooms) VALUES(?,?,?,?,?,?,?)','issdsii',[$user['user_id'],$name,$location,$price,$description,$capacity,$capacity]);
  mysqli_commit($conn);
 }
catch(Throwable $ex){
mysqli_rollback($conn);
throw $ex;
}
}
function delete_service($user,$id,$kind){
 global $conn;
mysqli_begin_transaction($conn);
 try{
  if($kind==='schedule'){
   $s=one('SELECT * FROM transport_schedules WHERE schedule_id=? FOR UPDATE','i',[$id]);
   if(!$s||($user['role']!=='admin'&&$s['provider_id']!=$user['user_id']))throw new Exception('Access denied.');
   if(one('SELECT booking_id FROM transport_bookings WHERE schedule_id=? LIMIT 1','i',[$id]))throw new Exception('Cannot delete a schedule with booking history.');
   query('DELETE FROM transport_schedules WHERE schedule_id=?','i',[$id]);
  }
elseif($kind==='hotel'){
   $h=one('SELECT * FROM hotels WHERE hotel_id=? AND provider_id=? FOR UPDATE','ii',[$id,$user['user_id']]);
   if(!$h)throw new Exception('Access denied.');
   if(one('SELECT booking_id FROM hotel_bookings WHERE hotel_id=? LIMIT 1','i',[$id]))throw new Exception('Cannot delete a hotel with booking history.');
   query('DELETE FROM hotels WHERE hotel_id=?','i',[$id]);
  }
else throw new Exception('Invalid service type.');
  mysqli_commit($conn);
 }
catch(Throwable $ex){
mysqli_rollback($conn);
throw $ex;
}
}
