<div class="mb-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Admin</a></li>
      <li class="breadcrumb-item active">Bài viết</li>
    </ol>
  </nav>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h2><?= htmlspecialchars($title) ?></h2>
  <a href="<?= BASE_URL ?>/articles/create" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Tạo bài viết mới
  </a>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <?php if ($_GET['success'] === 'deleted'): ?>
    Đã xóa bài viết thành công!
  <?php endif; ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <span>Tổng: <strong><?= number_format($total) ?></strong> bài viết</span>
      <div class="btn-group btn-group-sm" role="group">
        <input type="text" class="form-control form-control-sm" placeholder="Tìm kiếm..." id="searchInput">
      </div>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 60px;">ID</th>
            <th>Tiêu đề</th>
            <th style="width: 150px;">Tác giả</th>
            <th style="width: 150px;">Danh mục</th>
            <th style="width: 100px;" class="text-center">Lượt xem</th>
            <th style="width: 100px;" class="text-center">Bình luận</th>
            <th style="width: 140px;">Ngày tạo</th>
            <th style="width: 150px;" class="text-end">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($articles)): ?>
          <tr>
            <td colspan="8" class="text-center py-5">
              <i class="bi bi-inbox display-1 text-muted"></i>
              <p class="text-muted mt-3">Chưa có bài viết nào</p>
            </td>
          </tr>
          <?php else: ?>
            <?php foreach ($articles as $article): ?>
            <tr>
              <td><?= $article['id'] ?></td>
              <td>
                <a href="<?= BASE_URL ?>/admin/articles/<?= $article['id'] ?>" 
                   class="text-decoration-none fw-medium">
                  <?= htmlspecialchars($article['title']) ?>
                </a>
                <br>
                <small class="text-muted">
                  <code><?= htmlspecialchars($article['slug']) ?></code>
                </small>
              </td>
              <td><?= htmlspecialchars($article['author_name']) ?></td>
              <td>
                <?php if ($article['category_name']): ?>
                  <span class="badge bg-info"><?= htmlspecialchars($article['category_name']) ?></span>
                <?php else: ?>
                  <span class="text-muted">Chưa phân loại</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <span class="badge bg-primary"><?= number_format($article['views']) ?></span>
              </td>
              <td class="text-center">
                <span class="badge bg-success"><?= number_format($article['comment_count'] ?? 0) ?></span>
              </td>
              <td>
                <small class="text-muted">
                  <?= date('d/m/Y H:i', strtotime($article['created_at'])) ?>
                </small>
              </td>
              <td class="text-end">
                <div class="btn-group btn-group-sm">
                  <a href="<?= BASE_URL ?>/article/<?= urlencode($article['slug']) ?>" 
                     class="btn btn-outline-info" title="Xem" target="_blank">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="<?= BASE_URL ?>/admin/articles/<?= $article['id'] ?>" 
                     class="btn btn-outline-primary" title="Chi tiết">
                    <i class="bi bi-info-circle"></i>
                  </a>
                  <a href="<?= BASE_URL ?>/articles/edit/<?= $article['id'] ?>" 
                     class="btn btn-outline-warning" title="Sửa">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <button type="button" class="btn btn-outline-danger" 
                          onclick="deleteArticle(<?= $article['id'] ?>, '<?= htmlspecialchars($article['title'], ENT_QUOTES) ?>')" 
                          title="Xóa">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  
  <?php if ($totalPages > 1): ?>
  <div class="card-footer">
    <nav>
      <ul class="pagination pagination-sm mb-0 justify-content-center">
        <?php if ($currentPage > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?= $currentPage - 1 ?>">
            <i class="bi bi-chevron-left"></i>
          </a>
        </li>
        <?php endif; ?>
        
        <?php
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);
        
        if ($start > 1): ?>
          <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
          <?php if ($start > 2): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
          <?php endif; ?>
        <?php endif; ?>
        
        <?php for ($i = $start; $i <= $end; $i++): ?>
        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        
        <?php if ($end < $totalPages): ?>
          <?php if ($end < $totalPages - 1): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
          <?php endif; ?>
          <li class="page-item"><a class="page-link" href="?page=<?= $totalPages ?>"><?= $totalPages ?></a></li>
        <?php endif; ?>
        
        <?php if ($currentPage < $totalPages): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?= $currentPage + 1 ?>">
            <i class="bi bi-chevron-right"></i>
          </a>
        </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
  <?php endif; ?>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Xác nhận xóa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Bạn có chắc chắn muốn xóa bài viết <strong id="deleteTitle"></strong>?</p>
        <p class="text-danger mb-0">
          <i class="bi bi-exclamation-triangle"></i> 
          Hành động này không thể hoàn tác!
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <form id="deleteForm" method="POST" class="d-inline">
          <input type="hidden" name="csrf" value="<?= \App\Core\CSRF::token() ?>">
          <button type="submit" class="btn btn-danger">Xóa</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function deleteArticle(id, title) {
  document.getElementById('deleteTitle').textContent = title;
  document.getElementById('deleteForm').action = '<?= BASE_URL ?>/admin/articles/' + id + '/delete';
  new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Simple search
document.getElementById('searchInput')?.addEventListener('input', function(e) {
  const searchTerm = e.target.value.toLowerCase();
  const rows = document.querySelectorAll('tbody tr');
  
  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(searchTerm) ? '' : 'none';
  });
});
</script>