<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

$config_file = 'app/core/config.php';
$style_css = 'style.css'; // Assuming style.css is in the same directory as setup.php

// --- STAGE MANAGEMENT ---
$stage = $_GET['stage'] ?? 1;

// --- HTML & CSS FOR WIZARD ---
function render_header($title) {
    global $style_css;
    echo <<<HTML
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap">
    <link rel="stylesheet" href="$style_css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        #setup-container { max-width: 600px; width: 100%; }
        .log { background-color: #222; color: #0f0; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 14px; direction: ltr; text-align: left; margin-top: 15px; }
        .error { color: #f00; }
    </style>
</head>
<body>
<div id="setup-container" class="dp-container">
    <h1>نصب و راه اندازی</h1>
HTML;
}

function render_footer() {
    echo "</div></body></html>";
}

function render_message($message, $type = 'success') {
    echo "<div class='alert alert-$type'>$message</div>";
}

// --- STAGE 1: REQUIREMENTS CHECK ---
function stage1() {
    render_header("مرحله 1: بررسی پیش نیازها");

    $php_version_ok = version_compare(PHP_VERSION, '7.4.0', '>=');
    $mysqli_ok = extension_loaded('mysqli');
    $config_writable = !file_exists($GLOBALS['config_file']) || is_writable($GLOBALS['config_file']);

    echo "<h2>بررسی پیش نیازهای سرور</h2>";
    echo "<ul>";
    echo "<li>PHP Version >= 7.4.0: " . ($php_version_ok ? "✔️" : "❌") . "</li>";
    echo "<li>MySQLi Extension: " . ($mysqli_ok ? "✔️" : "❌") . "</li>";
    echo "<li>Config File Writable: " . ($config_writable ? "✔️" : "❌") . "</li>";
    echo "</ul>";

    if ($php_version_ok && $mysqli_ok && $config_writable) {
        echo '<a href="?stage=2" class="dp-button">شروع مرحله بعد</a>';
    } else {
        render_message("لطفا پیش نیازهای سرور را برآورده کرده و دوباره تلاش کنید.", "danger");
    }

    render_footer();
}

// --- STAGE 2: DATABASE CONFIGURATION ---
function stage2() {
    render_header("مرحله 2: تنظیمات دیتابیس");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION['db_host'] = $_POST['db_host'];
        $_SESSION['db_user'] = $_POST['db_user'];
        $_SESSION['db_pass'] = $_POST['db_pass'];
        $_SESSION['db_name'] = $_POST['db_name'];

        @$mysqli = new mysqli($_POST['db_host'], $_POST['db_user'], $_POST['db_pass'], $_POST['db_name']);

        if ($mysqli->connect_error) {
            render_message("خطا در اتصال به دیتابیس: " . $mysqli->connect_error, "danger");
        } else {
            $mysqli->close();
            header('Location: ?stage=3');
            exit;
        }
    }

    echo <<<HTML
    <h2>تنظیمات اتصال به دیتابیس</h2>
    <form method="post">
        <div class="form-group">
            <label>میزبان دیتابیس (Host):</label>
            <input type="text" name="db_host" class="dp-input" value="localhost" required>
        </div>
        <div class="form-group">
            <label>نام کاربری دیتابیس:</label>
            <input type="text" name="db_user" class="dp-input" required>
        </div>
        <div class="form-group">
            <label>رمز عبور دیتابیس:</label>
            <input type="password" name="db_pass" class="dp-input">
        </div>
        <div class="form-group">
            <label>نام دیتابیس:</label>
            <input type="text" name="db_name" class="dp-input" required>
        </div>
        <button type="submit" class="dp-button">تست و ادامه</button>
    </form>
HTML;

    render_footer();
}

// --- STAGE 3: TABLE CREATION & ADMIN SETUP ---
function stage3() {
    render_header("مرحله 3: ساخت جداول و ادمین");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Create config file
        $config_content = "<?php\n\n";
        $config_content .= "define('DB_HOST', '" . $_SESSION['db_host'] . "');\n";
        $config_content .= "define('DB_USER', '" . $_SESSION['db_user'] . "');\n";
        $config_content .= "define('DB_PASS', '" . $_SESSION['db_pass'] . "');\n";
        $config_content .= "define('DB_NAME', '" . $_SESSION['db_name'] . "');\n";

        if (file_put_contents($GLOBALS['config_file'], $config_content) === false) {
             render_message("خطا در ساخت فایل کانفیگ.", "danger");
             render_footer();
             exit;
        }

        require_once $GLOBALS['config_file'];
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($mysqli->connect_error) {
            die("Failed to connect to DB after config write.");
        }

        // Create tables SQL
        $sql = file_get_contents('app/core/schema.sql'); // We will create this file

        echo "<div class='log'>";
        if ($mysqli->multi_query($sql)) {
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
                echo "Executing query... OK\n";
            } while ($mysqli->next_result());
             echo "Tables created successfully.\n";
        } else {
            echo "<div class='error'>Error creating tables: " . $mysqli->error . "</div>";
            echo "</div>";
            render_footer();
            exit;
        }

        // Create Admin User
        $admin_user = $_POST['admin_user'];
        $admin_email = $_POST['admin_email'];
        $admin_pass = password_hash($_POST['admin_pass'], PASSWORD_DEFAULT);

        $stmt = $mysqli->prepare("INSERT INTO users (username, password, email, is_admin) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $admin_user, $admin_pass, $admin_email);
        if ($stmt->execute()) {
            echo "Admin user created successfully.\n";
        } else {
            echo "<div class='error'>Error creating admin user: " . $stmt->error . "</div>";
        }
        $stmt->close();
        echo "</div>";

        // Finalize
        session_destroy();
        echo '<h2>نصب با موفقیت انجام شد!</h2>';
        echo '<p>فایل <strong>setup.php</strong> به دلایل امنیتی باید حذف شود.</p>';
        echo '<a href="index.php" class="dp-button">رفتن به صفحه اصلی</a>';

    } else {
        echo <<<HTML
        <h2>ساخت حساب کاربری ادمین</h2>
        <form method="post">
            <div class="form-group">
                <label>نام کاربری ادمین:</label>
                <input type="text" name="admin_user" class="dp-input" required>
            </div>
            <div class="form-group">
                <label>ایمیل ادمین:</label>
                <input type="email" name="admin_email" class="dp-input" required>
            </div>
            <div class="form-group">
                <label>رمز عبور ادمین:</label>
                <input type="password" name="admin_pass" class="dp-input" required>
            </div>
            <button type="submit" class="dp-button">نصب نهایی</button>
        </form>
HTML;
    }

    render_footer();
}


// --- ROUTER ---
if (file_exists($config_file)) {
    render_header("خطا");
    render_message("به نظر می رسد این برنامه قبلا نصب شده است. برای نصب مجدد، لطفا فایل <code>app/core/config.php</code> را حذف کنید.", "danger");
    render_footer();
    exit;
}

switch ($stage) {
    case 2:
        stage2();
        break;
    case 3:
        stage3();
        break;
    default:
        stage1();
        break;
}
?>
