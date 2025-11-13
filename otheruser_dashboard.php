<?php
// user_dashboard.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once "data_class.php"; // make sure this path is correct

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// --- Get user id (from URL or session). Don't cast to int because your IDs are alphanumeric like 1JB23CS106
$userloginid = isset($_GET['userlogid']) ? $_GET['userlogid'] : ($_SESSION["userid"] ?? null);

if (!$userloginid) {
    // No id supplied
    die("❌ Invalid or missing user login ID.");
}

// Initialize data class and ensure DB connection
$u = new data();
$u->setconnection();

// Make sure fines are up-to-date
// This method uses DB and may insert fine records for overdue items
if (method_exists($u, 'calculateOverdueFines')) {
    $u->calculateOverdueFines();
}

// Fetch user details safely (userdetail returns a PDOStatement or array in your class)
$stmt = $u->userdetail($userloginid);

// $stmt may be a PDOStatement object or array. Normalize:
$user = null;
if (is_object($stmt) && method_exists($stmt, 'fetch')) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif (is_array($stmt)) {
    // If your method returned an array of rows, try first row or associative row
    $user = count($stmt) ? $stmt[0] : null;
} else {
    $user = false;
}

if (!$user) {
    // If not found, show clear message (you can change to redirect)
    die("❌ No user record found for ID: " . htmlspecialchars($userloginid));
}







// Save session id for later pages
$_SESSION["userid"] = $user['id'];

// Basic user vars
$search = $_GET['search'] ?? '';
$name = $user['name'] ?? "Unknown";
$email = $user['email'] ?? "";
$type = $user['type'] ?? "";
$userPhotoFile = !empty($user['photo']) ? basename($user['photo']) : '';
$uploadsDir = "uploads/";           // public uploads folder
$userPhoto = $userPhotoFile && file_exists(__DIR__ . "/$uploadsDir" . $userPhotoFile)
    ? $uploadsDir . rawurlencode($userPhotoFile)
    : "assets/no-image.png";

