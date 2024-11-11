<?php
require_once 'config/db.php';

$depart_name = $_POST['dataToInsert']; // รับค่า depart_name ผ่านทาง $_POST

try {
    $sql = "SELECT * FROM depart WHERE depart_name = :depart_name";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":depart_name", $depart_name);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($stmt->rowCount() > 0) {
        // ถ้ามีรายการนี้อยู่แล้วในฐานข้อมูล
        echo "มีรายการนี้อยู่แล้ว";
    } else {
        // ถ้ายังไม่มีรายการนี้อยู่ในฐานข้อมูล
        $sql = "INSERT INTO depart(depart_name) VALUES(:depart_name)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":depart_name", $depart_name);



        if ($stmt->execute()) {
            // บันทึกข้อมูลสำเร็จ
            $sql = "SELECT * FROM depart ORDER BY depart_id DESC LIMIT 1";
            $stmt2 = $conn->prepare($sql);
            $stmt2->execute();
            $result = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            echo $result['depart_id'];
        }
    }
} catch (PDOException $e) {
    // กรณีเกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูลหรือ query
    echo '' . $e->getMessage() . '';
}
