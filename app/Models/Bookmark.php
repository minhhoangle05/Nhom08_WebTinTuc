<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Bookmark extends Model
{
    /**
     * Kiểm tra xem người dùng đã lưu bài viết chưa
     */
    public function isBookmarked(int $userId, int $articleId): bool
    {
        $sql = "SELECT COUNT(*) FROM bookmarks 
                WHERE user_id = :user_id AND article_id = :article_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':article_id' => $articleId
        ]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Thêm bài viết vào danh sách yêu thích
     */
    public function add(int $userId, int $articleId): bool
{
    error_log("Bookmark::add - User: $userId, Article: $articleId");
    
    // Kiểm tra xem đã bookmark chưa
    if ($this->isBookmarked($userId, $articleId)) {
        error_log("Already bookmarked");
        return false;
    }
    
    $sql = "INSERT INTO bookmarks (user_id, article_id) 
            VALUES (:user_id, :article_id)";
    
    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute([
        ':user_id' => $userId,
        ':article_id' => $articleId
    ]);
    
    error_log("Insert result: " . ($result ? 'success' : 'failed'));
    
    if (!$result) {
        error_log("PDO Error: " . print_r($stmt->errorInfo(), true));
    }
    
    return $result;
}
    
    /**
     * Xóa bài viết khỏi danh sách yêu thích
     */
    public function remove(int $userId, int $articleId): bool
    {
        $sql = "DELETE FROM bookmarks 
                WHERE user_id = :user_id AND article_id = :article_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':user_id' => $userId,
            ':article_id' => $articleId
        ]);
    }
    
    /**
     * Toggle bookmark (thêm nếu chưa có, xóa nếu đã có)
     */
    public function toggle(int $userId, int $articleId): array
{
    error_log("Toggle bookmark - User: $userId, Article: $articleId");
    
    if ($this->isBookmarked($userId, $articleId)) {
        $success = $this->remove($userId, $articleId);
        error_log("Removed bookmark - Success: " . ($success ? 'true' : 'false'));
        return [
            'success' => $success,
            'action' => 'removed',
            'bookmarked' => false
        ];
    } else {
        $success = $this->add($userId, $articleId);
        error_log("Added bookmark - Success: " . ($success ? 'true' : 'false'));
        return [
            'success' => $success,
            'action' => 'added',
            'bookmarked' => true
        ];
    }
}
    
    /**
     * Lấy danh sách bài viết yêu thích của người dùng
     */
    public function getUserBookmarks(int $userId, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT 
                    a.id,
                    a.title,
                    a.slug,
                    a.summary,
                    a.featured_image,
                    a.views,
                    a.created_at,
                    u.name as author_name,
                    c.name as category_name,
                    c.slug as category_slug,
                    b.created_at as bookmarked_at,
                    (SELECT COUNT(*) FROM comments WHERE article_id = a.id) as comment_count
                FROM bookmarks b
                INNER JOIN articles a ON b.article_id = a.id
                INNER JOIN users u ON a.user_id = u.id
                LEFT JOIN categories c ON a.category_id = c.id
                WHERE b.user_id = :user_id
                ORDER BY b.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Đếm tổng số bài viết yêu thích của người dùng
     */
    public function countUserBookmarks(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM bookmarks WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Lấy danh sách ID bài viết đã bookmark của người dùng
     */
    public function getUserBookmarkIds(int $userId): array
    {
        $sql = "SELECT article_id FROM bookmarks WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Xóa tất cả bookmark của một bài viết (khi xóa bài viết)
     */
    public function deleteByArticle(int $articleId): bool
    {
        $sql = "DELETE FROM bookmarks WHERE article_id = :article_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':article_id' => $articleId]);
    }
    
    /**
     * Lấy số lượng người dùng đã bookmark một bài viết
     */
    public function getBookmarkCount(int $articleId): int
    {
        $sql = "SELECT COUNT(*) FROM bookmarks WHERE article_id = :article_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':article_id' => $articleId]);
        
        return (int) $stmt->fetchColumn();
    }
}