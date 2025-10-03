-- Thêm các trường mới cho bảng articles
ALTER TABLE articles
ADD COLUMN summary TEXT AFTER title,
ADD COLUMN featured_image VARCHAR(255) AFTER summary,
ADD COLUMN meta_description VARCHAR(255) AFTER featured_image,
ADD COLUMN meta_keywords VARCHAR(255) AFTER meta_description,
ADD COLUMN status ENUM('draft', 'published', 'private') NOT NULL DEFAULT 'draft' AFTER is_featured,
ADD COLUMN publish_at TIMESTAMP NULL AFTER created_at,
ADD COLUMN reading_time INT DEFAULT 0 AFTER views,
ADD COLUMN allow_comments BOOLEAN DEFAULT TRUE AFTER is_featured;

-- Bảng lưu bản nháp
CREATE TABLE IF NOT EXISTS article_drafts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NULL,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    summary TEXT,
    content MEDIUMTEXT NOT NULL,
    category_id INT NULL,
    featured_image VARCHAR(255),
    meta_description VARCHAR(255),
    meta_keywords VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_drafts_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE SET NULL,
    CONSTRAINT fk_drafts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_drafts_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Bảng lưu tags cho bài viết
CREATE TABLE IF NOT EXISTS article_tags (
    article_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (article_id, tag_id),
    CONSTRAINT fk_article_tags_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    CONSTRAINT fk_article_tags_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;