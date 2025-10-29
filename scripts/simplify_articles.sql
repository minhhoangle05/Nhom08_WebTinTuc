-- Script để đơn giản hóa bảng articles
-- Loại bỏ các tính năng phức tạp, chỉ giữ lại những gì cần thiết

USE article_portal;

-- Backup dữ liệu hiện tại (nếu có)
CREATE TABLE IF NOT EXISTS articles_backup AS SELECT * FROM articles;

-- Xóa các cột không cần thiết
ALTER TABLE articles 
DROP COLUMN IF EXISTS status,
DROP COLUMN IF EXISTS publish_at,
DROP COLUMN IF EXISTS reading_time,
DROP COLUMN IF EXISTS allow_comments,
DROP COLUMN IF EXISTS meta_description,
DROP COLUMN IF EXISTS meta_keywords,
DROP COLUMN IF EXISTS is_featured;

-- Đảm bảo các cột cần thiết tồn tại
ALTER TABLE articles 
ADD COLUMN IF NOT EXISTS summary TEXT AFTER title,
ADD COLUMN IF NOT EXISTS featured_image VARCHAR(255) AFTER summary;

-- Cập nhật tất cả bài viết hiện tại thành published (nếu có)
-- Không cần status nữa, tất cả bài viết đều là published

-- Đảm bảo bảng article_tags tồn tại
CREATE TABLE IF NOT EXISTS article_tags (
    article_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (article_id, tag_id),
    CONSTRAINT fk_article_tags_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    CONSTRAINT fk_article_tags_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Thêm dữ liệu mẫu nếu chưa có
INSERT IGNORE INTO categories (name, slug) VALUES
('Công nghệ', 'cong-nghe'),
('Kinh doanh', 'kinh-doanh'),
('Lối sống', 'loi-song'),
('Giáo dục', 'giao-duc'),
('Thể thao', 'the-thao'),
('Du lịch', 'du-lich'),
('Ẩm thực', 'am-thuc');

INSERT IGNORE INTO tags (name, slug) VALUES
('AI', 'ai'),
('Công nghệ', 'cong-nghe'),
('Startup', 'startup'),
('Kinh doanh', 'kinh-doanh'),
('Lối sống', 'loi-song'),
('Giáo dục', 'giao-duc'),
('Thể thao', 'the-thao'),
('Du lịch', 'du-lich'),
('Ẩm thực', 'am-thuc'),
('Tin tức', 'tin-tuc');

-- Xóa bảng drafts nếu có (không cần nữa)
DROP TABLE IF EXISTS article_drafts;

SELECT 'Database simplified successfully! Articles table now has only essential columns.' AS status;
