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

    <title>ใบเบิกครุภัณฑ์ในแผน</title>

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

        // แสดงผล
        ?>
        <div class="d-flex justify-content-between">
            <img width="70pt" height="80pt" style="text-align:left; margin-top: -10pt" src="image/ตราครุฑ 3cm.png" alt="">
            <div style="font-weight: bold; font-size:28pt;line-height:54pt">บันทึกข้อความ
            </div>
            <p style="text-align:right;"><b>(ใบขอเบิกวัสดุ ในแผน)</b></p>
        </div>

        <p style="line-height:10pt"><b style="font-size:22pt">ส่วนราชการ</b> โรงพยาบาลแม่สอด กลุ่มงาน เทคโนโลยีสารสนเทศ โทร 2056</p>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;......................................................................................................................................................................................................................................................</div>

        <div class="d-flex justify-content-between">
            <p style="line-height:10pt"><b style="font-size:20pt">ที่</b>&nbsp;&nbsp;&nbsp;ตก 0033. /</p>
            <p style="line-height:10pt"><b style="font-size:20pt">วันที่</b>&nbsp;&nbsp;&nbsp;<?= date('d', $timestamp) . ' ' . $monthThai . ' ' . (date('Y', $timestamp) + 543); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
        </div>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ...........................................................................................................................&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;..................................................................................................................................</div>
        <p style="line-height:10pt"><b style="font-size:20pt">เรื่อง</b> ขอเบิกครุภัณฑ์</p>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp .............................................................................................................................................................................................................................................................................</div>
        <div style="height: 10pt">

        </div>

        <p style="line-height:10pt">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= $order['depart_name'] ?></p>
        <div style="margin-top: -20pt;">
            <p style="line-height:10pt">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ด้วยกลุ่มงาน/งาน ....................................................................................... มีความประสงค์ขอเบิก </p>
        </div>

        <p style="line-height:10pt;">ครุภัณฑ์ ดังนี้</p>
        <div style="margin-top: -24pt;">
            <?php
            $index = 0;
            foreach ($items as $item): ?>
                <div c>

                </div>
                <p style="line-height:9pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= $item['list'] ?></p>
                <p style="line-height:9pt; margin-top: -20pt; margin-left: 450pt;"> <?= $item['price'] ?></p>
                <div style="margin-top: -20pt;">
                    <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $index + 1 ?>........................................................................................................... ราคา.................................. บาท</p>
                </div>
            <?php
                $index++;
            endforeach; ?>
        </div>
        <p style="line-height:9pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= number_format($sum) ?></p>
        <p style="line-height:9pt; margin-top: -20pt; margin-left: 300pt;"> <?= $thaiWords ?></p>

        <div style="margin-top: -20pt;">
            <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;รวมเป็นเงิน ................................... บาท (...............................................................................................)</p>
        </div>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ในการขอเบิกครุภัณฑ์ครั้งนี้ ได้แนบแผนที่ได้รับอนุมัติงบมาด้วยแล้ว และใช้เงิน ดังนี้</p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;( &nbsp; ) เงินนอกงบประมาณ-เงินบำรุง ปีงบประมาณ 2568</p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;( &nbsp; ) เงินงบประมาณ</p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;( &nbsp; ) เงินงบค่าเสื่อม</p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;( &nbsp; ) เงินบริจาค</p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;( &nbsp; ) เงินอื่นๆ</p>
        <p style="line-height:10pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;( &nbsp; ) ทดแทนของเดิมชำรุด ต้องแนบใบชำรุดของช่าง/ศูนย์เครื่องมือแพทย์</p>
        <p style="line-height:10pt;">มอบให้กลุ่มงานพัสดุ ดำเนินการจัดซื้อตามพระราชบัญญัติการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ</p>
        <p style="line-height:10pt;">พ.ศ. 2560 ต่อไป</p>


        <div style="margin-top: 20pt;" class="d-flex justify-content-end">
            <div>
                <p style="text-align:right;line-height:8pt">(ลงชื่อ)................................................เจ้าหน้าที่หน่วยงาน</p>
                <p style="text-align:left;line-height:8pt; margin-left: 40px;">(...............................................)</p>
                <p style="text-align:left;line-height:8pt">ตำแหน่ง...........................................
                </p>
            </div>
        </div>
        <div style="margin-top: 20pt; margin-right: 21px;" class="d-flex justify-content-end">
            <div>
                <p style="text-align:right;line-height:8pt">(ลงชื่อ)................................................หัวหน้ากลุ่มงาน</p>
                <p style="text-align:left;line-height:8pt; margin-left: 40px;">(................................................)</p>
                <p style="text-align:left;line-height:8pt">ตำแหน่ง............................................
                </p>
            </div>
        </div>

        <div style="margin-top: 30pt; margin-right: 116px;" class="d-flex justify-content-end">
            <div>
                <p style="text-align:center;line-height:8pt; margin-bottom: 45px;">อนุมัติ</p>
                <p style="text-align:center;line-height:8pt">................................................</p>
                <p style="text-align:center;line-height:8pt">(................................................)</p>
                <p style="text-align:center;line-height:8pt">หัวหน้ากลุ่มภารกิจ
                </p>
            </div>
        </div>

    </div>





</body>

</html>