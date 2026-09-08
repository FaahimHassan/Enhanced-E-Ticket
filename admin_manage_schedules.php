<?php
require_once 'controller/common.php';
$user=require_role('admin');
$kind='schedule';
require 'controller/serviceController.php';
$record=$id?one('SELECT * FROM transport_schedules WHERE schedule_id=?','i',[$id]):[];
if($id&&!$record){
flash('Schedule not found.');
go('admin_manage_schedules.php');
}
if($_SERVER['REQUEST_METHOD']==='POST'&&post('action')!=='delete')$record=$_POST;
$schedules=rows('SELECT * FROM transport_schedules ORDER BY departure_time DESC');
$title='Manage schedules';
require 'view/header.php';
require 'view/adminNav.php';
?>
<h1>Transport schedules</h1>
<?php
 if($error): 
?>
<p class="notice error">
<?= e($error) ?>
</p>
<?php
 endif;
?>
<?php
 require 'view/scheduleForm.php';
?>
<h2>All schedules</h2>
<?php
 require 'view/scheduleTable.php';
require 'view/footer.php';
?>
