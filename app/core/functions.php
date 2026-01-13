<?php
require_once __DIR__ . '/Database.php';

function is_logged_in() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function get_setting($key) {
    $db = Database::getInstance();
    $result = $db->query("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return null;
}

function update_setting($key, $value) {
    $db = Database::getInstance();
    $db->query("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
}
