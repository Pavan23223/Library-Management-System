<?php
session_start();
include("data_class.php");

if (!isset($_SESSION['adminid'])) {
    header("Location: index.html");
    exit();
}

$bookId = $_GET['id'] ?? null;

if ($bookId) {
    $u = new data();
    $u->setconnection();
    
    try {
        $stmt = $u->getConnection()->prepare("DELETE FROM book WHERE id = :id");
        $stmt->bindParam(':id', $bookId, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            header("Location: admin_service_dashboard.php?tab=bookreport&msg=Book+deleted+successfully");
        } else {
            header("Location: admin_service_dashboard.php?tab=bookreport&msg=Error+deleting+book");
        }
    } catch (PDOException $e) {
        header("Location: admin_service_dashboard.php?tab=bookreport&msg=Error:+" . urlencode($e->getMessage()));
    }
} else {
    header("Location: admin_service_dashboard.php?tab=bookreport&msg=Invalid+book+ID");
}
exit();
?>