<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?= BASE_URL ?>">Tin tức</a>
        </div>
    </nav>

    <article class="container">
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
                 class="img-fluid">
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
</article>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>


