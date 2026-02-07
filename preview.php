<?php
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$resume_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$resume_id) {
    header("Location: dashboard.php");
    exit();
}

// Get resume
$result = $conn->query("SELECT * FROM resumes WHERE id = $resume_id AND user_id = $user_id");
if ($result->num_rows === 0) {
    die("Resume not found");
}

$resume = $result->fetch_assoc();
$ai_resume = $resume['ai_resume'];
$ats_score = $resume['ats_score'];
$original_data = json_decode($resume['resume_data'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Preview - Resume AI</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <h1>🚀 Resume AI</h1>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="api/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container preview-page">
        <div class="preview-header">
            <h2>Resume Preview</h2>
            <div class="ats-score">
                <h3>ATS Score: <span class="score"><?php echo $ats_score; ?>/100</span></h3>
                <p>Compatibility with Applicant Tracking Systems</p>
            </div>
        </div>

        <div class="preview-controls">
            <a href="pdf/print.php?id=<?php echo $resume_id; ?>" target="_blank" class="btn btn-primary">📄 Download PDF (Print)</a>
            <a href="resume-form.html" class="btn btn-secondary">✏️ Edit</a>
        </div>

        <div class="resume-preview">
            <div id="resumeContent">
                <?php echo render_resume_html($ai_resume); ?>
            </div>
        </div>

        <div class="chat-section">
            <h3>💬 Ask AI to Improve a Section</h3>
            <form id="chatForm">
                <textarea id="question" placeholder="e.g., Improve my experience section or Make my summary stronger" rows="3" required></textarea>
                <button type="submit" class="btn btn-primary">Get AI Suggestion</button>
                <span id="chatLoading" style="display: none; margin-left: 10px;">⏳ Thinking...</span>
            </form>
            <div id="chatResponse" style="display: none; margin-top: 20px; padding: 15px; background-color: #f0f0f0; border-radius: 5px;">
                <h4>AI Response:</h4>
                <p id="responseText"></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const question = document.getElementById('question').value;
            const chatLoading = document.getElementById('chatLoading');
            const chatResponse = document.getElementById('chatResponse');
            const resumeId = <?php echo $resume_id; ?>;

            chatLoading.style.display = 'inline';

            fetch('api/chat_ai.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'resume_id=' + resumeId + '&question=' + encodeURIComponent(question)
            })
            .then(response => response.json())
            .then(data => {
                chatLoading.style.display = 'none';
                chatResponse.style.display = 'block';
                document.getElementById('responseText').textContent = data.answer || data.error;
            })
            .catch(error => {
                chatLoading.style.display = 'none';
                chatResponse.style.display = 'block';
                document.getElementById('responseText').textContent = 'Error: ' + error;
            });
        });
    </script>
</body>
</html>

<?php
// Helper: convert plain resume text to professional HTML with headings and lists
function render_resume_html($text) {
    $html = '';
    // Normalize real newlines and escaped backslash-newlines (\n) that appear in stored text
    $text = str_replace(["\r", "\\r", "\\n", "\\t"], ["", "", "\n", " "], $text);
    // Collapse multiple blank lines to max 2
    $text = preg_replace('/\n{3,}/', "\n\n", $text);

    $lines = explode("\n", $text);

    $in_list = false;
    $current_section = '';
    
    foreach ($lines as $line) {
        $trim = trim($line);
        if ($trim === '') {
            if ($in_list) { $html .= "</ul>\n"; $in_list = false; }
            $html .= "<div style='margin: 0.5rem 0;'></div>\n";
            continue;
        }

        // Major section headings (SUMMARY, SKILLS, EXPERIENCE, etc.)
        if (preg_match('/^(summary|professional summary|objective|skills|experience|internship|projects|certifications|education|contact|profile)\s*[:\-]?$/i', $trim)) {
            if ($in_list) { $html .= "</ul>\n"; $in_list = false; }
            $section_name = strtoupper(preg_replace('/[:\-]/', '', $trim));
            $current_section = strtolower($section_name);
            $html .= '<h2 class="resume-section">' . htmlspecialchars($section_name) . "</h2>\n";
            continue;
        }

        // Contact info line (email | phone | location)
        if (preg_match('/[\w.-]+@[\w.-]+\.\w+/', $trim) || preg_match('/\|.*\|/', $trim)) {
            if ($in_list) { $html .= "</ul>\n"; $in_list = false; }
            $html .= '<p class="contact-line">' . htmlspecialchars($trim) . "</p>\n";
            continue;
        }

        // If line starts with dash or bullet -> list
        if (preg_match('/^[-•\*]\s+/', $trim)) {
            if (!$in_list) { $html .= "<ul class='resume-list'>\n"; $in_list = true; }
            $item = preg_replace('/^[-•\*]\s+/', '', $trim);
            $html .= '<li>' . htmlspecialchars($item) . "</li>\n";
            continue;
        }

        // Company/Project name with dates or role (e.g., "Company Name – Role | Date")
        if (preg_match('/[–\-|].*\d{4}|\b(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/', $trim, $matches)) {
            if ($in_list) { $html .= "</ul>\n"; $in_list = false; }
            $html .= '<p class="resume-job-title">' . htmlspecialchars($trim) . "</p>\n";
            continue;
        }

        // Degree/Education line (e.g., "Bachelor of Science in Computer Science")
        if (preg_match('/\b(B\.?E|B\.?A|B\.?S|M\.?S|M\.?A|M\.?Tech|B\.?Tech|PhD|Intermediate|Secondary|High School)/i', $trim)) {
            if ($in_list) { $html .= "</ul>\n"; $in_list = false; }
            $html .= '<p class="resume-education">' . htmlspecialchars($trim) . "</p>\n";
            continue;
        }

        // Generic sub-heading with colon (e.g., "Languages: " or "Certifications:")
        if (preg_match('/^([A-Za-z0-9 &\-]{2,50})\s*:\s*(.*)$/', $trim, $m)) {
            if ($in_list) { $html .= "</ul>\n"; $in_list = false; }
            $html .= '<p class="resume-subheading"><strong>' . htmlspecialchars(ucwords($m[1])) . ":</strong> " . htmlspecialchars($m[2]) . "</p>\n";
            continue;
        }

        // Regular paragraph text
        if (strlen($trim) > 100) {
            // Long lines become list items
            if (!$in_list) { $html .= "<ul class='resume-list'>\n"; $in_list = true; }
            $html .= '<li>' . htmlspecialchars($trim) . "</li>\n";
        } else {
            if ($in_list) { $html .= "</ul>\n"; $in_list = false; }
            $html .= '<p class="resume-text">' . htmlspecialchars($trim) . "</p>\n";
        }
    }
    if ($in_list) $html .= "</ul>\n";
    return $html;
}

