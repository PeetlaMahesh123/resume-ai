<?php
include '../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

$user_id = $_SESSION['user_id'];
$resume_id = isset($_POST['resume_id']) ? (int)$_POST['resume_id'] : null;

if (!$resume_id) {
    die(json_encode(['error' => 'Resume ID required']));
}

// Verify the resume belongs to the user
$check = $conn->query("SELECT id FROM resumes WHERE id = $resume_id AND user_id = $user_id");
if ($check->num_rows === 0) {
    die(json_encode(['error' => 'Resume not found or unauthorized']));
}

// Delete the resume
$delete_sql = "DELETE FROM resumes WHERE id = $resume_id AND user_id = $user_id";
if ($conn->query($delete_sql)) {
    echo json_encode(['success' => true, 'message' => 'Resume deleted successfully']);
} else {
    echo json_encode(['error' => 'Failed to delete resume: ' . $conn->error]);
}
?>