// Helper to normalize results returned by your data class methods.
// Accepts PDOStatement, array, or traversable and returns array of assoc rows.
function normalizeResult($res) {
    if (is_array($res)) return $res;
    if (is_object($res) && method_exists($res, 'fetchAll')) {
        try {
            $rows = $res->fetchAll(PDO::FETCH_ASSOC);
            return $rows ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
    return [];
}

// Helper: get book row by id or name (uses available methods in data class).
function findBookRow($u, $key) {
    // key might be numeric id like '1254' or bookname 'C Programing'
    // Try by id first if numeric-like
    if (is_numeric($key)) {
        if (method_exists($u, 'getbookdetailById')) {
            $row = $u->getbookdetailById($key);
            if ($row) return $row;
        }
    }

    // Try by name
    if (method_exists($u, 'getbookdetailByName')) {
        $row = $u->getbookdetailByName($key);
        if ($row) return $row;
    }

    // As fallback: try general book() list and search
    if (method_exists($u, 'getbook')) {
        $all = $u->getbook();
        $allRows = normalizeResult($all);
        foreach ($allRows as $r) {
            if ((isset($r['id']) && (string)$r['id'] === (string)$key) ||
                (isset($r['bookname']) && strcasecmp($r['bookname'], $key) === 0)) {
                return $r;
            }
        }
    }

    return null;
}

// Get fines for user
$fines = [];
if (method_exists($u, 'getUserFines')) {
    $fines = $u->getUserFines($userloginid);
    if (!is_array($fines)) {
        // If method returned PDOStatement, normalize
        $fines = normalizeResult($fines);
    }
}

// Get available books for request (books with bookava != 0)
$availableBooks = [];
if (method_exists($u, 'getbookissue')) {
    $availableBooks = $u->getbookissue();
    $availableBooks = normalizeResult($availableBooks);
}

// Get issued books for this user (for the issue report)
$issuedBooksStmt = null;
$issuedBooks = [];
if (method_exists($u, 'getissuebook')) {
    $issuedBooksStmt = $u->getissuebook($userloginid);
    // your method returns a PDOStatement at the end; normalize it
    $issuedBooks = normalizeResult($issuedBooksStmt);
}

// fallback placeholder for images
$fallback = "assets/no-image.png";

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>User Dashboard</title>
  <link rel="icon" href="assets/logo.png?v=2">
  <style>
   :root {
  --primary: #002b5b;
  --secondary: #e3e8f0;
  --accent: #4fc3f7;
  --text-dark: #0a2342;
  --card-bg: #ffffff;
}

/* ===== RESET ===== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", Arial, sans-serif;
}

body {
  background: linear-gradient(145deg, #f7f9fc, var(--secondary));
  color: var(--text-dark);
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  line-height: 1.6;
}

/* ===== TOPBAR ===== */
.topbar {
  background: var(--card-bg);
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 18px;
  border-bottom: 2px solid var(--primary);
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.topbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.topbar-left img {
  height: 42px;
}

.topbar-left .title {
  font-weight: 700;
  color: var(--primary);
  font-size: 1.2rem;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 14px;
}

.topbar-right a {
  color: var(--text-dark);
  text-decoration: none;
  font-weight: 500;
}

.topbar-right a:hover {
  color: var(--accent);
}

.logout-btn {
  background-color: #e74c3c;
  color: #fff;
  border: none;
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.3s;
}

.logout-btn:hover {
  background-color: #c0392b;
}

/* ===== DASHBOARD CONTAINER ===== */
.dashboard-container {
  flex: 1;
  display: flex;
  overflow: hidden;
}

/* ===== SIDEBAR ===== */
.sidebar {
  width: 220px;
  background: var(--primary);
  color: #fff;
  padding: 20px 0;
  display: flex;
  flex-direction: column;
}

.sidebar ul {
  list-style: none;
  padding: 0;
}

/* Sidebar buttons like primary button */
/* Sidebar items as buttons, text left-aligned */
/* Sidebar buttons */
.sidebar ul li {
  background: var(--primary);   /* dark blue background */
  color: #fff;                  /* white text by default */
  margin-bottom: 10px;
  border-radius: 12px;
  padding: 12px 20px;
  text-align: left;
  font-weight: 600;
  transition: all 0.3s ease;
  cursor: pointer;
}

.sidebar ul li:hover,
.sidebar ul li.active {
  background: #fff;             /* white background on hover/active */
  color: var(--primary);        /* dark blue text */
}


/* ===== MAIN AREA ===== */
.rightinnerdiv {
  flex: 1;
  padding: 30px;
  overflow-y: auto;
}

/* ===== CARD STYLING ===== */
.card, .form-card {
  background: var(--card-bg);
  border-radius: 14px;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
  padding: 24px;
  margin-bottom: 24px;
  transition: all 0.3s ease;
}

.card:hover, .form-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

/* ===== PROFILE ===== */
.profile-row {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 20px;
}

.profile-photo {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--accent);
}

.user-info h2 {
  margin-bottom: 6px;
  color: var(--primary);
}

/* ===== TABLES ===== */
.styled-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 14px;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.styled-table th {
  background-color: var(--primary);
  color: #fff;
  padding: 12px 14px;
  text-transform: uppercase;
  text-align: left;
}

.styled-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #eee;
  background-color: var(--card-bg);
}

.styled-table tr:nth-child(even) td {
  background-color: var(--secondary);
}

.styled-table tr:hover td {
  background-color: var(--accent);
  color: var(--primary);
}

/* ===== BUTTONS ===== */
.btn-primary {
  background-color: var(--primary);
  color: #fff;
  padding: 10px 16px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background-color: var(--accent);
  color: var(--primary);
}

.btn-secondary {
  background-color: var(--accent);
  color: var(--primary);
  padding: 10px 16px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background-color: var(--primary);
  color: #fff;
}

.btn-feedback {
  background-color: var(--primary);
  color: #fff;
  padding: 12px 20px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.3s ease;
}

.btn-feedback:hover {
  background-color: var(--accent);
  color: var(--primary);
  transform: scale(1.05);
}

/* ===== BOOK THUMB ===== */
img.book-thumb {
  width: 70px;
  height: 70px;
  object-fit: cover;
  border-radius: 8px;
}

/* ===== DASHBOARD TITLE ===== */
.dashboard-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 20px;
  text-align: center;
}

/* ===== FEEDBACK BOX ===== */
.feedback-box {
  background: var(--card-bg);
  border-radius: 15px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.1);
  max-width: 650px;
  margin: 40px auto;
  padding: 25px 30px;
  border: 1px solid #ddd;
}

