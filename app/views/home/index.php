<!-- Hero Section -->
<section class="hero-section text-center position-relative mb-5">
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if (isset($user)): ?>
                <div class="user-welcome slide-in-left mb-4" style="--animation-delay: 0.1s">
                    <h2 class="h4 mb-3">Xin chào, <?= htmlspecialchars($user['name']) ?>!</h2>
                    <p class="text-light mb-3">
                        <i class="bi bi-envelope me-2"></i><?= htmlspecialchars($user['email']) ?>
                        <?php if ($user['role_id'] === 3): ?>
                            <span class="badge bg-warning ms-2">
                                <i class="bi bi-shield-check me-1"></i>Quản trị viên
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
                <?php endif; ?>
                <h1 class="display-4 fw-bold mb-4 text-gradient slide-in-left" style="--animation-delay: 0.2s">
                    Tin tức nổi bật mỗi ngày
                </h1>
                <p class="lead mb-4 slide-in-left" style="--animation-delay: 0.3s">
                    Khám phá những câu chuyện, xu hướng và thông tin mới nhất từ cộng đồng
                </p>
                <div class="d-flex justify-content-center gap-3 slide-in-left" style="--animation-delay: 0.4s">
                    <a class="btn btn-primary btn-lg" href="<?= BASE_URL ?>/articles">
                        <i class="bi bi-journal-richtext me-2"></i>Khám phá ngay
                    </a>
                    <a class="btn btn-outline-light btn-lg" href="<?= BASE_URL ?>/articles/create">
                        <i class="bi bi-pencil-square me-2"></i>Viết bài
                    </a>
                </div>
                <div class="mt-4 slide-in-left" style="--animation-delay: 0.5s">
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-clock me-1"></i>
                        <span id="currentTime"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-shapes"></div>
</section>

<?php
use App\Models\Article;
use App\Models\Category;
use App\Core\Auth;

// Kiểm tra user đăng nhập
$user = Auth::check() ? Auth::user() : null;

// Lấy bài viết nổi bật
try {
    $articleModel = new Article();
    $featured = $articleModel->featuredToday(6);
} catch (\Exception $e) {
    $featured = [];
}
?>

