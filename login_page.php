<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Library Management System">

  <link rel="icon" href="assets/logo.png">
  <title>Arivu - Login</title>

  <style>
    :root {
      --topbar-bg: #ffffff;
      --footer-bg: #003366;
      --button-accent: #FFD700;
      --background: #F8F3CE;
      --text-dark: #003366;
      --text-light: #ffffff;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: var(--background);
      font-family: Arial, sans-serif;
      overflow-x: hidden;
      padding-top: 70px;
      /* Space for fixed navbar */
    }

    /* ---------- Top Bar (Same as main page) ---------- */
    header {
      position: fixed;
      top: 0;
      width: 100%;
      background-color: var(--topbar-bg);
      border-bottom: 3px solid var(--footer-bg);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
      height: 70px;
      z-index: 1000;
    }

    .logo-name {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo-name img {
      height: 45px;
    }

    .logo-name h1 {
      font-size: 2rem;
      color: var(--text-dark);
      font-family: 'Times New Roman', serif;
    }

    nav ul {
      display: flex;
      list-style: none;
      gap: 25px;
    }

    nav ul li a {
      text-decoration: none;
      font-weight: bold;
      color: var(--text-dark);
      font-size: 1rem;
      transition: color 0.3s;
    }

    nav ul li a:hover {
      color: var(--button-accent);
    }

    nav {
      display: flex;
      align-items: center;
      gap: 30px;
    }

    /* ---------- LOGIN BOX ---------- */
    .login-container {
      background: #ffffff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.15);
      width: 350px;
      text-align: center;
      margin: 40px auto;
    }

    .login-container h2 {
      margin-bottom: 15px;
      color: var(--text-dark);
    }

    /* Tabs */
    .tab-container {
      display: flex;
      justify-content: space-around;
      margin-bottom: 20px;
    }

    .tab {
      flex: 1;
      text-align: center;
      padding: 10px;
      cursor: pointer;
      border-bottom: 3px solid transparent;
      transition: 0.3s;
      color: var(--text-dark);
      font-weight: bold;
    }

    .tab.active {
      border-bottom: 3px solid var(--button-accent);
      color: #000;
    }

    /* Input fields */
    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 8px;
      outline: none;
      font-size: 14px;
    }

    /* Submit button */
    .btnSubmit {
      width: 100%;
      padding: 12px;
      background-color: var(--text-dark);
      color: var(--text-light);
      font-size: 16px;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
      margin-top: 10px;
    }

    .btnSubmit:hover {
      background-color: var(--button-accent);
      color: var(--text-dark);
      transform: scale(1.05);
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
    }

    /* Extra links */
    .login-container p {
      margin-top: 15px;
      font-size: 14px;
    }

    .login-container a {
      color: var(--text-dark);
      text-decoration: none;
      font-weight: bold;
    }

    .login-container a:hover {
      text-decoration: underline;
    }

    /* Hidden toggle */
    .hidden {
      display: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .login-container {
        width: 90%;
      }

      nav ul {
        gap: 15px;
      }
    }
  </style>
</head>

<body>

  <!-- HEADER (Same as main page) -->
  <header>
    <div class="logo-name">
      <img src="../assets/logo.png" alt="Arivu Logo">
      <h1>Arivu</h1>
    </div>
    <nav>
      <ul>
          <li><a href="index.html">Home</a></li>
          <li><a href="index.html#about">About</a></li>
          <li><a href="index.html#features">Features</a></li>
        <li><a href="contact.html">Contact</a></li>
      </ul>
    </nav>
  </header>

  <!-- LOGIN BOX -->
  <div class="container login-container">
    <div class="row">
    </div>
    <h2>Login</h2>
    <div class="tab-container">
      <div id="student-tab" class="tab active" onclick="showForm('student')">Student / Faculty</div>
      <div id="admin-tab" class="tab" onclick="showForm('admin')">Admin</div>
    </div>

    <!-- Student Form -->
<div id="student-form">
  <form action="login_server_page.php" method="get">
    <input type="text" class="form-control" name="login_email" placeholder="User ID / Email" required>
    <input type="password" class="form-control" name="login_pasword" placeholder="Password" required>
    <div style="margin-top:8px; font-size:13px; display:flex; align-items:center; gap:5px;">
      <input class="show-password" type="checkbox">
      <label>Show password</label>
    </div>
    <input type="submit" class="btnSubmit" value="Login">
  </form>
</div>

<!-- Admin Form -->
<div id="admin-form" class="hidden">
  <form action="loginadmin_server_page.php" method="get">
    <input type="text" class="form-control" name="login_email" placeholder="Admin ID / Email" required>
    <input type="password" class="form-control" name="login_pasword" placeholder="Password" required>
    <div style="margin-top:8px;font-size:13px; display:flex; align-items:center; gap:5px;">
      <input class="show-password" type="checkbox">
      <label>Show password</label>
    </div>
    <input type="submit" class="btnSubmit" value="Login">
  </form>
</div>


    <p>Don't have an account? <a href="signup.html">Sign Up</a></p>
  </div>
  <div style="position: fixed; bottom: 20px; left: 20px;">
    <button onclick="goBack()"
      style="padding: 10px 20px; background-color:#003366; color:#fff; border:none; border-radius:5px; font-weight:bold; cursor:pointer;">
      Back
    </button>
  </div>

  <script>
    function showForm(role) {
      const studentTab = document.getElementById("student-tab");
      const adminTab = document.getElementById("admin-tab");
      const studentForm = document.getElementById("student-form");
      const adminForm = document.getElementById("admin-form");

      if (role === "student") {
        studentTab.classList.add("active");
        adminTab.classList.remove("active");
        studentForm.classList.remove("hidden");
        adminForm.classList.add("hidden");
      } else {
        adminTab.classList.add("active");
        studentTab.classList.remove("active");
        adminForm.classList.remove("hidden");
        studentForm.classList.add("hidden");
      }
    }

    function goBack() {
      window.history.back(); // goes to the previous page in history
    }
// Select all checkboxes with class "show-password"
const checkboxes = document.querySelectorAll('.show-password');

checkboxes.forEach(cb => {
  cb.addEventListener('change', () => {
    // find the password field in the same form
    const password = cb.closest('form').querySelector('input[type="password"]');
    password.type = cb.checked ? 'text' : 'password';
  });
});

  </script>

</body>

</html>