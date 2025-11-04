<?php
include("data_class.php");

$email = $_POST['email'];
$pass = $_POST['pass'];
$type = $_POST['type'];

// --- File upload handling ---
if (isset($_FILES['profileimg']) && $_FILES['profileimg']['error'] == 0) {
    $photo = $_FILES['profileimg']['name'];
    move_uploaded_file($_FILES['profileimg']['tmp_name'], "uploads/".$photo);
} else {
    $photo = null; // or set a default image
}

// --- Call your function ---
$u = new data();
$u->setconnection();
$u->addadmin($email, $pass, $type, $photo);

header("Location: admin_service_dashboard.php?msg=Admin+Added");
exit();
?>
