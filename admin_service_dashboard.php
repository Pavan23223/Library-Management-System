<?php
include("data_class.php");

// Start session if needed
session_start();

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
    $profileimg = "../assets/default_photo.jpg"; // default image
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
    /* ========== Responsive Sidebar ========== */
@media (max-width: 768px) {
  .menu-btn {
    display: block;
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    margin-right: 10px;
    color: #003366;
  }

  .sidebar {
    position: fixed;
    top: 85px; /* adjust for topbar height */
    left: -250px; /* hide sidebar initially */
    width: 220px;
    height: 100%;
    background: var(--sidebar-bg, #003366);
    transition: left 0.3s ease;
    z-index: 999;
    padding-top: 10px;
  }

  .sidebar.active {
    left: 0; /* show sidebar when toggled */
  }

  .dashboard-container {
    flex-direction: column;
  }

  .rightinnerdiv {
    padding: 15px;
  }
}

/* ========== Desktop (hide menu button, show sidebar normally) ========== */
@media (min-width: 769px) {
  .menu-btn {
    display: none; /* hide ☰ on desktop */
  }

  .sidebar {
    position: relative;
    left: 0;
    width: 220px;
    height: auto;
    background: var(--sidebar-bg, #003366);
  }
}

</style>

</head>

<body>

    <!-- TOPBAR -->
   <div class="topbar">
  <div class="topbar-left">
    <button id="menuToggle" class="menu-btn">☰</button>
    <img src="../assets/logo.png" alt="Arivu Logo">
    <span>Arivu</span>
  </div>
  <div class="topbar-right">
    <a href="../index.html">Home</a>
    <button onclick="window.location.href='logout.php'" class="logout-btn">Sign Out</button>

    <div class="profile-container">
      <img src="<?php echo $profileimg; ?>" id="profilePic" class="profile-pic">
      <div class="dropdown" id="dropdownMenu">
        <div style="text-align:center;">
          <img src="<?php echo $profileimg; ?>" style="width:100%;height:100%;object-fit:cover;margin-bottom:8px;">
        </div>
        <p><b>Email:</b> <?php echo htmlspecialchars($firstAdmin['email']); ?></p>
        <p><b>Type:</b> <?php echo htmlspecialchars($firstAdmin['type']); ?></p>
        <hr>
        <p><a href="#" onclick="loadSection('addadmin')" style="color: red;">Manage Profile</a></p>
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
                <li data-section="studentrecord" onclick="loadSection('studentrecord')">Student Reports</li>
                <li data-section="studentrecord" onclick="loadSection('adminsrecord')">Admins Reports</li>
                <li data-section="issuebook" onclick="loadSection('issuebook')">Issue Books</li>
                <li data-section="issuebookreport" onclick="loadSection('issuebookreport')">Issue Reports</li>
                <li data-section="bookreport" onclick="loadSection('bookreport')">Book Reports</li>
                <li data-section="managefines" onclick="loadSection('managefines')">Manage Fines</li>
               <li data-section="finesdetails" onclick="loadSection('finesdetails')">Fines Details</li>
               <li data-section="feedbackdetails" onclick="loadSection('feedbackdetails')">Feedback / Complaints</li>
                <li><a href="index.html">Logout</a></li>
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
  <div class="fine-card">
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

      <input type="submit" value="Add Fine" class="submit-btn">
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
          $imagePath = !empty($fb['image']) ? 'uploads/feedback/' . htmlspecialchars($fb['image']) : 'assets/no-image.png';
          echo "<tr>
                  <td>" . htmlspecialchars($fb['id']) . "</td>
                  <td>" . htmlspecialchars($fb['userid']) . "</td>
                  <td>" . htmlspecialchars($fb['type']) . "</td>
                  <td>" . htmlspecialchars($fb['message']) . "</td>
                  

                  <td><a href='uploads/feedback/". htmlspecialchars($paymentProof) . "' target='_blank' style='text-decoration: none;' ><img src='$imagePath' width='60'></a></td>
              
                  <td>" . htmlspecialchars($fb['created_at']) . "</td>
                  <td>
                      <a href='delete_feedback.php?id=" . $fb['id'] . "' 
                         onclick=\"return confirm('Are you sure you want to delete this feedback?');\" 
                         class='delete-btn btn btn-primary' >Delete</a>
                  </td>
                </tr>";
      }
      echo "</table>";
  } else {
      echo "<p>No feedback submitted yet.</p>";
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
        <input type="password" name="addpass" required>
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
                <h1 class="dashboard-title">Issue Book</h1>
                <form action="issuebook_server.php" method="post">
                    <label>Choose Book:</label>
                    <select name="book" required>
                        <option value="">-- Select Book --</option>
                        <?php
                        $u = new data();
                        $u->setconnection();
                        $books = $u->getbookissue();
                        foreach ($books as $book) {
                            // Assuming $book[0] = book ID, $book[2] = book name
                            echo "<option value='" . htmlspecialchars($book[0]) . "'>" . htmlspecialchars($book[2]) . "</option>";
                        }
                        ?>
                    </select>

                    <label>Select Student:</label>
                    <select name="userselect" required>
                        <option value="">-- Select Student --</option>
                        <?php
                        $students = $u->userdata();
                        foreach ($students as $student) {
                            // Assuming $student[0] = user ID, $student[1] = name
                            echo "<option value='" . htmlspecialchars($student[0]) . "'>" . htmlspecialchars($student[1]) . "</option>";
                        }
                        ?>
                    </select>

                    <label>Days:</label>
                    <input type="number" name="days" min="1" required>

                    <input type="submit" value="SUBMIT" />
                </form>
            </div>


            <!-- BOOK REQUESTS -->
            <div id="bookrequestapprove" class="innerright portion" style="display:none">
                <button class="greenbtn">BOOK REQUEST APPROVE</button>

                <?php
                $u = new data();
                $u->setconnection();
                $recordset = $u->requestbookdata();

                if ($recordset) {
                    $table = "<table style='font-family: Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;'>
                    <tr>
                        <th>Person Name</th>
                        <th>Person Type</th>
                        <th>Book Name</th>
                        <th>Days</th>
                        <th>Approve</th>
                    </tr>";

                    foreach ($recordset as $row) {
                        $table .= "<tr>";
                        $table .= "<td>" . htmlspecialchars($row['username']) . "</td>";
                        $table .= "<td>" . htmlspecialchars($row['usertype']) . "</td>";
                        $table .= "<td>" . htmlspecialchars($row['bookname']) . "</td>";
                        $table .= "<td>" . htmlspecialchars($row['issuedays']) . "</td>";
                        $table .= "<td>
                        <a href='approvebookrequest.php?reqid=" . $row['id'] . "&book=" . urlencode($row['bookname']) . "&userselect=" . urlencode($row['username']) . "&days=" . $row['issuedays'] . "'>
                            <button type='button' class='btn btn-primary'>Approve</button>
                        </a>
                       </td>";
                        $table .= "</tr>";
                    }

                    $table .= "</table>";
                    echo $table;
                } else {
                    echo "<p>No book requests found.</p>";
                }
                ?>
            </div>


            <!-- STUDENT RECORD -->
            <div id="studentrecord" class="portion" style="display:none;">
                <h1 class="dashboard-title">Student RECORD</h1>
                <?php
                $u = new data;
                $u->setconnection();
                $recordset = $u->userdata();

                echo "<table class='styled-table'>
            <tr>
                <th>Id</th>
                <th>Photo</th>
                <th>Name</th>
                <th>Email</th>
                <th>Type</th>
                <th>Mobile Number</th>
                <th>Branch</th>
                <th>Delete</th>
            </tr>";
                foreach ($recordset as $row) {
                    echo "<tr>
                    <td>{$row['id']}</td>
                    <td><img src='uploads/{$row['photo']}' width='50px' height='50px' style='border-radius:50%;object-fit:cover;'></td>
                <td>{$row['name']}</td>
                <td>{$row['email']}</td>
                <td>{$row['type']}</td>
                <td>{$row['mobile']}</td>
                <td>{$row['branch']}</td>
                <td><a href='deleteuser.php?useriddelete={$row['id']}' class='delete-btn btn btn-primary'>Delete</a></td>
              </tr>";
                }
                echo "</table>";
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
                <button class="greenbtn">BOOK RECORD</button>
                <?php
                $u = new data;
                $u->setconnection();
                $recordset = $u->getbook();

                $table = "<table style='font-family: Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;'>
                <tr>
                    <th style='padding: 8px;'>Book Name</th>
                    <th>Price</th>
                    <th>Qnt</th>
                    <th>Available</th>
                    <th>View</th>
                </tr>";
                foreach ($recordset as $row) {
                    $table .= "<tr>";
                    $table .= "<td>$row[2]</td>"; // bookname
                    $table .= "<td>$row[7]</td>"; // bookprice
                    $table .= "<td>$row[8]</td>"; // bookquantity
                    $table .= "<td>$row[9]</td>"; // bookava
                    $table .= "<td>
                    <a href='admin_service_dashboard.php?viewid=$row[0]'>
                        <button type='button' class='btn btn-primary'>View BOOK</button>
                    </a>
                   </td>";
                    $table .= "</tr>";
                }
                $table .= "</table>";
                echo $table;
                ?>
            </div>

            <!-- Book Details Section -->
            <div id="bookdetail" class="innerright portion"
                style="<?php echo !empty($_REQUEST['viewid']) ? '' : 'display:none'; ?>">

                <button class="greenbtn">BOOK DETAIL</button>

                <div class="bookdetail-container">
                    <!-- Book Image -->
                    <div class="book-img">
                        <?php if (!empty($bookimg)): ?>
                            <img src="uploads/<?php echo htmlspecialchars($bookimg); ?>" alt="Book Image">
                        <?php else: ?>
                            <img src="assets/default-book.jpg" alt="No Image Available">
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
                <button class="greenbtn">Issue Book Record</button>
                <?php
                $u = new data;
                $u->setconnection();
                $u->issuereport();
                $recordset = $u->issuereport();

                $table = "<table style='font-family: Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;'>
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
        <input type="password" name="pass" placeholder="Enter password" required>
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


