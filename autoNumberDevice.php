<?php
// autocomplete.php

// Include your database connection file here
require_once 'config/db.php';

$term = $_GET['term']; // คำที่ผู้ใช้ป้อน

$sql = "SELECT id , numberDevice1, numberDevice2 , numberDevice3 FROM orderdata WHERE numberDevice1 LIKE :term OR numberDevice2 LIKE :term OR numberDevice3 ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = array();
foreach ($result as $row) {
    if ($row['numberDevice1']) {
        $label = $row['numberDevice1'];
        $value = $row['id'];
    } elseif ($row['numberDevice2']) {
        $label = $row['numberDevice2'];
        $value = $row['id'];
    } else {
        $label = $row['numberDevice3'];
        $value = $row['id'];
    }


    $data[] = array(
        'label' => $label,
        'value' => $value
    );
}


echo json_encode($data);