.feedback-header {
  text-align: center;
  font-size: 1.4rem;
  font-weight: 600;
  color: var(--primary);
  margin-bottom: 20px;
}

/* ===== FORMS ===== */
.feedback-form .form-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 15px;
}

.feedback-form label {
  font-weight: 600;
  color: var(--primary);
  margin-bottom: 6px;
}

.feedback-form select,
.feedback-form textarea,
.feedback-form input[type="file"] {
  width: 100%;
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 0.95rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.feedback-form select:focus,
.feedback-form textarea:focus,
.feedback-form input[type="file"]:focus {
  border-color: var(--accent);
  box-shadow: 0 0 5px var(--accent);
  outline: none;
}

.feedback-form textarea {
  resize: vertical;
}
.profile-container {
  position: relative;
  display: inline-block;
  cursor: pointer;
}

.profile-pic {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--accent);
  transition: 0.3s;
}

/* Dropdown menu */
.dropdown {
  display: none;
  position: absolute;
  right: 0;
  top: 50px;
  background: #fff;
  min-width: 200px;
  padding: 12px;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.15);
  z-index: 100;
  font-size: 0.9rem;
  color: var(--text-dark);
}

.dropdown hr {
  border: 0;
  border-top: 1px solid #ddd;
  margin: 8px 0;
}

/* Show dropdown */
.profile-container.active .dropdown {
  display: block;
}

.dropdown a.btn-primary {
  display: block;
  padding: 8px 0;
  margin-bottom: 6px;
  border-radius: 8px;
  text-decoration: none;
  text-align: center;
}
.btn-logout {
  background-color: #e74c3c; /* red */
  color: #fff;
  padding: 8px 0;
  border-radius: 8px;
  text-decoration: none;
  text-align: center;
  display: block;
  font-weight: 600;
  transition: 0.3s;
}

.btn-logout:hover {
  background-color: #c0392b; /* darker red on hover */
  color: #fff;
}
/* Logout item in sidebar */
.sidebar ul li.logout-item {
  /* background: #e74c3c;   red background */
  color: #fff;            /* white text */
  cursor: pointer;
}

/* Hover effect like other sidebar items */
.sidebar ul li.logout-item:hover {
  background:  #e74c3c;     /* white on hover */
  color:#fff;   /* red text on hover */
}

/* Optional: active effect */
.sidebar ul li.logout-item.active {
  background: #fff;
  color: #e74c3c;
}

.branch-section {
  margin-bottom: 40px;
  background-color: #ffffff;
  padding: 20px;
  border-radius: 15px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.branch-title {
  color: #003366;
  font-size: 1.4rem;
  font-weight: bold;
  margin-bottom: 15px;
  border-left: 6px solid #FFD700;
  padding-left: 10px;
}

.styled-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.95rem;
  border-radius: 12px;
  overflow: hidden;
}

.styled-table th {
  background-color: #003366;
  color: #ffffff;
  text-align: left;
  padding: 10px;
}

.styled-table td {
  padding: 10px;
  border-bottom: 1px solid #dddddd;
}

.styled-table tr:hover {
  background-color: #f8f3ce;
}

.book-thumb {
  width: 60px;
  height: 80px;
  border-radius: 8px;
  object-fit: cover;
}

.btn-primary {
  background-color:  #003366 ;
  color: #fff;
  font-weight: bold;
  border: none;
  border-radius: 10px;
  padding: 6px 12px;
  cursor: pointer;
  transition: 0.3s;
  text-decoration: none;
}

.btn-primary:hover {
  background-color: #2169c7ff;
  color: #000000ff;
}
.btn-disabled {
  background-color: #ccc;
  color: #666;
  border: none;
  padding: 5px 10px;
  border-radius: 5px;
  cursor: not-allowed;
}


.search-bar {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 20px;
  gap: 10px;
}
.search-bar input {
  width: 300px;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
}
.search-bar button {
  padding: 8px 14px;
  border: none;
  background-color: #003366;
  color: white;
  border-radius: 6px;
  cursor: pointer;
}
.search-bar button:hover {
  background-color: #FFD700;
  color: black;
}


/* ===== RESPONSIVE ===== */
@media(max-width:900px){
  .sidebar{display:none;}
  .rightinnerdiv{padding:18px;}
  .profile-row{flex-direction:column;align-items:center;}
  .profile-photo{width:100px;height:100px;}
}


  </style>
