<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Bài viết của tôi</h1>
    <a class="btn btn-success" href="<?= BASE_URL ?>/articles/create"><i class="bi bi-plus-lg me-1"></i>Tạo bài viết</a>
  </div>

  <?php if (empty($articles)): ?>
    <div class="alert alert-info">Bạn chưa có bài viết nào.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Tiêu đề</th>
            <th>Trạng thái</th>
            <th>Lượt xem</th>
            <th>Cập nhật</th>
            <th class="text-end">Hành động</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($articles as $a): ?>
          <tr>
            <td>
              <div class="fw-semibold"><a href="<?= BASE_URL ?>/article/<?= rawurlencode($a['slug']) ?>" class="text-decoration-none"><?= htmlspecialchars($a['title']) ?></a></div>
              <div class="text-muted small">Danh mục: <?= htmlspecialchars($a['category_name'] ?? '—') ?></div>
            </td>
            <td>
              <?php if (($a['status'] ?? '') === 'draft'): ?>
                <span class="badge text-bg-secondary">Nháp</span>
              <?php elseif (($a['status'] ?? '') === 'private'): ?>
                <span class="badge text-bg-warning">Riêng tư</span>
              <?php else: ?>
                <span class="badge text-bg-success">Xuất bản</span>
              <?php endif; ?>
            </td>
            <td><?= (int)($a['views'] ?? 0) ?></td>
            <td><span class="text-muted small"><?= htmlspecialchars($a['updated_at'] ?? $a['created_at'] ?? '') ?></span></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/article/<?= (int)$a['id'] ?>/edit"><i class="bi bi-pencil-square me-1"></i>Sửa</a>
              <form method="post" action="<?= BASE_URL ?>/article/<?= (int)$a['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Bạn chắc chắn muốn xóa bài viết này?');">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Xóa</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if (($totalPages ?? 1) > 1): ?>
      <nav>
        <ul class="pagination">
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= ($p === ($currentPage ?? 1)) ? 'active' : '' ?>">
              <a class="page-link" href="<?= BASE_URL ?>/articles/mine?page=<?= $p ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>


