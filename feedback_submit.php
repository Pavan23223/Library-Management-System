<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("data_class.php");

if (!isset($_SESSION["userid"])) {
    die("No user logged in");
}

$userid = $_SESSION["userid"];
$type = $_POST["type"] ?? '';
$message = $_POST["message"] ?? '';
$image = $_FILES["image"]["name"] ?? null;

$uploadedImage = null;
if ($image && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
    $targetDir = "uploads/feedback/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $targetFile = $targetDir . time() . "_" . basename($image);
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        $uploadedImage = basename($targetFile);
    }
}

$u = new data();
$u->setconnection();
$u->submitFeedback($userid, $type, $message, $uploadedImage);

header("Location: otheruser_dashboard.php?section=feedback&status=success");
exit;
?>
