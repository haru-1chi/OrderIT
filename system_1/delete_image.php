<?php
require_once '../config/db.php';

if (isset($_POST['filename']) && isset($_POST['report_id'])) {
    $filename = $_POST['filename'];
    $report_id = (int) $_POST['report_id'];

    // Delete from DB
    $stmt = $conn->prepare("DELETE FROM images_table WHERE report_id = :report_id AND filename = :filename");
    $stmt->bindParam(':report_id', $report_id, PDO::PARAM_INT);
    $stmt->bindParam(':filename', $filename, PDO::PARAM_STR);

    if ($stmt->execute()) {
        // Delete from uploads folder
        $filePath = __DIR__ . "/../uploads/" . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
} else {
    echo json_encode(['status' => 'invalid']);
}
