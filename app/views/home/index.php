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
                <?php foreach ($featured as $index => $article): ?>
                    <article class="card article-card mb-4 border-0 shadow-sm slide-in-left" 
                             style="--animation-delay: <?= $index * 0.1 ?>s;">
                        <div class="row g-0">
                            <?php if ($article['featured_image']): ?>
                                <div class="col-md-4">
                                    <img src="<?= BASE_URL ?>/uploads/articles/<?= htmlspecialchars($article['featured_image']) ?>" 
                                         class="img-fluid rounded-start h-100" 
                                         style="object-fit: cover;"
                                         alt="<?= htmlspecialchars($article['title']) ?>">
                                </div>
                            <?php endif; ?>
                            
                            <div class="col-md-<?= $article['featured_image'] ? '8' : '12' ?>">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2 gap-2">
                                        <span class="badge bg-primary"><?= htmlspecialchars($article['category_name'] ?? 'Chung') ?></span>
                                        <span class="text-muted small">
                                            <i class="bi bi-eye me-1"></i><?= number_format($article['views']) ?> lượt xem
                                        </span>
                                        <span class="text-muted small">
                                            <i class="bi bi-chat-dots me-1"></i><?= number_format($article['comment_count']) ?> bình luận
                                        </span>
                                    </div>
                                    
                                    <h3 class="card-title h4">
                                        <a href="<?= BASE_URL ?>/article/<?= urlencode($article['slug']) ?>" class="text-decoration-none stretched-link">
                                            <?= htmlspecialchars($article['title']) ?>
                                        </a>
                                    </h3>
                                    
                                    <?php if ($article['summary']): ?>
                                        <p class="card-text text-muted"><?= htmlspecialchars($article['summary']) ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex align-items-center mt-3">
                                        <img src="<?= BASE_URL ?>/uploads/avatars/<?= $article['author_avatar'] ?? 'default.jpg' ?>" 
                                             class="rounded-circle me-2" 
                                             width="32" height="32" 
                                             alt="<?= htmlspecialchars($article['author_name']) ?>">
                                        <div class="small">
                                            <div class="fw-bold"><?= htmlspecialchars($article['author_name']) ?></div>
                                            <div class="text-muted">
                                                <time datetime="<?= $article['created_at'] ?>" data-timestamp="<?= strtotime($article['created_at']) ?>">
                                                    <?= date('d/m/Y H:i', strtotime($article['created_at'])) ?>
                                                </time>
                                                • <?= $article['reading_time'] ?? '5' ?> phút đọc
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
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

.article-card {
    transition: all 0.3s ease;
    border: none !important;
    overflow: hidden;
}

.article-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.article-card .card-title a {
    color: var(--bs-body-color);
    text-decoration: none;
    transition: color 0.2s ease;
}

.article-card .card-title a:hover {
    color: var(--primary-color);
}

.article-card img {
    transition: transform 0.3s ease;
}

.article-card:hover img {
    transform: scale(1.05);
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

/* Responsive styles */
@media (max-width: 768px) {
    .hero-section {
        border-radius: 0 0 1rem 1rem;
    }
    
    .article-card .col-md-4 {
        max-height: 200px;
        overflow: hidden;
    }

    .article-card .card-body {
        padding: 1rem;
    }

    .hero-section .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
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
