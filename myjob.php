<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';
date_default_timezone_set("Asia/Bangkok");

if (isset($_SESSION['admin_log'])) {
    $admin = $_SESSION['admin_log'];
    $sql = "SELECT CONCAT(fname, ' ', lname) AS full_name FROM admin WHERE username = :admin";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":admin", $admin);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $name = $result['full_name'];
}
if (!isset($_SESSION["admin_log"])) {
    $_SESSION["warning"] = "กรุณาเข้าสู่ระบบ";
    header("location: login.php");
}

?>
<!doctype html>
<html lang="en">

<head>
    <title>งานของฉัน | ระบบบริหารจัดการ ศูนย์บริการซ่อมคอมพิวเตอร์</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS v5.2.1 -->
    <?php bs5() ?>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            background-color: #F9FDFF;
        }

        #dataAll tbody tr td {
            background-color: #f2f7ff;
            color: #000;
        }

        .ui-autocomplete {
            z-index: 1055 !important;
        }

        .modal {
            background-color: rgba(0, 0, 0, 0.5);
        }

        .job-modal {
            width: 500px;
        }

        .withdraw-modal {
            width: 650px;
            height: fit-content;
        }
    </style>
</head>

<body>
    <?php navbar() ?>
    <div class="container-fluid">
        <h1 class="text-center my-4">งานของฉัน</h1>
        <div class="card rounded-4 shadow-sm p-3 mt-5 col-sm-12 col-lg-12 col-md-12">
            <div class="table-responsive">
                <?php if (isset($_SESSION['error'])) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php } ?>

                <?php if (isset($_SESSION['warning'])) { ?>
                    <div class="alert alert-warning" role="alert">
                        <?php
                        echo $_SESSION['warning'];
                        unset($_SESSION['warning']);
                        ?>
                    </div>
                <?php } ?>

                <?php if (isset($_SESSION['success'])) { ?>
                    <div class="alert alert-success" role="alert">
                        <?php
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php } ?>

                <?php
                if (isset($_POST['checkDate_my_work'])) {
                    // Get values from form
                    $status = $_POST['status_my_work'];
                    $dateStart = $_POST['dateStart_my_work'];
                    $dateEnd = $_POST['dateEnd_my_work'];

                    // แปลงวันที่เริ่มต้นเป็น พ.ศ.
                    $yearStart = date("Y", strtotime($dateStart)) + 543;
                    $yearEnd = date("Y", strtotime($dateEnd)) + 543;

                    $dateStart_buddhist = $yearStart . "-" . date("m-d", strtotime($dateStart));
                    $dateEnd_buddhist = $yearEnd . "-" . date("m-d", strtotime($dateEnd));

                    $sql = "SELECT dp.*, dt.depart_name 
                        FROM data_report AS dp
                        LEFT JOIN depart AS dt ON dp.department = dt.depart_id
                        WHERE dp.username = :username 
                        AND status = :status 
                        AND date_report BETWEEN :dateStart AND :dateEnd
                        ORDER BY dp.id DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":username", $admin);
                    $stmt->bindParam(":status", $status);
                    $stmt->bindParam(":dateStart", $dateStart_buddhist);
                    $stmt->bindParam(":dateEnd", $dateEnd_buddhist);
                } else {
                    // If checkDate is not set, retrieve all records without date range filter
                    $dateEnd = date('Y-m-d');
                    $dateStart = date('Y-m-d', strtotime('-3 days', strtotime($dateEnd)));

                    $yearStart = date("Y", strtotime($dateStart)) + 543;
                    $yearEnd = date("Y", strtotime($dateEnd)) + 543;

                    $dateStart_buddhist = $yearStart . "-" . date("m-d", strtotime($dateStart));
                    $dateEnd_buddhist = $yearEnd . "-" . date("m-d", strtotime($dateEnd));

                    $sql = "SELECT dp.*, dt.depart_name 
                        FROM data_report AS dp
                        LEFT JOIN depart AS dt ON dp.department = dt.depart_id
                        WHERE dp.username = :username AND date_report BETWEEN :dateStart AND :dateEnd
                        ORDER BY dp.id DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":username", $admin);
                    $stmt->bindParam(":dateStart", $dateStart_buddhist);
                    $stmt->bindParam(":dateEnd", $dateEnd_buddhist);
                }

                // Prepare and execute the SQL query
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $i = 0;

                ?>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <form method="post" action="export.php">
                            <button name="act" class="btn btn-primary" type="submit">Export->Excel</button>
                        </form>
                    </div>

                    <form action="" method="post">
                        <div class="d-flex gap-4">

                            <select class="form-select" name="status_my_work" id="numberWork" style="width: 250px;">
                                <option value="2" <?php if (isset($status) == 2 && $status == 2)
                                                        echo "selected"; ?>>
                                    กำลังดำเนินการ</option>
                                <option value="3" <?php if (isset($status) == 3 && $status == 3)
                                                        echo "selected"; ?>>
                                    เบิกอะไหล่</option>
                                <option value="5" <?php if (isset($status) == 5 && $status == 5)
                                                        echo "selected"; ?>>ส่งซ่อม
                                </option>
                                <option value="4" <?php if (isset($status) == 4 && $status == 4)
                                                        echo "selected"; ?>>
                                    เสร็จสิ้น</option>
                            </select>

                            <input type="date" value="<?= isset($dateStart) ? $dateStart : ''; ?>" name="dateStart_my_work"
                                class="form-control" style="width: 250px;">
                            <input type="date" value="<?= isset($dateEnd) ? $dateEnd : ''; ?>" name="dateEnd_my_work"
                                class="form-control" style="width: 250px;">
                            <button type="submit" name="checkDate_my_work" class="btn btn-primary">ยืนยัน</button>
                        </div>


                    </form>
                </div>

                <hr>
                <form action="system/insert.php" method="post">
                    <table id="dataAll" class="table table-primary">
                        <thead>
                            <tr>
                                <th class="text-center" scope="col">ลำดับ</th>
                                <th scope="col">วันที่</th>
                                <th scope="col">เวลา</th>

                                <!-- <th scope="col">อุปกรณ์</th> -->
                                <th scope="col">รูปแบบการทำงาน</th>
                                <th scope="col">หมายเลขครุภัณฑ์</th>
                                <th scope="col">อาการที่ได้รับแจ้ง</th>
                                <th scope="col">ผู้แจ้ง</th>
                                <th scope="col">หน่วยงาน</th>
                                <th scope="col">เบอร์โทร</th>
                                <th scope="col">ปิดงาน</th>
                                <th scope="col">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php

                            // Process the fetched data
                            foreach ($result as $row) {
                                $i++;

                                $dateString = $row['date_report'];
                                $timestamp = strtotime($dateString);
                                $dateFormatted = date('d/m/Y', $timestamp);

                                $timeString = $row['time_report'];
                                $timeFormatted = date('H:i', strtotime($timeString)) . ' น.';

                                $closeTimeString = $row['close_date'];
                                if (empty($closeTimeString) || $closeTimeString === '00:00:00.000000') {
                                    $closeTimeFormatted = '-';
                                } else {
                                    $closeTimeFormatted = date('H:i', strtotime($closeTimeString)) . ' น.';
                                }
                            ?>
                                <tr>
                                    <td class="text-start" scope="row"><?= $row['id'] ?></td>
                                    <td class="text-start" scope="row"><?= $dateFormatted ?></td>
                                    <td class="text-start"><?= $timeFormatted ?> น.</td>
                                    <td class="text-start"><?= $row['device'] ?></td>
                                    <td class="text-start"><?= $row['number_device'] ?></td>
                                    <td class="text-start"><?= $row['report'] ?></td>
                                    <td class="text-start"><?= $row['reporter'] ?></td>
                                    <td class="text-start"><?= $row['depart_name'] ?></td>
                                    <td class="text-start"><?= $row['tel'] ?></td>
                                    <td class="text-start"><?= $closeTimeFormatted ?></td>
                                    <?php
                                    if ($row['status'] == 1) {
                                        $statusText = "ยังไม่ได้ดำเนินการ";
                                    } else if ($row['status'] == 2) {
                                        $statusText = "กำลังดำเนินการ";
                                    } else if ($row['status'] == 3) {
                                        $statusText = "รออะไหล่" . ' ' . $row['withdraw'];
                                    } else if ($row['status'] == 4) {
                                        $statusText = "เสร็จสิ้น" . ' ' . $row['withdraw'];
                                    } else if ($row['status'] == 5) {
                                        $statusText = "ส่งซ่อม";
                                    } else if ($row['status'] == 6) {
                                        $statusText = "รอกรอกรายละเอียด";
                                    }
                                    ?>

                                    <form action="system/insert.php" method="post">
                                        <td>
                                            <?php if ($row['status'] == 1) { ?>
                                                <button type="submit" name="inTime"
                                                    style=" background-color: orange;color:white;border: 1px solid orange"
                                                    class="btn mb-3 btn-primary">เริ่มดำเนินการ</button>
                                            <?php } else if ($row['status'] == 2) { ?>
                                                <button type="button"
                                                    style="background-color: orange;color:white;border: 1px solid orange"
                                                    class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalTask<?= $i ?>')"><?= $statusText ?></button>
                                            <?php } else if ($row['status'] == 3) { ?>
                                                <button type="button"
                                                    style=" background-color: blue;color:white;border: 1px solid orange"
                                                    class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalTask<?= $i ?>')"><?= $statusText ?></button>
                                            <?php } else if ($row['status'] == 4) { ?>
                                                <button type="button"
                                                    style=" background-color: green;color:white;border: 1px solid orange"
                                                    class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalTask<?= $i ?>')"><?= $statusText ?></button>
                                            <?php } else if ($row['status'] == 5) { ?>
                                                <button type="button"
                                                    style=" background-color: #D673D3;color:white;border: 1px solid orange"
                                                    class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalTask<?= $i ?>')"><?= $statusText ?></button>
                                            <?php } else if ($row['status'] == 6) { ?>
                                                <button type="button"
                                                    style=" background-color: green;color:white;border: 1px solid orange"
                                                    class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalTask<?= $i ?>')"><?= $statusText ?></button>
                                            <?php } ?>

                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                            <!-- modal -->
                                            <div id="workflowModalTask<?= $i ?>" class="modal" style="display: none;">
                                                <div class="p-5 d-flex justify-content-center gap-4">
                                                    <div class="modal-content job-modal">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5" id="staticBackdropLabel">รายละเอียดงาน</h1>
                                                            <button type="button" class="btn-close" onclick="toggleModal('#workflowModalTask<?= $i ?>')"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form action="system/insert.php" method="post">
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <label>หมายเลขงาน</label>
                                                                        <input type="text" class="form-control"
                                                                            value="<?= $row['id'] ?>" disabled>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label>วันที่</label>
                                                                        <input type="text" class="form-control"
                                                                            value="<?= $row['date_report'] ?>" disabled>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-4">
                                                                        <label>เวลาแจ้ง</label>
                                                                        <input type="time" class="form-control"
                                                                            value="<?= date('H:i', strtotime($row['time_report'])) ?>" disabled>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <label>เวลารับงาน</label>
                                                                        <input type="text" class="form-control"
                                                                            value="<?= $row['take'] ?>" disabled>
                                                                    </div>
                                                                    <div class="col-4">
                                                                        <label>เวลาปิดงาน (ถ้ามี)</label>
                                                                        <input type="time" class="form-control" id="time_report" name="close_date"
                                                                            value="<?= ($row['status'] == 3 && ($row['close_date'] === '00:00:00.000000' || $row['close_date'] === null || trim($row['close_date']) === ''))
                                                                                        ? '' : (($row['close_date'] && $row['close_date'] !== '00:00:00.000000') ? date('H:i', strtotime($row['close_date'])) : '') ?>">
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <label>ผู้แจ้ง</label>
                                                                        <input type="text" class="form-control"
                                                                            value="<?= $row['reporter'] ?>" disabled>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label>หน่วยงาน</label>
                                                                        <?php
                                                                        $sql = "SELECT depart_name FROM depart WHERE depart_id = ?";
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->execute([$row['department']]);
                                                                        $departRow = $stmt->fetch(PDO::FETCH_ASSOC);
                                                                        ?>

                                                                        <input type="text" class="form-control"
                                                                            value="<?= $departRow['depart_name'] ?>" disabled>

                                                                        <input type="hidden" name="department"
                                                                            value="<?= $row['department'] ?>">
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <label>เบอร์ติดต่อกลับ</label>
                                                                        <input type="text" class="form-control"
                                                                            value="<?= $row['tel'] ?>" disabled>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label for="deviceInput">อุปกรณ์</label>
                                                                        <input type="text" class="form-control" id="deviceInput<?= $i ?>" name="deviceName"
                                                                            value="<?= $row['deviceName'] ?>">
                                                                        <input type="hidden" id="deviceId<?= $i ?>">
                                                                    </div>

                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <label>หมายเลขครุภัณฑ์ (ถ้ามี)</label>
                                                                        <input value="<?= $row['number_device'] ?>" type="text"
                                                                            class="form-control" name="number_devices">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label>หมายเลข IP addrees</label>
                                                                        <input type="text" class="form-control"
                                                                            value="<?= $row['ip_address'] ?>" disabled>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <label>อาการที่ได้รับแจ้ง</label>
                                                                        <input type="text" class="form-control"
                                                                            value="<?= $row['report'] ?>" disabled>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <label>รูปแบบการทำงาน<span style="color: red;">*</span></label>
                                                                        <select class="form-select" name="device"
                                                                            aria-label="Default select example">
                                                                            <option value="<?= $row['device'] ?: '' ?>"
                                                                                selected>
                                                                                <?= !empty($row['device']) ? $row['device'] : 'ไม่มี' ?>
                                                                            </option>
                                                                            <?php
                                                                            $sql = "SELECT * FROM workinglist";
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute();
                                                                            $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                            foreach ($checkD as $d) {
                                                                                if ($d['workingName'] != $row['device']) {
                                                                            ?>
                                                                                    <option value="<?= $d['workingName'] ?>">
                                                                                        <?= $d['workingName'] ?>
                                                                                    </option>
                                                                            <?php }
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label>หมายเลขใบเบิก</label>
                                                                        <?php if (empty($row['withdraw'])) { ?>
                                                                            <input disabled type="text"
                                                                                class="form-control withdrawInput" name="withdraw"
                                                                                id="withdrawInput<?= $i ?>">
                                                                        <?php } else { ?>
                                                                            <input disabled value="<?= $row['withdraw'] ?>"
                                                                                type="text" class="form-control withdrawInput"
                                                                                name="withdraw" id="withdrawInput<?= $i ?>">
                                                                            <input type="hidden" value="<?= $row['withdraw'] ?>"
                                                                                class="form-control withdrawInput"
                                                                                id="withdrawInputHidden<?= $i ?>" name="withdraw2">
                                                                        <?php } ?>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <label>รายละเอียด<span style="color: red;">*</span></label>
                                                                        <input value="<?= $row['description'] ?>" type="text"
                                                                            class="form-control" name="description">
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <label>หมายเหตุ</label>
                                                                        <input value="<?= $row['note'] ?>" type="text"
                                                                            class="form-control" name="note">
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-6">
                                                                        <label>ผู้คีย์งาน</label>
                                                                        <input value="<?= $row['create_by'] ?>" type="text"
                                                                            class="form-control" name="create_by" disabled>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label>ซ่อมครั้งที่</label>
                                                                        <?php
                                                                        if (!empty($row['number_device']) && $row['number_device'] !== '-') {
                                                                            $sqlCount = "SELECT COUNT(*) AS repair_count 
                     FROM data_report 
                     WHERE number_device = :number_device 
                     AND (number_device IS NOT NULL AND number_device <> '' AND number_device <> '-')";
                                                                            $stmtCount = $conn->prepare($sqlCount);
                                                                            $stmtCount->bindParam(":number_device", $row['number_device']);
                                                                            $stmtCount->execute();
                                                                            $count = $stmtCount->fetch(PDO::FETCH_ASSOC);
                                                                            $repairCount = $count['repair_count'];
                                                                        } else {
                                                                            $repairCount = '-';
                                                                        }
                                                                        ?>
                                                                        <input value="<?= $repairCount ?>" type="text" class="form-control" name="repair_count">
                                                                    </div>
                                                                </div>

                                                                <hr class="mb-2">
                                                                <!-- !!!!! -->
                                                                <h4 class="mt-0 mb-3" id="staticBackdropLabel">งานคุณภาพ</h4>
                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <label>ปัญหาอยู่ใน SLA หรือไม่<span style="color: red;">*</span></label>
                                                                        <select class="form-select" name="sla"
                                                                            aria-label="Default select example">
                                                                            <option value="<?= $row['sla'] ?: '' ?>" selected>
                                                                                <?= !empty($row['sla']) ? $row['sla'] : 'ไม่มี' ?>
                                                                            </option>
                                                                            <?php
                                                                            $sql = "SELECT * FROM sla";
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute();
                                                                            $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                            foreach ($checkD as $d) {
                                                                                if ($d['sla_name'] != $row['sla']) {
                                                                            ?>
                                                                                    <option value="<?= $d['sla_name'] ?>">
                                                                                        <?= $d['sla_name'] ?>
                                                                                    </option>
                                                                            <?php }
                                                                            }
                                                                            ?>

                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <label>เป็นตัวชี้วัดหรือไม่<span style="color: red;">*</span></label>
                                                                        <select class="form-select" name="kpi"
                                                                            aria-label="Default select example">
                                                                            <option value="<?= $row['kpi'] ?: '' ?>" selected>
                                                                                <?= !empty($row['kpi']) ? $row['kpi'] : 'ไม่มี' ?>
                                                                            </option>
                                                                            <?php
                                                                            $sql = "SELECT * FROM kpi";
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute();
                                                                            $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                            foreach ($checkD as $d) {
                                                                                if ($d['kpi_name'] != $row['kpi']) {
                                                                            ?>
                                                                                    <option value="<?= $d['kpi_name'] ?>">
                                                                                        <?= $d['kpi_name'] ?>
                                                                                    </option>
                                                                            <?php }
                                                                            }
                                                                            ?>

                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="row">
                                                                    <div class="col-12">
                                                                        <label>Activity Report<span style="color: red;">*</span></label>
                                                                        <select class="form-select" name="problem"
                                                                            aria-label="Default select example">
                                                                            <?php
                                                                            $sql = "SELECT * FROM problemlist";
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute();
                                                                            $data = $stmt->fetchAll(PDO::FETCH_ASSOC); ?>
                                                                            <option value="<?= $row['problem'] ?: '' ?>"
                                                                                selected>
                                                                                <?= !empty($row['problem']) ? $row['problem'] : 'ไม่มี' ?>
                                                                            </option>
                                                                            <?php foreach ($data as $d) {
                                                                                if ($row['problem'] != $d['problemName']) { ?>
                                                                                    <option value="<?= $d['problemName'] ?>">
                                                                                        <?= $d['problemName'] ?>
                                                                                    </option>
                                                                            <?php }
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <!-- !!!!! -->

                                                                <?php
                                                                $sql = "SELECT * FROM orderdata";
                                                                $stmt = $conn->prepare($sql);
                                                                $stmt->execute();
                                                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                if (!$result) {
                                                                    $newValueToCheck = "1/67";
                                                                } else {
                                                                    foreach ($result as $d) {
                                                                        if ($d) {
                                                                            $selectedValue = $d['numberWork'];
                                                                            list($numerator, $denominator) = explode('/', $selectedValue);

                                                                            $currentDate = new DateTime();

                                                                            // Set $october10 to be October 10 of the current year
                                                                            $october1 = new DateTime($currentDate->format('Y') . '-10-10');

                                                                            // Check if the current date is after October 10
                                                                            if ($currentDate > $october1) {
                                                                                // Add 1 to the numerator and set the denominator to 1
                                                                                $newNumerator = intval($numerator) + 1;
                                                                                //$newDenominator = intval($denominator) + 1; // เริ่มต้นที่ 1 ในปีถัดไป
                                                                                $newDenominator = intval($denominator);
                                                                            } else {
                                                                                // Keep the numerator and increment the denominator
                                                                                $newNumerator = intval($numerator) + 1;
                                                                                $newDenominator = intval($denominator);
                                                                            }

                                                                            $newValueToCheck = $newNumerator . '/' . $newDenominator;
                                                                        }
                                                                    }
                                                                }
                                                                ?>

                                                        </div>
                                                        <!-- <div class="d-flex justify-content-end gap-3">
                                                        <button type="button" class="btn btn-warning toggleWithdrawBtn"
                                                            data-row-index="<?= $i ?>">เปิดเบิกอะไหล่</button>
                                                        <button type="submit" class="btn me-3 btn-primary"
                                                            name="Bantext">บันทึก</button>
                                                    </div> -->
                                                        <div class="modal-footer"
                                                            style="justify-content: space-between; border: none;">
                                                            <button type="submit" class="btn btn-danger"
                                                                name="disWork">คืนงาน</button>

                                                            <!-- <div class="d-flex justify-content-end gap-3"> -->
                                                            <!-- <button disabled type="submit" name="withdrawSubmit"
                                                                class="btn btn-primary withdrawButton"
                                                                id="withdrawButton<?= $i ?>">เบิกอะไหล่</button> -->
                                                            <!-- <button type="submit" name="clam"
                                                                class="btn btn-primary">ส่งซ่อม</button> -->
                                                            <button type="button" class="btn btn-primary" onclick="toggleModal('#requisitionModal<?= $i ?>')">เบิก/ส่งซ่อม</button>
                                                            <button type="submit" class="btn me-3 btn-primary"
                                                                name="Bantext">บันทึก</button>
                                                            <button type="submit" name="CloseSubmit"
                                                                class="btn btn-success">ปิดงาน</button>

                                                        </div>

                                                    </div>

                                                    <?php
                                                    // Check if records exist in `orderdata_new` for the current `id`
                                                    $checkQuery = "SELECT * FROM orderdata_new WHERE id_ref = :id_ref";
                                                    $checkStmt = $conn->prepare($checkQuery);
                                                    $checkStmt->bindParam(":id_ref", $row['id']);
                                                    $checkStmt->execute();
                                                    $requisitionData = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
                                                    $hasRequisition = count($requisitionData) > 0;
                                                    ?>

                                                    <div id="requisitionModal<?= $i ?>" class="modal-content withdraw-modal" style="display: none;">
                                                        <div class="modal-header">
                                                            <?php if ($hasRequisition): ?>
                                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">แก้ไขใบเบิก</h1>
                                                            <?php else: ?>
                                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">สร้างใบเบิก</h1>
                                                            <?php endif; ?>
                                                            <button type="button" class="btn-close" onclick="toggleModal('#requisitionModal<?= $i ?>')"></button>
                                                        </div>

                                                        <div class=" p-3">
                                                            <div class="row">

                                                                

                                                                <?php if ($hasRequisition) {
                                                                    foreach ($requisitionData as $rowData) { ?>
                                                                        <form action="system/insert.php" method="post">
                                                                        <input type="hidden" name="id_ref" value="<?= $row['id'] ?>">
                                                                            <div class="col-sm-12">
                                                                                <div class="mb-3">
                                                                                    <label id="basic-addon1">เลขใบเบิก</label>
                                                                                    <input type="text" name="numberWork" class="form-control" value="<?= $rowData['numberWork'] ?>" disabled>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-4">
                                                                                <div class="mb-3">
                                                                                    <label id="basic-addon1">วันที่ออกใบเบิก</label>
                                                                                    <input required type="date" name="dateWithdraw" class="form-control" value="<?= $rowData['dateWithdraw'] ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-4">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">ประเภทการเบิก</label>
                                                                                    <select required class="form-select" name="ref_withdraw" id="inputGroupSelect01">
                                                                                        <?php
                                                                                        $sql = 'SELECT * FROM withdraw';
                                                                                        $stmt = $conn->prepare($sql);
                                                                                        $stmt->execute();
                                                                                        $withdraws = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                        foreach ($withdraws as $withdraw) {
                                                                                            $selected = ($withdraw['withdraw_id'] == $rowData['refWithdraw']) ? 'selected' : ''; ?>
                                                                                            <option value="<?= $withdraw['withdraw_id'] ?>" <?= $selected ?>><?= $withdraw['withdraw_name'] ?></option>
                                                                                        <?php } ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-4">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">ประเภทงาน</label>
                                                                                    <select required class="form-select" name="ref_work" id="inputGroupSelect01">
                                                                                        <?php
                                                                                        $sql = 'SELECT * FROM listwork';
                                                                                        $stmt = $conn->prepare($sql);
                                                                                        $stmt->execute();
                                                                                        $listworks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                        foreach ($listworks as $listwork) {
                                                                                            $selected = ($listwork['work_id'] == $rowData['refWork']) ? 'selected' : '';
                                                                                        ?>
                                                                                            <option value="<?= $listwork['work_id'] ?>" <?= $selected ?>><?= $listwork['work_name'] ?></option>
                                                                                        <?php }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-6">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">รายการอุปกรณ์</label>
                                                                                    <select required class="form-select" name="ref_device" id="inputGroupSelect01">
                                                                                        <?php
                                                                                        $sql = 'SELECT * FROM device';
                                                                                        $stmt = $conn->prepare($sql);
                                                                                        $stmt->execute();
                                                                                        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                        foreach ($devices as $device) {
                                                                                            $isSelected = ($device['device_id'] === $rowData['refDevice']) ? 'selected' : '';
                                                                                        ?>
                                                                                            <option value="<?= $device['device_id'] ?>" <?= $isSelected ?>>
                                                                                                <?= $device['device_name'] ?>
                                                                                            </option>
                                                                                        <?php } ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <form action="system/insert.php" method="post">
                                                                            <div class="col-sm-6">
                                                                                <div class="mb-3">
                                                                                    <label id="basic-addon1">หมายเลขครุภัณฑ์</label>
                                                                                    <div id="device-number-container-<?= $i ?>">
                                                                                        <?php
                                                                                        // Fetch data from the `order_numberdevice` table
                                                                                        $sql = 'SELECT * FROM order_numberdevice WHERE order_item = :order_item';
                                                                                        $stmt = $conn->prepare($sql);
                                                                                        $stmt->bindParam(':order_item', $rowData['id'], PDO::PARAM_INT);
                                                                                        $stmt->execute();
                                                                                        $numberDevices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                        if (!empty($numberDevices)) {
                                                                                            $isFirst = true; // Flag for the first element
                                                                                            foreach ($numberDevices as $device) { ?>
                                                                                                <div class="d-flex device-number-row">
                                                                                                    <input type="text" name="number_device[]" class="form-control mb-2" value="<?= htmlspecialchars($device['numberDevice']) ?>">
                                                                                                    <button type="button" class="btn btn-warning p-2 mb-2 ms-3 remove-field"
                                                                                                        style="visibility: <?= $isFirst ? 'hidden' : 'visible' ?>;">ลบ</button>
                                                                                                </div>
                                                                                            <?php
                                                                                                $isFirst = false; // Set flag to false after the first iteration
                                                                                            }
                                                                                        } else { ?>
                                                                                            <!-- If no records, show an empty input field -->
                                                                                            <div class="d-flex device-number-row">
                                                                                                <input type="text" name="number_device[]" class="form-control" value="">
                                                                                                <button type="button" class="btn btn-danger p-2 ms-3" style="visibility: hidden;">ลบ</button>
                                                                                            </div>
                                                                                        <?php } ?>
                                                                                    </div>
                                                                                    <div class="d-flex justify-content-end">
                                                                                        <button type="button" id="add-device-number-<?= $i ?>" class="btn btn-success mt-2 align-self-end">+ เพิ่มหมายเลขครุภัณฑ์</button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-6">
                                                                                <div class="mb-3">
                                                                                    <label for="departInput">หน่วยงาน</label>
                                                                                    <?php
                                                                                    $sql = "SELECT depart_name FROM depart WHERE depart_id = ?";
                                                                                    $stmt = $conn->prepare($sql);
                                                                                    $stmt->execute([$rowData['refDepart']]);
                                                                                    $departRow = $stmt->fetch(PDO::FETCH_ASSOC);
                                                                                    ?>

                                                                                    <input type="text" class="form-control" id="departInput<?= $i ?>" name="ref_depart"
                                                                                        value="<?= $departRow['depart_name'] ?>">

                                                                                    <input type="hidden" name="depart_id" id="departId<?= $i ?>"
                                                                                        value="<?= $rowData['refDepart'] ?>">
                                                                                </div>

                                                                                <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
                                                                                <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
                                                                                <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

                                                                                <script>
                                                                                    $(function() {
                                                                                        function setupAutocomplete(type, inputId, hiddenInputId, url, addDataUrl, confirmMessage) {
                                                                                            let inputChanged = false;

                                                                                            $(inputId).autocomplete({
                                                                                                    source: function(request, response) {
                                                                                                        $.ajax({
                                                                                                            url: url,
                                                                                                            dataType: "json",
                                                                                                            data: {
                                                                                                                term: request.term,
                                                                                                                type: type
                                                                                                            },
                                                                                                            success: function(data) {
                                                                                                                response(data); // Show suggestions
                                                                                                            }
                                                                                                        });
                                                                                                    },
                                                                                                    minLength: 1,
                                                                                                    autoFocus: true,
                                                                                                    select: function(event, ui) {
                                                                                                        $(inputId).val(ui.item.label); // Fill input with label
                                                                                                        $(hiddenInputId).val(ui.item.value); // Fill hidden input with ID
                                                                                                        return false; // Prevent default behavior
                                                                                                    }
                                                                                                })
                                                                                                .data("ui-autocomplete")._renderItem = function(ul, item) {
                                                                                                    return $("<li>")
                                                                                                        .append("<div>" + item.label + "</div>")
                                                                                                        .appendTo(ul);
                                                                                                };

                                                                                            $(inputId).on("autocompletefocus", function(event, ui) {
                                                                                                // You can log or do something here but won't change the input value
                                                                                                console.log("Item highlighted: ", ui.item.label);
                                                                                                return false;
                                                                                            });

                                                                                            $(inputId).on("keyup", function() {
                                                                                                inputChanged = true;
                                                                                            });

                                                                                            $(inputId).on("blur", function() {
                                                                                                if (inputChanged) {
                                                                                                    const userInput = $(this).val().trim();
                                                                                                    if (userInput === "") return;

                                                                                                    let found = false;
                                                                                                    $(this).autocomplete("instance").menu.element.find("div").each(function() {
                                                                                                        if ($(this).text() === userInput) {
                                                                                                            found = true;
                                                                                                            return false;
                                                                                                        }
                                                                                                    });

                                                                                                    if (!found) {
                                                                                                        Swal.fire({
                                                                                                            title: confirmMessage,
                                                                                                            icon: "info",
                                                                                                            showCancelButton: true,
                                                                                                            confirmButtonText: "ใช่",
                                                                                                            cancelButtonText: "ไม่"
                                                                                                        }).then((result) => {
                                                                                                            if (result.isConfirmed) {
                                                                                                                $.ajax({
                                                                                                                    url: addDataUrl,
                                                                                                                    method: "POST",
                                                                                                                    data: {
                                                                                                                        dataToInsert: userInput
                                                                                                                    },
                                                                                                                    success: function(response) {
                                                                                                                        console.log("Data inserted successfully!");
                                                                                                                        $(hiddenInputId).val(response); // Set inserted ID
                                                                                                                    },
                                                                                                                    error: function(xhr, status, error) {
                                                                                                                        console.error("Error inserting data:", error);
                                                                                                                    }
                                                                                                                });
                                                                                                            } else {
                                                                                                                $(inputId).val(""); // Clear input
                                                                                                                $(hiddenInputId).val("");
                                                                                                            }
                                                                                                        });
                                                                                                    }
                                                                                                }
                                                                                                inputChanged = false; // Reset the flag
                                                                                            });
                                                                                        }

                                                                                        $("input[id^='deviceInput']").each(function() {
                                                                                            const i = $(this).attr("id").replace("deviceInput", ""); // Extract index
                                                                                            setupAutocomplete(
                                                                                                "depart",
                                                                                                `#departInput${i}`,
                                                                                                `#departId${i}`,
                                                                                                "autocomplete.php",
                                                                                                "insertDevice.php",
                                                                                                "คุณต้องการเพิ่มข้อมูลนี้หรือไม่?"
                                                                                            );
                                                                                        });
                                                                                    });
                                                                                </script>
                                                                            </div>

                                                                            <div class="col-sm-6">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">ผู้รับเรื่อง
                                                                                    </label>
                                                                                    <input type="text" class="form-control" value="<?= htmlspecialchars($name) ?>" disabled>
                                                                                    <input type="hidden" name="ref_username" value="<?= htmlspecialchars($name) ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-12">
                                                                                <div class="mb-3">
                                                                                    <label id="basic-addon1">เหตุผลและความจำเป็น</label>
                                                                                    <input type="text" name="reason" class="form-control" value="<?= $rowData['reason'] ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-4">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">ร้านที่เสนอราคา</label>
                                                                                    <select required class="form-select" name="ref_offer" id="inputGroupSelect01">
                                                                                        <?php
                                                                                        $sql = 'SELECT * FROM offer';
                                                                                        $stmt = $conn->prepare($sql);
                                                                                        $stmt->execute();
                                                                                        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                                        foreach ($offers as $offer) {
                                                                                            $selected = ($offer['offer_id'] == $rowData['refOffer']) ? 'selected' : '';
                                                                                        ?>
                                                                                            <option value="<?= $offer['offer_id'] ?>" <?= $selected ?>><?= $offer['offer_name'] ?></option>
                                                                                        <?php }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-4">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">เลขที่ใบเสนอราคา
                                                                                    </label>
                                                                                    <input type="text" name="quotation" class="form-control" value="<?= $rowData['quotation'] ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-4">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">สถานะ</label>
                                                                                    <select required class="form-select" name="status" id="inputGroupSelect01">
                                                                                        <option value="1" <?= $rowData['status'] == 1 ? 'selected' : '' ?>>รอรับเอกสารจากหน่วยงาน</option>
                                                                                        <option value="2" <?= $rowData['status'] == 2 ? 'selected' : '' ?>>รอส่งเอกสารไปพัสดุ</option>
                                                                                        <option value="3" <?= $rowData['status'] == 3 ? 'selected' : '' ?>>รอพัสดุสั่งของ</option>
                                                                                        <option value="4" <?= $rowData['status'] == 4 ? 'selected' : '' ?>>รอหมายเลขครุภัณฑ์</option>
                                                                                        <option value="5" <?= $rowData['status'] == 5 ? 'selected' : '' ?>>ปิดงาน</option>
                                                                                        <option value="6" <?= $rowData['status'] == 6 ? 'selected' : '' ?>>ยกเลิก</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <input type="hidden" name="report" class="form-control">
                                                                            <!-- <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">อาการรับแจ้ง</label>
                                                                       
                                                                    </div>
                                                                </div> -->

                                                                            <div class="col-sm-12">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">หมายเหตุ
                                                                                    </label>
                                                                                    <input type="text" name="note" class="form-control" value="<?= $rowData['note'] ?>">
                                                                                </div>
                                                                            </div>

                                                                            <?php
                                                                            $orderId = $rowData['id'];
                                                                            $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
                                                                            $stmt->execute();
                                                                            $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                            ?>
                                                                            <table id="pdf" style="width: 100%;" class="table">
                                                                                <thead class="table-primary">
                                                                                    <tr class="text-center">
                                                                                        <th style="text-align:center;width: 10%;">ลำดับ</th>
                                                                                        <th style="text-align:center;width: 10%;">รายการ</th>
                                                                                        <th style="text-align:center;width: 20%;">คุณสมบัติ</th>
                                                                                        <th style="text-align:center;width: 10%;">จำนวน</th>
                                                                                        <th style="text-align:center; width: 10%;">ราคา</th>
                                                                                        <th style="text-align:center; width: 10%;">หน่วย</th>
                                                                                        <th style="text-align:center; width: 10%;"></th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody id="table-body-<?= $i ?>">
                                                                                    <?php
                                                                                    $rowNumber = 1;
                                                                                    $isFirstRow = true;
                                                                                    foreach ($orderItems as $item) {
                                                                                    ?>
                                                                                        <tr class="text-center">
                                                                                            <th scope="row"><?= $rowNumber++; ?></th>
                                                                                            <td>
                                                                                                <select style="width: 120px" class="form-select device-select" name="list[]" data-row="1">
                                                                                                    <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                                                                                                    <!-- Populate options dynamically -->
                                                                                                    <?php
                                                                                                    $deviceSql = "SELECT * FROM device_models ORDER BY models_name ASC";
                                                                                                    $deviceStmt = $conn->prepare($deviceSql);
                                                                                                    $deviceStmt->execute();
                                                                                                    $devices = $deviceStmt->fetchAll(PDO::FETCH_ASSOC);
                                                                                                    foreach ($devices as $device) {
                                                                                                        $selected = $device['models_id'] == $item['list'] ? 'selected' : '';
                                                                                                        echo "<option value='{$device['models_id']}' $selected>{$device['models_name']}</option>";
                                                                                                    }
                                                                                                    ?>
                                                                                                </select>
                                                                                            </td>
                                                                                            <td><textarea rows="2" maxlength="60" name="quality[]" class="form-control"><?= htmlspecialchars($item['quality']); ?></textarea></td>
                                                                                            <td><input style="width: 2rem; margin: 0 auto;" type="text" name="amount[]" class="form-control" value="<?= htmlspecialchars($item['amount']); ?>"></td>
                                                                                            <td><input style="width: 4rem;" type="text" name="price[]" class="form-control" value="<?= htmlspecialchars($item['price']); ?>"></td>
                                                                                            <td><input style="width: 4rem;" type="text" name="unit[]" class="form-control" value="<?= htmlspecialchars($item['unit']); ?>"></td>
                                                                                            <td><button type="button" class="btn btn-warning remove-row"
                                                                                                    style="visibility: <?= $isFirstRow ? 'hidden' : 'visible' ?>;">ลบ</button></td>
                                                                                        </tr>
                                                                                    <?php
                                                                                        $isFirstRow = false;
                                                                                    } ?>
                                                                                </tbody>
                                                                            </table>
                                                                            <div class="d-flex justify-content-end">
                                                                                <button type="button" id="add-row-<?= $i ?>" class="btn btn-success">+ เพิ่มแถว</button>
                                                                            </div>
                                                                            <div class="w-100 d-flex justify-content-center">
                                                                                <button type="submit" name="submit_with_work" class="w-100 btn btn-primary mt-3">บันทึกข้อมูล</button>
                                                                            </div>
                                                                        <?php }
                                                                } else { ?>
                                                                        <form action="system/insert.php" method="post">
                                                                            <input type="hidden" name="id_ref" value="<?= $row['id'] ?>">
                                                                            <div class="col-sm-4">
                                                                                <div class="mb-3">
                                                                                    <label id="basic-addon1">วันที่ออกใบเบิก</label>
                                                                                    <input required type="date" name="dateWithdraw" class="form-control thaiDateInput">
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-4">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">ประเภทการเบิก</label>
                                                                                    <select required class="form-select" name="ref_withdraw" id="inputGroupSelect01">
                                                                                        <?php
                                                                                        $sql = 'SELECT * FROM withdraw';
                                                                                        $stmt = $conn->prepare($sql);
                                                                                        $stmt->execute();
                                                                                        $withdraws = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                        foreach ($withdraws as $withdraw) { ?>

                                                                                            <option value="<?= $withdraw['withdraw_id'] ?>"><?= $withdraw['withdraw_name'] ?></option>
                                                                                        <?php }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-4">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">ประเภทงาน</label>
                                                                                    <select required class="form-select" name="ref_work" id="inputGroupSelect01">
                                                                                        <?php
                                                                                        $sql = 'SELECT * FROM listwork';
                                                                                        $stmt = $conn->prepare($sql);
                                                                                        $stmt->execute();
                                                                                        $listworks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                        foreach ($listworks as $listwork) { ?>

                                                                                            <option value="<?= $listwork['work_id'] ?>"><?= $listwork['work_name'] ?></option>
                                                                                        <?php }
                                                                                        ?>

                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-6">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">รายการอุปกรณ์</label>

                                                                                    <select required class="form-select" name="ref_device" id="inputGroupSelect01">
                                                                                        <?php
                                                                                        $sql = 'SELECT * FROM device';
                                                                                        $stmt = $conn->prepare($sql);
                                                                                        $stmt->execute();
                                                                                        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                        foreach ($devices as $device) {
                                                                                            $isSelected = ($device['device_name'] === $row['deviceName']) ? 'selected' : '';
                                                                                        ?>
                                                                                            <option value="<?= $device['device_id'] ?>" <?= $isSelected ?>>
                                                                                                <?= $device['device_name'] ?>
                                                                                            </option>
                                                                                        <?php } ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-6">
                                                                                <div class="mb-3">
                                                                                    <label id="basic-addon1">หมายเลขครุภัณฑ์</label>
                                                                                    <div id="device-number-container-<?= $i ?>">
                                                                                        <div class="d-flex device-number-row">
                                                                                            <input type="text" name="number_device[]" class="form-control"
                                                                                                value="<?= isset($row['number_device']) ? $row['number_device'] : '' ?>">
                                                                                            <button type="button" class="btn btn-danger p-2 ms-3" style="visibility: hidden;">ลบ</button>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="d-flex justify-content-end">
                                                                                        <button type="button" id="add-device-number-<?= $i ?>" class="btn btn-success mt-2 align-self-end">+ เพิ่มหมายเลขครุภัณฑ์</button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-6">
                                                                                <div class="mb-3">
                                                                                    <label for="departInput">หน่วยงาน</label>
                                                                                    <?php
                                                                                    $sql = "SELECT depart_name FROM depart WHERE depart_id = ?";
                                                                                    $stmt = $conn->prepare($sql);
                                                                                    $stmt->execute([$row['department']]);
                                                                                    $departRow = $stmt->fetch(PDO::FETCH_ASSOC);
                                                                                    ?>

                                                                                    <input type="text" class="form-control" id="departInput<?= $i ?>" name="ref_depart"
                                                                                        value="<?= $departRow['depart_name'] ?>">

                                                                                    <input type="hidden" name="depart_id" id="departId<?= $i ?>"
                                                                                        value="<?= $row['department'] ?>">
                                                                                </div>

                                                                                <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
                                                                                <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
                                                                                <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

                                                                                <script>
                                                                                    $(function() {
                                                                                        function setupAutocomplete(type, inputId, hiddenInputId, url, addDataUrl, confirmMessage) {
                                                                                            let inputChanged = false;

                                                                                            $(inputId).autocomplete({
                                                                                                    source: function(request, response) {
                                                                                                        $.ajax({
                                                                                                            url: url,
                                                                                                            dataType: "json",
                                                                                                            data: {
                                                                                                                term: request.term,
                                                                                                                type: type
                                                                                                            },
                                                                                                            success: function(data) {
                                                                                                                response(data); // Show suggestions
                                                                                                            }
                                                                                                        });
                                                                                                    },
                                                                                                    minLength: 1,
                                                                                                    autoFocus: true,
                                                                                                    select: function(event, ui) {
                                                                                                        $(inputId).val(ui.item.label); // Fill input with label
                                                                                                        $(hiddenInputId).val(ui.item.value); // Fill hidden input with ID
                                                                                                        return false; // Prevent default behavior
                                                                                                    }
                                                                                                })
                                                                                                .data("ui-autocomplete")._renderItem = function(ul, item) {
                                                                                                    return $("<li>")
                                                                                                        .append("<div>" + item.label + "</div>")
                                                                                                        .appendTo(ul);
                                                                                                };

                                                                                            $(inputId).on("autocompletefocus", function(event, ui) {
                                                                                                // You can log or do something here but won't change the input value
                                                                                                console.log("Item highlighted: ", ui.item.label);
                                                                                                return false;
                                                                                            });

                                                                                            $(inputId).on("keyup", function() {
                                                                                                inputChanged = true;
                                                                                            });

                                                                                            $(inputId).on("blur", function() {
                                                                                                if (inputChanged) {
                                                                                                    const userInput = $(this).val().trim();
                                                                                                    if (userInput === "") return;

                                                                                                    let found = false;
                                                                                                    $(this).autocomplete("instance").menu.element.find("div").each(function() {
                                                                                                        if ($(this).text() === userInput) {
                                                                                                            found = true;
                                                                                                            return false;
                                                                                                        }
                                                                                                    });

                                                                                                    if (!found) {
                                                                                                        Swal.fire({
                                                                                                            title: confirmMessage,
                                                                                                            icon: "info",
                                                                                                            showCancelButton: true,
                                                                                                            confirmButtonText: "ใช่",
                                                                                                            cancelButtonText: "ไม่"
                                                                                                        }).then((result) => {
                                                                                                            if (result.isConfirmed) {
                                                                                                                $.ajax({
                                                                                                                    url: addDataUrl,
                                                                                                                    method: "POST",
                                                                                                                    data: {
                                                                                                                        dataToInsert: userInput
                                                                                                                    },
                                                                                                                    success: function(response) {
                                                                                                                        console.log("Data inserted successfully!");
                                                                                                                        $(hiddenInputId).val(response); // Set inserted ID
                                                                                                                    },
                                                                                                                    error: function(xhr, status, error) {
                                                                                                                        console.error("Error inserting data:", error);
                                                                                                                    }
                                                                                                                });
                                                                                                            } else {
                                                                                                                $(inputId).val(""); // Clear input
                                                                                                                $(hiddenInputId).val("");
                                                                                                            }
                                                                                                        });
                                                                                                    }
                                                                                                }
                                                                                                inputChanged = false; // Reset the flag
                                                                                            });
                                                                                        }

                                                                                        $("input[id^='deviceInput']").each(function() {
                                                                                            const i = $(this).attr("id").replace("deviceInput", ""); // Extract index
                                                                                            setupAutocomplete(
                                                                                                "depart",
                                                                                                `#departInput${i}`,
                                                                                                `#departId${i}`,
                                                                                                "autocomplete.php",
                                                                                                "insertDevice.php",
                                                                                                "คุณต้องการเพิ่มข้อมูลนี้หรือไม่?"
                                                                                            );
                                                                                        });
                                                                                    });
                                                                                </script>
                                                                            </div>

                                                                            <div class="col-sm-6">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">ผู้รับเรื่อง
                                                                                    </label>
                                                                                    <input type="text" class="form-control" value="<?= htmlspecialchars($name) ?>" disabled>
                                                                                    <input type="hidden" name="ref_username" value="<?= htmlspecialchars($name) ?>">
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-12">
                                                                                <div class="mb-3">
                                                                                    <label id="basic-addon1">เหตุผลและความจำเป็น</label>
                                                                                    <input type="text" name="reason" class="form-control">
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-4">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">ร้านที่เสนอราคา
                                                                                    </label>
                                                                                    <select required class="form-select" name="ref_offer" id="inputGroupSelect01">
                                                                                        <?php
                                                                                        $sql = 'SELECT * FROM offer';
                                                                                        $stmt = $conn->prepare($sql);
                                                                                        $stmt->execute();
                                                                                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                                        foreach ($result as $row) { ?>
                                                                                            <option value="<?= $row['offer_id'] ?>"><?= $row['offer_name'] ?></option>
                                                                                        <?php }
                                                                                        ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-4">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">เลขที่ใบเสนอราคา
                                                                                    </label>
                                                                                    <input value="-" type="text" name="quotation" class="form-control">
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-sm-4">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">สถานะ
                                                                                    </label>
                                                                                    <select required class="form-select" name="status" id="inputGroupSelect01">
                                                                                        <option value="1">รอรับเอกสารจากหน่วยงาน</option>
                                                                                        <option value="2">รอส่งเอกสารไปพัสดุ</option>
                                                                                        <option value="3">รอพัสดุสั่งของ</option>
                                                                                        <option value="4">รอหมายเลขครุภัณฑ์</option>
                                                                                        <option value="5">ปิดงาน</option>
                                                                                        <option value="6">ยกเลิก</option>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <input type="hidden" name="report" class="form-control">
                                                                            <!-- <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">อาการรับแจ้ง</label>
                                                                       
                                                                    </div>
                                                                </div> -->

                                                                            <div class="col-sm-12">
                                                                                <div class="mb-3">
                                                                                    <label for="inputGroupSelect01">หมายเหตุ
                                                                                    </label>
                                                                                    <input value="-" type="text" name="note" class="form-control">
                                                                                </div>
                                                                            </div>

                                                                            <table id="pdf" style="width: 100%;" class="table">
                                                                                <thead class="table-primary">
                                                                                    <tr class="text-center">
                                                                                        <th style="text-align:center;width: 10%;">ลำดับ</th>
                                                                                        <th style="text-align:center;width: 10%;">รายการ</th>
                                                                                        <th style="text-align:center;width: 20%;">คุณสมบัติ</th>
                                                                                        <th style="text-align:center;width: 10%;">จำนวน</th>
                                                                                        <th style="text-align:center; width: 10%;">ราคา</th>
                                                                                        <th style="text-align:center; width: 10%;">หน่วย</th>
                                                                                        <th style="text-align:center; width: 10%;"></th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody id="table-body-<?= $i ?>">
                                                                                    <tr class="text-center">
                                                                                        <th scope="row">1</th>
                                                                                        <td>
                                                                                            <select style="width: 120px" class="form-select device-select" name="list[]" data-row="1">
                                                                                                <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                                                                                                <!-- Populate options dynamically -->
                                                                                                <?php
                                                                                                $sql = "SELECT * FROM device_models ORDER BY models_name ASC";
                                                                                                $stmt = $conn->prepare($sql);
                                                                                                $stmt->execute();
                                                                                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                                                foreach ($result as $d) {
                                                                                                ?>
                                                                                                    <option value="<?= $d['models_id'] ?>"><?= $d['models_name'] ?></option>
                                                                                                <?php
                                                                                                }
                                                                                                ?>
                                                                                            </select>
                                                                                        </td>
                                                                                        <td><textarea style="width: 150px" rows="2" maxlength="60" name="quality[]" class="form-control"></textarea></td>
                                                                                        <td><input style="width: 2rem; margin: 0 auto;" type="text" name="amount[]" class="form-control"></td>
                                                                                        <td><input style="width: 4rem;" type="text" name="price[]" class="form-control"></td>
                                                                                        <td><input style="width: 4rem;" type="text" name="unit[]" class="form-control"></td>
                                                                                        <td><button type="button" class="btn btn-danger" style="visibility: hidden;">ลบ</button></td>
                                                                                    </tr>

                                                                                </tbody>

                                                                            </table>

                                                                            <div class="d-flex justify-content-end">
                                                                                <button type="button" id="add-row-<?= $i ?>" class="btn btn-success">+ เพิ่มแถว</button>
                                                                            </div>
                                                                            <div class="w-100 d-flex justify-content-center">
                                                                                <button type="submit" name="submit_with_work" class="w-100 btn btn-primary mt-3">บันทึกข้อมูล</button>
                                                                            </div>

                                                            </div>
                                                        </div>

                                    </form>
            </div>


        </div>

    </div>
    </form>
    <script>
        // เพิ่มแถวตาราง
        let rowIndex = 1;
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id.startsWith('add-row-')) {
                const modalId = e.target.id.split('-').pop(); // Extract the modal index
                const tableBody = document.querySelector(`#table-body-${modalId}`);
                const rowIndex = tableBody.querySelectorAll('tr').length + 1;

                const newRow = document.createElement('tr');
                newRow.className = 'text-center';
                newRow.innerHTML = `
<th scope="row">${rowIndex}</th>
<td>
<select style="width: 120px" class="form-select device-select" name="list[]" data-row="${rowIndex}">
<option selected value="" disabled>เลือกรายการอุปกรณ์</option>
<?php
                                                                    foreach ($result as $d) {
?>
<option value="<?= $d['models_id'] ?>"><?= $d['models_name'] ?></option>
<?php
                                                                    }
?>
</select>
</td>
<td><textarea rows="2" maxlength="60" name="quality[]" class="form-control"></textarea></td>
<td><input style="width: 2rem;" type="text" name="amount[]" class="form-control"></td>
<td><input style="width: 4rem;" type="text" name="price[]" class="form-control"></td>
<td><input style="width: 4rem;" type="text" name="unit[]" class="form-control"></td>
<td><button type="button" class="btn btn-warning remove-row">ลบ</button></td>
        `;
                tableBody.appendChild(newRow);
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-row')) {
                const row = e.target.closest('tr');
                row.parentNode.removeChild(row);
            }
        });

        function updateRowNumbers() {
            const rows = document.querySelectorAll('#table-body tr');
            rows.forEach((row, index) => {
                row.querySelector('th').textContent = index + 1;
            });
            rowIndex = rows.length;
        }

        $(document).on('change', '.device-select', function() {
            var models_id = $(this).val();
            var rowElement = $(this).closest('tr');

            if (models_id) {
                $.ajax({
                    url: 'autoList.php',
                    type: 'POST',
                    data: {
                        models_id: models_id
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.success) {
                            rowElement.find('textarea[name="quality[]"]').val(data.quality);
                            rowElement.find('input[name="price[]"]').val(data.price);
                            rowElement.find('input[name="unit[]"]').val(data.unit);
                        } else {
                            alert('ไม่สามารถดึงข้อมูลได้');
                        }
                    },
                    error: function() {
                        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                    }
                });
            }
        });
    </script>
    </div>
    </td>
    </form>
    </tr>
