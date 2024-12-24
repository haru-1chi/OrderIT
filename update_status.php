<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    date_default_timezone_set('Asia/Bangkok');
    $timestamp = date('Y-m-d H:i:s');

    try {
        $sql = "INSERT INTO order_status (order_id, status, timestamp) VALUES (:order_id, :status, :timestamp)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'order_id' => $order_id,
            'status' => $status,
            'timestamp' => $timestamp
        ]);

        echo "Status updated successfully.";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    exit;
}
