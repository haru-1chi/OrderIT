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

        // $sql = "SELECT od.*, dp.depart_name ,lw.work_name,dv.device_name,ad.fname,ad.lname
        // FROM orderdata AS od
        // INNER JOIN depart AS dp ON od.refDepart = dp.depart_id
        // INNER JOIN listwork AS lw ON od.refWork = lw.work_id
        // INNER JOIN device AS dv ON od.refDevice = dv.device_id
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



        // แสดงผล
        ?>
        <p style="text-align:right;">ลำดับ &nbsp;&nbsp; <?= $order['numberWork'] ?></p>
        <div style="text-align:center;font-weight: bold; font-size:20pt;line-height:24pt">แบบฟอร์มคำขอส่งซ่อมบำรุงอุปกรณ์คอมพิวเตอร์
        </div>
        <p style="text-align:center;line-height:10pt">กลุ่มงานเทคโนโลยีสารสนเทศ โรงพยาบาลแม่สอด</p>
        <br>
        <p style="text-align:right;line-height:10pt">วัน / เดือน / ปี <?= date('d', $timestamp) . ' ' . $monthThai . ' ' . (date('Y', $timestamp) + 543); ?></p>
        <br>
        <p style="line-height:10pt">ส่งซ่อมอุปกรณ์ คอมพิวเตอร์ : <?= $order['device_name'] ?></p>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ........................................................................................................................................................................................................................</div>
        <p style="line-height:10pt">หมายเลขพัสดุ / ครุภัณฑ์ : <?= $numberDeviceString ?></p>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ..............................................................................................................................................................................................................................</div>
        <p style="line-height:10pt">รายละเอียด / อาการ : <?= $order['report'] ?></p>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ........................................................................................................................................................................................................................................</div>
        <br>
        <div style="margin-top: -22px;">.........................................................................................................................................................................................................................................................................................</div>
        <br>
        <div class="d-flex justify-content-between">
            <div>
                <p style="text-align:left; line-height:10pt">ผู้ส่งเรื่อง ______________________________________</p>

                <p style="text-align:left; line-height:10pt">&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; (____________________________________)
                </p>
                <p style="text-align:left; line-height:10pt">หน่วยงาน &nbsp;&nbsp; <?= $order['depart_name'] ?></p>
            </div>

            <div>
                <p style="text-align:center; line-height:10pt">ผู้รับเรื่อง &nbsp;&nbsp; <?= $order['refUsername'] ?></p>
                <p style="text-align:center; line-height:10pt">( <?= $order['refUsername'] ?> )</p>
                <p style="text-align:right; line-height:10pt">หน่วยงาน &nbsp;&nbsp; กลุ่มงานเทคโนโลยีสารสนเทศ</p>
            </div>
        </div>
        <br>
        <div style="text-align:left;font-weight: bold; font-size:20pt;">รายการเบิกอะไหล่ / อัพเกรดอุปกรณ์
            คอมพิวเตอร์
        </div>
        <br>
        <p style="text-align:left;">รายละเอียด <?= $order['reason'] ?></p>
        <div style="margin-top: -20pt;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp&nbsp;&nbsp;&nbsp; ............................................................................................................................................................................................................................................................</div>
        <br>
        <div style="margin-top: -22px;">.........................................................................................................................................................................................................................................................................................</div>
        <br>
        <br>
        <div style="height: 16pt;" class="d-flex justify-content-between">
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
                <tbody class="text-center text-start">
                    <?php
                    $rowCount = count($items); // Get the current number of items
                    $emptyRows = 15 - $rowCount; // Calculate how many empty rows to add
                    $index = 0; // Initialize index to start from 1

                    // Loop through the existing items
                    foreach ($items as $item): ?>
                        <tr class="empty-row">
                            <th style="font-weight: normal; vertical-align: top;" class="arabicNumber" scope="row"><?= $index + 1 ?></th>
                            <td style="font-weight: normal; text-align:left; vertical-align: top;"><?= $item['list'] ?></td>
                            <td style="font-weight: normal; text-align:left; vertical-align: top;"><?= nl2br($item['quality']) ?></td>
                            <td style="font-weight: normal; vertical-align: top;" class="arabicNumber"><?= $item['amount'] ?></td>
                            <td style="font-weight: normal; vertical-align: top;" class="arabicNumber"><?= $item['price'] ?></td>
                            <td style="font-weight: normal; vertical-align: top;" class="arabicNumber"><?= $item['total'] ?></td>
                        </tr>
                    <?php
                        $index++; // Increment index for each item row
                    endforeach;

                    // Loop through the remaining empty rows if $items has fewer than 15 rows
                    for ($i = 0; $i < $emptyRows; $i++): ?>
                        <tr class="empty-row">
                            <th style="font-weight: normal;" class="arabicNumber" scope="row"><?= $index + 1 ?></th>
                            <td style="font-weight: normal; text-align:left;"></td>
                            <td style="font-weight: normal; text-align:left;"></td>
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