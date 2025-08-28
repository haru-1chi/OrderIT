<?php
if (isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $tempDir = __DIR__ . '/../uploads/temp/'; // physical path
    if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);

    $fileName = time() . '_' . basename($file['name']); // unique name
    $filePath = $tempDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Convert to web-accessible path
        $webPath = 'uploads/temp/' . $fileName;
        echo json_encode(['url' => $webPath]);
    } else {
        echo json_encode(['error' => 'Upload failed']);
    }
}