<!-- Featured Articles Section -->
<section class="container my-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="mb-0 fw-bold">
                    <i class="bi bi-star-fill text-warning"></i>
                    Bài viết nổi bật
                </h2>
                <a href="<?= BASE_URL ?>/articles" class="btn btn-outline-primary rounded-pill">
                    Xem tất cả <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>

            <?php if (!empty($featured)): ?>
                <div class="featured-articles-grid">
                    <?php foreach ($featured as $index => $article): ?>
                        <article class="featured-article-card slide-in-left" 
                                 style="--animation-delay: <?= $index * 0.1 ?>s;">
                            <!-- Article Image -->
                            <div class="article-image-container">
                                <?php if ($article['featured_image']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/articles/<?= htmlspecialchars($article['featured_image']) ?>" 
                                         class="article-image" 
                                         alt="<?= htmlspecialchars($article['title']) ?>">
                                <?php else: ?>
                                    <div class="article-image-placeholder">
                                        <i class="bi bi-image"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Category Badge -->
                                <div class="category-badge">
                                    <?= htmlspecialchars($article['category_name'] ?? 'Chung') ?>
                                </div>
                                
                                <!-- Reading Time -->
                                <div class="reading-time">
                                    <i class="bi bi-clock"></i>
                                    <?= $article['reading_time'] ?? '5' ?> phút
                                </div>
                            </div>
                            
                            <!-- Article Content -->
                            <div class="article-content">
                                <div class="article-meta">
                                    <div class="author-info">
                                        <div class="author-avatar">
                                            <?= strtoupper(substr($article['author_name'], 0, 1)) ?>
                                        </div>
                                        <div class="author-details">
                                            <div class="author-name"><?= htmlspecialchars($article['author_name']) ?></div>
                                            <div class="publish-date">
                                                <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="article-stats">
                                        <span class="stat-item">
                                            <i class="bi bi-eye"></i>
                                            <?= number_format($article['views']) ?>
                                        </span>
                                        <span class="stat-item">
                                            <i class="bi bi-chat-dots"></i>
                                            <?= number_format($article['comment_count']) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <h3 class="article-title">
                                    <a href="<?= BASE_URL ?>/article/<?= urlencode($article['slug']) ?>">
                                        <?= htmlspecialchars($article['title']) ?>
                                    </a>
                                </h3>
                                
                                <?php if ($article['summary']): ?>
                                    <p class="article-summary">
                                        <?= htmlspecialchars(substr($article['summary'], 0, 120)) ?>...
                                    </p>
                                <?php endif; ?>
                                
                                <div class="article-footer">
                                    <a href="<?= BASE_URL ?>/article/<?= urlencode($article['slug']) ?>" 
                                       class="read-more-btn">
                                        Đọc tiếp
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="bi bi-newspaper display-1 text-muted mb-3"></i>
                        <h3>Chưa có bài viết nào</h3>
                        <p class="text-muted mb-4">Hãy là người đầu tiên chia sẻ bài viết với cộng đồng!</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="<?= BASE_URL ?>/articles" class="btn btn-light">
                                <i class="bi bi-journal-text me-2"></i>Xem tất cả bài viết
                            </a>
                            <a href="<?= BASE_URL ?>/articles/create" class="btn btn-primary">
                                <i class="bi bi-pencil-square me-2"></i>Viết bài ngay
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Welcome Card -->
            <div class="sticky-sidebar" style="position: sticky; top: 80px;">
                <div class="card sidebar-widget welcome-card mb-4">
                    <div class="card-body">
                        <h3 class="h5 mb-3">
                            <i class="bi bi-hand-thumbs-up-fill text-primary me-2"></i>
                            Chào mừng bạn đến với ArticleHub!
                        </h3>
                        <p class="text-muted mb-4">Nơi chia sẻ kiến thức, kinh nghiệm và những câu chuyện thú vị.</p>
                        <?php if (!Auth::check()): ?>
                            <div class="d-grid gap-2">
                                <a href="<?= BASE_URL ?>/auth/login" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                                </a>
                                <a href="<?= BASE_URL ?>/auth/register" class="btn btn-outline-primary">
                                    <i class="bi bi-person-plus me-2"></i>Đăng ký
                                </a>
                            </div>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/articles/create" class="btn btn-primary w-100">
                                <i class="bi bi-pencil-square me-2"></i>Viết bài mới
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            <!-- Popular Categories -->
            <div class="card sidebar-widget mb-4">
                <div class="card-header bg-transparent border-0">
                    <h3 class="h5 mb-0">
                        <i class="bi bi-tags-fill text-primary me-2"></i>
                        Chủ đề phổ biến
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex flex-wrap gap-2">
                        <?php if (!empty($categories)): ?>
                            <?php
                            // Define an array of Bootstrap color classes
                            $colors = ['primary', 'success', 'info', 'warning', 'danger'];
                            $colorIndex = 0;
                            ?>
                            <?php foreach ($categories as $category): ?>
                                <a href="<?= BASE_URL ?>/articles/search?category=<?= urlencode($category['name']) ?>" 
                                   class="badge bg-<?= $colors[$colorIndex % count($colors)] ?> text-decoration-none">
                                    <?= htmlspecialchars($category['name']) ?>
                                    <?php if (isset($category['article_count'])): ?>
                                        <span class="ms-1 badge bg-light text-dark"><?= $category['article_count'] ?></span>
                                    <?php endif; ?>
                                </a>
                                <?php $colorIndex++; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">Chưa có chuyên mục nào</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Newsletter -->
            <div class="card sidebar-widget mb-4">
                <div class="card-body">
                    <h3 class="h5 mb-3">
                        <i class="bi bi-envelope-paper-fill text-primary me-2"></i>
                        Đăng ký nhận tin
                    </h3>
                    <p class="text-muted small mb-3">Nhận thông báo khi có bài viết mới và các cập nhật thú vị!</p>
                    <form class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Email của bạn">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
    </div>
</section>

<!-- CSS styles -->
<style>
.hero-section {
    background: linear-gradient(135deg, var(--primary-color), #1e40af);
    color: #fff;
    overflow: hidden;
    border-radius: 0 0 2rem 2rem;
}

.text-gradient {
    background: linear-gradient(120deg, #fff, #f0f9ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-shapes {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
    z-index: 0;
}

.hero-shapes::before,
.hero-shapes::after {
    content: '';
    position: absolute;
    width: 400px;
    height: 400px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
}

.hero-shapes::before {
    top: -200px;
    right: -200px;
    animation: float 8s ease-in-out infinite;
}

.hero-shapes::after {
    bottom: -200px;
    left: -200px;
    animation: float 8s ease-in-out infinite reverse;
}

@keyframes float {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-20px) scale(1.05); }
}

/* Featured Articles Grid */
.featured-articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.featured-article-card {
    background: var(--bs-body-bg);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid var(--bs-border-color);
    position: relative;
}

.featured-article-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    border-color: var(--bs-primary);
}

/* Article Image Container */
.article-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.article-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.featured-article-card:hover .article-image {
    transform: scale(1.1);
}

.article-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 3rem;
    opacity: 0.7;
}

/* Category Badge */
.category-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: var(--bs-primary);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

/* Reading Time */
.reading-time {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
}

/* Article Content */
.article-content {
    padding: 1.5rem;
}

.article-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--bs-border-color);
}

.author-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.author-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--bs-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
}

.author-details {
    flex: 1;
}

