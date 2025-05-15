<?php
require_once 'config/db.php';

$postData = json_decode(file_get_contents('php://input'), true);
$filter = $postData['filter'] ?? 'day';

$filterCondition = '';
switch ($filter) {
    case 'day':
        $filterCondition = "DATE(date_report) = CURDATE()";
        break;
    case 'week':
        $filterCondition = "WEEK(date_report) = WEEK(CURDATE())";
        break;
    case 'month':
        $filterCondition = "MONTH(date_report) = MONTH(CURDATE())";
        break;
    case 'year':
    default:
        $filterCondition = "YEAR(date_report) = YEAR(CURDATE())";
        break;
}

$sql = "
SELECT 
    sla,
    FLOOR(
        CASE
            WHEN sla LIKE 'คอมพิวเตอร์ ใช้งานไม่ได้%' THEN 30 * 60
            WHEN sla LIKE 'เครื่องพิมพ์ ใช้งานไม่ได้ - 30 นาที%' THEN 30 * 60
            WHEN sla LIKE 'PMK ใช้งานไม่ได้%' THEN 15 * 60
            WHEN sla LIKE 'Internet ใช้งานไม่ได้%' THEN 20 * 60
            ELSE 0
        END / 60
    ) AS in_time_minutes,  -- Convert to minutes
    FLOOR(
        AVG(ABS(TIME_TO_SEC(TIMEDIFF(close_date, take)))) / 60
    ) AS avg_time_minutes  -- Convert to minutes
FROM 
    data_report
WHERE 
    $filterCondition
    AND sla IS NOT NULL
    AND sla != ''
    AND sla != 'ไม่ใช่'
GROUP BY 
    sla
ORDER BY 
    sla ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for JSON response
$labels = [];
$in_time_values = [];
$avg_time_values = [];

foreach ($result as $row) {
    $labels[] = $row['sla'];
    $in_time_values[] = $row['in_time_minutes'];
    $avg_time_values[] = $row['avg_time_minutes'];
}

// Send JSON response
echo json_encode([
    'labels' => $labels,
    'in_time_values' => $in_time_values,
    'avg_time_values' => $avg_time_values,
]);
?>
