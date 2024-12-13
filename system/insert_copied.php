<?php
session_start();
require_once '../config/db.php';
if (isset($_POST['submit'])) {
    // รับข้อมูลจากฟอร์ม
    $numberWork = generateNumberWork($conn);
    $dateWithdraw = $_POST["dateWithdraw"];
    $refWithdraw = $_POST["ref_withdraw"];
    $refWork = $_POST["ref_work"];
    $refDevice = $_POST["ref_device"];
    $reason = $_POST["reason"];
    $report = $_POST["report"];
    $refDepart = $_POST["depart_id"];
    $refUsername = $_POST["ref_username"];
    $refOffer = $_POST["ref_offer"];
    $quotation = $_POST["quotation"];
    $note = $_POST["note"];
    $status = $_POST["status"];
    date_default_timezone_set('Asia/Bangkok');
    $timestamp = date('Y-m-d H:i:s');

    $numberDevices = $_POST["device_numbers"];

    // รับข้อมูลจากตารางในฟอร์ม
    $lists = $_POST['list'];
    $qualities = $_POST['quality'];
    $amounts = $_POST['amount'];
    $prices = $_POST['price'];
    $units = $_POST['unit'];

    echo '<pre>';
    var_dump([
        'numberWork' => $numberWork,
        'dateWithdraw' => $dateWithdraw,
        'refWithdraw' => $refWithdraw,
        'refWork' => $refWork,
        'refDevice' => $refDevice,

        'numberDevices' => $numberDevices,

        'refDepart' => $refDepart,
        'refUsername' => $refUsername,
        'report' => $report,
        'reason' => $reason,
        'refOffer' => $refOffer,
        'quotation' => $quotation,
        'status' => $status,
        'note' => $note,

        'lists' => $lists,
        'qualities' => $qualities,
        'amounts' => $amounts,
        'prices' => $prices,
        'units' => $units,
    ]);
    echo '</pre>';
    exit();
    if (empty($refDepart)) {
        $_SESSION["error"] = "บันทีกข้อไม่สำเร็จ";
        $_SESSION["warning"] = "กรุณากดเลือกหน่วยงานหลังพิมพ์";
        header("Location: ../create.php");
    } else if (!$_SESSION['error'] && !$_SESSION['warning']) {

        try {
            // Begin transaction
            $conn->beginTransaction();

            // Insert into orderdata_new table
            $sql = "INSERT INTO orderdata_new (numberWork, dateWithdraw, refWithdraw, refWork, refDevice, reason, report, refDepart, refUsername, refOffer, quotation, note) 
                    VALUES (:numberWork, :dateWithdraw, :refWithdraw, :refWork, :refDevice, :reason, :report, :refDepart, :refUsername, :refOffer, :quotation, :note)";
            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':numberWork', $numberWork);
            $stmt->bindParam(':dateWithdraw', $dateWithdraw);
            $stmt->bindParam(':refWithdraw', $refWithdraw);
            $stmt->bindParam(':refWork', $refWork);
            $stmt->bindParam(':refDevice', $refDevice);
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':report', $report);
            $stmt->bindParam(':refDepart', $refDepart);
            $stmt->bindParam(':refUsername', $refUsername);
            $stmt->bindParam(':refOffer', $refOffer);
            $stmt->bindParam(':quotation', $quotation);
            $stmt->bindParam(':note', $note);

            if ($stmt->execute()) {
                $orderId = $conn->lastInsertId();

                // Insert into order_items table
                $itemSql = "INSERT INTO order_items (order_id, list, quality, amount, price, unit) 
                            VALUES (:order_id, :list, :quality, :amount, :price, :unit)";
                $itemStmt = $conn->prepare($itemSql);

                foreach ($lists as $index => $list) {
                    $itemStmt->bindParam(':order_id', $orderId);
                    $itemStmt->bindParam(':list', $list);
                    $itemStmt->bindParam(':quality', $qualities[$index]);
                    $itemStmt->bindParam(':amount', $amounts[$index]);
                    $itemStmt->bindParam(':price', $prices[$index]);
                    $itemStmt->bindParam(':unit', $units[$index]);
                    $itemStmt->execute();
                }

                // Insert into order_numberdevice table
                $deviceSql = "INSERT INTO order_numberdevice (order_item, numberDevice) VALUES (:order_item, :numberDevice)";
                $deviceStmt = $conn->prepare($deviceSql);

                foreach ($numberDevices as $numberDevice) {
                    if (trim($numberDevice) === "") {
                        continue;
                    }
                    $deviceStmt->bindParam(':order_item', $orderId); // Assuming `order_item` links to `order_id`
                    $deviceStmt->bindParam(':numberDevice', $numberDevice);
                    $deviceStmt->execute();
                }

                $statusSql = "INSERT INTO order_status (order_id, status, timestamp) 
                VALUES (:order_id, :status, :timestamp)";
                $statusStmt = $conn->prepare($statusSql);
                $statusStmt->bindParam(':order_id', $orderId);
                $statusStmt->bindParam(':status', $status);
                $statusStmt->bindParam(':timestamp', $timestamp);
                $statusStmt->execute();

                // Commit transaction
                $conn->commit();

                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
                header("location: ../create.php");
            } else {
                throw new Exception("Insert into orderdata_new failed.");
            }
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollBack();
            $_SESSION["error"] = "พบข้อผิดพลาด: " . $e->getMessage();
            header("location: ../check.php");
        }
    }
}
