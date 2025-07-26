CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('api_provider', 'gemini'),
('api_key', ''),
('api_url', ''),
('prompt_template', 'شما یک مشاور ارشد کسب و کار و متخصص در محصولات داناپرداز هستید. وظیفه شما تحلیل نیازهای یک کسب و کار و ارائه یک راهکار جامع به اوست.\n\nاطلاعات زیر از کسب و کار مشتری در دسترس است:\n- صنعت: {{industry}}\n- اندازه تیم: {{teamSize}}\n- هدف اصلی: {{primaryGoal}}\n- چالش اصلی: {{mainChallenge}}\n- ابزارهای فعلی: {{currentTools}}\n- نیاز به یکپارچگی: {{integrationNeed}}\n\nلطفا یک تحلیل جامع و پیشنهاد راهکار در قالب ساختار زیر ارائه دهید. خروجی شما باید یک JSON باشد که کلیدهای آن شامل "problemAnalysis", "solutionRecommendation", "implementationRoadmap", "potentialROI" است.\n\n{\n  "problemAnalysis": "در این بخش، تحلیلی از وضعیت فعلی مشتری بر اساس اطلاعات ارائه شده بنویسید. به چالش اصلی او اشاره کرده و توضیح دهید که چرا ابزارهای فعلی یا روش های دستی پاسخگوی نیاز او نیستند.",\n  "solutionRecommendation": "در این بخش، محصول اصلی داناپرداز که راهکار اصلی است را معرفی کنید (مثلا CRM یا Service Desk). سپس ماژول های جانبی که به حل کامل مشکل کمک می کنند را پیشنهاد دهید. به مزایای کلیدی مانند یکپارچگی، API، سادگی و قابلیت سفارشی سازی اشاره کنید.",\n  "implementationRoadmap": "یک نقشه راه ساده و ۳ مرحله ای برای پیاده سازی راهکار پیشنهادی ارائه دهید. مثلا: مرحله ۱: ورود اطلاعات اولیه و تنظیمات پایه. مرحله ۲: آموزش تیم و شروع استفاده. مرحله ۳: استفاده از گزارشات و بهینه سازی فرآیندها.",\n  "potentialROI": "در این بخش، به صورت تخمینی به بازگشت سرمایه ای که مشتری از این راهکار بدست می آورد اشاره کنید. مثلا: کاهش X درصدی در زمان پیگیری مشتریان یا افزایش Y درصدی در فروش به دلیل مدیریت بهتر سرنخ ها."\n}\n\nفقط و فقط یک JSON معتبر به عنوان خروجی برگردانید و هیچ متن اضافه ای قبل یا بعد از آن قرار ندهید.');

CREATE TABLE `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_text` text NOT NULL,
  `options` text NOT NULL COMMENT 'Comma-separated values',
  `question_key` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `questions` (`question_text`, `options`, `question_key`, `sort_order`) VALUES
('صنعت شما چیست؟', 'فروش و بازرگانی,خدمات پس از فروش,تولید,فناوری اطلاعات,خدمات مشاوره,دیگر', 'industry', 1),
('اندازه تیم شما چند نفر است؟', '1-10 نفر,11-50 نفر,51-200 نفر,بیش از 200 نفر', 'teamSize', 2),
('مهم ترین هدفی که به دنبال آن هستید چیست؟', 'افزایش فروش,بهبود رضایت مشتریان,افزایش بهره وری تیم,کاهش هزینه ها,مدیریت بهتر پروژه ها', 'primaryGoal', 3);


CREATE TABLE `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `needs_data` text NOT NULL COMMENT 'JSON data of user answers',
  `ai_response` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
