<?php
header('Content-Type: application/json');

$leads_path = 'app/data/leads.json';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['name']) || !isset($input['email']) || !isset($input['needs'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input.']);
    exit;
}

// Read existing leads
$leads = [];
if (file_exists($leads_path)) {
    $json = file_get_contents($leads_path);
    $leads = json_decode($json, true);
}

// Add new lead
$leads[] = [
    'name' => $input['name'],
    'email' => $input['email'],
    'phone' => $input['phone'] ?? '',
    'needs' => $input['needs'],
    'timestamp' => date('Y-m-d H:i:s')
];

// Save leads
$json = json_encode($leads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if (file_put_contents($leads_path, $json)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save lead.']);
}
