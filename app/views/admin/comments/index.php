<div class="d-flex justify-content-between align-items-center mb-4">
  <h2><?= htmlspecialchars($title) ?></h2>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <?php if ($_GET['success'] === 'deleted'): ?>
    Đã xóa bình luận thành công!
  <?php endif; ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <span>Tổng: <strong><?= number_format($total) ?></strong> bình luận</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 60px;">ID</th>
            <th>Nội dung</th>
            <th style="width: 150px;">Người dùng</th>
            <th style="width: 200px;">Bài viết</th>
            <th style="width: 140px;">Ngày tạo</th>
            <th style="width: 100px;" class="text-end">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($comments)): ?>
          <tr>
            <td colspan="6" class="text-center py-5">
              <i class="bi bi-chat-dots display-1 text-muted"></i>
              <p class="text-muted mt-3">Chưa có bình luận nào</p>
            </td>
          </tr>
          <?php else: ?>
            <?php foreach ($comments as $comment): ?>
            <tr>
              <td><?= $comment['id'] ?></td>
              <td>
                <div class="text-truncate" style="max-width: 300px;" title="<?= htmlspecialchars($comment['content']) ?>">
                  <?= htmlspecialchars($comment['content']) ?>
                </div>
              </td>
              <td><?= htmlspecialchars($comment['user_name']) ?></td>
              <td>
                <a href="<?= BASE_URL ?>/article/<?= urlencode($comment['article_slug']) ?>" 
                   class="text-decoration-none" target="_blank">
                  <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($comment['article_title']) ?>">
                    <?= htmlspecialchars($comment['article_title']) ?>
                  </div>
                </a>
              </td>
              <td>
                <small class="text-muted">
                  <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                </small>
              </td>
              <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger" 
                        onclick="deleteComment(<?= $comment['id'] ?>)" 
                        title="Xóa">
                  <i class="bi bi-trash"></i>
                </button>
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
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
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
        <p>Bạn có chắc chắn muốn xóa bình luận này?</p>
        <p class="text-danger mb-0">
          <i class="bi bi-exclamation-triangle"></i> 
          Hành động này không thể hoàn tác!
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <form id="deleteForm" method="POST" class="d-inline">
          <input type="hidden" name="csrf" value="<?= $csrf ?>">
          <button type="submit" class="btn btn-danger">Xóa</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function deleteComment(id) {
  document.getElementById('deleteForm').action = '<?= BASE_URL ?>/admin/comments/' + id + '/delete';
  new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>