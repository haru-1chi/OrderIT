<?php
session_start();
require_once '../config/db.php';

if (isset($_POST['withdraw'])) {
    $withdraw_name = $_POST['withdraw_name'];
    $withdraw_id = $_POST['withdraw_id'];
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
            $sql = "UPDATE withdraw SET withdraw_name = :withdraw_name WHERE withdraw_id = :withdraw_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":withdraw_id", $withdraw_id);
            $stmt->bindParam(":withdraw_name", $withdraw_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['work'])) {
    $work_name = $_POST['work_name'];
    $work_id = $_POST['work_id'];
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
            $sql = "UPDATE listwork SET work_name = :work_name WHERE work_id = :work_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":work_id", $work_id);
            $stmt->bindParam(":work_name", $work_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['device'])) {
    $device_name = $_POST['device_name'];
    $device_id = $_POST['device_id'];
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
            $sql = "UPDATE device SET device_name = :device_name WHERE device_id = :device_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":device_id", $device_id);
            $stmt->bindParam(":device_name", $device_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['models'])) {
    $models_name = $_POST['models_name'];
    $models_id = $_POST['models_id'];
    $quality = $_POST['quality'];
    $price = $_POST['price'];
    $unit = $_POST['unit'];
    try {

        if (!isset($_SESSION['error'])) {
            $sql = "UPDATE device_models SET models_name = :models_name , quality = :quality , price = :price , unit = :unit WHERE models_id = :models_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":models_id", $models_id);
            $stmt->bindParam(":models_name", $models_name);
            $stmt->bindParam(":quality", $quality);
            $stmt->bindParam(":price", $price);
            $stmt->bindParam(":unit", $unit);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['depart'])) {
    $depart_name = $_POST['depart_name'];
    $depart_id = $_POST['depart_id'];
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
            $sql = "UPDATE depart SET depart_name = :depart_name WHERE depart_id = :depart_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":depart_id", $depart_id);
            $stmt->bindParam(":depart_name", $depart_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['offer'])) {
    $offer_name = $_POST['offer_name'];
    $offer_id = $_POST['offer_id'];
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
            $sql = "UPDATE offer SET offer_name = :offer_name WHERE offer_id = :offer_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":offer_id", $offer_id);
            $stmt->bindParam(":offer_name", $offer_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['working'])) {
    $workingName = $_POST['workingName'];
    $id = $_POST['id'];
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
            $sql = "UPDATE workinglist SET workingName = :workingName WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":workingName", $workingName);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['problemL'])) {
    $problemName = $_POST['problemName'];
    $id = $_POST['id'];
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
            $sql = "UPDATE problemlist SET problemName = :problemName WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":problemName", $problemName);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['updateData'])) {
    $numberWork = $_POST['numberWork']; //ตอนนี้เป็น numberWork ให้เปลี่ยนเป็น id ใบเบิก
    $report = $_POST['report'];
    $reason = $_POST['reason'];
    $note = $_POST['note'];

    $numberDevices = $_POST["device_numbers"];
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

     echo '<pre>';
    var_dump([
        'numberWork' => $numberWork,
        'report' => $report,
        'reason' => $reason,
        'note' => $note,

        'numberDevices' => $numberDevices,
        'update_number_device' => $update_number_device,
        'deleted_devices' => $deleted_devices,

        'lists' => $lists,
        'qualities' => $qualities,
        'amounts' => $amounts,
        'prices' => $prices,
        'units' => $units,

        'update_lists' => $update_lists,
        'update_qualities' => $update_qualities,
        'update_amounts' => $update_amounts,
        'update_prices' => $update_prices,
        'update_units' => $update_units,

        'deleted_items' => $deleted_items,
    ]);
    echo '</pre>';
    exit();
    // สร้าง SQL UPDATE statement
    $sql = "UPDATE orderdata SET
            numberDevice1 = :numberDevice1,
            numberDevice2 = :numberDevice2,
            numberDevice3 = :numberDevice3,
            report = :report,
            reason = :reason,
            note = :note,
            status = :status
            WHERE id = :id";

    // เตรียมและ execute statement
    $stmt = $conn->prepare($sql);

    // bind id parameter
    $stmt->bindParam(":numberDevice1", $numberDevice1);
    $stmt->bindParam(":numberDevice2", $numberDevice2);
    $stmt->bindParam(":numberDevice3", $numberDevice3);
    $stmt->bindParam(":report", $report);
    $stmt->bindParam(":reason", $reason);
    $stmt->bindParam(":note", $note);
    $stmt->bindParam(":id", $id);
    // execute statement
    $stmt->execute();

    $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
    $_SESSION["warning"] = "กรุณาตรวจสอบสถานะของใบงานอีกครั้ง";
    header("location: ../check.php?numberWork=$id");
}
if (isset($_POST['updateStatus'])) {
    // Check if any checkboxes are checked
    if (empty($_POST['selectedRows'])) {
        $_SESSION["error"] = "ไม่พบข้อมูลที่เลือก";
        header("location: ../checkAll.php");
        exit();
    }

    // Convert selected rows to integers
    $selectedRows = array_map('intval', $_POST['selectedRows']);

    // ทำการอัพเดทฐานข้อมูลสำหรับทุกรายการที่เลือก
    $sql = "UPDATE orderdata SET deliveryDate = :deliveryDate, status = 3 WHERE id = :id";
    $stmt = $conn->prepare($sql);

    // ย้ายการผูกพารามิเตอร์ไปนอกลูป
    $stmt->bindParam(':deliveryDate', $deliveryDate);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the SQL statement for each selectedRow
    foreach ($selectedRows as $selectedRow) {
        $deliveryDate = $_POST["deliveryDate"][$selectedRow];
        $id = $selectedRow;

        // Execute the SQL statement for each selectedRow
        if ($stmt->execute()) {
            $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
        } else {
            $_SESSION["error"] = "พบข้อผิดพลาด: " . $stmt->errorInfo()[2];
        }
    }

    // Redirect after all updates are done
    header("location: ../checkAll.php");
    exit();
}
if (isset($_POST['updateStatus3'])) {
    // ย้ายนี้ออกจากลูป


    // ตรวจสอบว่ามี checkbox ไหนถูกเลือกหรือไม่
    if (empty($_POST['selectedRows3'])) {
        $_SESSION["error"] = "ไม่พบข้อมูลที่เลือก";
        header("location: ../checkAll.php");
        exit();
    }

    // แปลง ID ที่เลือกเป็นตัวเลข
    $selectedRows = array_map('intval', $_POST['selectedRows3']);

    // ทำการอัพเดทฐานข้อมูลสำหรับทุกรายการที่เลือก
    $sql = "UPDATE orderdata SET status = 4 WHERE id IN (" . implode(',', $selectedRows) . ")";
    $stmt = $conn->prepare($sql);

    // ย้ายการผูกพารามิเตอร์ไปนอกลูป


    // ทำการ execute คำสั่ง SQL
    if ($stmt->execute()) {
        $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
    } else {
        $_SESSION["error"] = "พบข้อผิดพลาด";
    }

    // Redirect หลังจากที่ทำการอัพเดทเสร็จสิ้น
    header("location: ../checkAll.php");
    exit();
}
if (isset($_POST['updateStatus4'])) {
    // Check if any checkboxes are checked
    if (empty($_POST['selectedRows4'])) {
        $_SESSION["error"] = "ไม่พบข้อมูลที่เลือก";
        header("location: ../checkAll.php");
        exit();
    }

    // Convert selected rows to integers
    $selectedRows = array_map('intval', $_POST['selectedRows4']);

    // ทำการอัพเดทฐานข้อมูลสำหรับทุกรายการที่เลือก
    $sql = "UPDATE orderdata SET closeDate = :closeDate, status = 5 WHERE id = :id";
    $stmt = $conn->prepare($sql);

    // ย้ายการผูกพารามิเตอร์ไปนอกลูป
    $stmt->bindParam(':closeDate', $closeDate);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the SQL statement for each selectedRow
    foreach ($selectedRows as $selectedRow) {
        $closeDate = $_POST["close_date"][$selectedRow];
        $id = $selectedRow;

        // Execute the SQL statement for each selectedRow
        if ($stmt->execute()) {
            $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
        } else {
            $_SESSION["error"] = "พบข้อผิดพลาด: " . $stmt->errorInfo()[2];
        }
    }

    // Redirect after all updates are done
    header("location: ../checkAll.php");
    exit();
}


if (isset($_POST['updateStatus5'])) {
    // ย้ายนี้ออกจากลูป


    // ตรวจสอบว่ามี checkbox ไหนถูกเลือกหรือไม่
    if (empty($_POST['selectedRows5'])) {
        $_SESSION["error"] = "ไม่พบข้อมูลที่เลือก";
        header("location: ../checkAll.php");
        exit();
    }

    // แปลง ID ที่เลือกเป็นตัวเลข
    $selectedRows = array_map('intval', $_POST['selectedRows5']);

    // ทำการอัพเดทฐานข้อมูลสำหรับทุกรายการที่เลือก
    $sql = "UPDATE orderdata SET status = 5 WHERE id IN (" . implode(',', $selectedRows) . ")";
    $stmt = $conn->prepare($sql);

    // ย้ายการผูกพารามิเตอร์ไปนอกลูป


    // ทำการ execute คำสั่ง SQL
    if ($stmt->execute()) {
        $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
    } else {
        $_SESSION["error"] = "พบข้อผิดพลาด";
    }

    // Redirect หลังจากที่ทำการอัพเดทเสร็จสิ้น
    header("location: ../checkAll.php");
    exit();
}
if (isset($_POST['sla'])) {
    $sla_name = $_POST['sla_name'];
    $sla_id = $_POST['sla_id'];
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
            $sql = "UPDATE sla SET sla_name = :sla_name WHERE sla_id = :sla_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":sla_id", $sla_id);
            $stmt->bindParam(":sla_name", $sla_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['kpi'])) {
    $kpi_name = $_POST['kpi_name'];
    $kpi_id = $_POST['kpi_id'];
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
            $sql = "UPDATE kpi SET kpi_name = :kpi_name WHERE kpi_id = :kpi_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":kpi_id", $kpi_id);
            $stmt->bindParam(":kpi_name", $kpi_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                header("location: ../insertData.php");
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}