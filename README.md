ArticleHub - Hệ thống Tin tức Tự động tích hợp AI

ArticleHub là nền tảng đọc báo thông minh, tự động thu thập tin tức từ các nguồn chính thống (RSS) và sử dụng Trí tuệ nhân tạo (Google Gemini) để tóm tắt nội dung, giúp người dùng nắm bắt thông tin nhanh chóng.

🚀 Tính năng nổi bật

1. Dành cho Người đọc (User)

Đọc báo thông minh: Giao diện sạch, tốc độ cao.

AI Tóm tắt: Xem nội dung chính chỉ trong 3 dòng (được xử lý bởi Gemini Pro).

Tương tác: Bình luận, Yêu thích, Tìm kiếm bài viết.

2. Dành cho Quản trị viên (Admin)

CMS mạnh mẽ: Soạn thảo bài viết, Quản lý danh mục/Tags.

AutoNews Crawler: Hệ thống tự động quét tin từ RSS (VnExpress, Dân Trí...).

Dashboard: Thống kê lượt xem, hoạt động người dùng trực quan.

3. Kỹ thuật & Bảo mật

Hybrid AI: Tự động chuyển sang thuật toán tóm tắt nội bộ (Local) nếu API Google lỗi.

Bảo mật: Chống SQL Injection, XSS, CSRF Protection.

🛠 Công nghệ sử dụng

Ngôn ngữ: PHP 8.0 (Native MVC Pattern).

Database: MySQL 8.0.

Frontend: Bootstrap 5, Vanilla JS.

AI Service: Google Gemini API.

Server: Apache / Nginx.

⚙️ Hướng dẫn Cài đặt

Yêu cầu hệ thống

PHP >= 8.0

MySQL >= 5.7

Composer (Tùy chọn)

Bước 1: Clone dự án

git clone [https://github.com/username/ArticleHub.git](https://github.com/username/ArticleHub.git)
cd ArticleHub


Bước 2: Cấu hình Database

Tạo cơ sở dữ liệu mới trong MySQL (ví dụ: article_portal).

Import file scripts/article_portal.sql vào database vừa tạo.

Bước 3: Cấu hình Môi trường

Mở file app/config/config.php và cập nhật thông tin:

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'article_portal');
define('DB_USER', 'root');
define('DB_PASS', '');

// AI API Key
define('GEMINI_API_KEY', 'YOUR_GOOGLE_API_KEY');

// Email SMTP (Gmail App Password)
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');


Bước 4: Phân quyền thư mục

Đảm bảo thư mục upload có quyền ghi để lưu ảnh từ Crawler:

chmod -R 755 public/uploads/articles


🤖 Cách vận hành Crawler

Để kích hoạt tính năng lấy tin tự động, bạn có thể chạy thủ công hoặc cài đặt Cronjob:

Chạy thủ công (trên trình duyệt):
Truy cập: http://your-domain.com/admin/autonews/fetch

Cài đặt Cronjob (Linux):

# Chạy mỗi 30 phút
*/30 * * * * /usr/bin/php /path/to/project/scripts/crawler.php


📂 Cấu trúc thư mục

ArticleHub/
├── app/
│   ├── Controllers/   # Logic xử lý (Auth, Article, API...)
│   ├── Models/        # Tương tác Database
│   └── Views/         # Giao diện (Layouts, Partials)
├── config/            # File cấu hình hệ thống
├── public/            # Assets (CSS, JS, Images) & index.php
└── scripts/           # SQL dump & Crawler scripts


👨‍💻 Tác giả

Nguyễn Gia Bảo - Lead Developer & System Architect

Team: [Tên các thành viên khác nếu có]

Dự án được phát triển như một đồ án môn học và đã được triển khai thực tế.
