<?php
require_once 'controller/common.php';
$user = require_role('admin');
require_once 'model/supportModel.php';
$status = $_GET['status'] ?? '';
$tickets = list_tickets($user, $status);
$title = 'Customer complaints & feedback';
require 'view/header.php';
require 'view/adminNav.php';
?>
<h1>Customer complaints & feedback</h1>
<p>Open a conversation to read the message, reply, or mark it resolved.</p>
<?php require 'view/ticketTable.php'; require 'view/footer.php'; ?>
