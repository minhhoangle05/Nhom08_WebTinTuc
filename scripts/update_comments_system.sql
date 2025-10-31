-- Update comments system for nested comments and moderation
USE article_portal;

-- Add missing columns to comments table for nested comments
ALTER TABLE comments 
ADD COLUMN IF NOT EXISTS parent_id INT NULL AFTER user_id,
ADD COLUMN IF NOT EXISTS status ENUM('pending', 'approved', 'rejected', 'hidden') NOT NULL DEFAULT 'approved' AFTER dislikes,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
ADD COLUMN IF NOT EXISTS is_edited BOOLEAN DEFAULT FALSE AFTER status;

-- Add foreign key for parent_id (self-referencing)
ALTER TABLE comments 
ADD CONSTRAINT fk_comments_parent FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE;

-- Create comment_likes table for tracking user likes/dislikes
CREATE TABLE IF NOT EXISTS comment_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    action ENUM('like', 'dislike') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_comment_action (user_id, comment_id, action),
    CONSTRAINT fk_comment_likes_comment FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_likes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create comment_reports table for moderation
CREATE TABLE IF NOT EXISTS comment_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    comment_id INT NOT NULL,
    user_id INT NOT NULL,
    reason ENUM('spam', 'inappropriate', 'harassment', 'offensive', 'other') NOT NULL,
    description TEXT,
    status ENUM('pending', 'reviewed', 'resolved') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by INT NULL,
    CONSTRAINT fk_comment_reports_comment FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_reports_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_reports_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_comments_article_parent ON comments(article_id, parent_id);
CREATE INDEX IF NOT EXISTS idx_comments_status ON comments(status);
CREATE INDEX IF NOT EXISTS idx_comments_created_at ON comments(created_at);
CREATE INDEX IF NOT EXISTS idx_comment_likes_comment ON comment_likes(comment_id);
CREATE INDEX IF NOT EXISTS idx_comment_reports_status ON comment_reports(status);

-- Update existing comments to have approved status
UPDATE comments SET status = 'approved' WHERE status IS NULL OR status = '';

SELECT 'Comments system updated successfully!' AS status;
