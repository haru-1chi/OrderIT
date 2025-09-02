<?php
require_once "../config/db.php";

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$stmt = $conn->prepare("SELECT name FROM category_note WHERE name LIKE :q");
$stmt->execute([':q' => "%$q%"]);
$results = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($results);
