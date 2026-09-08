<?php
require_once 'controller/common.php';
$user=require_role('admin');
$providers=true;
require 'controller/adminController.php';
$users=rows($providers?"SELECT * FROM users WHERE role='provider' ORDER BY status='pending' DESC, created_at DESC":'SELECT * FROM users ORDER BY created_at DESC');
$title=$providers?'Service providers':'Registered users';
require 'view/header.php';
require 'view/adminNav.php';
?>
<h1>
<?= e($title) ?>
</h1>
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Name</th>
<th>Contact</th>
<th>Role</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php
 foreach($users as $u): 
?>
<tr>
<td>
<?= e($u['name']) ?>
</td>
<td>
<?= e($u['email']) ?>
<br>
<small>
<?= e($u['phone']) ?>
</small>
</td>
<td>
<?= e($u['role']) ?>
</td>
<td>
<span class="tag">
<?= e($u['status']) ?>
</span>
</td>
<td>
<?php
 if($u['role']!=='admin'&&($providers||$u['status']!=='pending')): 
?>
<form class="actions" method="post">
<?= csrf() ?>
<input type="hidden" name="id" value="<?= $u['user_id'] ?>">
<?php
 if($u['status']!=='active'): 
?>
<button class="button small" name="status" value="active">
<?= $providers?'Approve':'Activate' ?>
</button>
<?php
 endif;
?>
<?php
 if($u['status']!=='blocked'): 
?>
<button class="danger" name="status" value="blocked">
<?= $u['status']==='pending'?'Reject':'Block' ?>
</button>
<?php
 endif;
?>
</form>
<?php
 else: 
?>—<?php
 endif;
?>
</td>
</tr>
<?php
 endforeach;
?>
</tbody>
</table>
</div>
<?php
 require 'view/footer.php';
?>
