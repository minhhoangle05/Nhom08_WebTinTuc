-- Fix articles table to match the code requirements
USE article_portal;

-- Add missing columns if they don't exist
ALTER TABLE articles 
ADD COLUMN IF NOT EXISTS summary TEXT AFTER title,
ADD COLUMN IF NOT EXISTS featured_image VARCHAR(255) AFTER summary,
ADD COLUMN IF NOT EXISTS meta_description VARCHAR(255) AFTER featured_image,
ADD COLUMN IF NOT EXISTS meta_keywords VARCHAR(255) AFTER meta_description,
ADD COLUMN IF NOT EXISTS status ENUM('draft', 'published', 'private') NOT NULL DEFAULT 'published' AFTER is_featured,
ADD COLUMN IF NOT EXISTS publish_at TIMESTAMP NULL AFTER created_at,
ADD COLUMN IF NOT EXISTS reading_time INT DEFAULT 0 AFTER views,
ADD COLUMN IF NOT EXISTS allow_comments BOOLEAN DEFAULT TRUE AFTER is_featured;

-- Update existing articles to have published status
UPDATE articles SET status = 'published' WHERE status IS NULL OR status = '';

-- Create article_tags table if it doesn't exist
CREATE TABLE IF NOT EXISTS article_tags (
    article_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (article_id, tag_id),
    CONSTRAINT fk_article_tags_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    CONSTRAINT fk_article_tags_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insert some sample categories if they don't exist
INSERT IGNORE INTO categories (name, slug) VALUES
('Công nghệ', 'cong-nghe'),
('Kinh doanh', 'kinh-doanh'),
('Lối sống', 'loi-song'),
('Giáo dục', 'giao-duc'),
('Thể thao', 'the-thao');

-- Insert some sample tags if they don't exist
INSERT IGNORE INTO tags (name, slug) VALUES
('AI', 'ai'),
('Productivity', 'productivity'),
('Startup', 'startup'),
('Technology', 'technology'),
('Business', 'business'),
('Lifestyle', 'lifestyle');

SELECT 'Database updated successfully!' AS status;
