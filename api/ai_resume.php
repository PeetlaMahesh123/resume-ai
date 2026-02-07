<?php
include '../db.php';
include '../.env.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];

// Get form data
$data = $_POST;

// Build AI prompt
$prompt = "You are an expert professional resume writer. Improve the following resume for ATS compatibility and professional presentation.

Input Information:
Name: {$data['name']}
Email: {$data['email']}
Phone: {$data['phone']}
Location: {$data['location']}
Target Role: {$data['job_role']}

Summary: {$data['summary']}
Skills: {$data['skills']}
Experience: {$data['experience']}
Projects: {$data['projects']}
Education: {$data['education']}

INSTRUCTIONS - CRITICAL:
1. Format as a professional resume with the following EXACT section structure:
   - Name and Contact (email, phone, location)
   - Professional Summary or Objective (2-3 impactful sentences)
   - Skills: (organized by category if applicable, comma-separated)
   - Internship/Experience: (if applicable, with dates and achievements)
   - Projects: (if applicable, with technologies used)
   - Certifications: (if applicable)
   - Education: (degrees, institution, graduation date, GPA if applicable)
2. Use action verbs (Led, Designed, Developed, Implemented, etc.)
3. Include metrics and numbers (achieved 30% improvement, built 5 projects, etc.)
4. Use bullet points (- prefix) for all list items under sections
5. Keep it ATS-friendly: no tables, no special unicode characters
6. Target 1 page length (concise but comprehensive)
7. Make section headers clear and uppercase (e.g., SKILLS:, EXPERIENCE:, PROJECTS:, EDUCATION:)

Return ONLY the formatted resume ready to display. Include all sections clearly labeled.";

// Call OpenAI API
$ch = curl_init("https://api.openai.com/v1/chat/completions");

// Choose model from config or fallback
$model = defined('OPENAI_MODEL') ? OPENAI_MODEL : 'gpt-3.5-turbo';

$payload = [
    'model' => $model,
    'messages' => [
        ['role' => 'user', 'content' => $prompt]
    ],
    'temperature' => 0.7
];

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . OPENAI_API_KEY,
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$ai_resume = null;

if ($http_code === 200) {
    $result = json_decode($response, true);
    if (isset($result['choices'][0]['message']['content'])) {
        $ai_resume = $result['choices'][0]['message']['content'];
    }
}

// Handle API errors and fallback to local processing if quota exceeded
if (empty($ai_resume)) {
    $errorPayload = json_decode($response, true);
    $quotaError = false;
    if (is_array($errorPayload) && isset($errorPayload['error']['code'])) {
        $code = $errorPayload['error']['code'];
        if ($code === 'insufficient_quota' || stripos($code, 'quota') !== false) {
            $quotaError = true;
        }
    }
    if (!$quotaError && is_array($errorPayload) && isset($errorPayload['error']['message'])) {
        $msg = $errorPayload['error']['message'];
        if (stripos($msg, 'quota') !== false || stripos($msg, 'insufficient') !== false) {
            $quotaError = true;
        }
    }

    if ($quotaError) {
        // Local fallback: simple improvements without external API
        $ai_resume = generate_local_ai_resume($data);
    } else {
        // Other API errors
        die("API Error: " . $response);
    }
}

// Calculate ATS score (simple logic)
$job_description = isset($data['job_description']) ? $data['job_description'] : '';
$ats_score = calculateATSScore($ai_resume, $data['skills'], $job_description);

// Save to database
$data['job_description'] = $job_description;
$original_data = json_encode($data);
$original_data = $conn->real_escape_string($original_data);
$ai_resume_escaped = $conn->real_escape_string($ai_resume);

$sql = "INSERT INTO resumes (user_id, resume_data, ai_resume, ats_score) 
        VALUES ($user_id, '$original_data', '$ai_resume_escaped', $ats_score)";

if ($conn->query($sql)) {
    $resume_id = $conn->insert_id;
    header("Location: ../preview.php?id=$resume_id");
} else {
    die("Database error: " . $conn->error);
}

