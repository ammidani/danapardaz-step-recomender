<?php
require_once 'app/core/functions.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['name']) || !isset($input['email']) || !isset($input['needs']) || !isset($input['aiResponse'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input.']);
    exit;
}

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("INSERT INTO leads (name, email, phone, needs_data, ai_response) VALUES (?, ?, ?, ?, ?)");
$needs_json = json_encode($input['needs'], JSON_UNESCAPED_UNICODE);
$stmt->bind_param("sssss", $input['name'], $input['email'], $input['phone'], $needs_json, $input['aiResponse']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save lead.', 'details' => $stmt->error]);
}

$stmt->close();
