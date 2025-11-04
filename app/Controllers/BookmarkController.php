<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

class BookmarkController extends Controller
{
    private $bookmarkModel;
    
    public function __construct()
    {
        // Khởi tạo model ngay từ đầu
        $this->bookmarkModel = $this->model('Bookmark');
        
        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            // Nếu là AJAX request
            if ($this->isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để sử dụng tính năng này',
                    'redirect' => BASE_URL . '/auth/login'
                ]);
                exit;
            }
            
            // Nếu là request thường
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }
    }
    
    /**
     * Kiểm tra xem có phải AJAX request không
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Hiển thị danh sách bài viết yêu thích
     */
    public function index(): void
    {
        $userId = Auth::id();
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 12;
        $offset = ($page - 1) * $perPage;
        
        // Lấy danh sách bookmarks
        $bookmarks = $this->bookmarkModel->getUserBookmarks($userId, $perPage, $offset);
        
        // Đếm tổng số
        $total = $this->bookmarkModel->countUserBookmarks($userId);
        $totalPages = ceil($total / $perPage);
        
        $this->view('bookmarks/index', [
            'title' => 'Bài viết yêu thích',
            'bookmarks' => $bookmarks,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }
    
    /**
     * Toggle bookmark (AJAX)
     */
    public function toggle(): void
    {
        // Disable error display và chỉ output JSON
        @ini_set('display_errors', 0);
        
        // Set headers trước khi output bất cứ thứ gì
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
        
        // Clear any output buffer
        if (ob_get_length()) ob_clean();
        
        try {
            // Log request
            error_log('Bookmark toggle called - Method: ' . $_SERVER['REQUEST_METHOD']);
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'message' => 'Method not allowed'
                ]);
                exit;
            }
            
            // Lấy article_id từ POST data
            $input = file_get_contents('php://input');
            error_log('Raw input: ' . $input);
            
            $data = json_decode($input, true);
            
            // Fallback cho trường hợp không phải JSON
            if (!$data) {
                $articleId = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;
            } else {
                $articleId = isset($data['article_id']) ? (int)$data['article_id'] : 0;
            }
            
            error_log('Article ID: ' . $articleId);
            
            if (!$articleId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu thông tin bài viết'
                ]);
                exit;
            }
            
            $userId = Auth::id();
            error_log('User ID: ' . $userId);
            
            // Kiểm tra bài viết có tồn tại không
            $articleModel = $this->model('Article');
            $article = $articleModel->findById($articleId);
            
            if (!$article) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Bài viết không tồn tại'
                ]);
                exit;
            }
            
            // Toggle bookmark
            $result = $this->bookmarkModel->toggle($userId, $articleId);
            
            if ($result['success']) {
                $message = $result['action'] === 'added' 
                    ? 'Đã thêm vào danh sách yêu thích' 
                    : 'Đã xóa khỏi danh sách yêu thích';
                
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => $message,
                    'bookmarked' => $result['bookmarked'],
                    'count' => $this->bookmarkModel->getBookmarkCount($articleId)
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra, vui lòng thử lại'
                ]);
            }
        } catch (\Exception $e) {
            error_log('Bookmark toggle error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi hệ thống',
                'debug' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
        exit;
    }
    
    /**
     * Thêm bookmark
     */
    public function add(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $articleId = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;
        $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : BASE_URL;
        
        if ($articleId) {
            $userId = Auth::id();
            
            try {
                $result = $this->bookmarkModel->add($userId, $articleId);
                
                if ($result) {
                    $_SESSION['flash_success'] = 'Đã thêm vào danh sách yêu thích';
                } else {
                    $_SESSION['flash_info'] = 'Bài viết đã có trong danh sách yêu thích';
                }
            } catch (\Exception $e) {
                error_log('Bookmark add error: ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
            }
        }
        
        header('Location: ' . $redirect);
        exit;
    }
    
    /**
     * Xóa bookmark
     */
    public function remove(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $articleId = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;
        $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : BASE_URL . '/bookmarks';
        
        if ($articleId) {
            $userId = Auth::id();
            
            try {
                $this->bookmarkModel->remove($userId, $articleId);
                $_SESSION['flash_success'] = 'Đã xóa khỏi danh sách yêu thích';
            } catch (\Exception $e) {
                error_log('Bookmark remove error: ' . $e->getMessage());
                $_SESSION['flash_error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
            }
        }
        
        header('Location: ' . $redirect);
        exit;
    }
    
    /**
     * Kiểm tra trạng thái bookmark (AJAX)
     */
    public function check(): void
    {
        @ini_set('display_errors', 0);
        
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        
        if (ob_get_length()) ob_clean();

        try {
            if (!Auth::check()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
                exit;
            }

            $articleId = isset($_GET['article_id']) ? (int)$_GET['article_id'] : 0;
            if (!$articleId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bài viết']);
                exit;
            }

            $userId = Auth::id();
            $isBookmarked = $this->bookmarkModel->isBookmarked($userId, $articleId);
            $count = $this->bookmarkModel->getBookmarkCount($articleId);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'bookmarked' => $isBookmarked,
                'count' => $count
            ]);
        } catch (\Throwable $e) {
            error_log('[Bookmark Check Error] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
        }
        exit;
    }
}