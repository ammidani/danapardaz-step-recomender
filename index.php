<?php
require_once 'app/core/functions.php';

$db = Database::getInstance()->getConnection();

// Fetch questions from the database
$questions_result = $db->query("SELECT * FROM questions ORDER BY sort_order ASC");
$questions = [];
while($row = $questions_result->fetch_assoc()) {
    // Convert comma-separated options to an array
    $row['options'] = array_map('trim', explode(',', $row['options']));
    $questions[] = $row;
}

require 'app/templates/public/main.php';
