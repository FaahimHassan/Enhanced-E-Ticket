<?php
require_once __DIR__.'/../controller/common.php';
require_once __DIR__.'/../model/transportModel.php';
header('Content-Type: application/json');
$type=$_GET['type'] ?? 'bus';
 $date=$_GET['date'] ?? date('Y-m-d',strtotime('+1 day'));
if (!in_array($type,['bus','train','air'],true) || !valid_date($date) || $date<date('Y-m-d') || strlen($_GET['source']??'')>100 || strlen($_GET['destination']??'')>100) {
    http_response_code(400);
 echo json_encode(['error'=>'Please enter valid search details.']);
 exit;
}
echo json_encode(search_routes($type,trim($_GET['source']??''),trim($_GET['destination']??''),$date));
