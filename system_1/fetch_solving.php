<?php
require_once '../config/db.php';

// Execute the query
$sql = "
    SELECT 
        issue_resolved, 
        ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM rating), 2) AS percentage
    FROM 
        rating
    GROUP BY 
        issue_resolved";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for the chart
$labels = [];
$data = [];
foreach ($result as $row) {
    $labels[] = $row['issue_resolved'];
    $data[] = $row['percentage'];
}

// Return JSON response
echo json_encode([
    'labels' => $labels,
    'data' => $data,
]);
