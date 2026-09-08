<?php
require_once __DIR__.'/../model/serviceModel.php';
$error='';
$id=(int)($_GET['id']??0);
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_csrf();
 try{
  if(post('action')==='delete')delete_service($user,(int)post('id'),post('kind'));
  elseif($kind==='schedule')save_schedule($user,$id,['transport_type'=>post('transport_type'),'source'=>post('source'),'destination'=>post('destination'),'departure_time'=>post('departure_time'),'arrival_time'=>post('arrival_time'),'price'=>post('price'),'total_seats'=>post('total_seats')]);
  else save_hotel($user,$id,['hotel_name'=>post('hotel_name'),'location'=>post('location'),'price_per_night'=>post('price_per_night'),'description'=>post('description'),'total_rooms'=>post('total_rooms')]);
  flash('Your changes have been saved.');
go($user['role']==='admin'?'admin_manage_schedules.php':'provider_dashboard.php');
 }
catch(Exception $ex){
$error=$ex instanceof mysqli_sql_exception?'The change could not be saved. Please try again.':$ex->getMessage();
}
}
