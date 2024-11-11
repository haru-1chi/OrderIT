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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                            <input type="text" class="form-control" id="departInput" name="ref_depart" required>
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

            <?php
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
            var inputChanged = false; // สร้างตัวแปรเช็คสถานะสำหรับตรวจสอบการเปลี่ยนแปลงในช่อง input

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

            // Check if the entered value is not in autocomplete
            $("#departInput").on("keyup", function() {
                inputChanged = true; // เมื่อมีการพิมพ์ในช่อง input เปลี่ยนสถานะเป็น true
            });

            // Check if the input field loses focus
            $("#departInput").on("blur", function() {
                if (inputChanged) { // ตรวจสอบเฉพาะเมื่อมีการเปลี่ยนแปลงในช่อง input เท่านั้น
                    var userInput = $(this).val();
                    if (userInput.trim() === "") {
                        return; // ไม่มีข้อมูลถูกป้อน ออกจากฟังก์ชัน
                    }
                    var found = false;
                    $(this).autocomplete("instance").menu.element.find("div").each(function() {
                        if ($(this).text() === userInput) {
                            found = true;
                            return false;
                        }
                    });
                    if (!found) {
                        Swal.fire({
                            title: "คุณต้องการเพิ่มข้อมูลนี้หรือไม่?",
                            icon: "info",
                            showCancelButton: true,
                            confirmButtonText: "ใช่",
                            cancelButtonText: "ไม่"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: "insertDepart.php", // เปลี่ยนเป็น URL ของไฟล์ที่ใช้ในการ insert ข้อมูล
                                    method: "POST",
                                    data: {
                                        dataToInsert: userInput // ส่งข้อมูลที่ต้องการ insert
                                    },
                                    success: function(response) {
                                        // ทำสิ่งที่ต้องการหลังจาก insert เสร็จสมบูรณ์
                                        console.log("Data inserted successfully!");
                                        $("#departId").val(response); // ใช้ค่าที่ได้จากการ insert ในการกำหนดค่าของ input hidden

                                    },
                                    error: function(xhr, status, error) {
                                        // กรณีเกิดข้อผิดพลาดในการ insert
                                        console.error("Error inserting data:", error);
                                    }
                                });
                            } else {
                                // Don't add the data
                                // For example, clear the input field
                                $("#departInput").val("");
                                $("#departId").val("");
                            }
                        });
                    }
                }
                inputChanged = false; // รีเซ็ตค่าตรวจสอบเมื่อสูญเสียการโฟกัส
            });
        });
    </script>
    <script>
        // ฟังก์ชันสำหรับแปลงปีคริสต์ศักราชเป็นปีพุทธศักราช
        function convertToBuddhistYear(englishYear) {
            return englishYear +543 ;
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