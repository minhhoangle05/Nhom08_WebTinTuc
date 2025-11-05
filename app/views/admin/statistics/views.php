<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?= htmlspecialchars($title) ?></h2>
        <a href="<?= BASE_URL ?>/admin/dashboard" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại Dashboard
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Bộ lọc</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/statistics/views">
                <div class="row g-3">
                    <!-- Khoảng thời gian -->
                    <div class="col-md-2">
                        <label class="form-label">Số ngày</label>
                        <select name="days" class="form-select">
                            <option value="7" <?= $days == 7 ? 'selected' : '' ?>>7 ngày</option>
                            <option value="14" <?= $days == 14 ? 'selected' : '' ?>>14 ngày</option>
                            <option value="30" <?= $days == 30 ? 'selected' : '' ?>>30 ngày</option>
                            <option value="60" <?= $days == 60 ? 'selected' : '' ?>>60 ngày</option>
                            <option value="90" <?= $days == 90 ? 'selected' : '' ?>>90 ngày</option>
                        </select>
                    </div>

                    <!-- Bài viết -->
                    <div class="col-md-3">
                        <label class="form-label">Bài viết</label>
                        <select name="article_id" class="form-select">
                            <option value="">-- Tất cả bài viết --</option>
                            <?php foreach ($articles as $article): ?>
                                <option value="<?= $article['id'] ?>" <?= ($filters['article_id'] ?? '') == $article['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($article['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tác giả -->
                    <div class="col-md-2">
                        <label class="form-label">Tác giả</label>
                        <select name="user_id" class="form-select">
                            <option value="">-- Tất cả --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>" <?= ($filters['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user['name']) ?> (<?= $user['article_count'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Từ ngày -->
                    <div class="col-md-2">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                    </div>

                    <!-- Đến ngày -->
                    <div class="col-md-2">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                    </div>

                    <!-- Sắp xếp -->
                    <div class="col-md-2">
                        <label class="form-label">Sắp xếp theo</label>
                        <select name="sort" class="form-select">
                            <option value="views" <?= ($filters['sort'] ?? 'views') == 'views' ? 'selected' : '' ?>>Lượt xem</option>
                            <option value="recent_views" <?= ($filters['sort'] ?? '') == 'recent_views' ? 'selected' : '' ?>>Xem gần đây</option>
                            <option value="date" <?= ($filters['sort'] ?? '') == 'date' ? 'selected' : '' ?>>Ngày đăng</option>
                            <option value="title" <?= ($filters['sort'] ?? '') == 'title' ? 'selected' : '' ?>>Tiêu đề</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Lọc
                            </button>
                            <a href="<?= BASE_URL ?>/admin/statistics/views" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-bg-primary">
                <div class="card-body">
                    <h6 class="card-title">Tổng lượt xem</h6>
                    <h3 class="mb-0">
                        <?php
                        $totalViews = array_sum(array_column($viewStats, 'total_views'));
                        echo number_format($totalViews);
                        ?>
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-bg-success">
                <div class="card-body">
                    <h6 class="card-title">Lượt xem <?= $days ?> ngày</h6>
                    <h3 class="mb-0">
                        <?php
                        $recentViews = array_sum(array_column($viewStats, 'recent_views'));
                        echo number_format($recentViews);
                        ?>
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-bg-info">
                <div class="card-body">
                    <h6 class="card-title">Số bài viết</h6>
                    <h3 class="mb-0"><?= number_format(count($viewStats)) ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-bg-warning">
                <div class="card-body">
                    <h6 class="card-title">TB lượt xem/bài</h6>
                    <h3 class="mb-0">
                        <?php
                        $avgViews = count($viewStats) > 0 ? $totalViews / count($viewStats) : 0;
                        echo number_format($avgViews, 1);
                        ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng thống kê chi tiết -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-bar-chart-line"></i> Chi tiết lượt xem từng bài viết</h5>
            <button class="btn btn-sm btn-success" onclick="exportToCSV()">
                <i class="bi bi-download"></i> Xuất CSV
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="statsTable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tiêu đề bài viết</th>
                            <th>Tác giả</th>
                            <th>Danh mục</th>
                            <th>Ngày đăng</th>
                            <th class="text-end">Tổng lượt xem</th>
                            <th class="text-end">Xem <?= $days ?> ngày</th>
                            <th class="text-end">Người duy nhất</th>
                            <th class="text-end">Đã đăng nhập</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($viewStats)): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    Không có dữ liệu thống kê
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($viewStats as $stat): ?>
                                <tr>
                                    <td><?= $stat['id'] ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/article/<?= urlencode($stat['slug']) ?>" target="_blank">
                                            <?= htmlspecialchars(mb_substr($stat['title'], 0, 50)) ?>
                                            <?= mb_strlen($stat['title']) > 50 ? '...' : '' ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($stat['author_name']) ?></td>
                                    <td>
                                        <?php if ($stat['category_name']): ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($stat['category_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($stat['created_at'])) ?></td>
                                    <td class="text-end">
                                        <strong><?= number_format($stat['total_views']) ?></strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary"><?= number_format($stat['recent_views']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-info"><?= number_format($stat['unique_visitors']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success"><?= number_format($stat['logged_in_views']) ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/admin/articles/<?= $stat['id'] ?>" 
                                               class="btn btn-outline-primary" 
                                               title="Chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/articles/edit/<?= $article['id'] ?>"
                                               class="btn btn-outline-secondary"
                                               title="Sửa">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge {
    font-weight: 500;
}
</style>

<script>
function exportToCSV() {
    const table = document.getElementById('statsTable');
    let csv = [];
    
    // Headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        headers.push(th.textContent.trim());
    });
    csv.push(headers.join(','));
    
    // Rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach((td, index) => {
            // Skip action column
            if (index < headers.length - 1) {
                let text = td.textContent.trim().replace(/,/g, ';');
                row.push('"' + text + '"');
            }
        });
        if (row.length > 0) {
            csv.push(row.join(','));
        }
    });
    
    // Download
    const blob = new Blob(['\ufeff' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'thong-ke-luot-xem-' + new Date().toISOString().slice(0,10) + '.csv';
    link.click();
}
</script>