<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="dp-admin-container">
        <div id="dp-admin-login">
            <h1>ورود به پنل مدیریت</h1>
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form action="admin.php" method="post">
                <div class="form-group">
                    <label for="username">نام کاربری:</label>
                    <input type="text" id="username" name="username" class="dp-input" required>
                </div>
                <div class="form-group">
                    <label for="password">رمز عبور:</label>
                    <input type="password" id="password" name="password" class="dp-input" required>
                </div>
                <button type="submit" class="dp-button">ورود</button>
            </form>
        </div>
    </div>
</body>
</html>
