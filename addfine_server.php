<?php
include("data_class.php");
session_start();

$u = new data();  // creates object
$u->setconnection(); // ensures DB connection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = $_POST['userid'] ?? '';
    $reason = trim($_POST['reason'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);

    if ($userid !== '' && $reason !== '' && $amount > 0) {
        $success = $u->addFine($userid, $reason, $amount);
        if ($success) {
            header("Location: admin_service_dashboard.php?tab=managefines&msg=Fine added successfully");
            exit();
        } else {
            header("Location: admin_service_dashboard.php?tab=managefines&msg=Error adding fine");
            exit();
        }
    } else {
        header("Location: admin_service_dashboard.php?tab=managefines&msg=Invalid input");
        exit();
    }
}

