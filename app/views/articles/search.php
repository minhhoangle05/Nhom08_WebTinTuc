<div class="row mb-4">
    <div class="col">
        <h2 class="mb-4">
            <?php if (!empty($category)): ?>
                Bài viết trong danh mục: <?= htmlspecialchars($category) ?>
            <?php elseif (!empty($query)): ?>
                Kết quả tìm kiếm cho: "<?= htmlspecialchars($query) ?>"
            <?php else: ?>
                Tất cả bài viết
            <?php endif; ?>
        </h2>
        <?php if (empty($articles)): ?>
            <div class="alert alert-info">
                Không tìm thấy bài viết nào
                <?php if (!empty($category)): ?>
                    trong danh mục này
                <?php elseif (!empty($query)): ?>
                    phù hợp với tìm kiếm của bạn
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php foreach ($articles as $article): ?>
        <div class="col">
            <article class="card h-100">
                <?php if (!empty($article['featured_image'])): ?>
                    <img src="<?= htmlspecialchars($article['featured_image']) ?>" 
                         class="card-img-top" alt="<?= htmlspecialchars($article['title']) ?>"
                         style="height: 200px; object-fit: cover;">
                <?php endif; ?>
                
                <div class="card-body">
                    <h5 class="card-title">
                        <a href="<?= BASE_URL ?>/articles/<?= htmlspecialchars($article['slug']) ?>" 
                           class="text-decoration-none">
                            <?= htmlspecialchars($article['title']) ?>
                        </a>
                    </h5>
                    
                    <?php if (!empty($article['summary'])): ?>
                        <p class="card-text"><?= htmlspecialchars($article['summary']) ?></p>
                    <?php else: ?>
                        <p class="card-text"><?= substr(strip_tags($article['content']), 0, 150) ?>...</p>
                    <?php endif; ?>
                </div>
                
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="bi bi-calendar me-1"></i>
                            <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                        </div>
                        <?php if (!empty($article['category_name'])): ?>
                            <a href="<?= BASE_URL ?>/articles/search?category=<?= urlencode($article['category_name']) ?>" 
                               class="badge text-bg-primary text-decoration-none">
                                <?= htmlspecialchars($article['category_name']) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($article['tags'])): ?>
                        <div class="mt-2">
                            <?php foreach (explode(',', $article['tags']) as $tag): ?>
                                <a href="<?= BASE_URL ?>/articles/search?tag=<?= urlencode(trim($tag)) ?>" 
                                   class="badge text-bg-secondary text-decoration-none me-1">
                                    <?= htmlspecialchars(trim($tag)) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
</div>

<?php if (!empty($totalPages) && $totalPages > 1): ?>
<nav aria-label="Phân trang" class="mt-4">
    <ul class="pagination justify-content-center">
        <?php if ($currentPage > 1): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $this->buildPageUrl($currentPage - 1) ?>" aria-label="Trang trước">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                <a class="page-link" href="<?= $this->buildPageUrl($i) ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <?php if ($currentPage < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="<?= $this->buildPageUrl($currentPage + 1) ?>" aria-label="Trang sau">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
