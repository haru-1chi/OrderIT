<?php
session_start();
if(isset($_POST['panel_id']) && isset($_POST['action'])){
    $panelId = $_POST['panel_id'];
    $action = $_POST['action']; // 'show' or 'hide'
    
    $_SESSION['panel_state'][$panelId] = $action;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
