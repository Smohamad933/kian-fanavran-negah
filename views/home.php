<?php
if (!defined('SITE_RENDER')) {
    http_response_code(404);
    exit;
}
$customFontPath = safe_font_src($settings['custom_font_path'] ?? '');
$footerCreditUrl = safe_href($settings['footer_credit_url'] ?? '', '');
$customFontFormat = match (strtolower(pathinfo($customFontPath, PATHINFO_EXTENSION))) {
    'woff2' => 'woff2', 'woff' => 'woff', 'ttf' => 'truetype', 'otf' => 'opentype', default => ''
};
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="<?= e($surfaceColor) ?>">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:type" content="website">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/site.css">
    <style>
        :root {
            --teal: <?= e($heroColor) ?>;
            --clay: <?= e($accentColor) ?>;
            --saffron: <?= e($saffronColor) ?>;
            --ink: <?= e($inkColor) ?>;
            --paper: <?= e($surfaceColor) ?>;
        }
        <?php if ($customFontPath !== ''): ?>
        @font-face { font-family: 'NegaahCustom'; src: url('<?= e($customFontPath) ?>') format('<?= e($customFontFormat) ?>'); font-weight: 100 900; font-display: swap; }
        :root { --font: 'NegaahCustom', 'Vazirmatn', Tahoma, sans-serif; }
        <?php endif; ?>
    </style>
    <script defer src="assets/js/site.js"></script>
