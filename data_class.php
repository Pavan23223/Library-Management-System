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



public function getUserFines($userid)
{
    try {
        $this->setconnection();

        $stmt = $this->connection->prepare("
            SELECT 
                f.id,
                f.reason,
                f.amount,
                f.status,
                i.issuebook AS bookname
            FROM fines AS f
            LEFT JOIN issuebook AS i ON f.issueid = i.id
            WHERE f.userid = :userid
            ORDER BY f.id DESC
        ");
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo '❌ Database error in getUserFines(): ' . $e->getMessage();
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





public function returnBook($issueId) {
    try {
        $currentDate = date("Y-m-d");

        $sql = "UPDATE issuebook 
                SET returndate = :returndate, status = 'returned' 
                WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':returndate', $currentDate);
        $stmt->bindParam(':id', $issueId);
        $stmt->execute();

        return true;
    } catch (PDOException $e) {
        echo "❌ Database Error (returnBook): " . $e->getMessage();
        return false;
    }
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
        $q = "SELECT * FROM book ";
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
            $issuename = $user['name'];
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
            $bookname = $bookrow['bookname'];
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
    ('$issueid', '$issuename', '$bookname', '$issuetype', '$days', '$getdate', '$returnDate', '0')";


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

   public function issuereport() {
    try {
        $sql = "SELECT * FROM issuebook ORDER BY issuedate DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "❌ Database Error (issuereport): " . $e->getMessage();
        return [];
    }
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


public function getIssuedBooks() {
    try {
        $stmt = $this->connection->prepare("
            SELECT 
                i.id,
                i.issuename AS username,
                i.issuebook AS bookname,
                i.issuetype,
                i.issuedays,
                i.issuedate,
                i.issuereturn AS returndate,
                i.fine,
                i.status
            FROM issuebook AS i
            WHERE i.status = 'issued'
            ORDER BY i.issuedate DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "❌ Database Error (getIssuedBooks): " . $e->getMessage();
        return [];
    }
}



public function getReturnedBooks() {
    try {
        $stmt = $this->connection->prepare("
            SELECT 
                i.id,
                i.issuename AS username,
                i.issuebook AS bookname,
                i.issuetype,
                i.issuedays,
                i.issuedate,
                i.issuereturn AS returndate,
                i.issuereturn AS actualreturndate,
                i.fine,
                i.status
            FROM issuebook AS i
            WHERE i.status = 'returned'
            ORDER BY i.issuedate DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "❌ Database Error (getReturnedBooks): " . $e->getMessage();
        return [];
    }
}

// Replace the calculateOverdueFines() method in data_class.php with this fixed version

public function calculateOverdueFines() {
    try {
        $today = new DateTime();
        $finePerDay = 10; // ₹10 per day

        // Fetch all issued books that haven't been returned
        $stmt = $this->connection->prepare("
            SELECT * FROM issuebook 
            WHERE status = 'issued' OR status IS NULL OR status = ''
        ");
        $stmt->execute();
        $issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($issues as $row) {
            $issueid = $row['id'];
            $userid = $row['userid'];
            $bookName = $row['issuebook'];
            
            // Parse issue date - try multiple formats
            $issuedate = null;
            if (!empty($row['issuedate']) && $row['issuedate'] !== '0000-00-00') {
                // Try Y-m-d format first (MySQL standard)
                $issuedate = DateTime::createFromFormat('Y-m-d', $row['issuedate']);
                
                // If that fails, try d/m/Y format
                if (!$issuedate) {
                    $issuedate = DateTime::createFromFormat('d/m/Y', $row['issuedate']);
                }
                
                // If still fails, try creating from string
                if (!$issuedate) {
                    try {
                        $issuedate = new DateTime($row['issuedate']);
                    } catch (Exception $e) {
                        continue; // Skip this record if date is invalid
                    }
                }
            }
            
            if (!$issuedate) {
                continue; // Skip if we couldn't parse the date
            }

            // Calculate due date
            $issueDays = (int)($row['issuedays'] ?? 7); // Default 7 days if not set
            $dueDate = clone $issuedate;
            $dueDate->modify("+{$issueDays} days");

            // Check if overdue
            if ($today > $dueDate) {
                $overdueDays = $today->diff($dueDate)->days;
                $fineAmount = $overdueDays * $finePerDay;

                // Check if fine already exists for this issue
                $checkStmt = $this->connection->prepare("
                    SELECT id FROM fines WHERE issueid = :issueid
                ");
                $checkStmt->execute([':issueid' => $issueid]);

                if ($checkStmt->rowCount() == 0) {
                    // No existing fine - create new one
                    $reason = "Late return of '$bookName' ({$overdueDays} days overdue)";
                    
                    $insertStmt = $this->connection->prepare("
                        INSERT INTO fines (userid, issueid, reason, amount, status, fine_date)
                        VALUES (:userid, :issueid, :reason, :amount, 'unpaid', NOW())
                    ");
                    
                    $insertStmt->execute([
                        ':userid' => $userid,
                        ':issueid' => $issueid,
                        ':reason' => $reason,
                        ':amount' => $fineAmount
                    ]);

                    // Update fine amount in issuebook table
                    $updateStmt = $this->connection->prepare("
                        UPDATE issuebook SET fine = :fine WHERE id = :id
                    ");
                    $updateStmt->execute([
                        ':fine' => $fineAmount,
                        ':id' => $issueid
                    ]);
                } else {
                    // Fine exists - update it if amount changed
                    $updateFineStmt = $this->connection->prepare("
                        UPDATE fines 
                        SET amount = :amount, 
                            reason = :reason 
                        WHERE issueid = :issueid AND status = 'unpaid'
                    ");
                    
                    $reason = "Late return of '$bookName' ({$overdueDays} days overdue)";
                    $updateFineStmt->execute([
                        ':amount' => $fineAmount,
                        ':reason' => $reason,
                        ':issueid' => $issueid
                    ]);

                    // Update issuebook table
                    $updateStmt = $this->connection->prepare("
                        UPDATE issuebook SET fine = :fine WHERE id = :id
                    ");
                    $updateStmt->execute([
                        ':fine' => $fineAmount,
                        ':id' => $issueid
                    ]);
                }
            }
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("❌ Fine Calculation Error: " . $e->getMessage());
        return false;
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


function normalizeResult($stmt) {
    if (!$stmt) return [];
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return is_array($rows) ? $rows : [];
}




public function searchBooks($searchTerm) {
    try {
        $searchTerm = trim($searchTerm);
        
        // If search term is empty, return all books
        if (empty($searchTerm)) {
            return $this->getbook();
        }
        
        // Prepare search query - searches in id, bookname, and bookauthor
        $stmt = $this->connection->prepare("
            SELECT * FROM book 
            WHERE id LIKE :search 
            OR bookname LIKE :search 
            OR bookauthor LIKE :search 
            ORDER BY bookname ASC
        ");
        
        $searchParam = '%' . $searchTerm . '%';
        $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("❌ Search Error: " . $e->getMessage());
        return [];
    }
}

// 2. For user dashboard - add this search method for request book section
public function searchAvailableBooks($searchTerm) {
    try {
        $searchTerm = trim($searchTerm);
        
        if (empty($searchTerm)) {
            // Return all available books
            $stmt = $this->connection->prepare("SELECT * FROM book WHERE bookava > 0 ORDER BY bookname ASC");
            $stmt->execute();
        } else {
            // Search in available books only
            $stmt = $this->connection->prepare("
                SELECT * FROM book 
                WHERE (id LIKE :search 
                OR bookname LIKE :search 
                OR bookauthor LIKE :search)
                AND bookava > 0
                ORDER BY bookname ASC
            ");
            
            $searchParam = '%' . $searchTerm . '%';
            $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("❌ Search Error: " . $e->getMessage());
        return [];
    }
}


public function getissuebook($userid)
{
    try {
        $this->setconnection();

        $stmt = $this->connection->prepare("
            SELECT 
                i.id,
                i.issuebook,
                i.issuename,
                i.issuedate,
                i.issuedays,
                i.fine,
                i.status
            FROM issuebook AS i
            WHERE i.userid = :userid AND i.status = 'issued'
            ORDER BY i.issuedate DESC
        ");
        $stmt->bindParam(':userid', $userid);
        $stmt->execute();

        return $stmt; // returning PDOStatement (like your code expects)
    } catch (PDOException $e) {
        echo '❌ Database error in getissuebook(): ' . $e->getMessage();
        return false;
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




