<?php
require_once 'controller/common.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') go('login.php');
check_csrf();
 $_SESSION=[];
 session_destroy();
 go('login.php');
