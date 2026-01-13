<?php
session_start();
require_once 'app/core/functions.php';

if (!is_logged_in()) {
    redirect('admin.php');
}

if (!isset($_GET['id'])) {
    redirect('admin.php?page=leads');
}

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("SELECT * FROM leads WHERE id = ?");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$lead = $stmt->get_result()->fetch_assoc();

if (!$lead) {
    redirect('admin.php?page=leads');
}

require_once 'app/templates/header.php';
?>

<div id="dp-admin-container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>جزئیات سرنخ: <?php echo htmlspecialchars($lead['name']); ?></h1>
        <a href="admin.php?page=leads" class="dp-button">بازگشت به لیست</a>
    </div>

    <h3>اطلاعات تماس</h3>
    <p><strong>نام:</strong> <?php echo htmlspecialchars($lead['name']); ?></p>
    <p><strong>ایمیل:</strong> <?php echo htmlspecialchars($lead['email']); ?></p>
    <p><strong>تلفن:</strong> <?php echo htmlspecialchars($lead['phone']); ?></p>
    <p><strong>تاریخ ثبت:</strong> <?php echo date('Y-m-d H:i', strtotime($lead['created_at'])); ?></p>

    <hr>

    <h3>پاسخ های کاربر</h3>
    <pre style="background: #f4f4f4; padding: 15px; border-radius: 5px;"><?php echo htmlspecialchars(json_encode(json_decode($lead['needs_data']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>

    <hr>

    <h3>تحلیل هوش مصنوعی</h3>
    <div style="background: #f4f4f4; padding: 15px; border-radius: 5px; white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($lead['ai_response'])); ?></div>

</div>

<?php require_once 'app/templates/footer.php'; ?>
