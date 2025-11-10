<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("data_class.php");

$u = new data();
$u->setconnection();
$conn = $u->getConnection();

$fineId = isset($_GET['fineid']) ? (int)$_GET['fineid'] : 0;

if ($fineId > 0) {
    $stmt = $conn->prepare("SELECT * FROM fines WHERE id = ?");
    $stmt->execute([$fineId]);
    $fine = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fine) {
        die("<p style='color:red;'>❌ No fine record found for ID $fineId.</p>");
    }
} else {
    die("<p style='color:red;'>❌ Invalid fine ID.</p>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pay Fine</title>
<style>
    body { font-family: Arial, sans-serif; background: #f2f2f2; display: flex; justify-content: center; align-items: center; height: 100vh; }
    .card { background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 0 10px #ccc; width: 600px; display: flex; gap: 20px; }
    .qr { flex: 0 0 180px; display: flex; justify-content: center; align-items: center; }
    .details { flex: 1; }
    .details p { margin: 8px 0; }
    .btn { display: inline-block; padding: 10px 15px; background: #e74c3c; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; margin-top: 10px; }
    .btn:hover { background: #c0392b; }
    input[type=file] { margin-top: 10px; }
</style>
</head>
<body>
<div class="card">
    <div class="qr">
        <!-- QR Code Image -->
        <img src="assets/qrcode.jpg" style="width: 100%;">
    </div>

    <div class="details">
        <h2>Pay Fine</h2>
        <p><b>Reason:</b> <?= htmlspecialchars($fine['reason']); ?></p>
        <p><b>Amount:</b> ₹<?= htmlspecialchars($fine['amount']); ?></p>
        <p><b>Status:</b> <?= htmlspecialchars($fine['status']); ?></p>

        <form action="upload_fine_proof.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="fineid" value="<?= $fine['id']; ?>">
            <label>Upload Payment Proof:</label>
            <input type="file" name="payment_proof" required>
            <br>
            <button type="submit" class="btn">Submit Payment Proof</button>
        </form>
    </div>
</div>
</body>
</html>
