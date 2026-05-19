<?php
include '../config/db.php';


// Validate that POST data is received
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../signup.php?error=Invalid request method");
    exit();
}

// Get and sanitize input
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$category = trim($_POST['category'] ?? '');
$package = trim($_POST['package'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
$errors = [];

if (empty($fullname) || strlen($fullname) < 3) {
    $errors[] = "Full name must be at least 3 characters";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email address";
}


if (empty($category)) {
    $errors[] = "Category is required";
}

if (empty($package)) {
    $errors[] = "Package is required";
}

if (empty($password) || strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

// Check if email already exists
$check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_email->bind_param("s", $email);
$check_email->execute();
$check_email->store_result();
if ($check_email->num_rows > 0) {
    $errors[] = "Email already registered";
}
$check_email->close();

// If there are errors, redirect back with error message
if (!empty($errors)) {
    $error_msg = urlencode(implode(", ", $errors));
    header("Location: ../signup.php?error=$error_msg");
    exit();
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Insert into database
$stmt = $conn->prepare("INSERT INTO users(fullname, email, category, package, password) VALUES(?, ?, ?, ?, ?)");

if (!$stmt) {
    header("Location: ../signup.php?error=Database error");
    exit();
}

$stmt->bind_param("sssss", $fullname, $email, $category, $package, $hashed_password);

if ($stmt->execute()) {
    header("Location: ../signup.php?success=1");
    exit();
} else {
    header("Location: ../signup.php?error=Failed to submit application. Please try again.");
    exit();
}

$stmt->close();
$conn->close();
?>