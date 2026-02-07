<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate
    if ($password !== $confirm_password) {
        die("Passwords don't match");
    }

    if (strlen($password) < 6) {
        die("Password must be at least 6 characters");
    }

    // Check if email exists
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        die("Email already registered");
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
    
    if ($conn->query($sql)) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_name'] = $name;
        header("Location: ../dashboard.php");
    } else {
        die("Error: " . $conn->error);
    }
}
?>
