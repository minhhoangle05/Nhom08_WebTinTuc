<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\CSRF;
use App\Core\Auth;
use App\Core\ActivityLogger;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;

class ArticleController extends Controller
{
    private Article $articleModel;
    private Category $categoryModel;
    private Tag $tagModel;

    public function __construct()
    {
        $this->articleModel = new Article();
        $this->categoryModel = new Category();
        $this->tagModel = new Tag();
    }

    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $categorySlug = $_GET['category'] ?? null;
        $sort = $_GET['sort'] ?? 'latest';
        
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        $filters = [
            'status' => Article::STATUS_PUBLISHED, // Only show published articles
            'sort' => $sort
        ];
        
        // Filter by category if provided
        $currentCategory = null;
        if ($categorySlug) {
            $currentCategory = $this->categoryModel->findBySlug($categorySlug);
            if ($currentCategory) {
                $filters['category_id'] = $currentCategory['id'];
            }
        }
        
        $articles = $this->articleModel->search($filters, $limit, $offset);
        $total = $this->articleModel->countSearch($filters);
        $totalPages = ceil($total / $limit);
        
        // Get all categories with article counts
        $categories = $this->categoryModel->withArticleCount();
        
        // Get popular articles for sidebar
        $popularArticles = $this->articleModel->popular(5);
        
        $this->view('articles/index', [
            'title' => $currentCategory ? 'Bài viết - ' . $currentCategory['name'] : 'Tất cả bài viết',
            'articles' => $articles,
            'categories' => $categories,
            'popularArticles' => $popularArticles,
            'currentCategory' => $currentCategory,
            'currentSort' => $sort,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }

    public function search(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'q' => $_GET['q'] ?? null,
            'category' => $_GET['category'] ?? null,
            'tag' => $_GET['tag'] ?? null,
            'sort' => $_GET['sort'] ?? 'latest'
        ];
        
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        $articles = $this->articleModel->search($filters, $limit, $offset);
        $total = $this->articleModel->countSearch($filters);
        $totalPages = ceil($total / $limit);
        
        // Build base URL for pagination
        $baseUrl = BASE_URL . '/articles/search?';
        $queryParams = $_GET;
        unset($queryParams['page']);
        if (!empty($queryParams)) {
            $baseUrl .= http_build_query($queryParams) . '&';
        }

        $this->view('articles/search', [
            'title' => !empty($filters['category']) ? 'Bài viết trong danh mục: ' . $filters['category'] : 'Tìm kiếm bài viết',
            'articles' => $articles,
            'category' => $filters['category'],
            'query' => $filters['q'],
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'baseUrl' => $baseUrl
        ]);
    }

    public function create(): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $categories = $this->categoryModel->all();
        $tags = $this->tagModel->all();
        
