<?php
include_once("data_class.php");

$login_email = $_GET['login_email'] ?? null;
$login_pasword = $_GET['login_pasword'] ?? null;

if ($login_email && $login_pasword) {
    $u = new data();
    $u->setconnection();
    $u->adminLogin($login_email, $login_pasword);
} else {
    echo "<p style='color:red;'>Please enter Admin ID / Email and Password</p>";
}