<?php
                                                                }
                                                            }
?>
</tbody>
</table>

</div>
</div>

<div class="container-fluid">
    <h1 class="mt-5">งานที่ยังไม่ได้กรอกรายละเอียด</h1>
    <div class="card rounded-4 shadow-sm p-3 mb-5 col-sm-12 col-lg-12 col-md-12">
        <div class="table-responsive">
            <?php
            if (isset($_POST['checkDate_status_6'])) {
                // Get values from form
                $dateStart = $_POST['dateStart_unfilled'];
                $dateEnd = $_POST['dateEnd_unfilled'];

                // แปลงวันที่เริ่มต้นเป็น พ.ศ.
                $yearStart = date("Y", strtotime($dateStart)) + 543;
                $yearEnd = date("Y", strtotime($dateEnd)) + 543;

                $dateStart_buddhist = $yearStart . "-" . date("m-d", strtotime($dateStart));
                $dateEnd_buddhist = $yearEnd . "-" . date("m-d", strtotime($dateEnd));

                $sql = "SELECT dp.*, dt.depart_name 
                        FROM data_report AS dp
                        LEFT JOIN depart AS dt ON dp.department = dt.depart_id
                        WHERE dp.username = :username 
                        AND status = 6
                        AND date_report BETWEEN :dateStart AND :dateEnd
                        ORDER BY dp.id DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":username", $admin);
                $stmt->bindParam(":dateStart", $dateStart_buddhist);
                $stmt->bindParam(":dateEnd", $dateEnd_buddhist);
            } else {
                $sql = "SELECT dp.*, dt.depart_name 
        FROM data_report AS dp
        LEFT JOIN depart AS dt ON dp.department = dt.depart_id
        WHERE dp.username = :username 
        AND dp.status = 6
        ORDER BY dp.id DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(":username", $admin);
            }
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $j = 0;

            ?>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <form method="post" action="export.php">
                        <button name="act" class="btn btn-primary" type="submit">Export->Excel</button>
                    </form>
                </div>

                <form action="" method="post">
                    <div class="d-flex gap-4">
                        <!-- ห้ามลบ แต่เป็นค่าว่างน่าจะได้ -->

                        <input type="date" value="<?= isset($dateStart) ? $dateStart : ''; ?>" name="dateStart_unfilled"
                            class="form-control" style="width: 250px;">
                        <input type="date" value="<?= isset($dateEnd) ? $dateEnd : ''; ?>" name="dateEnd_unfilled"
                            class="form-control" style="width: 250px;">
                        <button type="submit" name="checkDate_status_6" class="btn btn-primary">ยืนยัน</button>
                    </div>
                </form>
            </div>

            <hr>
            <table id="dataAll" class="table table-primary">
                <thead>
                    <tr>
                        <th class="text-center" scope="col">ลำดับ</th>
                        <th scope="col">วันที่</th>
                        <th scope="col">เวลา</th>

                        <!-- <th scope="col">อุปกรณ์</th> -->
                        <th scope="col">รูปแบบการทำงาน</th>
                        <th scope="col">หมายเลขครุภัณฑ์</th>
                        <th scope="col">อาการที่ได้รับแจ้ง</th>
                        <th scope="col">ผู้แจ้ง</th>
                        <th scope="col">หน่วยงาน</th>
                        <th scope="col">เบอร์โทร</th>
                        <th scope="col">ปิดงาน</th>
                        <th scope="col">สถานะ</th>
                    </tr>
                </thead>
                <tbody>

                    <?php

                    // Process the fetched data
                    foreach ($result as $row) {
                        $j++;

                        // Format date_report
                        $dateString = $row['date_report'];
                        $timestamp = strtotime($dateString);
                        $dateFormatted = date('d/m/Y', $timestamp);

                        // Format time_report (e.g., 14:22:00.000000 to 14:22)
                        $timeString = $row['time_report'];
                        $timeFormatted = date('H:i', strtotime($timeString));

                        // Format close_date (e.g., 0000-00-00 00:00:00.000000 to 22/11/2567 13:59)
                        $closeDateString = $row['close_date'];
                        if ($closeDateString === '0000-00-00 00:00:00.000000') {
                            $closeDateFormatted = '-'; // Show a placeholder for null/invalid dates
                        } else {
                            $closeTimestamp = strtotime($closeDateString);
                            $closeDateFormatted = date('d/m/Y H:i', $closeTimestamp);

                            // Adjust year for Buddhist Era (add 543 to the Gregorian year)
                            $closeDateFormatted = str_replace(
                                date('Y', $closeTimestamp),
                                date('Y', $closeTimestamp),
                                $closeDateFormatted
                            );

                            // Append "น." to the formatted date
                            $closeDateFormatted .= ' น.';
                        }

                    ?>
                        <tr>
                            <td class="text-start" scope="row"><?= $row['id'] ?></td>
                            <td class="text-start" scope="row"><?= $dateFormatted ?></td>
                            <td class="text-start"><?= $timeFormatted ?> น.</td>
                            <td class="text-start"><?= $row['device'] ?></td>
                            <td class="text-start"><?= $row['number_device'] ?></td>
                            <td class="text-start"><?= $row['report'] ?></td>
                            <td class="text-start"><?= $row['reporter'] ?></td>
                            <td class="text-start"><?= $row['depart_name'] ?></td>
                            <td class="text-start"><?= $row['tel'] ?></td>
                            <td class="text-start"><?= $closeDateFormatted ?> น.</td>
                            <?php
                            if ($row['status'] == 1) {
                                $statusText = "ยังไม่ได้ดำเนินการ";
                            } else if ($row['status'] == 2) {
                                $statusText = "กำลังดำเนินการ";
                            } else if ($row['status'] == 3) {
                                $statusText = "รออะไหล่" . ' ' . $row['withdraw'];
                            } else if ($row['status'] == 4) {
                                $statusText = "เสร็จสิ้น" . ' ' . $row['withdraw'];
                            } else if ($row['status'] == 5) {
                                $statusText = "ส่งซ่อม";
                            } else if ($row['status'] == 6) {
                                $statusText = "รอกรอกรายละเอียด";
                            }
                            ?>

                            <form action="system/insert.php" method="post">

                                <td>
                                    <?php if ($row['status'] == 1) { ?>
                                        <button type="submit" name="inTime"
                                            style=" background-color: orange;color:white;border: 1px solid orange"
                                            class="btn mb-3 btn-primary">เริ่มดำเนินการ</button>
                                    <?php } else if ($row['status'] == 2) { ?>
                                        <button type="button"
                                            style="background-color: orange;color:white;border: 1px solid orange"
                                            class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalUncomplete<?= $j ?>')"><?= $statusText ?></button>
                                    <?php } else if ($row['status'] == 3) { ?>
                                        <button type="button"
                                            style=" background-color: blue;color:white;border: 1px solid orange"
                                            class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalUncomplete<?= $j ?>')"><?= $statusText ?></button>
                                    <?php } else if ($row['status'] == 4) { ?>
                                        <button type="button"
                                            style=" background-color: green;color:white;border: 1px solid orange"
                                            class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalUncomplete<?= $j ?>')"><?= $statusText ?></button>
                                    <?php } else if ($row['status'] == 5) { ?>
                                        <button type="button"
                                            style=" background-color: #D673D3;color:white;border: 1px solid orange"
                                            class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalUncomplete<?= $j ?>')"><?= $statusText ?></button>
                                    <?php } else if ($row['status'] == 6) { ?>
                                        <button type="button"
                                            style=" background-color: green;color:white;border: 1px solid orange"
                                            class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalUncomplete<?= $j ?>')"><?= $statusText ?></button>
                                    <?php } ?>

                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                    <!-- modal -->
                                    <div id="workflowModalUncomplete<?= $j ?>" class="modal" style="display: none;">
                                        <div class="p-5 d-flex justify-content-center gap-4">
                                            <div class="modal-content job-modal">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">รายละเอียดงาน</h1>
                                                    <button type="button" class="btn-close" onclick="toggleModal('#workflowModalUncomplete<?= $j ?>')"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="system/insert.php" method="POST">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <label>หมายเลขงาน</label>
                                                                <input type="text" class="form-control"
                                                                    value="<?= $row['id'] ?>" disabled>
                                                            </div>
                                                            <div class="col-6">
                                                                <label>วันที่</label>
                                                                <input type="text" class="form-control"
                                                                    value="<?= $row['date_report'] ?>" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-4">
                                                                <label>เวลาแจ้ง</label>
                                                                <input type="time" class="form-control"
                                                                    value="<?= $row['time_report'] ?>" disabled>
                                                            </div>
                                                            <div class="col-4">
                                                                <label>เวลารับงาน</label>
                                                                <input type="text" class="form-control"
                                                                    value="<?= $row['take'] ?>" disabled>
                                                            </div>
                                                            <div class="col-4">
                                                                <?php if ($row['status'] == 3 && $row['close_date'] == "" || $row['close_date'] == null) { ?>
                                                                    <label>เวลาปิดงาน (ถ้ามี)</label>
                                                                    <input type="time" class="form-control" id="time_report"
                                                                        name="close_date">
                                                                <?php } else { ?>
                                                                    <label>เวลาปิดงาน (ถ้ามี)</label>
                                                                    <input value="<?= $row['close_date'] ?>" type="time"
                                                                        class="form-control" id="time_report"
                                                                        name="close_date">
                                                                <?php } ?>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-6">
                                                                <label>ผู้แจ้ง</label>
                                                                <input type="text" class="form-control"
                                                                    value="<?= $row['reporter'] ?>" disabled>
                                                            </div>
                                                            <div class="col-6">
                                                                <label>หน่วยงาน</label>
                                                                <?php
                                                                $sql = "SELECT depart_name FROM depart WHERE depart_id = ?";
                                                                $stmt = $conn->prepare($sql);
                                                                $stmt->execute([$row['department']]);
                                                                $departRow = $stmt->fetch(PDO::FETCH_ASSOC);
                                                                ?>

                                                                <input type="text" class="form-control"
                                                                    value="<?= $departRow['depart_name'] ?>" disabled>

                                                                <input type="hidden" name="department"
                                                                    value="<?= $row['department'] ?>">
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-6">
                                                                <label>เบอร์ติดต่อกลับ</label>
                                                                <input type="text" class="form-control"
                                                                    value="<?= $row['tel'] ?>" disabled>
                                                            </div>
                                                            <div class="col-6">
                                                                <label for="deviceInput">อุปกรณ์</label>
                                                                <input type="text" class="form-control" id="deviceInput" name="deviceName"
                                                                    value="<?= $row['deviceName'] ?>">
                                                                <input type="hidden" id="deviceId">
                                                            </div>

                                                        </div>

                                                        <div class="row">
                                                            <div class="col-6">
                                                                <label>หมายเลขครุภัณฑ์ (ถ้ามี)</label>
                                                                <input value="<?= $row['number_device'] ?>" type="text"
                                                                    class="form-control" name="number_devices">
                                                            </div>
                                                            <div class="col-6">
                                                                <label>หมายเลข IP addrees</label>
                                                                <input type="text" class="form-control"
                                                                    value="<?= $row['ip_address'] ?>" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <label>อาการที่ได้รับแจ้ง</label>
                                                                <input type="text" class="form-control"
                                                                    value="<?= $row['report'] ?>" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <label>รูปแบบการทำงาน</label>
                                                                <select class="form-select" name="device"
                                                                    aria-label="Default select example">
                                                                    <option value="<?= $row['device'] ?: '' ?>"
                                                                        selected>
                                                                        <?= !empty($row['device']) ? $row['device'] : 'ไม่มี' ?>
                                                                    </option>
                                                                    <?php
                                                                    $sql = "SELECT * FROM workinglist";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->execute();
                                                                    $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                    foreach ($checkD as $d) {
                                                                        if ($d['workingName'] != $row['device']) {
                                                                    ?>
                                                                            <option value="<?= $d['workingName'] ?>">
                                                                                <?= $d['workingName'] ?>
                                                                            </option>
                                                                    <?php }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-6">
                                                                <label>หมายเลขใบเบิก</label>
                                                                <?php if (empty($row['withdraw'])) { ?>
                                                                    <input disabled type="text"
                                                                        class="form-control withdrawInput" name="withdraw"
                                                                        id="withdrawInput<?= $j ?>">
                                                                <?php } else { ?>
                                                                    <input disabled value="<?= $row['withdraw'] ?>"
                                                                        type="text" class="form-control withdrawInput"
                                                                        name="withdraw" id="withdrawInput<?= $j ?>">
                                                                    <input type="hidden" value="<?= $row['withdraw'] ?>"
                                                                        class="form-control withdrawInput"
                                                                        id="withdrawInputHidden<?= $j ?>" name="withdraw2">
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <label>รายละเอียด</label>
                                                                <input value="<?= $row['description'] ?>" type="text"
                                                                    class="form-control" name="description">
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <label>หมายเหตุ</label>
                                                                <input value="<?= $row['note'] ?>" type="text"
                                                                    class="form-control" name="note">
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-6">
                                                                <label>ผู้คีย์งาน</label>
                                                                <input value="<?= $row['create_by'] ?>" type="text"
                                                                    class="form-control" name="create_by" disabled>
                                                            </div>
                                                            <div class="col-6">
                                                                <label>ซ่อมครั้งที่</label>
                                                                <input value="<?= $row['note'] ?>" type="text"
                                                                    class="form-control" name="note">
                                                            </div>
                                                        </div>

                                                        <hr class="mb-2">
                                                        <!-- !!!!! -->
                                                        <h4 class="mt-0 mb-3" id="staticBackdropLabel">งานคุณภาพ</h4>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <label>ปัญหาอยู่ใน SLA หรือไม่</label>
                                                                <select class="form-select" name="sla"
                                                                    aria-label="Default select example">
                                                                    <option value="<?= $row['sla'] ?: '' ?>" selected>
                                                                        <?= !empty($row['sla']) ? $row['sla'] : 'ไม่มี' ?>
                                                                    </option>
                                                                    <?php
                                                                    $sql = "SELECT * FROM sla";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->execute();
                                                                    $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                    foreach ($checkD as $d) {
                                                                        if ($d['sla_name'] != $row['sla']) {
                                                                    ?>
                                                                            <option value="<?= $d['sla_name'] ?>">
                                                                                <?= $d['sla_name'] ?>
                                                                            </option>
                                                                    <?php }
                                                                    }
                                                                    ?>

                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-12">
                                                                <label>เป็นตัวชี้วัดหรือไม่</label>
                                                                <select class="form-select" name="kpi"
                                                                    aria-label="Default select example">
                                                                    <option value="<?= $row['kpi'] ?: '' ?>" selected>
                                                                        <?= !empty($row['kpi']) ? $row['kpi'] : 'ไม่มี' ?>
                                                                    </option>
                                                                    <?php
                                                                    $sql = "SELECT * FROM kpi";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->execute();
                                                                    $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                    foreach ($checkD as $d) {
                                                                        if ($d['kpi_name'] != $row['kpi']) {
                                                                    ?>
                                                                            <option value="<?= $d['kpi_name'] ?>">
                                                                                <?= $d['kpi_name'] ?>
                                                                            </option>
                                                                    <?php }
                                                                    }
                                                                    ?>

                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-12">
                                                                <label>Activity Report</label>
                                                                <select class="form-select" name="problem"
                                                                    aria-label="Default select example">
                                                                    <?php
                                                                    $sql = "SELECT * FROM problemlist";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->execute();
                                                                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC); ?>
                                                                    <option value="<?= $row['problem'] ?: '' ?>"
                                                                        selected>
                                                                        <?= !empty($row['problem']) ? $row['problem'] : 'ไม่มี' ?>
                                                                    </option>
                                                                    <?php foreach ($data as $d) {
                                                                        if ($row['problem'] != $d['problemName']) { ?>
                                                                            <option value="<?= $d['problemName'] ?>">
                                                                                <?= $d['problemName'] ?>
                                                                            </option>
                                                                    <?php }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <!-- !!!!! -->

                                                        <?php
                                                        $sql = "SELECT * FROM orderdata";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->execute();
                                                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                        if (!$result) {
                                                            $newValueToCheck = "1/67";
                                                        } else {
                                                            foreach ($result as $d) {
                                                                if ($d) {
                                                                    $selectedValue = $d['numberWork'];
                                                                    list($numerator, $denominator) = explode('/', $selectedValue);

                                                                    $currentDate = new DateTime();

                                                                    // Set $october10 to be October 10 of the current year
                                                                    $october1 = new DateTime($currentDate->format('Y') . '-10-10');

                                                                    // Check if the current date is after October 10
                                                                    if ($currentDate > $october1) {
                                                                        // Add 1 to the numerator and set the denominator to 1
                                                                        $newNumerator = intval($numerator) + 1;
                                                                        //$newDenominator = intval($denominator) + 1; // เริ่มต้นที่ 1 ในปีถัดไป
                                                                        $newDenominator = intval($denominator);
                                                                    } else {
                                                                        // Keep the numerator and increment the denominator
                                                                        $newNumerator = intval($numerator) + 1;
                                                                        $newDenominator = intval($denominator);
                                                                    }

                                                                    $newValueToCheck = $newNumerator . '/' . $newDenominator;
                                                                }
                                                            }
                                                        }
                                                        ?>

                                                </div>
                                                <div class="d-flex justify-content-end gap-3">

                                                    <button type="button" class="btn btn-warning toggleWithdrawBtn"
                                                        data-row-index="<?= $j ?>">เปิดเบิกอะไหล่</button>

                                                </div>
                                                <div class="modal-footer"
                                                    style="justify-content: space-between; border: none;">
                                                    <div>
                                                        <button type="submit" class="btn btn-danger"
                                                            name="disWork">คืนงาน</button>
                                                    </div>
                                                    <div class="d-flex justify-content-end gap-3">
                                                        <!-- <button disabled type="submit" name="withdrawSubmit"
                                                                class="btn btn-primary withdrawButton"
                                                                id="withdrawButton<?= $j ?>">เบิกอะไหล่</button> -->
                                                        <button type="button" class="btn btn-primary" onclick="toggleModal('#requisitionModal<?= $j ?>')">เบิก/ซ่อม</button>
                                                        <!-- <button type="submit" name="clam"
                                                                class="btn btn-primary">ส่งซ่อม</button> -->
                                                        <button type="submit" class="btn me-3 btn-primary"
                                                            name="Bantext">บันทึก</button>
                                                        <button type="submit" name="CloseSubmit"
                                                            class="btn btn-success">ปิดงาน</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="requisitionModal<?= $j ?>" class="modal-content withdraw-modal" style="display: none;">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">ใบเบิก
                                                    </h1>
                                                    <button type="button" class="btn-close" onclick="toggleModal('#requisitionModal<?= $j ?>')"></button>
                                                </div>
                                                <form action="system/insert.php" method="post">
                                                    <div class=" p-3">
                                                        <div class="row">
                                                            <input type="hidden" name="id_ref" value="<?= $row['id'] ?>">
                                                            <div class="col-sm-4">
                                                                <div class="mb-3">
                                                                    <label id="basic-addon1">วันที่ออกใบเบิก</label>
                                                                    <input required type="date" name="dateWithdraw" class="form-control thaiDateInput">
                                                                </div>
                                                            </div>

                                                            <div class="col-sm-4">
                                                                <div class="mb-3">
                                                                    <label for="inputGroupSelect01">ประเภทการเบิก</label>
                                                                    <select required class="form-select" name="ref_withdraw" id="inputGroupSelect01">
                                                                        <?php
                                                                        $sql = 'SELECT * FROM withdraw';
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->execute();
                                                                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                        foreach ($result as $row) { ?>

                                                                            <option value="<?= $row['withdraw_id'] ?>"><?= $row['withdraw_name'] ?></option>
                                                                        <?php }
                                                                        ?>

                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-sm-4">
                                                                <div class="mb-3">
                                                                    <label for="inputGroupSelect01">ประเภทงาน</label>
                                                                    <select required class="form-select" name="ref_work" id="inputGroupSelect01">
                                                                        <?php
                                                                        $sql = 'SELECT * FROM listwork';
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->execute();
                                                                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                        foreach ($result as $row) { ?>

                                                                            <option value="<?= $row['work_id'] ?>"><?= $row['work_name'] ?></option>
                                                                        <?php }
                                                                        ?>

                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-sm-6">
                                                                <div class="mb-3">
                                                                    <label for="inputGroupSelect01">รายการอุปกรณ์</label>
                                                                    <select required class="form-select" name="ref_device" id="inputGroupSelect01">
                                                                        <?php
                                                                        $sql = 'SELECT * FROM device';
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->execute();
                                                                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                        foreach ($result as $row) { ?>
                                                                            <option value="<?= $row['device_id'] ?>"><?= $row['device_name'] ?></option>
                                                                        <?php }
                                                                        ?>

                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-sm-6">
                                                                <div class="mb-3">
                                                                    <label id="basic-addon1">หมายเลขครุภัณฑ์</label>
                                                                    <div id="device-number-container">
                                                                        <div class="d-flex device-number-row">
                                                                            <input type="text" name="number_device[]" class="form-control">
                                                                            <button type="button" class="btn btn-danger p-2 ms-3" style="visibility: hidden;">ลบ</button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex justify-content-end">
                                                                        <button type="button" id="add-device-number" class="btn btn-success mt-2 align-self-end">+ เพิ่มหมายเลขครุภัณฑ์</button>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-sm-6">
                                                                <div class="mb-3">
                                                                    <label for="departInput">หน่วยงาน</label>
                                                                    <input type="text" class="form-control" id="departInput" name="ref_depart">
                                                                    <input type="hidden" id="departId" name="depart_id">
                                                                </div>

                                                                <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
                                                                <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
                                                                <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

                                                                <script>
                                                                    $(function() {
                                                                        function setupAutocomplete(type, inputId, hiddenInputId, url, addDataUrl, confirmMessage) {
                                                                            let inputChanged = false;

                                                                            $(inputId).autocomplete({
                                                                                    source: function(request, response) {
                                                                                        $.ajax({
                                                                                            url: url,
                                                                                            dataType: "json",
                                                                                            data: {
                                                                                                term: request.term,
                                                                                                type: type
                                                                                            },
                                                                                            success: function(data) {
                                                                                                response(data); // Show suggestions
                                                                                            }
                                                                                        });
                                                                                    },
                                                                                    minLength: 1,
                                                                                    autoFocus: true,
                                                                                    select: function(event, ui) {
                                                                                        $(inputId).val(ui.item.label); // Fill input with label
                                                                                        $(hiddenInputId).val(ui.item.value); // Fill hidden input with ID
                                                                                        return false; // Prevent default behavior
                                                                                    }
                                                                                })
                                                                                .data("ui-autocomplete")._renderItem = function(ul, item) {
                                                                                    return $("<li>")
                                                                                        .append("<div>" + item.label + "</div>")
                                                                                        .appendTo(ul);
                                                                                };

                                                                            $(inputId).on("autocompletefocus", function(event, ui) {
                                                                                // You can log or do something here but won't change the input value
                                                                                console.log("Item highlighted: ", ui.item.label);
                                                                                return false;
                                                                            });

                                                                            $(inputId).on("keyup", function() {
                                                                                inputChanged = true;
                                                                            });

                                                                            $(inputId).on("blur", function() {
                                                                                if (inputChanged) {
                                                                                    const userInput = $(this).val().trim();
                                                                                    if (userInput === "") return;

                                                                                    let found = false;
                                                                                    $(this).autocomplete("instance").menu.element.find("div").each(function() {
                                                                                        if ($(this).text() === userInput) {
                                                                                            found = true;
                                                                                            return false;
                                                                                        }
                                                                                    });

                                                                                    if (!found) {
                                                                                        Swal.fire({
                                                                                            title: confirmMessage,
                                                                                            icon: "info",
                                                                                            showCancelButton: true,
                                                                                            confirmButtonText: "ใช่",
                                                                                            cancelButtonText: "ไม่"
                                                                                        }).then((result) => {
                                                                                            if (result.isConfirmed) {
                                                                                                $.ajax({
                                                                                                    url: addDataUrl,
                                                                                                    method: "POST",
                                                                                                    data: {
                                                                                                        dataToInsert: userInput
                                                                                                    },
                                                                                                    success: function(response) {
                                                                                                        console.log("Data inserted successfully!");
                                                                                                        $(hiddenInputId).val(response); // Set inserted ID
                                                                                                    },
                                                                                                    error: function(xhr, status, error) {
                                                                                                        console.error("Error inserting data:", error);
                                                                                                    }
                                                                                                });
                                                                                            } else {
                                                                                                $(inputId).val(""); // Clear input
                                                                                                $(hiddenInputId).val("");
                                                                                            }
                                                                                        });
                                                                                    }
                                                                                }
                                                                                inputChanged = false; // Reset the flag
                                                                            });
                                                                        }

                                                                        // Setup autocomplete for "หน่วยงาน" (departInput)
                                                                        setupAutocomplete(
                                                                            "depart",
                                                                            "#departInput",
                                                                            "#departId",
                                                                            "autocomplete.php",
                                                                            "insertDepart.php",
                                                                            "คุณต้องการเพิ่มข้อมูลนี้หรือไม่?"
                                                                        );
                                                                    });
                                                                </script>
                                                            </div>

                                                            <div class="col-sm-6">
                                                                <div class="mb-3">
                                                                    <label for="inputGroupSelect01">ผู้รับเรื่อง
                                                                    </label>
                                                                    <input required type="text" name="ref_username" class="form-control" value="<?= $admin ?>" readonly>
                                                                </div>
                                                            </div>

                                                            <div class="col-sm-12">
                                                                <div class="mb-3">
                                                                    <label id="basic-addon1">เหตุผลและความจำเป็น</label>
                                                                    <input type="text" name="reason" class="form-control">
                                                                </div>
                                                            </div>

                                                            <div class="col-sm-4">
                                                                <div class="mb-3">
                                                                    <label for="inputGroupSelect01">ร้านที่เสนอราคา
                                                                    </label>
                                                                    <select required class="form-select" name="ref_offer" id="inputGroupSelect01">
                                                                        <?php
                                                                        $sql = 'SELECT * FROM offer';
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->execute();
                                                                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                        foreach ($result as $row) { ?>
                                                                            <option value="<?= $row['offer_id'] ?>"><?= $row['offer_name'] ?></option>
                                                                        <?php }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-sm-4">
                                                                <div class="mb-3">
                                                                    <label for="inputGroupSelect01">เลขที่ใบเสนอราคา
                                                                    </label>
                                                                    <input value="-" type="text" name="quotation" class="form-control">
                                                                </div>
                                                            </div>

                                                            <div class="col-sm-4">
                                                                <div class="mb-3">
                                                                    <label for="inputGroupSelect01">สถานะ
                                                                    </label>
                                                                    <select required class="form-select" name="status" id="inputGroupSelect01">
                                                                        <option value="1">รอรับเอกสารจากหน่วยงาน</option>
                                                                        <option value="2">รอส่งเอกสารไปพัสดุ</option>
                                                                        <option value="3">รอพัสดุสั่งของ</option>
                                                                        <option value="4">รอหมายเลขครุภัณฑ์</option>
                                                                        <option value="5">ปิดงาน</option>
                                                                        <option value="6">ยกเลิก</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <!-- <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">อาการรับแจ้ง</label>
                                                                        <input type="text" name="report" class="form-control">
                                                                    </div>
                                                                </div> -->

                                                            <div class="col-sm-12">
                                                                <div class="mb-3">
                                                                    <label for="inputGroupSelect01">หมายเหตุ
                                                                    </label>
                                                                    <input value="-" type="text" name="note" class="form-control">
                                                                </div>
                                                            </div>

                                                            <table id="pdf" style="width: 100%;" class="table">
                                                                <thead class="table-primary">
                                                                    <tr class="text-center">
                                                                        <th style="text-align:center;width: 10%;">ลำดับ</th>
                                                                        <th style="text-align:center;width: 10%;">รายการ</th>
                                                                        <th style="text-align:center;width: 20%;">คุณสมบัติ</th>
                                                                        <th style="text-align:center;width: 10%;">จำนวน</th>
                                                                        <th style="text-align:center; width: 10%;">ราคา</th>
                                                                        <th style="text-align:center; width: 10%;">หน่วย</th>
                                                                        <th style="text-align:center; width: 10%;"></th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="table-body">
                                                                    <tr class="text-center">
                                                                        <th scope="row">1</th>
                                                                        <td>
                                                                            <select style="width: 120px" class="form-select device-select" name="list[]" data-row="1">
                                                                                <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                                                                                <!-- Populate options dynamically -->
                                                                                <?php
                                                                                $sql = "SELECT * FROM device_models ORDER BY models_name ASC";
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->execute();
                                                                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                                foreach ($result as $d) {
                                                                                ?>
                                                                                    <option value="<?= $d['models_id'] ?>"><?= $d['models_name'] ?></option>
                                                                                <?php
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </td>
                                                                        <td><textarea style="width: 150px" rows="2" maxlength="60" name="quality[]" class="form-control"></textarea></td>
                                                                        <td><input style="width: 2rem; margin: 0 auto;" type="text" name="amount[]" class="form-control"></td>
                                                                        <td><input style="width: 4rem;" type="text" name="price[]" class="form-control"></td>
                                                                        <td><input style="width: 4rem;" type="text" name="unit[]" class="form-control"></td>
                                                                        <td><button type="button" class="btn btn-danger" style="visibility: hidden;">ลบ</button></td>
                                                                    </tr>

                                                                </tbody>

                                                            </table>

                                                            <div class="d-flex justify-content-end">
                                                                <button type="button" id="add-row" class="btn btn-success">+ เพิ่มแถว</button>
                                                            </div>
                                                            <div class="w-100 d-flex justify-content-center">
                                                                <button type="submit" name="submit_with_work" class="w-100 btn btn-primary mt-3">บันทึกข้อมูล</button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </form>

                                            </div>


                                        </div>

                                    </div>

                            </form>
        </div>
        </td>
        </tr>
    <?php
                    }
    ?>
    </tbody>
    </table>

    </div>
</div>



<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script>
    const now = new Date();

    // Format the time as HH:mm
    const currentTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

    // Set the current time as the default value for the input fields
    const timeReportInputs = document.querySelectorAll('.time_report');
    timeReportInputs.forEach(input => input.value = currentTime);
</script>
<script>
    $(function() {
        function setupAutocomplete(type, inputId, hiddenInputId, url, addDataUrl, confirmMessage) {
            let inputChanged = false;

            $(inputId).autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: url,
                            dataType: "json",
                            data: {
                                term: request.term,
                                type: type
                            },
                            success: function(data) {
                                response(data); // Show suggestions
                            }
                        });
                    },
                    minLength: 1,
                    autoFocus: true,
                    select: function(event, ui) {
                        $(inputId).val(ui.item.label); // Fill input with label
                        $(hiddenInputId).val(ui.item.value); // Fill hidden input with ID
                        return false; // Prevent default behavior
                    }
                })
                .data("ui-autocomplete")._renderItem = function(ul, item) {
                    return $("<li>")
                        .append("<div>" + item.label + "</div>")
                        .appendTo(ul);
                };

            $(inputId).on("autocompletefocus", function(event, ui) {
                // You can log or do something here but won't change the input value
                console.log("Item highlighted: ", ui.item.label);
                return false;
            });

            $(inputId).on("keyup", function() {
                inputChanged = true;
            });

            $(inputId).on("blur", function() {
                if (inputChanged) {
                    const userInput = $(this).val().trim();
                    if (userInput === "") return;

                    let found = false;
                    $(this).autocomplete("instance").menu.element.find("div").each(function() {
                        if ($(this).text() === userInput) {
                            found = true;
                            return false;
                        }
                    });

                    if (!found) {
                        Swal.fire({
                            title: confirmMessage,
                            icon: "info",
                            showCancelButton: true,
                            confirmButtonText: "ใช่",
                            cancelButtonText: "ไม่"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: addDataUrl,
                                    method: "POST",
                                    data: {
                                        dataToInsert: userInput
                                    },
                                    success: function(response) {
                                        console.log("Data inserted successfully!");
                                        $(hiddenInputId).val(response); // Set inserted ID
                                    },
                                    error: function(xhr, status, error) {
                                        console.error("Error inserting data:", error);
                                    }
                                });
                            } else {
                                $(inputId).val(""); // Clear input
                                $(hiddenInputId).val("");
                            }
                        });
                    }
                }
                inputChanged = false; // Reset the flag
            });
        }

        // Initialize autocomplete for all dynamically generated inputs
        $("input[id^='deviceInput']").each(function() {
            const i = $(this).attr("id").replace("deviceInput", ""); // Extract index
            setupAutocomplete(
                "device",
                `#deviceInput${i}`,
                `#deviceId${i}`,
                "autocomplete.php",
                "insertDevice.php",
                "คุณต้องการเพิ่มข้อมูลอุปกรณ์นี้หรือไม่?"
            );
        });
    });
