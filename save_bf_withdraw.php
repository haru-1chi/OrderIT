<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

$id = $_POST['id'];
$department = $_POST['department'];
$deviceName = $_POST['deviceName'];
$description = $_POST['description'];
$number_device = $_POST['number_devices'];

try {
    $sql = "UPDATE data_report 
                SET description = :description, 
                    number_device = :number_device, 
                    deviceName = :deviceName, 
                    department = :department
                    WHERE id = :id";

    $stmt = $conn->prepare($sql);

    // Bind common parameters
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":number_device", $number_device);
    $stmt->bindParam(":deviceName", $deviceName);
    $stmt->bindParam(":department", $department);
    $stmt->bindParam(":id", $id);

    if ($stmt->execute()) {
        echo "บันทึกสำเร็จ";
    } else {
        echo "Error";
    }
} catch (PDOException $e) {
    echo '' . $e->getMessage() . '';
}