</head>
<body>
    <div class="scroll-progress" aria-hidden="true"><span></span></div>
    <header class="site-header" id="top">
        <div class="container header-inner">
            <a class="brand" href="#top" aria-label="<?= e($settings['brand_name']) ?>، صفحهٔ اصلی">
                <span class="brand-symbol" aria-hidden="true">
                    <svg viewBox="0 0 46 46" fill="none"><path d="M4 23C9.6 14.9 16 10.8 23 10.8S36.4 14.9 42 23c-5.6 8.1-12 12.2-19 12.2S9.6 31.1 4 23Z" stroke="currentColor" stroke-width="1.8"/><circle cx="23" cy="23" r="5.7" fill="currentColor"/><circle cx="23" cy="23" r="2" fill="var(--paper)"/></svg>
                </span>
                <span class="brand-wordmark"><strong><?= e($settings['brand_name']) ?></strong><small><?= e($settings['brand_descriptor']) ?></small></span>
            </a>

            <button class="menu-toggle" type="button" aria-label="باز کردن فهرست" aria-expanded="false" aria-controls="primary-nav">
                <?= icon_svg('menu', 'menu-open-icon') ?>
                <?= icon_svg('close', 'menu-close-icon') ?>
            </button>
            <nav class="primary-nav" id="primary-nav" aria-label="فهرست اصلی">
                <a href="#services"><?= e($settings['nav_services']) ?></a>
                <a href="#portfolio"><?= e($settings['nav_portfolio']) ?></a>
                <a href="#partners"><?= e($settings['nav_partners']) ?></a>
                <a href="#about"><?= e($settings['nav_about']) ?></a>
                <a href="#journal"><?= e($settings['nav_journal']) ?></a>
            </nav>
            <a class="header-contact" href="#contact"><span><?= e($settings['nav_contact']) ?></span><?= icon_svg('arrow-left') ?></a>
        </div>
    </header>

    <main>
        <section class="hero-section section-shell">
            <div class="container hero-grid">
                <div class="hero-copy" data-reveal>
                    <div class="eyebrow"><span class="eyebrow-mark"></span><?= e($settings['hero_kicker']) ?></div>
                    <h1><?= e($settings['hero_title']) ?><br><span class="hero-highlight"><?= e($settings['hero_highlight']) ?><i class="highlight-dot"></i></span></h1>
                    <p class="hero-description"><?= e($settings['hero_description']) ?></p>
                    <div class="hero-actions">
                        <a class="button button-primary" href="<?= e(safe_href($settings['hero_primary_url'])) ?>"><span><?= e($settings['hero_primary_label']) ?></span><?= icon_svg('arrow-left') ?></a>
                        <a class="text-link" href="<?= e(safe_href($settings['hero_secondary_url'], '#portfolio')) ?>"><span class="text-link-icon"><?= icon_svg('arrow-down') ?></span><?= e($settings['hero_secondary_label']) ?></a>
                    </div>
                    <div class="hero-proof">
                        <div class="proof-avatars" aria-hidden="true"><span>ن</span><span>م</span><span>ر</span></div>
                        <p><?= e($settings['hero_proof_first']) ?><br><strong><?= e($settings['hero_proof_second']) ?></strong></p>
                    </div>
                </div>

                <div class="hero-art" data-reveal data-reveal-delay="120">
                    <div class="hero-art-backdrop"></div>
                    <div class="hero-image-frame">
                        <img src="<?= e(safe_image_src($settings['hero_image'], 'assets/img/hero-persian-campaign.webp')) ?>" alt="چیدمان هنری با الهام از معماری و رنگ‌های ایرانی" fetchpriority="high">
                    </div>
                    <div class="hero-art-caption"><span class="caption-index">۰۱</span><span>نگاهی نو به امکان‌های تازه</span><span class="caption-rule"></span></div>
                    <div class="hero-stamp" aria-label="ایده، هویت، رشد"><span>ایده</span><i></i><span>هویت</span><i></i><span>رشد</span><b>ن</b></div>
                    <div class="hero-floating-note"><span class="note-star">✳</span><span>نگاه متفاوت،<br><b>نتیجهٔ متفاوت.</b></span></div>
                    <span class="hero-ornament hero-ornament-one" aria-hidden="true"></span>
                    <span class="hero-ornament hero-ornament-two" aria-hidden="true"></span>
                </div>
            </div>
            <div class="container hero-stats" data-reveal>
                <div class="stat-item"><strong><?= e($settings['stat_projects']) ?></strong><span><?= e($settings['stat_projects_label']) ?></span></div>
                <div class="stat-item"><strong><?= e($settings['stat_years']) ?></strong><span><?= e($settings['stat_years_label']) ?></span></div>
                <div class="stat-item"><strong><?= e($settings['stat_growth']) ?></strong><span><?= e($settings['stat_growth_label']) ?></span></div>
                <div class="stat-note"><span class="stat-sun">✳</span><span><?= e($settings['stat_note']) ?></span></div>
            </div>
            <div class="container hero-bottom-line"><span>استراتژی</span><i></i><span>روایت</span><i></i><span>طراحی</span><i></i><span>رشد</span><span class="hero-bottom-side">۰۱ / ۰۶</span></div>
        </section>

        <section class="services-section section-space" id="services">
            <div class="container">
                <div class="section-heading section-heading-split" data-reveal>
                    <div>
                        <div class="eyebrow"><span class="eyebrow-mark"></span><?= e($settings['services_eyebrow']) ?></div>
                        <h2><?= nl2br(e($settings['services_title'])) ?></h2>
                    </div>
                    <div class="heading-side"><p><?= e($settings['services_description']) ?></p><a class="round-link" href="#contact" aria-label="گفت‌وگو درباره خدمات"><?= icon_svg('arrow-left') ?></a></div>
                </div>

                <div class="services-grid">
                    <?php foreach ($services as $index => $service): ?>
                        <article class="service-card" data-reveal data-reveal-delay="<?= e((string) ($index * 80)) ?>">
                            <div class="service-top"><span class="service-icon"><?= icon_svg((string) ($service['icon'] ?? 'spark')) ?></span><span class="service-number"><?= fa_num(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span></div>
                            <div class="service-body"><span class="service-tagline"><?= e($service['tagline'] ?? '') ?></span><h3><?= e($service['name'] ?? '') ?></h3><p><?= e($service['description'] ?? '') ?></p></div>
                            <a href="#contact" class="service-arrow" aria-label="درباره <?= e($service['name'] ?? 'این خدمت') ?> گفت‌وگو کنیم"><?= icon_svg('arrow-left') ?></a>
                            <span class="service-pattern" aria-hidden="true"></span>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="services-footnote"><span class="footnote-star">✳</span><span><?= e($settings['services_footnote']) ?></span><a href="#contact"><?= e($settings['services_footnote_link']) ?> <?= icon_svg('arrow-left') ?></a></div>
            </div>
        </section>

        <section class="portfolio-section section-space" id="portfolio">
            <div class="container">
                <div class="section-heading section-heading-split" data-reveal>
                    <div>
                        <div class="eyebrow"><span class="eyebrow-mark"></span><?= e($settings['portfolio_eyebrow']) ?></div>
                        <h2><?= e($settings['portfolio_title']) ?></h2>
                    </div>
                    <div class="heading-side"><p><?= e($settings['portfolio_description']) ?></p><div class="portfolio-controls" aria-label="فیلتر نمونه‌کارها"><button class="portfolio-filter is-active" type="button" data-filter="all">همه</button><button class="portfolio-filter" type="button" data-filter="brand">هویت برند</button><button class="portfolio-filter" type="button" data-filter="digital">دیجیتال</button><button class="portfolio-filter" type="button" data-filter="campaign">کمپین</button></div></div>
                </div>
                <div class="portfolio-grid">
                    <?php foreach ($projects as $index => $project): ?>
                        <?php
                        $theme = in_array(($project['visual_theme'] ?? ''), ['saffron', 'teal', 'coral', 'ink'], true) ? $project['visual_theme'] : ['saffron', 'teal', 'coral'][$index % 3];
                        $category = (string) ($project['category'] ?? '');
                        $filter = (str_contains($category, 'دیجیتال') || str_contains($category, 'تجربه')) ? 'digital' : ((str_contains($category, 'کمپین') || str_contains($category, 'محتوا')) ? 'campaign' : 'brand');
                        $projectImage = safe_image_src($project['image'] ?? '', '');
                        ?>
                        <article class="project-card project-card-<?= fa_num((string) ($index + 1)) ?>" data-project-card data-category="<?= e($filter) ?>" data-reveal data-reveal-delay="<?= e((string) ($index * 100)) ?>">
                            <a class="project-visual project-visual--<?= e($theme) ?>" href="#contact" aria-label="گفت‌وگو درباره <?= e($project['title'] ?? '') ?>">
                                <?php if ($projectImage !== ''): ?>
                                    <img class="project-uploaded-image" src="<?= e($projectImage) ?>" alt="نمایی از پروژهٔ <?= e($project['title'] ?? '') ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="project-artwork project-artwork--<?= e($theme) ?>" aria-hidden="true">
                                        <?php if ($theme === 'saffron'): ?>
                                            <span class="art-sun"></span><span class="art-arch"></span><span class="art-vessel"></span><span class="art-label">ریشه <small>طعمِ خانه</small></span><span class="art-floor"></span>
                                        <?php elseif ($theme === 'teal'): ?>
                                            <span class="art-window"><i></i><i></i><i></i></span><span class="art-tile"></span><span class="art-ring"></span><span class="art-word">NAGHSH</span><span class="art-mini-line"></span>
                                        <?php else: ?>
                                            <span class="art-coral-sun"></span><span class="art-poster"><i>دُرنا</i><small>هر روز<br>یک شروع تازه</small><b>۱۴۰۵</b></span><span class="art-cube"></span><span class="art-shadow"></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <span class="project-view"><?= icon_svg('arrow') ?></span>
                                <span class="project-image-label"><?= e($project['metrics'] ?: 'نگاه مدیا') ?></span>
                            </a>
                            <div class="project-info"><div><span class="project-category"><?= e($project['category'] ?? '') ?></span><h3><?= e($project['title'] ?? '') ?></h3><p><?= e($project['excerpt'] ?? '') ?></p></div><a class="project-open" href="#contact" aria-label="درباره پروژه <?= e($project['title'] ?? '') ?>"><?= icon_svg('arrow-left') ?></a></div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="portfolio-bottom"><span><b></b><?= e($settings['portfolio_note']) ?></span><a href="#contact" class="underlined-link"><?= e($settings['portfolio_cta']) ?> <?= icon_svg('arrow-left') ?></a></div>
            </div>
        </section>

        <section class="partners-section section-space" id="partners">
            <div class="container">
                <div class="section-heading section-heading-split" data-reveal>
                    <div>
                        <div class="eyebrow"><span class="eyebrow-mark"></span><?= e($settings['partners_eyebrow']) ?></div>
                        <h2><?= e($settings['partners_title']) ?></h2>
                    </div>
                    <div class="heading-side partners-heading-side"><p><?= e($settings['partners_description']) ?></p><div class="partners-heading-actions"><span class="partners-count"><b><?= fa_num((string) count($featuredBrands)) ?></b><small>برند منتخب</small></span><a class="partners-all-link" href="brands.php">مشاهدهٔ همهٔ برندها <?= icon_svg('arrow-left') ?></a></div></div>
                </div>
                <?php if ($featuredBrands !== []): ?>
                    <div class="partners-grid">
                        <?php foreach ($featuredBrands as $index => $brand): ?>
                            <?php $previewImage = safe_image_src($brand['preview_image'] ?? '', ''); ?>
                            <a class="partner-card partner-card--preview" href="brand.php?slug=<?= e(rawurlencode((string) $brand['slug'])) ?>" aria-label="مشاهدهٔ صفحهٔ <?= e($brand['name']) ?>" data-reveal data-reveal-delay="<?= e((string) (($index % 4) * 55)) ?>">
                                <span class="partner-preview">
                                    <?php if ($previewImage !== ''): ?><img src="<?= e($previewImage) ?>" alt="پیش‌نمایش برند <?= e($brand['name']) ?>" loading="lazy"><?php else: ?><span class="partner-preview-empty"><small>پیش‌نمایش از پنل اضافه می‌شود</small></span><?php endif; ?>
                                </span>
                                <span class="partner-card-details"><span class="partner-card-topline"><span class="partner-number" dir="ltr">NO. <?= fa_num(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span><span class="partner-visit"><?= icon_svg('arrow-left') ?><span>مشاهده صفحه</span></span></span><span class="partner-card-copy"><span class="partner-name"><?= e($brand['name']) ?></span><?php if (trim((string) ($brand['short_description'] ?? '')) !== ''): ?><small class="partner-short-description"><?= e($brand['short_description']) ?></small><?php endif; ?></span></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="partners-empty"><strong>برندهای منتخب به‌زودی در این بخش نمایش داده می‌شوند.</strong></div>
                <?php endif; ?>
            </div>
        </section>

        <section class="about-section section-space" id="about">
            <div class="container about-grid">
                <div class="about-visual" data-reveal>
                    <div class="about-tile-art">
                        <div class="tile-orbit tile-orbit-one"></div><div class="tile-orbit tile-orbit-two"></div>
                        <div class="tile-center"><span class="tile-eye">ن</span><span>نگاه<br>به آینده</span></div>
                        <div class="tile-corner tile-corner-one"></div><div class="tile-corner tile-corner-two"></div>
                        <span class="tile-caption">تهران · ایران</span>
                    </div>
                    <div class="about-note"><span class="about-note-icon">✳</span><span><?= e($settings['about_metric']) ?><small><?= e($settings['about_metric_label']) ?></small></span></div>
                </div>
                <div class="about-copy" data-reveal data-reveal-delay="100">
                    <div class="eyebrow eyebrow-light"><span class="eyebrow-mark"></span><?= e($settings['about_eyebrow']) ?></div>
                    <h2><?= nl2br(e($settings['about_title'])) ?></h2>
                    <p><?= e($settings['about_description']) ?></p>
                    <div class="about-quote"><?= icon_svg('quote') ?><span><?= e($settings['about_quote']) ?></span></div>
                    <a class="button button-light" href="#contact"><span><?= e($settings['about_button_label']) ?></span><?= icon_svg('arrow-left') ?></a>
                    <div class="about-scribble" aria-hidden="true">نگاه</div>
                </div>
            </div>
        </section>

        <section class="process-section section-space" id="process">
            <div class="container process-layout">
                <div class="process-intro" data-reveal>
                    <div class="eyebrow"><span class="eyebrow-mark"></span><?= e($settings['process_eyebrow']) ?></div>
                    <h2><?= nl2br(e($settings['process_title'])) ?></h2>
                    <p><?= e($settings['process_description']) ?></p>
                    <span class="process-compass" aria-hidden="true"><i></i><b>ن</b></span>
                </div>
                <div class="process-steps">
                    <?php for ($step = 1; $step <= 4; $step++): ?>
                        <article class="process-step" data-reveal data-reveal-delay="<?= e((string) ($step * 65)) ?>">
                            <span class="step-number"><?= fa_num(str_pad((string) $step, 2, '0', STR_PAD_LEFT)) ?></span>
                            <div><h3><?= e($settings['process_step_' . $step . '_title']) ?></h3><p><?= e($settings['process_step_' . $step . '_text']) ?></p></div>
                            <span class="step-check"><?= icon_svg('arrow-left') ?></span>
                        </article>
                    <?php endfor; ?>
                </div>
            </div>
        </section>

        <?php if ($testimonials !== []): ?>
        <section class="testimonial-section section-space">
            <div class="container">
                <div class="section-heading section-heading-center" data-reveal>
                    <div class="eyebrow"><span class="eyebrow-mark"></span><?= e($settings['testimonials_eyebrow']) ?></div>
                    <h2><?= e($settings['testimonials_title']) ?></h2>
                    <p><?= e($settings['testimonials_intro']) ?></p>
                </div>
                <div class="testimonial-grid">
                    <?php foreach ($testimonials as $index => $testimonial): ?>
                        <figure class="testimonial-card" data-reveal data-reveal-delay="<?= e((string) ($index * 90)) ?>">
                            <div class="testimonial-top"><span class="quote-mark">“</span><span class="testimonial-stars" aria-label="۵ از ۵">✳ ✳ ✳ ✳ ✳</span></div>
                            <blockquote><?= e($testimonial['quote'] ?? '') ?></blockquote>
                            <figcaption><span class="testimonial-avatar"><?= e(first_char($testimonial['name'] ?? 'ن')) ?></span><span><strong><?= e($testimonial['name'] ?? '') ?></strong><small><?= e($testimonial['role'] ?? '') ?>، <?= e($testimonial['company'] ?? '') ?></small></span><span class="testimonial-line"></span></figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
                <?php if (trim($settings['testimonials_disclaimer']) !== ''): ?><div class="testimonial-footnote"><span class="footnote-star">✳</span><span><?= e($settings['testimonials_disclaimer']) ?></span></div><?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($articles !== []): ?>
        <section class="journal-section section-space" id="journal">
            <div class="container">
                <div class="section-heading section-heading-split" data-reveal>
                    <div><div class="eyebrow"><span class="eyebrow-mark"></span><?= e($settings['journal_eyebrow']) ?></div><h2><?= e($settings['journal_title']) ?></h2></div>
                    <div class="heading-side"><p><?= e($settings['journal_description']) ?></p><a class="round-link" href="#contact" aria-label="ارتباط با ما"><?= icon_svg('arrow-left') ?></a></div>
                </div>
                <div class="journal-grid">
                    <?php foreach (array_slice($articles, 0, 3) as $index => $article): ?>
                        <?php $articleImage = safe_image_src($article['image'] ?? '', ''); ?>
                        <article class="journal-card" data-reveal data-reveal-delay="<?= e((string) ($index * 75)) ?>">
                            <a class="journal-image journal-image--<?= fa_num((string) ($index + 1)) ?>" href="article.php?slug=<?= e(rawurlencode((string) ($article['slug'] ?? ''))) ?>" aria-label="خواندن <?= e($article['title'] ?? '') ?>">
                                <?php if ($articleImage !== ''): ?><img src="<?= e($articleImage) ?>" alt="" loading="lazy"><?php else: ?><span class="journal-image-shape"><i></i><b></b><small><?= fa_num(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></small></span><?php endif; ?>
                                <span class="journal-image-arrow"><?= icon_svg('arrow') ?></span>
                            </a>
                            <div class="journal-meta"><span><?= e($article['category'] ?? '') ?></span><span>یادداشت نگاه</span></div>
                            <h3><a href="article.php?slug=<?= e(rawurlencode((string) ($article['slug'] ?? ''))) ?>"><?= e($article['title'] ?? '') ?></a></h3>
                            <p><?= e($article['excerpt'] ?? '') ?></p>
                            <a class="journal-read" href="article.php?slug=<?= e(rawurlencode((string) ($article['slug'] ?? ''))) ?>">ادامهٔ یادداشت <?= icon_svg('arrow-left') ?></a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <section class="contact-section section-space" id="contact">
            <div class="container contact-panel">
                <div class="contact-copy" data-reveal>
                    <div class="eyebrow eyebrow-light"><span class="eyebrow-mark"></span><?= e($settings['contact_eyebrow']) ?></div>
                    <h2><?= nl2br(e($settings['contact_title'])) ?></h2>
                    <p><?= e($settings['contact_description']) ?></p>
                    <div class="contact-direct">
                        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $settings['contact_phone'])) ?>"><span class="contact-icon"><?= icon_svg('phone') ?></span><span><small>تماس مستقیم</small><strong><?= e($settings['contact_phone']) ?></strong></span></a>
                        <a href="mailto:<?= e($settings['contact_email']) ?>"><span class="contact-icon"><?= icon_svg('mail') ?></span><span><small>برایمان بنویسید</small><strong><?= e($settings['contact_email']) ?></strong></span></a>
                        <div class="contact-address"><span class="contact-icon"><?= icon_svg('pin') ?></span><span><small>پیدایمان کنید</small><strong><?= e($settings['contact_address']) ?></strong></span></div>
                    </div>
                    <div class="contact-pattern" aria-hidden="true"></div>
                </div>
                <div class="contact-form-wrap" data-reveal data-reveal-delay="100">
                    <div class="form-heading"><span><?= e($settings['contact_form_title']) ?></span><small><?= e($settings['contact_form_subtitle']) ?></small></div>
                    <?php if ($flash): ?><div class="form-alert form-alert--<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div><?php endif; ?>
                    <form class="contact-form" action="index.php#contact" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="form_type" value="contact">
                        <div class="form-honeypot" aria-hidden="true"><label>وب‌سایت<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                        <div class="form-row">
                            <label><span><?= e($settings['contact_form_name_label']) ?> <b>*</b></span><input type="text" name="name" placeholder="<?= e($settings['contact_form_name_placeholder']) ?>" autocomplete="name" required maxlength="120"></label>
                            <label><span><?= e($settings['contact_form_phone_label']) ?> <b>*</b></span><input type="tel" name="phone" placeholder="<?= e($settings['contact_form_phone_placeholder']) ?>" autocomplete="tel" required maxlength="60" dir="ltr"></label>
                        </div>
                        <div class="form-row">
                            <label><span><?= e($settings['contact_form_email_label']) ?></span><input type="email" name="email" placeholder="<?= e($settings['contact_form_email_placeholder']) ?>" autocomplete="email" maxlength="190" dir="ltr"></label>
                            <label><span><?= e($settings['contact_form_subject_label']) ?></span><select name="subject"><option value="">انتخاب کنید</option><?php foreach (preg_split('/\\R/u', (string) $settings['contact_form_subjects']) ?: [] as $subjectOption): $subjectOption = trim($subjectOption); if ($subjectOption !== ''): ?><option value="<?= e($subjectOption) ?>"><?= e($subjectOption) ?></option><?php endif; endforeach; ?></select></label>
                        </div>
                        <label><span><?= e($settings['contact_form_message_label']) ?> <b>*</b></span><textarea name="message" rows="3" placeholder="<?= e($settings['contact_form_message_placeholder']) ?>" required maxlength="4000"></textarea></label>
                        <button class="button button-primary button-submit" type="submit"><span><?= e($settings['contact_form_submit']) ?></span><?= icon_svg('arrow-left') ?></button>
                        <small class="privacy-note"><?= e($settings['contact_privacy_note']) ?></small>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-main">
            <div class="footer-brand-column">
                <a class="brand brand-footer" href="#top">
                    <span class="brand-symbol" aria-hidden="true"><svg viewBox="0 0 46 46" fill="none"><path d="M4 23C9.6 14.9 16 10.8 23 10.8S36.4 14.9 42 23c-5.6 8.1-12 12.2-19 12.2S9.6 31.1 4 23Z" stroke="currentColor" stroke-width="1.8"/><circle cx="23" cy="23" r="5.7" fill="currentColor"/><circle cx="23" cy="23" r="2" fill="var(--deep)"/></svg></span>
                    <span class="brand-wordmark"><strong><?= e($settings['brand_name']) ?></strong><small><?= e($settings['brand_descriptor']) ?></small></span>
                </a>
                <p><?= e($settings['footer_description']) ?></p>
                <div class="social-links">
                    <a href="<?= e(safe_href($settings['social_instagram'], 'https://instagram.com/')) ?>" target="_blank" rel="noopener noreferrer" aria-label="اینستاگرام نگاه مدیا"><?= icon_svg('instagram') ?></a>
                    <a href="<?= e(safe_href($settings['social_linkedin'], 'https://linkedin.com/')) ?>" target="_blank" rel="noopener noreferrer" aria-label="لینکدین نگاه مدیا"><?= icon_svg('linkedin') ?></a>
                    <a href="<?= e(safe_href($settings['social_telegram'], 'https://t.me/')) ?>" target="_blank" rel="noopener noreferrer" aria-label="تلگرام نگاه مدیا"><?= icon_svg('telegram') ?></a>
                </div>
            </div>
            <div class="footer-links-column"><h3><?= e($settings['footer_quick_links_title']) ?></h3><a href="#services"><?= e($settings['nav_services']) ?></a><a href="#portfolio"><?= e($settings['nav_portfolio']) ?></a><a href="#partners"><?= e($settings['nav_partners']) ?></a><a href="#about"><?= e($settings['nav_about']) ?></a><a href="#journal"><?= e($settings['nav_journal']) ?></a></div>
            <div class="footer-links-column"><h3><?= e($settings['footer_brand_links_title']) ?></h3><a href="#contact">درخواست مشاوره</a><a href="#contact">همکاری با ما</a><a href="#contact">پرسش‌های شما</a><?php if ((string) ($settings['footer_show_admin_login'] ?? '0') === '1'): ?><a href="admin/login.php">ورود مدیر سایت</a><?php endif; ?><?php if ((string) ($settings['footer_show_source_link'] ?? '1') === '1' && trim((string) ($settings['source_download_url'] ?? '')) !== ''): ?><a class="source-download-link" href="<?= e(safe_href($settings['source_download_url'], '#')) ?>" target="_blank" rel="noopener noreferrer">دانلود سورس سایت <?= icon_svg('arrow') ?></a><?php endif; ?></div>
            <div class="footer-contact-column"><h3><?= e($settings['footer_contact_title']) ?></h3><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $settings['contact_phone'])) ?>"><?= e($settings['contact_phone']) ?></a><a href="mailto:<?= e($settings['contact_email']) ?>"><?= e($settings['contact_email']) ?></a><span><?= e($settings['contact_address']) ?></span><a class="footer-top-link" href="#top">بازگشت به بالا <?= icon_svg('arrow') ?></a></div>
        </div>
        <div class="container footer-bottom"><span>© <?= fa_num(date('Y')) ?> <?= e($settings['brand_name']) ?>. همهٔ حقوق محفوظ است.</span><?php if ((string) ($settings['footer_show_credit'] ?? '1') === '1'): ?><span class="footer-credit">نگاه مدیا، از خانوادهٔ <?php if ($footerCreditUrl !== ''): ?><a href="<?= e($footerCreditUrl) ?>"<?= (str_starts_with($footerCreditUrl, 'http://') || str_starts_with($footerCreditUrl, 'https://')) ? ' target="_blank" rel="noopener noreferrer"' : '' ?>><b>کیان فناوران نگاه</b></a><?php else: ?><b>کیان فناوران نگاه</b><?php endif; ?></span><?php endif; ?></div>
    </footer>
</body>
</html>
