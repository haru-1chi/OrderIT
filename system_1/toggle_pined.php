<?php
require_once "../config/db.php";

if (isset($_POST['id']) && isset($_POST['pined'])) {
    $id = $_POST['id'];
    $pined = $_POST['pined'];

    try {
        $stmt = $conn->prepare("UPDATE notelist SET pined = :pined WHERE id = :id");
        $stmt->bindParam(":pined", $pined);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        echo "Pin status updated successfully.";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
} else {
    http_response_code(400);
    echo "Invalid parameters.";
}
