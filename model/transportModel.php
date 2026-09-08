<?php
require_once __DIR__.'/dbModel.php';
function search_routes($type,$source,$destination,$date) {
    return rows("SELECT s.*, u.name AS provider_name FROM transport_schedules s LEFT JOIN users u ON s.provider_id=u.user_id WHERE s.schedule_status='active' AND s.transport_type=? AND s.source LIKE ? AND s.destination LIKE ? AND DATE(s.departure_time)=? AND s.departure_time>NOW() AND (s.provider_id IS NULL OR u.status='active') ORDER BY s.departure_time",'ssss',[$type,'%'.$source.'%','%'.$destination.'%',$date]);
}