</head>
<body>

<div class="topbar">
  <div class="topbar-left">
    <img src="assets/logo.png" alt="logo">
    <div class="title">User Dashboard</div>
  </div>

  <div class="topbar-right">
    <a href="index.html">Home</a>

    <div class="profile-container">
      <img src="<?php echo htmlspecialchars($userPhoto); ?>" id="profilePic" class="profile-pic" alt="Profile">
      
      <div class="dropdown" id="dropdownMenu">
        <div style="text-align:center; margin-bottom:8px;">
          <img src="<?php echo htmlspecialchars($userPhoto); ?>" 
               style="width:70%; height:70%; object-fit:cover; border-radius:50%;">
        </div>
        <p><b>Name:</b> <?php echo htmlspecialchars($name); ?></p>
        <p><b>Email:</b> <?php echo htmlspecialchars($email); ?></p>
        <p><b>Type:</b> <?php echo htmlspecialchars(ucfirst($type)); ?></p>
        <hr>
        <a href="logout.php" class="btn-logout">Logout</a>

      </div>
    </div>

  </div>
</div>


  <div class="dashboard-container">
    <aside class="sidebar">
      <ul>
        <li data-section="myaccount" class="active">My Account</li>
        <li data-section="requestbook">Request Book</li>
        <li data-section="issuereport">Book Report</li>
        <li data-section="fines">My Fines</li>
        <li data-section="feedback">Feedback</li>
        <li class="logout-item">Logout</li>
      </ul>
    </aside>

    <main class="rightinnerdiv">
    <!-- MY ACCOUNT -->
<section id="myaccount" class="card">
  <div class="profile-row">
    <img class="profile-photo" src="<?php echo htmlspecialchars($userPhoto); ?>" alt="User Photo">
    <div class="user-info">
      <h2><?php echo htmlspecialchars($name); ?></h2>
      <div><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></div>
      <div style="margin-top:8px"><strong>Account Type:</strong> <?php echo htmlspecialchars(ucfirst($type)); ?></div>
    </div>
  </div>

  <h3 style="margin-top:25px;color:#003366;">Account Details</h3>
  <table class="styled-table">
    <tr>
      <th>Field</th>
      <th>Value</th>
    </tr>
    <?php 
    foreach ($user as $key => $value): 
        if (in_array($key, ['photo', 'pass'])) continue; // Skip photo & password
        if ($value === null || $value === '') $value = '-';
    ?>
      <tr>
        <td><?php echo ucwords(str_replace('_', ' ', htmlspecialchars($key))); ?></td>
        <td><?php echo htmlspecialchars($value); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</section>


      <!-- REQUEST BOOK -->
    <section id="requestbook" class="card" style="display:none">
  <h3>Request Book</h3>

  <!-- Search Bar -->
  <div class="search-bar">
    <form method="GET" action="" style="display:flex; gap:10px; margin-bottom:20px;">
      <input type="hidden" name="section" value="requestbook">
      <input 
        type="text" 
        name="search" 
        placeholder="Search by book name, author, or ID..." 
        value="<?php echo htmlspecialchars($search); ?>"
        style="flex:1; padding:10px; border:2px solid var(--primary); border-radius:8px; font-size:1rem;">
      <button type="submit" class="btn-primary" style="padding:10px 20px;">
        🔍 Search
      </button>
      <?php if (!empty($search)): ?>
        <a href="?section=requestbook" class="btn-secondary" style="padding:10px 20px; text-decoration:none; display:inline-block;">
          Clear
        </a>
      <?php endif; ?>
    </form>
  </div>

  <?php 
  // Get books based on search
  if (!empty($search)) {
      $availableBooks = $u->searchAvailableBooks($search);
  } else {
      $availableBooks = $u->getbookissue();
      $availableBooks = normalizeResult($availableBooks);
  }
  
  if (count($availableBooks) === 0): ?>
    <p style="text-align:center; color:#666; padding:20px;">
      <?php echo !empty($search) ? 'No books found matching your search.' : 'No available books right now.'; ?>
    </p>
  <?php else: ?>
    <?php
    // Group books by branch
    $booksByBranch = [];
    foreach ($availableBooks as $row) {
        $branch = $row['branch'] ?? ($row[6] ?? 'Unknown');
        $booksByBranch[$branch][] = $row;
    }
    ?>

    <?php if (!empty($search)): ?>
      <p style="color:var(--primary); font-weight:600; margin-bottom:15px;">
        Found <?php echo count($availableBooks); ?> book(s) matching "<?php echo htmlspecialchars($search); ?>"
      </p>
    <?php endif; ?>

    <?php foreach ($booksByBranch as $branch => $books): ?>
      <div class="branch-section">
        <h4 class="branch-title"><?php echo htmlspecialchars(strtoupper($branch)); ?></h4>
        <table class="styled-table">
          <tr>
            <th>Image</th>
            <th>Book ID</th>
            <th>Book Name</th>
            <th>Details</th>
            <th>Author</th>
            <th>Available</th>
            <th>Action</th>
          </tr>

          <?php foreach ($books as $row): 
              $bookId = $row['id'] ?? ($row[0] ?? null);
              $bookPic = $row['bookpic'] ?? ($row[1] ?? '');
              $bookName = $row['bookname'] ?? ($row[2] ?? 'Unknown');
              $bookDetail = $row['bookdetail'] ?? ($row[3] ?? '');
              $bookAuthor = $row['bookauthor'] ?? ($row[4] ?? '');
              $bookAvailable = $row['bookava'] ?? ($row[9] ?? '');
              
              $picFile = basename($bookPic);
              $serverPath = __DIR__ . "/$uploadsDir" . $picFile;
              $publicUrl = file_exists($serverPath) ? $uploadsDir . rawurlencode($picFile) : $fallback;
          ?>
          <tr>
            <td><img src="<?php echo htmlspecialchars($publicUrl); ?>" class="book-thumb" alt="pic"></td>
            <td><strong><?php echo htmlspecialchars($bookId); ?></strong></td>
            <td><?php echo htmlspecialchars($bookName); ?></td>
            <td><?php echo htmlspecialchars($bookDetail); ?></td>
            <td><?php echo htmlspecialchars($bookAuthor); ?></td>
            <td><?php echo htmlspecialchars($bookAvailable); ?></td>
            <td>
              <?php if ($bookId && $bookAvailable > 0): ?>
                <a class="btn-primary" href="requestbook.php?bookid=<?php echo urlencode($bookId); ?>&userid=<?php echo urlencode($userloginid); ?>">Request</a>
              <?php else: ?>
                <button class="btn-disabled" onclick="alert('❌ This book is currently unavailable.')">Unavailable</button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </table>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<!-- ISSUE REPORT -->
