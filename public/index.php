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

// ========================================
// PUBLIC ROUTES
// ========================================

// Home
$router->get('/', 'HomeController@index');

// ========================================
// AUTHENTICATION ROUTES
// ========================================
$router->get('/auth/login', 'AuthController@login');
$router->post('/auth/login', 'AuthController@doLogin');
$router->get('/auth/register', 'AuthController@register');
$router->post('/auth/register', 'AuthController@doRegister');
$router->get('/auth/logout', 'AuthController@logout');
$router->get('/auth/forgot-password', 'AuthController@forgotPassword');
$router->post('/auth/forgot-password', 'AuthController@processForgotPassword');
$router->get('/auth/reset-password/:token', 'AuthController@resetPassword');
$router->post('/auth/reset-password', 'AuthController@processResetPassword');

// ========================================
// ARTICLE ROUTES
// ========================================

// Article browsing
$router->get('/articles', 'ArticleController@index');
$router->get('/articles/search', 'ArticleController@search');
$router->get('/article/:slug', 'ArticleController@show');

// My articles (user's own articles)
$router->get('/articles/mine', 'ArticleController@myArticles');

// Article creation & editing
$router->get('/articles/create', 'ArticleController@create');
$router->post('/articles', 'ArticleController@store');
$router->get('/articles/edit/:id', 'ArticleController@edit');
$router->post('/articles/:id/update', 'ArticleController@update');
$router->post('/articles/:id/delete', 'ArticleController@delete');

// Draft management
$router->get('/articles/drafts', 'ArticleController@drafts');
$router->get('/articles/draft/:id', 'ArticleController@editDraft');
$router->post('/articles/draft/:id/update', 'ArticleController@updateDraft');
$router->post('/articles/draft/:id/delete', 'ArticleController@deleteDraft');
$router->get('/articles/preview/:id', 'ArticleController@preview');

// Article status management
$router->post('/articles/:id/publish', 'ArticleController@publish');
$router->post('/articles/:id/unpublish', 'ArticleController@unpublish');
$router->post('/articles/:id/save-draft', 'ArticleController@saveDraft');

// ========================================
// COMMENT ROUTES
// ========================================
$router->get('/comments/get', 'CommentController@getComments');
$router->post('/comments/create', 'CommentController@create');
$router->post('/comments/update', 'CommentController@update');
$router->post('/comments/delete', 'CommentController@delete');
$router->post('/comments/toggle-like', 'CommentController@toggleLike');
$router->post('/comments/report', 'CommentController@report');
$router->post('/comments/moderate', 'CommentController@moderate');

// ========================================
// USER PROFILE ROUTES
// ========================================
$router->get('/account/profile', 'ProfileController@show');
$router->post('/account/profile', 'ProfileController@update');

// ========================================
// ADMIN ROUTES
// ========================================

// Admin Dashboard
$router->get('/admin', 'AdminController@index');
$router->get('/admin/dashboard', 'AdminController@index');

// Admin - Articles Management
$router->get('/admin/articles', 'AdminController@articles');
$router->get('/admin/articles/:id', 'AdminController@articleDetail');
$router->post('/admin/articles/:id/delete', 'AdminController@deleteArticle');

// Admin - Users Management
$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/users/:id', 'AdminController@userDetail');
$router->post('/admin/users/:id/delete', 'AdminController@deleteUser');

// Admin - Categories Management
$router->get('/admin/categories', 'AdminController@categories');
$router->post('/admin/categories/create', 'AdminController@createCategory');
$router->post('/admin/categories/:id/update', 'AdminController@updateCategory');
$router->post('/admin/categories/:id/delete', 'AdminController@deleteCategory');

// Admin - Tags Management
$router->get('/admin/tags', 'AdminController@tags');
$router->post('/admin/tags/create', 'AdminController@createTag');
$router->post('/admin/tags/:id/update', 'AdminController@updateTag');
$router->post('/admin/tags/:id/delete', 'AdminController@deleteTag');

// Admin - Comments Management
$router->get('/admin/comments', 'AdminController@comments');
$router->post('/admin/comments/:id/delete', 'AdminController@deleteComment');
$router->get('/admin/comments/moderation', 'AdminController@commentsModeration');
$router->get('/admin/comments/reports', 'AdminController@commentsReports');
$router->post('/admin/comments/resolve-report', 'AdminController@resolveReport');

// Admin - Statistics
$router->get('/admin/statistics', 'AdminController@statistics');
$router->get('/admin/statistics/views', 'AdminController@viewStatistics');
$router->get('/admin/statistics/articles', 'AdminController@articleStatistics');
$router->get('/admin/statistics/users', 'AdminController@userStatistics');

// ========================================
// DISPATCH REQUEST
// ========================================
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);