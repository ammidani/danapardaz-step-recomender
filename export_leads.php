<?php
session_start();
require_once 'app/core/functions.php';

if (!is_logged_in()) {
    redirect('admin.php');
}

$db = Database::getInstance()->getConnection();
$result = $db->query("SELECT * FROM leads ORDER BY created_at DESC");

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=leads.csv');

$output = fopen('php://output', 'w');

// Add BOM to fix UTF-8 in Excel
fputs($output, "\xEF\xBB\xBF");

fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Date', 'Needs', 'AI Response']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['email'],
        $row['phone'],
        $row['created_at'],
        $row['needs_data'],
        $row['ai_response']
    ]);
}

fclose($output);
exit;
