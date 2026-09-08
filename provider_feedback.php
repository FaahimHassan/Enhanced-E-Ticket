<?php
require_once 'controller/common.php';
$user = require_role('provider');
require_once 'model/supportModel.php';
$status = $_GET['status'] ?? '';
$tickets = list_tickets($user, $status);
$title = 'Feedback for your services';
require 'view/header.php';
require 'view/providerNav.php';
?>
<h1>Feedback for your services</h1>
<p>Open a conversation to read the message, reply, or mark it resolved.</p>
<?php require 'view/ticketTable.php'; require 'view/footer.php'; ?>
