<?php
// autocomplete.php

// Include your database connection file here
require_once 'config/db.php';

$term = $_GET['term']; // คำที่ผู้ใช้ป้อน

$sql = "SELECT depart_id, depart_name FROM depart WHERE depart_name LIKE :term";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = array();
foreach ($result as $row) {
    $data[] = array(
        'label' => $row['depart_name'],
        'value' => $row['depart_id']
    );
}

echo json_encode($data);
