<?php
// App configuration for InfinityFree hosting
// Base URL - QUAN TRỌNG: Không có dấu / ở cuối
    if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}
define('BASE_URL', 'https://articlehub.lovestoblog.com');
// Database credentials
define('DB_HOST', 'sql305.infinityfree.net');
define('DB_NAME', 'if0_40338957_article_portal');
define('DB_USER', 'if0_40338957');
define('DB_PASS', 'GiaBao260705');
define('DB_CHARSET', 'utf8mb4');
// Security
define('CSRF_TOKEN_KEY', 'csrf_token');
define('SESSION_COOKIE_NAME', 'article_portal_sid');
// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'baonguyen.31231022406@st.ueh.edu.vn');
define('SMTP_PASSWORD', 'thrz spaq gkdb nejm'); // App Password for Gmail
define('SMTP_FROM_NAME', 'ArticleHub');
define('SMTP_PORT', 587);
// Upload paths
define('UPLOAD_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');
// Specific upload directories
define('ARTICLES_UPLOAD_PATH', UPLOAD_PATH . DIRECTORY_SEPARATOR . 'articles');
define('ARTICLES_UPLOAD_URL', UPLOAD_URL . '/articles');
