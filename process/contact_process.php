<?php
include '../config/db.php';

// Validate that POST data is received
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../contact.php?error=Invalid request method");
    exit();
}

// Get and sanitize input
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
$errors = [];

if (empty($fullname) || strlen($fullname) < 3) {
    $errors[] = "Full name must be at least 3 characters";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address";
}

if (empty($subject) || strlen($subject) < 3) {
    $errors[] = "Subject must be at least 3 characters";
}

if (empty($message) || strlen($message) < 10) {
    $errors[] = "Message must be at least 10 characters";
}

// If there are errors, redirect back with error message
if (!empty($errors)) {
    $error_msg = urlencode(implode(", ", $errors));
    header("Location: ../contact.php?error=$error_msg");
    exit();
}

// Escape strings for database
$fullname = htmlspecialchars($fullname);
$email = htmlspecialchars($email);
$subject = htmlspecialchars($subject);
$message = htmlspecialchars($message);

// Insert into database
$stmt = $conn->prepare("INSERT INTO messages(fullname, email, subject, message) VALUES(?, ?, ?, ?)");

if (!$stmt) {
    header("Location: ../contact.php?error=Database error");
    exit();
}

$stmt->bind_param("ssss", $fullname, $email, $subject, $message);

if ($stmt->execute()) {
    header("Location: ../contact.php?success=1");
    exit();
} else {
    header("Location: ../contact.php?error=Failed to send message. Please try again.");
    exit();
}

$stmt->close();
$conn->close();
?>