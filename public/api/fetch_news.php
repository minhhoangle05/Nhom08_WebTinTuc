<?php
// ✅ Tải toàn bộ môi trường ứng dụng
require_once __DIR__ . '/../../public/index.php'; 

use App\Controllers\AutoNewsController;

header('Content-Type: application/json; charset=utf-8');

$controller = new AutoNewsController();
$controller->fetchNews();
