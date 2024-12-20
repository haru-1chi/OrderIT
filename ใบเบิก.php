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

    <title>แบบฟอร์มใบเบิก</title>

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
            font-size: 18pt;
        }

        .breakhere {
            page-break-after: always;
        }


        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            font-size: 18pt
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
        $sql = "
 SELECT 
 od.*, 
 wd.withdraw_name, 
 lw.work_name, 
 dv.device_name, 
 dp.depart_name, 
 of.offer_name,
 nd.numberDevice, 
 nd.id AS numberDevice_id, 
 oi.id AS item_id, 
 dm.models_name AS list, 
 oi.quality, 
 oi.amount, 
 oi.price, 
 oi.unit
 FROM 
 orderdata_new AS od
 INNER JOIN 
 withdraw AS wd ON od.refWithdraw = wd.withdraw_id
 INNER JOIN 
 offer AS of ON od.refOffer = of.offer_id
 INNER JOIN 
 depart AS dp ON od.refDepart = dp.depart_id
 INNER JOIN 
 listwork AS lw ON od.refWork = lw.work_id
 INNER JOIN 
 device AS dv ON od.refDevice = dv.device_id
 LEFT JOIN 
 order_numberdevice AS nd ON od.id = nd.order_item
 LEFT JOIN 
 order_items AS oi ON od.id = oi.order_id
   LEFT JOIN 
     device_models AS dm ON oi.list = dm.models_id
 WHERE 
 (od.numberWork = :numberWork) AND
 (nd.is_deleted = 0 OR nd.is_deleted IS NULL)
 AND (oi.is_deleted = 0 OR oi.is_deleted IS NULL)
 ORDER BY nd.id, oi.id
 ";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":numberWork", $id);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($results) {
            $order = $results[0];
            $devices = [];
            $items = [];

            $columnCount = 0;
            $sum = 0;

            foreach ($results as $row) {
                if (!empty($row['numberDevice_id']) && !isset($devices[$row['numberDevice_id']])) {
                    $devices[$row['numberDevice_id']] =
                        [
                            'numberDevice_id' => $row['numberDevice_id'],
                            'numberDevice' => $row['numberDevice']
                        ];
                }

                if (!empty($row['item_id']) && !isset($items[$row['item_id']])) {
                    $items[$row['item_id']] = [
                        'item_id' => $row['item_id'],
                        'list' => $row['list'],
                        'quality' => $row['quality'],
                        'amount' => $row['amount'],
                        'price' => $row['price'],
                        'total' => $row['price'] * $row['amount'],
                        'unit' => $row['unit']
                    ];

                    $columnCount++;
                    $sum += $row['price'] * $row['amount'];
                }
            }

            $devices = array_values($devices);
            $items = array_values($items);

            $numberDeviceList = array_column($devices, 'numberDevice');
            $numberDeviceString = implode(', ', $numberDeviceList);

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
            $dateWithdrawFromDB = $order['dateWithdraw']; // เปลี่ยนตามฐานข้อมูลของคุณ

            // แปลงวันที่ในรูปแบบ Y-m-d เป็น timestamp
            $timestamp = strtotime($dateWithdrawFromDB);

            // ดึงเดือน
            $monthNumber = date('n', $timestamp);

            // แปลงเดือนเป็นภาษาไทย
            $monthThai = toMonthThai($monthNumber);
        } else {
            echo "No records found.";
            $order = [];
            $devices = [];
            $items = [];
        }


        // $sql = "SELECT od.*, dp.depart_name ,lw.work_name,dv.device_name,ad.fname,ad.lname,dw.withdraw_name
        // FROM orderdata AS od
        // INNER JOIN depart AS dp ON od.refDepart = dp.depart_id
        // INNER JOIN listwork AS lw ON od.refWork = lw.work_id
        // INNER JOIN device AS dv ON od.refDevice = dv.device_id
        // INNER JOIN withdraw AS dw ON od.refWithdraw = dw.withdraw_id
        // INNER JOIN admin AS ad ON od.refUsername = ad.username
        //  WHERE od.id = :id";
        // $stmt = $conn->prepare($sql);
        // $stmt->bindParam(":id", $id);
        // $stmt->execute();
        // $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // $Device2 = "";
        // $Device3 = "";

        // if ($data['numberDevice2'] == "") {
        //     $Device2 = "";
        // } else {
        //     $Device2 = ', ' . $data["numberDevice2"];
        // }
        // if ($data['numberDevice3'] == "") {
        //     $Device3 = "";
        // } else {
        //     $Device3 = ', ' . $data["numberDevice3"];
        // }

        // function toMonthThai($m)
        // {
        //     $monthNamesThai = array(
        //         "",
        //         "มกราคม",
        //         "กุมภาพันธ์",
        //         "มีนาคม",
        //         "เมษายน",
        //         "พฤษภาคม",
        //         "มิถุนายน",
        //         "กรกฎาคม",
        //         "สิงหาคม",
        //         "กันยายน",
        //         "ตุลาคม",
        //         "พฤศจิกายน",
        //         "ธันวาคม"
        //     );
        //     return $monthNamesThai[$m];
        // }
        // $dateWithdrawFromDB = $data['dateWithdraw']; // เปลี่ยนตามฐานข้อมูลของคุณ

        // // แปลงวันที่ในรูปแบบ Y-m-d เป็น timestamp
        // $timestamp = strtotime($dateWithdrawFromDB);

        // // ดึงเดือน
        // $monthNumber = date('n', $timestamp);

        // // แปลงเดือนเป็นภาษาไทย
        // $monthThai = toMonthThai($monthNumber);

        // แสดงผล
        ?>
        <div class="d-flex justify-content-between">
            <img width="70pt" height="80pt" style="text-align:left; margin-top: -10pt" src="image/ตราครุฑ 3cm.png" alt="">
            <div style="font-weight: bold; font-size:28pt;line-height:54pt">บันทึกข้อความ
            </div>
            <p style="text-align:right;"><b>(ใบขอเบิกวัสดุ/<?= $order['withdraw_name'] ?>)</b></p>
        </div>
        <p style="line-height:10pt"><b style="font-size:22pt">ส่วนราชการ</b> โรงพยาบาลแม่สอด กลุ่มงาน <?= $order['depart_name'] ?> งาน ศูนย์คอมพิวเตอร์ รหัสหน่วยงาน 67 โทร 1554</p>
        <div class="d-flex justify-content-between">
            <p style="line-height:10pt"><b style="font-size:20pt">ที่</b>&nbsp;&nbsp;&nbsp;ตก 0033. /พิเศษ</p>
            <p style="line-height:10pt"><b style="font-size:20pt">วันที่</b>&nbsp;&nbsp;&nbsp;<?= date('d', $timestamp) . ' ' . $monthThai . ' ' . date('Y', $timestamp); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
        </div>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ...........................................................................................................................&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;..................................................................................................................................</div>
        <p style="line-height:10pt"><b style="font-size:20pt">เรื่อง</b> ขอเบิกวัสดุ / <?= $order['withdraw_name'] ?> หมวด</p>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp .............................................................................................................................................................................................................................................................................</div>
        <div style="height: 10pt">

        </div>
        <p style="line-height:10pt">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; กลุ่มงาน <?= $order['depart_name'] ?> มีความประสงค์ขออนุมัติเบิก</p>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ........................................................................................................................................................................................................................................................</div>
        <?php
        // $columns = [];

        // for ($i = 1; $i <= 15; $i++) {
        //     $columns[] = "`list$i`, `quality$i`, `amount$i`, `price$i` , `unit$i`";
        // }
        // $columnString = implode(", ", $columns);
        // $sql = "SELECT `list1`, `list2`, `list3`, `list4`, `list5`, `list6`, `list7`, `list8`, `list9`, `list10`, `list11`, `list12`, `list13`, `list14` FROM `orderdata` WHERE id = :id";
        // $stmt = $conn->prepare($sql);
        // $stmt->bindParam(":id", $id);
        // $stmt->execute();
        // $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // $filteredResult = array_filter($result, function ($value) {
        //     return $value !== null && $value !== '';
        // });

        // $columnCount = count($filteredResult);

        // $sql = "SELECT $columnString FROM `orderdata` WHERE id = :id";
        // $stmt = $conn->prepare($sql);
        // $stmt->bindParam(":id", $id);
        // $stmt->execute();
        // $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // $sum = 0;
        // for ($i = 1; $i <= 15; $i++) {

        //     $list = $result["list$i"];
        //     $quality = $result["quality$i"];
        //     $amount = $result["amount$i"];
        //     $price = $result["price$i"];
        //     $unit = $result["unit$i"];

        //     $amount = intval($amount);
        //     $price = intval($price);


        //     // คำนวณ $sum
        //     $currentSum = $amount * $price;

        //     $sum += intval($currentSum);

        //     // ตรวจสอบว่า $currentSum เป็น 0 หรือไม่
        //     if ($currentSum == 0) {
        //         $currentSum = ""; // กำหนดให้ $currentSum เป็นค่าว่าง
        //     }
        //     if ($result["list$i"] == "" || $result["quality$i"] == "" || $result["amount$i"] == "" || $result["price$i"] == "" || $result["unit$i"] == "") {
        //         $list = "";
        //         $quality = "";
        //         $amount = "";
        //         $price = "";
        //         $unit = "";
        //     }
        // }

        function numberToThaiWords($number)
        {
            // อารบิก
            $digits = array(
                '',
                'หนึ่ง',
                'สอง',
                'สาม',
                'สี่',
                'ห้า',
                'หก',
                'เจ็ด',
                'แปด',
                'เก้า'
            );

            // ชื่อหลัก
            $units = array(
                '',
                'สิบ',
                'ร้อย',
                'พัน',
                'หมื่น',
                'แสน',
                'ล้าน'
            );

            // แยกระหว่างจำนวนเต็มและทศนิยม
            list($integer) = explode('.', $number);

            // แปลงจำนวนเต็ม
            $integerThai = '';
            $numLength = strlen($integer);
            for ($i = 0; $i < $numLength; $i++) {
                $digit = (int)$integer[$i];
                if ($digit !== 0) {
                    $integerThai .= $digits[$digit] . $units[$numLength - $i - 1];
                }
            }

            // แปลงทศนิยม
            $decimalThai = '';
            if (!empty($decimal)) {
                $decimalLength = strlen($decimal);
                for ($i = 0; $i < $decimalLength; $i++) {
                    $digit = (int)$decimal[$i];
                    if ($digit !== 0) {
                        $decimalThai .= $digits[$digit] . $units[- ($i + 1)];
                    }
                }
            }

            // รวมทั้งหมด
            $result = $integerThai . 'บาท';
            if (!empty($decimalThai)) {
                $result .= $decimalThai . 'สตางค์';
            }

            return $result;
        }

        // ตัวอย่างการใช้งาน
        $number = $sum;
        $thaiWords = numberToThaiWords($number);
        ?>

        <div class="table-responsive">
            <table style="width:100%;" cellspacing="0">
                <thead>
                    <tr>
                        <th style="text-align:center;width: 50px;">ลำดับ</th>
                        <th style="text-align:center;width: 90px;">รหัส EID</th>
                        <th style="text-align:center;width: 260px">รายการ</th>
                        <th style="text-align:center;width: 60px">หน่วยนับ</th>
                        <th style="text-align:center;width: 60px">จำนวน</th>
                        <th style="text-align:center; width: 100px;">ราคาต่อหน่วย</th>
                        <th style="text-align:center; width: 80px;">รวม</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <?php

                    // $sum = 0;

                    // for ($i = 1; $i <= 15; $i++) {

                    //     $list = $result["list$i"];
                    //     $quality = $result["quality$i"];
                    //     $amount = $result["amount$i"];
                    //     $price = $result["price$i"];
                    //     $unit = $result["unit$i"];

                    //     // คำนวณ $sum
                    //     $amount = intval($amount);
                    //     $price = intval($price);
                    //     $currentSum = $amount * $price;

                    //     $sum += intval($currentSum);

                    //     // ตรวจสอบว่า $currentSum เป็น 0 หรือไม่
                    //     if ($currentSum == 0) {
                    //         $currentSum = ""; // กำหนดให้ $currentSum เป็นค่าว่าง
                    //     }
                    //     if ($result["list$i"] == "" || $result["quality$i"] == "" || $result["amount$i"] == "" || $result["price$i"] == "") {
                    //         $list = "";
                    //         $quality = "";
                    //         $amount = "";
                    //         $price = "";
                    //         $unit = "";
                    //     }
                    //     $deviceModelName = "";
                    //     if (!empty($list)) {
                    //         $sqlDeviceModel = "SELECT models_name FROM device_models WHERE models_id = :models_id";
                    //         $stmtDeviceModel = $conn->prepare($sqlDeviceModel);
                    //         $stmtDeviceModel->bindParam(":models_id", $list);
                    //         $stmtDeviceModel->execute();
                    //         $deviceModelResult = $stmtDeviceModel->fetch(PDO::FETCH_ASSOC);
                    //         $deviceModelName = $deviceModelResult['models_name'];
                    //     }
                    $rowCount = count($items);
                    $emptyRows = 15 - $rowCount;
                    $index = 0;
                    foreach ($items as $item): ?>
                        <tr class="empty-row">
                            <th style="font-weight: normal;" class="arabicNumber" scope="row"><?= $index + 1 ?></th>
                            <th style="font-weight: normal;" class="arabicNumber" scope="row"></th>
                            <td style="font-weight: normal; text-align:left;"><?= $item['list'] ?></td>
                            <td style="font-weight: normal;"><?= $item['unit'] ?></td>
                            <td style="font-weight: normal;" class="arabicNumber"><?= $item['amount'] ?></td>
                            <td style="font-weight: normal;" class="arabicNumber"><?= $item['price'] ?></td>
                            <td style="font-weight: normal;" class="arabicNumber" id="number<?= $i ?>"><?= $item['total'] ?></td>
                        </tr>
                    <?php
                        $index++;
                    endforeach;
                    for ($i = 0; $i < $emptyRows; $i++): ?>
                        <tr class="empty-row">
                            <th style="font-weight: normal;" class="arabicNumber" scope="row"><?= $index + 1 ?></th>
                            <td style="font-weight: normal; text-align:left;"></td>
                            <td style="font-weight: normal; text-align:left;"></td>
                            <td style="font-weight: normal;" class="arabicNumber"></td>
                            <td style="font-weight: normal;" class="arabicNumber"></td>
                            <td style="font-weight: normal;" class="arabicNumber"></td>
                            <td style="font-weight: normal;" class="arabicNumber"></td>
                        </tr>
                    <?php
                        $index++; // Increment index for each empty row
                    endfor;
                    ?>
                </tbody>
            </table>


        </div>
        <p style="line-height:10pt;margin-top: 10pt">ราคาทั้งสิ้น...............<?= number_format($sum) ?>...............บาท (...............<?= $thaiWords ?>...............)</p>
        <p style="line-height:10pt;">เหตุผลและความจำเป็น <?= $order['reason'] ?></p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ในการเบิกครั้งนี้</p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;( ) ได้รับอนุมัติจัดซื้อด้วยเงินนอกงบประมาณ-เงินบำรุง ปีงบประมาณ ................ แล้ว</p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;จำนวน ......................... บาท</p>
        <p style="line-height:10pt;">
            รายละเอียดปรากฏตามแผนเงินนอกงบประมาณ-เงินบำรุงที่แนบ</p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;( ) ทดแทนของเดิมที่ชำรุด ต้องแนบใบชำรุดของช่าง / ศูนย์เครื่องมือแพทย์</p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;( ) นอกงบ ไม่ได้ตั้งแผนนอกงบประมาณ-เงินบำรุง เข้าคณะกรรมการ CEO</p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;เมื่อวันที่ .............................................</p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;มอบให้กลุ่มงานพัสดุดำเนินการจัดซื้อตามพระราชบัญญัติการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ</p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;พ.ศ.2560 ต่อไป</p>
        <br>
        <div class="d-flex justify-content-end">
            <div>
                <p style="text-align:right;line-height:8pt">(ลงชื่อ)____________________________________เจ้าหน้าที่</p>
                <p style="text-align:left;line-height:8pt">( __________________________________ )</p>
                <p style="text-align:left;line-height:8pt">ตำแหน่ง_______________________________
                </p>
            </div>
        </div>
        <div style="margin-top: 20pt;" class="d-flex justify-content-end">

            <div>
                <p style="text-align:right;line-height:8pt">(ลงชื่อ)_______________________________หัวหน้ากลุ่มงาน</p>
                <p style="text-align:left;line-height:8pt">( __________________________________ )</p>
                <p style="text-align:left;line-height:8pt">ตำแหน่ง_______________________________
                </p>
            </div>
        </div>
        <!-- <footer>
            <p style="float:right;font-size:10pt;">
                พัฒนาโดย นายอานุภาพ ศรเทียน, ปรินทร ปัญโยน้อย นักเรียนวิทยาลัยเทคนิคแม่สอด
            </p>
        </footer> -->

    </div>





</body>

</html>