<?php
require_once '../config/db.php';

$device_name = $_POST['dataToInsert']; // รับค่า depart_name ผ่านทาง $_POST

try {
    $sql = "SELECT * FROM device WHERE device_name = :device_name";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":device_name", $device_name);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($stmt->rowCount() > 0) {
        // ถ้ามีรายการนี้อยู่แล้วในฐานข้อมูล
        echo "มีรายการนี้อยู่แล้ว";
    } else {
        // ถ้ายังไม่มีรายการนี้อยู่ในฐานข้อมูล
        $sql = "INSERT INTO device(device_name) VALUES(:device_name)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":device_name", $device_name);



        if ($stmt->execute()) {
            // บันทึกข้อมูลสำเร็จ
            $sql = "SELECT * FROM device ORDER BY device_id DESC LIMIT 1";
            $stmt2 = $conn->prepare($sql);
            $stmt2->execute();
            $result = $stmt2->fetch(PDO::FETCH_ASSOC);

            echo $result['device_id'];
        }
    }
} catch (PDOException $e) {
    // กรณีเกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูลหรือ query
    echo '' . $e->getMessage() . '';
}