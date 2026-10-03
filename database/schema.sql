-- نگاه مدیا | MySQL 5.7+ / MariaDB 10.4+
-- اجرا: ساخت پایگاه داده با utf8mb4 و سپس Import همین فایل
CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(60) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL DEFAULT '',
    tagline VARCHAR(220) NOT NULL DEFAULT '',
    description TEXT NOT NULL,
    icon VARCHAR(40) NOT NULL DEFAULT 'spark',
    sort_order INT NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_services_public_order (is_published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(220) NOT NULL,
    client VARCHAR(180) NOT NULL DEFAULT '',
    category VARCHAR(120) NOT NULL DEFAULT '',
    excerpt TEXT NOT NULL,
    description MEDIUMTEXT NOT NULL,
    image VARCHAR(500) NOT NULL DEFAULT '',
    visual_theme VARCHAR(32) NOT NULL DEFAULT 'saffron',
    metrics VARCHAR(180) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_projects_public_order (is_published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS testimonials (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(140) NOT NULL,
    role VARCHAR(180) NOT NULL DEFAULT '',
    company VARCHAR(180) NOT NULL DEFAULT '',
    quote TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_testimonials_public_order (is_published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS articles (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(220) NOT NULL,
    slug VARCHAR(220) NOT NULL,
    category VARCHAR(120) NOT NULL DEFAULT '',
    excerpt TEXT NOT NULL,
    body MEDIUMTEXT NOT NULL,
    image VARCHAR(500) NOT NULL DEFAULT '',
    published_at DATE NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_article_slug (slug),
    KEY idx_articles_public_date (is_published, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS brands (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(220) NOT NULL,
    slug VARCHAR(220) NOT NULL,
    logo VARCHAR(500) NOT NULL DEFAULT '',
    short_description TEXT NOT NULL,
    long_description MEDIUMTEXT NOT NULL,
    testimonial_quote TEXT NOT NULL,
    testimonial_author VARCHAR(180) NOT NULL DEFAULT '',
    testimonial_role VARCHAR(180) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_brand_slug (slug),
    KEY idx_brands_public_order (is_published, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add the homepage selection field idempotently for existing installations.
SET @brand_featured_column_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'brands' AND column_name = 'is_featured');
SET @brand_featured_column_sql = IF(@brand_featured_column_exists = 0, 'ALTER TABLE brands ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER is_published', 'SELECT 1 INTO @brand_featured_column_noop');
PREPARE brand_featured_column_stmt FROM @brand_featured_column_sql;
EXECUTE brand_featured_column_stmt;
DEALLOCATE PREPARE brand_featured_column_stmt;

CREATE TABLE IF NOT EXISTS brand_media (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    brand_id INT UNSIGNED NOT NULL,
    title VARCHAR(220) NOT NULL,
    caption TEXT NOT NULL,
    media_type ENUM('image', 'video') NOT NULL DEFAULT 'image',
    media_path VARCHAR(500) NOT NULL DEFAULT '',
    poster_path VARCHAR(500) NOT NULL DEFAULT '',
    aspect_ratio ENUM('16:9', '9:16', '1:1') NOT NULL DEFAULT '16:9',
    sort_order INT NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_brand_media_public_order (brand_id, is_published, sort_order),
    CONSTRAINT fk_brand_media_brand FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inquiries (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(60) NOT NULL,
    email VARCHAR(190) NOT NULL DEFAULT '',
    subject VARCHAR(190) NOT NULL DEFAULT '',
    message TEXT NOT NULL,
    status ENUM('new', 'contacted', 'closed') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_inquiries_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- محتوای آغازین سایت؛ اجرای مجدد فایل داده‌های ویرایش‌شدهٔ مدیر را بازنویسی نمی‌کند.
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
('brand_name', 'نگاه مدیا'),
('brand_descriptor', 'استودیو خلاقیت و رشد'),
('hero_kicker', 'استودیوی خلاقیت و رشد دیجیتال'),
('hero_title', 'برند شما،'),
('hero_highlight', 'از زاویه‌ای تازه.'),
('hero_description', 'برای برندهایی که می‌خواهند متفاوت دیده شوند؛ از ایده و هویت بصری تا کمپین‌های دیجیتالِ نتیجه‌محور، کنار شما هستیم.'),
('hero_primary_label', 'برای شروع گفت‌وگو'),
('hero_primary_url', '#contact'),
('hero_secondary_label', 'دیدن روایت پروژه‌ها'),
('hero_secondary_url', '#portfolio'),
('hero_image', 'assets/img/hero-persian-campaign.webp'),
('hero_proof_first', 'از شناخت دقیق تا'),
('hero_proof_second', 'اثر ماندگار، همراه برندها هستیم.'),
('stat_projects', '۴'),
('stat_projects_label', 'مسیر تخصصی'),
('stat_years', '۳'),
('stat_years_label', 'گام تا اجرای ایده'),
('stat_growth', '۱'),
('stat_growth_label', 'تیم در کنار شما'),
('stat_note', 'فکر خوب، با اثر خوب کامل می‌شود.'),
('services_eyebrow', 'توانمندی‌های ما'),
('services_title', 'از ایده تا اثری که\nدر ذهن می‌ماند'),
('services_description', 'هر نقطه تماس برند شما، فرصتی‌ست برای ساختن یک تجربه بهتر. ما این فرصت‌ها را با نگاه استراتژیک و اجرای خلاق به هم وصل می‌کنیم.'),
('services_footnote', 'از یک قدم کوچک تا یک تحول بزرگ، مسیر را با هم طراحی می‌کنیم.'),
('services_footnote_link', 'ببینیم چطور؟'),
('portfolio_eyebrow', 'قصه‌هایی که ساخته‌ایم'),
('portfolio_title', 'چند قاب از نگاه ما'),
('portfolio_description', 'هر همکاری، یک مسئله تازه و یک فرصت برای ساختن چیزی ماندگار است.'),
('portfolio_note', 'نمونه‌های این صفحه مفهومی هستند و برای نمایش اولیه ساخته شده‌اند.'),
('portfolio_cta', 'پروژه بعدی را با هم بسازیم'),
('partners_eyebrow', 'اعتمادهای دوطرفه'),
('partners_title', 'برندهایی که هم‌مسیر نگاه‌اند'),
('partners_description', 'آرشیوی از همراهی‌ها؛ هر نام، دریچه‌ای به روایت، بازخورد و محتوای همان برند است.'),
('about_eyebrow', 'نگاه ما به ماجرا'),
('about_title', 'خلاقیت، وقتی\nاثرگذار است که\nجهت داشته باشد.'),
('about_description', 'ما یک تیم مستقل از استراتژیست‌ها، طراحان و سازندگانیم؛ با یک باور مشترک: تبلیغات خوب فقط دیده نمی‌شود، چیزی را در رفتار و ذهن مخاطب تغییر می‌دهد.'),
('about_quote', '«ما به جای صدای بلندتر، دنبال پیام دقیق‌تریم.»'),
('about_metric', '۱ نگاه، بی‌نهایت امکان'),
('about_metric_label', 'ترکیب درستِ داده، خلاقیت و اجرا'),
('about_button_label', 'بیشتر با هم آشنا شویم'),
('process_eyebrow', 'مسیر همکاری'),
('process_title', 'قدم‌به‌قدم،\nرو به جلو'),
('process_description', 'مسیر روشن، همکاری را ساده‌تر می‌کند. از شنیدن مسئله تا سنجش نتیجه، کنار شما می‌مانیم.'),
('process_step_1_title', 'شنیدن و شناخت'),
('process_step_1_text', 'اهداف، مخاطب و چالش اصلی کسب‌وکار را با دقت می‌شناسیم.'),
('process_step_2_title', 'چیدن نقشه'),
('process_step_2_text', 'بینش‌ها را به استراتژی، پیام و برنامه‌ای قابل اجرا تبدیل می‌کنیم.'),
('process_step_3_title', 'ساختن و اجرا'),
('process_step_3_text', 'با تیم‌های تخصصی، ایده را در تمام نقاط تماس زنده می‌کنیم.'),
('process_step_4_title', 'سنجش و رشد'),
('process_step_4_text', 'نتیجه‌ها را می‌سنجیم، یاد می‌گیریم و مسیر را بهتر می‌کنیم.'),
('testimonials_eyebrow', 'از زبان همراهان'),
('testimonials_title', 'اعتماد، در جزئیات ساخته می‌شود'),
('testimonials_intro', 'بهترین نشانِ کار خوب، حرف کسانی‌ست که در این مسیر کنارمان بوده‌اند.'),
('testimonials_disclaimer', 'دیدگاه‌ها فقط با اجازهٔ صاحبان نظر منتشر می‌شوند.'),
('journal_eyebrow', 'دفترچه نگاه'),
('journal_title', 'فکرهایی برای فردا'),
('journal_description', 'یادداشت‌های کوتاه ما درباره برند، تبلیغات و تجربه‌های دیجیتال.'),
('contact_eyebrow', 'یک شروع تازه'),
('contact_title', 'از ایده‌تان\nبرایمان بگویید.'),
('contact_description', 'فرم کوتاه روبه‌رو را پر کنید؛ در اولین فرصت برای یک گفت‌وگوی بی‌تکلف با شما تماس می‌گیریم.'),
('contact_form_title', 'از اینجا شروع کنیم'),
('contact_form_subtitle', 'پاسخ‌گویی در اولین فرصت'),
('contact_form_name_label', 'نام و نام خانوادگی'),
('contact_form_name_placeholder', 'مثلاً نازنین محمدی'),
('contact_form_phone_label', 'شماره تماس'),
('contact_form_phone_placeholder', '۰۹۱۲ ۳۴۵ ۶۷۸۹'),
('contact_form_email_label', 'ایمیل'),
('contact_form_email_placeholder', 'name@example.com'),
('contact_form_subject_label', 'موضوع همکاری'),
('contact_form_subjects', 'هویت و برندینگ\nکمپین تبلیغاتی\nبازاریابی دیجیتال\nمشاوره و استراتژی\nسایر'),
('contact_form_message_label', 'کمی از پروژه برایمان بگویید'),
('contact_form_message_placeholder', 'چه مسئله‌ای را می‌خواهید با هم حل کنیم؟'),
('contact_form_submit', 'ارسال درخواست'),
('contact_privacy_note', 'اطلاعات شما فقط برای پیگیری همین درخواست استفاده می‌شود.'),
('contact_phone', '۰۲۱ ـ ۸۸۴۴ ۷۳۱۰'),
('contact_email', 'hello@negaah.media'),
('contact_address', 'تهران، خیابان ولیعصر، استودیو نگاه'),
('social_instagram', 'https://instagram.com/'),
('social_linkedin', 'https://linkedin.com/'),
('social_telegram', 'https://t.me/'),
('footer_description', 'به برندها کمک می‌کنیم روشن‌تر ببینند، هوشمندانه‌تر روایت کنند و ماندگارتر رشد کنند.'),
('footer_quick_links_title', 'دسترسی سریع'),
('footer_brand_links_title', 'با نگاه مدیا'),
('footer_contact_title', 'راه‌های ارتباطی'),
('source_download_url', 'https://github.com/Smohamad933/kian-fanavran-negah/archive/refs/heads/arena/01a10192-kian-fanavran-negah.zip'),
('custom_font_path', ''),
('nav_services', 'خدمات'),
('nav_portfolio', 'نمونه‌کارها'),
('nav_partners', 'برندهای همکار'),
('nav_about', 'درباره ما'),
('nav_journal', 'دفترچه نگاه'),
('nav_contact', 'ارتباط با ما'),
('theme_primary', '#155C5A'),
('theme_accent', '#BD5D43'),
('theme_saffron', '#D7A84A'),
('theme_ink', '#1E2C2B'),
('theme_surface', '#F6F3EA');

-- پروژه‌های نمونه برای نمایش اولیه مفهومی‌اند؛ هیچ دیدگاه مشتریِ ساختگی seed نمی‌شود.
INSERT INTO services (name, slug, tagline, description, icon, sort_order, is_published)
SELECT 'استراتژی و مشاوره', 'strategy', 'اول، درست ببینیم.', 'شناخت بازار و مخاطب، جایگاه‌یابی برند و طراحی نقشه‌ای شفاف برای رشد.', 'compass', 1, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM services WHERE slug = 'strategy');
INSERT INTO services (name, slug, tagline, description, icon, sort_order, is_published)
SELECT 'هویت و طراحی برند', 'branding', 'شبیه خودتان باشید.', 'از نام و نشان تا لحن کلام؛ هویتی منسجم که به یاد می‌ماند و اعتماد می‌سازد.', 'spark', 2, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM services WHERE slug = 'branding');
INSERT INTO services (name, slug, tagline, description, icon, sort_order, is_published)
SELECT 'کمپین و تولید محتوا', 'campaigns', 'حرفی برای گفتن.', 'ایده‌های کمپین، تولید محتوای چندرسانه‌ای و روایت‌هایی که مخاطب را درگیر می‌کنند.', 'megaphone', 3, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM services WHERE slug = 'campaigns');
INSERT INTO services (name, slug, tagline, description, icon, sort_order, is_published)
SELECT 'بازاریابی دیجیتال', 'digital', 'رشد، با عدد و معنا.', 'از طراحی تجربه دیجیتال و سئو تا بهینه‌سازی تبلیغات بر پایه داده و نتیجه.', 'chart', 4, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM services WHERE slug = 'digital');

INSERT INTO projects (title, client, category, excerpt, description, image, visual_theme, metrics, sort_order, is_published)
SELECT 'ریشه؛ روایت یک طعم اصیل', 'برند ریشه', 'هویت بصری', 'بازآفرینی هویت یک برند محصولات بومی با الهام از نقش‌مایه‌های ایرانی.', '', '', 'saffron', 'پروژه مفهومی', 1, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM projects WHERE title = 'ریشه؛ روایت یک طعم اصیل');
INSERT INTO projects (title, client, category, excerpt, description, image, visual_theme, metrics, sort_order, is_published)
SELECT 'نقش؛ خانه‌ای برای فرم‌های تازه', 'استودیو نقش', 'دیجیتال و تجربه کاربری', 'طراحی تجربه خرید آنلاین با تمرکز بر سادگی و کشف محصول.', '', '', 'teal', 'پروژه مفهومی', 2, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM projects WHERE title = 'نقش؛ خانه‌ای برای فرم‌های تازه');
INSERT INTO projects (title, client, category, excerpt, description, image, visual_theme, metrics, sort_order, is_published)
SELECT 'دُرنا؛ انرژی یک شروع نو', 'دُرنا', 'کمپین تبلیغاتی', 'کمپینی یکپارچه برای معرفی محصولی تازه به نسلی تازه.', '', '', 'coral', 'پروژه مفهومی', 3, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM projects WHERE title = 'دُرنا؛ انرژی یک شروع نو');

-- Disable legacy demo quotations so only verified customer feedback is public.
UPDATE testimonials SET is_published = 0 WHERE
    (name = 'سارا امینی' AND company = 'همراه پروژه ریشه') OR
    (name = 'آرمان نیک‌پی' AND company = 'همراه پروژه نقش') OR
    (name = 'مهتاب یوسفی' AND company = 'همراه پروژه دُرنا');

INSERT IGNORE INTO articles (title, slug, category, excerpt, body, image, published_at, is_published) VALUES
('چطور یک برند، صدای خودش را پیدا می‌کند؟', 'finding-brand-voice', 'برندینگ', 'لحن برند، فقط چند واژه در شبکه‌های اجتماعی نیست؛ تجربه‌ای‌ست که در تمام تماس‌ها شکل می‌گیرد.', 'لحن برند فقط چند واژه در شبکه‌های اجتماعی نیست؛ تجربه‌ای‌ست که در تمام تماس‌ها شکل می‌گیرد. از نخستین آگهی تا پاسخ پشتیبانی، صدای برند باید یک‌پارچه، صادقانه و متناسب با آدم‌هایی باشد که با آن حرف می‌زند.\n\nبرای پیدا کردن این صدا، از ارزش‌ها شروع کنید، مخاطب را خوب بشناسید و چند اصل ساده برای نوشتن و گفت‌وگو تعریف کنید. بعد، همان اصول را در تمام کانال‌ها تمرین و بازبینی کنید.', '', CURRENT_DATE, 1),
('کمپین خوب از پرسیدن سؤال درست شروع می‌شود', 'better-campaigns', 'استراتژی', 'پیش از انتخاب رسانه و فرمت، باید بدانیم قرار است چه چیزی در مخاطب تغییر کند.', 'پیش از انتخاب رسانه و فرمت، باید بدانیم قرار است چه چیزی در مخاطب تغییر کند. کمپین زمانی جهت پیدا می‌کند که یک مسئله روشن، یک مخاطب مشخص و یک هدف قابل سنجش داشته باشد.\n\nبا پرسیدن سؤال درست، ایده از حد یک تصویر جذاب فراتر می‌رود و به تجربه‌ای تبدیل می‌شود که مخاطب آن را می‌فهمد، به یاد می‌آورد و درباره‌اش حرف می‌زند.', '', CURRENT_DATE, 1);

-- برندهای همکار اولیه؛ دیدگاه واقعی هر برند را از پنل ثبت کنید.
INSERT IGNORE INTO brands (name, slug, logo, short_description, long_description, testimonial_quote, sort_order, is_published, is_featured) VALUES
('رنس تکس', 'rans-tex', '', '', '', '', 1, 1, 0),
('گالری طلاوجواهر محمود', 'mahmoud-jewelry', '', '', '', '', 2, 1, 0),
('رویان شبکه', 'royan-network', '', '', '', '', 3, 1, 0),
('الدراگ استور', 'aldrag-store', '', '', '', '', 4, 1, 0),
('دانشگاه شهید چمران اهواز', 'shahid-chamran-university', '', '', '', '', 5, 1, 0),
('وزارت علوم، تحقیقات و فناوری', 'ministry-science-research', '', '', '', '', 6, 1, 0),
('چمران پلاس', 'chamran-plus', '', '', '', '', 7, 1, 0),
('صداوسیما مرکز خوزستان', 'irib-khuzestan', '', '', '', '', 8, 1, 0),
('انجمن خیریه ۱۴ معصوم', 'charity-14-maasoom', '', '', '', '', 9, 1, 0),
('گالری نقره سیده راد', 'seyedarad-silver', '', '', '', '', 10, 1, 0),
('گالری جواهرات هم‌نفس', 'hamnafas-jewelry', '', '', '', '', 11, 1, 0),
('سازه‌های آبی شوشتر', 'shushtar-water-structures', '', '', '', '', 12, 1, 0),
('مجموعه نظریان', 'nazarian-group', '', '', '', '', 13, 1, 0),
('ابزارآلات قشقایی', 'ghashghai-tools', '', '', '', '', 14, 1, 0),
('شهرداری اهواز', 'ahvaz-municipality', '', '', '', '', 15, 1, 0),
('استانداری هرمزگان', 'hormozgan-governorate', '', '', '', '', 16, 1, 0),
('استانداری خوزستان', 'khuzestan-governorate', '', '', '', '', 17, 1, 0),
('کنسرت علیرضا قربانی', 'alireza-ghorbani-concert', '', '', '', '', 18, 1, 0),
('ارکستر سازهای ایرانی به یاد خالقی', 'khalaghi-iranian-orchestra', '', '', '', '', 19, 1, 0),
('جایزه ملی آهنگسازی استاد روح‌الله خالقی', 'khalaghi-composition-award', '', '', '', '', 20, 1, 0),
('خانه موسیقی تهران', 'tehran-music-house', '', '', '', '', 21, 1, 0),
('مشاوران افق دانش ثریا', 'ofogh-danesh-soraya', '', '', '', '', 22, 1, 0),
('مرکز رسانه استان خوزستان', 'khuzestan-media-center', '', '', '', '', 23, 1, 0),
('سازمان تبلیغات استان خوزستان', 'khuzestan-advertising-organization', '', '', '', '', 24, 1, 0),
('آژانس تبلیغاتی لامیلا', 'lamila-ad-agency', '', '', '', '', 25, 1, 0),
('مجموعه سرودهای استان خوزستان', 'khuzestan-choir-group', '', '', '', '', 26, 1, 0),
('مؤسسه برتینا', 'bertina-institute', '', '', '', '', 27, 1, 0),
('گروه موسیقی نی‌نوا', 'ney-nava-music-group', '', '', '', '', 28, 1, 0),
('دفتر امام جمعه اهواز', 'ahvaz-friday-office', '', '', '', '', 29, 1, 0),
('استودیو هور', 'studio-hoor', '', '', '', '', 30, 1, 0),
('کلینیک مشاوره کودک و نوجوان بهشت زندگی', 'behesht-zendegi-clinic', '', '', '', '', 31, 1, 0),
('طلا و جواهرات محمد سیاوشی', 'mohammad-siavashi-jewelry', '', '', '', '', 32, 1, 0);
