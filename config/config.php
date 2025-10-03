<?php
// App configuration for XAMPP environment

// Base URL (adjust if your folder name under htdocs is different)
$appFolder = basename(dirname(__DIR__));
define('BASE_URL', '/' . $appFolder . '/public');

// Database credentials
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'article_portal');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP default is empty
define('DB_CHARSET', 'utf8mb4');

// Security
define('CSRF_TOKEN_KEY', 'csrf_token');
define('SESSION_COOKIE_NAME', 'article_portal_sid');


