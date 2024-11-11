<?php
session_start();
require_once '../config/db.php';
unset($_SESSION['admin_log']);
header('location: ../login.php');
