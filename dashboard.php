<?php
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info
$user_result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $user_result->fetch_assoc();

// Get all resumes for this user
$resumes_result = $conn->query("SELECT * FROM resumes WHERE user_id = $user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Resume AI</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <h1>🚀 Resume AI</h1>
            </div>
            <ul class="nav-links">
                <li>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</li>
                <li><a href="api/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Your Dashboard</h2>
        
        <a href="resume-form.html" class="btn btn-primary">+ Create New Resume</a>

        <h3>Your Resumes</h3>
        
        <?php if ($resumes_result->num_rows > 0): ?>
            <table class="resumes-table">
                <tr>
                    <th>Created</th>
                    <th>ATS Score</th>
                    <th>Actions</th>
                </tr>
                <?php while ($resume = $resumes_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($resume['created_at'])); ?></td>
                        <td><?php echo $resume['ats_score'] ?? 'N/A'; ?> / 100</td>
                        <td>
                            <a href="preview.php?id=<?php echo $resume['id']; ?>" class="btn btn-small">View</a>
                            <a href="pdf/print.php?id=<?php echo $resume['id']; ?>" target="_blank" class="btn btn-small">Download PDF</a>
                            <button class="btn btn-small btn-danger" onclick="deleteResume(<?php echo $resume['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No resumes yet. <a href="resume-form.html">Create one now!</a></p>
        <?php endif; ?>
    </div>

    <script>
        function deleteResume(resumeId) {
            if (!confirm('Are you sure you want to delete this resume? This action cannot be undone.')) {
                return;
            }

            fetch('api/delete_resume.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'resume_id=' + resumeId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Resume deleted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete resume'));
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
    </script>
</body>
</html>
