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
            'popularArticles' => $this->adminModel->getPopularArticles(5)
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
            'total' => $total
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
            header('Location: ' . BASE_URL . '/admin/comments?success=deleted');
        } else {
            http_response_code(500);
            echo 'Không thể xóa bình luận';
        }
    }
}