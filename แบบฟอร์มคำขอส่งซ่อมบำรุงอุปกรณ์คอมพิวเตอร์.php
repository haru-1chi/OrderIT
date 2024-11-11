<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';

if (!isset($_SESSION["admin_log"])) {
    $_SESSION["warning"] = "กรุณาเข้าสู่ระบบ";
    header("location: login.php");
}
$id = $_GET['workid'];

?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <title>แบบฟอร์มคำขอส่งซ่อมบำรุงอุปกรณ์คอมพิวเตอร์</title>

    <!-- Behavioral Meta Data -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <?php bs5() ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'TH SarabunIT๙';
            src: url('THSarabunIT๙/THSarabunIT๙.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'TH SarabunIT๙', sans-serif;
            padding-top: 100px;
            margin-top: -80px;
            line-height: 1.5;



        }

        tr {
            height: 1.5pt;
            line-height: 18pt;
            page-break-inside: avoid;
        }

        p {
            line-height: 10pt;
            font-size: 16pt;
        }

        .breakhere {
            page-break-after: always;
        }


        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            font-size: 16pt
        }

        td {
            height: 18.8pt;

        }

        .empty-row td {
            height: 18.8pt;
            /* กำหนดความสูงตามที่คุณต้องการ */
        }

        @page {
            size: A4;
            margin-top: 2em;
            padding-top: 2em;
            padding-left: 2em;
            padding-right: 2em;
            page-break-after: always;
        }

        footer {
            display: block;
        }

        @media print {

            html,
            body {
                width: 210mm;
                height: 297mm;
                padding-top: 2em;
                padding-left: 2em;
                padding-right: 2em;
                page-break-after: always;
            }

            /* ... the rest of the rules ... */
        }
    </style>

    <!-- กำหนดฟังก์ชันแปลงเลขไทย -->
    <script>
        function convertToThaiNumber(arabicNumber) {
            const thaiNumbers = ['๐', '๑', '๒', '๓', '๔', '๕', '๖', '๗', '๘', '๙'];
            const arabicNumbers = arabicNumber.toString().split('');

            const thaiResult = arabicNumbers.map(digit => {
                if (digit === '.') {
                    return '.';
                } else {
                    return thaiNumbers[parseInt(digit)];
                }
            });

            return thaiResult.join('');
        }
    </script>




