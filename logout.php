<?php
session_start();
session_unset();
session_destroy();

// Redirect to index.html (outside this folder)
header("Location: ../index.html");
exit();
?>
