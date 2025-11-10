<?php
include("db.php");

class data extends db
{

    private $bookid;
    private $bookpic;
    private $bookname;
    private $bookdetail;
    private $bookauthor;
    private $bookpub;
    private $branch;
    private $bookprice;
    private $bookquantity;
    private $type;
    public $conn;
protected $connection; 

    private $book;
    private $userselect;
    private $days;
    private $getdate;
    private $returnDate;


    
    function __construct(){
         $this->setconnection();
         
    }

    public function getConnection() {
    return $this->connection;
}

public function addadmin($email, $pass, $type, $photo)
{
    try {
        // Use prepared statements to prevent SQL injection
        $stmt = $this->connection->prepare("INSERT INTO admin (email, pass, type, photo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $pass, $type, $photo]);

        return true;
    } catch (PDOException $e) {
        echo "Error adding admin: " . $e->getMessage();
        return false;
    }
}


function adminLogin($t1, $t2){
    $stmt = $this->connection->prepare("SELECT * FROM admin WHERE email=:email AND pass=:pass LIMIT 1");
    $stmt->bindParam(':email', $t1);
    $stmt->bindParam(':pass', $t2);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if($admin){
        session_start();
        $_SESSION['adminid'] = $admin['id'];
        header("Location: admin_service_dashboard.php");
        exit();
    } else {
        header("Location: index.php?msg=Invalid Credentials");
        exit();
    }
}
// Marks the fine as 'pending' after user uploads proof

public function markFinePaid($fineid, $filename) {
    $stmt = $this->connection->prepare("UPDATE fines SET status='paid', payment_proof=:proof WHERE id=:id");
    $stmt->bindParam(':proof', $filename);
    $stmt->bindParam(':id', $fineid);
    return $stmt->execute();
}



public function getUserFines($userid) {
        $q = "SELECT * FROM fines WHERE userid = ?";
        try {
            $stmt = $this->connection->prepare($q);
            $stmt->execute([$userid]);
            $recordset = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $recordset;
        } catch (PDOException $e) {
           
            return [];
        }
    }

public function getAllFinesSorted() {
    $stmt = $this->connection->prepare("
        SELECT f.*, u.name AS username, 
               COALESCE(i.issuebook, 'N/A') AS bookname
        FROM fines AS f
        LEFT JOIN userdata AS u ON f.userid = u.id
        LEFT JOIN issuebook AS i ON f.issueid = i.id
        ORDER BY CASE WHEN f.status='paid' THEN 1 ELSE 2 END, f.id DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}





public function addFine($userid, $reason, $amount) {
    $stmt = $this->connection->prepare(
        "INSERT INTO fines (userid, reason, amount, status) VALUES (:userid, :reason, :amount, 'unpaid')"
    );
    $stmt->bindValue(':userid', $userid, PDO::PARAM_STR); // <-- STRING
    $stmt->bindValue(':reason', $reason, PDO::PARAM_STR);
    $stmt->bindValue(':amount', $amount, PDO::PARAM_STR); // fine as decimal
    return $stmt->execute();
}





    function userdetail($id){
        $q = "SELECT * FROM userdata where id ='$id'";
        $data = $this->connection->query($q);
        return $data;
    }

   function addnewuser($id, $name, $password, $email, $addmobile, $type, $branch, $photo) {
    try {
        $stmt = $this->connection->prepare("INSERT INTO userdata (id, name, email, pass, type, mobile, branch, photo)
                                            VALUES (:id, :name, :email, :pass, :type, :mobile, :branch, :photo)");

        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':pass', $password); // You can hash this with password_hash()
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':mobile', $addmobile);
        $stmt->bindParam(':branch', $branch);
        $stmt->bindParam(':photo', $photo);

        if ($stmt->execute()) {
            header("Location: admin_service_dashboard.php?msg=New+user+added&tab=addperson");
            exit();
        } else {
            header("Location: admin_service_dashboard.php?msg=Error+adding+user&tab=addperson");
            exit();
        }
    } catch (PDOException $e) {
        // For debugging
        die("Error: " . $e->getMessage());
    }
}


    function addbook($bookid, $bookpic, $bookname, $bookdetail, $bookauthor, $bookpub, $branch, $bookprice, $bookquantity){
        $this->bookid = $bookid;
        $this->bookpic = $bookpic;
        $this->bookname = $bookname;
        $this->bookdetail = $bookdetail;
        $this->bookauthor = $bookauthor;
        $this->bookpub = $bookpub;
        $this->branch = $branch;
        $this->bookprice = $bookprice;
        $this->bookquantity = $bookquantity;

        $q = "INSERT INTO book (id, bookpic, bookname, bookdetail, bookauthor, bookpub, branch, bookprice, bookquantity, bookava) 
              VALUES ('$bookid', '$bookpic', '$bookname', '$bookdetail', '$bookauthor', '$bookpub', '$branch', '$bookprice', '$bookquantity', '$bookquantity')";

        if ($this->connection->exec($q)) {

            header("Location: admin_service_dashboard.php?msg=done");
            exit();
        } else {
            header("Location: admin_service_dashboard.php?msg=fail");
            exit();
        }
    }


    function userdata(){
        $q = "SELECT * FROM userdata ";
        $data = $this->connection->query($q);
        return $data;
    }


    function delteuserdata($id){
        $q = "DELETE from userdata where id='$id'";
        if ($this->connection->exec($q)) {


            header("Location:admin_service_dashboard.php?msg=done");
        } else {
            header("Location:admin_service_dashboard.php?msg=fail");
        }
    }

 public function deleteadmin($id){
    if(!$this->connection){
        echo "Database connection not set!";
        return false;
    }
    $sql = "DELETE FROM admin WHERE id = :id";
    $stmt = $this->connection->prepare($sql);
    return $stmt->execute(['id' => $id]);
}

public function deleteFine($fineId) {
    $stmt = $this->connection->prepare("DELETE FROM fines WHERE id = :id");
    $stmt->bindParam(':id', $fineId, PDO::PARAM_INT);
    return $stmt->execute();
}






    function getbook() {
        $q = "SELECT * FROM book ";
        $data = $this->connection->query($q);
        return $data;
    }



function getbookdetail($id){
    $q = "SELECT * FROM book WHERE id = '$id'";
    return $this->connection->query($q);
}



    function getbookissue(){
        $q = "SELECT * FROM book where bookava !=0 ";
        $data = $this->connection->query($q);
        return $data;
    }

        function issuebook($book, $userselect, $days, $getdate, $returnDate)
        {
            // ---- Get user ----
            // $q = "SELECT * FROM userdata WHERE name='$userselect'";
            $q = "SELECT * FROM userdata WHERE id='$userselect'";

            $userResult = $this->connection->query($q);

            if ($userResult->rowCount() == 0) {
                header("Location: admin_service_dashboard.php?msg=no_user");
                exit();
            }

            $user = $userResult->fetch(PDO::FETCH_ASSOC);
            $issueid = $user['id'];
            $issuetype = $user['type'];

            // ---- Get book ----
            // $q = "SELECT * FROM book WHERE bookname='$book'";
            $q = "SELECT * FROM book WHERE id='$book'";

            $bookResult = $this->connection->query($q);

            if ($bookResult->rowCount() == 0) {
                header("Location: admin_service_dashboard.php?msg=no_book");
                exit();
            }

            $bookrow = $bookResult->fetch(PDO::FETCH_ASSOC);
            $bookid   = $bookrow['id'];
            $bookava  = $bookrow['bookava'];

            // ---- Check availability BEFORE update ----
            if ($bookava <= 0) {
                header("Location: admin_service_dashboard.php?msg=not_available");
                exit();
            }

            // ---- Update stock ----
            $newbookava = $bookava - 1;
            $q = "UPDATE book SET bookava='$newbookava' WHERE id='$bookid'";
            $this->connection->exec($q);

            // ---- Insert issue record ----
            $q = "INSERT INTO issuebook
                (userid, issuename, issuebook, issuetype, issuedays, issuedate, issuereturn, fine)
            VALUES
                ('$issueid', '$userselect', '$book', '$issuetype', '$days', '$getdate', '$returnDate', '0')";

            if ($this->connection->exec($q)) {
                header("Location: admin_service_dashboard.php?msg=done");
            } else {
                header("Location: admin_service_dashboard.php?msg=fail");
            }
        }



function getbookdetailByName($bookname) {
    $q = "SELECT * FROM book WHERE bookname = ?";
    $stmt = $this->connection->prepare($q);
    $stmt->execute([$bookname]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}




    function requestbookdata()
    {
        $q = "SELECT * FROM requestbook ";
        $data = $this->connection->query($q);
        return $data;
    }

    function requestbook($userid, $bookid){

        $q = "SELECT * FROM book where id='$bookid'";
        $recordSetss = $this->connection->query($q);

        $q = "SELECT * FROM userdata where id='$userid'";
        $recordSet = $this->connection->query($q);

        foreach ($recordSet->fetchAll() as $row) {
            $username = $row['name'];
            $usertype = $row['type'];
        }

        foreach ($recordSetss->fetchAll() as $row) {
            $bookname = $row['bookname'];
        }

        if ($usertype == "student") {
            $days = 7;
        }
        if ($usertype == "teacher") {
            $days = 21;
        }


        $q = "INSERT INTO requestbook (id,userid,bookid,username,usertype,bookname,issuedays)VALUES('','$userid', '$bookid', '$username', '$usertype', '$bookname', '$days')";

        if ($this->connection->exec($q)) {
            header("Location:otheruser_dashboard.php?userlogid=$userid");
        } else {
            header("Location:otheruser_dashboard.php?msg=fail");
        }
    }

    function issuereport()
    {
            $this->setconnection();
         $this->autoCalculateFines();
        $q = "SELECT * FROM issuebook ";
        $data = $this->connection->query($q);
        return $data;
    }

  function userLogin($t1, $t2){
    $q = "SELECT * FROM userdata WHERE (id = :email OR email = :email) AND pass = :pass LIMIT 1";
    $stmt = $this->connection->prepare($q);
    $stmt->bindParam(':email', $t1);
    $stmt->bindParam(':pass', $t2);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user){
        session_start();
        $_SESSION['userid'] = $user['id'];
        header("Location: otheruser_dashboard.php"); // redirect to student dashboard
        exit();
    } else {
        echo "<p style='color:red;'>Invalid credentials</p>";
    }
}


function getIssueById($issueid) {
    $stmt = $this->connection->prepare("SELECT * FROM issuebook WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $issueid, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row : null;
}



function getissuebook($userloginid) {
    $finePerDay = 10; // fine per overdue day
    $today = date("Y-m-d");

    // Fetch all issued books for the user
    $q = "SELECT * FROM issuebook WHERE userid = ?";
    $stmt = $this->connection->prepare($q);
    $stmt->execute([$userloginid]);
    $issueRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($issueRecords as $row) {
        $issueId = $row['id'];
        $bookName = $row['issuebook'];
        $issuedate = $row['issuedate'];
        $issuereturn = $row['issuereturn'];
        $issuedays = $row['issuedays'];

        // Calculate expected return date (issuedate + issuedays)
        $expectedReturn = date('Y-m-d', strtotime($issuedate . " + $issuedays days"));

        // Check if not yet returned and overdue
        if ($issuereturn == '0000-00-00' && $today > $expectedReturn) {
           $daysOverdue = floor((strtotime($today) - strtotime($expectedReturn)) / (60 * 60 * 24));
            $newFine = $daysOverdue * $finePerDay;

            // 1️⃣ Update fine in issuebook table
            $updateIssue = $this->connection->prepare("UPDATE issuebook SET fine = ? WHERE id = ?");
            $updateIssue->execute([$newFine, $issueId]);

            // 2️⃣ Insert or update fine record in fines table
            $checkFine = $this->connection->prepare("SELECT id FROM fines WHERE issueid = ?");
            $checkFine->execute([$issueId]);

            if ($checkFine->rowCount() == 0) {
                // Insert new fine record
                $reason = "Late return of '$bookName' ($daysOverdue days overdue)";
                $insertFine = $this->connection->prepare("
                    INSERT INTO fines (userid, issueid, reason, amount, status, fine_date)
                    VALUES (?, ?, ?, ?, 'unpaid', NOW())
                ");
                $insertFine->execute([$userloginid, $issueId, $reason, $newFine]);
            } else {
                // Update existing fine amount if already exists
                $updateFine = $this->connection->prepare("UPDATE fines SET amount = ?, fine_date = NOW() WHERE issueid = ?");
                $updateFine->execute([$newFine, $issueId]);
            }
        }
    }

    // Return updated issuebook data for dashboard display
    $q = "SELECT * FROM issuebook WHERE userid = ?";
    $stmt = $this->connection->prepare($q);
    $stmt->execute([$userloginid]);
    return $stmt;
}

//  calaculatee the fine
public function calculateOverdueFines() {
    $today = new DateTime();
    $finePerDay = 10; // ₹10 per day

    $stmt = $this->connection->prepare("SELECT * FROM issuebook");
    $stmt->execute();
    $issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($issues as $row) {
        $issueid = $row['id'];
        $userid = $row['userid'];
        $bookName = $row['issuebook'];

        // 🔥 Use correct date format (d/m/Y)
        $issuedate = DateTime::createFromFormat('d/m/Y', $row['issuedate']);
        $dueDate = DateTime::createFromFormat('d/m/Y', $row['issuereturn']);

        if (!$issuedate || !$dueDate) {
            continue; // skip if date parsing fails
        }

        // Only if overdue
        if ($today > $dueDate) {
            $overdueDays = $today->diff($dueDate)->days;
            $fineAmount = $overdueDays * $finePerDay;

            // Avoid duplicate fines
            $check = $this->connection->prepare("SELECT * FROM fines WHERE issueid = :issueid");
            $check->execute([':issueid' => $issueid]);

            if ($check->rowCount() == 0) {
                $reason = "Late return of '$bookName' ({$overdueDays} days overdue)";
                $stmt2 = $this->connection->prepare("
                    INSERT INTO fines (userid, issueid, reason, amount, status, fine_date)
                    VALUES (:userid, :issueid, :reason, :amount, 'unpaid', NOW())
                ");
                $stmt2->execute([
                    ':userid' => $userid,
                    ':issueid' => $issueid,
                    ':reason' => $reason,
                    ':amount' => $fineAmount
                ]);

                $updateFine = $this->connection->prepare("UPDATE issuebook SET fine = :fine WHERE id = :id");
                $updateFine->execute([':fine' => $fineAmount, ':id' => $issueid]);
            }
        }
    }
}


function getbookdetailById($bookId) {
    $this->setconnection();
    $stmt = $this->connection->prepare("SELECT * FROM book WHERE id = ?");
    $stmt->execute([$bookId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



function autoCalculateFines() {
    $this->setconnection();

    // Fetch all unreturned books
    $query = "SELECT * FROM issuebook WHERE issuereturn = '0000-00-00'";
    $stmt = $this->connection->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($result) {
        $today = new DateTime();

        foreach ($result as $row) {
            $issuedate = new DateTime($row['issuedate']);
            $dueDate = clone $issuedate;
            $dueDate->modify('+' . $row['issuedays'] . ' days');

            // Only calculate fine if overdue
            if ($today > $dueDate) {
                $daysOverdue = $today->diff($dueDate)->days;
                $fineAmount = $daysOverdue * 10; // ₹10/day fine

                // Check if fine already exists for this issue
                $checkFine = "SELECT * FROM fines WHERE issueid = ?";
                $checkStmt = $this->connection->prepare($checkFine);
                $checkStmt->execute([$row['id']]);

                if ($checkStmt->rowCount() == 0) {
                    $reason = "Late return of '{$row['issuebook']}' ($daysOverdue days overdue)";
                    $insertFine = "INSERT INTO fines (userid, issueid, reason, amount, status, fine_date)
                                   VALUES (?, ?, ?, ?, 'unpaid', NOW())";
                    $insertStmt = $this->connection->prepare($insertFine);
                    $insertStmt->execute([$row['userid'], $row['id'], $reason, $fineAmount]);
                }
            }
        }
    }
}






    // issue issuebookapprove
    function issuebookapprove($book, $userselect, $days, $getdate, $returnDate, $redid){
        $this->book = $book;
        $this->userselect = $userselect;
        $this->days = $days;
        $this->getdate = $getdate;
        $this->returnDate = $returnDate;

        // Fetch book info
        $q = "SELECT * FROM book WHERE bookname='$book'";
        $recordSetBook = $this->connection->query($q);
        $bookRow = $recordSetBook->fetch(PDO::FETCH_ASSOC);

        // Fetch user info
        $q = "SELECT * FROM userdata WHERE name='$userselect'";
        $recordSetUser = $this->connection->query($q);
        $userRow = $recordSetUser->fetch(PDO::FETCH_ASSOC);

        if ($userRow && $bookRow) {
            $issueid = $userRow['id'];
            $issuetype = $userRow['type'];
            $bookid = $bookRow['id'];

            // Reduce book availability
            $newbookava = $bookRow['bookava'] - 1;
            $q = "UPDATE book SET bookava='$newbookava' WHERE id='$bookid'";

            if ($this->connection->exec($q)) {
                // Insert issue record
                $q = "INSERT INTO issuebook (userid,issuename,issuebook,issuetype,issuedays,issuedate,issuereturn,fine)
                  VALUES('$issueid','$userselect','$book','$issuetype','$days','$getdate','$returnDate','0')";
                if ($this->connection->exec($q)) {
                    // Delete request
                    $q = "DELETE FROM requestbook WHERE id='$redid'";
                    $this->connection->exec($q);

                    header("Location:admin_service_dashboard.php?msg=done");
                    exit;
                } else {
                    header("Location:admin_service_dashboard.php?msg=fail");
                    exit;
                }
            } else {
                header("Location:admin_service_dashboard.php?msg=fail");
                exit;
            }
        } else {
            header("Location:index.php?msg=Invalid Credentials");
            exit;
        }
    }


    
    function getCount($table, $condition = null)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM $table";
            if ($condition) {
                $sql .= " WHERE $condition";
            }
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }

public function getAllFeedback() {
    try {
        $sql = "SELECT * FROM feedback ORDER BY created_at DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "❌ Database Error (getAllFeedback): " . $e->getMessage();
        return [];
    }
}

public function deleteFeedback($id) {
    try {
        $sql = "DELETE FROM feedback WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo "❌ Database Error (deleteFeedback): " . $e->getMessage();
        return false;
    }
}




    public function submitFeedback($userid, $type, $message, $image = null) {
    try {
        $sql = "INSERT INTO feedback (userid, type, message, image, status, created_at)
                VALUES (:userid, :type, :message, :image, 'pending', NOW())";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':userid', $userid);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':image', $image);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo "❌ Database Error (submitFeedback): " . $e->getMessage();
        return false;
    }
}




    function getadmins()    {
        $q = "SELECT * FROM admin";
        $stmt = $this->connection->prepare($q);
        $stmt->execute();
        $recordset = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $recordset;
    }

public function getAdminById($adminId) {
    $this->setconnection(); // ensure DB connection
    $stmt = $this->connection->prepare("SELECT * FROM admin WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $adminId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row : null;
}



}



?>