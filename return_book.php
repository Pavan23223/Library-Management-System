<?php
include("data_class.php");

if (isset($_GET['issueid'])) {
    $issueid = $_GET['issueid'];

    $obj = new data();
    $obj->setconnection();

    // ✅ Get DB connection safely
    $conn = $obj->getConnection();

    try {
        // ✅ Set current return date
        $returnDate = date("Y-m-d");

        // ✅ Update status and actual return date
        $query = "UPDATE issuebook 
                  SET status='returned', issuereturn = :returndate 
                  WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':returndate', $returnDate);
        $stmt->bindParam(':id', $issueid);
        $stmt->execute();

        // ✅ Recalculate fines (if overdue)
        $obj->calculateOverdueFines();

        echo "<script>
                alert('✅ Book returned successfully and fines recalculated!');
                window.location.href='admin_service_dashboard.php';
              </script>";
    } catch (PDOException $e) {
        echo "<script>
                alert('❌ Database Error: " . addslashes($e->getMessage()) . "');
                window.history.back();
              </script>";
    }
}
?>
