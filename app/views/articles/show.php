<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/css/style.css" rel="stylesheet">
    
    <style>
        /* CSS cho AI Summary Section */
.ai-summary-section {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.btn-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
    color: white;
}

.btn-gradient:active {
    transform: translateY(0);
}

.btn-gradient:disabled {
    background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
    box-shadow: none;
    cursor: not-allowed;
}

#summaryContainer {
    animation: slideDown 0.4s ease-out;
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.summary-content {
    font-size: 1.05rem;
    line-height: 1.8;
    color: #374151;
}

.summary-text {
    padding: 1rem;
    background: linear-gradient(to right, #f9fafb 0%, #ffffff 100%);
    border-left: 4px solid #667eea;
    border-radius: 0.5rem;
    position: relative;
}

.summary-text::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, transparent 100%);
    border-radius: 0.5rem;
    pointer-events: none;
}

/* Responsive */
@media (max-width: 768px) {
    .btn-gradient {
        font-size: 0.95rem;
        padding: 0.75rem;
    }
    
    .summary-content {
        font-size: 1rem;
    }
}
        .bookmark-btn {
    transition: all 0.3s ease;
}

.bookmark-btn:not(.bookmarked):hover {
    background-color: #dc3545;
    color: white;
    transform: scale(1.05);
}

.bookmark-btn.bookmarked {
    background-color: #dc3545;
    color: white;
    border-color: #dc3545;
}

.bookmark-btn.bookmarked:hover {
    background-color: #bb2d3b;
    border-color: #bb2d3b;
}
#bookmarkCount {
    transition: transform 0.2s ease;
    display: inline-block;
}
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
            padding: 1.5rem;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 0.5rem;
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
        .related-articles .card {
            transition: transform 0.3s ease;
        }
        .related-articles .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>
    <article class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <header class="article-header">
                    <h1 class="mb-3"><?= htmlspecialchars($article['title']) ?></h1>
                    
                    <div class="article-meta d-flex flex-wrap align-items-center gap-3">
                        <span>
                            <i class="bi bi-person-circle me-1"></i>
                            <?= htmlspecialchars($article['author_name'] ?? 'Unknown') ?>
                        </span>
                        
                        <?php if ($article['category_name']): ?>
                        <span>
                            <i class="bi bi-folder me-1"></i>
                            <a href="<?= BASE_URL ?>/articles?category=<?= urlencode($article['category_slug']) ?>" 
                               class="text-decoration-none">
                                <?= htmlspecialchars($article['category_name']) ?>
                            </a>
                        </span>
                        <?php endif; ?>
                        
                        <span>
                            <i class="bi bi-calendar3 me-1"></i>
                            <?= date('d/m/Y H:i', strtotime($article['created_at'])) ?>
                        </span>
                        
                        <span>
                            <i class="bi bi-eye me-1"></i>
                            <?= number_format($article['views'] ?? 0) ?>
                        </span>
                        
                        <span>
                            <i class="bi bi-chat-dots me-1"></i>
                            <?= number_format($article['comment_count'] ?? 0) ?>
                        </span>
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
                         class="img-fluid rounded shadow">
                </div>
                <?php endif; ?>
<!-- AI Summary Section - Thêm phần này sau article-summary -->
<div class="ai-summary-section mb-4">
    <button id="summarizeBtn" class="btn btn-gradient btn-lg w-100">
        <i class="bi bi-stars me-2"></i>Tóm tắt bài viết bằng AI
    </button>
    
    <!-- Container hiển thị kết quả tóm tắt -->
    <div id="summaryContainer" class="card mt-3 d-none">
        <div class="card-header bg-primary bg-gradient text-white">
            <h6 class="mb-0">
                <i class="bi bi-lightbulb me-2"></i>Tóm tắt nội dung
            </h6>
        </div>
        <div class="card-body">
            <div id="summaryContent" class="summary-content"></div>
        </div>
        <div class="card-footer text-muted small">
            <i class="bi bi-info-circle me-1"></i>
            Tóm tắt được tạo tự động bằng AI
        </div>
    </div>
