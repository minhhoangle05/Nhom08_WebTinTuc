<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\ActivityLogger;
use App\Models\Comment;
use App\Models\User;

class CommentController extends Controller
{
    private Comment $commentModel;
    private User $userModel;

    public function __construct()
    {
        $this->commentModel = new Comment();
        $this->userModel = new User();
    }

    /**
     * Get comments for an article (AJAX)
     */
    public function getComments(): void
    {
        $articleId = (int)($_GET['article_id'] ?? 0);
        
        if (!$articleId) {
            http_response_code(400);
            echo json_encode(['error' => 'Article ID is required']);
            return;
        }

        $comments = $this->commentModel->getByArticle($articleId);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'comments' => $comments
        ]);
    }

    /**
     * Create a new comment
     */
    public function create(): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Bạn cần đăng nhập để bình luận']);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['error' => 'Phiên làm việc đã hết hạn']);
            return;
        }

        $articleId = (int)($_POST['article_id'] ?? 0);
        $parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $content = trim($_POST['content'] ?? '');

        if (!$articleId || empty($content)) {
            http_response_code(400);
            echo json_encode(['error' => 'Thông tin không đầy đủ']);
            return;
        }

        if (strlen($content) < 3) {
            http_response_code(400);
            echo json_encode(['error' => 'Bình luận phải có ít nhất 3 ký tự']);
            return;
        }

        if (strlen($content) > 1000) {
            http_response_code(400);
            echo json_encode(['error' => 'Bình luận không được quá 1000 ký tự']);
            return;
        }

        // Check if parent comment exists and belongs to same article
        if ($parentId) {
            $parentComment = $this->commentModel->findById($parentId);
            if (!$parentComment || $parentComment['article_id'] != $articleId) {
                http_response_code(400);
                echo json_encode(['error' => 'Bình luận cha không hợp lệ']);
                return;
            }
        }

        $commentData = [
            'article_id' => $articleId,
            'user_id' => Auth::user()['id'],
            'parent_id' => $parentId,
            'content' => $content,
            'status' => 'approved' // Auto-approve for now, can be changed to 'pending' for moderation
        ];

        try {
            $commentId = $this->commentModel->create($commentData);
            
            if ($commentId) {
                ActivityLogger::log('comment_create', $commentId);
                
                // Get the created comment with user info
                $comment = $this->commentModel->findByIdWithUser($commentId);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Bình luận đã được thêm thành công',
                    'comment' => $comment
                ]);
            } else {
                throw new \Exception('Không thể tạo bình luận');
            }
            
        } catch (\Exception $e) {
            error_log("Error creating comment: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Có lỗi xảy ra khi tạo bình luận']);
        }
    }

    /**
     * Update a comment
     */
    public function update(): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Bạn cần đăng nhập']);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['error' => 'Phiên làm việc đã hết hạn']);
            return;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if (!$commentId || empty($content)) {
            http_response_code(400);
            echo json_encode(['error' => 'Thông tin không đầy đủ']);
            return;
        }

        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            echo json_encode(['error' => 'Bình luận không tồn tại']);
            return;
        }

        // Check permissions
        if (!Auth::isAdmin() && Auth::user()['id'] !== $comment['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Bạn không có quyền chỉnh sửa bình luận này']);
            return;
        }

        if (strlen($content) < 3 || strlen($content) > 1000) {
            http_response_code(400);
            echo json_encode(['error' => 'Nội dung bình luận không hợp lệ']);
            return;
        }

        try {
            if ($this->commentModel->update($commentId, ['content' => $content, 'is_edited' => true])) {
                ActivityLogger::log('comment_update', $commentId);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Bình luận đã được cập nhật'
                ]);
            } else {
                throw new \Exception('Không thể cập nhật bình luận');
            }
            
        } catch (\Exception $e) {
            error_log("Error updating comment: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Có lỗi xảy ra khi cập nhật bình luận']);
        }
    }

    /**
     * Delete a comment
     */
    public function delete(): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Bạn cần đăng nhập']);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['error' => 'Phiên làm việc đã hết hạn']);
            return;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);

        if (!$commentId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID bình luận không hợp lệ']);
            return;
        }

        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            echo json_encode(['error' => 'Bình luận không tồn tại']);
            return;
        }

        // Check permissions
        if (!Auth::isAdmin() && Auth::user()['id'] !== $comment['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Bạn không có quyền xóa bình luận này']);
            return;
        }

        try {
            if ($this->commentModel->delete($commentId)) {
                ActivityLogger::log('comment_delete', $commentId);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Bình luận đã được xóa'
                ]);
            } else {
                throw new \Exception('Không thể xóa bình luận');
            }
            
        } catch (\Exception $e) {
            error_log("Error deleting comment: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Có lỗi xảy ra khi xóa bình luận']);
        }
    }

    /**
     * Like or dislike a comment
     */
    public function toggleLike(): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Bạn cần đăng nhập']);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['error' => 'Phiên làm việc đã hết hạn']);
            return;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        $action = $_POST['action'] ?? ''; // 'like' or 'dislike'

        if (!$commentId || !in_array($action, ['like', 'dislike'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Thông tin không hợp lệ']);
            return;
        }

        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            echo json_encode(['error' => 'Bình luận không tồn tại']);
            return;
        }

        try {
            $result = $this->commentModel->toggleLike($commentId, Auth::user()['id'], $action);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'action' => $result['action'],
                'likes' => $result['likes'],
                'dislikes' => $result['dislikes']
            ]);
            
        } catch (\Exception $e) {
            error_log("Error toggling like: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Report a comment
     */
    public function report(): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Bạn cần đăng nhập']);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['error' => 'Phiên làm việc đã hết hạn']);
            return;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        $reason = $_POST['reason'] ?? '';
        $description = trim($_POST['description'] ?? '');

        if (!$commentId || !in_array($reason, ['spam', 'inappropriate', 'harassment', 'offensive', 'other'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Thông tin không hợp lệ']);
            return;
        }

        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            echo json_encode(['error' => 'Bình luận không tồn tại']);
            return;
        }

        // Can't report your own comment
        if ($comment['user_id'] === Auth::user()['id']) {
            http_response_code(400);
            echo json_encode(['error' => 'Bạn không thể báo cáo bình luận của chính mình']);
            return;
        }

        try {
            $reportId = $this->commentModel->report($commentId, Auth::user()['id'], $reason, $description);
            
            if ($reportId) {
                ActivityLogger::log('comment_report', $commentId);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Báo cáo đã được gửi thành công'
                ]);
            } else {
                throw new \Exception('Không thể tạo báo cáo');
            }
            
        } catch (\Exception $e) {
            error_log("Error reporting comment: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Có lỗi xảy ra khi gửi báo cáo']);
        }
    }

    /**
     * Admin moderation actions
     */
    public function moderate(): void
    {
        if (!Auth::check() || !Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Bạn không có quyền thực hiện hành động này']);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['error' => 'Phiên làm việc đã hết hạn']);
            return;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        $action = $_POST['action'] ?? ''; // 'approve', 'reject', 'hide'

        if (!$commentId || !in_array($action, ['approve', 'reject', 'hide'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Thông tin không hợp lệ']);
            return;
        }

        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            echo json_encode(['error' => 'Bình luận không tồn tại']);
            return;
        }

        $statusMap = [
            'approve' => 'approved',
            'reject' => 'rejected',
            'hide' => 'hidden'
        ];

        try {
            if ($this->commentModel->update($commentId, ['status' => $statusMap[$action]])) {
                ActivityLogger::log('comment_moderate', $commentId, "Admin {$action} comment");
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Hành động đã được thực hiện thành công'
                ]);
            } else {
                throw new \Exception('Không thể cập nhật trạng thái bình luận');
            }
            
        } catch (\Exception $e) {
            error_log("Error moderating comment: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Có lỗi xảy ra']);
        }
    }
}
