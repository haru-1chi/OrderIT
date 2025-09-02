<?php
require_once "../config/db.php";

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$stmt = $conn->prepare("SELECT category_name FROM category_note WHERE category_name LIKE :q");
$stmt->execute([':q' => "%$q%"]);
$results = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($results);
