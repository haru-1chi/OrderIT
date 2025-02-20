<?php
session_start();
require_once '../config/db.php';

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
// exit;

if (isset($_POST['addUsers'])) { // เพิ่ม Admin
    $username = $_POST['username'];
    $password = $_POST['password'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    try {
        $sql = "SELECT * FROM admin WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['username'] == $username) {
                $_SESSION['error'] = 'ชื่อผู้ใช้นี้มีอยู่แล้ว';
                header('location: ../insertData.php');
            }
        } else if (!isset($_SESSION['error'])) {
            $passwordhash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO admin VALUES(:username,:password,:fname,:lname)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password", $passwordhash);
            $stmt->bindParam(":fname", $fname);
            $stmt->bindParam(":lname", $lname);

            if ($stmt->execute()) {
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['addWithdraw'])) { // เพิ่ม ประเภทการเบิก
    $withdraw_name = $_POST['withdraw_name'];
    try {
        $sql = "SELECT * FROM withdraw WHERE withdraw_name = :withdraw_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":withdraw_name", $withdraw_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['withdraw_name'] == $withdraw_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                header('location: ../insertData.php');
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "INSERT INTO withdraw(withdraw_name) VALUES(:withdraw_name)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":withdraw_name", $withdraw_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['addListWork'])) { // เพิ่ม ประเภทงาน
    $work_name = $_POST['work_name'];
    try {
        $sql = "SELECT * FROM listwork WHERE work_name = :work_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":work_name", $work_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['work_name'] == $work_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                header('location: ../insertData.php');
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "INSERT INTO listwork(work_name) VALUES(:work_name)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":work_name", $work_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['addDevice'])) { // เพิ่ม รายการอุปกรณ์
    $device_name = $_POST['device_name'];
    try {
        $sql = "SELECT * FROM device WHERE device_name = :device_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":device_name", $device_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['device_name'] == $device_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                header('location: ../insertData.php');
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "INSERT INTO device(device_name) VALUES(:device_name)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":device_name", $device_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['addmodels'])) { // เพิ่ม รายการอุปกรณ์
    $models_name = $_POST['models_name'];
    $quality = $_POST['quality'];
    $price = $_POST['price'];
    $unit = $_POST['unit'];
    try {
        $sql = "SELECT * FROM device_models WHERE models_name = :models_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":models_name", $models_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['models_name'] == $models_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                header('location: ../insertData.php');
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "INSERT INTO device_models(models_name , quality , price , unit ) VALUES(:models_name , :quality , :price , :unit)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":models_name", $models_name);
            $stmt->bindParam(":quality", $quality);
            $stmt->bindParam(":price", $price);
            $stmt->bindParam(":unit", $unit);

            if ($stmt->execute()) {
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['addDepart'])) { // เพิ่ม ประเภทงาน
    $depart_name = $_POST['depart_name'];
    try {
        $sql = "SELECT * FROM depart WHERE depart_name = :depart_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":depart_name", $depart_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['depart_name'] == $depart_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                header('location: ../insertData.php');
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "INSERT INTO depart(depart_name) VALUES(:depart_name)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":depart_name", $depart_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['addOffer'])) { // เพิ่ม ร้านที่เสนอราคา
    $offer_name = $_POST['offer_name'];
    try {
        $sql = "SELECT * FROM offer WHERE offer_name = :offer_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":offer_name", $offer_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['offer_name'] == $offer_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                header('location: ../insertData.php');
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "INSERT INTO offer(offer_name) VALUES(:offer_name)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":offer_name", $offer_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['addWorkingName'])) { // เพิ่ม ร้านที่เสนอราคา
    $workingName = $_POST['workingName'];
    try {
        $sql = "SELECT * FROM workinglist WHERE workingName = :workingName";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":workingName", $workingName);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['workingName'] == $workingName) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                header('location: ../insertData.php');
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "INSERT INTO workinglist(workingName) VALUES(:workingName)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":workingName", $workingName);

            if ($stmt->execute()) {
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['addproblemName'])) { // เพิ่ม ร้านที่เสนอราคา
    $problemName = $_POST['problemName'];
    try {
        $sql = "SELECT * FROM problemlist WHERE problemName = :problemName";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":problemName", $problemName);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['problemName'] == $problemName) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                header('location: ../insertData.php');
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "INSERT INTO problemlist(problemName) VALUES(:problemName)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":problemName", $problemName);

            if ($stmt->execute()) {
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['addSLA'])) { // เพิ่ม ปัญหาใน SLA
    $sla_name = $_POST['sla_name'];
    try {
        $sql = "SELECT * FROM sla WHERE sla_name = :sla_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":sla_name", $sla_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['sla_name'] == $sla_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                header('location: ../insertData.php');
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "INSERT INTO sla(sla_name) VALUES(:sla_name)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":sla_name", $sla_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['addKPI'])) { // เพิ่ม ตัวชี้วัด
    $kpi_name = $_POST['kpi_name'];
    try {
        $sql = "SELECT * FROM kpi WHERE kpi_name = :kpi_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":kpi_name", $kpi_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['kpi_name'] == $kpi_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                header('location: ../insertData.php');
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "INSERT INTO kpi(kpi_name) VALUES(:kpi_name)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":kpi_name", $kpi_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}

function generateNumberWork($conn)
{
    // Fetch the latest numberWork from the database
    $sql = "SELECT numberWork FROM orderdata_new ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $latestNumberWork = $result['numberWork'];
        $cleanNumberWork = str_replace(' S', '', $latestNumberWork);
        list($numerator, $denominator) = explode('/', $cleanNumberWork);
    } else {
        // Default if no numberWork exists
        $numerator = 0;
        $denominator = 68; // Starting year or any default value
    }

    $currentDate = new DateTime();
    $october10 = new DateTime(($currentDate->format('Y') + 1) . '-10-10');

    if ($currentDate > $october10) {
        // Add 1 to the numerator and increment the denominator for the new year
        $newNumerator = intval($numerator) + 1;
        $newDenominator = intval($denominator) + 1;
    } else {
        // Increment only the numerator
        $newNumerator = intval($numerator) + 1;
        $newDenominator = intval($denominator);
    }

    return $newNumerator . '/' . $newDenominator;
}

// ตรวจสอบว่ามีการส่งข้อมูลมาจากฟอร์มหรือไม่
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
    $id_ref = $_POST["id_ref"];
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

    // echo '<pre>';
    // var_dump([
    //     'numberWork' => $numberWork,
    //     'dateWithdraw' => $dateWithdraw,
    //     'refWithdraw' => $refWithdraw,
    //     'refWork' => $refWork,
    //     'refDevice' => $refDevice,

    //     'numberDevices' => $numberDevices,

    //     'refDepart' => $refDepart,
    //     'refUsername' => $refUsername,
    //     'report' => $report,
    //     'reason' => $reason,
    //     'refOffer' => $refOffer,
    //     'quotation' => $quotation,
    //     'status' => $status,
    //     'note' => $note,

    //     'lists' => $lists,
    //     'qualities' => $qualities,
    //     'amounts' => $amounts,
    //     'prices' => $prices,
    //     'units' => $units,
    // ]);
    // echo '</pre>';
    // exit();
    if (empty($refDepart)) {
        $_SESSION["error"] = "บันทีกข้อไม่สำเร็จ";
        $_SESSION["warning"] = "กรุณากดเลือกหน่วยงานหลังพิมพ์";
        header("Location: ../create.php");
    } else if (!$_SESSION['error'] && !$_SESSION['warning']) {

        try {
            // Begin transaction
            $conn->beginTransaction();

            // Insert into orderdata_new table
            $sql = "INSERT INTO orderdata_new (numberWork, dateWithdraw, refWithdraw, refWork, refDevice, reason, report, refDepart, refUsername, refOffer, quotation, note,id_ref) 
                    VALUES (:numberWork, :dateWithdraw, :refWithdraw, :refWork, :refDevice, :reason, :report, :refDepart, :refUsername, :refOffer, :quotation, :note, :id_ref)";
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
            $stmt->bindParam(':id_ref', $id_ref);
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
                header("location: ../check.php");
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
if (isset($_POST['copied_submit'])) {
    // รับข้อมูลจากฟอร์ม
    $numberWork = generateNumberWork($conn);
    $dateWithdraw = $_POST["copied_dateWithdraw"];
    $refWithdraw = $_POST["copied_ref_withdraw"];
    $refWork = $_POST["copied_ref_work"];
    $refDevice = $_POST["copied_ref_device"];
    $reason = $_POST["copied_reason"];
    $report = $_POST["copied_report"];
    $refDepart = $_POST["copied_depart_id"];
    $refUsername = $_POST["copied_ref_username"];
    $refOffer = $_POST["copied_ref_offer"];
    $quotation = $_POST["copied_quotation"];
    $note = $_POST["copied_note"];
    $status = $_POST["copied_status"];
    date_default_timezone_set('Asia/Bangkok');
    $timestamp = date('Y-m-d H:i:s');

    $numberDevices = $_POST["copied_device_numbers"];

    // รับข้อมูลจากตารางในฟอร์ม
    $lists = $_POST['copied_list'];
    $qualities = $_POST['copied_quality'];
    $amounts = $_POST['copied_amount'];
    $prices = $_POST['copied_price'];
    $units = $_POST['copied_unit'];

    // echo '<pre>';
    // var_dump([
    //     'numberWork' => $numberWork,
    //     'dateWithdraw' => $dateWithdraw,
    //     'refWithdraw' => $refWithdraw,
    //     'refWork' => $refWork,
    //     'refDevice' => $refDevice,

    //     'numberDevices' => $numberDevices,

    //     'refDepart' => $refDepart,
    //     'refUsername' => $refUsername,
    //     'report' => $report,
    //     'reason' => $reason,
    //     'refOffer' => $refOffer,
    //     'quotation' => $quotation,
    //     'status' => $status,
    //     'note' => $note,

    //     'lists' => $lists,
    //     'qualities' => $qualities,
    //     'amounts' => $amounts,
    //     'prices' => $prices,
    //     'units' => $units,
    // ]);
    // echo '</pre>';
    // exit();
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
if (isset($_POST['submit_with_work'])) {
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
    $status = 1;
    $id_ref = $_POST["id_ref"];
    $numberDevices = $_POST["number_device"];

    $lists = $_POST['list'];
    $qualities = $_POST['quality'];
    $amounts = $_POST['amount'];
    $prices = $_POST['price'];
    $units = $_POST['unit'];
    // Additional fields from Bantext
    $date_report = $_POST['date_report'] ?? null;
    $time_report = $_POST['time_report'] ?? null;
    $take = $_POST['take'] ?? null;
    $problem = $_POST['problem'] ?? null;
    $description = $_POST['description'] ?? null;
    $withdraw = $_POST['withdraw'] ?? $_POST['withdraw2'] ?? null;
    $department = $_POST['department'] ?? null;
    $repair_count = $_POST['repair_count'] ?? null;
    $device = $_POST['device'] ?? null;
    $deviceName = $_POST['deviceName'] ?? null;
    $noteTask = $_POST["noteTask"] ?? null;
    $sla = $_POST['sla'] ?? null;
    $kpi = $_POST['kpi'] ?? null;
    $close_date = $_POST['close_date'] ?? null;
    $statusTask = 3;
    if (!empty($close_date) && strtotime($close_date)) {
        if (empty($device) || empty($problem) || empty($sla) || empty($kpi)) {
            $statusTask = 6; // Incomplete
        } else {
            $statusTask = 4; // Complete
        }
    }

    date_default_timezone_set('Asia/Bangkok');
    $timestamp = date('Y-m-d H:i:s');
    // echo '<pre>';
    // var_dump([
    //     'id_ref' => $id_ref,
    //     'numberWork' => $numberWork,
    //     'dateWithdraw' => $dateWithdraw,
    //     'refWithdraw' => $refWithdraw,
    //     'refWork' => $refWork,
    //     'refDevice' => $refDevice,
    //     'numberDevices' => $numberDevices,
    //     'refDepart' => $refDepart,
    //     'refUsername' => $refUsername,
    //     'report' => $report,
    //     'reason' => $reason,
    //     'refOffer' => $refOffer,
    //     'quotation' => $quotation,
    //     'status' => $status,
    //     'note' => $note,
    //     'lists' => $lists,
    //     'qualities' => $qualities,
    //     'amounts' => $amounts,
    //     'prices' => $prices,
    //     'units' => $units,

    //     //ขาด number_devices ของ task
    //     'close_date' => $close_date,
    //     'department' => $department,
    //     'deviceName' => $deviceName,
    //     'device' => $device,
    //     'description' => $description,
    //     'repair_count' => $repair_count,
    //     'withdraw' => $withdraw,
    //     'noteTask' => $noteTask,
    //     'sla' => $sla,
    //     'kpi' => $kpi,
    //     'problem' => $problem,
    // ]);
    // echo '</pre>';
    // exit();
    if (empty($refDepart)) {
        $_SESSION["error"] = "บันทีกข้อไม่สำเร็จ";
        $_SESSION["warning"] = "กรุณากดเลือกหน่วยงานหลังพิมพ์";
        header("Location: ../create.php");
    } else if (!$_SESSION['error'] && !$_SESSION['warning']) {
        try {
            $conn->beginTransaction();

            // Insert into orderdata_new
            $sql = "INSERT INTO orderdata_new (numberWork, dateWithdraw, refWithdraw, refWork, refDevice, reason, report, refDepart, refUsername, refOffer, quotation, note, id_ref) 
                    VALUES (:numberWork, :dateWithdraw, :refWithdraw, :refWork, :refDevice, :reason, :report, :refDepart, :refUsername, :refOffer, :quotation, :note, :id_ref)";
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
            $stmt->bindParam(':id_ref', $id_ref);

            if ($stmt->execute()) {
                $orderId = $conn->lastInsertId();

                // Prepare statements
                $itemSql = "INSERT INTO order_items (order_id, list, quality, amount, price, unit) 
                VALUES (:order_id, :list, :quality, :amount, :price, :unit)";
                $itemStmt = $conn->prepare($itemSql);

                $deviceSql = "INSERT INTO order_numberdevice (order_item, numberDevice) 
                  VALUES (:order_item, :numberDevice)";
                $deviceStmt = $conn->prepare($deviceSql);

                foreach ($lists as $modalId => $modalLists) {
                    foreach ($modalLists as $index => $list) {
                        $itemStmt->bindParam(':order_id', $orderId);
                        $itemStmt->bindParam(':list', $list);
                        $itemStmt->bindParam(':quality', $qualities[$modalId][$index]);
                        $itemStmt->bindParam(':amount', $amounts[$modalId][$index]);
                        $itemStmt->bindParam(':price', $prices[$modalId][$index]);
                        $itemStmt->bindParam(':unit', $units[$modalId][$index]);
                        $itemStmt->execute();
                    }
                }

                if (isset($numberDevices)) {
                    foreach ($numberDevices as $modalId => $devices) {
                        foreach ($devices as $index => $numberDevice) {
                            if (trim($numberDevice) === "") {
                                continue;
                            }
                            // Save devices to the order_numberdevice table
                            $deviceStmt->bindParam(':order_item', $orderId);
                            $deviceStmt->bindParam(':numberDevice', $numberDevice);
                            $deviceStmt->execute();

                            // Save the first device into $firstNumberDevice
                            if ($index === 0) {
                                $firstNumberDevice = $numberDevice;
                            }
                        }
                    }
                }

                $statusSql = "INSERT INTO order_status (order_id, status, timestamp) 
                  VALUES (:order_id, :status, :timestamp)";
                $statusStmt = $conn->prepare($statusSql);
                $statusStmt->bindParam(':order_id', $orderId);
                $statusStmt->bindParam(':status', $status);
                $statusStmt->bindParam(':timestamp', $timestamp);
                $statusStmt->execute();

                // Update additional fields in data_report if `id_ref` matches
                $checkSql = "SELECT numberWork FROM orderdata_new WHERE id_ref = :id_ref";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bindParam(":id_ref", $id_ref);
                $checkStmt->execute();

                if ($checkStmt->rowCount() > 0) {
                    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
                    $withdraw = $result['numberWork'];
                }

                $firstNumberDevice = $firstNumberDevice ?? null;

                $updateSql = "UPDATE data_report 
                              SET date_report = :date_report, time_report = :time_report, take = :take, problem = :problem, description = :description, note = :note, withdraw = :withdraw,
                                  number_device = :number_device, device = :device, deviceName = :deviceName, sla = :sla, 
                                  kpi = :kpi, repair_count = :repair_count, close_date = :close_date, department = :department";

                // if (!empty($close_date) && strtotime($close_date)) {
                $updateSql .= ", status = :status";
                // }

                $updateSql .= " WHERE id = :id_ref";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bindParam(":date_report", $date_report);
                $updateStmt->bindParam(":time_report", $time_report);
                $updateStmt->bindParam(":take", $take);
                $updateStmt->bindParam(":problem", $problem);
                $updateStmt->bindParam(":description", $description);
                $updateStmt->bindParam(":note", $noteTask);
                $updateStmt->bindParam(":withdraw", $withdraw);
                $updateStmt->bindParam(":number_device", $firstNumberDevice);
                $updateStmt->bindParam(":device", $device);
                $updateStmt->bindParam(":deviceName", $deviceName);
                $updateStmt->bindParam(":sla", $sla);
                $updateStmt->bindParam(":kpi", $kpi);
                $updateStmt->bindParam(":repair_count", $repair_count);
                $updateStmt->bindParam(":close_date", $close_date);
                $updateStmt->bindParam(":department", $department);
                $updateStmt->bindParam(":id_ref", $id_ref);
                // if (!empty($close_date) && strtotime($close_date)) {
                $updateStmt->bindParam(":status", $statusTask);
                // }
                $updateStmt->execute();

                $conn->commit();
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จและอัปเดตเรียบร้อย";
                header("location: ../myjob.php");
            } else {
                throw new Exception("Insert into orderdata_new failed.");
            }
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION["error"] = "พบข้อผิดพลาด: " . $e->getMessage();
            header("location: ../myjob.php");
        }
    }
}

if (isset($_POST['save_with_work'])) {
    $numberWork = $_POST["numberWork"];
    $withdraw_id = $_POST["withdraw_id"];
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
    $id_ref = $_POST["id_ref"];

    $numberDevices = $_POST["number_device"];
    $update_number_device = $_POST["update_number_device"];
    $deleted_devices = $_POST["deleted_devices"];

    $lists = $_POST['list'];
    $qualities = $_POST['quality'];
    $amounts = $_POST['amount'];
    $prices = $_POST['price'];
    $units = $_POST['unit'];

    $update_lists = $_POST['update_list'];
    $update_qualities = $_POST['update_quality'];
    $update_amounts = $_POST['update_amount'];
    $update_prices = $_POST['update_price'];
    $update_units = $_POST['update_unit'];

    $deleted_items = $_POST['deleted_items'];

    // Additional fields from Bantext
    $date_report = $_POST['date_report'] ?? null;
    $time_report = $_POST['time_report'] ?? null;
    $take = $_POST['take'] ?? null;
    $problem = $_POST['problem'] ?? null;
    $description = $_POST['description'] ?? null;
    $withdraw = $_POST['withdraw'] ?? $_POST['withdraw2'] ?? null;
    $department = $_POST['department'] ?? null;
    $repair_count = $_POST['repair_count'] ?? null;
    $device = $_POST['device'] ?? null;
    $deviceName = $_POST['deviceName'] ?? null;
    $noteTask = $_POST["noteTask"] ?? null;
    $sla = $_POST['sla'] ?? null;
    $kpi = $_POST['kpi'] ?? null;
    $close_date = $_POST['close_date'] ?? null;
    $statusTask = 3;
    if (!empty($close_date) && strtotime($close_date)) {
        if (empty($device) || empty($problem) || empty($sla) || empty($kpi)) {
            $statusTask = 6; // Incomplete
        } else {
            $statusTask = 4; // Complete
        }
    }

    date_default_timezone_set('Asia/Bangkok');
    $timestamp = date('Y-m-d H:i:s');
    // echo '<pre>';
    // var_dump([
    //     'id_ref' => $id_ref,
    //     'numberWork' => $numberWork,
    //     'dateWithdraw' => $dateWithdraw,
    //     'refWithdraw' => $refWithdraw,
    //     'refWork' => $refWork,
    //     'refDevice' => $refDevice,
    //     'refDepart' => $refDepart,
    //     'refUsername' => $refUsername,
    //     'report' => $report,
    //     'reason' => $reason,
    //     'refOffer' => $refOffer,
    //     'quotation' => $quotation,
    //     'status' => $status,
    //     'note' => $note,

    //     'numberDevices' => $numberDevices,
    //     'update_number_device' => $update_number_device,
    //     'deleted_devices' => $deleted_devices,

    //     'lists' => $lists,
    //     'qualities' => $qualities,
    //     'amounts' => $amounts,
    //     'prices' => $prices,
    //     'units' => $units,

    //     'update_lists' => $update_lists,
    //     'update_qualities' => $update_qualities,
    //     'update_amounts' => $update_amounts,
    //     'update_prices' => $update_prices,
    //     'update_units' => $update_units,

    //     'deleted_items' => $deleted_items,

    //     //ขาด number_devices ของ task
    //     'close_date' => $close_date,
    //     'department' => $department,
    //     'deviceName' => $deviceName,
    //     'device' => $device,
    //     'description' => $description,
    //     'repair_count' => $repair_count,
    //     'withdraw' => $withdraw,
    //     'noteTask' => $noteTask,
    //     'sla' => $sla,
    //     'kpi' => $kpi,
    //     'problem' => $problem,
    // ]);
    // echo '</pre>';
    // exit();
    if (empty($refDepart)) {
        $_SESSION["error"] = "บันทีกข้อไม่สำเร็จ";
        $_SESSION["warning"] = "กรุณากดเลือกหน่วยงานหลังพิมพ์";
        header("Location: ../create.php");
    } else if (!$_SESSION['error'] && !$_SESSION['warning']) {
        try {
            $conn->beginTransaction();
            $sql = "UPDATE orderdata_new 
        SET numberWork = :numberWork,
            dateWithdraw = :dateWithdraw,
            refWithdraw = :refWithdraw,
            refWork = :refWork,
            refDevice = :refDevice,
            reason = :reason,
            report = :report,
            refDepart = :refDepart,
            refUsername = :refUsername,
            refOffer = :refOffer,
            quotation = :quotation,
            note = :note,
            id_ref = :id_ref
        WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $withdraw_id);
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
            $stmt->bindParam(':id_ref', $id_ref);

            if ($stmt->execute()) {
                if (!empty($_POST['list'])) {
                    foreach ($_POST['list'] as $modalId => $modalLists) {
                        foreach ($modalLists as $index => $list) {
                            $sql = "INSERT INTO order_items (order_id, list, quality, amount, price, unit)  
                                    VALUES (:order_id, :list, :quality, :amount, :price, :unit)";
                            $stmt = $conn->prepare($sql);

                            $stmt->bindParam(':order_id', $withdraw_id, PDO::PARAM_INT);
                            $stmt->bindParam(':list', $list, PDO::PARAM_INT);
                            $stmt->bindParam(':quality', $_POST['quality'][$modalId][$index], PDO::PARAM_STR);
                            $stmt->bindParam(':amount', $_POST['amount'][$modalId][$index], PDO::PARAM_INT);
                            $stmt->bindParam(':price', $_POST['price'][$modalId][$index], PDO::PARAM_STR);
                            $stmt->bindParam(':unit', $_POST['unit'][$modalId][$index], PDO::PARAM_STR);

                            $stmt->execute();
                        }
                    }
                }

                if (!empty($_POST['update_list'])) {
                    foreach ($_POST['update_list'] as $modalId => $updateLists) {
                        foreach ($updateLists as $recordId => $list) {
                            $sql = "UPDATE order_items 
                                    SET list = :list, quality = :quality, amount = :amount, price = :price, unit = :unit 
                                    WHERE id = :id";
                            $stmt = $conn->prepare($sql);

                            $stmt->bindParam(':list', $list, PDO::PARAM_STR);
                            $stmt->bindParam(':quality', $_POST['update_quality'][$modalId][$recordId], PDO::PARAM_STR);
                            $stmt->bindParam(':amount', $_POST['update_amount'][$modalId][$recordId], PDO::PARAM_INT);
                            $stmt->bindParam(':price', $_POST['update_price'][$modalId][$recordId], PDO::PARAM_STR);
                            $stmt->bindParam(':unit', $_POST['update_unit'][$modalId][$recordId], PDO::PARAM_STR);
                            $stmt->bindParam(':id', $recordId, PDO::PARAM_INT);

                            $stmt->execute();
                        }
                    }
                }

                if (!empty($_POST['deleted_items'])) {
                    foreach ($_POST['deleted_items'] as $modalId => $items) {
                        foreach ($items as $recordId => $value) {
                            $sql = "UPDATE order_items SET is_deleted = 1 WHERE id = :id";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':id', $recordId, PDO::PARAM_INT);
                            $stmt->execute();
                        }
                    }
                }

                if (!empty($_POST['update_number_device'])) {
                    $firstNumberDevice = null;
                    foreach ($_POST['update_number_device'] as $orderItem => $devices) {
                        foreach ($devices as $deviceId => $numberDevice) {
                            if ($firstNumberDevice === null) {
                                $firstNumberDevice = $numberDevice;
                            }

                            $sql = "UPDATE order_numberdevice SET numberDevice = :numberDevice WHERE id = :id";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':numberDevice', $numberDevice);
                            $stmt->bindParam(':id', $deviceId, PDO::PARAM_INT);
                            $stmt->execute();
                        }
                    }
                } else {
                    $firstNumberDevice = null;
                }

                // Handle deletions
                if (!empty($_POST['deleted_devices'])) {
                    foreach ($_POST['deleted_devices'] as $orderItem => $devices) {
                        foreach ($devices as $deviceId => $numberDevice) {
                            $sql = "UPDATE order_numberdevice SET is_deleted = 1 WHERE id = :id";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':id', $deviceId, PDO::PARAM_INT);
                            $stmt->execute();
                        }
                    }
                }

                // Handle new insertions
                if (!empty($_POST['number_device'])) {
                    foreach ($_POST['number_device'] as $orderItem => $devices) {
                        foreach ($devices as $numberDevice) {
                            if (trim($numberDevice) === "") {
                                continue;
                            }

                            $sql = "INSERT INTO order_numberdevice (order_item, numberDevice) VALUES (:order_item, :numberDevice)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':order_item', $withdraw_id, PDO::PARAM_INT);
                            $stmt->bindParam(':numberDevice', $numberDevice);
                            $stmt->execute();
                        }
                    }
                }

                //     $statusSql = "UPDATE order_status 
                //   SET status = :status, timestamp = :timestamp 
                //   WHERE order_id = :order_id";
                //     $statusStmt = $conn->prepare($statusSql);
                //     $statusStmt->bindParam(':order_id', $withdraw_id, PDO::PARAM_INT);
                //     $statusStmt->bindParam(':status', $status, PDO::PARAM_STR);
                //     $statusStmt->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
                //     $statusStmt->execute();

                // Update additional fields in data_report if `id_ref` matches
                $checkSql = "SELECT numberWork FROM orderdata_new WHERE id_ref = :id_ref";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bindParam(":id_ref", $id_ref);
                $checkStmt->execute();

                if ($checkStmt->rowCount() > 0) {
                    $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
                    $withdraw = $result['numberWork'];
                }

                $updateSql = "UPDATE data_report 
                              SET date_report = :date_report, time_report = :time_report, take = :take,problem = :problem, description = :description, note = :note, withdraw = :withdraw,
                                  number_device = :number_device, device = :device, deviceName = :deviceName, sla = :sla, 
                                  kpi = :kpi, repair_count = :repair_count, close_date = :close_date, department = :department";

                // if (!empty($close_date) && strtotime($close_date)) {
                $updateSql .= ", status = :status";
                // }

                $updateSql .= " WHERE id = :id_ref";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bindParam(":date_report", $date_report);
                $updateStmt->bindParam(":time_report", $time_report);
                $updateStmt->bindParam(":take", $take);
                $updateStmt->bindParam(":problem", $problem);
                $updateStmt->bindParam(":description", $description);
                $updateStmt->bindParam(":note", $noteTask);
                $updateStmt->bindParam(":withdraw", $withdraw);
                $updateStmt->bindParam(":number_device", $firstNumberDevice);
                $updateStmt->bindParam(":device", $device);
                $updateStmt->bindParam(":deviceName", $deviceName);
                $updateStmt->bindParam(":sla", $sla);
                $updateStmt->bindParam(":kpi", $kpi);
                $updateStmt->bindParam(":repair_count", $repair_count);
                $updateStmt->bindParam(":close_date", $close_date);
                $updateStmt->bindParam(":department", $department);
                $updateStmt->bindParam(":id_ref", $id_ref);
                // if (!empty($close_date) && strtotime($close_date)) {
                $updateStmt->bindParam(":status", $statusTask);
                // }
                $updateStmt->execute();

                $conn->commit();
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จและอัปเดตเรียบร้อย";
                header("location: ../myjob.php");
            } else {
                throw new Exception("Insert into orderdata_new failed.");
            }
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION["error"] = "พบข้อผิดพลาด: " . $e->getMessage();
            header("location: ../myjob.php");
        }
    }
}

// if (isset($_POST['submit_with_work'])) {
//     // รับข้อมูลจากฟอร์ม
//     $numberWork = generateNumberWork($conn);
//     $dateWithdraw = $_POST["dateWithdraw"];
//     $refWithdraw = $_POST["ref_withdraw"];
//     $refWork = $_POST["ref_work"];
//     $refDevice = $_POST["ref_device"];
//     $reason = $_POST["reason"];
//     $report = $_POST["report"];
//     $refDepart = $_POST["depart_id"];
//     $refUsername = $_POST["ref_username"];
//     $refOffer = $_POST["ref_offer"];
//     $quotation = $_POST["quotation"];
//     $note = $_POST["note"];
//     $status = $_POST["status"];
//     $id_ref = $_POST["id_ref"];

//     $numberDevices = $_POST["number_device"];

//     // รับข้อมูลจากตารางในฟอร์ม
//     $lists = $_POST['list'];
//     $qualities = $_POST['quality'];
//     $amounts = $_POST['amount'];
//     $prices = $_POST['price'];
//     $units = $_POST['unit'];

//     if (empty($refDepart)) {
//         $_SESSION["error"] = "บันทีกข้อไม่สำเร็จ";
//         $_SESSION["warning"] = "กรุณากดเลือกหน่วยงานหลังพิมพ์";
//         header("Location: ../create.php");
//     } else if (!$_SESSION['error'] && !$_SESSION['warning']) {

//         try {
//             // Begin transaction
//             $conn->beginTransaction();

//             // Insert into orderdata_new table
//             $sql = "INSERT INTO orderdata_new (numberWork, dateWithdraw, refWithdraw, refWork, refDevice, reason, report, refDepart, refUsername, refOffer, quotation, note, status, id_ref) 
//                     VALUES (:numberWork, :dateWithdraw, :refWithdraw, :refWork, :refDevice, :reason, :report, :refDepart, :refUsername, :refOffer, :quotation, :note, :status, :id_ref)";
//             $stmt = $conn->prepare($sql);

//             $stmt->bindParam(':numberWork', $numberWork);
//             $stmt->bindParam(':dateWithdraw', $dateWithdraw);
//             $stmt->bindParam(':refWithdraw', $refWithdraw);
//             $stmt->bindParam(':refWork', $refWork);
//             $stmt->bindParam(':refDevice', $refDevice);
//             $stmt->bindParam(':reason', $reason);
//             $stmt->bindParam(':report', $report);
//             $stmt->bindParam(':refDepart', $refDepart);
//             $stmt->bindParam(':refUsername', $refUsername);
//             $stmt->bindParam(':refOffer', $refOffer);
//             $stmt->bindParam(':quotation', $quotation);
//             $stmt->bindParam(':note', $note);
//             $stmt->bindParam(':status', $status);
//             $stmt->bindParam(':id_ref', $id_ref);

//             if ($stmt->execute()) {
//                 $orderId = $conn->lastInsertId();

//                 // Insert into order_items table
//                 $itemSql = "INSERT INTO order_items (order_id, list, quality, amount, price, unit) 
//                             VALUES (:order_id, :list, :quality, :amount, :price, :unit)";
//                 $itemStmt = $conn->prepare($itemSql);

//                 foreach ($lists as $index => $list) {
//                     $itemStmt->bindParam(':order_id', $orderId);
//                     $itemStmt->bindParam(':list', $list);
//                     $itemStmt->bindParam(':quality', $qualities[$index]);
//                     $itemStmt->bindParam(':amount', $amounts[$index]);
//                     $itemStmt->bindParam(':price', $prices[$index]);
//                     $itemStmt->bindParam(':unit', $units[$index]);
//                     $itemStmt->execute();
//                 }

//                 // Insert into order_numberdevice table
//                 $deviceSql = "INSERT INTO order_numberdevice (order_item, numberDevice) VALUES (:order_item, :numberDevice)";
//                 $deviceStmt = $conn->prepare($deviceSql);

//                 foreach ($numberDevices as $numberDevice) {
//                     $deviceStmt->bindParam(':order_item', $orderId); // Assuming `order_item` links to `order_id`
//                     $deviceStmt->bindParam(':numberDevice', $numberDevice);
//                     $deviceStmt->execute();
//                 }

//                 // Commit transaction
//                 $conn->commit();

//                 $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
//                 header("location: ../create.php");
//             } else {
//                 throw new Exception("Insert into orderdata_new failed.");
//             }
//         } catch (Exception $e) {
//             // Rollback on error
//             $conn->rollBack();
//             $_SESSION["error"] = "พบข้อผิดพลาด: " . $e->getMessage();
//             header("location: ../create.php");
//         }
//     }
// }
// if (isset($_POST['submit'])) { //OLD CODE
//     // รับข้อมูลจากฟอร์ม
//     // $numberWork = $_POST["numberWork"];

//     $numberWork = generateNumberWork($conn);
//     $dateWithdraw = $_POST["dateWithdraw"];
//     $refWithdraw = $_POST["ref_withdraw"];
//     $refWork = $_POST["ref_work"];
//     $refDevice = $_POST["ref_device"];
//     $numberDevice1 = $_POST["number_device_1"];
//     $numberDevice2 = $_POST["number_device_2"];
//     $numberDevice3 = $_POST["number_device_3"];
//     $reason = $_POST["reason"];
//     $report = $_POST["report"];
//     $refDepart = $_POST["depart_id"];
//     $refUsername = $_POST["ref_username"];
//     $refOffer = $_POST["ref_offer"];
//     $quotation = $_POST["quotation"];
//     // $receiptDate = $_POST["receipt_date"];
//     // $deliveryDate = $_POST["delivery_date"];
//     // $closeDate = $_POST["close_date"];
//     $note = $_POST["note"];
//     $status = $_POST["status"];

//     $list1 = $_POST["list1"];



//     // ตรวจสอบว่า list1 มีค่าว่างหรือไม่
//     if (empty($list1)) {
//         $_SESSION["error"] = "กรุณาเลือกรายการ";
//         header("Location: ../create.php");
//     } else if ($list1 == "") {
//         $_SESSION["error"] = "กรุณาเลือกรายการ";
//         header("Location: ../create.php");
//     }
//     if ($refDepart == 0 || $refDepart == "" || $refDepart == "0" || $refDepart == null) {
//         $_SESSION["error"] = "บันทีกข้อไม่สำเร็จ";
//         $_SESSION["warning"] = "กรุณากดเลือกหน่วยงานหลังพิมพ์";
//         header("Location: ../create.php");
//     } else if (!$_SESSION['error'] && !$_SESSION['warning']) {


//         for ($i = 1; $i <= 15; $i++) {
//             ${"list$i"} = $_POST["list$i"];
//             ${"quality$i"} = $_POST["quality$i"];
//             ${"amount$i"} = $_POST["amount$i"];
//             ${"price$i"} = $_POST["price$i"];
//             ${"unit$i"} = $_POST["unit$i"];
//         }


//         try {
//             $sql = "INSERT INTO orderdata (numberWork, dateWithdraw, refWithdraw, refWork, refDevice, ";
//             for ($i = 1; $i <= 15; $i++) {
//                 $sql .= "list$i, quality$i, amount$i, price$i, unit$i";
//                 if ($i < 15) {
//                     $sql .= ", ";
//                 }
//             }
//             $sql .= ", reason, report, refDepart, refUsername, refOffer, quotation, note, status,numberDevice1,numberDevice2,numberDevice3)
//                 VALUES (:numberWork, :dateWithdraw, :refWithdraw, :refWork, :refDevice, ";
//             for ($i = 1; $i <= 15; $i++) {
//                 $sql .= ":list$i, :quality$i, :amount$i, :price$i, :unit$i";
//                 if ($i < 15) {
//                     $sql .= ", ";
//                 }
//             }
//             $sql .= ", :reason, :report, :refDepart, :refUsername, :refOffer, :quotation, :note, :status,:numberDevice1,:numberDevice2,:numberDevice3)";

//             // เตรียมและสร้าง statement
//             $stmt = $conn->prepare($sql);

//             // ผูกค่าข้อมูล
//             $stmt->bindParam(':numberWork', $numberWork);
//             $stmt->bindParam(':dateWithdraw', $dateWithdraw);
//             $stmt->bindParam(':refWithdraw', $refWithdraw);
//             $stmt->bindParam(':refWork', $refWork);
//             $stmt->bindParam(':refDevice', $refDevice);
//             for ($i = 1; $i <= 15; $i++) {
//                 $stmt->bindParam(":list$i", ${"list$i"});
//                 $stmt->bindParam(":quality$i", ${"quality$i"});
//                 $stmt->bindParam(":amount$i", ${"amount$i"});
//                 $stmt->bindParam(":price$i", ${"price$i"});
//                 $stmt->bindParam(":unit$i", ${"unit$i"});
//             }
//             $stmt->bindParam(':reason', $reason);
//             $stmt->bindParam(':report', $report);
//             $stmt->bindParam(':refDepart', $refDepart);
//             $stmt->bindParam(':refUsername', $refUsername);
//             $stmt->bindParam(':refOffer', $refOffer);
//             $stmt->bindParam(':quotation', $quotation);
//             // $stmt->bindParam(':receiptDate', $receiptDate);
//             // $stmt->bindParam(':deliveryDate', $deliveryDate);
//             // $stmt->bindParam(':closeDate', $closeDate);
//             $stmt->bindParam(':note', $note);
//             $stmt->bindParam(':status', $status);
//             $stmt->bindParam(':numberDevice1', $numberDevice1);
//             $stmt->bindParam(':numberDevice2', $numberDevice2);
//             $stmt->bindParam(':numberDevice3', $numberDevice3);

//             // ทำการเพิ่มข้อมูล
//             if ($stmt->execute()) {
//                 $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
//                 header("location: ../create.php");
//             } else {
//                 $_SESSION["error"] = "พบข้อผิดพลาด";
//                 header("location: ../create.php");
//             }
//         } catch (PDOException $e) {
//             echo "" . $e->getMessage() . "";
//         }
//     }
// }

if (isset($_POST['submitW'])) {
    // รับข้อมูลจากฟอร์ม
    $numberWork = $_POST["numberWork"];
    $dateWithdraw = $_POST["dateWithdraw"];
    $refWithdraw = $_POST["ref_withdraw"];
    $refWork = $_POST["ref_work"];
    $refDevice = $_POST["ref_device"];
    $numberDevice1 = $_POST["number_device_1"];
    $numberDevice2 = $_POST["number_device_2"];
    $numberDevice3 = $_POST["number_device_3"];
    $reason = $_POST["reason"];
    $report = $_POST["report"];
    $refDepart = $_POST["depart_id"];
    $refUsername = $_POST["ref_username"];
    $refOffer = $_POST["ref_offer"];
    $quotation = $_POST["quotation"];
    $note = $_POST["note"];
    $status = $_POST["status"];

    $list1 = $_POST["list1"];
    $id_ref = $_POST['id_ref'];


    if (empty($list1)) {
        $_SESSION["error"] = "กรุณาเลือกรายการ";
        header("Location: ../create.php");
    } else if ($list1 == "") {
        $_SESSION["error"] = "กรุณาเลือกรายการ";
        header("Location: ../create.php");
    }
    if ($refDepart == 0 || $refDepart == "" || $refDepart == "0" || $refDepart == null) {
        $_SESSION["error"] = "บันทีกข้อไม่สำเร็จ";
        $_SESSION["warning"] = "กรุณากดเลือกหน่วยงานหลังพิมพ์";
        header("Location: ../create.php");
    } else {
        try {

            $sql = "UPDATE orderdata SET 
numberWork = :numberWork, 
dateWithdraw = :dateWithdraw, 
refWithdraw = :refWithdraw, 
refWork = :refWork, 
refDevice = :refDevice,
reason = :reason, 
report = :report, 
refDepart = :refDepart, 
refUsername = :refUsername, 
refOffer = :refOffer, 
quotation = :quotation,
note = :note, 
status = :status,
numberDevice1 = :numberDevice1,
numberDevice2 = :numberDevice2,
numberDevice3 = :numberDevice3,
list1 = :list1, quality1 = :quality1, amount1 = :amount1, price1 = :price1, unit1 = :unit1,
            list2 = :list2, quality2 = :quality2, amount2 = :amount2, price2 = :price2, unit2 = :unit2,
            list3 = :list3, quality3 = :quality3, amount3 = :amount3, price3 = :price3, unit3 = :unit3,
            list4 = :list4, quality4 = :quality4, amount4 = :amount4, price4 = :price4, unit4 = :unit4,
            list5 = :list5, quality5 = :quality5, amount5 = :amount5, price5 = :price5, unit5 = :unit5,
            list6 = :list6, quality6 = :quality6, amount6 = :amount6, price6 = :price6, unit6 = :unit6,
            list7 = :list7, quality7 = :quality7, amount7 = :amount7, price7 = :price7, unit7 = :unit7,
            list8 = :list8, quality8 = :quality8, amount8 = :amount8, price8 = :price8, unit8 = :unit8,
            list9 = :list9, quality9 = :quality9, amount9 = :amount9, price9 = :price9, unit9 = :unit9,
            list10 = :list10, quality10 = :quality10, amount10 = :amount10, price10 = :price10, unit10 = :unit10,
            list11 = :list11, quality11 = :quality11, amount11 = :amount11, price11 = :price11, unit11 = :unit11,
            list12 = :list12, quality12 = :quality12, amount12 = :amount12, price12 = :price12, unit12 = :unit12,
            list13 = :list13, quality13 = :quality13, amount13 = :amount13, price13 = :price13, unit13 = :unit13,
            list14 = :list14, quality14 = :quality14, amount14 = :amount14, price14 = :price14, unit14 = :unit14,
            list15 = :list15, quality15 = :quality15, amount15 = :amount15, price15 = :price15, unit15 = :unit15
WHERE id_ref = :id_ref";

            // เตรียมและสร้าง statement
            $stmt = $conn->prepare($sql);

            // ผูกค่าข้อมูล
            $stmt->bindParam(':numberWork', $numberWork);
            $stmt->bindParam(':dateWithdraw', $dateWithdraw);
            $stmt->bindParam(':refWithdraw', $refWithdraw);
            $stmt->bindParam(':refWork', $refWork);
            $stmt->bindParam(':refDevice', $refDevice);
            for ($i = 1; $i <= 15; $i++) {
                $stmt->bindParam(":list$i", $_POST["list$i"]);
                $stmt->bindParam(":quality$i", $_POST["quality$i"]);
                $stmt->bindParam(":amount$i", $_POST["amount$i"]);
                $stmt->bindParam(":price$i", $_POST["price$i"]);
                $stmt->bindParam(":unit$i", $_POST["unit$i"]);
            }
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':report', $report);
            $stmt->bindParam(':refDepart', $refDepart);
            $stmt->bindParam(':refUsername', $refUsername);
            $stmt->bindParam(':refOffer', $refOffer);
            $stmt->bindParam(':quotation', $quotation);
            $stmt->bindParam(':note', $note);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':numberDevice1', $numberDevice1);
            $stmt->bindParam(':numberDevice2', $numberDevice2);
            $stmt->bindParam(':numberDevice3', $numberDevice3);
            $stmt->bindParam(':id_ref', $id_ref);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัปเดตข้อมูลสำเร็จ";
                header("location: ../create.php");
            } else {
                $_SESSION["error"] = "พบข้อผิดพลาดในการอัปเดต";
                header("location: ../create.php");
            }
        } catch (PDOException $e) {
            echo "" . $e->getMessage() . "";
        }
    }
}
if (isset($_POST['CheckAll'])) {
    $numberWork = generateNumberWork($conn) . ' S'; //gen numberWork โดยการเติมลงท้ายด้วย S
    $dateWithdraw = $_POST["dateWithdraw"]; //วันที่ปัจจุบัน
    $refWithdraw = 23;
    $refWork = 10;
    $refDevice = 43;
    $reason = $_POST['reason'];
    $report = "เบิกอะไหล่รายสัปดาห์ตามเอกสารแนบ";
    $refDepart = 3;
    $refUsername = $_POST["username"];
    $refOffer = 4;
    $quotation = "-";
    if ($_POST['update_status']) {
        $update_status = array_map('intval', $_POST['update_status']);
    }
    // $receiptDate = $_POST["dateWithdraw"];
    // $deliveryDate = $_POST["dateWithdraw"];
    // $closeDate = $_POST["dateWithdraw"];
    $note = "-";
    $status = 3;
    date_default_timezone_set('Asia/Bangkok');
    $timestamp = date('Y-m-d H:i:s');

    $lists = $_POST['list'];
    $qualities = $_POST['quality'];
    $amounts = $_POST['amount'];
    $prices = $_POST['price'];
    $units = $_POST['unit'];

    // echo '<pre>';
    // var_dump([
    //     '$_POST[update_status]' => $_POST['update_status'],
    //     '$update_status' => $update_status,
        
    //     'numberWork' => $numberWork,
    //     'dateWithdraw' => $dateWithdraw,
    //     'refUsername' => $refUsername,
    //     'refWithdraw' => $refWithdraw,
    //     'refWork' => $refWork,
    //     'refDevice' => $refDevice,
    //     'reason' => $reason,
    //     'report' => $report,
    //     'refDepart' => $refDepart,
    //     'refOffer' => $refOffer,
    //     'quotation' => $quotation,
    //     'status' => $status,
    //     'note' => $note,

    //     'lists' => $lists,
    //     'qualities' => $qualities,
    //     'amounts' => $amounts,
    //     'prices' => $prices,
    // ]);
    // echo '</pre>';
    // exit();

    try {
        $conn->beginTransaction();
        $sql = "INSERT INTO orderdata_new (numberWork, dateWithdraw, refWithdraw, refWork, refDevice, reason, report, refDepart, refUsername, refOffer, quotation, note) 
                    VALUES (:numberWork, :dateWithdraw, :refWithdraw, :refWork, :refDevice, :reason, :report, :refDepart, :refUsername, :refOffer, :quotation, :note)";

        // เตรียมและสร้าง statement
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

        // ทำการเพิ่มข้อมูล
        if ($stmt->execute()) {
            $orderId = $conn->lastInsertId();

            $itemSql = "INSERT INTO order_items (order_id, list, quality, amount, price
            , unit
            ) 
            VALUES (:order_id, :list, :quality, :amount, :price
            , :unit
            )";
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

            $statusSql = "INSERT INTO order_status (order_id, status, timestamp) 
                VALUES (:order_id, :status, :timestamp)";
            $statusStmt = $conn->prepare($statusSql);
            $statusStmt->bindParam(':order_id', $orderId);
            $statusStmt->bindParam(':status', $status);
            $statusStmt->bindParam(':timestamp', $timestamp);
            $statusStmt->execute();
            //-------------------------------
            if ($_POST['update_status']) {
                $update_status_Sql = "INSERT INTO order_status (order_id, status, timestamp) VALUES (:order_id, :status, :timestamp)";
                $update_status_Stmt = $conn->prepare($update_status_Sql);

                foreach ($update_status as $order_id) {
                    $update_status_Stmt->execute([
                        'order_id' => $order_id,
                        'status' => $status,
                        'timestamp' => $timestamp
                    ]);
                }
            }
            //-------------------------------
            $conn->commit();

            $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
            $_SESSION["warning"] = "เมื่อเพิ่มข้อมูลเรียบร้อยแล้ว หลังจากบันทึกแล้วกรุณาปิดงานด้วย";
            header("location: ../checkAll.php");
        } else {
            $_SESSION["error"] = "พบข้อผิดพลาด";
            header("location: ../checkAll.php");
        }
    } catch (PDOException $e) {
        echo "" . $e->getMessage() . "";
    }
}
if (isset($_POST['takeaway'])) { // เพิ่ม รายการอุปกรณ์
    $id = $_POST['id'];
    $username = $_POST['username'];
    $take = $_POST['take'];
    $status = 2;
    try {
        $sql = "UPDATE data_report SET username = :username, take = :take, status = :status WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":take", $take);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            $_SESSION["success"] = "รับงานเรียบร้อยแล้ว";
            header("location: ../myjob.php");
        } else {
            $_SESSION["error"] = "พบข้อผิดพลาด";
            header("location: ../dashboard.php");
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['inTime'])) { // เพิ่ม รายการอุปกรณ์
    $id = $_POST['id'];

    $status = 2;
    try {
        $sql = "UPDATE data_report SET status = :status WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            $_SESSION["success"] = "กำลังดำเนินการ";
            header("location: ../myjob.php");
        } else {
            $_SESSION["error"] = "พบข้อผิดพลาด";
            header("location: ../myjob.php");
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['withdrawSubmit'])) { // เพิ่ม รายการอุปกรณ์
    $id = $_POST['id'];
    $id_ref = $_POST['id_ref'];
    $problem = $_POST['problem'];
    $description = $_POST['description'];
    $withdraw = $_POST['withdraw'];
    $note = $_POST['note'];
    $device = $_POST['device'];
    $deviceName = $_POST['deviceName'];
    $sla = $_POST['sla'];
    $kpi = $_POST['kpi'];
    $status = 3;
    try {
        $select = "SELECT * FROM orderdata WHERE id_ref = :id";
        $stmt_select = $conn->prepare($select);
        $stmt_select->bindParam(":id", $id);
        $stmt_select->execute();
        $result = $stmt_select->fetchAll(PDO::FETCH_ASSOC);

        if ($stmt_select->rowCount() == 0) {
            $sql2 = "INSERT INTO orderdata(id_ref,numberWork) VALUES(:id,:numberWork)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(":id", $id);
            $stmt2->bindParam(":numberWork", $withdraw);
            $stmt2->execute();
        } else {
            $sql2 = "UPDATE orderdata SET numberWork = :numberWork WHERE id_ref = :id";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(":id", $id);
            $stmt2->bindParam(":numberWork", $withdraw);
            $stmt2->execute();
        }
        $sql = "UPDATE data_report SET problem = :problem, description = :description , withdraw = :withdraw, status = :status , device = :device, deviceName = :deviceName,sla = :sla,kpi = :kpi,note = :note WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":problem", $problem);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":withdraw", $withdraw);
        $stmt->bindParam(":device", $device);
        $stmt->bindParam(":deviceName", $deviceName);
        $stmt->bindParam(":sla", $sla);
        $stmt->bindParam(":kpi", $kpi);
        $stmt->bindParam(":note", $note);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            $_SESSION["success"] = "เพิ่มอะไหล่";
            header("location: ../create.php?withdraw=$id");
        } else {
            $_SESSION["error"] = "พบข้อผิดพลาด";
            header("location: ../myjob.php");
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['disWork'])) {
    $id = $_POST['id'];
    $problem = $_POST['problem'];
    $description = $_POST['description'];
    $withdraw = $_POST['withdraw'];

    if ($withdraw == "" || empty($withdraw)) {
        $withdraw = $_POST['withdraw2'];
    }
    $note = $_POST['note'];
    $device = $_POST['device'];
    $deviceName = $_POST['deviceName'];
    $sla = $_POST['sla'];
    $kpi = $_POST['kpi'];
    $username = "";
    $status = 0;
    try {
        $sql = "UPDATE data_report SET problem = :problem, description = :description , withdraw = :withdraw, status = :status , device = :device, deviceName = :deviceName,sla = :sla,kpi = :kpi,note = :note, username = :username WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":problem", $problem);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":withdraw", $withdraw);
        $stmt->bindParam(":device", $device);
        $stmt->bindParam(":deviceName", $deviceName);
        $stmt->bindParam(":sla", $sla);
        $stmt->bindParam(":kpi", $kpi);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":note", $note);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            $_SESSION["success"] = "คืนงานเรียบร้อยแล้ว";
            header("location: ../dashboard.php");
        } else {
            $_SESSION["error"] = "พบข้อผิดพลาด";
            header("location: ../myjob.php");
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['CloseSubmit'])) {
    $id = $_POST['id'];
    $date_report = $_POST['date_report'];
    $time_report = $_POST['time_report'];
    $take = $_POST['take'];
    $problem = $_POST['problem'];
    $description = $_POST['description'];
    $note = $_POST['noteTask'];
    $department = $_POST['department'];
    $device = $_POST['device'];
    $deviceName = $_POST['deviceName'];
    $sla = $_POST['sla'];
    $kpi = $_POST['kpi'];
    $number_device = $_POST['number_devices'];
    $repair_count = $_POST['repair_count'];
    // echo '<pre>';
    // var_dump([
    //     'close_date' => $_POST['close_date'],
    // ]);
    // echo '</pre>';
    // exit();
    // Get current date and time
    date_default_timezone_set('Asia/Bangkok');
    // Check if 'close_date' from POST is empty or null
    if (empty($_POST['close_date'])) {
        // If empty, use the current date and time
        $close_date = date('Y-m-d H:i:s');
    } else {
        // Otherwise, use the provided 'close_date'
        $close_date = $_POST['close_date'];
    }

    if (empty($device) || empty($problem) || empty($sla) || empty($kpi)) {
        $status = 6;
    } else {
        $status = 4;
    }

    try {
        $sql = "UPDATE data_report 
                SET date_report = :date_report, time_report = :time_report, take = :take,problem = :problem, 
                    description = :description, 
                    device = :device, 
                    deviceName = :deviceName, 
                    sla = :sla, 
                    kpi = :kpi, 
                    number_device = :number_device,
                    repair_count = :repair_count,
                    close_date = :close_date, 
                    note = :note, 
                    status = :status, 
                    department = :department 
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":date_report", $date_report);
        $stmt->bindParam(":time_report", $time_report);
        $stmt->bindParam(":take", $take);
        $stmt->bindParam(":problem", $problem);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":device", $device);
        $stmt->bindParam(":deviceName", $deviceName);
        $stmt->bindParam(":sla", $sla);
        $stmt->bindParam(":number_device", $number_device);
        $stmt->bindParam(":repair_count", $repair_count);
        $stmt->bindParam(":kpi", $kpi);
        $stmt->bindParam(":close_date", $close_date);
        $stmt->bindParam(":note", $note);
        $stmt->bindParam(":department", $department);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            $_SESSION["success"] = "เสร็จงานเรียบร้อยแล้ว";
            header("location: ../myjob.php");
        } else {
            $_SESSION["error"] = "พบข้อผิดพลาด";
            header("location: ../myjob.php");
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['clam'])) {
    $id = $_POST['id'];
    $problem = $_POST['problem'];
    $description = $_POST['description'];
    $note = $_POST['note'];
    $device = $_POST['device'];
    $deviceName = $_POST['deviceName'];
    $sla = $_POST['sla'];
    $kpi = $_POST['kpi'];
    $status = 5;
    try {
        $sql = "UPDATE data_report SET problem = :problem, description = :description , device = :device, deviceName = :deviceName, sla = :sla, kpi = :kpi, note = :note , status = :status WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":problem", $problem);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":device", $device);
        $stmt->bindParam(":deviceName", $deviceName);
        $stmt->bindParam(":sla", $sla);
        $stmt->bindParam(":kpi", $kpi);
        $stmt->bindParam(":note", $note);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            $_SESSION["success"] = "เสร็จงานเรียบร้อยแล้ว";
            header("location: ../myjob.php");
        } else {
            $_SESSION["error"] = "พบข้อผิดพลาด";
            header("location: ../myjob.php");
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['Bantext'])) {
    $id = $_POST['id'];
    $date_report = $_POST['date_report'];
    $time_report = $_POST['time_report'];
    $take = $_POST['take'];
    $problem = $_POST['problem'];
    $description = $_POST['description'];
    $withdraw = $_POST['withdraw'];

    if ($withdraw == "" || empty($withdraw)) {
        $withdraw = $_POST['withdraw2'];
    }

    $note = $_POST['noteTask'];
    $department = $_POST['department'];
    $number_device = $_POST['number_devices'];
    $repair_count = $_POST['repair_count'];
    $device = $_POST['device'];
    $deviceName = $_POST['deviceName'];
    $sla = $_POST['sla'];
    $kpi = $_POST['kpi'];
    $close_date = $_POST['close_date'];

    // Determine the status only if close_date is provided
    $status = null;
    if (!empty($close_date) && strtotime($close_date)) {
        if (empty($device) || empty($problem) || empty($sla) || empty($kpi)) {
            $status = 6; // Incomplete
        } else {
            $status = 4; // Complete
        }
    }

    try {
        // Check if id_ref exists in orderdata_new and get the corresponding numberWork
        $checkSql = "SELECT numberWork FROM orderdata_new WHERE id_ref = :id_ref";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(":id_ref", $id);
        $checkStmt->execute();

        // If a record is found, fetch the numberWork and set it to the withdraw field
        if ($checkStmt->rowCount() > 0) {
            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
            $withdraw = $result['numberWork'];
        }

        // Prepare the SQL query dynamically
        $sql = "UPDATE data_report 
                SET date_report = :date_report, time_report = :time_report, take = :take,
                    problem = :problem, 
                    description = :description, 
                    note = :note, 
                    withdraw = :withdraw, 
                    number_device = :number_device, 
                    device = :device, 
                    deviceName = :deviceName, 
                    sla = :sla, 
                    kpi = :kpi, 
                    repair_count = :repair_count,
                    close_date = :close_date, 
                    department = :department";

        // Append status update only if close_date is provided
        if (!empty($close_date) && strtotime($close_date)) {
            $sql .= ", status = :status";
        }

        $sql .= " WHERE id = :id";
        $stmt = $conn->prepare($sql);

        // Bind common parameters
        $stmt->bindParam(":date_report", $date_report);
        $stmt->bindParam(":time_report", $time_report);
        $stmt->bindParam(":take", $take);
        $stmt->bindParam(":problem", $problem);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":note", $note);
        $stmt->bindParam(":withdraw", $withdraw);
        $stmt->bindParam(":number_device", $number_device);
        $stmt->bindParam(":repair_count", $repair_count);
        $stmt->bindParam(":device", $device);
        $stmt->bindParam(":deviceName", $deviceName);
        $stmt->bindParam(":sla", $sla);
        $stmt->bindParam(":kpi", $kpi);
        $stmt->bindParam(":close_date", $close_date);
        $stmt->bindParam(":department", $department);
        $stmt->bindParam(":id", $id);

        // Bind status only if it is included in the query
        if (!empty($close_date) && strtotime($close_date)) {
            $stmt->bindParam(":status", $status);
        }

        if ($stmt->execute()) {
            $_SESSION["success"] = "บันทึกเรียบร้อยแล้ว";
            header("location: ../myjob.php");
        } else {
            $_SESSION["error"] = "พบข้อผิดพลาด";
            header("location: ../myjob.php");
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['saveWork'])) {
    $time_report = $_POST['time_report'];
    $date_report = $_POST['date_report'];
    $device = $_POST['device'];
    $deviceName = $_POST['deviceName'];
    $number_device = $_POST['number_device'];
    $ip_address = $_POST['ip_address'];
    $report = $_POST['report'];
    $reporter = $_POST['reporter'];
    $department = $_POST['depart_id'];
    $tel = $_POST['tel'];
    $take = $_POST['take'];  // Placeholder for the 'take' field
    $problem = $_POST['problem'];
    $description = $_POST['description'];
    $withdraw = $_POST['withdraw'];
    $close_date = $_POST['close_date'];
    $countList = $_POST['countList'];
    $status = 0;
    $create_by = $_POST['create_by'];
    // if ($department == "" || $department = null) {
    //     $_SESSION['error'] = "หน่วยงานมีค่าว่าง กรุณากรอกใหม่อีกครั้ง";
    //     header("location: ../myjob.php");
    //     exit();
    // }

    try {
        if (empty($department)) {
            $_SESSION['error'] = "หน่วยงานมีค่าว่าง กรุณากรอกใหม่อีกครั้ง";
            header("location: ../myjob.php"); //validate ให้ alert ดีกว่า
            exit();
        }

        $sql = "INSERT INTO data_report(time_report, date_report, device, number_device, ip_address, report, reporter, department, tel, take, problem, description, withdraw, close_date, status,deviceName ,create_by) 
                VALUES (:time_report, :date_report, :device, :number_device, :ip_address, :report, :reporter, :department, :tel, :take, :problem, :description, :withdraw, :close_date, :status,:deviceName,:create_by)";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":time_report", $time_report);
        $stmt->bindParam(":date_report", $date_report);
        $stmt->bindParam(":device", $device);
        $stmt->bindParam(":number_device", $number_device);
        $stmt->bindParam(":ip_address", $ip_address);
        $stmt->bindParam(":report", $report);
        $stmt->bindParam(":reporter", $reporter);
        $stmt->bindParam(":department", $department);
        $stmt->bindParam(":tel", $tel);
        $stmt->bindParam(":take", $take);
        $stmt->bindParam(":problem", $problem);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":withdraw", $withdraw);
        $stmt->bindParam(":close_date", $close_date);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":deviceName", $deviceName);
        $stmt->bindParam(":create_by", $create_by);
        for ($i = 1; $i <= $countList; $i++) {
            if ($stmt->execute()) {
                $_SESSION['success'] = "เพิ่มงานเรียบร้อยแล้ว";
                header("location: ../dashboard.php");
            } else {
                $_SESSION['error'] = "พบข้อผิดพลาดบางอย่าง";
                header("location: ../myjob.php");
            }
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
if (isset($_POST['saveWorkSuccess'])) {
    $time_report = $_POST['time_report'];
    $date_report = $_POST['date_report'];
    $device = $_POST['device'];
    $deviceName = $_POST['deviceName'];
    $number_device = $_POST['number_device'];
    $ip_address = $_POST['ip_address'];
    $report = $_POST['report'];
    $reporter = $_POST['reporter'];
    $department = $_POST['depart_id'];
    $tel = $_POST['tel'];
    $take = $_POST['take'];  // Placeholder for the 'take' field
    $problem = $_POST['problem'];
    $description = $_POST['description'];
    $withdraw = $_POST['withdraw'];
    $close_date = $_POST['close_date'];
    $username = $_POST['username'];
    $status = 4;

    try {
        $sql = "INSERT INTO data_report(time_report, date_report, device, number_device, ip_address, report, reporter, department, tel, take, problem, description, withdraw, close_date, status,username,deviceName) 
                VALUES (:time_report, :date_report, :device, :number_device, :ip_address, :report, :reporter, :department, :tel, :take, :problem, :description, :withdraw, :close_date, :status,:username,:deviceName)";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":time_report", $time_report);
        $stmt->bindParam(":date_report", $date_report);
        $stmt->bindParam(":device", $device);
        $stmt->bindParam(":number_device", $number_device);
        $stmt->bindParam(":ip_address", $ip_address);
        $stmt->bindParam(":report", $report);
        $stmt->bindParam(":reporter", $reporter);
        $stmt->bindParam(":department", $department);
        $stmt->bindParam(":tel", $tel);
        $stmt->bindParam(":take", $take);
        $stmt->bindParam(":problem", $problem);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":withdraw", $withdraw);
        $stmt->bindParam(":close_date", $close_date);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":deviceName", $deviceName);

        if ($stmt->execute()) {
            $_SESSION['success'] = "เพิ่มงานเรียบร้อยแล้ว";
            header("location: ../myjob.php");
        } else {
            $_SESSION['error'] = "พบข้อผิดพลาดบางอย่าง";
            header("location: ../myjob.php");
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
