<?php
require_once '../config/db.php';

// Get the term and type parameters
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

// SQL for creators
$sql_creators = "
    SELECT 
        a.username AS create_by, 
        COUNT(*) AS finish_create
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
    if (!in_array($row['create_by'], $labels)) {
        $labels[] = $row['create_by'];
    }
}

foreach ($taker_data as $row) {
    if (!in_array($row['username'], $labels)) {
        $labels[] = $row['username'];
    }
}

// Initialize counts to zero
foreach ($labels as $label) {
    $creator_counts[$label] = 0;
    $taker_counts[$label] = 0;
}

// Fill in actual values
foreach ($creator_data as $row) {
    $creator_counts[$row['create_by']] = $row['finish_create'];
}

foreach ($taker_data as $row) {
    $taker_counts[$row['username']] = $row['finish_take'];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode([
    'labels' => $labels,
    'creator_counts' => array_values($creator_counts),
    'taker_counts' => array_values($taker_counts),
], JSON_PRETTY_PRINT);
exit;
