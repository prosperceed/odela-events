<?php
session_start();

// Destroy the session
session_destroy();

// Redirect to applicant login
header("Location: applicant-login.php");
exit();
?>
