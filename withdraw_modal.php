<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';

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

if (isset($_GET['id'])) {
    $id = $_GET['id'];
}
if (isset($_GET['withdraw'])) {
    $idwithdraw = $_GET['withdraw'];
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php bs5() ?>
    <title>สร้าง | ระบบบริหารจัดการ ศูนย์บริการซ่อมคอมพิวเตอร์</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #F9FDFF;
        }
    </style>
</head>

<body>
        <form action="system/insert.php" method="post">
            <div class="row d-flex justify-content-center">
                <div class="card p-3 col-sm-12 col-lg-6 col-md-12">
                    <div class="row">

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
                                        <input required type="text" name="number_device[]" class="form-control">
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
                                <label id="basic-addon1">อาการรับแจ้ง</label>
                                <input required type="text" name="report" class="form-control">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label id="basic-addon1">เหตุผลและความจำเป็น</label>
                                <input required type="text" name="reason" class="form-control">
                            </div>
                        </div>

                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label for="departInput">หน่วยงาน</label>
                                <input type="text" required class="form-control" id="departInput" name="ref_depart">
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

                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label for="inputGroupSelect01">ผู้รับเรื่อง
                                </label>
                                <input required type="text" name="ref_username" class="form-control" value="<?= $admin ?>" readonly>
                            </div>
                        </div>

                        <div class="col-sm-3">
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

                        <div class="col-sm-3">
                            <div class="mb-3">
                                <label for="inputGroupSelect01">เลขที่ใบเสนอราคา
                                </label>
                                <input value="-" type="text" name="quotation" class="form-control">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="mb-3">
                                <label for="inputGroupSelect01">หมายเหตุ
                                </label>
                                <input value="-" type="text" name="note" class="form-control">
                            </div>
                        </div>

                        <div class="col-sm-6">
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
                            <button type="submit" name="submit" class="w-100 btn btn-primary mt-3">บันทึกข้อมูล</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script>
        //เพิ่มแถวหมายเลขครุภัณฑ์
        document.getElementById('add-device-number').addEventListener('click', function() {
            const container = document.getElementById('device-number-container');
            const newRow = document.createElement('div');
            newRow.className = 'd-flex device-number-row';
            newRow.innerHTML = `
      <input required type="text" name="number_device[]" class="form-control mt-2">
<button type="button" class="btn btn-warning mt-2 p-2 remove-field ms-3">ลบ</button>
    `;
            container.appendChild(newRow);
        });

        document.getElementById('device-number-container').addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-field')) {
                e.target.parentElement.remove();
            }
        });
    </script>
    <script>
        // เพิ่มแถวตาราง
        let rowIndex = 1;

        document.getElementById('add-row').addEventListener('click', function() {
            rowIndex++;
            const tableBody = document.getElementById('table-body');
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
                                  <td><input style="width: 2rem; margin: 0 auto;" type="text" name="amount[]" class="form-control"></td>
                                  <td><input style="width: 4rem;" type="text" name="price[]" class="form-control"></td>
                                  <td><input style="width: 4rem;" type="text" name="unit[]" class="form-control"></td>
<td><button type="button" class="btn btn-warning remove-row">ลบ</button></td>
`;
            tableBody.appendChild(newRow);
        });

        document.getElementById('table-body').addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-row')) {
                e.target.parentElement.parentElement.remove();
                updateRowNumbers();
            }
        });

        function updateRowNumbers() {
            const rows = document.querySelectorAll('#table-body tr');
            rows.forEach((row, index) => {
                row.querySelector('th').textContent = index + 1;
            });
            rowIndex = rows.length;
        }

        $(document).ready(function() {
            $('.device-select').change(function() {
                var models_id = $(this).val();
                var row = $(this).data('row');

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
                                $('#quality' + row).val(data.quality);
                                $('#price' + row).val(data.price);
                                $('#unit' + row).val(data.unit);
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
    <?php SC5() ?>
</body>

</html>