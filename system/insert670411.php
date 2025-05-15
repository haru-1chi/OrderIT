<?php
session_start();
require_once '../config/db.php';

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
            $sql = "INSERT INTO device_models(models_name) VALUES(:models_name)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":models_name", $models_name);

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

// ตรวจสอบว่ามีการส่งข้อมูลมาจากฟอร์มหรือไม่
if (isset($_POST['submit'])) {
    // รับข้อมูลจากฟอร์ม
    $numberWork = $_POST["numberWork"];
    $dateWithdraw = $_POST["dateWithdraw"];
    $refWithdraw = $_POST["refWithdraw"];
    $refWork = $_POST["refWork"];
    $refDevice = $_POST["ref_device"];
    $numberDevice1 = $_POST["number_device_1"];
    $numberDevice2 = $_POST["number_device_2"];
    $numberDevice3 = $_POST["number_device_3"];
    $reason = $_POST["reason"];
    $report = $_POST["report"];
    $refDepart = $_POST["depart_id"];
    $refUsername = $_POST["ref_username"];
    $refOffer = $_POST["refOffer"];
    $quotation = $_POST["quotation"];
    // $receiptDate = $_POST["receipt_date"];
    // $deliveryDate = $_POST["delivery_date"];
    // $closeDate = $_POST["close_date"];
    $note = $_POST["note"];
    $status = $_POST["status"];

    $list1 = $_POST["list1"];



    // ตรวจสอบว่า list1 มีค่าว่างหรือไม่
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
    } else if (!$_SESSION['error'] && !$_SESSION['warning']) {


        for ($i = 1; $i <= 15; $i++) {
            ${"list$i"} = $_POST["list$i"];
            ${"quality$i"} = $_POST["quality$i"];
            ${"amount$i"} = $_POST["amount$i"];
            ${"price$i"} = $_POST["price$i"];
            ${"unit$i"} = $_POST["unit$i"];
        }


        try {
            $sql = "INSERT INTO orderdata (numberWork, dateWithdraw, refWithdraw, refWork, refDevice, ";
            for ($i = 1; $i <= 15; $i++) {
                $sql .= "list$i, quality$i, amount$i, price$i, unit$i";
                if ($i < 15) {
                    $sql .= ", ";
                }
            }
            $sql .= ", reason, report, refDepart, refUsername, refOffer, quotation, note, status,numberDevice1,numberDevice2,numberDevice3)
                VALUES (:numberWork, :dateWithdraw, :refWithdraw, :refWork, :refDevice, ";
            for ($i = 1; $i <= 15; $i++) {
                $sql .= ":list$i, :quality$i, :amount$i, :price$i, :unit$i";
                if ($i < 15) {
                    $sql .= ", ";
                }
            }
            $sql .= ", :reason, :report, :refDepart, :refUsername, :refOffer, :quotation, :note, :status,:numberDevice1,:numberDevice2,:numberDevice3)";

            // เตรียมและสร้าง statement
            $stmt = $conn->prepare($sql);

            // ผูกค่าข้อมูล
            $stmt->bindParam(':numberWork', $numberWork);
            $stmt->bindParam(':dateWithdraw', $dateWithdraw);
            $stmt->bindParam(':refWithdraw', $refWithdraw);
            $stmt->bindParam(':refWork', $refWork);
            $stmt->bindParam(':refDevice', $refDevice);
            for ($i = 1; $i <= 15; $i++) {
                $stmt->bindParam(":list$i", ${"list$i"});
                $stmt->bindParam(":quality$i", ${"quality$i"});
                $stmt->bindParam(":amount$i", ${"amount$i"});
                $stmt->bindParam(":price$i", ${"price$i"});
                $stmt->bindParam(":unit$i", ${"unit$i"});
            }
            $stmt->bindParam(':reason', $reason);
            $stmt->bindParam(':report', $report);
            $stmt->bindParam(':refDepart', $refDepart);
            $stmt->bindParam(':refUsername', $refUsername);
            $stmt->bindParam(':refOffer', $refOffer);
            $stmt->bindParam(':quotation', $quotation);
            // $stmt->bindParam(':receiptDate', $receiptDate);
            // $stmt->bindParam(':deliveryDate', $deliveryDate);
            // $stmt->bindParam(':closeDate', $closeDate);
            $stmt->bindParam(':note', $note);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':numberDevice1', $numberDevice1);
            $stmt->bindParam(':numberDevice2', $numberDevice2);
            $stmt->bindParam(':numberDevice3', $numberDevice3);

            // ทำการเพิ่มข้อมูล
            if ($stmt->execute()) {
                $_SESSION["success"] = "เพิ่มข้อมูลสำเร็จ";
                header("location: ../create.php");
            } else {
                $_SESSION["error"] = "พบข้อผิดพลาด";
                header("location: ../create.php");
            }
        } catch (PDOException $e) {
            echo "" . $e->getMessage() . "";
        }
    }
}
if (isset($_POST['submitW'])) {
    // รับข้อมูลจากฟอร์ม
    $numberWork = $_POST["numberWork"];
    $dateWithdraw = $_POST["dateWithdraw"];
    $refWithdraw = $_POST["refWithdraw"];
    $refWork = $_POST["refWork"];
    $refDevice = $_POST["ref_device"];
    $numberDevice1 = $_POST["number_device_1"];
    $numberDevice2 = $_POST["number_device_2"];
    $numberDevice3 = $_POST["number_device_3"];
    $reason = $_POST["reason"];
    $report = $_POST["report"];
    $refDepart = $_POST["depart_id"];
    $refUsername = $_POST["ref_username"];
    $refOffer = $_POST["refOffer"];
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

    $numberWork = $_POST["numberWork"];
    $dateWithdraw = $_POST["dateWithdraw"];
    $refWithdraw = 23;
    $refWork = 10;
    $refDevice = 17;
    $numberDevice1 = "-";
    $numberDevice2 = "-";
    $numberDevice3 = "-";
    $reason = $_POST['reason'];
    $report = "เบิกอะไหล่รายสัปดาห์ตามเอกสารแนบ";
    $refDepart = 3;
    $refUsername = $_POST["username"];
    $refOffer = 1;
    $quotation = "-";
    $receiptDate = $_POST["dateWithdraw"];
    $deliveryDate = $_POST["dateWithdraw"];
    $closeDate = $_POST["dateWithdraw"];
    $note = "-";
    $status = 3;

    for ($i = 1; $i <= 15; $i++) {
        ${"list$i"} = $_POST["list$i"];
        ${"quality$i"} = $_POST["quality$i"];
        ${"amount$i"} = $_POST["amount$i"];
        ${"price$i"} = $_POST["price$i"];
        ${"unit$i"} = "";
    }


    try {
        $sql = "INSERT INTO orderdata (numberWork, dateWithdraw, refWithdraw, refWork, refDevice, ";
        for ($i = 1; $i <= 15; $i++) {
            $sql .= "list$i, quality$i, amount$i, price$i, unit$i";
            if ($i < 15) {
                $sql .= ", ";
            }
        }
        $sql .= ", reason, report, refDepart, refUsername, refOffer, quotation, receiptDate, deliveryDate, closeDate, note, status, numberDevice1, numberDevice2, numberDevice3) ";
        $sql .= "VALUES (:numberWork, :dateWithdraw, :refWithdraw, :refWork, :refDevice, ";
        for ($i = 1; $i <= 15; $i++) {
            $sql .= ":list$i, :quality$i, :amount$i, :price$i, :unit$i";
            if ($i < 15) {
                $sql .= ", ";
            }
        }
        $sql .= ", :reason, :report, :refDepart, :refUsername, :refOffer, :quotation, :receiptDate, :deliveryDate, :closeDate, :note, :status,:numberDevice1,:numberDevice2,:numberDevice3)";

        // เตรียมและสร้าง statement
        $stmt = $conn->prepare($sql);

        // ผูกค่าข้อมูล
        $stmt->bindParam(':numberWork', $numberWork);
        $stmt->bindParam(':dateWithdraw', $dateWithdraw);
        $stmt->bindParam(':refWithdraw', $refWithdraw);  // เพิ่มบรรทัดนี้
        $stmt->bindParam(':refWork', $refWork);          // เพิ่มบรรทัดนี้
        $stmt->bindParam(':refDevice', $refDevice);
        for ($i = 1; $i <= 15; $i++) {
            $stmt->bindParam(":list$i", ${"list$i"});
            $stmt->bindParam(":quality$i", ${"quality$i"});
            $stmt->bindParam(":amount$i", ${"amount$i"});
            $stmt->bindParam(":price$i", ${"price$i"});
            $stmt->bindParam(":unit$i", ${"unit$i"});
        }
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':report', $report);
        $stmt->bindParam(':refDepart', $refDepart);
        $stmt->bindParam(':refUsername', $refUsername);
        $stmt->bindParam(':refOffer', $refOffer);
        $stmt->bindParam(':quotation', $quotation);
        $stmt->bindParam(':receiptDate', $receiptDate);
        $stmt->bindParam(':deliveryDate', $deliveryDate);
        $stmt->bindParam(':closeDate', $closeDate);
        $stmt->bindParam(':note', $note);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':numberDevice1', $numberDevice1);
        $stmt->bindParam(':numberDevice2', $numberDevice2);
        $stmt->bindParam(':numberDevice3', $numberDevice3);

        // ทำการเพิ่มข้อมูล
        if ($stmt->execute()) {
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
        $sql = "UPDATE data_report SET problem = :problem, description = :description , withdraw = :withdraw, status = :status , note = :note WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":problem", $problem);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":withdraw", $withdraw);
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
    $username = "";
    $status = 0;
    try {
        $sql = "UPDATE data_report SET problem = :problem, description = :description , withdraw = :withdraw, status = :status , note = :note, username = :username WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":problem", $problem);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":withdraw", $withdraw);
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
    $problem = $_POST['problem'];
    $description = $_POST['description'];
    $close_date = $_POST['close_date'];
    $note = $_POST['note'];
    $department = $_POST['department'];
    $status = 4;
    try {
        $sql = "UPDATE data_report SET problem = :problem, description = :description , close_date = :close_date , note = :note , status = :status,department = :department WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":problem", $problem);
        $stmt->bindParam(":description", $description);
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
    $status = 5;
    try {
        $sql = "UPDATE data_report SET problem = :problem, description = :description , note = :note , status = :status WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":problem", $problem);
        $stmt->bindParam(":description", $description);
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
    $problem = $_POST['problem'];
    $description = $_POST['description'];
    $withdraw = $_POST['withdraw'];

    if ($withdraw == "" || empty($withdraw)) {
        $withdraw = $_POST['withdraw2'];
    }

    $note = $_POST['note'];
    $department = $_POST['department'];
    $number_device = $_POST['number_device'];
    try {
        $sql = "UPDATE data_report SET problem = :problem, description = :description, note = :note,withdraw = :withdraw,number_device = :number_device,department = :department WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":problem", $problem);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":note", $note);
        $stmt->bindParam(":withdraw", $withdraw);
        $stmt->bindParam(":number_device", $number_device);
        $stmt->bindParam(":department", $department);
        $stmt->bindParam(":id", $id);

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

    // if ($department == "" || $department = null) {
    //     $_SESSION['error'] = "หน่วยงานมีค่าว่าง กรุณากรอกใหม่อีกครั้ง";
    //     header("location: ../myjob.php");
    //     exit();
    // }

    try {
        if (empty($department)) {
            $_SESSION['error'] = "หน่วยงานมีค่าว่าง กรุณากรอกใหม่อีกครั้ง";
            header("location: ../myjob.php");
            exit();
        }

        $sql = "INSERT INTO data_report(time_report, date_report, device, number_device, ip_address, report, reporter, department, tel, take, problem, description, withdraw, close_date, status,deviceName) 
                VALUES (:time_report, :date_report, :device, :number_device, :ip_address, :report, :reporter, :department, :tel, :take, :problem, :description, :withdraw, :close_date, :status,:deviceName)";

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
