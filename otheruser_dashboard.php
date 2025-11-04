<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("data_class.php");

// ✅ Get user ID from session or URL
$userloginid = isset($_GET['userlogid']) ? (int)$_GET['userlogid'] : ($_SESSION["userid"] ?? null);

if (!$userloginid) {
    die("❌ Invalid or missing user login ID.");
}

// ✅ Initialize data class
$u = new data();
$u->setconnection();
$u->calculateOverdueFines();

// ✅ Fetch user details
$stmt = $u->userdetail($userloginid);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // ✅ Store the correct user ID
    $_SESSION["userid"] = $user['id'];  
    $name = $user['name'];
   // or 'name' depending on your DB
    $email = $user['email'] ?? '';
    $type = $user['type'] ?? '';
    $userPhoto = !empty($user['photo']) ? "uploads/" . $user['photo'] : "uploads/default.jpg";
} else {
    die("❌ No user record found for ID: " . htmlspecialchars($userloginid));
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="assets/logo.png?v=2">
<title>User Dashboard</title>
<style>
:root {
    --sidebar-bg: #003366;
    --accent: #FFD700;
    --text-dark: #003366;
    --card-bg: #ffffff;
}
* { margin:0; padding:0; box-sizing:border-box; font-family: Arial, sans-serif;}
body { background:#F8F3CE; color:#333; display:flex; flex-direction:column; min-height:100vh;}

/* TOPBAR */
.topbar { background:#fff; color:var(--text-dark); display:flex; justify-content:space-between; align-items:center; padding:12px 30px; border-bottom:2px solid var(--text-dark);}
.topbar-left { display:flex; align-items:center;}
.topbar-left img { height:45px; margin-right:12px;}
.topbar-left span { font-size:1.6rem; font-weight:bold; color:var(--text-dark);}
.topbar-right { display:flex; align-items:center; gap:25px;}
.topbar-right a { color:var(--text-dark); text-decoration:none; font-size:1rem; font-weight:500;}
.topbar-right a:hover { color:var(--accent);}
.profile-container { position:relative; width:60px; height:60px;}
.profile-pic { width:100%; height:100%; border-radius:50%; object-fit:cover; cursor:pointer;}
.dropdown { position:absolute; right:0; top:50px; display:none; background:#fff; color:var(--text-dark); min-width:180px; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.2); padding:10px; z-index:1000;}
.dropdown.show { display:block;}
.dropdown p { margin:8px 0; font-size:0.9rem;}
.dropdown hr { border:none; border-top:1px solid #ccc; margin:8px 0;}

/* DASHBOARD LAYOUT */
.dashboard-container { flex:1; display:flex; overflow:hidden; min-height:calc(100vh - 66px);}
.sidebar { background:var(--sidebar-bg); color:white; width:220px; padding-top:20px; border-right:2px solid #ddd;}
.sidebar ul { list-style:none;}
.sidebar ul li { padding:12px 20px; font-weight:500; cursor:pointer; transition:0.3s;}
.sidebar ul li:hover, .sidebar ul li.active { background:var(--accent); color:var(--text-dark);}
.sidebar ul li a { color:inherit; text-decoration:none; display:block;}
.rightinnerdiv { flex:1; padding:20px; overflow-y:auto;}
h1.dashboard-title { margin-bottom:20px; color:var(--text-dark); }
input, select { padding:8px; width:100%; margin-top:4px; }
input[type="submit"], .greenbtn { margin-top:15px; padding:10px 15px; background-color:var(--accent); border:none; cursor:pointer; }

/* Tables */
.styled-table { border-collapse:collapse; margin:20px 0; font-size:16px; width:100%; box-shadow:0 0 20px rgba(0,0,0,0.1);}
.styled-table th, .styled-table td { padding:12px 15px; border:1px solid #ddd; text-align:left;}
.styled-table th { background-color:var(--sidebar-bg); color:#fff; text-transform:uppercase;}
.styled-table tr { background-color:#f8f3ce; }
.styled-table tr:nth-child(even) { background-color:#e0dcae; }
.styled-table tr:hover { background-color:#ffd700; cursor:pointer; }

/* Buttons in tables */
.btn-primary { padding:6px 12px; font-size:0.9rem; border-radius:5px; border:none; background:var(--sidebar-bg); color:#fff; cursor:pointer;}
.btn-primary:hover { background:var(--accent); color:var(--text-dark); }

/* Sections */
.portion { display:none; }
.logout-btn { background-color:#ff4b5c; color:white; border:none; padding:10px 18px; border-radius:8px; cursor:pointer; font-weight:bold; transition:0.3s;}
.logout-btn:hover { background-color:#e63b4a;}
.portion {
  background-color: #F8F3CE;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 40px 0;
}

/* Card container */
.account-card {
  background-color: #ffffff;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.1);
  width: 350px;
  padding: 25px;
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.account-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Profile image */
.profile-section {
  display: flex;
  justify-content: center;
  margin-bottom: 15px;
}

.profile-photo {
  width: 200px;
  height: 200px;
  object-fit: cover;
  background-color: #f0f0f0;
}

/* Info */
.user-name {
  font-size: 1.6em;
  color: #003366;
  margin-bottom: 8px;
}

.user-email,
.user-type {
  color: #333;
  font-size: 1em;
  margin: 5px 0;
}

.user-type {
  background-color: #FFD700;
  color: #003366;
  font-weight: bold;
  border-radius: 20px;
  display: inline-block;
  padding: 5px 15px;
  margin-top: 10px;
}

/* Responsive design */
@media (max-width: 600px) {
  .account-card {
    width: 90%;
  }
}
/* Hamburger button */
.menu-btn {
  display: none;
  font-size: 1.8rem;
  background: none;
  border: none;
  color: var(--text-dark);
  cursor: pointer;
}

/* Responsive behavior */
@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    left: -220px;
    top: 85px;
    height: 100%;
    width: 220px;
    background: var(--sidebar-bg);
    transition: left 0.3s ease;
    z-index: 1000;
  }

  .sidebar.active {
    left: 0;
  }

  .menu-btn {
    display: block;
    margin-right: 10px;
  }

  .dashboard-container {
    flex-direction: column;
  }

  .rightinnerdiv {
    padding: 15px;
  }
}


.feedback-card {
  background-color: #ffffff;
  border-radius: 15px;
  padding: 25px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  width: 70%;
  margin: auto;
  transition: 0.3s;
}

.feedback-card:hover {
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
}

.feedback-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-weight: bold;
  margin-bottom: 5px;
  color: #003366;
}

.form-control {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 10px;
  outline: none;
  transition: border-color 0.3s;
}

.form-control:focus {
  border-color: #FFD700;
}

textarea.form-control {
  min-height: 120px;
  resize: vertical;
}

.btn-primary {
  background-color: #FFD700;
  color: #003366;
  font-weight: bold;
  border: none;
  border-radius: 10px;
  padding: 10px 20px;
  cursor: pointer;
  transition: 0.3s;
}

.btn-primary:hover {
  background-color: #003366;
  color: #ffffff;
}

</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-left">
        <button id="menuToggle" class="menu-btn">☰</button>
        <img src="../assets/logo.png" alt="Logo">
        <span>User Dashboard</span>
    </div>
    <div class="topbar-right">
        <a href="../index.html">Home</a>
        <button onclick="window.location.href='logout.php'" class="logout-btn">Sign Out</button>
        <div class="profile-container">
            <img src="<?php echo $userPhoto; ?>" class="profile-pic" id="profilePic" alt="Profile">
            <div class="dropdown" id="dropdownMenu">
                <div style="text-align:center;">
                    <img src="<?php echo $userPhoto; ?>" style="width:100%;height:100%;object-fit:cover;margin-bottom:8px;">
                </div>
                <p><strong>Name: </strong><?php echo $name ?></p>
                <p><strong>Email: </strong><?php echo $email ?></p>
                <hr>
                <p><a href="#">View Profile</a></p>
            </div>
        </div>
    </div>
</div>

<!-- DASHBOARD -->
<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <ul>
            <li data-section="myaccount" >My Account</li>
            <li data-section="requestbook" >Request Book</li>
            <li data-section="issuereport" >Book Report</li>
            <li data-section="fines" >My Fines</li>
            <li data-section="feedback" >Feedback</li>
            <li><a href="../index.html">Logout</a></li>
        </ul>
    </div>

    <!-- Right content -->
    <div class="rightinnerdiv">
        <!-- My Account -->
        <div id="myaccount" class="portion" style="display:block;">
            <div class="account-card">
                <h1 class="dashboard-title">My Account</h1>
                <div class="profile-section">
      <img src="<?php echo !empty($photo) ? 'uploads/' . $photo : 'uploads/default_photo.jpg'; ?>" 
           alt="Profile Photo" 
           class="profile-photo">
    </div>

    <div class="info-section">
      <h2 class="user-name"><?php echo $name; ?></h2>
      <p class="user-email"><strong>Email:</strong> <?php echo $email; ?></p>
      <p class="user-type"><strong>Account Type:</strong> <?php echo ucfirst($type); ?></p>
    </div>
  </div>
</div>


<!-- fines  -->
        <div id="fines" class="portion">
    <h1 class="dashboard-title">My Fines</h1>
    <?php
    $fines = $u->getUserFines($userloginid); 

    if ($fines && count($fines) > 0) {
        echo "<table class='styled-table'>
                <tr>
                    <th>Book</th>
                    <th>Reason</th>
                    <th>Amount (₹)</th>
                    <th>Status</th>
                    <th>Payment Proof</th>
                </tr>";

        foreach ($fines as $fine) {
            $bookName = htmlspecialchars($fine['bookname'] ?? 'N/A');
            $reason = htmlspecialchars($fine['reason'] ?? 'N/A');
            $amount = htmlspecialchars($fine['amount'] ?? '0');
            $status = htmlspecialchars($fine['status'] ?? 'unknown');
            $paymentProof = htmlspecialchars($fine['payment_proof'] ?? '');

            echo "<tr>
                    <td>{$bookName}</td>
                    <td>{$reason}</td>
                    <td>₹{$amount}</td>
                    <td>{$status}</td>
                    <td>";

            if ($status === 'unpaid') {
                echo "<form action='upload_fine_proof.php' method='post' enctype='multipart/form-data'>
                        <input type='hidden' name='fineid' value='" . (int)$fine['id'] . "'>
                        <input type='file' name='payment_proof' required style='margin-bottom:6px;'>
                        <input type='submit' value='Upload' class='btn-primary'>
                      </form>";
            } elseif (!empty($paymentProof)) {
                echo "<img src='uploads/payments/{$paymentProof}' width='60' alt='Proof'>";
            } else {
                echo "N/A";
            }

            echo "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No fines found.</p>";
    }
    ?>
</div>

        <!-- Request Book -->
        <div id="requestbook" class="portion">
            <h1 class="dashboard-title">Request Book</h1>
            <?php
            $recordset = $u->getbookissue();
            echo "<table class='styled-table'>
                <tr><th>Image</th><th>Book Name</th><th>Details</th><th>Author</th><th>Branch</th><th>Request</th></tr>";
            foreach ($recordset as $row) {
                echo "<tr>
                    <td><img src='uploads/$row[1]' width='80px' height='80px'></td>
                    <td>$row[2]</td>
                    <td>$row[3]</td>
                    <td>$row[4]</td>
                    <td>$row[6]</td>
                    <td><a href='requestbook.php?bookid=$row[0]&userid=$userloginid' class='btn-primary'>Request</a></td>
                  </tr>";
            }
            echo "</table>";
            ?>
        </div>

        
        <!-- Book Issue Report -->
       <div id="issuereport" class="portion">
<h1 class="dashboard-title">Book Issue Report</h1>
<?php
$recordset = $u->getissuebook($userloginid);
echo "<table class='styled-table'>
<tr>
<th>Book Photo</th>
<th>Book Name</th>
<th>Issue Date</th>
<th>Return Day Left</th>
<th>Fine</th>
<th>Pay Fine</th>
</tr>";

foreach ($recordset as $row) {
    // Default values in case of an error
    $daysLeftText = "N/A"; 
    $bookPhoto = "assets/default-book.jpg";
    $bookName = "Unknown Book";

    try {
        $bookName = $row['issuebook'];
        $bookData = $u->getbookdetailByName($bookName); 
        $bookPhoto = !empty($bookData['bookpic']) ? "uploads/" . $bookData['bookpic'] : "assets/default-book.jpg";
        
        // ✅ Check for valid date
        if (!empty($row['issuedate']) && $row['issuedate'] !== '0000-00-00 00:00:00') {
            $issueDateObj = new DateTime($row['issuedate']);
            $returnDateObj = clone $issueDateObj;
            $returnDateObj->modify("+" . $row['issuedays'] . " days");

            $today = new DateTime();
            $daysLeft = $today->diff($returnDateObj)->format("%r%a");
            $daysLeftText = $daysLeft >= 0 ? "$daysLeft days left" : "Overdue by " . abs($daysLeft) . " days";
        } else {
            $daysLeftText = "Invalid issue date";
        }

    } catch (Exception $e) {
        // Safe fallback message in case of error
        $daysLeftText = "Error calculating date";
    }

    // ✅ Echo table row (always runs)
echo "<tr>
        <td><img src='$bookPhoto' width='50px' height='50px' style='border-radius:50%;object-fit:cover;'></td>
        <td>" . htmlspecialchars($bookName) . "</td>
        <td>" . htmlspecialchars($row['issuedate']) . "</td>
        <td>$daysLeftText</td>
        <td>" . htmlspecialchars($row['fine']) . "</td>
       <td><a href='pay_fine.php?fineid=" . $row['id'] . "' class='btn-primary'>Pay Fine</a></td>


      </tr>";



}

echo "</table>";

?>
                  
</div>



                  
<!-- FEEDBACK SECTION -->
<div id="feedback" class="portion" style="display:none;">
  <div class="feedback-card">
    <h1 class="dashboard-title">Feedback / Complaint / Query</h1>

    <form action="feedback_submit.php" method="POST" enctype="multipart/form-data" class="feedback-form">
      <input type="hidden" name="userid" value="<?php echo $userloginid; ?>">

      <div class="form-group">
        <label>Type:</label>
        <select name="type" required class="form-control">
          <option value="feedback">Feedback</option>
          <option value="complaint">Complaint</option>
          <option value="query">Query</option>
        </select>
      </div>

      <div class="form-group">
        <label>Message:</label>
        <textarea name="message" placeholder="Type your message..." required class="form-control"></textarea>
      </div>

      <div class="form-group">
        <label>Upload Image (optional):</label>
        <input type="file" name="image" class="form-control">
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-primary">Submit</button>
      </div>
    </form>
  </div>
</div>



                    <!-- PAY FINE SECTION -->
<div id="payfine" class="portion">
  <h1 class="dashboard-title">Pay Fine</h1>

  <?php
  // If the pay button was clicked, a query param ?issueid= will exist
  if (isset($_GET['issueid'])) {
      $issueId = (int)$_GET['issueid'];
      echo "
      <form action='upload_fine_proof.php' method='post' enctype='multipart/form-data'>
          <input type='hidden' name='fineid' value='{$issueId}'>
          <label>Select Payment Screenshot:</label><br>
          <input type='file' name='payment_proof' accept='image/*' required><br><br>
          <input type='submit' value='Upload Proof' class='btn-primary'>
      </form>";
  } else {
      echo "<p>No fine selected for payment.</p>";
  }
  ?>
</div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    console.log("✅ Dashboard JS Loaded");

    // Profile dropdown toggle
    const profilePic = document.getElementById("profilePic");
    const dropdownMenu = document.getElementById("dropdownMenu");

    if (profilePic && dropdownMenu) {
        profilePic.addEventListener("click", () => {
            dropdownMenu.classList.toggle("show");
        });

        document.addEventListener("click", (e) => {
            if (!profilePic.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove("show");
            }
        });
    }

    // Section loading function
    function loadSection(sectionId) {
        document.querySelectorAll(".portion").forEach(section => section.style.display = "none");
        const section = document.getElementById(sectionId);
        if (section) section.style.display = "block";

        document.querySelectorAll(".sidebar ul li").forEach(li => li.classList.remove("active"));
        const activeLi = document.querySelector(`.sidebar ul li[data-section="${sectionId}"]`);
        if (activeLi) activeLi.classList.add("active");

        console.log("📘 Switched to:", sectionId);
    }

    // Sidebar click events
    document.querySelectorAll(".sidebar ul li").forEach(li => {
        li.addEventListener("click", () => {
            const sectionId = li.getAttribute("data-section");
            loadSection(sectionId);
        });
    });

    // Load section from URL (?section=payfine)
    const urlParams = new URLSearchParams(window.location.search);
    const sectionFromUrl = urlParams.get("section");
    if (sectionFromUrl) {
        loadSection(sectionFromUrl);
    } else {
        loadSection("myaccount");
    }
});

// Sidebar toggle for mobile
const menuToggle = document.getElementById("menuToggle");
const sidebar = document.querySelector(".sidebar");

if (menuToggle && sidebar) {
  menuToggle.addEventListener("click", () => {
    sidebar.classList.toggle("active");
  });

  document.querySelectorAll(".sidebar ul li").forEach(li => {
    li.addEventListener("click", () => {
      sidebar.classList.remove("active");
    });
  });
}


</script>


</body>
</html>
