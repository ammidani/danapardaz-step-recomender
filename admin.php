<?php
session_start();
require_once 'app/core/functions.php';

$db = Database::getInstance()->getConnection();

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    redirect('admin.php');
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT password FROM users WHERE username = ? AND is_admin = 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['is_admin'] = true;
            $_SESSION['username'] = $username;
            redirect('admin.php');
        }
    }
    $error_message = 'نام کاربری یا رمز عبور اشتباه است.';
}

// Handle Settings Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_logged_in() && isset($_POST['save_settings'])) {
    // Save API settings
    update_setting('api_provider', $_POST['api_provider']);
    if (!empty($_POST['api_key'])) {
        update_setting('api_key', $_POST['api_key']);
    }
    update_setting('api_url', $_POST['api_url']);
    update_setting('prompt_template', $_POST['prompt_template']);

    // Save questions (delete all and re-insert)
    $db->query("DELETE FROM questions");
    if (isset($_POST['questions'])) {
        $stmt = $db->prepare("INSERT INTO questions (question_text, options, question_key, sort_order) VALUES (?, ?, ?, ?)");
        foreach ($_POST['questions'] as $index => $q) {
            if (!empty($q['question']) && !empty($q['options']) && !empty($q['key'])) {
                $stmt->bind_param("sssi", $q['question'], $q['options'], $q['key'], $index);
                $stmt->execute();
            }
        }
    }

    $success_message = 'تغییرات با موفقیت ذخیره شد.';
}


// Decide which view to show
if (is_logged_in()) {
    $page = $_GET['page'] ?? 'dashboard';

    if ($page === 'leads') {
        require 'app/templates/admin/leads.php';
    } else { // dashboard
        // Fetch data for dashboard
        $settings['api_provider'] = get_setting('api_provider');
        $settings['api_key'] = get_setting('api_key');
        $settings['api_url'] = get_setting('api_url');
        $settings['prompt_template'] = get_setting('prompt_template');

        $questions_result = $db->query("SELECT * FROM questions ORDER BY sort_order ASC");
        $questions = [];
        while($row = $questions_result->fetch_assoc()) {
            $questions[] = $row;
        }

        require 'app/templates/admin/dashboard.php';
    }
} else {
    require 'app/templates/admin/login.php';
}