        $this->view('articles/create', [
            'title' => 'Tạo bài viết mới',
            'csrf' => CSRF::token(),
            'categories' => $categories,
            'tags' => $tags
        ]);
    }

    public function store(): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $tags = $_POST['tags'] ?? [];
        
        if ($title === '' || $slug === '' || $content === '') {
            http_response_code(422);
            echo 'Vui lòng điền đầy đủ thông tin bắt buộc';
            return;
        }

        $articleData = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'user_id' => Auth::user()['id'],
            'category_id' => $categoryId ?: null,
            'tags' => $tags
        ];

        $articleId = $this->articleModel->create($articleData);

        ActivityLogger::log('article_create', $articleId);
        
        header('Location: ' . BASE_URL . '/article/' . rawurlencode($slug));
        exit;
    }

    public function show(string $slug): void
    {
        $article = $this->articleModel->findBySlug($slug);
        
        if (!$article) {
            http_response_code(404);
            echo 'Không tìm thấy bài viết';
            return;
        }

        $tags = $this->articleModel->getArticleTags($article['id']);

        $this->articleModel->incrementViews($article['id']);
        if (Auth::check()) {
            ActivityLogger::log('article_view', $article['id']);
        }

        $this->view('articles/show', [
            'title' => $article['title'],
            'article' => $article,
            'tags' => $tags,
            'csrf' => CSRF::token()
        ]);
    }

    public function edit(int $id): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $article = $this->articleModel->findById($id);
        if (!$article) {
            http_response_code(404);
            echo 'Không tìm thấy bài viết';
            return;
        }

        if (!Auth::isAdmin() && Auth::user()['id'] !== $article['user_id']) {
            http_response_code(403);
            echo 'Bạn không có quyền sửa bài viết này';
            return;
        }

        $categories = $this->categoryModel->all();
        $tags = $this->tagModel->all();
        $articleTags = $this->articleModel->getArticleTags($id);

        $this->view('articles/edit', [
            'title' => 'Chỉnh sửa bài viết',
            'article' => $article,
            'categories' => $categories,
            'tags' => $tags,
            'articleTags' => $articleTags,
            'csrf' => CSRF::token()
        ]);
    }

    public function update(int $id): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $article = $this->articleModel->findById($id);
        if (!$article) {
            http_response_code(404);
            echo 'Không tìm thấy bài viết';
            return;
        }

        if (!Auth::isAdmin() && Auth::user()['id'] !== $article['user_id']) {
            http_response_code(403);
            echo 'Bạn không có quyền sửa bài viết này';
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        
        if ($title === '' || $slug === '' || $content === '') {
            http_response_code(422);
            echo 'Vui lòng điền đầy đủ thông tin bắt buộc';
            return;
        }

        $featuredImage = $article['featured_image'];
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'public/uploads/articles/';
            $fileName = uniqid() . '_' . $_FILES['featured_image']['name'];
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $uploadDir . $fileName)) {
                if ($featuredImage && file_exists($uploadDir . $featuredImage)) {
                    unlink($uploadDir . $featuredImage);
                }
                $featuredImage = $fileName;
            }
        }

        $updateData = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'category_id' => (int)($_POST['category_id'] ?? 0) ?: null,
            'featured_image' => $featuredImage,
            'tags' => $_POST['tags'] ?? []
        ];

        if ($this->articleModel->update($id, $updateData)) {
            ActivityLogger::log('article_update', $id);
            header('Location: ' . BASE_URL . '/article/' . rawurlencode($slug));
        } else {
            http_response_code(500);
            echo 'Có lỗi xảy ra khi cập nhật bài viết';
        }
    }

    public function preview(int $id): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $article = $this->articleModel->findById($id);
        if (!$article) {
            http_response_code(404);
            echo 'Không tìm thấy bài viết';
            return;
        }

        if (!Auth::isAdmin() && Auth::user()['id'] !== $article['user_id']) {
            http_response_code(403);
            echo 'Bạn không có quyền xem bài viết này';
            return;
        }

        $tags = $this->articleModel->getArticleTags($id);

        $this->view('articles/preview', [
            'title' => $article['title'] . ' (Preview)',
            'article' => $article,
            'tags' => $tags
        ]);
    }

    public function drafts(): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $drafts = $this->articleModel->getUserDrafts(Auth::user()['id']);

        $this->view('articles/drafts', [
            'title' => 'Bản nháp của tôi',
            'drafts' => $drafts
        ]);
    }

    public function editDraft(int $id): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        $draft = $this->articleModel->getDraft($id);
        if (!$draft) {
            http_response_code(404);
            echo 'Không tìm thấy bản nháp';
            return;
        }

        if (!Auth::isAdmin() && Auth::user()['id'] !== $draft['user_id']) {
            http_response_code(403);
            echo 'Bạn không có quyền sửa bản nháp này';
            return;
        }

        $categories = $this->categoryModel->all();
        $tags = $this->tagModel->all();

        $this->view('articles/edit_draft', [
            'title' => 'Chỉnh sửa bản nháp',
            'draft' => $draft,
            'categories' => $categories,
            'tags' => $tags,
            'csrf' => CSRF::token()
        ]);
    }

    public function publish(int $id): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $article = $this->articleModel->findById($id);
        if (!$article) {
            http_response_code(404);
            echo json_encode(['error' => 'Article not found']);
            return;
        }

        if (!Auth::isAdmin() && Auth::user()['id'] !== $article['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        if ($this->articleModel->publish($id)) {
            ActivityLogger::log('article_publish', $id);
            header('Location: ' . BASE_URL . '/article/' . rawurlencode($article['slug']));
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to publish article']);
        }
    }

    public function unpublish(int $id): void
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $article = $this->articleModel->findById($id);
        if (!$article) {
            http_response_code(404);
            echo json_encode(['error' => 'Article not found']);
            return;
        }

        if (!Auth::isAdmin() && Auth::user()['id'] !== $article['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        if ($this->articleModel->unpublish($id)) {
            ActivityLogger::log('article_unpublish', $id);
            header('Location: ' . BASE_URL . '/articles/drafts');
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to unpublish article']);
        }
    }

    public function saveDraft(): void
    {
        if (!Auth::check()) {
            header('Location: ' . BASE_URL . '/auth/login');
            exit;
        }

        if (!CSRF::validate($_POST['csrf'] ?? null)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        
        if ($title === '' || $slug === '') {
            http_response_code(422);
            echo 'Vui lòng điền tiêu đề và slug';
            return;
        }

        $articleData = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'user_id' => Auth::user()['id'],
            'category_id' => (int)($_POST['category_id'] ?? 0) ?: null,
            'tags' => $_POST['tags'] ?? [],
            'status' => Article::STATUS_DRAFT
        ];

        $articleId = $this->articleModel->create($articleData);

        ActivityLogger::log('article_draft_save', $articleId);
        
        header('Location: ' . BASE_URL . '/articles/drafts');
        exit;
    }

    private function buildPageUrl($page): string
    {
        $params = $_GET;
        $params['page'] = $page;
        return '?' . http_build_query($params);
    }
}