<body onload="window.print()">
    <div class="breakhere">
        <br />
        <?php
        $sql = "SELECT od.*, dp.depart_name ,lw.work_name,dv.device_name,ad.fname,ad.lname
        FROM orderdata AS od
        INNER JOIN depart AS dp ON od.refDepart = dp.depart_id
        INNER JOIN listwork AS lw ON od.refWork = lw.work_id
        INNER JOIN device AS dv ON od.refDevice = dv.device_id
        INNER JOIN admin AS ad ON od.refUsername = ad.username
         WHERE od.id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $Device2 = "";
        $Device3 = "";

        if ($data['numberDevice2'] == "") {
            $Device2 = "";
        } else {
            $Device2 = ', ' . $data["numberDevice2"];
        }
        if ($data['numberDevice3'] == "") {
            $Device3 = "";
        } else {
            $Device3 = ', ' . $data["numberDevice3"];
        }

        function toMonthThai($m)
        {
            $monthNamesThai = array(
                "",
                "มกราคม",
                "กุมภาพันธ์",
                "มีนาคม",
                "เมษายน",
                "พฤษภาคม",
                "มิถุนายน",
                "กรกฎาคม",
                "สิงหาคม",
                "กันยายน",
                "ตุลาคม",
                "พฤศจิกายน",
                "ธันวาคม"
            );
            return $monthNamesThai[$m];
        }
        $dateWithdrawFromDB = $data['dateWithdraw']; // เปลี่ยนตามฐานข้อมูลของคุณ

        // แปลงวันที่ในรูปแบบ Y-m-d เป็น timestamp
        $timestamp = strtotime($dateWithdrawFromDB);

        // ดึงเดือน
        $monthNumber = date('n', $timestamp);

        // แปลงเดือนเป็นภาษาไทย
        $monthThai = toMonthThai($monthNumber);

        // แสดงผล
        ?>
        <p style="text-align:right;">ลำดับ &nbsp;&nbsp; <?= $data['numberWork'] ?></p>
        <div style="text-align:center;font-weight: bold; font-size:20pt;line-height:24pt">แบบฟอร์มคำขอส่งซ่อมบำรุงอุปกรณ์คอมพิวเตอร์
        </div>
        <p style="text-align:center;line-height:10pt">ศูนย์คอมพิวเตอร์ฝ่ายแผนงานและสารสนเทศ</p>
        <br>
        <p style="text-align:right;line-height:10pt">วัน / เดือน / ปี <?= date('d', $timestamp) . ' ' . $monthThai . ' ' . date('Y', $timestamp); ?></p>
        <br>
        <p style="line-height:10pt">ส่งซ่อมอุปกรณ์ คอมพิวเตอร์ : <?= $data['device_name'] ?></p>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ........................................................................................................................................................................................................................</div>
        <p style="line-height:10pt">หมายเลขพัสดุ / ครุภัณฑ์ : <?= $data['numberDevice1'] . $Device2 . $Device3 ?></p>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ..............................................................................................................................................................................................................................</div>
        <p style="line-height:10pt">รายละเอียด / อาการ : <?= $data['report'] ?></p>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ........................................................................................................................................................................................................................................</div>
        <br>
        <div style="margin-top: -22px;">.........................................................................................................................................................................................................................................................................................</div>
        <br>
        <div class="d-flex justify-content-between">
            <div>
                <p style="text-align:left; line-height:10pt">ผู้ส่งเรื่อง ______________________________________</p>

                <p style="text-align:left; line-height:10pt">&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; (
                    ____________________________________ )
                </p>
                <p style="text-align:left; line-height:10pt">แผนก &nbsp;&nbsp; <?= $data['depart_name'] ?></p>
            </div>

            <div>
                <p style="text-align:right; line-height:10pt">ผู้รับเรื่อง <?= $data['fname'] . ' ' . $data['lname'] ?></p>

                <p style="text-align:right; line-height:10pt">&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; (
                    <?= $data['fname'] . ' ' . $data['lname'] ?> )
                </p>
                <p style="text-align:right; line-height:10pt">แผนก &nbsp;&nbsp; ศูนย์คอมพิวเตอร์</p>
            </div>
        </div>
        <br>
        <div style="text-align:left;font-weight: bold; font-size:20pt;">รายการเบิกอะไหล่ / อัพเกรดอุปกรณ์
            คอมพิวเตอร์
        </div>
        <br>
        <p style="text-align:left;">รายละเอียด <?= $data['reason'] ?></p>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp; ............................................................................................................................................................................................................................................................</div>
        <br>
        <div style="margin-top: -22px;">.........................................................................................................................................................................................................................................................................................</div>
        <br>
        <br>
        <div style="height: 16pt;" class="d-flex justify-content-between">
            <?php
            $columns = [];

            for ($i = 1; $i <= 15; $i++) {
                $columns[] = "`list$i`, `quality$i`, `amount$i`, `price$i` , `unit$i`";
            }
            $columnString = implode(", ", $columns);
            $sql = "SELECT `list1`, `list2`, `list3`, `list4`, `list5`, `list6`, `list7`, `list8`, `list9`, `list10`, `list11`, `list12`, `list13`, `list14` FROM `orderdata` WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $filteredResult = array_filter($result, function ($value) {
                return $value !== null && $value !== '';
            });

            $columnCount = count($filteredResult);

            $sql = "SELECT $columnString FROM `orderdata` WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $sum = 0;
            for ($i = 1; $i <= 15; $i++) {

                $list = $result["list$i"];
                $quality = $result["quality$i"];
                $amount = $result["amount$i"];
                $price = $result["price$i"];

                $amount = intval($amount);
                $price = intval($price);


                // คำนวณ $sum
                $currentSum = $amount * $price;

                $sum += intval($currentSum);

                // ตรวจสอบว่า $currentSum เป็น 0 หรือไม่
                if ($currentSum == 0) {
                    $currentSum = ""; // กำหนดให้ $currentSum เป็นค่าว่าง
                }
                if ($result["list$i"] == "" || $result["quality$i"] == "" || $result["amount$i"] == "" || $result["price$i"] == "") {
                    $list = "";
                    $quality = "";
                    $amount = "";
                    $price = "";
                }
            }
            ?>
            <div>
                <p style="text-align:left;">จำนวนทั้งหมด &nbsp;&nbsp; <?= $columnCount ?> &nbsp;&nbsp;รายการ</p>
            </div>


            <div>
                <p style="text-align:right;">ราคาประเมิน &nbsp;&nbsp; <?= number_format($sum) ?> &nbsp;&nbsp; บาท</p>
            </div>
        </div>
        <div class="table-responsive">
            <table style="width:100%;" cellspacing="0">
                <thead>
                    <tr>
                        <th style="text-align:center;width: 50px;">ลำดับ</th>
                        <th style="text-align:center;width: 260px">รายการ</th>
                        <th style="text-align:center;width: 260px">คุณสมบัติ</th>
                        <th style="text-align:center;width: 60px">จำนวน</th>
                        <th style="text-align:center;">ราคา</th>
                        <th style="text-align:center;">รวม</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php

                    $sum = 0;

                    for ($i = 1; $i <= 15; $i++) {

                        $list = $result["list$i"];
                        $quality = $result["quality$i"];
                        $amount = $result["amount$i"];
                        $price = $result["price$i"];

                        // คำนวณ $sum
                        $amount = intval($amount);
                        $price = intval($price);
                        $currentSum = $amount * $price;

                        $sum += intval($currentSum);

                        // ตรวจสอบว่า $currentSum เป็น 0 หรือไม่
                        if ($currentSum == "-" || $currentSum == 0) {
                            $currentSum = ""; // กำหนดให้ $currentSum เป็นค่าว่าง
                        }
                        if ($result["list$i"] == "" || $result["quality$i"] == "" || $result["amount$i"] == "" || $result["price$i"] == "") {
                            $list = "";
                            $quality = "";
                            $amount = "";
                            $price = "";
                        }
                        $deviceModelName = "";
                        if (!empty($list)) {
                            $sqlDeviceModel = "SELECT models_name FROM device_models WHERE models_id = :models_id";
                            $stmtDeviceModel = $conn->prepare($sqlDeviceModel);
                            $stmtDeviceModel->bindParam(":models_id", $list);
                            $stmtDeviceModel->execute();
                            $deviceModelResult = $stmtDeviceModel->fetch(PDO::FETCH_ASSOC);
                            $deviceModelName = $deviceModelResult['models_name'];
                        }
                    ?>
                        <tr class="empty-row">
                            <th style="font-weight: normal;" class="arabicNumber" scope="row"><?= $i; ?></th>
                            <td style="font-weight: normal; text-align:left;"><?= $deviceModelName ?></td>
                            <td style="font-weight: normal; text-align:left;"><?= nl2br($quality) ?></td>
                            <td style="font-weight: normal;" class="arabicNumber"><?= $amount ?></td>
                            <td style="font-weight: normal;" class="arabicNumber">
                                <?php if ($price == "") {
                                    echo $price;
                                } else {
                                    echo number_format($price);
                                } ?>
                            </td>
                            <td style="font-weight: normal;" class="arabicNumber" id="number<?= $i ?>">

                                <?php if ($currentSum == "") {
                                    echo $currentSum;
                                } else {
                                    echo number_format($currentSum);
                                } ?>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>




                </tbody>
            </table>


        </div>

        <!-- <div style="margin-top: 24pt;" class="d-flex justify-content-between">
            <div>
                <p style="text-align:left;line-height:8pt">ผู้จ่าย __________________________________</p>
                <p style="text-align:left;line-height:8pt">หน่วยงาน พัสดุ
                </p>
                <p style="text-align:left;line-height:8pt">วันที่จ่าย ________________________________</p>
            </div>
            <div>
                <p style="text-align:left;line-height:8pt">ผู้รับ __________________________________</p>
                <p style="text-align:left;line-height:8pt">หน่วยงาน ______________________________
                </p>
                <p style="text-align:left;line-height:8pt">วันที่จ่าย _______________________________</p>
            </div>
        </div> -->
        <!-- <footer>
            <p style="float:right;font-size:10pt;">
                พัฒนาโดย นายอานุภาพ ศรเทียน, ปรินทร ปัญโยน้อย นักเรียนวิทยาลัยเทคนิคแม่สอด
            </p>
        </footer> -->

    </div>





</body>

</html>