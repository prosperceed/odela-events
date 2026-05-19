<?php
session_start();
include '../config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get applicant ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: applicants.php?error=Invalid applicant ID");
    exit();
}

// Delete the applicant
$stmt = $conn->prepare("DELETE FROM applicants WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: applicants.php?success=Applicant deleted successfully");
} else {
    header("Location: applicants.php?error=Failed to delete applicant");
}

$stmt->close();
$conn->close();
?>