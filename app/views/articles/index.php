<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Bài viết') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px 0; margin-bottom: 30px; }
        header h1 { font-size: 2rem; color: #2c3e50; }
        .content { display: grid; grid-template-columns: 1fr 300px; gap: 30px; }
        .articles-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .article-card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .article-card:hover { transform: translateY(-4px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .article-image { width: 100%; height: 200px; object-fit: cover; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .article-content { padding: 20px; }
        .article-title { font-size: 1.25rem; margin-bottom: 10px; color: #2c3e50; }
        .article-title a { text-decoration: none; color: inherit; }
        .article-title a:hover { color: #667eea; }
        .article-meta { font-size: 0.875rem; color: #7f8c8d; margin-bottom: 10px; }
        .article-summary { color: #555; line-height: 1.6; }
        .sidebar { background: #fff; border-radius: 8px; padding: 20px; height: fit-content; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .sidebar h3 { margin-bottom: 15px; color: #2c3e50; }
        .category-list { list-style: none; }
        .category-list li { padding: 8px 0; border-bottom: 1px solid #ecf0f1; }
        .category-list li:last-child { border-bottom: none; }
        .category-list a { text-decoration: none; color: #555; display: flex; justify-content: space-between; }
        .category-list a:hover { color: #667eea; }
        .category-list a.active { color: #667eea; font-weight: 600; }
        .pagination { display: flex; justify-content: center; gap: 10px; margin-top: 30px; }
        .pagination a, .pagination span { padding: 8px 16px; background: #fff; border-radius: 4px; text-decoration: none; color: #555; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .pagination a:hover { background: #667eea; color: #fff; }
        .pagination .current { background: #667eea; color: #fff; }
        .no-articles { text-align: center; padding: 60px 20px; background: #fff; border-radius: 8px; }
        @media (max-width: 768px) {
            .content { grid-template-columns: 1fr; }
            .articles-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><?= htmlspecialchars($title) ?></h1>
        </div>
    </header>

    <div class="container">
        <div class="content">
            <main>
                <?php if (empty($articles)): ?>
                    <div class="no-articles">
                        <h2>Chưa có bài viết nào</h2>
                        <p>Hãy quay lại sau nhé!</p>
                    </div>
                <?php else: ?>
                    <div class="articles-grid">
                        <?php foreach ($articles as $article): ?>
                            <article class="article-card">
                                <?php if ($article['featured_image']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/articles/<?= htmlspecialchars($article['featured_image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="article-image">
                                <?php else: ?>
                                    <div class="article-image"></div>
                                <?php endif; ?>
                                
                                <div class="article-content">
                                    <h2 class="article-title">
                                        <a href="<?= BASE_URL ?>/article/<?= htmlspecialchars($article['slug']) ?>">
                                            <?= htmlspecialchars($article['title']) ?>
                                        </a>
                                    </h2>
                                    
                                    <div class="article-meta">
                                        <span><?= htmlspecialchars($article['author_name'] ?? 'Unknown') ?></span>
                                        <?php if ($article['category_name']): ?>
                                            • <span><?= htmlspecialchars($article['category_name']) ?></span>
                                        <?php endif; ?>
                                        • <span><?= date('d/m/Y', strtotime($article['created_at'])) ?></span>
                                    </div>
                                    
                                    <?php if ($article['summary']): ?>
                                        <p class="article-summary"><?= htmlspecialchars(substr($article['summary'], 0, 150)) ?>...</p>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($currentPage > 1): ?>
                                <a href="<?= $baseUrl ?>page=<?= $currentPage - 1 ?>">← Trước</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $currentPage): ?>
                                    <span class="current"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="<?= $baseUrl ?>page=<?= $i ?>"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($currentPage < $totalPages): ?>
                                <a href="<?= $baseUrl ?>page=<?= $currentPage + 1 ?>">Sau →</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </main>

            <aside class="sidebar">
                <h3>Danh mục</h3>
                <ul class="category-list">
                    <li>
                        <a href="<?= BASE_URL ?>/articles" class="<?= !$currentCategory ? 'active' : '' ?>">
                            <span>Tất cả</span>
                        </a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="<?= BASE_URL ?>/articles?category=<?= urlencode($category['slug']) ?>" 
                               class="<?= $currentCategory === $category['slug'] ? 'active' : '' ?>">
                                <span><?= htmlspecialchars($category['name']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        </div>
    </div>
</body>
</html>
