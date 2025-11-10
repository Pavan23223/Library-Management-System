<?php
include("data_class.php");

// Start session if needed
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (!isset($_SESSION['adminid'])) {
    header("Location: index.html");  
    exit();
}


// Create one $u object and set connection
$u = new data();
$u->setconnection();
$u->calculateOverdueFines();
$conn = $u->getConnection();

try {
    // Prepare the query — adjust table/column names as per your DB
$stmt = $conn->prepare("
    SELECT 
        f.id,
        u.name AS username,
        f.type,
        f.message,
        f.image,
        f.status,
        f.created_at
    FROM feedback f
    JOIN userdata u ON f.userid = u.id
    ORDER BY f.created_at DESC
");
$stmt->execute();
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("❌ Database Error: " . $e->getMessage());
}




// Get logged-in admin ID
$adminId = $_SESSION['adminid'] ?? null;

$allFines = $u->getAllFinesSorted(); // fetch fines to pass to your table

if ($adminId) {
    $firstAdmin = $u->getAdminById($adminId);
}

// Determine profile image
if (!empty($firstAdmin) && !empty($firstAdmin['photo']) && file_exists("uploads/".$firstAdmin['photo'])) {
    $profileimg = "uploads/".$firstAdmin['photo'];
} else {
    $profileimg = "uploads/default_photo.jpg"; // default image
    $firstAdmin = $firstAdmin ?? ['email'=>'Not logged in','type'=>''];
}

// Get URL params
$viewid = $_GET['viewid'] ?? '';
$msg = $_GET['msg'] ?? '';
$tab = $_GET['tab'] ?? '';

$allFines = $u->getAllFinesSorted();

// Default book variables
$bookid = $bookimg = $bookname = $bookdetail = $bookauthour = $bookpub = $branch = $bookprice = $bookquantity = $bookava = null;

// Load book details if viewid is set
if (!empty($viewid)) {
    $stmt = $u->getbookdetail($viewid);
    if ($stmt && $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $bookid = $row['id'];
        $bookimg = $row['bookpic'] ?? '';
        $bookname = $row['bookname'] ?? '';
        $bookdetail = $row['bookdetail'] ?? '';
        $bookauthour = $row['bookauthor'] ?? '';
        $bookpub = $row['bookpub'] ?? '';
        $branch = $row['branch'] ?? '';
        $bookprice = $row['bookprice'] ?? '';
        $bookquantity = $row['bookquantity'] ?? '';
        $bookava = $row['bookava'] ?? '';
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="assets/logo.png?v=2">

    <title>Librarian Dashboard</title>
  <link rel="stylesheet" href="dashboard.css">
  <style>
    :root {
      --primary: #003366;   /* 60% */
      --light-bg: #f4f6fa;  /* 30% */
      --accent:  #f4f6fa;     /* 10% */
      --text-dark: #1e293b;
      --text-light: #ffffff;
      --card-bg: #ffffff;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      display: flex;
      min-height: 100vh;
      background-color: var(--light-bg);
    }

    /* Sidebar */
    .sidebar {
      width: 240px;
      background: var(--primary);
      color: var(--text-light);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: 0.3s;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar h2 {
      text-align: center;
      padding: 1.5rem 0;
      font-size: 1.5rem;
      background: rgba(255, 255, 255, 0.1);
      letter-spacing: 1px;
    }

    .sidebar ul {
      list-style: none;
    }

    .sidebar ul li {
      padding: 15px 25px;
      cursor: pointer;
      transition: 0.3s;
    }

    .sidebar ul li:hover,
    .sidebar ul li.active {
      background: var(--accent);
      color: var(--primary);
      border-left: 4px solid var(--primary);
    }

    .sidebar .logout {
      padding: 15px 25px;
      background: rgba(255, 255, 255, 0.1);
      text-align: center;
      cursor: pointer;
      transition: 0.3s;
    }

    .sidebar .logout:hover {
      background: var(--accent);
      color: var(--primary);
    }

    /* Main content */
    .main-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      background: var(--light-bg);
    }

    header {
      background: var(--primary);
      color: var(--text-light);
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    header h1 {
      font-size: 1.6rem;
      font-weight: 600;
    }

    header .btn {
      background: var(--accent);
      color: var(--primary);
      border: none;
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      transition: 0.3s;
    }

    header .btn:hover {
      background: #ffd633;
    }

    .content-area {
      flex: 1;
      padding: 2rem;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
    }
.cards-row {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  gap: 20px; /* Adds space between cards */
  margin: 20px 0; /* Top and bottom breathing space */
  padding: 10px;  /* Adds consistent side spacing */
}

.card {
  flex: 1 1 calc(33.333% - 20px); /* 3 cards per row with gap */
  background: var(--card-bg, #ffffff);
  color: var(--text-dark, #003366);
  border-radius: 16px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
  padding: 1.5rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  min-width: 260px; /* Prevents squishing on small screens */
}
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
    }

    .card h3 {
      color: var(--primary);
      font-size: 1.2rem;
      margin-bottom: 10px;
    }

    .card p {
      color: var(--text-dark);
      font-size: 0.95rem;
      line-height: 1.5;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        position: fixed;
        left: -100%;
        top: 0;
        height: 100%;
        z-index: 1000;
      }

      .sidebar.active {
        left: 0;
      }

      header {
        position: relative;
      }

      .menu-toggle {
        background: none;
        border: 2px solid var(--accent);
        color: var(--accent);
        padding: 6px 10px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 18px;
      }
    }
  </style>

</head>

<body>

    <!-- TOPBAR -->
  <!-- TOPBAR (Original) -->
<div class="topbar">
  <div class="topbar-left">
    <img src="assets/logo.png" alt="Arivu Logo">
    <span>Arivu Library</span>
  </div>

  <div class="topbar-right">
    <a href="index.html">Home</a>
    <button onclick="window.location.href='logout.php'" class="logout-btn">Sign Out</button>
    <div class="profile-container">
      <img src="<?php echo $profileimg; ?>" id="profilePic" class="profile-pic" alt="Profile">
      <div class="dropdown" id="dropdownMenu">
        <div style="text-align:center;">
          <img src="<?php echo $profileimg; ?>" 
               style="width:80%;height:80%;object-fit:cover;margin-bottom:8px;">
        </div>
        <p><b>Email:</b> <?php echo htmlspecialchars($firstAdmin['email']); ?></p>
        <p><b>Type:</b> <?php echo htmlspecialchars($firstAdmin['type']); ?></p>
        <hr>
        <button onclick="window.location.href='logout.php'" 
                class="logout-btn">Logout</button>
      </div>
    </div>
  </div>
</div>




    <!-- MESSAGE BOX -->
    <div id="messageBox" style="display:none;"><span id="msgText"></span></div>

    <!-- DASHBOARD CONTAINER -->
    <div class="dashboard-container">

        <!-- SIDEBAR -->
        <div class="sidebar">
            <ul>
                <li data-section="dashboard" onclick="loadSection('dashboard')">Dashboard</li>
                <li data-section="addperson" onclick="loadSection('addperson')">Manage Users</li>
                <li data-section="addadmin" onclick="loadSection('addadmin')">Manage Admins</li>
                <li data-section="addbook" onclick="loadSection('addbook')">Manage Books</li>
                <li data-section="bookrequestapprove" onclick="loadSection('bookrequestapprove')">Requests</li>
                <li data-section="userrecord" onclick="loadSection('userrecord')">Users Reports</li>
                <li data-section="adminsrecord" onclick="loadSection('adminsrecord')">Admins Reports</li>
                <li data-section="issuebook" onclick="loadSection('issuebook')">Issue Books</li>
                <li data-section="issuebookreport" onclick="loadSection('issuebookreport')">Issue Reports</li>
                <li data-section="bookreport" onclick="loadSection('bookreport')">Book Reports</li>
                <li data-section="managefines" onclick="loadSection('managefines')">Manage Fines</li>
               <li data-section="finesdetails" onclick="loadSection('finesdetails')">Fines Details</li>
               <li data-section="feedbackdetails" onclick="loadSection('feedbackdetails')">Feedback / Complaints</li>
               <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- RIGHT CONTENT -->

<div class="rightinnerdiv">

    <!-- DASHBOARD -->
    <div id="dashboard" class="portion">
        <h1 class="dashboard-title">Dashboard</h1>
        <?php
        // Make sure $u is defined and connected
$userCount = $u->getCount("userdata");
$bookCount = $u->getCount("book");
$requestCount = $u->getCount("requestbook", "approve = 0");


        ?>
        <div class="cards-row">
            <div class="card">
                <h2><?php echo htmlspecialchars($userCount); ?></h2>
                <p>Total Users</p>
            </div>
            <div class="card">
                <h2><?php echo htmlspecialchars($bookCount); ?></h2>
                <p>Total Books</p>
            </div>
            <div class="card">
                <h2><?php echo htmlspecialchars($requestCount); ?></h2>
                <p>Pending Requests</p>
            </div>
        </div>
    </div>


    <!-- fine secrion  -->
<div id="managefines" class="portion" style="display:none;">
  <div class="form-card">
    <h1 class="dashboard-title">Add Fine to User</h1>
    <form action="addfine_server.php" method="post">
      <label>Select Student:</label>
      <select name="userid" required>
        <option value="">-- Select Student --</option>
        <?php
        $students = $u->userdata();
        foreach ($students as $student) {
          echo "<option value='{$student['id']}'>{$student['name']} ({$student['email']})</option>";
        }
        ?>
      </select>

      <label>Reason:</label>
      <input type="text" name="reason" placeholder="e.g., Late return" required>

      <label>Amount (₹):</label>
      <input type="number" step="0.01" name="amount" required>

      <input type="submit" value="Add Fine">
    </form>
  </div>
</div>



<!-- Fines Details section -->
<div id="finesdetails" class="portion" style="display:none;">
    <h1 class="dashboard-title">Fines Details</h1>

    <?php
    if (!empty($allFines)) {
    echo "<table class='styled-table'>
        <tr>
            <th>User</th>
            <th>Book</th>
            <th>Reason</th>
            <th>Amount (₹)</th>
            <th>Status</th>
            <th>Payment Proof</th>
            <th>Action</th>
        </tr>";

foreach ($allFines as $fine) {
    $userName = htmlspecialchars($fine['username']);
    $bookName = htmlspecialchars($fine['bookname'] ?? 'N/A');
    $reason = htmlspecialchars($fine['reason']);
    $amount = htmlspecialchars($fine['amount']);
    $status = htmlspecialchars($fine['status']);
    $fineId = (int)$fine['id'];
    $paymentProof = $fine['payment_proof'] ?? '';

    echo "<tr>
            <td>{$userName}</td>
            <td>{$bookName}</td>
            <td>{$reason}</td>
            <td>₹{$amount}</td>
            <td>{$status}</td>
            <td>";
            
            if (!empty($paymentProof)) {
              echo "<a href='uploads/payments/" . htmlspecialchars($paymentProof) . "' target='_blank' style='text-decoration: none;'>View Proof</a>";

            } else {
                echo "No proof uploaded";
            }

    echo "</td>
          <td>
              <a href='deletefine.php?id={$fineId}' class='delete-btn btn btn-primary' onclick=\"return confirm('Are you sure you want to delete this fine?');\">Delete</a>
          </td>
        </tr>";
}

echo "</table>";

    } else {
        echo "<p>No fines found.</p>";
    }
    ?>
</div>

<div id="feedbackdetails" class="portion" style="display:none;">
  <h1 class="dashboard-title">All Feedback / Complaints</h1>

  <?php
  $feedbacks = $u->getAllFeedback();
  if ($feedbacks && count($feedbacks) > 0) {
      echo "<table class='styled-table'>
              <tr>
                  <th>ID</th>
                  <th>User ID</th>
                  <th>Type</th>
                  <th>Message</th>
                  <th>Image</th>
                  <th>Date</th>
                  <th>Action</th>
              </tr>";

      foreach ($feedbacks as $fb) {
          // ✅ Handle image properly
          $imageHTML = "N/A";
          if (!empty($fb['image']) && file_exists("uploads/feedback/" . $fb['image'])) {
              $imagePath = "uploads/feedback/" . $fb['image'];
              $imageHTML = "<a href='$imagePath' target='_blank'>
                              <img src='$imagePath' width='60' height='60' style='object-fit:cover;border-radius:6px;'>
                            </a>";
          }

          // ✅ Display table row
          echo "<tr>
                  <td>" . htmlspecialchars($fb['id']) . "</td>
                  <td>" . htmlspecialchars($fb['userid']) . "</td>
                  <td>" . htmlspecialchars(ucfirst($fb['type'])) . "</td>
                  <td>" . htmlspecialchars($fb['message']) . "</td>
                  <td>$imageHTML</td>
                  <td>" . htmlspecialchars($fb['created_at']) . "</td>
                  <td>
                      <a href='delete_feedback.php?id=" . $fb['id'] . "' 
                         onclick=\"return confirm('Are you sure you want to delete this feedback?');\" 
                         class='btn btn-primary' 
                         style='background:#ff4d4d;color:white;padding:6px 10px;border-radius:6px;text-decoration:none;'>
                         Delete
                      </a>
                  </td>
                </tr>";
      }

      echo "</table>";
  } else {
      echo "<p style='color:#666;'>No feedback submitted yet.</p>";
  }
  ?>
</div>




 
           <!-- ADD PERSON -->
<div id="addperson" class="portion" style="display:none;">
  <div class="form-card">
    <h1 class="dashboard-title">Add User</h1>

    <form action="addpersonserver_page.php" method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label>User ID:</label>
        <input type="text" name="addid" required>
      </div>

      <div class="form-group">
        <label>Name:</label>
        <input type="text" name="addname" required>
      </div>

      <div class="form-group">
        <label>Password:</label>
        <input type="text" name="addpass" required>
      </div>

      <div class="form-group">
        <label>Email:</label>
        <input type="email" name="addemail" required>
      </div>

      <div class="form-group">
        <label>Mobile Number:</label>
        <input type="number" name="addmobile" pattern="\d{10}" maxlength="10" required>
      </div>

      <div class="form-group">
        <label>Branch:</label>
        <select name="branch" required>
          <option value="">-- Select Branch --</option>
          <option value="CSE">CSE</option>
          <option value="ISE">ISE</option>
          <option value="DS">DS</option>
          <option value="ECE">ECE</option>
          <option value="EEE">EEE</option>
          <option value="CE">CE</option>
          <option value="ME">ME</option>
          <option value="Teacher">Teacher</option>
        </select>
      </div>

      <div class="form-group">
        <label>Type:</label>
        <select name="type">
          <option value="student">Student</option>
          <option value="teacher">Teacher</option>
        </select>
      </div>

      <div class="form-group">
        <label>Profile Image:</label>
        <input type="file" name="profileimg" accept="image/*" required>
      </div>

      <div class="form-group">
        <input type="submit" value="SUBMIT">
      </div>
    </form>
  </div>
</div>


            <!-- ISSUE BOOK -->
<div id="issuebook" class="portion" style="display:none;">
  <div class="form-card">
    <h1 class="dashboard-title">Issue Book</h1>
    <form action="issuebook_server.php" method="post" class="issue-book-form">
      
      <div class="form-group">
        <label>Choose Book:</label>
        <select name="book" required>
          <option value="">-- Select Book --</option>
          <?php
          $u = new data();
          $u->setconnection();
          $books = $u->getbookissue();
          foreach ($books as $book) {
            echo "<option value='" . htmlspecialchars($book[0]) . "'>" . htmlspecialchars($book[2]) . "</option>";
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label>Select Student:</label>
        <select name="userselect" required>
          <option value="">-- Select Student --</option>
          <?php
          $students = $u->userdata();
          foreach ($students as $student) {
            echo "<option value='" . htmlspecialchars($student[0]) . "'>" . htmlspecialchars($student[1]) . "</option>";
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label>Days:</label>
        <input type="number" name="days" min="1" required>
      </div>

      <button type="submit" class="btn-primary">Issue Book</button>
    </form>
  </div>
</div>


            <!-- BOOK REQUESTS -->       
    <div id="bookrequestapprove" class="rightinnerdiv portion" style="display:none;">
    <h1 class="dashboard-title">Book Request Approve</h1>
    <?php
      $u = new data();
      $u->setconnection();
      $recordset = $u->requestbookdata();

      if ($recordset) {
          echo "<table class='styled-table'>
                  <thead>
                    <tr>
                      <th>Person Name</th>
                      <th>Person Type</th>
                      <th>Book Name</th>
                      <th>Days</th>
                      <th>Approve</th>
                    </tr>
                  </thead>
                  <tbody>";
          foreach ($recordset as $row) {
              echo "<tr>
                      <td>" . htmlspecialchars($row['username']) . "</td>
                      <td>" . htmlspecialchars($row['usertype']) . "</td>
                      <td>" . htmlspecialchars($row['bookname']) . "</td>
                      <td>" . htmlspecialchars($row['issuedays']) . "</td>
                      <td>
                        <a href='approvebookrequest.php?reqid=" . $row['id'] . "&book=" . urlencode($row['bookname']) . "&userselect=" . urlencode($row['username']) . "&days=" . $row['issuedays'] . "'>
                          <button type='button' class='btn-primary'>Approve</button>
                        </a>
                      </td>
                    </tr>";
          }
          echo "</tbody></table>";
      } else {
          echo "<p>No book requests found.</p>";
      }
    ?>
</div>


<!-- USER RECORD -->
<div id="userrecord" class="portion" style="display:none;">
    <h1 class="dashboard-title">USER RECORD</h1>
    <?php
    $u = new data();
    $u->setconnection();
    $recordset = $u->userdata();

    $teachers = [];
    $students = [];

    // Separate teachers and students
    foreach ($recordset as $row) {
        if ($row['type'] === 'teacher') {
            $teachers[] = $row;
        } elseif ($row['type'] === 'student') {
            $branchKey = !empty($row['branch']) ? $row['branch'] : "No Branch";
            $students[$branchKey][] = $row;
        }
    }

    // Function to get photo with fallback
    function getUserPhoto($photo) {
        if (!empty($photo) && file_exists('uploads/' . $photo)) {
            return 'uploads/' . htmlspecialchars($photo);
        } else {
            return 'assets/no-image.png'; // Make sure this exists
        }
    }

    // Display Teachers
    if (!empty($teachers)) {
        echo "<h2>Teachers</h2>";
        echo "<table class='styled-table'>
            <tr>
                <th>Id</th>
                <th>Photo</th>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile Number</th>
                <th>Delete</th>
            </tr>";

        foreach ($teachers as $row) {
            $photoPath = getUserPhoto($row['photo']);
            echo "<tr>
                    <td>" . htmlspecialchars($row['id']) . "</td>
                    <td><img src='$photoPath' width='50' height='50' style='border-radius:50%;object-fit:cover;'></td>
                    <td>" . htmlspecialchars($row['name']) . "</td>
                    <td>" . htmlspecialchars($row['email']) . "</td>
                    <td>" . htmlspecialchars($row['mobile']) . "</td>
                    <td><a href='deleteuser.php?useriddelete=" . $row['id'] . "' class='delete-btn btn btn-primary'>Delete</a></td>
                  </tr>";
        }

        echo "</table>";
    }

    // Display Students by Branch
    if (!empty($students)) {
        foreach ($students as $branchName => $studentList) {
            echo "<h2>Students - Branch: " . htmlspecialchars($branchName) . "</h2>";
            echo "<table class='styled-table'>
                <tr>
                    <th>Id</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile Number</th>
                    <th>Delete</th>
                </tr>";

            foreach ($studentList as $row) {
                $photoPath = getUserPhoto($row['photo']);
                echo "<tr>
                        <td>" . htmlspecialchars($row['id']) . "</td>
                        <td><img src='$photoPath' width='50' height='50' style='border-radius:50%;object-fit:cover;'></td>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . htmlspecialchars($row['email']) . "</td>
                        <td>" . htmlspecialchars($row['mobile']) . "</td>
                        <td><a href='deleteuser.php?useriddelete=" . $row['id'] . "' class='delete-btn btn btn-primary'>Delete</a></td>
                      </tr>";
            }

            echo "</table>";
        }
    }
    ?>
</div>




            <!-- ADMINS RECORD -->
            <div id="adminsrecord" class="portion" style="display:none;">
                <h1 class="dashboard-title">Admins RECORD</h1>
                <?php
                $u = new data;
                $u->setconnection();
                $recordset = $u->getadmins(); // Get only admins

                echo "<table class='styled-table'>
            <tr>
                <th>Id</th>
                <th>Email</th>
                <th>Type</th>
                <th>Profile</th>
                <th>Action</th>
            </tr>";

                foreach ($recordset as $row) {
                    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['email']}</td>
            <td>{$row['type']}</td>
            <td><img src='uploads/{$row['photo']}' width='50px' height='50px' style='border-radius:50%;object-fit:cover;'></td>
            <td>
                <a href='deleteadmin.php?adminid={$row['id']}' class='delete-btn btn btn-primary' onclick='return confirm(\"Are you sure you want to delete this admin?\");'>Delete</a>
            </td>
          </tr>";
                }


                echo "</table>";
                ?>
            </div>

<!-- Book Reports -->
<div id="bookreport" class="innerright portion" style="display:none">
    <h1>BOOK RECORD</h1>
    <?php
    $u = new data();
    $u->setconnection();
    $recordset = $u->getbook(); // Fetch all books

    // Group books by branch
    $branches = [];
    foreach ($recordset as $row) {
        $branches[$row['branch']][] = $row;
    }

    // Loop through each branch and display table
    foreach ($branches as $branchName => $books) {
        echo "<h2 style='margin-top:20px;'>Branch: " . htmlspecialchars($branchName) . "</h2>";

        $table = "<table class='styled-table' style='font-family: Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;'>
        <tr>
            <th>Image</th>
            <th>Book Name</th>
            <th>Price</th>
            <th>Qnt</th>
            <th>Available</th>
            <th>View</th>
        </tr>";

        foreach ($books as $row) {
            $table .= "<tr>";
            $table .= "<td><img src='uploads/" . htmlspecialchars($row['bookpic']) . "' alt='Book Image' style='width:60px;height:auto;'></td>";
            $table .= "<td>" . htmlspecialchars($row['bookname']) . "</td>";
            $table .= "<td>₹" . htmlspecialchars($row['bookprice']) . "</td>";
            $table .= "<td>" . htmlspecialchars($row['bookquantity']) . "</td>";
            $table .= "<td>" . htmlspecialchars($row['bookava']) . "</td>";
            $table .= "<td>
                        <a href='admin_service_dashboard.php?viewid=" . $row['id'] . "'>
                            <button type='button' class='btn btn-primary'>View BOOK</button>
                        </a>
                       </td>";
            $table .= "</tr>";
        }

        $table .= "</table>";
        echo $table;
    }
    ?>
</div>


            <!-- Book Details Section -->
            <div id="bookdetail" class="innerright portion"
                style="<?php echo !empty($_REQUEST['viewid']) ? '' : 'display:none'; ?>">

                <h1 >BOOK DETAIL</h1>

                <div class="bookdetail-container">
                    <!-- Book Image -->
                    <div class="book-img">
                        <?php if (!empty($bookimg)): ?>
                            <img src="uploads/<?php echo htmlspecialchars($bookimg); ?>" alt="Book Image">
                        <?php else: ?>
                            <img src="assets/books.png" alt="No Image Available">
                        <?php endif; ?>
                    </div>

                    <!-- Book Info -->
                    <div class="book-info">
                        <p><strong>Book Name:</strong> <?php echo htmlspecialchars($bookname ?? 'N/A'); ?></p>
                        <p><strong>Book Detail:</strong> <?php echo htmlspecialchars($bookdetail ?? 'N/A'); ?></p>
                        <p><strong>Book Author:</strong> <?php echo htmlspecialchars($bookauthour ?? 'N/A'); ?></p>
                        <p><strong>Book Publisher:</strong> <?php echo htmlspecialchars($bookpub ?? 'N/A'); ?></p>
                        <p><strong>Book Branch:</strong> <?php echo htmlspecialchars($branch ?? 'N/A'); ?></p>
                        <p><strong>Book Price:</strong> ₹<?php echo htmlspecialchars($bookprice ?? 'N/A'); ?></p>
                        <p><strong>Book Available:</strong> <?php echo htmlspecialchars($bookava ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>


            <!-- issue Reports -->
            <div id="issuebookreport" class="innerright portion" style="display:none">
                <h1>Issue Book Record</h1>
                
                <?php
                $u = new data;
                $u->setconnection();
                $u->issuereport();
                $recordset = $u->issuereport();

                $table = "<table  class='styled-table' style='font-family: Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;'>
                <tr>
                    <th style='padding: 8px;'>Issue Name</th>
                    <th>Book Name</th>
                    <th>Issue Date</th>
                    <th>Return Date</th>
                    <th>Fine</th>
                    <th>Issue Type</th>
                </tr>";

                foreach ($recordset as $row) {
                    $table .= "<tr>";
                    "<td>$row[0]</td>"; // (unused, kept for original)
                    $table .= "<td>$row[2]</td>";
                    $table .= "<td>$row[3]</td>";
                    $table .= "<td>$row[6]</td>";
                    $table .= "<td>$row[7]</td>";
                    $table .= "<td>$row[8]</td>";
                    $table .= "<td>$row[4]</td>";
                    $table .= "</tr>";
                }
                $table .= "</table>";
                echo $table;
                ?>
            </div>



            <!-- MANAGE ADMINS -->
           <!-- MANAGE ADMINS -->
<div id="addadmin" class="portion" style="display:none;">
  <div class="form-card">
    <h1 class="dashboard-title">Manage Admins</h1>

    <form action="addadmin_service.php" method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label>Email:</label>
        <input type="email" name="email" placeholder="Enter admin email" required>
      </div>

      <div class="form-group">
        <label>Password:</label>
        <input type="text" name="pass" placeholder="Enter password" required>
      </div>

      <div class="form-group">
        <label>Type:</label>
        <select name="type" required>
          <option value="">-- Select Role --</option>
          <option value="superadmin">Super Admin</option>
          <option value="librarian">Librarian</option>
        </select>
      </div>

      <div class="form-group">
        <label>Profile Image:</label>
        <input type="file" name="profileimg" accept="image/*" required>
      </div>

      <div class="form-group">
        <input type="submit" value="Add Admin">
      </div>
    </form>
  </div>
</div>


            <!-- ADD BOOK -->
            <!-- ADD BOOK -->
<div id="addbook" class="portion" style="display:none;">
  <div class="form-card">
    <h1 class="dashboard-title">Add Book</h1>

    <form action="addbookserver_page.php" method="post" enctype="multipart/form-data">

      <div class="form-group">
        <label>Book ID:</label>
        <input type="number" name="bookid" placeholder="Enter book ID" required>
      </div>

      <div class="form-group">
        <label>Book Name:</label>
        <input type="text" name="bookname" placeholder="Enter book name" required>
      </div>

      <div class="form-group">
        <label>Detail:</label>
        <input type="text" name="bookdetail" placeholder="Enter details about the book" required>
      </div>

      <div class="form-group">
        <label>Author:</label>
        <input type="text" name="bookauthor" placeholder="Enter author name" required>
      </div>

      <div class="form-group">
        <label>Publication:</label>
        <input type="text" name="bookpub" placeholder="Enter publication" required>
      </div>

      <div class="form-group">
        <label>Branch:</label>
        <div class="branch-options">
          <label><input type="radio" name="branch" value="CSE" required> CSE</label>
          <label><input type="radio" name="branch" value="ISE"> ISE</label>
          <label><input type="radio" name="branch" value="DS"> DS</label>
          <label><input type="radio" name="branch" value="ECE"> ECE</label>
          <label><input type="radio" name="branch" value="EEE"> EEE</label>
          <label><input type="radio" name="branch" value="CE"> CE</label>
          <label><input type="radio" name="branch" value="ME"> ME</label>
        </div>
      </div>

      <div class="form-group">
        <label>Price (₹):</label>
        <input type="number" name="bookprice" placeholder="Enter price" required>
      </div>

      <div class="form-group">
        <label>Quantity:</label>
        <input type="number" name="bookquantity" placeholder="Enter quantity" required>
      </div>

      <div class="form-group">
        <label>Book Photo:</label>
        <input type="file" name="bookphoto" accept="image/*" required>
      </div>

      <div class="form-group">
        <input type="submit" value="Add Book">
      </div>

    </form>
  </div>
</div>


        </div>
    </div>

    <script>
        // Profile dropdown
        const profilePic = document.getElementById('profilePic');
        const dropdownMenu = document.getElementById('dropdownMenu');

        profilePic.addEventListener('click', () => dropdownMenu.classList.toggle('show'));
        document.addEventListener('click', (e) => {
            if (!profilePic.contains(e.target) && !dropdownMenu.contains(e.target))
                dropdownMenu.classList.remove('show');
        });

        // Sidebar navigation
        function loadSection(sectionId) {
    document.querySelectorAll('.portion').forEach(s => s.style.display = 'none');
    const target = document.getElementById(sectionId);
    if (target) target.style.display = 'block';
    document.querySelectorAll('.sidebar ul li').forEach(li => li.classList.remove('active'));
    const activeLi = document.querySelector(`.sidebar ul li[data-section='${sectionId}']`);
    if (activeLi) activeLi.classList.add('active');
}

const menuToggle = document.getElementById("menuToggle");
  const sidebar = document.querySelector(".sidebar");

  if (menuToggle && sidebar) {
    menuToggle.addEventListener("click", () => {
      sidebar.classList.toggle("active");
    });

    // Optional: Hide sidebar when a menu item is clicked
    document.querySelectorAll(".sidebar ul li").forEach(li => {
      li.addEventListener("click", () => {
        sidebar.classList.remove("active");
      });
    });
  }
    </script>

</body>

</html>


