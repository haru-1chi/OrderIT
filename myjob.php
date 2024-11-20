<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';
date_default_timezone_set("Asia/Bangkok");

if (isset($_SESSION['admin_log'])) {
    $admin = $_SESSION['admin_log'];
    $sql = "SELECT * FROM admin WHERE username = :admin";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":admin", $admin);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
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
                if (isset($_POST['checkDate'])) {
                    // Get values from form
                    $status = $_POST['status'];
                    $dateStart = $_POST['dateStart'];
                    $dateEnd = $_POST['dateEnd'];

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

                            <select class="form-select" name="status" id="numberWork" style="width: 250px;">
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

                            <input type="date" value="<?= isset($dateStart) ? $dateStart : ''; ?>" name="dateStart"
                                class="form-control" style="width: 250px;">

                            <input type="date" value="<?= isset($dateEnd) ? $dateEnd : ''; ?>" name="dateEnd"
                                class="form-control" style="width: 250px;">
                            <button type="submit" name="checkDate" class="btn btn-primary">ยืนยัน</button>
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
                            $i++;
                            $dateString = $row['date_report'];
                            $timestamp = strtotime($dateString);
                            $dateFormatted = date('d/m/Y', $timestamp);
                            // Output or process the data as needed

                        ?>
                            <tr>
                                <td class="text-start" scope="row"><?= $row['id'] ?></td>
                                <td class="text-start" scope="row"><?= $dateFormatted ?></td>
                                <td class="text-start"><?= $row['time_report'] ?> น.</td>
                                <td class="text-start"><?= $row['device'] ?></td>
                                <td class="text-start"><?= $row['number_device'] ?></td>
                                <td class="text-start"><?= $row['report'] ?></td>
                                <td class="text-start"><?= $row['reporter'] ?></td>
                                <td class="text-start"><?= $row['depart_name'] ?></td>
                                <td class="text-start"><?= $row['tel'] ?></td>
                                <td class="text-start"><?= $row['close_date'] ?> น.</td>
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
                                                class="btn mb-3 btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                        <?php } else if ($row['status'] == 3) { ?>
                                            <button type="button"
                                                style=" background-color: blue;color:white;border: 1px solid orange"
                                                class="btn mb-3 btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                        <?php } else if ($row['status'] == 4) { ?>
                                            <button type="button"
                                                style=" background-color: green;color:white;border: 1px solid orange"
                                                class="btn mb-3 btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                        <?php } else if ($row['status'] == 5) { ?>
                                            <button type="button"
                                                style=" background-color: #D673D3;color:white;border: 1px solid orange"
                                                class="btn mb-3 btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                        <?php } else if ($row['status'] == 6) { ?>
                                            <button type="button"
                                                style=" background-color: green;color:white;border: 1px solid orange"
                                                class="btn mb-3 btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                        <?php } ?>

                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                        <!-- modal -->
                                        <div class="modal fade" id="workflow<?= $i ?>" data-bs-backdrop="static"
                                            data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">รายละเอียดงาน
                                                        </h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="system/insert.php" method="POST">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>หมายเลขงาน</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['id'] ?>" disabled>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>เวลาแจ้ง</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['time_report'] ?>" disabled>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label>เวลารับงาน</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['take'] ?>" disabled>
                                                                </div>
                                                            </div>

                                                            <hr>

                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>ผู้แจ้ง</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['reporter'] ?>" disabled>
                                                                </div>
                                                            </div>
                                                            <div class="row">
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
                                                                <div class="col-6">
                                                                    <label>เบอร์ติดต่อกลับ</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['tel'] ?>" disabled>
                                                                </div>
                                                            </div>

                                                            <hr>

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
                                                                    <label>อุปกรณ์</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['deviceName'] ?>" disabled>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>หมายเลขครุภัณฑ์ (ถ้ามี)</label>
                                                                    <input value="<?= $row['number_device'] ?>" type="text"
                                                                        class="form-control" name="number_device">
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
                                                                <div class="col-12">
                                                                    <label>ปัญหาเกี่ยวกับ</label>
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
                                                            <!-- !!!!! -->
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>รายละเอียด</label>
                                                                    <input value="<?= $row['description'] ?>" type="text"
                                                                        class="form-control" name="description">
                                                                </div>
                                                            </div>
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
                                                            <div class="row">
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
                                                                <div class="col-6">
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
                                                                <div class="col-12">
                                                                    <label>หมายเหตุ</label>
                                                                    <input value="<?= $row['note'] ?>" type="text"
                                                                        class="form-control" name="note">
                                                                </div>
                                                            </div>
                                                    </div>

                                                    <div class="d-flex justify-content-end gap-3">
                                                        <button type="button" class="btn btn-warning toggleWithdrawBtn"
                                                            data-row-index="<?= $i ?>">เปิดเบิกอะไหล่</button>
                                                        <button type="submit" class="btn me-3 btn-primary"
                                                            name="Bantext">บันทึก</button>
                                                    </div>
                                                    <div class="modal-footer"
                                                        style="justify-content: space-between; border: none;">
                                                        <div>
                                                            <button type="submit" class="btn btn-danger"
                                                                name="disWork">คืนงาน</button>
                                                        </div>
                                                        <div class="d-flex justify-content-end gap-3">
                                                            <button disabled type="submit" name="withdrawSubmit"
                                                                class="btn btn-primary withdrawButton"
                                                                id="withdrawButton<?= $i ?>">เบิกอะไหล่</button>
                                                            <button type="submit" name="clam"
                                                                class="btn btn-primary">ส่งซ่อม</button>
                                                            <button type="submit" name="CloseSubmit"
                                                                class="btn btn-success">ปิดงาน</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                </form>
            </div>
            </td>
            </form>
            </tr>
        <?php
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
                    $dateStart = $_POST['dateStart'];
                    $dateEnd = $_POST['dateEnd'];

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
                            <!-- ห้ามลบ แต่เป็นค่าว่างน่าจะได้ -->

                            <input type="date" value="<?= isset($dateStart) ? $dateStart : ''; ?>" name="dateStart"
                                class="form-control" style="width: 250px;">

                            <input type="date" value="<?= isset($dateEnd) ? $dateEnd : ''; ?>" name="dateEnd"
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
                            $i++;
                            $dateString = $row['date_report'];
                            $timestamp = strtotime($dateString);
                            $dateFormatted = date('d/m/Y', $timestamp);
                            // Output or process the data as needed

                        ?>
                            <tr>
                                <td class="text-start" scope="row"><?= $row['id'] ?></td>
                                <td class="text-start" scope="row"><?= $dateFormatted ?></td>
                                <td class="text-start"><?= $row['time_report'] ?> น.</td>
                                <td class="text-start"><?= $row['device'] ?></td>
                                <td class="text-start"><?= $row['number_device'] ?></td>
                                <td class="text-start"><?= $row['report'] ?></td>
                                <td class="text-start"><?= $row['reporter'] ?></td>
                                <td class="text-start"><?= $row['depart_name'] ?></td>
                                <td class="text-start"><?= $row['tel'] ?></td>
                                <td class="text-start"><?= $row['close_date'] ?> น.</td>
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
                                                class="btn mb-3 btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                        <?php } else if ($row['status'] == 3) { ?>
                                            <button type="button"
                                                style=" background-color: blue;color:white;border: 1px solid orange"
                                                class="btn mb-3 btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                        <?php } else if ($row['status'] == 4) { ?>
                                            <button type="button"
                                                style=" background-color: green;color:white;border: 1px solid orange"
                                                class="btn mb-3 btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                        <?php } else if ($row['status'] == 5) { ?>
                                            <button type="button"
                                                style=" background-color: #D673D3;color:white;border: 1px solid orange"
                                                class="btn mb-3 btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                        <?php } else if ($row['status'] == 6) { ?>
                                            <button type="button"
                                                style=" background-color: green;color:white;border: 1px solid orange"
                                                class="btn mb-3 btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                        <?php } ?>

                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                        <!-- modal -->
                                        <div class="modal fade" id="workflow<?= $i ?>" data-bs-backdrop="static"
                                            data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">รายละเอียดงาน
                                                        </h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="system/insert.php" method="POST">
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>หมายเลขงาน</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['id'] ?>" disabled>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>เวลาแจ้ง</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['time_report'] ?>" disabled>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label>เวลารับงาน</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['take'] ?>" disabled>
                                                                </div>
                                                            </div>

                                                            <hr>

                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>ผู้แจ้ง</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['reporter'] ?>" disabled>
                                                                </div>
                                                            </div>
                                                            <div class="row">
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
                                                                <div class="col-6">
                                                                    <label>เบอร์ติดต่อกลับ</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['tel'] ?>" disabled>
                                                                </div>
                                                            </div>

                                                            <hr>

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
                                                                    <label>อุปกรณ์</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['deviceName'] ?>" disabled>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>หมายเลขครุภัณฑ์ (ถ้ามี)</label>
                                                                    <input value="<?= $row['number_device'] ?>" type="text"
                                                                        class="form-control" name="number_device">
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
                                                                <div class="col-12">
                                                                    <label>ปัญหาเกี่ยวกับ</label>
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
                                                            <!-- !!!!! -->
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>รายละเอียด</label>
                                                                    <input value="<?= $row['description'] ?>" type="text"
                                                                        class="form-control" name="description">
                                                                </div>
                                                            </div>
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
                                                            <div class="row">
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
                                                                <div class="col-6">
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
                                                                <div class="col-12">
                                                                    <label>หมายเหตุ</label>
                                                                    <input value="<?= $row['note'] ?>" type="text"
                                                                        class="form-control" name="note">
                                                                </div>
                                                            </div>
                                                    </div>

                                                    <div class="d-flex justify-content-end gap-3">
                                                        <button type="button" class="btn btn-warning toggleWithdrawBtn"
                                                            data-row-index="<?= $i ?>">เปิดเบิกอะไหล่</button>
                                                        <button type="submit" class="btn me-3 btn-primary"
                                                            name="Bantext">บันทึก</button>
                                                    </div>
                                                    <div class="modal-footer"
                                                        style="justify-content: space-between; border: none;">
                                                        <div>
                                                            <button type="submit" class="btn btn-danger"
                                                                name="disWork">คืนงาน</button>
                                                        </div>
                                                        <div class="d-flex justify-content-end gap-3">
                                                            <button disabled type="submit" name="withdrawSubmit"
                                                                class="btn btn-primary withdrawButton"
                                                                id="withdrawButton<?= $i ?>">เบิกอะไหล่</button>
                                                            <button type="submit" name="clam"
                                                                class="btn btn-primary">ส่งซ่อม</button>
                                                            <button type="submit" name="CloseSubmit"
                                                                class="btn btn-success">ปิดงาน</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                </form>
            </div>
            </td>
            </form>
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
            $("#departInput").autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: "autocomplete.php",
                            dataType: "json",
                            data: {
                                term: request.term
                            },
                            success: function(data) {
                                response(data);
                            }
                        });
                    },
                    minLength: 2,
                    select: function(event, ui) {
                        $("#departInput").val(ui.item.label);
                        $("#departId").val(ui.item.value);
                        return false;
                    },
                    autoFocus: true
                })
                .data("ui-autocomplete")._renderItem = function(ul, item) {
                    return $("<li>")
                        .append("<div>" + item.label + "</div>")
                        .appendTo(ul);
                };

            // Trigger select event when an item is highlighted
            $("#departInput").on("autocompletefocus", function(event, ui) {
                $("#departInput").val(ui.item.label);
                $("#departId").val(ui.item.value);
                return false;
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (!$.fn.DataTable.isDataTable('#dataAll')) {
                // Initialize DataTables
                $('#dataAll').DataTable({
                    order: [
                        [10, 'asc']
                    ] // assuming you want to sort the first column in ascending order
                });
            }
            document.getElementById("dataAll").addEventListener("click", function(event) {
                if (event.target.classList.contains("toggleWithdrawBtn")) {
                    toggleWithdrawInput(event.target);
                }
            });


            var toggleWithdrawBtns = document.querySelectorAll(".toggleWithdrawBtn");
            var withdrawButtons = document.querySelectorAll(".withdrawButton");

            toggleWithdrawBtns.forEach(function(btn) {
                btn.addEventListener("click", function() {
                    toggleWithdrawInput(btn);
                });
            });

            function toggleWithdrawInput(clickedBtn) {
                var rowIndex = clickedBtn.getAttribute("data-row-index");
                var withdrawInput = document.getElementById("withdrawInput" + rowIndex);
                var withdrawInputHidden = document.getElementById("withdrawInputHidden" + rowIndex);
                var withdrawButton = document.getElementById("withdrawButton" + rowIndex);

                withdrawInput.removeAttribute("disabled");

                // Update the form submission logic here
                if (withdrawInput.disabled) {
                    // Handle disabled state
                    withdrawInput.value = "<?= $row['withdraw'] !== '' ? $row['withdraw'] : $newValueToCheck ?>";
                    withdrawButton.setAttribute("disabled", "disabled");
                } else {
                    // Handle enabled state
                    withdrawInput.value = "<?= $row['withdraw'] !== '' ? $row['withdraw'] : $newValueToCheck ?>";
                    withdrawButton.removeAttribute("disabled");
                }
            }
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