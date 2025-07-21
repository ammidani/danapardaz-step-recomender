<?php
session_start();

$config_path = 'app/data/config.json';
$questions_path = 'app/data/questions.json';

// Function to read config
function get_config() {
    global $config_path;
    if (!file_exists($config_path)) {
        die("Error: config.json not found.");
    }
    $json = file_get_contents($config_path);
    return json_decode($json, true);
}

// Function to save config
function save_config($config) {
    global $config_path;
    $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($config_path, $json);
}

// Function to read questions
function get_questions() {
    global $questions_path;
    if (!file_exists($questions_path)) {
        // Create a default questions file if it doesn't exist
        $default_questions = [
            [
                "question" => "اندازه کسب و کار شما چیست؟",
                "options" => ["کسب و کار کوچک (1-10 نفر)", "کسب و کار متوسط (11-50 نفر)", "سازمان بزرگ (بیش از 50 نفر)"],
                "key" => "businessSize"
            ]
        ];
        file_put_contents($questions_path, json_encode($default_questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $default_questions;
    }
    $json = file_get_contents($questions_path);
    return json_decode($json, true);
}

// Function to save questions
function save_questions($questions) {
    global $questions_path;
    $json = json_encode($questions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($questions_path, $json);
}


$config = get_config();
$error_message = '';
$success_message = '';

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    if ($_POST['username'] === $config['admin_user'] && password_verify($_POST['password'], $config['admin_pass'])) {
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error_message = 'نام کاربری یا رمز عبور اشتباه است.';
    }
}

// Handle Settings Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['is_admin']) && isset($_POST['save_settings'])) {
    // Save API settings
    $config['api_settings']['provider'] = $_POST['api_provider'];
    if (!empty($_POST['api_key'])) {
        $config['api_settings']['api_key'] = $_POST['api_key'];
    }
    $config['api_settings']['api_url'] = $_POST['api_url'];

    // Save prompt template
    $config['prompt_template'] = $_POST['prompt_template'];

    save_config($config);

    // Save questions
    $new_questions = [];
    if (isset($_POST['questions'])) {
        foreach ($_POST['questions'] as $q) {
            if (!empty($q['question']) && !empty($q['options']) && !empty($q['key'])) {
                $new_questions[] = [
                    'question' => $q['question'],
                    'options' => array_map('trim', explode(',', $q['options'])),
                    'key' => $q['key']
                ];
            }
        }
    }
    save_questions($new_questions);

    $success_message = 'تغییرات با موفقیت ذخیره شد.';
}


// Decide which view to show
if (isset($_SESSION['is_admin'])) {
    $questions = get_questions();
    require 'app/templates/admin_dashboard.php';
} else {
    require 'app/templates/admin_login.php';
}
