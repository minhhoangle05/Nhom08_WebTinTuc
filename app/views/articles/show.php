<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/css/style.css" rel="stylesheet">
    <style>
        .article-header {
            border-bottom: 1px solid #eee;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
        }
        .article-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .article-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }
        .article-summary {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
        }
        .social-share {
            position: sticky;
            top: 100px;
        }
        .share-btn {
            display: block;
            width: 100%;
            margin-bottom: 0.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .share-btn:hover {
            transform: translateX(5px);
        }
    </style>
</head>
<body>

    <article class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <header class="article-header">
                    <h1 class="mb-3"><?= htmlspecialchars($article['title']) ?></h1>
                    
                    <div class="article-meta">
                        <span class="me-3">Tác giả: <?= htmlspecialchars($article['author_name'] ?? 'Unknown') ?></span>
                        
                        <?php if ($article['category_name']): ?>
                        <span class="me-3">
                            Danh mục: <a href="<?= BASE_URL ?>/articles?category=<?= urlencode($article['category_slug']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($article['category_name']) ?>
                            </a>
                        </span>
                        <?php endif; ?>
                        
                        <span class="me-3">Ngày đăng: <?= date('d/m/Y H:i', strtotime($article['created_at'])) ?></span>
                        
                        <span>Lượt xem: <?= number_format($article['views'] ?? 0) ?></span>
                    </div>
                </header>

                <?php if ($article['summary']): ?>
                <div class="article-summary">
                    <?= htmlspecialchars($article['summary']) ?>
                </div>
                <?php endif; ?>

                <?php if ($article['featured_image']): ?>
                <div class="text-center mb-4">
                    <img src="<?= BASE_URL ?>/uploads/articles/<?= htmlspecialchars($article['featured_image']) ?>"
                         alt="<?= htmlspecialchars($article['title']) ?>"
                         class="img-fluid rounded">
                </div>
                <?php endif; ?>

                <div class="article-content">
                    <?= nl2br(htmlspecialchars($article['content'])) ?>
                </div>

                <?php if (!empty($tags)): ?>
                <div class="mt-4 mb-4">
                    <h5>Tags:</h5>
                    <?php foreach ($tags as $tag): ?>
                        <a href="<?= BASE_URL ?>/articles?tag=<?= urlencode($tag['name']) ?>" 
                           class="btn btn-sm btn-outline-secondary me-2 mb-2">
                            <?= htmlspecialchars($tag['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Social Share -->
                <div class="mt-4 mb-4">
                    <h5>Chia sẻ:</h5>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/article/' . $article['slug']) ?>" 
                           target="_blank" class="btn btn-primary btn-sm">
                            <i class="bi bi-facebook me-1"></i>Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(BASE_URL . '/article/' . $article['slug']) ?>&text=<?= urlencode($article['title']) ?>" 
                           target="_blank" class="btn btn-info btn-sm">
                            <i class="bi bi-twitter me-1"></i>Twitter
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(BASE_URL . '/article/' . $article['slug']) ?>" 
                           target="_blank" class="btn btn-secondary btn-sm">
                            <i class="bi bi-linkedin me-1"></i>LinkedIn
                        </a>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard()">
                            <i class="bi bi-link-45deg me-1"></i>Copy Link
                        </button>
                    </div>
                </div>

                <!-- Related Articles -->
                <?php if (!empty($relatedArticles)): ?>
                <div class="mt-5">
                    <h4>Bài viết liên quan</h4>
                    <div class="row">
                        <?php foreach ($relatedArticles as $related): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <a href="<?= BASE_URL ?>/article/<?= urlencode($related['slug']) ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($related['title']) ?>
                                        </a>
                                    </h6>
                                    <p class="card-text small text-muted">
                                        <?= date('d/m/Y', strtotime($related['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="social-share">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-share me-2"></i>Chia sẻ bài viết
                            </h6>
                        </div>
                        <div class="card-body">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/article/' . $article['slug']) ?>" 
                               target="_blank" class="share-btn btn btn-primary">
                                <i class="bi bi-facebook me-2"></i>Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode(BASE_URL . '/article/' . $article['slug']) ?>&text=<?= urlencode($article['title']) ?>" 
                               target="_blank" class="share-btn btn btn-info">
                                <i class="bi bi-twitter me-2"></i>Twitter
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(BASE_URL . '/article/' . $article['slug']) ?>" 
                               target="_blank" class="share-btn btn btn-secondary">
                                <i class="bi bi-linkedin me-2"></i>LinkedIn
                            </a>
                            <button class="share-btn btn btn-outline-secondary" onclick="copyToClipboard()">
                                <i class="bi bi-link-45deg me-2"></i>Copy Link
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </article>

    <!-- Comments Section -->
    <?php include APP_PATH . '/views/articles/comments.php'; ?>

    <?php include APP_PATH . '/views/layouts/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Comments JS -->
    <script src="<?= BASE_URL ?>/js/comments.js"></script>
    
    <script>
        function copyToClipboard() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                // Show success message
                const toast = document.createElement('div');
                toast.className = 'toast align-items-center text-white bg-success border-0';
                toast.setAttribute('role', 'alert');
                toast.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-check-circle me-2"></i>
                            Đã copy link vào clipboard!
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                `;
                
                let toastContainer = document.querySelector('.toast-container');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                    document.body.appendChild(toastContainer);
                }
                
                toastContainer.appendChild(toast);
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
                
                toast.addEventListener('hidden.bs.toast', () => {
                    toast.remove();
                });
            }).catch(() => {
                alert('Không thể copy link. Vui lòng thử lại.');
            });
        }
    </script>
</body>
</html>