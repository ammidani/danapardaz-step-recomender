<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="dp-admin-container">
        <div id="dp-admin-dashboard">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h1>پنل مدیریت</h1>
                <a href="admin.php?logout=true" class="dp-button" style="background: #6c757d;">خروج</a>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="admin.php" method="post">
                <h2>تنظیمات API</h2>
                <div class="form-group">
                    <label for="api-provider">سرویس دهنده:</label>
                    <select id="api-provider" name="api_provider" class="dp-select">
                        <option value="gemini" <?php echo ($config['api_settings']['provider'] === 'gemini') ? 'selected' : ''; ?>>Gemini</option>
                        <option value="qwen" <?php echo ($config['api_settings']['provider'] === 'qwen') ? 'selected' : ''; ?>>Qwen</option>
                        <option value="openrouter" <?php echo ($config['api_settings']['provider'] === 'openrouter') ? 'selected' : ''; ?>>OpenRouter</option>
                        <option value="custom" <?php echo ($config['api_settings']['provider'] === 'custom') ? 'selected' : ''; ?>>Custom</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="api-key">API Key:</label>
                    <input type="password" id="api-key" name="api_key" class="dp-input" value="<?php echo htmlspecialchars($config['api_settings']['api_key']); ?>">
                </div>
                <div class="form-group">
                    <label for="api-url">API URL (برای Custom):</label>
                    <input type="text" id="api-url" name="api_url" class="dp-input" value="<?php echo htmlspecialchars($config['api_settings']['api_url']); ?>">
                </div>

                <h2>قالب پرامپت</h2>
                <div class="form-group">
                    <textarea name="prompt_template" rows="10" class="dp-input"><?php echo htmlspecialchars($config['prompt_template']); ?></textarea>
                </div>

                <h2>سوالات ویزارد</h2>
                <div id="questions-container">
                    <?php foreach ($questions as $index => $q): ?>
                        <div class="question-group" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 8px;">
                            <div class="form-group">
                                <label>سوال:</label>
                                <input type="text" name="questions[<?php echo $index; ?>][question]" class="dp-input" value="<?php echo htmlspecialchars($q['question']); ?>">
                            </div>
                            <div class="form-group">
                                <label>گزینه ها (جدا شده با کاما):</label>
                                <input type="text" name="questions[<?php echo $index; ?>][options]" class="dp-input" value="<?php echo htmlspecialchars(implode(',', $q['options'])); ?>">
                            </div>
                             <div class="form-group">
                                <label>کلید (Key):</label>
                                <input type="text" name="questions[<?php echo $index; ?>][key]" class="dp-input" value="<?php echo htmlspecialchars($q['key']); ?>">
                            </div>
                            <button type="button" class="dp-button" style="background: var(--error-color);" onclick="removeQuestion(this)">حذف سوال</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="dp-button" onclick="addQuestion()">افزودن سوال جدید</button>

                <hr style="margin: 30px 0;">

                <button type="submit" name="save_settings" class="dp-button">ذخیره تغییرات</button>
            </form>
        </div>
    </div>

    <script>
        function addQuestion() {
            const container = document.getElementById('questions-container');
            const index = container.children.length;
            const newQuestion = document.createElement('div');
            newQuestion.className = 'question-group';
            newQuestion.style = "border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 8px;";
            newQuestion.innerHTML = `
                <div class="form-group">
                    <label>سوال:</label>
                    <input type="text" name="questions[${index}][question]" class="dp-input">
                </div>
                <div class="form-group">
                    <label>گزینه ها (جدا شده با کاما):</label>
                    <input type="text" name="questions[${index}][options]" class="dp-input">
                </div>
                <div class="form-group">
                    <label>کلید (Key):</label>
                    <input type="text" name="questions[${index}][key]" class="dp-input">
                </div>
                <button type="button" class="dp-button" style="background: var(--error-color);" onclick="removeQuestion(this)">حذف سوال</button>
            `;
            container.appendChild(newQuestion);
        }

        function removeQuestion(button) {
            button.parentElement.remove();
        }
    </script>
</body>
</html>
