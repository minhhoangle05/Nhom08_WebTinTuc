<?php require __DIR__ . '/../layouts/main.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <?php if ($article['featured_image']): ?>
                <img src="<?= BASE_URL ?>/public/uploads/articles/<?= htmlspecialchars($article['featured_image']) ?>"
                     class="img-fluid rounded mb-4" alt="<?= htmlspecialchars($article['title']) ?>">
            <?php endif; ?>

            <h1 class="mb-3"><?= htmlspecialchars($article['title']) ?></h1>

            <?php if ($article['summary']): ?>
                <div class="lead mb-4">
                    <?= htmlspecialchars($article['summary']) ?>
                </div>
            <?php endif; ?>

            <div class="article-content">
                <?= $article['content'] ?>
            </div>

            <?php if (!empty($tags)): ?>
                <div class="mt-4">
                    <?php foreach ($tags as $tag): ?>
                        <a href="<?= BASE_URL ?>/articles?tag=<?= urlencode($tag['name']) ?>" class="badge bg-secondary text-decoration-none">
                            <?= htmlspecialchars($tag['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Thông tin bài viết</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <small class="text-muted">Trạng thái:</small>
                            <br>
                            <span class="badge bg-warning">Preview</span>
                        </li>
                        <?php if ($article['publish_at']): ?>
                            <li class="mb-2">
                                <small class="text-muted">Dự kiến xuất bản:</small>
                                <br>
                                <?= date('d/m/Y H:i', strtotime($article['publish_at'])) ?>
                            </li>
                        <?php endif; ?>
                        <li class="mb-2">
                            <small class="text-muted">Thời gian đọc:</small>
                            <br>
                            <?= $article['reading_time'] ?> phút
                        </li>
                        <?php if ($article['category_name']): ?>
                            <li class="mb-2">
                                <small class="text-muted">Chuyên mục:</small>
                                <br>
                                <a href="<?= BASE_URL ?>/articles?category=<?= urlencode($article['category_name']) ?>">
                                    <?= htmlspecialchars($article['category_name']) ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Tùy chọn</h5>
                    <div class="d-grid gap-2">
                        <a href="<?= BASE_URL ?>/article/<?= $article['id'] ?>/edit" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Tiếp tục chỉnh sửa
                        </a>
                        <?php if ($article['status'] === 'draft'): ?>
                            <form action="<?= BASE_URL ?>/article/<?= $article['id'] ?>/publish" method="POST">
                                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check"></i> Xuất bản ngay
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($article['meta_description']) || !empty($article['meta_keywords'])): ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">SEO</h5>
                        <?php if (!empty($article['meta_description'])): ?>
                            <div class="mb-3">
                                <small class="text-muted">Meta Description:</small>
                                <p class="mb-0"><?= htmlspecialchars($article['meta_description']) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($article['meta_keywords'])): ?>
                            <div>
                                <small class="text-muted">Meta Keywords:</small>
                                <p class="mb-0"><?= htmlspecialchars($article['meta_keywords']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>