</script>
<script>
    function toggleModal(modalId) {
        const modal = document.querySelector(modalId);
        if (modal) {
            modal.style.display = modal.style.display === "none" || modal.style.display === "" ? "block" : "none";
        } else {
            console.error("Modal not found:", modalId);
        }
    }
</script>
<script>
    //เพิ่มแถวหมายเลขครุภัณฑ์
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id.startsWith('add-device-number-')) {
            const modalId = e.target.id.split('-').pop();
            const container = document.querySelector(`#device-number-container-${modalId}`);
            const newRow = document.createElement('div');
            newRow.className = 'd-flex device-number-row';
            newRow.innerHTML = `
<input type="text" name="number_device[]" class="form-control mt-2">
<button type="button" class="btn btn-warning mt-2 p-2 remove-field ms-3">ลบ</button>
        `;
            container.appendChild(newRow);
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-field')) {
            e.target.closest('.device-number-row').remove();
        }
    });
</script>

<script>
    // ฟังก์ชันสำหรับแปลงปีคริสต์ศักราชเป็นปีพุทธศักราช
    function convertToBuddhistYear(englishYear) {
        return englishYear + 543;
    }

    // ดึงอินพุทธศักราชปัจจุบัน
    const currentGregorianYear = new Date().getFullYear();
    const currentBuddhistYear = convertToBuddhistYear(currentGregorianYear);

    // หากคุณมีหลาย input ที่ต้องการกำหนดค่า
    const thaiDateInputs = document.querySelectorAll('.thaiDateInput');

    thaiDateInputs.forEach((input) => {
        // แปลงปีปัจจุบันเป็นปีพุทธศักราชแล้วกำหนดค่าให้กับ input
        const currentDate = new Date();
        input.value = currentBuddhistYear + '-' +
            ('0' + (currentDate.getMonth() + 1)).slice(-2) + '-' +
            ('0' + currentDate.getDate()).slice(-2);
    });
</script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<!-- <script>
    $('#dataAll').DataTable({
        order: [
            [10, 'asc']
        ] // assuming you want to sort the first column in ascending order
    });
  
</script> -->
<?php SC5() ?>
</body>

</html>