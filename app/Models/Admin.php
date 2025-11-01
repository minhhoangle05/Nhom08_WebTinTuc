<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Admin extends Model
{
    /**
     * ========== DASHBOARD STATISTICS ==========
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

    public function getCategoryStatistics(): array
    {
        $stmt = $this->db->query('
            SELECT c.name, 
                   COUNT(a.id) as article_count, 
                   COALESCE(SUM(a.views), 0) as total_views
            FROM categories c
            LEFT JOIN articles a ON c.id = a.category_id
            GROUP BY c.id, c.name
            ORDER BY article_count DESC
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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
    
    public function getUsers(int $limit = 20, int $offset = 0, ?string $search = null, ?int $roleId = null): array
    {
        $sql = '
            SELECT u.*, r.name as role_name,
                   (SELECT COUNT(*) FROM articles WHERE user_id = u.id) as article_count,
                   (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comment_count
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE 1=1
        ';
        
        $params = [];
        
        if ($search) {
            $sql .= ' AND (u.name LIKE ? OR u.email LIKE ?)';
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($roleId) {
            $sql .= ' AND u.role_id = ?';
            $params[] = $roleId;
        }
        
        $sql .= ' ORDER BY u.created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUsers(?string $search = null, ?int $roleId = null): int
    {
        $sql = 'SELECT COUNT(*) FROM users WHERE 1=1';
        $params = [];
        
        if ($search) {
            $sql .= ' AND (name LIKE ? OR email LIKE ?)';
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if ($roleId) {
            $sql .= ' AND role_id = ?';
            $params[] = $roleId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getUserDetail(int $userId): ?array
    {
        $stmt = $this->db->prepare('
            SELECT u.*, r.name as role_name,
                   (SELECT COUNT(*) FROM articles WHERE user_id = u.id) as article_count,
                   (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comment_count,
                   (SELECT SUM(views) FROM articles WHERE user_id = u.id) as total_views
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
        ');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

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

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function createUser(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO users (name, email, password, role_id, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ');
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['password'],
            $data['role_id']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateUserRole(int $userId, int $roleId): bool
    {
        $stmt = $this->db->prepare('UPDATE users SET role_id = ? WHERE id = ?');
        return $stmt->execute([$roleId, $userId]);
    }

    public function deleteUser(int $userId): bool
    {
        // Xóa các bản ghi liên quan
        $this->db->prepare('DELETE FROM user_activities WHERE user_id = ?')->execute([$userId]);
        $this->db->prepare('DELETE FROM comments WHERE user_id = ?')->execute([$userId]);
        $this->db->prepare('DELETE FROM remember_tokens WHERE user_id = ?')->execute([$userId]);
        $this->db->prepare('DELETE FROM article_views WHERE user_id = ?')->execute([$userId]);
        
        // Xóa user
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$userId]);
    }

    /**
     * ========== COMMENT MANAGEMENT ==========
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

    public function countComments(): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM comments')->fetchColumn();
    }

    public function deleteComment(int $commentId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id = ?');
        return $stmt->execute([$commentId]);
    }

    /**
     * ========== ARTICLE STATISTICS ==========
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
              AND viewed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(viewed_at)
            ORDER BY date DESC
        ');
        $stmt->execute([$articleId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thống kê chi tiết lượt xem với filter
     */
    public function getDetailedViewStatistics(int $days = 7, array $filters = []): array
    {
        $sql = '
            SELECT 
                a.id,
                a.title,
                a.slug,
                a.views as total_views,
                u.name as author_name,
                c.name as category_name,
                a.created_at,
                COUNT(DISTINCT av.id) as recent_views,
                COUNT(DISTINCT av.ip_address) as unique_visitors,
                COUNT(DISTINCT av.user_id) as logged_in_views
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN article_views av ON a.id = av.article_id 
                AND av.viewed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            WHERE 1=1
        ';
        
        $params = [$days];
        
        if (!empty($filters['article_id'])) {
            $sql .= ' AND a.id = ?';
            $params[] = $filters['article_id'];
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= ' AND a.user_id = ?';
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= ' AND a.created_at >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= ' AND a.created_at <= ?';
            $params[] = $filters['date_to'];
        }
        
        $sql .= ' GROUP BY a.id, a.title, a.slug, a.views, u.name, c.name, a.created_at';
        
        // Sorting
        $sort = $filters['sort'] ?? 'views';
        switch ($sort) {
            case 'views':
                $sql .= ' ORDER BY a.views DESC';
                break;
            case 'recent_views':
                $sql .= ' ORDER BY recent_views DESC';
                break;
            case 'date':
                $sql .= ' ORDER BY a.created_at DESC';
                break;
            case 'title':
                $sql .= ' ORDER BY a.title ASC';
                break;
            default:
                $sql .= ' ORDER BY a.views DESC';
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách tác giả (users có bài viết)
     */
    public function getAllAuthors(): array
    {
        $stmt = $this->db->query('
            SELECT DISTINCT u.id, u.name, COUNT(a.id) as article_count
            FROM users u
            INNER JOIN articles a ON u.id = a.user_id
            GROUP BY u.id, u.name
            ORDER BY u.name
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ========== TAG MANAGEMENT ==========
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