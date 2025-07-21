<?php
// Function to read questions
function get_questions() {
    $questions_path = 'app/data/questions.json';
    if (!file_exists($questions_path)) {
        return []; // Return empty if not configured yet
    }
    $json = file_get_contents($questions_path);
    return json_decode($json, true);
}

$questions = get_questions();

// Pass questions to the template
require 'app/templates/main.php';
