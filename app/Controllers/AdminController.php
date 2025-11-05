<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\ActivityLogger;
use App\Models\Admin;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Comment;

class AdminController extends Controller
{
    private Admin $adminModel;

    public function __construct()
    {
        $this->adminModel = new Admin();
    }

    private function checkAdminAccess(): bool
    {
        if (!Auth::check() || !Auth::isAdmin()) {
            http_response_code(403);
            echo 'Forbidden - Admin access required';
            return false;
        }
        return true;
    }

    // ========== DASHBOARD ==========
    public function index(): void
    {
        if (!$this->checkAdminAccess()) return;

        $this->view('admin/dashboard', [
            'title' => 'Bảng điều khiển',
            'stats' => $this->adminModel->getDashboardStats(),
            'viewStats' => $this->adminModel->getViewStatistics(7),
            'recentActivities' => $this->adminModel->getRecentActivities(10),
            'latestArticles' => $this->adminModel->getLatestArticles(5),
            'latestUsers' => $this->adminModel->getLatestUsers(5),
            'categoryStats' => $this->adminModel->getCategoryStatistics(),
            'popularArticles' => $this->adminModel->getPopularArticles(5),
            'csrf' => CSRF::token()
        ]);
    }

    // ========== ARTICLES MANAGEMENT ==========
    public function articles(): void
    {
        if (!$this->checkAdminAccess()) return;

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $articleModel = new Article();
        $articles = $articleModel->search(['status' => 'all'], $limit, $offset);
        $total = $articleModel->countSearch(['status' => 'all']);
        $totalPages = ceil($total / $limit);

        $this->view('admin/articles/index', [
            'title' => 'Quản lý bài viết',
            'articles' => $articles,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'csrf' => CSRF::token()
        ]);
    }

    public function articleDetail(string $id): void
    {
        if (!$this->checkAdminAccess()) return;

        $articleModel = new Article();
        $article = $articleModel->findById((int)$id);
        
        if (!$article) {
            http_response_code(404);
            echo 'Bài viết không tồn tại';
            return;
        }

        $viewHistory = $this->adminModel->getArticleViewHistory((int)$id, 30);
        $tags = $articleModel->getArticleTags((int)$id);

        $this->view('admin/articles/detail', [
            'title' => 'Chi tiết bài viết - ' . $article['title'],
            'article' => $article,
            'tags' => $tags,
            'viewHistory' => $viewHistory,
            'csrf' => CSRF::token()
        ]);
    }

