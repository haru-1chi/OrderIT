<?php
require_once 'config/db.php';

$postData = json_decode(file_get_contents('php://input'), true);
$filter = $postData['filter'] ?? 'day';

$selectClause = '';
$groupByClause = '';

switch ($filter) {
    case 'day':
        $selectClause = "DATE_FORMAT(DATE_SUB(date_report, INTERVAL 543 YEAR), '%d') AS period";
        $groupByClause = "DATE_FORMAT(DATE_SUB(date_report, INTERVAL 543 YEAR), '%d'),
        DATE_SUB(date_report, INTERVAL 543 YEAR)";
        $range = "DATE_SUB(date_report, INTERVAL 543 YEAR) >= CURDATE() - INTERVAL 30 DAY";
        $period = "DATE_SUB(date_report, INTERVAL 543 YEAR)";
        break;
    case 'week':
        $selectClause = "DATE_FORMAT(
        DATE_SUB(
            DATE_SUB(date_report, INTERVAL 543 YEAR),
            INTERVAL WEEKDAY(DATE_SUB(date_report, INTERVAL 543 YEAR)) DAY
        ),
        '%Y-%m-%d'
    ) AS period";
        $groupByClause = "DATE_FORMAT(
        DATE_SUB(
            DATE_SUB(date_report, INTERVAL 543 YEAR),
            INTERVAL WEEKDAY(DATE_SUB(date_report, INTERVAL 543 YEAR)) DAY
        ),
        '%Y-%m-%d'
    )";
        $range = "YEAR(DATE_SUB(date_report, INTERVAL 543 YEAR)) = YEAR(CURDATE())";
        $period = "period ";
        break;
    case 'month':
        $selectClause = "DATE_FORMAT(DATE_SUB(date_report, INTERVAL 543 YEAR), '%m-%Y') AS period";
        $groupByClause = "DATE_FORMAT(DATE_SUB(date_report, INTERVAL 543 YEAR), '%m-%Y')";
        $range = "YEAR(DATE_SUB(date_report, INTERVAL 543 YEAR)) = YEAR(CURDATE())";
        $period = "period ";
        break;
    case 'year':
        $selectClause = "YEAR(DATE_SUB(date_report, INTERVAL 543 YEAR)) AS period";
        $groupByClause = "YEAR(DATE_SUB(date_report, INTERVAL 543 YEAR))";
        $range = "YEAR(DATE_SUB(date_report, INTERVAL 543 YEAR)) = YEAR(CURDATE())";
        $period = "period ";
        break;
    default:
        $selectClause = "DATE_FORMAT(DATE_SUB(date_report, INTERVAL 543 YEAR), '%d') AS period";
        $groupByClause = "DATE_FORMAT(DATE_SUB(date_report, INTERVAL 543 YEAR), '%d'),
    DATE_SUB(date_report, INTERVAL 543 YEAR)";
        $range = "DATE_SUB(date_report, INTERVAL 543 YEAR) >= CURDATE() - INTERVAL 30 DAY";
        $period = "DATE_SUB(date_report, INTERVAL 543 YEAR)";
        break;
}

$sql = "
    SELECT 
        $selectClause,
        COUNT(*) AS task_count
    FROM 
        data_report
    WHERE 
        status = 4 
        AND $range
    GROUP BY 
        $groupByClause
    ORDER BY 
        $period ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for JSON response
$labels = [];
$task_counts = [];

foreach ($result as $row) {
    $labels[] = $row['period'];
    $task_counts[] = $row['task_count'];
}

// Send JSON response
echo json_encode([
    'labels' => $labels,
    'task_counts' => $task_counts,
]);
