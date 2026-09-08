<?php
 $viewer = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>
<?= e($title ?? 'Travel & Stay') ?> · E-Ticket</title>
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/validation.js" defer>
</script>
</head>
<body>
<header>
<a class="brand" href="index.php">
<span class="brand-icon">↗</span> e-ticket<span class="brand-dot">.</span>
</a>
<nav>
<a href="index.php">Transport</a>
<a href="hotel_search.php">Hotels</a>
<?php
 if($viewer): 
?>
<a href="<?= e(dashboard()) ?>">Dashboard</a>
<a href="profile.php">Profile</a>
<?php if($viewer['role']==='passenger'): ?>
<a href="passenger_support.php">Help & feedback</a>
<?php elseif($viewer['role']==='provider'): ?>
<a href="provider_feedback.php">Customer feedback</a>
<?php else: ?>
<a href="admin_complaints.php">Complaints</a>
<?php endif; ?>
<form method="post" action="logout.php" class="inline">
<?= csrf() ?>
<button class="link">Log out</button>
</form>
<?php
 else: 
?>
<a href="provider_register.php">Provider registration</a>
<a href="login.php">Log in</a>
<a class="button small" href="register.php">Create account</a>
<?php
 endif;
?>
</nav>
</header>
<main>
<?php
 if(!empty($_SESSION['message'])): 
?>
<p class="notice" role="status">
<?= e($_SESSION['message']) ?>
</p>
<?php
 unset($_SESSION['message']);
 endif;
?>
