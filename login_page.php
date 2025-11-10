<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Arivu - Login</title>
  <link rel="icon" href="assets/logo.png" />

  <style>
    :root {
      --primary: #f4f6fa;
      --secondary: #1e293b;
      --accent: #3b82f6;
      --text-light: #ffffff;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    .hidden {
      display: none;
    }

    body {
      font-family: "Inter", sans-serif;
      background-color: var(--primary);
      color: var(--secondary);
      overflow-x: hidden;
      line-height: 1.6;
      min-height: 100vh;
    }

    /* ---------- Header ---------- */
    header {
      position: fixed;
      top: 0;
      width: 100%;
      background-color: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(8px);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.5rem 1rem;
      z-index: 1000;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .logo img {
      height: 40px;
      width: 40px;
    }

    .logo span {
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--secondary);
    }

    nav {
      display: flex;
      align-items: center;
    }

    nav ul {
      list-style: none;
      display: flex;
      align-items: center;
      gap: 25px;
    }

    nav ul li a {
      text-decoration: none;
      font-weight: 600;
      color: var(--secondary);
      transition: color 0.3s;
    }

    nav ul li a:hover {
      color: var(--accent);
    }

    /* ---------- Hamburger ---------- */
    .menu-toggle {
      display: none;
      flex-direction: column;
      cursor: pointer;
      gap: 4px;
    }

    .bar {
      width: 25px;
      height: 3px;
      background-color: var(--secondary);
      border-radius: 3px;
      transition: all 0.3s ease;
    }

   @media (max-width: 768px) {
  nav ul {
    position: absolute;
    top: 60px;
    left: 0;
    width: 100%;
    background-color: rgba(255, 255, 255, 0.97);
    backdrop-filter: blur(8px);
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    gap: 20px;
    padding: 20px 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-100%);
    opacity: 0;
    pointer-events: none; /* Prevent clicks when hidden */
    transition: all 0.3s ease;
    z-index: 2000; /* make sure it's above everything */
  }

  nav ul.active {
    transform: translateY(0);
    opacity: 1;
    pointer-events: auto; /* enable clicks when shown */
  }

  .menu-toggle {
    display: flex;
    position: relative;
    z-index: 3000; /* ensure button is clickable */
  }
}



    /* ---------- Login Form ---------- */
    .login-section {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding-top: 80px;
background: linear-gradient(to bottom right, #e6edf7, #ffffff);

    }

    .login-container {
      background: white;
      padding: 40px 30px;
      border-radius: 16px;
      width: 350px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      animation: fadeIn 0.6s ease forwards;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .login-container h2 {
      text-align: center;
      margin-bottom: 20px;
      color: var(--secondary);
    }

    .tab-container {
      display: flex;
      justify-content: space-around;
      margin-bottom: 25px;
      border-bottom: 2px solid #e5e7eb;
    }

    .tab {
      flex: 1;
      text-align: center;
      padding: 10px 0;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      color: var(--secondary);
      position: relative;
    }

    .tab.active {
      color: var(--accent);
    }

    .tab.active::after {
      content: "";
      position: absolute;
      bottom: -2px;
      left: 0;
      width: 100%;
      height: 3px;
      background: var(--accent);
      border-radius: 2px;
      animation: slideLine 0.3s ease forwards;
    }

    @keyframes slideLine {
      from {
        width: 0;
      }

      to {
        width: 100%;
      }
    }

    form {
      display: none;
      opacity: 0;
      transform: translateY(10px);
      transition: opacity 0.5s ease, transform 0.4s ease;
      text-align: center;
    }

    form.active {
      display: block;
      opacity: 1;
      transform: translateY(0);
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      outline: none;
      font-size: 14px;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
      border-color: var(--accent);
      box-shadow: 0 0 6px rgba(59, 130, 246, 0.4);
      outline: none;
      transition: all 0.3s ease;
    }

    .btnSubmit {
      width: 100%;
      padding: 12px;
      background-color: var(--accent);
      color: var(--text-light);
      font-weight: 600;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      margin-top: 10px;
      transition: all 0.3s ease;
    }

    .btnSubmit:hover {
      background-color: #2563eb;
      transform: translateY(-2px);
    }

    .login-container p {
      margin-top: 15px;
      font-size: 14px;
      text-align: center;
    }

    .login-container a {
      color: var(--accent);
      font-weight: 600;
      text-decoration: none;
    }

    .login-container a:hover {
      text-decoration: underline;
    }

    .back-btn {
      position: fixed;
      bottom: 20px;
      left: 20px;
      padding: 10px 20px;
      background-color: var(--secondary);
      color: var(--text-light);
      border: none;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s, transform 0.3s;
    }
    .show-password-container {
  display: flex;
  align-items: center;
  justify-content: flex-start; /* moves to left side */
  gap: 6px;
  margin-top: 6px;
  font-size: 13px;
}


    .back-btn:hover {
      background-color: var(--accent);
      transform: translateY(-2px);
    }

    @media (max-width: 768px) {
      .login-container {
        width: 75%;
        padding: 10px 12px;
      }
    }
  </style>
</head>

<body>
  <!-- Header -->
  <header>
    <div class="logo">
      <img src="assets/logo.png" alt="Arivu Logo" />
      <span>Arivu</span>
    </div>

    <nav>
      <ul>
        <li><a href="index.html#hero">Home</a></li>
        <li><a href="index.html#about">About</a></li>
        <li><a href="index.html#features">Features</a></li>
        <li><a href="index.html#contact">Contact</a></li>
      </ul>
    </nav>

    <div class="menu-toggle" id="menu-toggle">
      <div class="bar"></div>
      <div class="bar"></div>
      <div class="bar"></div>
    </div>
  </header>

  <!-- Login Section -->
  <section class="login-section">
    <div class="login-container">
      <h2>Login</h2>

      <div class="tab-container">
        <div id="student-tab" class="tab active" onclick="showForm('student')">Student / Faculty</div>
        <div id="admin-tab" class="tab" onclick="showForm('admin')">Admin</div>
      </div>

      <form id="student-form" class="active" action="login_server_page.php" method="get">
        <input type="text" class="form-control" name="login_email" placeholder="User ID / Email" required>
        <input type="password" class="form-control" name="login_pasword" placeholder="Password" required>
        <div class="show-password-container">
  <input class="show-password" type="checkbox" id="showPassStudent">
  <label for="showPassStudent">Show password</label>
</div>

        <input type="submit" class="btnSubmit" value="Login">
      </form>

      <form id="admin-form" class="hidden" action="loginadmin_server_page.php" method="get">
        <input type="text" class="form-control" name="login_email" placeholder="Admin ID / Email" required>
        <input type="password" class="form-control" name="login_pasword" placeholder="Password" required>
        <div class="show-password-container">
  <input class="show-password" type="checkbox" id="showPassAdmin">
  <label for="showPassAdmin">Show password</label>
</div>

        <input type="submit" class="btnSubmit" value="Login">
      </form>

      <p>Don’t have an account? <a href="contact.html">Contact Us</a></p>
    </div>
  </section>

  <button class="back-btn" onclick="goBack()">Back</button>
<script>
  function showForm(role) {
    const studentTab = document.getElementById("student-tab");
    const adminTab = document.getElementById("admin-tab");
    const studentForm = document.getElementById("student-form");
    const adminForm = document.getElementById("admin-form");

    if (role === "student") {
      studentTab.classList.add("active");
      adminTab.classList.remove("active");
      studentForm.classList.add("active");
      adminForm.classList.remove("active");
      adminForm.classList.add("hidden");
      studentForm.classList.remove("hidden");
    } else {
      adminTab.classList.add("active");
      studentTab.classList.remove("active");
      adminForm.classList.add("active");
      studentForm.classList.remove("active");
      studentForm.classList.add("hidden");
      adminForm.classList.remove("hidden");
    }
  }

  function goBack() {
    window.history.back();
  }

  document.addEventListener("DOMContentLoaded", () => {
    // Show/Hide password toggle
    const checkboxes = document.querySelectorAll(".show-password");
    checkboxes.forEach(cb => {
      cb.addEventListener("change", () => {
        const password = cb.closest("form").querySelector('input[type="password"]');
        if (password) password.type = cb.checked ? "text" : "password";
      });
    });

    // 🔧 FIXED: Menu toggle code
    const menuToggle = document.getElementById("menu-toggle");
    const navMenu = document.querySelector("nav ul");
    const navLinks = document.querySelectorAll("nav ul li a");

    // Toggle open/close
    menuToggle.addEventListener("click", () => {
      navMenu.classList.toggle("active");
    });

    // Close when clicking a nav link
    navLinks.forEach(link => {
      link.addEventListener("click", () => {
        navMenu.classList.remove("active");
      });
    });

    // Close when clicking outside
    document.addEventListener("click", (e) => {
      if (!e.target.closest("nav") && !e.target.closest(".menu-toggle")) {
        navMenu.classList.remove("active");
      }
    });
  });
</script>


</body>
</html>
