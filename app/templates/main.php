<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ابزار پیشنهاد محصولات داناپرداز</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="dp-container">
        <h1>ابزار هوشمند پیشنهاد محصولات داناپرداز</h1>
        <p>با پاسخ به چند سوال کوتاه، بهترین راهکار را برای کسب و کار خود پیدا کنید.</p>

        <div id="dp-wizard">
            <!-- Steps will be injected here by JavaScript -->
        </div>

        <div id="dp-result" style="display: none;">
            <h3>پیشنهاد هوشمند ما برای شما:</h3>
            <p id="dp-ai-response">در حال پردازش...</p>
        </div>

        <div id="dp-lead-form" style="display: none;">
            <hr style="margin: 30px 0;">
            <h3>برای دریافت مشاوره رایگان و مشاهده دمو، اطلاعات خود را وارد کنید:</h3>
            <div class="form-group">
                <input type="text" id="dp-name" class="dp-input" placeholder="نام و نام خانوادگی" required>
            </div>
            <div class="form-group">
                <input type="email" id="dp-email" class="dp-input" placeholder="ایمیل" required>
            </div>
            <div class="form-group">
                <input type="tel" id="dp-phone" class="dp-input" placeholder="شماره تماس (اختیاری)">
            </div>
            <button id="dp-submit-lead" class="dp-button">ارسال و دریافت مشاوره</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wizard = document.getElementById('dp-wizard');
            const resultDiv = document.getElementById('dp-result');
            const leadForm = document.getElementById('dp-lead-form');
            const aiResponseP = document.getElementById('dp-ai-response');
            const submitLeadBtn = document.getElementById('dp-submit-lead');

            let currentStep = 0;
            const userAnswers = {};
            const steps = <?php echo json_encode($questions, JSON_UNESCAPED_UNICODE); ?>;

            function showStep(stepIndex) {
                if (!steps || steps.length === 0) {
                    wizard.innerHTML = '<p>متاسفانه ابزار در حال حاضر پیکربندی نشده است. لطفا با مدیر سایت تماس بگیرید.</p>';
                    return;
                }
                const step = steps[stepIndex];
                let optionsHtml = '';
                step.options.forEach(option => {
                    optionsHtml += `
                        <label>
                            <input type="radio" name="step${stepIndex}" value="${option}" data-key="${step.key}">
                            <span>${option}</span>
                        </label>
                    `;
                });

                wizard.innerHTML = `
                    <div class="dp-step active">
                        <h3>${step.question}</h3>
                        <div class="dp-options">${optionsHtml}</div>
                        <button class="dp-button" onclick="nextStep(${stepIndex})">${stepIndex === steps.length - 1 ? 'نمایش پیشنهاد' : 'مرحله بعد'}</button>
                    </div>
                `;
            }

            window.nextStep = function(stepIndex) {
                const selectedOption = document.querySelector(`input[name="step${stepIndex}"]:checked`);
                if (!selectedOption) {
                    alert('لطفا یک گزینه را انتخاب کنید.');
                    return;
                }
                userAnswers[selectedOption.dataset.key] = selectedOption.value;

                currentStep++;
                if (currentStep < steps.length) {
                    showStep(currentStep);
                } else {
                    wizard.style.display = 'none';
                    resultDiv.style.display = 'block';
                    fetchAIResponse();
                }
            }

            async function fetchAIResponse() {
                try {
                    const response = await fetch('api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ answers: userAnswers })
                    });
                    if (!response.ok) {
                        throw new Error('Server error');
                    }
                    const data = await response.json();
                    aiResponseP.innerText = data.response;
                    leadForm.style.display = 'block';
                } catch (error) {
                    console.error('Fetch AI Error:', error);
                    aiResponseP.innerText = 'خطا در ارتباط با سرویس. لطفا بعدا تلاش کنید.';
                }
            }

            submitLeadBtn.addEventListener('click', async function() {
                const leadData = {
                    name: document.getElementById('dp-name').value,
                    email: document.getElementById('dp-email').value,
                    phone: document.getElementById('dp-phone').value,
                    needs: userAnswers
                };

                if (!leadData.name || !leadData.email) {
                    alert('لطفا نام و ایمیل را وارد کنید.');
                    return;
                }

                try {
                    const response = await fetch('save_lead.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(leadData)
                    });
                    if (!response.ok) throw new Error('Server error');

                    const result = await response.json();
                    if(result.success) {
                        leadForm.innerHTML = '<h3>از اعتماد شما سپاسگزاریم!</h3><p>کارشناسان ما به زودی با شما تماس خواهند گرفت.</p>';
                    } else {
                        throw new Error('Failed to save lead');
                    }

                } catch (error) {
                    console.error('Submit Lead Error:', error);
                    alert('خطا در ارسال اطلاعات. لطفا دوباره تلاش کنید.');
                }
            });

            showStep(currentStep);
        });
    </script>
</body>
</html>
