<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\ActivityLogger;
use App\Core\Session;
use App\Models\Admin;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;

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
        
        // Lấy filters
        $filters = [
            'q' => $_GET['q'] ?? null,
            'category_id' => $_GET['category_id'] ?? null,
            'user_id' => $_GET['user_id'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'sort' => $_GET['sort'] ?? 'latest'
        ];
        
        $articleModel = new Article();
        $articles = $articleModel->search($filters, $limit, $offset);
        $total = $articleModel->countSearch($filters);
        $totalPages = ceil($total / $limit);

        // Lấy categories và users cho filter
        $categoryModel = new Category();
        $categories = $categoryModel->all();
        $users = $this->adminModel->getAllAuthors();

        $this->view('admin/articles/index', [
            'title' => 'Quản lý bài viết',
            'articles' => $articles,
            'categories' => $categories,
            'users' => $users,
            'filters' => $filters,
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
            Session::flash('success', 'Đã xóa bài viết thành công');
            header('Location: ' . BASE_URL . '/admin/articles');
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

        // Filters
        $search = $_GET['search'] ?? null;
        $roleId = $_GET['role_id'] ?? null;

        $users = $this->adminModel->getUsers($limit, $offset, $search, $roleId);
        $total = $this->adminModel->countUsers($search, $roleId);
        $totalPages = ceil($total / $limit);

        $this->view('admin/users/index', [
            'title' => 'Quản lý người dùng',
            'users' => $users,
            'search' => $search,
            'roleId' => $roleId,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'csrf' => CSRF::token()
        ]);
    }

    public function createUserForm(): void
    {
        if (!$this->checkAdminAccess()) return;

        $this->view('admin/users/create', [
            'title' => 'Thêm người dùng mới',
            'csrf' => CSRF::token()
        ]);
    }

    public function createUser(): void
    {
        if (!$this->checkAdminAccess()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            Session::flash('error', 'Invalid CSRF token');
            header('Location: ' . BASE_URL . '/admin/users/create');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int)($_POST['role_id'] ?? 2); // Default: user

        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            Session::flash('error', 'Vui lòng điền đầy đủ thông tin');
            header('Location: ' . BASE_URL . '/admin/users/create');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Email không hợp lệ');
            header('Location: ' . BASE_URL . '/admin/users/create');
            return;
        }

        // Check email exists
        if ($this->adminModel->emailExists($email)) {
            Session::flash('error', 'Email đã tồn tại');
            header('Location: ' . BASE_URL . '/admin/users/create');
            return;
        }

        $userId = $this->adminModel->createUser([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role_id' => $roleId
        ]);

        if ($userId) {
            Session::flash('success', 'Đã tạo người dùng thành công');
            header('Location: ' . BASE_URL . '/admin/users');
        } else {
            Session::flash('error', 'Không thể tạo người dùng');
            header('Location: ' . BASE_URL . '/admin/users/create');
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

    public function toggleUserRole(string $id): void
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

        $userId = (int)$id;
        $newRoleId = (int)($_POST['role_id'] ?? 2);

        if (Auth::user()['id'] === $userId) {
            http_response_code(400);
            echo 'Không thể thay đổi vai trò của chính bạn';
            return;
        }

        if ($this->adminModel->updateUserRole($userId, $newRoleId)) {
            Session::flash('success', 'Đã cập nhật vai trò người dùng');
            header('Location: ' . BASE_URL . '/admin/users/' . $userId);
        } else {
            http_response_code(500);
            echo 'Không thể cập nhật vai trò';
        }
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
            Session::flash('success', 'Đã xóa người dùng thành công');
            header('Location: ' . BASE_URL . '/admin/users');
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
            Session::flash('success', 'Đã tạo danh mục thành công');
            header('Location: ' . BASE_URL . '/admin/categories');
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
            Session::flash('success', 'Đã cập nhật danh mục thành công');
            header('Location: ' . BASE_URL . '/admin/categories');
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
            Session::flash('success', 'Đã xóa danh mục thành công');
            header('Location: ' . BASE_URL . '/admin/categories');
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
            Session::flash('success', 'Đã tạo thẻ thành công');
            header('Location: ' . BASE_URL . '/admin/tags');
        } else {
            http_response_code(500);
            echo 'Không thể tạo thẻ';
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
            Session::flash('success', 'Đã xóa thẻ thành công');
            header('Location: ' . BASE_URL . '/admin/tags');
        } else {
            http_response_code(500);
            echo 'Không thể xóa thẻ';
        }
    }

    // ========== COMMENTS MANAGEMENT ==========
    public function comments(): void
    {
        if (!$this->checkAdminAccess()) return;

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $comments = $this->adminModel->getComments($limit, $offset);
        $total = $this->adminModel->countComments();
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

    public function deleteComment(string $id): void
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

        if ($this->adminModel->deleteComment((int)$id)) {
            ActivityLogger::log('comment_delete', (int)$id);
            Session::flash('success', 'Đã xóa bình luận thành công');
            header('Location: ' . BASE_URL . '/admin/comments');
        } else {
            http_response_code(500);
            echo 'Không thể xóa bình luận';
        }
    }

    // ========== STATISTICS ==========
    public function viewStatistics(): void
    {
        if (!$this->checkAdminAccess()) return;

        $days = (int)($_GET['days'] ?? 7);
        $days = min(max($days, 1), 90); // Giới hạn 1-90 ngày

        // Filters
        $filters = [
            'article_id' => $_GET['article_id'] ?? null,
            'user_id' => $_GET['user_id'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'sort' => $_GET['sort'] ?? 'views' // views, date, title
        ];

        $viewStats = $this->adminModel->getDetailedViewStatistics($days, $filters);
        
        // Lấy danh sách bài viết và users cho filter
        $articleModel = new Article();
        $articles = $articleModel->all(100, 0); // Top 100 articles
        $users = $this->adminModel->getAllAuthors();

        $this->view('admin/statistics/views', [
            'title' => 'Thống kê lượt xem chi tiết',
            'viewStats' => $viewStats,
            'articles' => $articles,
            'users' => $users,
            'filters' => $filters,
            'days' => $days,
            'csrf' => CSRF::token()
        ]);
    }
}