</div>
                <div class="article-content">
                    <?= nl2br(htmlspecialchars($article['content'])) ?>
                </div>

                <?php if (!empty($tags)): ?>
                <div class="mt-4 mb-4">
                    <h5><i class="bi bi-tags me-2"></i>Tags:</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($tags as $tag): ?>
                            <a href="<?= BASE_URL ?>/articles?tag=<?= urlencode($tag['name']) ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-tag me-1"></i><?= htmlspecialchars($tag['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                
                <?php
// Kiểm tra xem user đã bookmark bài viết này chưa
$isBookmarked = false;
if (\App\Core\Auth::check()) {
    $bookmarkModel = new \App\Models\Bookmark();
    $isBookmarked = $bookmarkModel->isBookmarked(\App\Core\Auth::id(), $article['id']);
}
?>

<!-- Bookmark Card -->
<?php if (\App\Core\Auth::check()): ?>
<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <h6 class="mb-0">
            <i class="bi bi-bookmark-heart me-2"></i>Lưu bài viết
        </h6>
    </div>
    <div class="card-body text-center">
        <button id="bookmarkBtn" 
                class="btn btn-outline-danger w-100 bookmark-btn <?= $isBookmarked ? 'bookmarked' : '' ?>"
                data-article-id="<?= $article['id'] ?>"
                data-bookmarked="<?= $isBookmarked ? 'true' : 'false' ?>">
            <i class="bi bi-bookmark-heart<?= $isBookmarked ? '-fill' : '' ?> me-2"></i>
            <span class="bookmark-text"><?= $isBookmarked ? 'Đã lưu' : 'Lưu bài viết' ?></span>
        </button>
        <p class="small text-muted mt-2 mb-0">
            <span id="bookmarkCount">0</span> người đã lưu
        </p>
    </div>
</div>
<?php endif; ?>
                <!-- Related Articles -->
                <?php if (!empty($relatedArticles)): ?>
                <div class="mt-5 related-articles">
                    <h4 class="mb-4">
                        <i class="bi bi-newspaper me-2"></i>Bài viết liên quan
                    </h4>
                    <div class="row g-4">
                        <?php foreach ($relatedArticles as $related): ?>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <?php if ($related['featured_image']): ?>
                                <img src="<?= BASE_URL ?>/uploads/articles/<?= htmlspecialchars($related['featured_image']) ?>" 
                                     class="card-img-top" alt="<?= htmlspecialchars($related['title']) ?>"
                                     style="height: 150px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <a href="<?= BASE_URL ?>/article/<?= urlencode($related['slug']) ?>" 
                                           class="text-decoration-none">
                                            <?= htmlspecialchars($related['title']) ?>
                                        </a>
                                    </h6>
                                    <p class="card-text small text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?= date('d/m/Y', strtotime($related['created_at'])) ?>
                                        <span class="ms-2">
                                            <i class="bi bi-eye me-1"></i>
                                            <?= number_format($related['views'] ?? 0) ?>
                                        </span>
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
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
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
                               target="_blank" class="share-btn btn btn-info text-white">
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
    <div data-article-id="<?= $article['id'] ?>">
        <?php include APP_PATH . '/views/articles/comments.php'; ?>
    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/js/article-ai.js"></script>
    <!-- Pass PHP variables to JavaScript -->
    <script>
        // Global configuration
        window.BASE_URL = '<?= BASE_URL ?>';
        
        // Current user data (if logged in)
        <?php if (\App\Core\Auth::check()): ?>
        window.currentUser = {
            id: <?= \App\Core\Auth::user()['id'] ?>,
            name: <?= json_encode(\App\Core\Auth::user()['name']) ?>,
            email: <?= json_encode(\App\Core\Auth::user()['email']) ?>,
            role_id: <?= \App\Core\Auth::user()['role_id'] ?? 1 ?>
        };
        <?php else: ?>
        window.currentUser = null;
        <?php endif; ?>
        
        // Admin check
        window.isAdmin = <?= \App\Core\Auth::check() && \App\Core\Auth::isAdmin() ? 'true' : 'false' ?>;
    </script>
    
    <!-- Comments System JS -->
    <script src="<?= BASE_URL ?>/js/comments.js"></script>
    
    <script>
        function copyToClipboard() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
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
                const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
                bsToast.show();
                
                toast.addEventListener('hidden.bs.toast', () => {
                    toast.remove();
                });
            }).catch(() => {
                alert('Không thể copy link. Vui lòng thử lại.');
            });
        }
        // Bookmark functionality
document.addEventListener('DOMContentLoaded', function() {
    const bookmarkBtn = document.getElementById('bookmarkBtn');
    const bookmarkCountEl = document.getElementById('bookmarkCount');
    
    if (!bookmarkBtn) return;
    
    // Load bookmark count khi trang load
    loadBookmarkCount();
    
    bookmarkBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const articleId = this.dataset.articleId;
        
        // Disable button during request
        this.disabled = true;
        
        try {
            console.log('Sending bookmark request for article:', articleId);
            
            const response = await fetch('<?= BASE_URL ?>/bookmarks/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ article_id: parseInt(articleId) })
            });
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                // Update button state
                updateBookmarkUI(data.bookmarked, data.count);
                
                // Show toast
                showToast('success', data.message);
            } else {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    showToast('error', data.message || 'Có lỗi xảy ra');
                }
            }
        } catch (error) {
            console.error('Bookmark error:', error);
            showToast('error', 'Không thể kết nối đến server');
        } finally {
            this.disabled = false;
        }
    });
    
    function updateBookmarkUI(isBookmarked, count) {
        const icon = bookmarkBtn.querySelector('i');
        const text = bookmarkBtn.querySelector('.bookmark-text');
        
        if (isBookmarked) {
            bookmarkBtn.classList.add('bookmarked');
            icon.classList.remove('bi-bookmark-heart');
            icon.classList.add('bi-bookmark-heart-fill');
            text.textContent = 'Đã lưu';
            bookmarkBtn.dataset.bookmarked = 'true';
        } else {
            bookmarkBtn.classList.remove('bookmarked');
            icon.classList.remove('bi-bookmark-heart-fill');
            icon.classList.add('bi-bookmark-heart');
            text.textContent = 'Lưu bài viết';
            bookmarkBtn.dataset.bookmarked = 'false';
        }
        
        // Update count with animation
        if (count !== undefined && bookmarkCountEl) {
            bookmarkCountEl.textContent = count;
            bookmarkCountEl.style.transform = 'scale(1.3)';
            setTimeout(() => {
                bookmarkCountEl.style.transform = 'scale(1)';
            }, 200);
        }
    }
    
    async function loadBookmarkCount() {
        try {
            const articleId = bookmarkBtn.dataset.articleId;
            const response = await fetch('<?= BASE_URL ?>/bookmarks/check?article_id=' + articleId);
            
            if (!response.ok) return;
            
            const data = await response.json();
            console.log('Bookmark check:', data);
            
            if (data.success) {
                // Update count
                if (data.count !== undefined && bookmarkCountEl) {
                    bookmarkCountEl.textContent = data.count;
                }
                
                // Update button state nếu đã bookmark
                if (data.bookmarked) {
                    updateBookmarkUI(true, data.count);
                }
            }
        } catch (error) {
            console.error('Error loading bookmark count:', error);
        }
    }
});

function showToast(type, message) {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}
    </script>
</body>
</html>