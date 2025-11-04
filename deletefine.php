<?php
include("data_class.php");
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['adminid'])) {
    header("Location: login.php");
    exit;
}

// Check if fine ID is provided
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $u = new data();
    $u->setconnection(); // Ensure DB connection

    // Use class method to delete fine
    $success = $u->deleteFine($id);

    if ($success) {
        header("Location: admin_service_dashboard.php?tab=managefines&msg=Fine deleted successfully");
    } else {
        header("Location: admin_service_dashboard.php?tab=managefines&msg=Error deleting fine");
    }
    exit;
}

// If no ID provided, redirect back
header("Location: admin_service_dashboard.php?tab=managefines");
exit;
