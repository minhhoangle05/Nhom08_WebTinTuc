<?php
// App configuration for XAMPP environment

// Base URL
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_URL', rtrim($scriptDir, '/'));

// Database credentials
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'article_portal');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP default is empty
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

