<?php
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_csrf();
$id=(int)post('id');
$status=post('status');
 $target=one('SELECT * FROM users WHERE user_id=?','i',[$id]);
 if(!$target||$target['role']==='admin'||!in_array($status,['active','blocked'],true))flash('That account cannot be changed.');
 elseif($providers && $target['role']!=='provider')flash('Not a provider account.');
 elseif(!$providers && $target['role']==='provider' && $target['status']==='pending')flash('Review pending providers in the Providers page.');
 else{
query('UPDATE users SET status=? WHERE user_id=?','si',[$status,$id]);
flash('Account status updated.');
}
 go($providers?'admin_manage_providers.php':'admin_manage_users.php');
}
