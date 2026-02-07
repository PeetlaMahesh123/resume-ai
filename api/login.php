<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Find user
    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    
    if ($result->num_rows === 0) {
        die("Invalid email or password");
    }

    $user = $result->fetch_assoc();

    // Verify password
    if (!password_verify($password, $user['password'])) {
        die("Invalid email or password");
    }

    // Login successful
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    header("Location: ../dashboard.php");
}
?>
