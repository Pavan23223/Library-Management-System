
<?php
session_start();
include("data_class.php");

$u = new data(); // create object
$u->setconnection(); // make sure connection is set

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fineid = isset($_POST['fineid']) ? (int)$_POST['fineid'] : 0;

    if ($fineid <= 0) {
        die("Invalid Fine ID.");
    }

    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['payment_proof']['tmp_name'];
        $fileName = $_FILES['payment_proof']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            die("Upload failed. Allowed types: " . implode(', ', $allowedExtensions));
        }

        $newFileName = 'fine_' . $fineid . '_' . time() . '.' . $fileExtension;
        $uploadDir = __DIR__ . '/uploads/payments/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Update fine record using your data class
            $u->markFinePaid($fineid, $newFileName); // make sure this function exists in your class

            header("Location: otheruser_dashboard.php?msg=Payment uploaded successfully");
            exit();
        } else {
            die("Error moving uploaded file.");
        }

    } else {
        die("No file uploaded or upload error.");
    }

} else {
    die("Invalid request method.");
}
?>

