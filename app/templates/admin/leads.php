<?php
// This file is included from admin.php, so DB connection is available.
global $db;

$search_term = $_GET['search'] ?? '';
$page_num = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$per_page = 20;
$offset = ($page_num - 1) * $per_page;

// Get leads with search and pagination
$sql = "SELECT * FROM leads";
$count_sql = "SELECT COUNT(id) as total FROM leads";
$params = [];

if (!empty($search_term)) {
    $sql .= " WHERE name LIKE ? OR email LIKE ?";
    $count_sql .= " WHERE name LIKE ? OR email LIKE ?";
    $like_term = "%{$search_term}%";
    $params = [$like_term, $like_term];
}

$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params_with_pagination = array_merge($params, [$per_page, $offset]);

$stmt = $db->prepare($sql);
$stmt->bind_param(str_repeat('s', count($params)) . 'ii', ...$params_with_pagination);
$stmt->execute();
$leads_result = $stmt->get_result();

// Get total count for pagination
$stmt = $db->prepare($count_sql);
if (!empty($params)) $stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$total_rows = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $per_page);

require_once __DIR__ . '/../header.php';
?>

<div id="dp-admin-container">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1>مدیریت سرنخ ها</h1>
        <a href="admin.php?logout=true" class="dp-button" style="background: #6c757d;">خروج</a>
    </div>

    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <form method="get">
            <input type="hidden" name="page" value="leads">
            <input type="text" name="search" class="dp-input" placeholder="جستجو بر اساس نام یا ایمیل..." value="<?php echo htmlspecialchars($search_term); ?>" style="width: auto; display: inline-block;">
            <button type="submit" class="dp-button">جستجو</button>
        </form>
        <a href="export_leads.php" class="dp-button">خروجی CSV</a>
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="border: 1px solid #ddd; padding: 8px;">نام</th>
                <th style="border: 1px solid #ddd; padding: 8px;">ایمیل</th>
                <th style="border: 1px solid #ddd; padding: 8px;">تاریخ</th>
                <th style="border: 1px solid #ddd; padding: 8px;">عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($lead = $leads_result->fetch_assoc()): ?>
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($lead['name']); ?></td>
                    <td style="border: 1px solid #ddd; padding: 8px;"><?php echo htmlspecialchars($lead['email']); ?></td>
                    <td style="border: 1px solid #ddd; padding: 8px;"><?php echo date('Y-m-d H:i', strtotime($lead['created_at'])); ?></td>
                    <td style="border: 1px solid #ddd; padding: 8px;">
                        <a href="view_lead.php?id=<?php echo $lead['id']; ?>" class="dp-button">مشاهده</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div style="margin-top: 20px;">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=leads&p=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>" class="dp-button <?php echo $i == $page_num ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
