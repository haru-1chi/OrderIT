<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';
date_default_timezone_set("Asia/Bangkok");

if (!isset($_SESSION["admin_log"])) {
    $_SESSION["warning"] = "กรุณาเข้าสู่ระบบ";
    header("location: login.php");
    exit;
}
$admin = $_SESSION['admin_log'];
$sql = "SELECT CONCAT(fname, ' ', lname) AS full_name FROM admin WHERE username = :admin LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":admin", $admin);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$name = $result['full_name'] ?? '-';

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

        .choices {
            margin-bottom: 0 !important;
        }

        .choices__inner {
            border-radius: 0.375rem !important;
            min-height: 33px !important;
            border: 1px solid #ced4da;
            padding: 0 !important;
            background-color: #fff !important;
            font-size: 1rem !important;
            line-height: 1.5;
        }

        .choices__inner.is-invalid {
            border-color: #dc3545 !important;
        }

        .choices__list--single {
            padding: 0 !important;
        }

        .choices__list--dropdown {
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
        }

        .choices__item--selectable {
            padding: 0.375rem 0.75rem;
        }

        .choices.is-focused .choices__inner,
        .choices.is-open .choices__inner {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, .25) !important;
        }
    </style>
</head>

<body>
    <?php navbar() ?>
    <div class="container" style="width: 50%;">
        <h1 class="text-center my-4">สร้างงาน</h1>

        <?php foreach (['error' => 'danger', 'warning' => 'warning', 'success' => 'success'] as $key => $class): ?>
            <?php if (isset($_SESSION[$key])): ?>
                <div class="alert alert-<?= $class ?>" role="alert">
                    <?= htmlspecialchars($_SESSION[$key], ENT_QUOTES, 'UTF-8') ?>
                    <?php unset($_SESSION[$key]); ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <div class="card card-body rounded-4 mt-5 shadow-sm">
            <form action="system/insert.php" method="POST">
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label" for="date_report">วันที่แจ้ง</label>
                        <input required type="date" name="date_report" id="date_report" class="form-control auto-date">
                    </div>

                    <div class="col-6 mb-3">
                        <label class="form-label" for="time_report">เวลาที่แจ้ง</label>
                        <input type="time" name="time_report" id="time_report" class="form-control auto-time">
                    </div>

                    <div class="col-4 mb-3">
                        <label class="form-label" for="reporter">ผู้แจ้ง</label>
                        <input class="form-control" value="-" type="text" name="reporter" id="reporter" required>
                    </div>
                    <div class="col-4 mb-3">
                        <label class="form-label" for="departInput">หน่วยงาน</label>
                        <select class="form-select" id="departSelect" name="depart_id" required></select>
                    </div>

                    <div class="col-4 mb-3">
                        <label class="form-label" for="contactInput">เบอร์โทร</label>
                        <input class="form-control" value="-" type="text" id="contactInput" name="tel" required>
                    </div>

                    <div class="col-4 mb-3">
                        <label class="form-label" for="deviceInput">อุปกรณ์</label>
                        <select class="form-select" id="deviceSelect" name="deviceName" required></select>
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
        document.addEventListener("DOMContentLoaded", () => {
            const now = new Date();

            // Format current date
            const formattedDate = now.toISOString().split('T')[0];
            document.querySelectorAll('.auto-date').forEach(input => {
                input.value = formattedDate;
            });

            // Format current time as HH:mm
            const currentTime = now.toTimeString().slice(0, 5); // HH:mm
            document.querySelectorAll('.auto-time').forEach(input => {
                input.value = currentTime;
            });
        });
    </script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function setupChoicesAutocomplete({
                type,
                selectSelector,
                sourceUrl,
                notFoundMessage = "ไม่พบข้อมูลในระบบ",
                resetValue = '',
                defaultHiddenId = ''
            }) {
                const $select = document.querySelector(selectSelector);

                const choices = new Choices($select, {
                    searchEnabled: true,
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: `กรุณาเลือก...`,
                    searchPlaceholderValue: 'พิมพ์เพื่อค้นหา...',
                    removeItemButton: false,
                    itemSelectText: '',
                    searchResultLimit: -1
                });

                // Fetch function
                async function fetchData(term = '') {
                    try {
                        const response = await fetch(`${sourceUrl}?term=${encodeURIComponent(term)}&type=${type}`);
                        const data = await response.json();

                        choices.clearChoices();

                        if (data.length === 0) {
                            choices.setChoices([{
                                value: '',
                                label: notFoundMessage,
                                disabled: true
                            }], 'value', 'label', true);
                            return;
                        }

                        const options = [{
                                value: '',
                                label: 'กรุณาเลือก...',
                                selected: true,
                                disabled: false
                            },
                            ...data.map(item => ({
                                value: (type === 'device') ? item.label : item.value,
                                label: item.label
                            }))
                        ];

                        choices.setChoices(options, 'value', 'label', true);

                    } catch (error) {
                        console.error('Error fetching data:', error);
                    }
                }

                // Initial load (empty search)
                fetchData();
            }

            // Example usage for "depart"
            setupChoicesAutocomplete({
                type: "depart",
                selectSelector: "#departSelect",
                sourceUrl: "system_1/autocomplete.php",
                notFoundMessage: "ไม่พบหน่วยงานนี้ในระบบ",
                resetValue: "-",
                defaultHiddenId: "222"
            });

            // Example usage for "device"
            setupChoicesAutocomplete({
                type: "device",
                selectSelector: "#deviceSelect",
                sourceUrl: "system_1/autocomplete.php",
                notFoundMessage: "ไม่พบอุปกรณ์นี้ในระบบ",
                resetValue: "-",
                defaultHiddenId: "105"
            });
        });
    </script>
    <?php SC5() ?>
</body>

</html>