    public function deleteArticle(string $id): void
    {
        if (!$this->checkAdminAccess()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $articleModel = new Article();
        $article = $articleModel->findById((int)$id);
        
        if (!$article) {
            http_response_code(404);
            echo 'Bài viết không tồn tại';
            return;
        }

        if ($articleModel->delete((int)$id)) {
            ActivityLogger::log('article_delete', (int)$id);
            header('Location: ' . BASE_URL . '/admin/articles?success=deleted');
        } else {
            http_response_code(500);
            echo 'Không thể xóa bài viết';
        }
    }

    // ========== USERS MANAGEMENT ==========
    public function users(): void
    {
        if (!$this->checkAdminAccess()) return;

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $users = $this->adminModel->getUsers($limit, $offset);
        $total = $this->adminModel->countUsers();
        $totalPages = ceil($total / $limit);

        $this->view('admin/users/index', [
            'title' => 'Quản lý người dùng',
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }

    public function createUserPage(): void
    {
        if (!$this->checkAdminAccess()) return;

        $this->view('admin/users/create', [
            'title' => 'Tạo người dùng mới',
            'csrf' => CSRF::token()
        ]);
    }

    public function createUser(): void
    {
        if (!$this->checkAdminAccess()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int)($_POST['role_id'] ?? 2);

        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            header('Location: ' . BASE_URL . '/admin/users/create?error=missing_fields');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: ' . BASE_URL . '/admin/users/create?error=invalid_email');
            return;
        }

        if (strlen($password) < 6) {
            header('Location: ' . BASE_URL . '/admin/users/create?error=password_short');
            return;
        }

        // Check if email exists
        if ($this->adminModel->emailExists($email)) {
            header('Location: ' . BASE_URL . '/admin/users/create?error=email_exists');
            return;
        }

        // Create user
        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role_id' => $roleId
        ];

        if ($this->adminModel->createUser($userData)) {
            header('Location: ' . BASE_URL . '/admin/users?success=created');
        } else {
            header('Location: ' . BASE_URL . '/admin/users/create?error=create_failed');
        }
    }

    public function editUserPage(string $id): void
    {
        if (!$this->checkAdminAccess()) return;

        $user = $this->adminModel->getUserDetail((int)$id);

        if (!$user) {
            http_response_code(404);
            echo 'Người dùng không tồn tại';
            return;
        }

        $this->view('admin/users/edit', [
            'title' => 'Chỉnh sửa người dùng - ' . $user['name'],
            'user' => $user,
            'csrf' => CSRF::token()
        ]);
    }

    public function updateUser(string $id): void
    {
        if (!$this->checkAdminAccess()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $userId = (int)$id;
        $user = $this->adminModel->getUserDetail($userId);

        if (!$user) {
            http_response_code(404);
            echo 'Người dùng không tồn tại';
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int)($_POST['role_id'] ?? $user['role_id']);

        // Validation
        if (empty($name) || empty($email)) {
            header('Location: ' . BASE_URL . '/admin/users/' . $userId . '/edit?error=missing_fields');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: ' . BASE_URL . '/admin/users/' . $userId . '/edit?error=invalid_email');
            return;
        }

        // Check if email exists (excluding current user)
        if ($email !== $user['email'] && $this->adminModel->emailExists($email)) {
            header('Location: ' . BASE_URL . '/admin/users/' . $userId . '/edit?error=email_exists');
            return;
        }

        // Update data
        $updateData = [
            'name' => $name,
            'email' => $email,
            'role_id' => $roleId
        ];

        // Update password if provided
        if (!empty($password)) {
            if (strlen($password) < 6) {
                header('Location: ' . BASE_URL . '/admin/users/' . $userId . '/edit?error=password_short');
                return;
            }
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($this->adminModel->updateUser($userId, $updateData)) {
            header('Location: ' . BASE_URL . '/admin/users/' . $userId . '?success=updated');
        } else {
            header('Location: ' . BASE_URL . '/admin/users/' . $userId . '/edit?error=update_failed');
        }
    }

    public function userDetail(string $id): void
    {
        if (!$this->checkAdminAccess()) return;

        $user = $this->adminModel->getUserDetail((int)$id);

        if (!$user) {
            http_response_code(404);
            echo 'Người dùng không tồn tại';
            return;
        }

        $articleModel = new Article();
        $articles = $articleModel->getByUser((int)$id, 10);
        $activities = $this->adminModel->getUserActivities((int)$id, 20);

        $this->view('admin/users/detail', [
            'title' => 'Chi tiết người dùng - ' . $user['name'],
            'user' => $user,
            'articles' => $articles,
            'activities' => $activities,
            'csrf' => CSRF::token()
        ]);
    }

    public function deleteUser(string $id): void
    {
        if (!$this->checkAdminAccess()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $userId = (int)$id;

        if (Auth::user()['id'] === $userId) {
            http_response_code(400);
            echo 'Không thể xóa tài khoản của chính bạn';
            return;
        }

        if ($this->adminModel->deleteUser($userId)) {
            header('Location: ' . BASE_URL . '/admin/users?success=deleted');
        } else {
            http_response_code(500);
            echo 'Không thể xóa người dùng';
        }
    }

    // ========== CATEGORIES MANAGEMENT ==========
    public function categories(): void
    {
        if (!$this->checkAdminAccess()) return;

        $categoryModel = new Category();
        $categories = $categoryModel->withArticleCount();

        $this->view('admin/categories/index', [
            'title' => 'Quản lý danh mục',
            'categories' => $categories,
            'csrf' => CSRF::token()
        ]);
    }

    public function createCategory(): void
    {
        if (!$this->checkAdminAccess()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');

        if (empty($name) || empty($slug)) {
            http_response_code(400);
            echo 'Tên và slug không được để trống';
            return;
        }

        $categoryModel = new Category();
        if ($categoryModel->create(['name' => $name, 'slug' => $slug])) {
            header('Location: ' . BASE_URL . '/admin/categories?success=created');
        } else {
            http_response_code(500);
            echo 'Không thể tạo danh mục';
        }
    }

    public function updateCategory(string $id): void
    {
        if (!$this->checkAdminAccess()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');

        if (empty($name) || empty($slug)) {
            http_response_code(400);
            echo 'Tên và slug không được để trống';
            return;
        }

        $categoryModel = new Category();
        if ($categoryModel->update((int)$id, ['name' => $name, 'slug' => $slug])) {
            header('Location: ' . BASE_URL . '/admin/categories?success=updated');
        } else {
            http_response_code(500);
            echo 'Không thể cập nhật danh mục';
        }
    }

    public function deleteCategory(string $id): void
    {
        if (!$this->checkAdminAccess()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $articleModel = new Article();
        $count = $articleModel->countByCategory((int)$id);

        if ($count > 0) {
            http_response_code(400);
            echo "Không thể xóa danh mục có {$count} bài viết. Vui lòng chuyển bài viết sang danh mục khác trước.";
            return;
        }

        $categoryModel = new Category();
        if ($categoryModel->delete((int)$id)) {
            header('Location: ' . BASE_URL . '/admin/categories?success=deleted');
        } else {
            http_response_code(500);
            echo 'Không thể xóa danh mục';
        }
    }

    // ========== TAGS MANAGEMENT ==========
    public function tags(): void
    {
        if (!$this->checkAdminAccess()) return;

        $tagModel = new Tag();
        $tags = $tagModel->withArticleCount();

        $this->view('admin/tags/index', [
            'title' => 'Quản lý thẻ',
            'tags' => $tags,
            'csrf' => CSRF::token()
        ]);
    }

    public function createTag(): void
    {
        if (!$this->checkAdminAccess()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');

        if (empty($name) || empty($slug)) {
            http_response_code(400);
            echo 'Tên và slug không được để trống';
            return;
        }

        $tagModel = new Tag();
        if ($tagModel->create(['name' => $name, 'slug' => $slug])) {
            header('Location: ' . BASE_URL . '/admin/tags?success=created');
        } else {
            http_response_code(500);
            echo 'Không thể tạo thẻ';
        }
    }

    public function updateTag(string $id): void
    {
        if (!$this->checkAdminAccess()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');

        if (empty($name) || empty($slug)) {
            http_response_code(400);
            echo 'Tên và slug không được để trống';
            return;
        }

        $tagModel = new Tag();
        if ($tagModel->update((int)$id, ['name' => $name, 'slug' => $slug])) {
            header('Location: ' . BASE_URL . '/admin/tags?success=updated');
        } else {
            http_response_code(500);
            echo 'Không thể cập nhật thẻ';
        }
    }

    public function deleteTag(string $id): void
    {
        if (!$this->checkAdminAccess()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $tagModel = new Tag();
        if ($tagModel->delete((int)$id)) {
            header('Location: ' . BASE_URL . '/admin/tags?success=deleted');
        } else {
            http_response_code(500);
            echo 'Không thể xóa thẻ';
        }
    }

    // ========== COMMENTS MANAGEMENT ==========
    
    /**
     * Trang quản lý bình luận (UI)
     */
    // Thêm vào AdminController.php trong phần COMMENTS MANAGEMENT

public function comments(): void
    {
        if (!$this->checkAdminAccess()) return;

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $commentModel = new Admin();
        $comments = $commentModel->getComments($limit, $offset); // Cần có method này
        $total = $commentModel->countComments(); // Cần có method này
        $totalPages = ceil($total / $limit);

        $this->view('admin/comments/index', [
            'title' => 'Quản lý bình luận',
            'comments' => $comments,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'csrf' => CSRF::token()
        ]);
    }
    /**
 * Xóa bình luận (Admin)
 */
public function deleteComment(string $id): void
{
    if (!$this->checkAdminAccess()) return;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo 'Method not allowed';
        return;
    }

    if (!CSRF::validate($_POST['csrf'] ?? null)) {
        http_response_code(400);
        echo 'Invalid CSRF token';
        return;
    }

    $commentId = (int)$id;
    $commentModel = new Comment();
    $comment = $commentModel->findById($commentId);
    
    if (!$comment) {
        http_response_code(404);
        echo 'Bình luận không tồn tại';
        return;
    }

    if ($commentModel->delete($commentId)) {
        ActivityLogger::log('comment_delete', $commentId);
        header('Location: ' . BASE_URL . '/admin/comments?success=deleted');
    } else {
        http_response_code(500);
        echo 'Không thể xóa bình luận';
    }
}
    /**
     * API: Lấy danh sách bình luận cần kiểm duyệt
     */
    public function commentsModeration(): void
    {
        if (!$this->checkAdminAccess()) return;

        header('Content-Type: application/json');

        try {
            $commentModel = new Comment();
            $comments = $commentModel->getForModeration(50, 0);

            echo json_encode([
                'success' => true,
                'comments' => $comments
            ]);
        } catch (\Exception $e) {
            error_log("Error in commentsModeration: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi tải bình luận'
            ]);
        }
    }

    /**
     * API: Lấy danh sách báo cáo bình luận
     */
    public function commentsReports(): void
    {
        if (!$this->checkAdminAccess()) return;

        header('Content-Type: application/json');

        try {
            $commentModel = new Comment();
            $reports = $commentModel->getReports(50, 0);

            echo json_encode([
                'success' => true,
                'reports' => $reports
            ]);
        } catch (\Exception $e) {
            error_log("Error in commentsReports: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi tải báo cáo'
            ]);
        }
    }

    /**
     * API: Xử lý báo cáo bình luận
     */
    public function resolveReport(): void
    {
        if (!$this->checkAdminAccess()) return;

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            return;
        }

        $reportId = (int)($_POST['report_id'] ?? 0);
        $status = $_POST['status'] ?? 'resolved';

        if (!$reportId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Report ID is required']);
            return;
        }

        try {
            $commentModel = new Comment();
            if ($commentModel->updateReportStatus($reportId, $status, Auth::user()['id'])) {
                ActivityLogger::log('comment_report_resolved', $reportId);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Báo cáo đã được xử lý'
                ]);
            } else {
                throw new \Exception('Không thể cập nhật trạng thái báo cáo');
            }
        } catch (\Exception $e) {
            error_log("Error resolving report: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi xử lý báo cáo'
            ]);
        }
    }
    // ========== STATISTICS ==========

/**
 * Trang thống kê lượt xem chi tiết
 */
public function viewStatistics(): void
{
    if (!$this->checkAdminAccess()) return;

    // Lấy filters từ query string
    $days = (int)($_GET['days'] ?? 7);
    $filters = [
        'article_id' => $_GET['article_id'] ?? null,
        'user_id' => $_GET['user_id'] ?? null,
        'date_from' => $_GET['date_from'] ?? null,
        'date_to' => $_GET['date_to'] ?? null,
        'sort' => $_GET['sort'] ?? 'views'
    ];

    // Lấy danh sách bài viết cho filter dropdown
    $articleModel = new Article();
    $articles = $articleModel->search(['status' => 'published'], 1000, 0);

    // Lấy danh sách tác giả
    $users = $this->adminModel->getAllAuthors();

    // Lấy thống kê lượt xem
    $viewStats = $this->adminModel->getDetailedViewStatistics($days, $filters);

    $this->view('admin/statistics/views', [
        'title' => 'Thống kê lượt xem chi tiết',
        'viewStats' => $viewStats,
        'articles' => $articles,
        'users' => $users,
        'days' => $days,
        'filters' => $filters
    ]);
}

/**
 * API: Thống kê bài viết
 */
public function articleStatistics(): void
{
    if (!$this->checkAdminAccess()) return;

    header('Content-Type: application/json');

    try {
        $period = $_GET['period'] ?? '30'; // 7, 30, 90 days
        $stats = $this->adminModel->getArticleStatistics((int)$period);

        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
    } catch (\Exception $e) {
        error_log("Error in articleStatistics: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Có lỗi xảy ra khi tải thống kê'
        ]);
    }
}

/**
 * API: Thống kê người dùng
 */
public function userStatistics(): void
{
    if (!$this->checkAdminAccess()) return;

    header('Content-Type: application/json');

    try {
        $period = $_GET['period'] ?? '30'; // 7, 30, 90 days
        $stats = $this->adminModel->getUserStatistics((int)$period);

        echo json_encode([
            'success' => true,
            'data' => $stats
        ]);
    } catch (\Exception $e) {
        error_log("Error in userStatistics: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Có lỗi xảy ra khi tải thống kê'
        ]);
    }
}

/**
 * Trang tổng quan thống kê (nếu cần)
 */
public function statistics(): void
{
    if (!$this->checkAdminAccess()) return;

    $this->view('admin/statistics/index', [
        'title' => 'Thống kê tổng quan',
        'stats' => $this->adminModel->getDashboardStats(),
        'viewStats' => $this->adminModel->getViewStatistics(30),
        'categoryStats' => $this->adminModel->getCategoryStatistics(),
        'popularArticles' => $this->adminModel->getPopularArticles(10)
    ]);
}
}