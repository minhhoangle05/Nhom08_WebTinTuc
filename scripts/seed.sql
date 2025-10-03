-- Seed demo data directly via SQL
-- Usage:
--   1) Import scripts/database.sql first (to create schema)
--   2) Run this file in phpMyAdmin SQL tab or:
--      mysql -u root -p < scripts/seed.sql

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Adjust if your DB name is different
USE `article_portal`;

-- Ensure base roles exist
INSERT IGNORE INTO roles (name) VALUES ('user'), ('editor'), ('admin');

-- Upsert demo users (password hash corresponds to password: user123)
-- You can change hash later; this is a bcrypt $2y$ hash
SET @hash := '$2y$10$2gH0mB9VwGQz0QKk7s3QWuQy6VhKMpa8t0XKy1kI8s2lYo0a2bK9K';

INSERT INTO users (name, email, password_hash, role_id)
VALUES 
  ('Demo User', 'user@example.com', @hash, (SELECT id FROM roles WHERE name='user' LIMIT 1)),
  ('Editor Jane', 'editor@example.com', @hash, (SELECT id FROM roles WHERE name='editor' LIMIT 1))
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  password_hash = VALUES(password_hash),
  role_id = VALUES(role_id);

-- Capture user IDs
SELECT id INTO @user_id FROM users WHERE email='user@example.com' LIMIT 1;
SELECT id INTO @editor_id FROM users WHERE email='editor@example.com' LIMIT 1;

-- Upsert categories
INSERT INTO categories (name, slug) VALUES
  ('Công nghệ', 'cong-nghe'),
  ('Kinh doanh', 'kinh-doanh'),
  ('Lối sống', 'loi-song')
ON DUPLICATE KEY UPDATE
  name = VALUES(name);

-- Capture category IDs
SELECT id INTO @cat_tech FROM categories WHERE slug='cong-nghe' LIMIT 1;
SELECT id INTO @cat_biz FROM categories WHERE slug='kinh-doanh' LIMIT 1;
SELECT id INTO @cat_life FROM categories WHERE slug='loi-song' LIMIT 1;

-- Upsert sample articles
INSERT INTO articles (user_id, category_id, title, slug, content, views, is_featured, created_at)
VALUES
  (@editor_id, @cat_tech, 'Những xu hướng công nghệ năm nay bạn cần biết', 'nhung-xu-huong-cong-nghe-nam-nay-ban-can-biet',
   'Bài viết tổng hợp các xu hướng công nghệ nổi bật như AI, Edge, 5G... với ví dụ thực tế và tác động đến doanh nghiệp.', 123, 1, NOW()),
  (@user_id, @cat_biz, 'Bí quyết tăng trưởng doanh thu cho startup giai đoạn đầu', 'bi-quyet-tang-truong-doanh-thu-cho-startup-giai-doan-dau',
   'Tập trung vào sản phẩm lõi, kênh phân phối hiệu quả và tối ưu chi phí vận hành là chìa khóa...', 89, 1, NOW()),
  (@user_id, @cat_life, '7 thói quen lành mạnh giúp nâng cao chất lượng cuộc sống', '7-thoi-quen-lanh-manh-giup-nang-cao-chat-luong-cuoc-song',
   'Giấc ngủ, dinh dưỡng, vận động, thiền định và quản lý thời gian là những điểm mấu chốt...', 55, 0, NOW())
ON DUPLICATE KEY UPDATE
  title = VALUES(title),
  content = VALUES(content),
  views = VALUES(views),
  is_featured = VALUES(is_featured);

-- Optional: a few tags
INSERT INTO tags (name, slug) VALUES
  ('AI', 'ai'),
  ('Productivity', 'productivity'),
  ('Startup', 'startup')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Link tags to first article if exists
SELECT id INTO @first_article FROM articles WHERE slug='nhung-xu-huong-cong-nghe-nam-nay-ban-can-biet' LIMIT 1;
SELECT id INTO @tag_ai FROM tags WHERE slug='ai' LIMIT 1;
INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (@first_article, @tag_ai);

-- Done
SELECT 'Seed SQL executed successfully' AS status;


