<?php
require_once 'config/db.php';

$sql = "
SELECT 
    id, 
    username AS name, 
    TIME(take) AS take_time, 
    TIME(close_date) AS close_time, 
    problem 
FROM 
    data_report 
WHERE 
    status = 4 
    AND DATE(DATE_SUB(date_report, INTERVAL 543 YEAR)) = CURDATE()";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = array_map(function ($row) {
    return [
        'id' => $row['id'],
        'name' => $row['name'],
        'start' => $row['take_time'], // Start time
        'end' => $row['close_time'], // End time
        'problem' => $row['problem'] // Problem type
    ];
}, $result);

// Send JSON response
echo json_encode($data);
?>
