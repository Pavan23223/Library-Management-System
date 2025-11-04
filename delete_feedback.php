<?php
include("data_class.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $u = new data();
    $u->setconnection();

    if ($u->deleteFeedback($id)) {
        header("Location: admin_service_dashboard.php");
        exit;
    } else {
        echo "❌ Failed to delete feedback.";
    }
} else {
    echo "❌ No feedback ID provided.";
}
?>
