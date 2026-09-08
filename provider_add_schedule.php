<?php
require_once 'controller/common.php';
$user=require_role('provider');
$kind='schedule';
require 'controller/serviceController.php';
$record=$id?one('SELECT * FROM transport_schedules WHERE schedule_id=? AND provider_id=?','ii',[$id,$user['user_id']]):[];
if($id&&!$record){
flash('Schedule not found.');
go('provider_dashboard.php');
}
if($_SERVER['REQUEST_METHOD']==='POST')$record=$_POST;
$title='Manage schedule';
require 'view/header.php';
require 'view/providerNav.php';
?>
<h1>Your transport schedule</h1>
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
require 'view/footer.php';
?>
