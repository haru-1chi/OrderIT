<?php
require_once 'config/db.php';

// Get query parameters
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'problem';

// Validate filter to prevent SQL injection
$validFilters = ['device', 'problem', 'report', 'sla'];
$filterColumn = in_array($filter, $validFilters) ? $filter : 'problem';

$sql = "
SELECT 
    id, 
    username AS name, 
    TIME(take) AS take_time, 
    TIME(close_date) AS close_time, 
    $filterColumn AS problem
FROM 
    data_report 
WHERE 
    status = 4 
    AND DATE(DATE_SUB(date_report, INTERVAL 543 YEAR)) = :selected_date";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':selected_date', $date, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = array_map(function ($row) {
    return [
        'id' => $row['id'],
        'name' => $row['name'],
        'start' => $row['take_time'],
        'end' => $row['close_time'],
        'problem' => $row['problem']
    ];
}, $result);

echo json_encode($data);
