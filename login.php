<?php
require_once 'controller/common.php';
$mode='login';
require 'controller/authController.php';
$title='Welcome back';
 require 'view/header.php';
?>
<section class="auth">
<div>
<span class="eyebrow">TRAVEL, SIMPLIFIED</span>
<h1>
<?= e($title) ?>
</h1>
<p>One place for your transport tickets and hotel stays.</p>
<div class="auth-art">
<span>BUS / TRAIN / AIR</span>
<h2>Less planning.<br>More travelling.</h2>
<span>BANGLADESH, CONNECTED.</span>
</div>
</div>
<div class="card">
<h2>
<?= $mode==='login'?'Log in to your account':'Create your account' ?>
</h2>
<?php
 if($error): 
?>
<p class="notice error" role="alert">
<?= e($error) ?>
</p>
<?php
 endif;
?>
<form method="post" data-validate>
<?= csrf() ?>
<?php
 if($mode!=='login'): 
?>
<label>Full name<input name="name" required minlength="2" maxlength="100" value="<?= e(post('name')) ?>">
</label>
<label>Email address<input name="email" type="email" required maxlength="100" value="<?= e(post('email')) ?>">
</label>
<label>Phone number<input name="phone" type="tel" required pattern="\+?[0-9]{7,15}" value="<?= e(post('phone')) ?>">
</label>
<?php
 else: 
?>
<label>Email or phone<input name="identity" required autocomplete="username" value="<?= e(post('identity')) ?>">
</label>
<?php
 endif;
?>
<label>Password<input type="password" name="password" required <?= $mode!=='login'?'minlength="8" maxlength="72"':'' ?> autocomplete="<?= $mode==='login'?'current-password':'new-password' ?>">
</label>
<?php
 if($mode!=='login'): 
?>
<label>Confirm password<input type="password" name="confirm_password" required minlength="8" maxlength="72">
</label>
<?php
 endif;
?>
<p class="form-error" role="alert">
</p>
<button class="button wide">
<?= $mode==='login'?'Log in →':'Create account →' ?>
</button>
</form>
<p>
<?= $mode==='login'?'New here? <a href="register.php">Create an account</a>':'Already registered? <a href="login.php">Log in</a>' ?>
</p>
</div>
</section>
<p class="card">Service providers and admins use this same login form. New providers: <a href="provider_register.php">register here</a>. Approval is required before provider login.</p>
<?php
 require 'view/footer.php';
?>
