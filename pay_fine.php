<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("data_class.php");

$u = new data();
$u->setconnection();

// ✅ Use public getter instead of protected property
$conn = $u->getConnection();

// ✅ Get fine ID from URL (either ?fineid= or ?issueid=)
$fineId = isset($_GET['fineid']) ? (int)$_GET['fineid'] : 0;
$issueId = isset($_GET['issueid']) ? (int)$_GET['issueid'] : 0;

if ($fineId > 0) {
    // ✅ Fetch by fine ID
    $stmt = $conn->prepare("SELECT * FROM fines WHERE issueid = ?");
    $stmt->execute([$fineId]);
    $fine = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($issueId > 0) {
    // ✅ Fetch by issue ID
    $stmt = $conn->prepare("SELECT * FROM fines WHERE issueid = ?");
    $stmt->execute([$issueId]);
    $fine = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    die("<p style='color:red;'>❌ Invalid fine or issue ID.</p>");
}

if (!$fine) {
    die("<p style='color:red;'>❌ No fine record found.</p>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pay Fine</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
            width: 400px;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: #0056b3;
        }
        input[type=file] {
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="card">
    
    <h2>Pay Fine</h2>
    <p><b>Reason:</b> <?= htmlspecialchars($fine['reason']); ?></p>
    <p><b>Amount:</b> ₹<?= htmlspecialchars($fine['amount']); ?></p>
    <p><b>Status:</b> <?= htmlspecialchars($fine['status']); ?></p>

    <form action="upload_fine_proof.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="fineid" value="<?= $fine['id']; ?>">
        <label>Upload Payment Proof:</label>
        <input type="file" name="payment_proof" required>
        <br><br>
        <button type="submit" class="btn">Submit Payment Proof</button>
    </form>
</div>

</body>
</html>