.author-name {
    font-weight: 600;
    color: var(--bs-body-color);
    font-size: 0.9rem;
    margin-bottom: 0.2rem;
}

.publish-date {
    font-size: 0.8rem;
    color: var(--bs-secondary);
}

.article-stats {
    display: flex;
    gap: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.8rem;
    color: var(--bs-secondary);
}

.stat-item i {
    font-size: 0.7rem;
}

/* Article Title */
.article-title {
    margin-bottom: 0.75rem;
}

.article-title a {
    color: var(--bs-body-color);
    text-decoration: none;
    font-size: 1.2rem;
    font-weight: 600;
    line-height: 1.4;
    transition: color 0.3s ease;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.article-title a:hover {
    color: var(--bs-primary);
}

/* Article Summary */
.article-summary {
    color: var(--bs-secondary);
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Article Footer */
.article-footer {
    display: flex;
    justify-content: flex-end;
}

.read-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--bs-primary);
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 25px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.read-more-btn:hover {
    background: var(--bs-primary);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.read-more-btn i {
    font-size: 0.8rem;
    transition: transform 0.3s ease;
}

.read-more-btn:hover i {
    transform: translateX(3px);
}

[data-bs-theme="dark"] .article-card {
    background: var(--bs-gray-800);
}

[data-bs-theme="dark"] .sidebar-widget {
    background: var(--bs-gray-800);
}

.sidebar-widget {
    border: none !important;
    border-radius: 1rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.sidebar-widget:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1) !important;
}

.badge {
    padding: 0.5em 1em;
    font-weight: 500;
    transition: all 0.2s ease;
}

.badge:hover {
    transform: translateY(-1px);
}

.slide-in-left {
    opacity: 0;
    transform: translateX(-30px);
    animation: slideIn 0.6s ease forwards;
    animation-delay: var(--animation-delay, 0s);
}

@keyframes slideIn {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Dark Mode Support */
[data-bs-theme="dark"] .featured-article-card {
    background: var(--bs-gray-800);
    border-color: var(--bs-gray-700);
}

[data-bs-theme="dark"] .article-meta {
    border-color: var(--bs-gray-700);
}

[data-bs-theme="dark"] .article-image-placeholder {
    background: linear-gradient(135deg, #4c1d95 0%, #1e1b4b 100%);
}

[data-bs-theme="dark"] .reading-time {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
}

/* Responsive styles */
@media (max-width: 1200px) {
    .featured-articles-grid {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .hero-section {
        border-radius: 0 0 1rem 1rem;
    }
    
    .featured-articles-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .featured-article-card {
        margin-bottom: 1rem;
    }
    
    .article-image-container {
        height: 180px;
    }
    
    .article-content {
        padding: 1.25rem;
    }
    
    .article-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .article-stats {
        align-self: flex-end;
    }
    
    .article-title a {
        font-size: 1.1rem;
    }
    
    .hero-section .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .featured-articles-grid {
        gap: 1rem;
    }
    
    .article-image-container {
        height: 160px;
    }
    
    .article-content {
        padding: 1rem;
    }
    
    .article-title a {
        font-size: 1rem;
    }
    
    .article-summary {
        font-size: 0.85rem;
    }
    
    .read-more-btn {
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
    }
}

/* Dark mode enhancements */
[data-bs-theme="dark"] .text-gradient {
    background: linear-gradient(120deg, #fff, #94a3b8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

[data-bs-theme="dark"] .hero-section {
    background: linear-gradient(135deg, #1e293b, #0f172a);
}

[data-bs-theme="dark"] .badge.bg-light {
    background-color: #2d3748 !important;
    color: #e2e8f0 !important;
}

/* Custom CSS Variables */
:root {
    --primary-color: #3b82f6;
    --primary-dark: #1d4ed8;
    --secondary-color: #64748b;
    --success-color: #22c55e;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --info-color: #3b82f6;
    --light-color: #f8fafc;
    --dark-color: #0f172a;
}

/* Additional Responsive Fixes */
@media (max-width: 576px) {
    .container {
        padding-left: 15px;
        padding-right: 15px;
    }
    
    .hero-section {
        padding: 2rem 0;
    }
    
    .hero-section h1 {
        font-size: 2rem;
    }
    
    .hero-section .lead {
        font-size: 1rem;
    }
    
    .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 1rem;
    }
    
    .article-card .card-title {
        font-size: 1.25rem;
    }
}

/* Enhancement for larger screens */
@media (min-width: 992px) {
    .hero-section {
        min-height: 60vh;
        display: flex;
        align-items: center;
    }
    
    .article-card .card-body {
        padding: 2rem;
    }
}
</style>

<!-- Script for current time -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateClock() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        document.getElementById('currentTime').textContent = now.toLocaleDateString('vi-VN', options);
    }
    updateClock();
    setInterval(updateClock, 60000);
});
</script>
