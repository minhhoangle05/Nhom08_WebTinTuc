<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Comment extends Model
{
    public function countByArticle(int $articleId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM comments WHERE article_id = ? AND status = "approved"');
        $stmt->execute([$articleId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get comments for an article with nested structure
     */
    public function getByArticle(int $articleId): array
    {
        $stmt = $this->db->prepare('
            SELECT 
                c.*,
                u.name as user_name,
                u.email as user_email,
                cl.like_count,
                cl.dislike_count,
                cl.user_action
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN (
                SELECT 
                    comment_id,
                    SUM(CASE WHEN action = "like" THEN 1 ELSE 0 END) as like_count,
                    SUM(CASE WHEN action = "dislike" THEN 1 ELSE 0 END) as dislike_count,
                    MAX(CASE WHEN user_id = ? THEN action ELSE NULL END) as user_action
                FROM comment_likes 
                GROUP BY comment_id
            ) cl ON c.id = cl.comment_id
            WHERE c.article_id = ? AND c.status = "approved"
            ORDER BY c.created_at ASC
        ');
        
        $userId = \App\Core\Auth::check() ? \App\Core\Auth::user()['id'] : null;
        $stmt->execute([$userId, $articleId]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Build nested structure
        return $this->buildNestedStructure($comments);
    }

    /**
     * Build nested comment structure
     */
    private function buildNestedStructure(array $comments): array
    {
        $commentMap = [];
        $rootComments = [];
        
        // Create a map of all comments
        foreach ($comments as $comment) {
            $comment['replies'] = [];
            $comment['like_count'] = (int)($comment['like_count'] ?? 0);
            $comment['dislike_count'] = (int)($comment['dislike_count'] ?? 0);
            $commentMap[$comment['id']] = $comment;
        }
        
        // Build the tree structure
        foreach ($comments as $comment) {
            if ($comment['parent_id'] === null) {
                $rootComments[] = $commentMap[$comment['id']];
            } else {
                if (isset($commentMap[$comment['parent_id']])) {
                    $commentMap[$comment['parent_id']]['replies'][] = $commentMap[$comment['id']];
                }
            }
        }
        
        return $rootComments;
    }

    /**
     * Create a new comment
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO comments 
            (article_id, user_id, parent_id, content, status, created_at, likes, dislikes) 
            VALUES (?, ?, ?, ?, ?, NOW(), 0, 0)
        ');
        
        $stmt->execute([
            $data['article_id'],
            $data['user_id'],
            $data['parent_id'] ?? null,
            $data['content'],
            $data['status'] ?? 'approved'
        ]);
        
        return (int)$this->db->lastInsertId();
    }


    /**
     * Find comment by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM comments WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find comment by ID with user info
     */
    public function findByIdWithUser(int $id): ?array
    {
        $stmt = $this->db->prepare('
            SELECT 
                c.*,
                u.name as user_name,
                u.email as user_email
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.id = ?
        ');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Update comment
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $fields[] = "{$field} = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        
        $stmt = $this->db->prepare("UPDATE comments SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }

    /**
     * Delete comment
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Toggle like/dislike for a comment
     */
    public function toggleLike(int $commentId, int $userId, string $action): array
    {
        $this->db->beginTransaction();
        
        try {
            // Check if user already liked/disliked this comment
            $stmt = $this->db->prepare('
                SELECT action FROM comment_likes 
                WHERE comment_id = ? AND user_id = ?
            ');
            $stmt->execute([$commentId, $userId]);
            $existingAction = $stmt->fetchColumn();
            
            if ($existingAction === $action) {
                // Remove the like/dislike
                $stmt = $this->db->prepare('DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ? AND action = ?');
                $stmt->execute([$commentId, $userId, $action]);
                $newAction = null;
            } else {
                // Remove existing action if any
                if ($existingAction) {
                    $stmt = $this->db->prepare('DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?');
                    $stmt->execute([$commentId, $userId]);
                }
                
                // Add new action
                $stmt = $this->db->prepare('INSERT INTO comment_likes (comment_id, user_id, action) VALUES (?, ?, ?)');
                $stmt->execute([$commentId, $userId, $action]);
                $newAction = $action;
            }
            
            // Update comment counts
            $stmt = $this->db->prepare('
                UPDATE comments SET 
                    likes = (SELECT COUNT(*) FROM comment_likes WHERE comment_id = ? AND action = "like"),
                    dislikes = (SELECT COUNT(*) FROM comment_likes WHERE comment_id = ? AND action = "dislike")
                WHERE id = ?
            ');
            $stmt->execute([$commentId, $commentId, $commentId]);
            
            // Get updated counts
            $stmt = $this->db->prepare('SELECT likes, dislikes FROM comments WHERE id = ?');
            $stmt->execute([$commentId]);
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->db->commit();
            
            return [
                'action' => $newAction,
                'likes' => (int)$counts['likes'],
                'dislikes' => (int)$counts['dislikes']
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Report a comment
     */
    public function report(int $commentId, int $userId, string $reason, string $description = ''): int
    {
        // Check if user already reported this comment
        $stmt = $this->db->prepare('SELECT id FROM comment_reports WHERE comment_id = ? AND user_id = ?');
        $stmt->execute([$commentId, $userId]);
        
        if ($stmt->fetchColumn()) {
            throw new \Exception('Bạn đã báo cáo bình luận này rồi');
        }
        
        $stmt = $this->db->prepare('
            INSERT INTO comment_reports (comment_id, user_id, reason, description) 
            VALUES (?, ?, ?, ?)
        ');
        
        $stmt->execute([$commentId, $userId, $reason, $description]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Get comments for moderation (admin)
     */
    public function getForModeration(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare('
            SELECT 
                c.*,
                u.name as user_name,
                u.email as user_email,
                a.title as article_title,
                a.slug as article_slug,
                COUNT(cr.id) as report_count
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN articles a ON c.article_id = a.id
            LEFT JOIN comment_reports cr ON c.id = cr.comment_id AND cr.status = "pending"
            WHERE c.status IN ("pending", "rejected", "hidden")
            GROUP BY c.id
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?
        ');
        
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get comment reports for moderation
     */
    public function getReports(int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare('
            SELECT 
                cr.*,
                c.content as comment_content,
                c.created_at as comment_created_at,
                u1.name as reporter_name,
                u2.name as commenter_name,
                a.title as article_title,
                a.slug as article_slug
            FROM comment_reports cr
            LEFT JOIN comments c ON cr.comment_id = c.id
            LEFT JOIN users u1 ON cr.user_id = u1.id
            LEFT JOIN users u2 ON c.user_id = u2.id
            LEFT JOIN articles a ON c.article_id = a.id
            WHERE cr.status = "pending"
            ORDER BY cr.created_at DESC
            LIMIT ? OFFSET ?
        ');
        
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update report status
     */
    public function updateReportStatus(int $reportId, string $status, int $reviewedBy): bool
    {
        $stmt = $this->db->prepare('
            UPDATE comment_reports 
            SET status = ?, reviewed_at = NOW(), reviewed_by = ?
            WHERE id = ?
        ');
        
        return $stmt->execute([$status, $reviewedBy, $reportId]);
    }
}


