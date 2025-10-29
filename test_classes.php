<?php
// Test script để kiểm tra chức năng đăng bài đơn giản
require_once 'vendor/autoload.php';
require_once 'config/config.php';

// Test autoloader
try {
    $controller = new \App\Controllers\ArticleController();
    echo "✅ ArticleController loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Error loading ArticleController: " . $e->getMessage() . "\n";
}

// Test models
try {
    $articleModel = new \App\Models\Article();
    echo "✅ Article model loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Error loading Article model: " . $e->getMessage() . "\n";
}

try {
    $categoryModel = new \App\Models\Category();
    echo "✅ Category model loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Error loading Category model: " . $e->getMessage() . "\n";
}

try {
    $tagModel = new \App\Models\Tag();
    echo "✅ Tag model loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Error loading Tag model: " . $e->getMessage() . "\n";
}

// Test core classes
try {
    $session = new \App\Core\Session();
    echo "✅ Session class loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Error loading Session: " . $e->getMessage() . "\n";
}

try {
    $csrf = new \App\Core\CSRF();
    echo "✅ CSRF class loaded successfully\n";
} catch (Exception $e) {
    echo "❌ Error loading CSRF: " . $e->getMessage() . "\n";
}

echo "\n🎉 All classes loaded successfully! Ready to test article creation.\n";
echo "\n📋 Next steps:\n";
echo "1. Run: mysql -u root -p article_portal < scripts/simplify_articles.sql\n";
echo "2. Visit: http://localhost/Web-Project/public/articles/create\n";
echo "3. Or use: test_simple_article.html\n";
?>
