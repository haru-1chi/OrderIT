<?php
session_start();
require_once 'config/db.php'; // Include your DB connection file

$sql = "SELECT COUNT(*) as count FROM data_report WHERE `status` = 0";
$stmt = $conn->prepare($sql);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$_SESSION['report_count'] = $row['count'] ?? 0;

echo json_encode(['report_count' => $_SESSION['report_count']]);
?>