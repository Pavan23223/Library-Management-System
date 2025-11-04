<?php
include("data_class.php");

if (isset($_GET['adminid'])) {
    $adminId = $_GET['adminid'];

    $obj = new data();
    $obj->setconnection();

    if ($obj->deleteadmin($adminId)) {
        header("Location: admin_service_dashboard.php?msg=Admin deleted successfully&tab=addadmin");
        exit();
    } else {
        echo "Failed to delete admin.";
    }
} else {
    echo "No admin selected to delete.";
}
?>
