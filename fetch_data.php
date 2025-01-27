<?php
require_once 'config/db.php';

// Get the selected date from the query parameter
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Fetch data for the selected date
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
    AND DATE(DATE_SUB(date_report, INTERVAL 543 YEAR)) = :selected_date";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':selected_date', $date, PDO::PARAM_STR);
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
