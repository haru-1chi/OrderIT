<?php
if (isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $tempDir = 'uploads/temp/'; // temporary folder
    if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);

    $fileName = time() . '_' . basename($file['name']); // unique name
    $filePath = $tempDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode(['url' => $filePath]);
    } else {
        echo json_encode(['error' => 'Upload failed']);
    }
}
