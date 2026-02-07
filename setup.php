<?php
// Setup and Verification Page
// This file helps verify your installation and get started

session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'resume_ai';

// Test database connection
$db_connected = false;
$db_error = '';

try {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        $db_error = $conn->connect_error;
    } else {
        $db_connected = true;
        // Check tables
        $tables_result = $conn->query("SHOW TABLES");
        $table_count = $tables_result->num_rows;
    }
} catch (Exception $e) {
    $db_error = $e->getMessage();
}

// Check if API key is set
$api_key_set = !empty(file_get_contents('.env.php')) && 
               strpos(file_get_contents('.env.php'), 'your-api-key-here') === false;

// Check files
$files_required = [
    'index.html',
    'register.html',
    'login.html',
    'resume-form.html',
    'dashboard.php',
    'preview.php',
    'db.php',
    '.env.php',
    'api/register.php',
    'api/login.php',
    'api/ai_resume.php',
    'css/style.css',
    'js/main.js'
];

$files_status = [];
foreach ($files_required as $file) {
    $files_status[$file] = file_exists($file);
}

$all_files_exist = array_reduce($files_status, function($carry, $item) {
    return $carry && $item;
}, true);

// If API key submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['api_key'])) {
    $api_key = $_POST['api_key'];
    if (!empty($api_key) && strlen($api_key) > 10) {
        $env_content = "<?php\n// 🔑 Get your API key from https://platform.openai.com/api-keys\n// ⚠️ NEVER commit this file to GitHub\n\ndefine('OPENAI_API_KEY', '$api_key');\n?>";
        file_put_contents('.env.php', $env_content);
        $api_key_set = true;
        $success_message = "✅ API Key saved successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume AI - Setup Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f0f0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; border-radius: 10px; text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 2.5rem; margin-bottom: 10px; }
        .section { background: white; padding: 25px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section h2 { color: #2c3e50; margin-bottom: 15px; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .status-row { display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8f9fa; margin-bottom: 10px; border-radius: 5px; font-weight: 500; }
        .status-row.success { background: #d4edda; color: #155724; }
        .status-row.error { background: #f8d7da; color: #721c24; }
        .status-icon { font-size: 1.3rem; font-weight: bold; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #2c3e50; font-weight: 500; }
        .form-group input[type="text"], .form-group input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 1rem; font-family: monospace; }
        .form-group input:focus { outline: none; border-color: #3498db; box-shadow: 0 0 5px rgba(52, 152, 219, 0.3); }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; font-weight: 500; transition: all 0.3s; }
        .btn:hover { background: #2980b9; transform: translateY(-2px); }
        .success-message { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 15px; }
        .error-message { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 15px; }
        .progress-bar { width: 100%; height: 8px; background: #ddd; border-radius: 5px; margin: 20px 0; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); transition: width 0.3s; }
        .instructions { background: #ecf0f1; padding: 15px; border-left: 4px solid #3498db; border-radius: 5px; margin-top: 15px; }
        .instructions ol { margin-left: 20px; }
        .instructions li { margin-bottom: 10px; }
        .next-btn { background: #27ae60; font-size: 1.1rem; padding: 12px 30px; }
        .next-btn:hover { background: #229954; }
        .note { background: #fff3cd; padding: 12px; border-radius: 5px; margin-top: 15px; color: #856404; border-left: 4px solid #ffc107; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; color: #c7254e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Resume AI Setup</h1>
            <p>Complete Setup & Verification</p>
        </div>

        <?php $completed = 0; $total = 4; ?>
        
        <!-- Database Status -->
        <div class="section">
            <h2>✓ Step 1: Database</h2>
            <div class="status-row <?php echo $db_connected ? 'success' : 'error'; ?>">
                <div>MySQL Database: <strong><?php echo $db; ?></strong></div>
                <div class="status-icon"><?php echo $db_connected ? '✅' : '❌'; ?></div>
            </div>
            <?php if ($db_connected): ?>
                <div class="status-row success">
                    <div>Tables Created: <strong><?php echo isset($table_count) ? $table_count : 0; ?> tables</strong></div>
                    <div class="status-icon">✅</div>
                </div>
                <?php $completed++; ?>
            <?php else: ?>
                <div class="error-message">
                    <strong>Error:</strong> <?php echo htmlspecialchars($db_error); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Files Status -->
        <div class="section">
            <h2>✓ Step 2: Project Files</h2>
            <?php foreach ($files_status as $file => $exists): ?>
                <div class="status-row <?php echo $exists ? 'success' : 'error'; ?>">
                    <div><?php echo htmlspecialchars($file); ?></div>
                    <div class="status-icon"><?php echo $exists ? '✅' : '❌'; ?></div>
                </div>
            <?php endforeach; ?>
            <?php if ($all_files_exist) { $completed++; } ?>
        </div>

        <!-- API Key Setup -->
        <div class="section">
            <h2>✓ Step 3: OpenAI API Key</h2>
            <?php if ($api_key_set): ?>
                <div class="success-message">
                    ✅ API Key is configured and ready!
                </div>
                <?php $completed++; ?>
            <?php else: ?>
                <div class="error-message">
                    ⚠️ API Key not yet configured. Get it from <strong><a href="https://platform.openai.com/api-keys" target="_blank" style="color: #721c24; text-decoration: underline;">OpenAI Dashboard</a></strong>
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label for="api_key">Paste your OpenAI API Key:</label>
                        <input type="password" id="api_key" name="api_key" placeholder="sk-..." required>
                        <small style="color: #7f8c8d; display: block; margin-top: 5px;">Never share this key publicly. It will be saved locally in .env.php</small>
                    </div>
                    <button type="submit" class="btn">Save API Key</button>
                </form>
                <div class="instructions">
                    <strong>How to get your API Key:</strong>
                    <ol>
                        <li>Visit <a href="https://platform.openai.com/api-keys" target="_blank">https://platform.openai.com/api-keys</a></li>
                        <li>Sign in with your OpenAI account (create one if needed)</li>
                        <li>Click <strong>"+ Create new secret key"</strong></li>
                        <li>Copy the key and paste it above</li>
                        <li>Make sure you have <strong>API credits</strong> enabled</li>
                    </ol>
                </div>
            <?php endif; ?>
        </div>

        <!-- Final Status -->
        <div class="section">
            <h2>✓ Step 4: Ready to Launch</h2>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo ($completed / $total) * 100; ?>%"></div>
            </div>
            <p style="text-align: center; margin-bottom: 20px; font-size: 1.1rem; color: #2c3e50;">
                Setup Progress: <strong><?php echo $completed; ?>/<?php echo $total; ?></strong> ✓
            </p>

            <?php if ($completed === $total): ?>
                <div class="success-message" style="padding: 20px; text-align: center; font-size: 1.1rem;">
                    <strong>🎉 Everything is ready!</strong><br>
                    <p style="margin-top: 10px;">Your app is configured and ready to use.</p>
                </div>
                
                <div class="instructions">
                    <strong>📝 Next Steps:</strong>
                    <ol>
                        <li>Go to <a href="index.html" style="color: #0c5460; font-weight: bold;">Homepage</a></li>
                        <li>Click <strong>"Get Started Free"</strong></li>
                        <li>Register with your email</li>
                        <li>Create your first resume</li>
                        <li>Watch AI improve your content!</li>
                    </ol>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="index.html" class="btn next-btn">Go to Resume AI →</a>
                </div>
            <?php else: ?>
                <div class="note">
                    <strong>⏳ Setup not complete yet</strong><br>
                    Please complete the missing steps above. You need:
                    <?php if (!$db_connected) echo "❌ Database connection"; ?>
                    <?php if (!$all_files_exist) echo "❌ All project files"; ?>
                    <?php if (!$api_key_set) echo "❌ OpenAI API Key"; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer Info -->
        <div style="text-align: center; margin-top: 30px; color: #7f8c8d;">
            <p>Resume AI MVP v1.0 | <a href="https://openai.com" target="_blank" style="color: #3498db;">Powered by OpenAI</a></p>
        </div>
    </div>
</body>
</html>