<section id="issuereport" class="form-card" style="display:none;">
  <h2 class="dashboard-title">Book Issue Report</h2>

  <?php if (count($issuedBooks) === 0): ?>
    <p style="text-align:center; color:var(--primary); font-weight:500;">
      No issued books found.
    </p>
  <?php else: ?>
    <table class="styled-table">
      <tr>
        <th>Book Photo</th>
        <th>Book Name</th>
        <th>Issue Date</th>
        <th>Return Day Left</th>
        <th>Fine</th>
        
      </tr>

      <?php foreach ($issuedBooks as $row):
          $issueId = $row['id'] ?? ($row[0] ?? null);
          $issueBookVal = $row['issuebook'] ?? ($row[3] ?? null);
          $issueDate = $row['issuedate'] ?? ($row['issue_date'] ?? '');
          $issueDays = (int)($row['issuedays'] ?? 0);
          $fine = $row['fine'] ?? 0;
          $returned = $row['returned'] ?? 0; // assuming 0 = not returned, 1 = returned

          // find book data
          $bookData = findBookRow($u, $issueBookVal);
          $bookPic = $bookData['bookpic'] ?? '';
          $bookName = $bookData['bookname'] ?? $issueBookVal;
          $picFile = basename($bookPic);
          $serverPath = __DIR__ . "/$uploadsDir" . $picFile;
          $publicUrl = (file_exists($serverPath) && !empty($bookPic))
                        ? $uploadsDir . rawurlencode($picFile)
                        : "N/A";

          // calculate days left
          $daysLeftText = "N/A";
          if (!empty($issueDate) && $issueDate !== '0000-00-00') {
              try {
                  $issueDT = new DateTime($issueDate);
                  $dueDT = clone $issueDT;
                  $dueDT->modify("+{$issueDays} days");
                  $today = new DateTime();
                  $diff = (int)$today->diff($dueDT)->format('%r%a');
                  $daysLeftText = $diff >= 0 ? "{$diff} days left" : "Overdue by " . abs($diff) . " days";
              } catch (Exception $e) {
                  $daysLeftText = "Invalid date";
              }
          }
      ?>
      <tr>
        <td>
          <?php if ($publicUrl !== "N/A"): ?>
            <img src="<?php echo htmlspecialchars($publicUrl); ?>" class="book-thumb" alt="book" width="60" height="60" style="object-fit:cover;border-radius:8px;">
          <?php else: ?>
            N/A
          <?php endif; ?>
        </td>

        <td><?php echo htmlspecialchars($bookName); ?></td>
        <td><?php echo htmlspecialchars($issueDate); ?></td>
        <td><?php echo htmlspecialchars($daysLeftText); ?></td>
        <td>₹<?php echo htmlspecialchars($fine); ?></td>

       
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</section>
<!-- FINES -->
<section id="fines" class="card" style="display:none">
  <h3>My Fines</h3>

  <?php if (empty($fines)): ?>
    <p>No fines found.</p>
  <?php else: ?>
    <table class="styled-table">
      <tr>
        <th>Book</th>
        <th>Reason</th>
        <th>Amount (₹)</th>
        <th>Status</th>
        <th>Pay</th>
      </tr>

      <?php foreach ($fines as $fine): 
          $bookName = $fine['bookname'] ?? 'N/A';
          $reason = $fine['reason'] ?? 'N/A';
          $amount = $fine['amount'] ?? 0;
          $status = $fine['status'] ?? '';
      ?>
      <tr>
        <td><?php echo htmlspecialchars($bookName); ?></td>
        <td><?php echo htmlspecialchars($reason); ?></td>
        <td>₹<?php echo htmlspecialchars($amount); ?></td>
        <td><?php echo htmlspecialchars($status); ?></td>
        <td>
          <a href="pay_fine.php?fineid=<?= (int)$fine['id']; ?>" class="btn-primary" style="display:block; text-align:center;">Pay</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</section>

 
