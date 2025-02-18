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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #F9FDFF;
        }
    </style>
</head>

<body>
    <?php navbar() ?>
    <div class="container" style="width: 50%;">
        <h1 class="text-center my-4">สร้างงาน</h1>

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
        <div class="card card-body rounded-4 mt-5 shadow-sm">
            <form action="system/insert.php" method="POST">
                <div class="row">
                    <input value="<?= $admin ?>" type="hidden" name="username" class="form-control">
                    <input type="hidden" value="" name="device">
                    <input class="form-control" type="hidden" id="receiveTimeInput" name="take">
                    <!-- !!!!!!!!!! (ถ้าไม่ป้อนจะเป็น null) ให้ gen เป็นเวลาปัจจุบัน -->
                    <input class="form-control" type="hidden" name="problem">
                    <!-- !!!!!!!!!! บังคับไม่เป็นค่าว่าง -->
                    <input type="hidden" class="form-control" id="descriptionInput" name="description">
                    <!-- !!!!!!!!!! เป็นค่าว่างได้ -->
                    <input type="hidden" class="form-control" id="closeTimeInput" name="close_date">
                    <!-- !!!!!!!!!! เป็นค่าว่างได้ -->
                    <input type="hidden" value="1" class="form-control" name="countList">
                    <!-- !!!!!!!!! บังคับไม่เป็นค่าว่าง ให้มีค่าเริ่มต้นเป็น 1 -->



                    <div class="col-6 mb-3">
                        <label class="form-label" for="issueInput">วันที่แจ้ง</label>
                        <input required type="date" name="date_report" class="form-control thaiDateInput">
                    </div>

                    <div class="col-6 mb-3">
                        <label class="form-label" for="issueInput">เวลาที่แจ้ง</label>
                        <input type="time" name="time_report" class="time_report form-control">
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
                        <label class="form-label" for="deviceInput">อุปกรณ์</label>
                        <input class="form-control" type="text" id="deviceInput" name="deviceName" required>
                        <input type="hidden" id="deviceId" name="device_id">
                    </div>

                    <div class="col-4 mb-3">
                        <label class="form-label" for="assetInput">หมายเลขครุภัณฑ์ (ถ้ามี)</label>
                        <input class="form-control" value="-" type="text" id="assetInput" name="number_device" required>
                    </div>
                    <div class="col-4 mb-3">
                        <label class="form-label" for="ipInput">หมายเลข IP address</label>
                        <input class="form-control" value="-" type="text" id="ipInput" name="ip_address" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="issueInput">อาการที่ได้รับแจ้ง</label>
                        <textarea class="form-control " id="issueInput" name="report" rows="2" required></textarea>

                    </div>
                    <input type="hidden" class="form-control" id="withdrawInput" name="withdraw">
                    <input type="hidden" name="create_by" value="<?= htmlspecialchars($name) ?>">
                    <div class="d-grid gap-3 my-3">
                        <button type="submit" name="saveWork" class="btn p-3 btn-primary">บันทึก</button>
                        <!-- <button type="submit" name="saveWorkSuccess" class="btn p-3 btn-success">ปิดงาน</button> -->
                    </div>
                </div>
            </form>
        </div>

        <?php
        ?>
        </tbody>
        </table>
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

            // Setup autocomplete for "อุปกรณ์" (deviceInput)
            setupAutocomplete(
                "device",
                "#deviceInput",
                "#deviceId",
                "autocomplete.php",
                "insertDevice.php",
                "คุณต้องการเพิ่มข้อมูลอุปกรณ์นี้หรือไม่?"
            );
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