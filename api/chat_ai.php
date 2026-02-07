<?php
include '../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Not logged in']));
}

$user_id = $_SESSION['user_id'];
$resume_id = isset($_POST['resume_id']) ? (int)$_POST['resume_id'] : null;
$question = isset($_POST['question']) ? $_POST['question'] : '';

if (!$resume_id || !$question) {
    die(json_encode(['error' => 'Missing parameters']));
}

// Get resume
$result = $conn->query("SELECT ai_resume FROM resumes WHERE id = $resume_id AND user_id = $user_id");
if ($result->num_rows === 0) {
    die(json_encode(['error' => 'Resume not found']));
}

$resume = $result->fetch_assoc()['ai_resume'];

include '../.env.php';

// Call OpenAI API for chat
$prompt = "You are a professional resume writer helping a candidate improve their resume.

Resume content:
$resume

User question: $question

Provide a helpful, professional response. Keep it concise and actionable.";

$ch = curl_init("https://api.openai.com/v1/chat/completions");

// Choose model from config or fallback
$model = defined('OPENAI_MODEL') ? OPENAI_MODEL : 'gpt-3.5-turbo';

$payload = [
    'model' => $model,
    'messages' => [
        ['role' => 'user', 'content' => $prompt]
    ]
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

if ($http_code !== 200) {
    // Try to parse error and fallback to local suggestions if quota issue
    $err = json_decode($response, true);
    $quotaError = false;
    if (is_array($err) && isset($err['error']['code'])) {
        $code = $err['error']['code'];
        if ($code === 'insufficient_quota' || stripos($code, 'quota') !== false) $quotaError = true;
    }
    if (!$quotaError && is_array($err) && isset($err['error']['message'])) {
        $msg = $err['error']['message'];
        if (stripos($msg, 'quota') !== false || stripos($msg, 'insufficient') !== false) $quotaError = true;
    }

    if ($quotaError) {
        // Local fallback: provide simple suggestions based on resume and question
        $fallback = local_chat_suggestion($resume, $question);
        echo json_encode(['answer' => $fallback, 'source' => 'local_fallback']);
        exit;
    }

    die(json_encode(['error' => 'API Error']));
}

$result = json_decode($response, true);
$answer = $result['choices'][0]['message']['content'] ?? 'No response';

echo json_encode(['answer' => $answer]);
 

// Local fallback function to generate a concise suggestion when OpenAI is unavailable
function local_chat_suggestion($resume, $question) {
    $question = strtolower($question);
    // Simple heuristics
    if (strpos($question, 'experience') !== false) {
        return "Suggestion: Break your experience into short bullet points starting with strong action verbs (Led, Managed, Developed). Add measurable outcomes where possible, e.g., 'Reduced costs by 20%'.";
    }
    if (strpos($question, 'summary') !== false || strpos($question, 'objective') !== false) {
        return "Suggestion: Start with your title and years of experience, then 2–3 key achievements or skills. Example: 'Senior Backend Developer with 6+ years experience in PHP and MySQL, improved API performance by 40%.'";
    }
    if (strpos($question, 'skills') !== false) {
        return "Suggestion: List technical skills as a short, comma-separated list. Prioritize job-relevant skills and group them: 'Languages: PHP, Python; Databases: MySQL, PostgreSQL; Tools: Docker, Git.'";
    }
    if (strpos($question, 'projects') !== false) {
        return "Suggestion: For each project, include role, tech stack, and 1-2 bullets highlighting impact (metrics if possible). Keep it concise.";
    }

    // Generic fallback
    return "Suggestion: Use active verbs, quantify achievements where possible, and keep bullets concise. If you can, add specific results (%, $ or time saved).";
}
?>
