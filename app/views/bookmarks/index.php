<div class="container my-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="fw-bold mb-2">
                        <i class="bi bi-bookmark-heart-fill text-danger me-2"></i>
                        Bài viết yêu thích
                    </h1>
                    <p class="text-muted mb-0">
                        Bạn đã lưu <?= number_format($total) ?> bài viết
                    </p>
                </div>
                <a href="<?= BASE_URL ?>/articles" class="btn btn-outline-primary">
                    <i class="bi bi-journal-text me-2"></i>Khám phá thêm
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['flash_success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); endif; ?>

    <?php if (empty($bookmarks)): ?>
        <!-- Empty State -->
        <div class="text-center py-5">
            <div class="empty-state">
                <i class="bi bi-bookmark display-1 text-muted mb-4"></i>
                <h3 class="mb-3">Chưa có bài viết yêu thích</h3>
                <p class="text-muted mb-4">
                    Hãy khám phá và lưu lại những bài viết bạn yêu thích để đọc lại sau
                </p>
                <a href="<?= BASE_URL ?>/articles" class="btn btn-primary btn-lg">
                    <i class="bi bi-compass me-2"></i>Khám phá bài viết
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Bookmarks Grid -->
        <div class="row g-4">
            <?php foreach ($bookmarks as $article): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card bookmark-card h-100 shadow-sm">
                    <!-- Article Image -->
                    <div class="bookmark-image-container">
                        <?php if ($article['featured_image']): ?>
                            <img src="<?= BASE_URL ?>/uploads/articles/<?= htmlspecialchars($article['featured_image']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($article['title']) ?>">
                        <?php else: ?>
                            <div class="bookmark-image-placeholder">
                                <i class="bi bi-image"></i>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Remove Button -->
                        <button class="btn btn-remove-bookmark" 
                                data-article-id="<?= $article['id'] ?>"
                                title="Xóa khỏi yêu thích">
                            <i class="bi bi-bookmark-fill"></i>
                        </button>
                    </div>

                    <div class="card-body">
                        <!-- Category Badge -->
                        <?php if ($article['category_name']): ?>
                        <a href="<?= BASE_URL ?>/articles/search?category=<?= urlencode($article['category_name']) ?>" 
                           class="badge bg-primary text-decoration-none mb-2">
                            <?= htmlspecialchars($article['category_name']) ?>
                        </a>
                        <?php endif; ?>

                        <!-- Title -->
                        <h5 class="card-title mb-3">
                            <a href="<?= BASE_URL ?>/article/<?= urlencode($article['slug']) ?>" 
                               class="text-decoration-none text-dark">
                                <?= htmlspecialchars($article['title']) ?>
                            </a>
                        </h5>

                        <!-- Summary -->
                        <?php if ($article['summary']): ?>
                        <p class="card-text text-muted small mb-3">
                            <?= htmlspecialchars(substr($article['summary'], 0, 100)) ?>...
                        </p>
                        <?php endif; ?>

                        <!-- Meta Info -->
                        <div class="d-flex justify-content-between align-items-center text-muted small">
                            <div>
                                <i class="bi bi-person me-1"></i>
                                <?= htmlspecialchars($article['author_name']) ?>
                            </div>
                            <div>
                                <i class="bi bi-eye me-1"></i>
                                <?= number_format($article['views']) ?>
                            </div>
                        </div>

                        <!-- Bookmarked Date -->
                        <div class="text-muted small mt-2">
                            <i class="bi bi-clock me-1"></i>
                            Đã lưu: <?= date('d/m/Y', strtotime($article['bookmarked_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Bookmark pagination" class="mt-5">
            <ul class="pagination justify-content-center">
                <!-- Previous -->
                <li class="page-item <?= $currentPage == 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>

                <!-- Page Numbers -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 2 && $i <= $currentPage + 2)): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php elseif ($i == $currentPage - 3 || $i == $currentPage + 3): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>
                <?php endfor; ?>

                <!-- Next -->
                <li class="page-item <?= $currentPage == $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.bookmark-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    overflow: hidden;
}

.bookmark-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.bookmark-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bookmark-image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.bookmark-card:hover .bookmark-image-container img {
    transform: scale(1.1);
}

.bookmark-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}

.btn-remove-bookmark {
    position: absolute;
    top: 12px;
    right: 12px;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #dc3545;
    font-size: 1.2rem;
    transition: all 0.3s ease;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.btn-remove-bookmark:hover {
    background: #dc3545;
    color: white;
    transform: scale(1.1);
}

.card-title a:hover {
    color: var(--bs-primary) !important;
}

.empty-state {
    padding: 3rem 1rem;
}

.empty-state i {
    opacity: 0.3;
}

/* Dark mode support */
[data-bs-theme="dark"] .bookmark-card {
    background: var(--bs-gray-800);
    border-color: var(--bs-gray-700);
}

[data-bs-theme="dark"] .card-title a {
    color: var(--bs-light) !important;
}

[data-bs-theme="dark"] .btn-remove-bookmark {
    background: rgba(0, 0, 0, 0.7);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle remove bookmark
    document.querySelectorAll('.btn-remove-bookmark').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const articleId = this.dataset.articleId;
            const card = this.closest('.col-lg-4');
            
            if (!confirm('Bạn có chắc muốn xóa bài viết này khỏi danh sách yêu thích?')) {
                return;
            }
            
            try {
                const response = await fetch('<?= BASE_URL ?>/bookmarks/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ article_id: articleId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Fade out and remove card
                    card.style.transition = 'opacity 0.3s ease';
                    card.style.opacity = '0';
                    
                    setTimeout(() => {
                        card.remove();
                        
                        // Check if no more bookmarks
                        const remainingCards = document.querySelectorAll('.bookmark-card').length;
                        if (remainingCards === 0) {
                            location.reload();
                        }
                    }, 300);
                    
                    // Show success message
                    showToast('success', data.message);
                } else {
                    showToast('error', data.message || 'Có lỗi xảy ra');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('error', 'Không thể kết nối đến server');
            }
        });
    });
});

function showToast(type, message) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed bottom-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>