// Function to calculate ATS score
function calculateATSScore($resume, $skills, $job_description = '') {
    // Weighted scoring (total 100)
    // Contact Info: 10
    // Summary presence: 10
    // Skills match: 40
    // Experience & metrics: 25
    // Formatting & sections: 15

    $score = 0;

    // Contact info
    $contact_points = 0;
    if (preg_match('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i', $resume)) $contact_points += 5;
    if (preg_match('/\b\+?\d[\d\s\-()]{6,}\b/', $resume)) $contact_points += 5;
    $score += $contact_points; // up to 10

    // Summary presence
    $summary_points = 0;
    if (preg_match('/(summary|professional summary|profile)[:\n]/i', $resume) || strlen($resume) > 200) {
        $summary_points = 10;
    }
    $score += $summary_points;

    // Build required keywords from job_description if present, otherwise use job role + skills
    $required = [];
    if (!empty($job_description)) {
        // extract words of length >=4 and common tech tokens
        preg_match_all('/[A-Za-z+#.]{3,}/', $job_description, $m);
        $tokens = array_map('strtolower', $m[0]);
        // remove common stopwords
        $stop = ['with','from','that','this','your','will','years','year','experience','responsibilities','including','and','or','the','a','an','in','on','for','to','of'];
        $tokens = array_values(array_filter($tokens, function($t) use ($stop) { return !in_array($t, $stop); }));
        $freq = array_count_values($tokens);
        arsort($freq);
        $required = array_slice(array_keys($freq), 0, 20);
    }

    // Add user-provided skills as required
    $skill_array = array_filter(array_map('trim', explode(',', $skills)));
    foreach ($skill_array as $s) {
        $required[] = strtolower($s);
    }

    $required = array_values(array_unique($required));
    $required = array_filter($required, function($r){ return strlen($r) >= 3; });

    // Skills match scoring (40 points)
    $matched = 0;
    $total_required = max(1, count($required));
    foreach ($required as $req) {
        if (stripos($resume, $req) !== false) $matched++;
    }
    $skills_score = ($matched / $total_required) * 40;
    $score += round($skills_score);

    // Experience & metrics scoring (25 points)
    $exp_points = 0;
    // count lines with numbers or % or $ as metrics
    preg_match_all('/\d+%|\$\d+|\d+\s+(years|year)/i', $resume, $metrics);
    $metric_count = count($metrics[0]);
    $exp_lines = preg_split('/[\r\n]+/', $resume);
    $exp_bullets = 0;
    foreach ($exp_lines as $line) if (preg_match('/^\s*[-•\*]\s*/', $line) || strlen($line) > 50) $exp_bullets++;
    $exp_points = min(15, $exp_bullets) + min(10, $metric_count * 2);
    $score += $exp_points; // up to 25

    // Formatting & sections (15 points)
    $format_points = 0;
    $sections = ['summary','skills','experience','projects','education'];
    foreach ($sections as $sec) {
        if (preg_match('/' . preg_quote($sec, '/') . '[:\n]/i', $resume)) $format_points += 3;
    }
    // cap at 15
    $score += min($format_points, 15);

    // Normalize and ensure between 0-100
    $score = max(0, min(100, round($score)));
    return $score;
}

// Local fallback resume generator (structured, ATS-friendly improvements)
function generate_local_ai_resume($data) {
    // Build a professional, ATS-friendly resume using available fields
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $location = trim($data['location'] ?? '');
    $summary = trim($data['summary'] ?? '');
    $skills = trim($data['skills'] ?? '');
    $experience = trim($data['experience'] ?? '');
    $projects = trim($data['projects'] ?? '');
    $education = trim($data['education'] ?? '');
    $job_role = trim($data['job_role'] ?? '');

    // Normalize skills to clean comma-separated list
    $skill_list = array_filter(array_map('trim', preg_split('/[,;\n]+/', $skills)));
    $skills_formatted = implode(', ', $skill_list);

    // Improve summary: ensure professional tone and proper sentence structure
    $summary = preg_replace('/\s+/', ' ', $summary);
    if (!empty($summary)) {
        $summary = ucfirst(trim($summary));
        if (substr($summary, -1) !== '.') $summary .= '.';
        // Limit to 3 sentences
        $sentences = preg_split('/(?<=[.!?])\s+/', $summary);
        $summary = implode(' ', array_slice($sentences, 0, 3));
    }

    // Process experience with bullet formatting
    $exp_lines = array_filter(array_map('trim', preg_split('/[\r\n]+/', $experience)));
    if (empty($exp_lines) && !empty($experience)) {
        $exp_lines = array_filter(array_map('trim', explode('.', $experience)));
    }

    // Ensure proper formatting and action verbs
    $action_verbs = ['Led', 'Managed', 'Designed', 'Developed', 'Implemented', 'Improved', 'Optimized', 'Created', 'Built', 'Engineered', 'Coordinated'];
    foreach ($exp_lines as &$line) {
        $line = ucfirst(trim($line));
        if (!preg_match('/^(Led|Managed|Designed|Developed|Implemented|Improved|Optimized|Created|Built|Engineered|Coordinated)\b/i', $line)
            && !preg_match('/[–|-]|(\d{4})/', $line)) {
            $verb = $action_verbs[array_rand($action_verbs)];
            $line = "$verb " . lcfirst($line);
        }
        if (substr($line, -1) !== '.') $line .= '.';
    }

    // Projects lines
    $proj_lines = array_filter(array_map('trim', preg_split('/[\r\n]+/', $projects)));
    foreach ($proj_lines as &$pl) {
        $pl = ucfirst(trim($pl));
        if (substr($pl, -1) !== '.') $pl .= '.';
    }

    // Assemble professional resume
    $out = '';
    
    // Header with name
    if ($name) {
        $out .= strtoupper($name) . "\n";
    }
    
    // Contact information
    $contact = array_filter([$email, $phone, $location]);
    if (!empty($contact)) {
        $out .= implode(' | ', $contact) . "\n\n";
    }

    // Professional title/job role
    if ($job_role) {
        $out .= strtoupper($job_role) . "\n\n";
    }

    // Summary/Objective section
    if ($summary) {
        $out .= "PROFESSIONAL SUMMARY\n";
        $out .= "$summary\n\n";
    }

    // Skills section
    if ($skills_formatted) {
        $out .= "SKILLS\n";
        $out .= "$skills_formatted\n\n";
    }

    // Experience/Internship section
    if (!empty($exp_lines)) {
        $out .= "EXPERIENCE\n";
        foreach ($exp_lines as $el) {
            $out .= "- $el\n";
        }
        $out .= "\n";
    }

    // Projects section
    if (!empty($proj_lines)) {
        $out .= "PROJECTS\n";
        foreach ($proj_lines as $pl) {
            $out .= "- $pl\n";
        }
        $out .= "\n";
    }

    // Education section
    if ($education) {
        $out .= "EDUCATION\n";
        $out .= "- " . ucfirst($education) . "\n\n";
    }

    return $out;
}
?>
