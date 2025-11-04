<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= htmlspecialchars($title) ?></h2>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/statistics/views" class="btn btn-primary">
                <i class="bi bi-graph-up"></i> Thống kê chi tiết
            </a>
            <a href="<?= BASE_URL ?>/admin/users/create" class="btn btn-success">
                <i class="bi bi-person-plus"></i> Thêm người dùng
            </a>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-bg-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Bài viết</h6>
                            <h2 class="mb-0"><?= number_format($stats['articles']) ?></h2>
                        </div>
                        <i class="bi bi-file-earmark-text" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/admin/articles" class="btn btn-sm btn-light">Quản lý</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-bg-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Người dùng</h6>
                            <h2 class="mb-0"><?= number_format($stats['users']) ?></h2>
                        </div>
                        <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/admin/users" class="btn btn-sm btn-light">Quản lý</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-bg-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Bình luận</h6>
                            <h2 class="mb-0"><?= number_format($stats['comments']) ?></h2>
                        </div>
                        <i class="bi bi-chat-dots" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/admin/comments" class="btn btn-sm btn-light">Quản lý</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-bg-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Lượt xem</h6>
                            <h2 class="mb-0"><?= number_format($stats['total_views']) ?></h2>
                        </div>
                        <i class="bi bi-eye" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                    <div class="mt-3">
                        <a href="<?= BASE_URL ?>/admin/statistics/views" class="btn btn-sm btn-light">Chi tiết</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê lượt xem 7 ngày -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-graph-up"></i> Thống kê lượt xem 7 ngày qua</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Ngày</th>
                            <th class="text-end">Tổng lượt xem</th>
                            <th class="text-end">Người dùng duy nhất</th>
                            <th class="text-end">IP duy nhất</th>
                            <th class="text-end">Tỷ lệ đăng nhập</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($viewStats as $stat): ?>
                            <tr>
                                <td><strong><?= date('d/m/Y', strtotime($stat['date'])) ?></strong></td>
                                <td class="text-end">
                                    <span class="badge bg-primary"><?= number_format($stat['view_count']) ?></span>
                                </td>
                                <td class="text-end"><?= number_format($stat['unique_users']) ?></td>
                                <td class="text-end"><?= number_format($stat['unique_ips']) ?></td>
                                <td class="text-end">
                                    <?php 
                                    $rate = $stat['view_count'] > 0 
                                        ? ($stat['unique_users'] / $stat['view_count']) * 100 
                                        : 0;
                                    ?>
                                    <span class="badge <?= $rate > 50 ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= number_format($rate, 1) ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Bài viết mới nhất -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Bài viết mới nhất</h5>
                    <a href="<?= BASE_URL ?>/admin/articles" class="btn btn-sm btn-primary">Xem tất cả</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tiêu đề</th>
                                    <th>Tác giả</th>
                                    <th>Danh mục</th>
                                    <th class="text-end">Lượt xem</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latestArticles as $article): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASE_URL ?>/article/<?= urlencode($article['slug']) ?>" target="_blank">
                                                <?= htmlspecialchars(mb_substr($article['title'], 0, 40)) ?>
                                                <?= mb_strlen($article['title']) > 40 ? '...' : '' ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($article['author_name']) ?></td>
                                        <td>
                                            <?php if ($article['category_name']): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($article['category_name']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end"><?= number_format($article['views']) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= BASE_URL ?>/articles/edit/<?= $article['id'] ?>"
                                                   class="btn btn-outline-secondary" 
                                                   title="Sửa">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="deleteArticle(<?= $article['id'] ?>, '<?= htmlspecialchars($article['title']) ?>')"
                                                        title="Xóa">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Người dùng mới -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Người dùng mới</h5>
                    <a href="<?= BASE_URL ?>/admin/users" class="btn btn-sm btn-success">Xem tất cả</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên</th>
                                    <th>Email</th>
                                    <th>Vai trò</th>
                                    <th class="text-end">Bài viết</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latestUsers as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <span class="badge <?= $user['role_id'] === 1 ? 'bg-danger' : 'bg-primary' ?>">
                                                <?= htmlspecialchars($user['role_name']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end"><?= number_format($user['article_count']) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>" 
                                                   class="btn btn-outline-primary"
                                                   title="Chi tiết">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-danger" 
                                                        onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')"
                                                        title="Xóa"
                                                        <?= $user['role_id'] === 3 ? 'disabled' : '' ?>>
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thống kê theo danh mục -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-folder"></i> Thống kê theo danh mục</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>Danh mục</th>
                                    <th class="text-end">Bài viết</th>
                                    <th class="text-end">Lượt xem</th>
                                    <th class="text-end">TB/bài</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categoryStats as $cat): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                                        <td class="text-end"><?= number_format($cat['article_count']) ?></td>
                                        <td class="text-end"><?= number_format($cat['total_views']) ?></td>
                                        <td class="text-end">
                                            <?php 
                                            $avg = $cat['article_count'] > 0 
                                                ? $cat['total_views'] / $cat['article_count'] 
                                                : 0;
                                            echo number_format($avg, 1);
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bài viết xem nhiều nhất -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-fire"></i> Bài viết phổ biến nhất</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>Tiêu đề</th>
                                    <th>Tác giả</th>
                                    <th class="text-end">Lượt xem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($popularArticles as $article): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASE_URL ?>/article/<?= urlencode($article['slug']) ?>" target="_blank">
                                                <?= htmlspecialchars(mb_substr($article['title'], 0, 50)) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($article['author_name']) ?></td>
                                        <td class="text-end">
                                            <strong class="text-primary"><?= number_format($article['views']) ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form xóa ẩn -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf" value="<?= $csrf ?>">
</form>

<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}
</style>

<script>
function deleteArticle(id, title) {
    if (confirm(`Bạn có chắc chắn muốn xóa bài viết "${title}"?`)) {
        const form = document.getElementById('deleteForm');
        form.action = '<?= BASE_URL ?>/admin/articles/' + id + '/delete';
        form.submit();
    }
}

function deleteUser(id, name) {
    if (confirm(`Bạn có chắc chắn muốn xóa người dùng "${name}"? Hành động này không thể hoàn tác!`)) {
        const form = document.getElementById('deleteForm');
        form.action = '<?= BASE_URL ?>/admin/users/' + id + '/delete';
        form.submit();
    }
}
</script>