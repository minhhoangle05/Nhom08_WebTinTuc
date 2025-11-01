<?php
// Front Controller

// Enable error reporting during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define root paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'app');
define('CONFIG_PATH', BASE_PATH . DIRECTORY_SEPARATOR . 'config');
define('PUBLIC_PATH', __DIR__);

// Load Composer's autoloader
require_once BASE_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Load config
require_once CONFIG_PATH . DIRECTORY_SEPARATOR . 'config.php';

// Simple PSR-4 like autoloader for the app namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . DIRECTORY_SEPARATOR;
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Bootstrap core services
use App\Core\Session;
use App\Core\Router;

Session::start();

$router = new Router();

// Web routes
$router->get('/', 'HomeController@index');

// Authentication routes
$router->get('/auth/login', 'AuthController@login');
$router->post('/auth/login', 'AuthController@doLogin');
$router->get('/auth/register', 'AuthController@register');
$router->post('/auth/register', 'AuthController@doRegister');
$router->get('/auth/logout', 'AuthController@logout');
$router->get('/auth/forgot-password', 'AuthController@forgotPassword');
$router->post('/auth/forgot-password', 'AuthController@processForgotPassword');
$router->get('/auth/reset-password/:token', 'AuthController@resetPassword');
$router->post('/auth/reset-password', 'AuthController@processResetPassword');

// Trang chủ bài viết
$router->get('/articles', 'ArticleController@index');
$router->get('/articles/search', 'ArticleController@search');
$router->get('/articles/mine', 'ArticleController@myArticles');

// Quản lý bài viết
$router->get('/articles/create', 'ArticleController@create');
$router->post('/articles', 'ArticleController@store');
$router->get('/article/:slug', 'ArticleController@show');
$router->get('/article/:id/edit', 'ArticleController@edit');
$router->put('/article/:id', 'ArticleController@update');
$router->delete('/article/:id', 'ArticleController@delete');
$router->post('/article/:id/delete', 'ArticleController@delete');


// Preview và bản nháp
$router->get('/article/preview/:id', 'ArticleController@preview');
$router->get('/articles/drafts', 'ArticleController@drafts');
$router->get('/article/draft/:id', 'ArticleController@editDraft');
$router->put('/article/draft/:id', 'ArticleController@updateDraft');
$router->post('/article/:id/update', 'ArticleController@update');
$router->delete('/article/draft/:id', 'ArticleController@deleteDraft');
$router->get('/admin/dashboard', 'AdminController@index');
$router->get('/admin', 'AdminController@index');

// Quản lý trạng thái
$router->post('/article/:id/publish', 'ArticleController@publish');
$router->post('/article/:id/unpublish', 'ArticleController@unpublish');
$router->post('/article/:id/save-draft', 'ArticleController@saveDraft');
// Quản lý bài viết
$router->get('/admin/articles', 'AdminController@articles');
$router->get('/admin/articles/:id', 'AdminController@articleDetail');
$router->post('/admin/articles/:id/delete', 'AdminController@deleteArticle');

// Quản lý người dùng
$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/users/:id', 'AdminController@userDetail');
$router->get('/admin/users/create', 'AdminController@createUserForm');
$router->post('/admin/users/create', 'AdminController@createUser');
$router->post('/admin/users/:id/delete', 'AdminController@deleteUser');
$router->post('/admin/users/:id/toggle-role', 'AdminController@toggleUserRole');

// Quản lý danh mục
$router->get('/admin/categories', 'AdminController@categories');
$router->post('/admin/categories/create', 'AdminController@createCategory');
$router->post('/admin/categories/:id/update', 'AdminController@updateCategory');
$router->post('/admin/categories/:id/delete', 'AdminController@deleteCategory');

// Quản lý tags
$router->get('/admin/tags', 'AdminController@tags');
$router->post('/admin/tags/create', 'AdminController@createTag');
$router->post('/admin/tags/:id/delete', 'AdminController@deleteTag');

// Quản lý bình luận
$router->get('/admin/comments', 'AdminController@comments');
$router->post('/admin/comments/:id/delete', 'AdminController@deleteComment');

// Thống kê nâng cao
$router->get('/admin/statistics', 'AdminController@statistics');
$router->get('/admin/statistics/views', 'AdminController@viewStatistics');



// Admin routes
$router->get('/admin/dashboard', 'AdminController@index');
$router->get('/admin', 'AdminController@index'); // Redirect from /admin to /admin/dashboard
$router->get('/admin/comments', 'AdminController@commentsModerationPage');
$router->get('/admin/comments/moderation', 'AdminController@commentsModeration');
$router->get('/admin/comments/reports', 'AdminController@commentsReports');
$router->post('/admin/comments/resolve-report', 'AdminController@resolveReport');

// Account / Profile
$router->get('/account/profile', 'ProfileController@show');
$router->post('/account/profile', 'ProfileController@update');

// Comment routes
$router->get('/comment/getComments', 'CommentController@getComments');
$router->post('/comment/create', 'CommentController@create');
$router->post('/comment/update', 'CommentController@update');
$router->post('/comment/delete', 'CommentController@delete');
$router->post('/comment/toggleLike', 'CommentController@toggleLike');
$router->post('/comment/report', 'CommentController@report');
$router->post('/comment/moderate', 'CommentController@moderate');

// Dispatch request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);


