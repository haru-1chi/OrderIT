<?php
require_once 'config/db.php';

$dateNow = new DateTime();
$dateNow->modify("+543 years");
$dateThai = $dateNow->format("Y-m-d");

// Get the latest report
$sql = "SELECT dp.*, dt.depart_name 
            FROM data_report as dp
            LEFT JOIN depart as dt ON dp.department = dt.depart_id
            WHERE dp.status = 0 AND DATE(date_report) = '$dateThai'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['reports' => $reports]);
?>