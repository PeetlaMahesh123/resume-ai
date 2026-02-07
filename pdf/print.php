<?php
include '../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$resume_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$resume_id) {
    die("Resume not found");
}

// Get resume
$result = $conn->query("SELECT * FROM resumes WHERE id = $resume_id AND user_id = $user_id");
if ($result->num_rows === 0) {
    die("Resume not found");
}

$resume = $result->fetch_assoc();
$ai_resume = $resume['ai_resume'];
$ats_score = $resume['ats_score'];

function render_resume_html_for_print($text) {
    $html = '';
    $text = str_replace(["\r", "\\r", "\\n", "\\t"], ["", "", "\n", " "], $text);
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
        
        // Major section headings
        if (preg_match('/^(summary|professional summary|objective|skills|experience|internship|projects|certifications|education|contact|profile)\s*[:\-]?$/i', $trim)) {
            if ($in_list) { $html .= "</ul>\n"; $in_list = false; }
            $section_name = strtoupper(preg_replace('/[:\-]/', '', $trim));
            $html .= '<h2 style="font-size: 1.05rem; font-weight: 700; margin-top: 12px; margin-bottom: 6px; padding-bottom: 4px; border-bottom: 1px solid #333;">' . htmlspecialchars($section_name) . "</h2>\n";
            continue;
        }
        
        // Contact info
        if (preg_match('/[\w.-]+@[\w.-]+\.\w+/', $trim) || preg_match('/\|.*\|/', $trim)) {
            if ($in_list) { $html .= "</ul>\n"; $in_list = false; }
            $html .= '<p style="text-align: center; font-size: 0.9rem; margin: 2px 0;">' . htmlspecialchars($trim) . "</p>\n";
            continue;
        }
        
        // List items
        if (preg_match('/^[-•\*]\s+/', $trim)) {
            if (!$in_list) { $html .= "<ul style='margin: 6px 0 6px 20px; padding: 0; list-style-type: disc;'>\n"; $in_list = true; }
            $item = preg_replace('/^[-•\*]\s+/', '', $trim);
            $html .= '<li style="margin: 3px 0;">' . htmlspecialchars($item) . "</li>\n";
            continue;
        }
        
        // Job titles/dates
        if (preg_match('/[–\-|].*\d{4}|\b(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/', $trim)) {
            if ($in_list) { $html .= "</ul>\n"; $in_list = false; }
            $html .= '<p style="font-weight: 600; margin: 8px 0 3px 0;">' . htmlspecialchars($trim) . "</p>\n";
            continue;
        }
        
        // Education info
        if (preg_match('/\b(B\.?E|B\.?A|B\.?S|M\.?S|M\.?A|M\.?Tech|B\.?Tech|PhD|Intermediate|Secondary|High School)/i', $trim)) {
            if ($in_list) { $html .= "</ul>\n"; $in_list = false; }
            $html .= '<p style="font-weight: 600; margin: 6px 0 2px 0;">' . htmlspecialchars($trim) . "</p>\n";
            continue;
        }
        
        // Subheadings
        if (preg_match('/^([A-Za-z0-9 &\-]{2,50})\s*:\s*(.*)$/', $trim, $m)) {
            if ($in_list) { $html .= "</ul>\n"; $in_list = false; }
            $html .= '<p style="margin: 4px 0;"><strong>' . htmlspecialchars(ucwords($m[1])) . ":</strong> " . htmlspecialchars($m[2]) . "</p>\n";
            continue;
        }
        
        // Regular text
        if (strlen($trim) > 100) {
            if (!$in_list) { $html .= "<ul style='margin: 6px 0 6px 20px; padding: 0; list-style-type: disc;'>\n"; $in_list = true; }
            $html .= '<li style="margin: 3px 0;">' . htmlspecialchars($trim) . "</li>\n";
        } else {
            if ($in_list) { $html .= "</ul>\n"; $in_list = false; }
            $html .= '<p style="margin: 4px 0;">' . htmlspecialchars($trim) . "</p>\n";
        }
    }
    if ($in_list) $html .= "</ul>\n";
    return $html;
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Resume</title>
    <style>
        @page { size: A4; margin: 20mm; }
        body { font-family: Arial, sans-serif; color: #111; }
        h1, h2, h3 { margin: 6px 0; }
        p { margin: 6px 0; }
        ul { margin: 6px 0 12px 20px; }
        .ats { font-size: 12px; color: #666; margin-top: 18px; }
        .container { max-width: 800px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Resume</h1>
        <hr>
        <div>
            <?php echo render_resume_html_for_print($ai_resume); ?>
        </div>
        <p class="ats">ATS Score: <?php echo htmlspecialchars($ats_score); ?>/100</p>
    </div>

    <script>
        // Auto-trigger print dialog; user can Save as PDF in browser
        window.onload = function() {
            setTimeout(function() { window.print(); }, 300);
        };
    </script>
</body>
</html>
