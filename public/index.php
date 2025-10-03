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

// Trang chủ bài viết
$router->get('/articles', 'ArticleController@index');
$router->get('/articles/search', 'ArticleController@search');

// Quản lý bài viết
$router->get('/articles/create', 'ArticleController@create');
$router->post('/articles', 'ArticleController@store');
$router->get('/article/:slug', 'ArticleController@show');
$router->get('/article/:id/edit', 'ArticleController@edit');
$router->put('/article/:id', 'ArticleController@update');
$router->delete('/article/:id', 'ArticleController@delete');

// Preview và bản nháp
$router->get('/article/preview/:id', 'ArticleController@preview');
$router->get('/articles/drafts', 'ArticleController@drafts');
$router->get('/article/draft/:id', 'ArticleController@editDraft');
$router->put('/article/draft/:id', 'ArticleController@updateDraft');
$router->delete('/article/draft/:id', 'ArticleController@deleteDraft');

// Quản lý trạng thái
$router->post('/article/:id/publish', 'ArticleController@publish');
$router->post('/article/:id/unpublish', 'ArticleController@unpublish');
$router->post('/article/:id/save-draft', 'ArticleController@saveDraft');

// Authentication
$router->get('/auth/login', 'AuthController@login');
$router->post('/auth/login', 'AuthController@doLogin');
$router->get('/auth/logout', 'AuthController@logout');
$router->get('/auth/register', 'AuthController@register');
$router->post('/auth/register', 'AuthController@doRegister');

// Admin routes
$router->get('/admin/dashboard', 'AdminController@index');
$router->get('/admin', 'AdminController@index'); // Redirect from /admin to /admin/dashboard

// Dispatch request
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);


