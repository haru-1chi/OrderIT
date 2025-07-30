<?php
// autocomplete.php

require_once '../config/db.php';

// Get the term and type parameters
$term = isset($_GET['term']) ? $_GET['term'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Define the mappings for the type parameter to table and columns
$tableMap = [
    'depart' => [
        'table' => 'depart',
        'id_column' => 'depart_id',
        'name_column' => 'depart_name'
    ],
    'device' => [
        'table' => 'device',
        'id_column' => 'device_id',
        'name_column' => 'device_name'
    ]
];

// Check if the type is valid
if (!array_key_exists($type, $tableMap)) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid type']);
    exit;
}

// Get table and column details for the specified type
$table = $tableMap[$type]['table'];
$idColumn = $tableMap[$type]['id_column'];
$nameColumn = $tableMap[$type]['name_column'];

try {
    // Prepare the SQL query
    $sql = "SELECT $idColumn, $nameColumn FROM $table WHERE $nameColumn LIKE :term";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the response data
    $data = array();
    foreach ($result as $row) {
        $data[] = [
            'label' => $row[$nameColumn],
            'value' => $row[$idColumn]
        ];
    }

    echo json_encode($data);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}