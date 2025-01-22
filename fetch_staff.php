<?php
require_once 'config/db.php';

// Get the term and type parameters
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
    default:
        $filterCondition = "YEAR(DATE_SUB(date_report, INTERVAL 543 YEAR)) = YEAR(CURDATE())";
        break;
}

// SQL for creators
$sql_creators = "
    SELECT 
        a.username AS create_by, 
        COUNT(*) AS finish_take
    FROM 
        data_report AS d
    JOIN 
        admin AS a
    ON 
        d.create_by = CONCAT(a.fname, ' ', a.lname)
    WHERE 
        status = 4 AND $filterCondition
    GROUP BY 
        a.username";

$stmt_creators = $conn->prepare($sql_creators);
$stmt_creators->execute();
$creator_data = $stmt_creators->fetchAll(PDO::FETCH_ASSOC);

// SQL for takers
$sql_takers = "
    SELECT 
        username, 
        COUNT(*) AS finish_take
    FROM 
        data_report
    WHERE 
        status = 4 AND $filterCondition
    GROUP BY 
        username";

$stmt_takers = $conn->prepare($sql_takers);
$stmt_takers->execute();
$taker_data = $stmt_takers->fetchAll(PDO::FETCH_ASSOC);

// Prepare data
$labels = [];
$creator_counts = [];
$taker_counts = [];

foreach ($creator_data as $row) {
    $labels[] = $row['create_by'];
    $creator_counts[$row['create_by']] = $row['finish_take'];
}

foreach ($taker_data as $row) {
    if (!in_array($row['username'], $labels)) {
        $labels[] = $row['username'];
    }
    $taker_counts[$row['username']] = $row['finish_take'];
}

// Align data
foreach ($labels as $label) {
    if (!isset($creator_counts[$label])) $creator_counts[$label] = 0;
    if (!isset($taker_counts[$label])) $taker_counts[$label] = 0;
}

// Send JSON response
echo json_encode([
    'labels' => $labels,
    'creator_counts' => array_values($creator_counts),
    'taker_counts' => array_values($taker_counts),
]);