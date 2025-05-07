<?php
require_once 'config/db.php';

date_default_timezone_set('Asia/Bangkok');
$dateNow = new DateTime();
$dateNow->modify("+543 years");
$dateThai = $dateNow->format("Y-m-d");
$timeThai = $dateNow->format("H:i:s");

$sql1 = "SELECT dp.*, dt.depart_name 
         FROM data_report AS dp
         LEFT JOIN depart AS dt ON dp.department = dt.depart_id
         WHERE dp.status = 0 AND DATE(date_report) = ?
         AND TIME(dp.time_report) <= TIME(?)
         ";
$stmt1 = $conn->prepare($sql1);
$stmt1->execute([$dateThai, $timeThai]);
$newReports = $stmt1->fetchAll(PDO::FETCH_ASSOC);

// 2. Records at current time ±30 seconds
$sql2 = "SELECT dp.*, dt.depart_name 
         FROM data_report AS dp
         LEFT JOIN depart AS dt ON dp.department = dt.depart_id
         WHERE dp.status = 0 
           AND DATE(date_report) = ? 
           AND ABS(TIMESTAMPDIFF(SECOND, TIME(dp.time_report), TIME(?))) <= 15";
$stmt2 = $conn->prepare($sql2);
$stmt2->execute([$dateThai, $timeThai]);
$timeReports = $stmt2->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['reports' => $newReports,'reports_set_up' => $timeReports]);
?>