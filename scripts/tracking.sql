-- Article views tracking
CREATE TABLE IF NOT EXISTS article_views (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    user_id INT NULL, -- NULL for anonymous views
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_views_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    CONSTRAINT fk_views_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_article_views_date (viewed_at)
) ENGINE=InnoDB;

-- User activity logging
CREATE TABLE IF NOT EXISTS user_activities (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type ENUM('login', 'logout', 'article_view', 'article_create', 'article_edit', 'article_delete', 'comment_create', 'comment_delete') NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255),
    reference_id INT NULL, -- Article ID or Comment ID if applicable
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_activities_date (created_at)
) ENGINE=InnoDB;