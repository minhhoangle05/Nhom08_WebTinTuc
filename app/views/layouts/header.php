<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Tin tức') ?></title>
    
    <!-- Define BASE_URL for JavaScript -->
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= BASE_URL ?>/js/bookmark.js"></script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/css/new-style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .article-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
        }
        .article-content img {
            max-width: 100%;
            height: auto;
            margin: 1rem 0;
        }
        .article-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .article-meta a {
            color: inherit;
            text-decoration: none;
        }
        .article-meta a:hover {
            color: #0d6efd;
        }
        .article-summary {
            font-size: 1.2rem;
            color: #505050;
            border-left: 4px solid #0d6efd;
            padding-left: 1rem;
            margin: 1rem 0;
            background: #f8f9fa;
            padding: 1rem;
        }
        .related-articles .card {
            transition: transform 0.2s;
        }
        .related-articles .card:hover {
            transform: translateY(-5px);
        }
        
        /* Bookmark badge animation */
        .bookmark-count-badge {
            transition: all 0.3s ease;
        }
        .bookmark-count-badge.updating {
            transform: scale(1.2);
        }
    </style>
</head>
<body>
    <?php require_once 'navbar.php'; ?>