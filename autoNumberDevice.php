<?php
// autocomplete.php

// Include your database connection file here
require_once 'config/db.php';

$term = $_GET['term']; // Search term entered by the user

// SQL query with JOIN to fetch relevant data
$sql = "
    SELECT od.numberWork, ond.numberDevice
    FROM order_numberdevice AS ond
    INNER JOIN orderdata_new AS od ON ond.order_item = od.id
    WHERE ond.is_deleted = 0
    AND (
        ond.numberDevice LIKE :term
    )
    ORDER BY ond.id DESC
";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare JSON response
$data = [];
foreach ($result as $row) {
    $data[] = [
        'label' => $row['numberDevice'], // This is what the user sees in autocomplete
        'value' => $row['numberWork'],   // This is what will be passed to the query string
    ];
}

// Output response in JSON format
echo json_encode($data);
