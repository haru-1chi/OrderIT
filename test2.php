<!DOCTYPE html>
<html lang="en">
<?php
require_once 'config/db.php';
$idCards = [];
$sql = "SELECT username, id_card FROM admin";
$stmt = $conn->prepare($sql);
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($admins as $admin) {
    $idCards[$admin['id_card']] = $admin['username']; // map id_card to username
}

require_once 'config/leave_db.php';
$placeholders = implode(',', array_fill(0, count($idCards), '?'));

$sql = "SELECT EMPLOYEE_ID, START_DATE, END_DATE, TYPE_LEAVE, DETAIL 
        FROM data_leave 
        WHERE EMPLOYEE_ID IN ($placeholders)";
$stmt = $conn->prepare($sql);
$stmt->execute(array_keys($idCards));
$leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1'>
<tr><th>ชื่อผู้ใช้</th><th>วันเริ่มลา</th><th>วันสิ้นสุด</th><th>ประเภท</th><th>รายละเอียด</th></tr>";

foreach ($leaves as $row) {
    $username = $idCards[$row['EMPLOYEE_ID']] ?? 'ไม่ทราบชื่อ';

    echo "<tr>";
    echo "<td>$username</td>";
    echo "<td>{$row['START_DATE']}</td>";
    echo "<td>{$row['END_DATE']}</td>";
    echo "<td>{$row['TYPE_LEAVE']}</td>";
    echo "<td>{$row['DETAIL']}</td>";
    echo "</tr>";
}
echo "</table>";

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gantt Chart Summary</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
</head>

<body>
</body>

</html>