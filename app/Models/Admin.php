<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Admin extends Model
{
    /**
     * ========== DASHBOARD STATISTICS ==========
     */
    
    /**
     * Lấy thống kê tổng quan
     */
    public function getDashboardStats(): array
    {
        return [
            'articles' => (int)$this->db->query('SELECT COUNT(*) FROM articles')->fetchColumn(),
            'users' => (int)$this->db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'comments' => (int)$this->db->query('SELECT COUNT(*) FROM comments')->fetchColumn(),
            'total_views' => (int)$this->db->query('SELECT SUM(views) FROM articles')->fetchColumn()
        ];
    }

    /**
     * Lấy thống kê lượt xem theo ngày (7 ngày gần nhất)
     */
    public function getViewStatistics(int $days = 7): array
    {
        $stmt = $this->db->prepare('
            SELECT 
                DATE(viewed_at) as date,
                COUNT(*) as view_count,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT ip_address) as unique_ips
            FROM article_views
            WHERE viewed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(viewed_at)
            ORDER BY date DESC
        ');
        $stmt->execute([$days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy hoạt động người dùng gần đây
     */
    public function getRecentActivities(int $limit = 10): array
    {
        $stmt = $this->db->prepare('
            SELECT 
                ua.*,
                u.name as user_name,
                CASE 
                    WHEN ua.activity_type IN ("article_view", "article_create", "article_edit", "article_delete") 
                    THEN (SELECT title FROM articles WHERE id = ua.reference_id)
                    ELSE NULL
                END as article_title
            FROM user_activities ua
            JOIN users u ON ua.user_id = u.id
            ORDER BY ua.created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy bài viết mới nhất
     */
    public function getLatestArticles(int $limit = 5): array
    {
        $stmt = $this->db->prepare('
            SELECT a.*, u.name as author_name, c.name as category_name 
            FROM articles a 
            LEFT JOIN users u ON a.user_id = u.id 
            LEFT JOIN categories c ON a.category_id = c.id 
            ORDER BY a.created_at DESC 
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy người dùng mới nhất
     */
    public function getLatestUsers(int $limit = 5): array
    {
        $stmt = $this->db->prepare('
            SELECT u.*, r.name as role_name,
                   (SELECT COUNT(*) FROM articles WHERE user_id = u.id) as article_count
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY u.created_at DESC 
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thống kê theo danh mục
     */
    public function getCategoryStatistics(): array
    {
        $stmt = $this->db->query('
            SELECT c.name, 
                   COUNT(a.id) as article_count, 
                   SUM(a.views) as total_views
            FROM categories c
            LEFT JOIN articles a ON c.id = a.category_id
            GROUP BY c.id, c.name
            ORDER BY article_count DESC
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy bài viết xem nhiều nhất
     */
    public function getPopularArticles(int $limit = 5): array
    {
        $stmt = $this->db->prepare('
            SELECT a.*, u.name as author_name, c.name as category_name
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            ORDER BY a.views DESC
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ========== USER MANAGEMENT ==========
     */
    
    /**
     * Lấy danh sách users với phân trang
     */
    public function getUsers(int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare('
            SELECT u.*, r.name as role_name,
                   (SELECT COUNT(*) FROM articles WHERE user_id = u.id) as article_count,
                   (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comment_count
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?
        ');
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm tổng số users
     */
    public function countUsers(): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    /**
     * Lấy thông tin chi tiết user
     */
    public function getUserDetail(int $userId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.*, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
        ');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Lấy hoạt động của user
     */
    public function getUserActivities(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->prepare('
            SELECT ua.*, 
                   CASE 
                       WHEN ua.activity_type IN ("article_view", "article_create", "article_edit", "article_delete") 
                       THEN (SELECT title FROM articles WHERE id = ua.reference_id)
                       ELSE NULL
                   END as article_title
            FROM user_activities ua
            WHERE ua.user_id = ?
            ORDER BY ua.created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Xóa user và dữ liệu liên quan
     */
    public function deleteUser(int $userId): bool
    {
        // Xóa các bản ghi liên quan
        $this->db->prepare('DELETE FROM user_activities WHERE user_id = ?')->execute([$userId]);
        $this->db->prepare('DELETE FROM comments WHERE user_id = ?')->execute([$userId]);
        $this->db->prepare('DELETE FROM remember_tokens WHERE user_id = ?')->execute([$userId]);
        $this->db->prepare('DELETE FROM bookmarks WHERE user_id = ?')->execute([$userId]);
        
        // Xóa user
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$userId]);
    }

    /**
     * ========== COMMENT MANAGEMENT ==========
     */
    
    /**
     * Lấy danh sách comments với phân trang
     */
    public function getComments(int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare('
            SELECT c.*, 
                   u.name as user_name, 
                   a.title as article_title, 
                   a.slug as article_slug
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN articles a ON c.article_id = a.id
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?
        ');
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm tổng số comments
     */
    public function countComments(): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM comments')->fetchColumn();
    }

    /**
     * Xóa comment
     */
    public function deleteComment(int $commentId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id = ?');
        return $stmt->execute([$commentId]);
    }

    /**
     * ========== ARTICLE STATISTICS ==========
     */
    
    /**
     * Lấy thống kê view của một bài viết
     */
    public function getArticleViewHistory(int $articleId, int $days = 30): array
    {
        $stmt = $this->db->prepare('
            SELECT 
                DATE(viewed_at) as date,
                COUNT(*) as views,
                COUNT(DISTINCT ip_address) as unique_views
            FROM article_views
            WHERE article_id = ?
            GROUP BY DATE(viewed_at)
            ORDER BY date DESC
            LIMIT ?
        ');
        $stmt->execute([$articleId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * ========== TAG MANAGEMENT ==========
     */
    
    /**
     * Lấy tất cả tags với số lượng bài viết
     */
    public function getAllTagsWithCount(): array
    {
        $stmt = $this->db->query('
            SELECT t.*, COUNT(at.article_id) as article_count
            FROM tags t
            LEFT JOIN article_tags at ON t.id = at.tag_id
            GROUP BY t.id
            ORDER BY article_count DESC, t.name
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}