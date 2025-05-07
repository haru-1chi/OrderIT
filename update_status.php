<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

try {
    date_default_timezone_set('Asia/Bangkok');
    $timestamp = date('Y-m-d H:i:s');

    if (isset($_POST['order_id'], $_POST['status'])) {
        $order_id = $_POST['order_id'];
        $new_status = (int)$_POST['status'];

        if ($new_status <= 5) {
            $statusOptions = [
                1 => "รอรับเอกสารจากหน่วยงาน",
                2 => "รอส่งเอกสารไปพัสดุ",
                3 => "รอพัสดุสั่งของ",
                4 => "รอหมายเลขครุภัณฑ์",
                5 => "ปิดงาน"
            ];

            // Fetch existing statuses in one DB call
            $stmt = $conn->prepare("SELECT status FROM order_status WHERE order_id = :order_id");
            $stmt->execute(['order_id' => $order_id]);
            $existingStatuses = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'status');

            // Insert only missing statuses up to $new_status
            $insertedStatuses = [];
            $insertStmt = $conn->prepare(
                "INSERT INTO order_status (order_id, status, timestamp) VALUES (:order_id, :status, :timestamp)"
            );

            for ($s = 1; $s <= $new_status; $s++) {
                if (!in_array($s, $existingStatuses)) {
                    $insertStmt->execute([
                        'order_id' => $order_id,
                        'status' => $s,
                        'timestamp' => $timestamp
                    ]);
                    if ($s === $new_status) {
                        $insertedStatuses[] = $s;
                    }
                }
            }

            if ($insertedStatuses) {
                $texts = array_map(fn($s) => $statusOptions[$s], $insertedStatuses);
                echo "อัพเดตสถานะ " . implode(", ", $texts) . " แล้ว";
            } else {
                echo "ไม่มีสถานะใหม่ที่ต้องเพิ่ม";
            }
        } else {
            // Direct insert for statuses > 5 (e.g. cancellation)
            $stmt = $conn->prepare(
                "INSERT INTO order_status (order_id, status, timestamp) VALUES (:order_id, :status, :timestamp)"
            );
            $stmt->execute([
                'order_id' => $order_id,
                'status' => $new_status,
                'timestamp' => $timestamp
            ]);
            echo "ยกเลิกใบเบิกแล้ว";
        }
    } elseif (isset($_POST['id'])) {
        // DELETE status
        $stmt = $conn->prepare("DELETE FROM order_status WHERE id = :id");
        $stmt->execute(['id' => $_POST['id']]);
        echo "ย้อนกลับสถานะสำเร็จ";
    } else {
        echo "Invalid request: missing parameters.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
exit;
