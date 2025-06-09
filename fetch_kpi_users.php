<?php
require_once 'config/db.php';

// Execute the query
$kpiId = $_GET['kpi'] ?? null;

if (!$kpiId) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT username FROM kpi_assignment WHERE kpi_id = ?");
$stmt->execute([$kpiId]);
$assignedUsers = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'username');

// Fetch all users
$stmt = $conn->prepare("SELECT * FROM admin");
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build HTML
$html = '';
foreach ($admins as $user) {
    $checked = in_array($user['username'], $assignedUsers) ? 'checked' : '';
    $username = htmlspecialchars($user['username']);
    $html .= "
        <label class='list-group-item'>
            <input class='form-check-input me-1' type='checkbox' name='users[]'
                value='$username' $checked>
            $username
        </label>";
}

echo json_encode(['html' => $html]);
