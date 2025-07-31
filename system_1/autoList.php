<?php
// system_1/autocomplete.php

// Include your database connection file here
require_once '../config/db.php';

if (isset($_POST['models_id'])) {
    $models_id = $_POST['models_id'];

    $sql = "SELECT quality, price, unit FROM device_models WHERE models_id = :models_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':models_id', $models_id, PDO::PARAM_INT);
    $stmt->execute();
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($device) {
        echo json_encode(['success' => true, 'quality' => $device['quality'], 'price' => $device['price'], 'unit' => $device['unit']]);
    } else {
        echo json_encode(['success' => false]);
    }
}
