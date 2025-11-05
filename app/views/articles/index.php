<style>
.search-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 3rem 0;
    margin-bottom: 2rem;
    border-radius: 0.5rem;
    color: white;
}

.search-box {
    background: white;
    border-radius: 50px;
    padding: 0.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.search-box input {
    border: none;
    padding: 0.5rem 1rem;
    font-size: 1rem;
}

.search-box input:focus {
    outline: none;
    box-shadow: none;
}

.search-box button {
    border-radius: 50px;
    padding: 0.5rem 2rem;
}

.filter-card {
    position: sticky;
    top: 20px;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.filter-section {
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 0;
}

.filter-section:last-child {
    border-bottom: none;
}

.filter-section h6 {
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: #495057;
}

.category-badge {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    margin: 0.25rem;
    border-radius: 20px;
    font-size: 0.85rem;
    transition: all 0.3s;
    cursor: pointer;
    border: 2px solid #e9ecef;
}

.category-badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.category-badge.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.article-card {
    transition: all 0.3s;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    height: 100%;
}

.article-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

.article-card .card-img-top {
    height: 220px;
    object-fit: cover;
    border-radius: 0.5rem 0.5rem 0 0;
}

.article-title {
    font-size: 1.1rem;
    font-weight: 600;
    line-height: 1.4;
    margin-bottom: 0.75rem;
}

.article-title a {
    color: #212529;
    text-decoration: none;
    transition: color 0.3s;
}

.article-title a:hover {
    color: #667eea;
}

.article-meta {
    font-size: 0.85rem;
    color: #6c757d;
}

.article-meta i {
    margin-right: 0.25rem;
}

.sort-dropdown {
    border-radius: 20px;
    padding: 0.5rem 1rem;
    border: 2px solid #e9ecef;
}

.results-header {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.tag-cloud .badge {
    margin: 0.25rem;
    padding: 0.5rem 0.75rem;
    font-weight: 500;
    transition: all 0.3s;
}

.tag-cloud .badge:hover {
    transform: scale(1.05);
}

.no-results {
    text-align: center;
    padding: 3rem 0;
}

.no-results i {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .filter-card {
        position: relative;
        top: 0;
        margin-bottom: 2rem;
    }
    
    .search-hero {
        padding: 2rem 0;
    }
}
</style>

<!-- Search Hero Section -->
<div class="search-hero">
    <div class="container">
        <h1 class="text-center mb-4">
            <i class="bi bi-search me-2"></i>
            <?php if ($currentCategory): ?>
                <?= htmlspecialchars($currentCategory['name']) ?>
            <?php else: ?>
                Khám phá bài viết
            <?php endif; ?>
        </h1>
        
        <!-- Search Form -->
        <form method="GET" action="<?= BASE_URL ?>/articles/search" class="search-box mx-auto" style="max-width: 600px;">
            <div class="input-group">
                <input type="text" 
                       name="q" 
                       class="form-control border-0" 
                       placeholder="Tìm kiếm bài viết..." 
                       value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search me-1"></i> Tìm kiếm
                </button>
            </div>
        </form>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card filter-card">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-funnel me-2"></i>Bộ lọc
                    </h5>
                    
                    <!-- Categories Filter -->
                    <div class="filter-section">
                        <h6><i class="bi bi-folder me-2"></i>Danh mục</h6>
                        <div>
                            <a href="<?= BASE_URL ?>/articles" 
                               class="category-badge <?= !$currentCategory ? 'active' : '' ?>">
                                <i class="bi bi-grid-3x3-gap me-1"></i>Tất cả
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="<?= BASE_URL ?>/articles/search?category=<?= urlencode($cat['slug']) ?>" 
                                   class="category-badge <?= $currentCategory && $currentCategory['id'] == $cat['id'] ? 'active' : '' ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                    <span class="badge bg-light text-dark ms-1"><?= $cat['article_count'] ?? 0 ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Sort Options -->
                    <div class="filter-section">
                        <h6><i class="bi bi-sort-down me-2"></i>Sắp xếp</h6>
                        <form method="GET" action="<?= BASE_URL ?>/articles/search" id="sortForm">
                            <?php if (isset($_GET['category'])): ?>
                                <input type="hidden" name="category" value="<?= htmlspecialchars($_GET['category']) ?>">
                            <?php endif; ?>
                            <?php if (isset($_GET['q'])): ?>
                                <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q']) ?>">
                            <?php endif; ?>
                            
                            <select name="sort" class="form-select sort-dropdown" onchange="this.form.submit()">
                                <option value="latest" <?= $currentSort === 'latest' ? 'selected' : '' ?>>
                                    Mới nhất
                                </option>
                                <option value="oldest" <?= $currentSort === 'oldest' ? 'selected' : '' ?>>
                                    Cũ nhất
                                </option>
                                <option value="popular" <?= $currentSort === 'popular' ? 'selected' : '' ?>>
                                    Phổ biến nhất
                                </option>
                                <option value="title" <?= $currentSort === 'title' ? 'selected' : '' ?>>
                                    Theo tên A-Z
                                </option>
                            </select>
                        </form>
                    </div>
                    
                    <!-- Popular Articles -->
                    <?php if (!empty($popularArticles)): ?>
                    <div class="filter-section">
                        <h6><i class="bi bi-fire me-2"></i>Bài viết phổ biến</h6>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($popularArticles, 0, 5) as $article): ?>
                                <a href="<?= BASE_URL ?>/article/<?= htmlspecialchars($article['slug']) ?>" 
                                   class="list-group-item list-group-item-action border-0 px-0">
                                    <div class="d-flex align-items-start">
                                        <?php if (!empty($article['featured_image'])): ?>
                                            <img src="<?= BASE_URL ?>/uploads/articles/<?= htmlspecialchars($article['featured_image']) ?>" 
                                                 alt="" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php endif; ?>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold small mb-1">
                                                <?= htmlspecialchars(mb_substr($article['title'], 0, 60)) ?><?= mb_strlen($article['title']) > 60 ? '...' : '' ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="bi bi-eye me-1"></i><?= number_format($article['views'] ?? 0) ?>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Results Header -->
            <div class="results-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h5 class="mb-0">
                            <?php if (!empty($_GET['q'])): ?>
                                Kết quả cho "<strong><?= htmlspecialchars($_GET['q']) ?></strong>"
                            <?php elseif ($currentCategory): ?>
                                <?= htmlspecialchars($currentCategory['name']) ?>
                            <?php else: ?>
                                Tất cả bài viết
                            <?php endif; ?>
                        </h5>
                        <small class="text-muted">
                            Tìm thấy <?= number_format($total) ?> bài viết
                        </small>
                    </div>
                    
                    <div class="mt-2 mt-md-0">
                        <span class="badge bg-primary">Trang <?= $currentPage ?>/<?= $totalPages ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Articles Grid -->
            <?php if (!empty($articles)): ?>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php foreach ($articles as $article): ?>
                        <div class="col">
                            <article class="card article-card">
                                <?php if (!empty($article['featured_image'])): ?>
                                    <img src="<?= BASE_URL ?>/uploads/articles/<?= htmlspecialchars($article['featured_image']) ?>" 
                                         class="card-img-top" 
                                         alt="<?= htmlspecialchars($article['title']) ?>">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 220px;">
                                        <i class="bi bi-file-text" style="font-size: 3rem; color: #dee2e6;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="article-title">
                                        <a href="<?= BASE_URL ?>/article/<?= htmlspecialchars($article['slug']) ?>">
                                            <?= htmlspecialchars($article['title']) ?>
                                        </a>
                                    </h5>
                                    
                                    <?php if (!empty($article['summary'])): ?>
                                        <p class="card-text text-muted">
                                            <?= htmlspecialchars(mb_substr($article['summary'], 0, 120)) ?><?= mb_strlen($article['summary']) > 120 ? '...' : '' ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="article-meta d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-person-circle"></i>
                                            <?= htmlspecialchars($article['author_name'] ?? 'Unknown') ?>
                                        </div>
                                        <div>
                                            <i class="bi bi-calendar3"></i>
                                            <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-transparent border-0 pt-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if (!empty($article['category_name'])): ?>
                                                <a href="<?= BASE_URL ?>/articles/search?category=<?= urlencode($article['category_slug']) ?>" 
                                                   class="badge bg-primary text-decoration-none">
                                                    <i class="bi bi-folder me-1"></i><?= htmlspecialchars($article['category_name']) ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="bi bi-eye-fill me-1"></i><?= number_format($article['views'] ?? 0) ?>
                                            <i class="bi bi-chat-dots ms-2 me-1"></i><?= $article['comment_count'] ?? 0 ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($article['tags'])): ?>
                                        <div class="tag-cloud mt-2">
                                            <?php foreach (array_slice(explode(',', $article['tags']), 0, 3) as $tag): ?>
                                                <a href="<?= BASE_URL ?>/articles/search?tag=<?= urlencode(trim($tag)) ?>" 
                                                   class="badge bg-light text-dark text-decoration-none">
                                                    <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars(trim($tag)) ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Phân trang" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php if ($currentPage > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $baseUrl ?>page=<?= $currentPage - 1 ?>">
                                        <i class="bi bi-chevron-left"></i> Trước
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $currentPage - 2);
                            $end = min($totalPages, $currentPage + 2);
                            
                            if ($start > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $baseUrl ?>page=1">1</a>
                                </li>
                                <?php if ($start > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start; $i <= $end; $i++): ?>
                                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $baseUrl ?>page=<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($end < $totalPages): ?>
                                <?php if ($end < $totalPages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $baseUrl ?>page=<?= $totalPages ?>"><?= $totalPages ?></a>
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($currentPage < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= $baseUrl ?>page=<?= $currentPage + 1 ?>">
                                        Sau <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- No Results -->
                <div class="no-results">
                    <i class="bi bi-inbox"></i>
                    <h4 class="text-muted">Không tìm thấy bài viết nào</h4>
                    <p class="text-muted">
                        <?php if (!empty($_GET['q'])): ?>
                            Không tìm thấy kết quả phù hợp với "<strong><?= htmlspecialchars($_GET['q']) ?></strong>"
                        <?php elseif ($currentCategory): ?>
                            Chưa có bài viết nào trong danh mục này
                        <?php else: ?>
                            Hãy thử điều chỉnh bộ lọc hoặc từ khóa tìm kiếm
                        <?php endif; ?>
                    </p>
                    <a href="<?= BASE_URL ?>/articles" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>Xem tất cả bài viết
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Smooth scroll to top when changing pages
document.querySelectorAll('.pagination a').forEach(link => {
    link.addEventListener('click', function(e) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});
</script>