<?php
require_once 'config/db.php';

$postData = json_decode(file_get_contents('php://input'), true);
$filter = $postData['filter'] ?? 'day';

$selectClause = '';
$groupByClause = '';

switch ($filter) {
    case 'day':
        $selectClause = "DATE_FORMAT(date_report, '%d') AS period";
        $groupByClause = "DATE_FORMAT(date_report, '%d'),
        date_report";
        $range = "date_report >= CURDATE() - INTERVAL 30 DAY";
        $period = "date_report";
        break;
    case 'week':
        $selectClause = "DATE_FORMAT(
        DATE_SUB(
            date_report,
            INTERVAL WEEKDAY(date_report) DAY
        ),
        '%Y-%m-%d'
    ) AS period";
        $groupByClause = "DATE_FORMAT(
        DATE_SUB(
            date_report,
            INTERVAL WEEKDAY(date_report) DAY
        ),
        '%Y-%m-%d'
    )";
        $range = "YEAR(date_report) = YEAR(CURDATE())";
        $period = "period ";
        break;
    case 'month':
        $selectClause = "DATE_FORMAT(date_report, '%m-%Y') AS period";
        $groupByClause = "DATE_FORMAT(date_report, '%m-%Y')";
        $range = "YEAR(date_report) = YEAR(CURDATE())";
        $period = "period ";
        break;
    case 'year':
        $selectClause = "YEAR(date_report) AS period";
        $groupByClause = "YEAR(date_report)";
        $range = "YEAR(date_report) = YEAR(CURDATE())";
        $period = "period ";
        break;
    default:
        $selectClause = "DATE_FORMAT(date_report, '%d') AS period";
        $groupByClause = "DATE_FORMAT(date_report, '%d'),
    date_report";
        $range = "date_report >= CURDATE() - INTERVAL 30 DAY";
        $period = "date_report";
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
