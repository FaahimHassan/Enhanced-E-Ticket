<?php
require_once 'controller/common.php';
$user=require_role(['passenger','provider','admin']);
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_csrf();
 if(post('action')==='password'){
  $password=$_POST['password']??'';
  if(!password_verify($_POST['current_password']??'',$user['password']))$error='Current password is incorrect.';
  elseif(strlen($password)<8||strlen($password)>72||$password!==($_POST['confirm_password']??''))$error='New passwords must match and contain 8–72 characters.';
  else{
query('UPDATE users SET password=? WHERE user_id=?','si',[password_hash($password,PASSWORD_DEFAULT),$user['user_id']]);
session_regenerate_id(true);
flash('Password updated.');
go('profile.php');
}
 }
else{
  if(!valid_person(post('name'),post('email'),post('phone')))$error='Please enter valid profile details.';
  else try{
query('UPDATE users SET name=?,email=?,phone=? WHERE user_id=?','sssi',[post('name'),post('email'),post('phone'),$user['user_id']]);
flash('Profile updated.');
go('profile.php');
}
catch(mysqli_sql_exception $ex){
if($ex->getCode()===1062)$error='Email or phone already registered.';
else throw $ex;
}
 }
}
$title='Your profile';
require 'view/header.php';
?>
<h1>Your profile</h1>
<?php
 if($error): 
?>
<p class="notice error">
<?= e($error) ?>
</p>
<?php
 endif;
?>
<div class="two-column">
<form class="card" method="post" data-validate>
<h2>Personal details</h2>
<?= csrf() ?>
<input type="hidden" name="action" value="profile">
<?php
 foreach(['name'=>'Full name','email'=>'Email address','phone'=>'Phone number'] as $key=>$label): 
?>
<label>
<?= $label ?>
<input name="<?= $key ?>" type="<?= $key==='email'?'email':'text' ?>" value="<?= e($user[$key]) ?>" required <?= $key==='phone'?'pattern="\+?[0-9]{7,15}"':($key==='name'?'minlength="2"':'') ?> maxlength="<?= $key==='phone'?20:100 ?>">
</label>
<?php
 endforeach;
?>
<button class="button">Save changes</button>
</form>
<form class="card" method="post" data-validate>
<h2>Change password</h2>
<?= csrf() ?>
<input type="hidden" name="action" value="password">
<label>Current password<input type="password" name="current_password" required>
</label>
<label>New password<input type="password" name="password" minlength="8" maxlength="72" required>
</label>
<label>Confirm new password<input type="password" name="confirm_password" minlength="8" maxlength="72" required>
</label>
<p class="form-error" role="alert">
</p>
<button class="button">Update password</button>
</form>
</div>
<?php
 require 'view/footer.php';
?>
