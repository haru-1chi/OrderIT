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

</head>

<body>
    <?php navbar() ?>
    <div class="container-fluid">

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
            <div class="d-flex justify-content-between mt-5 mb-3">

                <h1>งาน</h1>
                <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                    สร้างงาน
                </button>
            </div>
            <div class="collapse" id="collapseExample">
                <div style="border: none;" class="card card-body">
                    <form action="system/insert.php" method="POST">
                        <div class="row">
                            <div class="col-sm-4 mb-3">
                                <label class="form-label" for="timeInput">วันที่ได้รับแจ้ง</label>
                                <input required type="date" name="date_report" class="form-control thaiDateInput">
                            </div>
                            <div class="col-4 mb-3">
                                <input value="<?= $admin ?>" type="hidden" name="username" class="form-control">
                                <label class="form-label" for="timeInput">เวลาที่ได้รับแจ้ง</label>
                                <input type="time" name="time_report" class="time_report form-control">
                            </div>
                            <!-- <div class="col-4 mb-3"> -->
                            <!-- <label class="form-label">อุปกรณ์</label> -->
                            <!-- <input class="form-control" type="text" name="deviceName" required> -->
                            <input type="hidden" value="" name="deviceName">
                            <!-- </div> -->
                            <div class="col-4 mb-3">
                                <!-- <label class="form-label" for="deviceInput">อุปกรณ์</label>
                                <input class="form-control" type="text" id="deviceInput" name="device" required> -->
                                <label class="form-label" for="deviceInput">รูปแบบการทำงาน</label>
                                <select required class="form-select" name="device" aria-label="Default select example">
                                    <option value="" disabled selected>เลือกรูปแบบการทำงาน</option>
                                    <?php
                                    $sql = "SELECT * FROM workinglist";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->execute();
                                    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($row as $d) { ?>
                                        <option value="<?= $d['workingName'] ?>"><?= $d['workingName'] ?></option>
                                    <?php  }
                                    ?>

                                </select>
                            </div>
                            <div class="col-4 mb-3">
                                <label class="form-label" for="assetInput">หมายเลขครุภัณฑ์ (ถ้ามี)</label>
                                <input class="form-control" value="-" type="text" id="assetInput" name="number_device" required>
                            </div>

                            <div class="col-4 mb-3">
                                <label class="form-label" for="ipInput">หมายเลข IP address</label>
                                <input class="form-control" value="-" type="text" id="ipInput" name="ip_address" required>
                            </div>
                            <div class="col-4 mb-3">
                                <label class="form-label" for="issueInput">อาการที่ได้รับแจ้ง</label>
                                <input class="form-control" type="text" id="issueInput" name="report" required>
                            </div>
                            <div class="col-4 mb-3">
                                <label class="form-label" for="issueInput">ผู้แจ้ง</label>
                                <input class="form-control" value="-" type="text" name="reporter" required>
                            </div>
                            <div class="col-4 mb-3">
                                <label class="form-label" for="departInput">หน่วยงาน</label>
                                <input type="text" class="form-control" id="departInput" name="ref_depart">
                                <input type="hidden" id="departId" name="depart_id">
                            </div>


                            <div class="col-4 mb-3">
                                <label class="form-label" for="contactInput">เบอร์โทร</label>
                                <input class="form-control" value="-" type="text" id="contactInput" name="tel" required>
                            </div>
                            <div class="col-4 mb-3">
                                <label class="form-label" for="receiveTimeInput">เวลารับงาน</label>
                                <input class="form-control" type="time" id="receiveTimeInput" name="take">
                            </div>
                            <div class="col-4 mb-3">
                                <label class="form-label" for="problemInput">ปัญหาที่พบ</label>
                                <!-- <input type="text" class="form-control" id="problemInput" name="problem"> -->
                                <select class="form-select" name="problem" aria-label="Default select example">
                                    <option value="" disabled selected>เลือก ปัญหาที่พบ</option>

                                    <?php
                                    $sql = "SELECT * FROM problemlist";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->execute();
                                    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($row as $d) { ?>
                                        <option value="<?= $d['problemName'] ?>"><?= $d['problemName'] ?></option>
                                    <?php  }
                                    ?>

                                </select>
                            </div>

                            <div class="col-4 mb-3">
                                <label class="form-label" for="descriptionInput">รายละเอียด</label>
                                <input type="text" class="form-control" id="descriptionInput" name="description">
                            </div>
                            <!-- <div class="col-4 mb-3">
                                <label class="form-label" for="withdrawInput">หมายเลขใบเบิก</label> -->
                            <input type="hidden" class="form-control" id="withdrawInput" name="withdraw">
                            <!-- </div> -->
                            <div class="col-4 mb-3">
                                <label class="form-label" for="closeTimeInput">เวลาปิดงาน (ถ้ามี)</label>
                                <input type="time" class="form-control" id="closeTimeInput" name="close_date">
                            </div>
                            <div class="col-4 mb-3">
                                <label class="form-label" for="closeTimeInput">จำนวน</label>
                                <input type="number" value="1" class="form-control" name="countList">
                            </div>
                            <div class="d-grid gap-3">
                                <button type="submit" name="saveWork" class="btn p-3 btn-primary">บันทึก</button>
                                <button type="submit" name="saveWorkSuccess" class="btn p-3 btn-success">ปิดงาน</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <form method="post" action="export.php">
                <button name="act" class="btn btn-primary" type="submit">Export->Excel</button>
            </form>
            <hr>
            <table id="dataAll" class="table table-primary">
                <thead>
                    <tr>
                        <th class="text-center" scope="col">ลำดับ</th>
                        <th scope="col">วันที่</th>
                        <th scope="col">เวลา</th>
                        <th scope="col">ปิดงาน</th>
                        <!-- <th scope="col">อุปกรณ์</th> -->
                        <th scope="col">รูปแบบการทำงาน</th>
                        <th scope="col">หมายเลขครุภัณฑ์</th>
                        <th scope="col">อาการที่ได้รับแจ้ง</th>
                        <th scope="col">ผู้แจ้ง</th>
                        <th scope="col">หน่วยงาน</th>
                        <th scope="col">เบอร์โทร</th>
                        <th scope="col">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT dp.*,dt.depart_name 
                    FROM data_report as dp
                    LEFT JOIN depart as dt ON dp.department = dt.depart_id
                    WHERE dp.username = :username ORDER BY dp.status 
                    ";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":username", $admin);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $i = 0;
                    foreach ($result as $row) {
                        $i++;

                        $dateString = $row['date_report'];
                        $timestamp = strtotime($dateString);
                        $dateFormatted = date('d/m/Y', $timestamp);

                    ?>
                        <tr style="text-align: center;" class="text-center">
                            <td class="text-start" scope="row"><?= $row['id'] ?></td>
                            <td class="text-start" scope="row"><?= $dateFormatted ?></td>
                            <td class="text-start"><?= $row['time_report'] ?> น.</td>
                            <td class="text-start"><?= $row['close_date'] ?> น.</td>
                            <td class="text-start"><?= $row['device'] ?></td>
                            <td class="text-start"><?= $row['number_device'] ?></td>
                            <td class="text-start"><?= $row['report'] ?></td>
                            <td class="text-start"><?= $row['reporter'] ?></td>
                            <td class="text-start"><?= $row['depart_name'] ?></td>
                            <td class="text-start"><?= $row['tel'] ?></td>
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
                            }
                            ?>

                            <form action="system/insert.php" method="post">

                                <td>
                                    <?php if ($row['status'] == 1) { ?>
                                        <button type="submit" name="inTime" style=" background-color: orange;color:white;border: 1px solid orange" class="btn mb-3 btn-primary">เริ่มดำเนินการ</button>
                                    <?php } else if ($row['status'] == 2) { ?>
                                        <button type="button" style="background-color: orange;color:white;border: 1px solid orange" class="btn mb-3 btn-primary" data-bs-toggle="modal" data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                    <?php   } else if ($row['status'] == 3) { ?>
                                        <button type="button" style=" background-color: blue;color:white;border: 1px solid orange" class="btn mb-3 btn-primary" data-bs-toggle="modal" data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                    <?php  } else if ($row['status'] == 4) { ?>
                                        <button type="button" style=" background-color: green;color:white;border: 1px solid orange" class="btn mb-3 btn-primary" data-bs-toggle="modal" data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                    <?php } else if ($row['status'] == 5) { ?>
                                        <button type="button" style=" background-color: #D673D3;color:white;border: 1px solid orange" class="btn mb-3 btn-primary" data-bs-toggle="modal" data-bs-target="#workflow<?= $i ?>"><?= $statusText ?></button>
                                    <?php } ?>

                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">


                                    <div class="modal fade" id="workflow<?= $i ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h1 class="modal-title fs-5" id="staticBackdropLabel">รายละเอียดงาน</h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">

                                                    <div class="table-responsive">
                                                        <table class="table table-primary">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col"></th>
                                                                    <th scope="col"></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr class="">
                                                                    <td scope="row">หมายเลขงาน</td>
                                                                    <td scope="row"><?= $row['id'] ?></td>
                                                                </tr>
                                                                <tr class="">
                                                                    <td scope="row">เวลาแจ้ง</td>
                                                                    <td scope="row"><?= $row['time_report'] ?></td>
                                                                </tr>
                                                                <tr class="">
                                                                    <td scope="row">อุปกรณ์</td>
                                                                    <td scope="row"><?= $row['deviceName'] ?></td>
                                                                </tr>
                                                                <tr class="">
                                                                    <td scope="row">รูปแบบการทำงาน</td>
                                                                    <td scope="row"><?= $row['device'] ?></td>
                                                                </tr>
                                                                <form action="system/insert.php" method="POST">

                                                                    <tr class="">
                                                                        <td scope="row">หมายเลขครุภัณฑ์ (ถ้ามี)</td>
                                                                        <td scope="row">
                                                                            <input value="<?= $row['number_device'] ?>" type="text" class="form-control" name="number_device">
                                                                        </td>
                                                                    </tr>
                                                                    <tr class="">
                                                                        <td scope="row">หมายเลข IP addrees</td>
                                                                        <td scope="row"><?= $row['ip_address'] ?></td>
                                                                    </tr>
                                                                    <tr class="">
                                                                        <td scope="row">อาการเสีย</td>
                                                                        <td scope="row"><?= $row['report'] ?></td>
                                                                    </tr>
                                                                    <tr class="">
                                                                        <td scope="row">ผู้แจ้ง</td>
                                                                        <td scope="row"><?= $row['reporter'] ?></td>
                                                                    </tr>
                                                                    <tr class="">
                                                                        <td scope="row">หน่วยงาน</td>
                                                                        <td scope="row">
                                                                            <select class="form-select" name="department" aria-label="Default select example">
                                                                                <option value="<?= $row['department'] ?>" selected><?= $row['depart_name'] ?></option>
                                                                                <?php
                                                                                $sql = "SELECT * FROM depart";
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->execute();
                                                                                $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                foreach ($checkD as $d) {
                                                                                    if ($d['depart_id'] != $row['department']) {
                                                                                ?>
                                                                                        <option value="<?= $d['depart_id'] ?>"><?= $d['depart_name'] ?></option>
                                                                                <?php  }
                                                                                }
                                                                                ?>

                                                                            </select>
                                                                        </td>
                                                                    </tr>
                                                                    <tr class="">
                                                                        <td scope="row">เบอร์ติดต่อกลับ</td>
                                                                        <td scope="row"><?= $row['tel'] ?></td>
                                                                    </tr>
                                                                    <tr class="">
                                                                        <td scope="row">เวลารับงาน</td>
                                                                        <td scope="row"><?= $row['take'] ?></td>
                                                                    </tr>
                                                                    <tr class="">
                                                                        <td scope="row">ปัญหาเกี่ยวกับ</td>
                                                                        <td scope="row">
                                                                            <select class="form-select" name="problem" aria-label="Default select example">
                                                                                <?php
                                                                                $sql = "SELECT * FROM problemlist";
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->execute();
                                                                                $data = $stmt->fetchAll(PDO::FETCH_ASSOC); ?>
                                                                                <option value="<?= $row['problem'] ?>"><?= $row['problem'] ?></option>
                                                                                <?php foreach ($data as $d) {
                                                                                    if ($row['problem'] != $d['problemName']) { ?>
                                                                                        <option value="<?= $d['problemName'] ?>"><?= $d['problemName'] ?></option>
                                                                                <?php  }
                                                                                }
                                                                                ?>

                                                                        </td>
                                                                    <tr class="">
                                                                        <td scope="row">รายละเอียด</td>
                                                                        <td scope="row">
                                                                            <input value="<?= $row['description'] ?>" type="text" class="form-control" name="description">
                                                                        </td>
                                                                    </tr>
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
                                                                                    $newDenominator = intval($denominator) + 1; // เริ่มต้นที่ 1 ในปีถัดไป
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
                                                                    <tr class="">
                                                                        <td scope="row">หมายเลขใบเบิก</td>
                                                                        <td scope="row">
                                                                            <?php if (empty($row['withdraw'])) { ?>
                                                                                <input disabled type="text" class="form-control withdrawInput" name="withdraw" id="withdrawInput<?= $i ?>">
                                                                            <?php } else { ?>
                                                                                <input disabled value="<?= $row['withdraw'] ?>" type="text" class="form-control withdrawInput" name="withdraw" id="withdrawInput<?= $i ?>">
                                                                                <input type="hidden" value="<?= $row['withdraw'] ?>" class="form-control withdrawInput" id="withdrawInputHidden<?= $i ?>" name="withdraw2">
                                                                            <?php } ?>
                                                                        </td>
                                                                    </tr>
                                                                    <?php if ($row['status'] == 3 && $row['close_date'] == "" || $row['close_date'] == null) { ?>
                                                                        <tr class="">
                                                                            <td scope="row">เวลาปิดงาน (ถ้ามี)</td>
                                                                            <td scope="row">
                                                                                <input type="time" class="form-control" id="time_report" name="close_date">
                                                                            </td>
                                                                        </tr>
                                                                    <?php   } else { ?>
                                                                        <tr class="">
                                                                            <td scope="row">เวลาปิดงาน (ถ้ามี)</td>
                                                                            <td scope="row">
                                                                                <input value="<?= $row['close_date'] ?>" type="time" class="form-control" id="time_report" name="close_date">
                                                                            </td>
                                                                        </tr>
                                                                    <?php } ?>
                                                                    <tr>
                                                                        <td>หมายเหตุ</td>
                                                                        <td>
                                                                            <input value="<?= $row['note'] ?>" type="text" class="form-control" name="note">
                                                                        </td>
                                                                    </tr>
                                                            </tbody>
                                                        </table>
                                                        <div class="d-flex justify-content-center">
                                                            <button type="button" class="btn btn-warning toggleWithdrawBtn" data-row-index="<?= $i ?>">เปิดเบิกอะไหล่</button>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="modal-footer">
                                                    <div style="margin-right: 40px">
                                                        <button type="submit" class="btn btn-danger" name="disWork">คืนงาน</button>

                                                    </div>

                                                    <div class="d-flex justify-content-end">
                                                        <button type="submit" class="btn me-3 btn-secondary" name="Bantext">บันทึก</button>
                                                        <button disabled type="submit" name="withdrawSubmit" class="btn btn-primary me-3 withdrawButton" id="withdrawButton<?= $i ?>">เบิกอะไหล่</button>
                                                        <button type="submit" name="clam" class="btn btn-primary me-3">ส่งซ่อม</button>
                                                        <button type="submit" name="CloseSubmit" class="btn btn-success">ปิดงาน</button>
                                                    </div>
                                                </div>
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

                            </form>
        </div>
    </div>
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
<br>
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