<!-- FEEDBACK / COMPLAINT / QUERY -->
<section id="feedback" class="feedback-box" style="display:none;">
  <h2 class="feedback-header">Feedback / Complaint / Query</h2>

  <form action="feedback_submit.php" method="post" enctype="multipart/form-data" class="feedback-form">
    <input type="hidden" name="userid" value="<?php echo htmlspecialchars($userloginid); ?>">

    <div class="form-group">
      <label for="type">Type</label>
      <select id="type" name="type" required>
        <option value="feedback">Feedback</option>
        <option value="complaint">Complaint</option>
        <option value="query">Query</option>
      </select>
    </div>

    <div class="form-group">
      <label for="message">Message</label>
      <textarea id="message" name="message" rows="4" placeholder="Write your feedback here..." required></textarea>
    </div>

    <div class="form-group">
      <label for="image">Attach Image (optional)</label>
      <input id="image" type="file" name="image" accept="image/*">
    </div>

    <div class="form-actions">
      
      <button type="submit" class="btn-feedback">Submit</button>
    </div>
  </form>
</section>

    </main>
  </div>

<script>
  
const profileContainer = document.querySelector('.profile-container');
const profilePic = document.getElementById('profilePic');

profilePic.addEventListener('click', () => {
  profileContainer.classList.toggle('active');
});

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
  if (!profileContainer.contains(e.target)) {
    profileContainer.classList.remove('active');
  }
});


  // small tab switching
  (function(){
    const lis = document.querySelectorAll('.sidebar li');
    const sections = document.querySelectorAll('main section');

    function show(id){
      sections.forEach(s => s.style.display = (s.id === id ? 'block' : 'none'));
      lis.forEach(li => li.classList.toggle('active', li.getAttribute('data-section') === id));
    }

    lis.forEach(li => {
      li.addEventListener('click', () => {
        const id = li.getAttribute('data-section');
        show(id);
      });
    });

    // load section param if provided
    const urlParams = new URLSearchParams(window.location.search);
    const sec = urlParams.get('section');
    if (sec) {
      show(sec);
    } else {
      show('myaccount');
    }
  })();

  document.querySelector('.sidebar ul li.logout-item').addEventListener('click', function() {
  window.location.href = 'logout.php';
});

</script>

</body>
</html>
