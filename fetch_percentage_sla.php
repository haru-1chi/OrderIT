<?php
require_once 'config/db.php';

$postData = json_decode(file_get_contents('php://input'), true);
$filter = $postData['filter'] ?? 'day';

$filterCondition = '';
switch ($filter) {
    case 'day':
        $filterCondition = "DATE(DATE_SUB(date_report, INTERVAL 543 YEAR)) = CURDATE()";
        break;
    case 'week':
        $filterCondition = "WEEK(DATE_SUB(date_report, INTERVAL 543 YEAR)) = WEEK(CURDATE())";
        break;
    case 'month':
        $filterCondition = "MONTH(DATE_SUB(date_report, INTERVAL 543 YEAR)) = MONTH(CURDATE())";
        break;
    case 'year':
        $filterCondition = "YEAR(DATE_SUB(date_report, INTERVAL 543 YEAR)) = YEAR(CURDATE())";
    default:
        $filterCondition = "YEAR(DATE_SUB(date_report, INTERVAL 543 YEAR)) = YEAR(CURDATE())";
        break;
}

$sql = "
SELECT
    CONCAT(ROUND(SUM(
        CASE
            WHEN ABS(TIME_TO_SEC(TIMEDIFF(close_date, take))) <= 
                 CASE
                    WHEN sla LIKE 'คอมพิวเตอร์ ใช้งานไม่ได้%' THEN 30 * 60
                    WHEN sla LIKE 'เครื่องพิมพ์ ใช้งานไม่ได้ - 30 นาที%' THEN 30 * 60
                    WHEN sla LIKE 'PMK ใช้งานไม่ได้%' THEN 15 * 60
                    WHEN sla LIKE 'Internet ใช้งานไม่ได้%' THEN 20 * 60
                    ELSE 0
                END
        THEN 1
        ELSE 0
        END
    ) / COUNT(*) * 100, 2), '%') AS in_time_percentage,
    
    CONCAT(ROUND(SUM(
        CASE
            WHEN ABS(TIME_TO_SEC(TIMEDIFF(close_date, take))) > 
                 CASE
                    WHEN sla LIKE 'คอมพิวเตอร์ ใช้งานไม่ได้%' THEN 30 * 60
                    WHEN sla LIKE 'เครื่องพิมพ์ ใช้งานไม่ได้ - 30 นาที%' THEN 30 * 60
                    WHEN sla LIKE 'PMK ใช้งานไม่ได้%' THEN 15 * 60
                    WHEN sla LIKE 'Internet ใช้งานไม่ได้%' THEN 20 * 60
                    ELSE 0
                END
        THEN 1
        ELSE 0
        END
    ) / COUNT(*) * 100, 2), '%') AS over_time_percentage
FROM 
    data_report
WHERE 
    $filterCondition
    AND sla IS NOT NULL
    AND sla != ''
    AND sla != 'ไม่ใช่';
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// Extract and return percentages
echo json_encode([
    'in_time' => floatval(trim($data['in_time_percentage'], '%')),
    'over_time' => floatval(trim($data['over_time_percentage'], '%'))
]);
?>
