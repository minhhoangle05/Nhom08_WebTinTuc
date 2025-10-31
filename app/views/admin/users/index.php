<div class="d-flex justify-content-between align-items-center mb-4">
  <h2><?= htmlspecialchars($title) ?></h2>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <?php if ($_GET['success'] === 'deleted'): ?>
    Đã xóa người dùng thành công!
  <?php endif; ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <span>Tổng: <strong><?= number_format($total) ?></strong> người dùng</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 60px;">ID</th>
            <th>Tên</th>
            <th>Email</th>
            <th style="width: 120px;">Vai trò</th>
            <th style="width: 100px;" class="text-center">Bài viết</th>
            <th style="width: 100px;" class="text-center">Bình luận</th>
            <th style="width: 140px;">Ngày tham gia</th>
            <th style="width: 150px;" class="text-end">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user): ?>
          <tr>
            <td><?= $user['id'] ?></td>
            <td>
              <a href="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>" class="text-decoration-none fw-medium">
                <?= htmlspecialchars($user['name']) ?>
              </a>
            </td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
              <?php if ($user['role_id'] === 3): ?>
                <span class="badge bg-danger">Admin</span>
              <?php elseif ($user['role_id'] === 2): ?>
                <span class="badge bg-warning">Editor</span>
              <?php else: ?>
                <span class="badge bg-primary">User</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <span class="badge bg-info"><?= number_format($user['article_count']) ?></span>
            </td>
            <td class="text-center">
              <span class="badge bg-success"><?= number_format($user['comment_count']) ?></span>
            </td>
            <td>
              <small class="text-muted">
                <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
              </small>
            </td>
            <td class="text-end">
              <div class="btn-group btn-group-sm">
                <a href="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>" 
                   class="btn btn-outline-info" title="Chi tiết">
                  <i class="bi bi-eye"></i>
                </a>
                <?php if ($user['id'] !== \App\Core\Auth::user()['id']): ?>
                <button type="button" class="btn btn-outline-danger" 
                        onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name'], ENT_QUOTES) ?>')" 
                        title="Xóa">
                  <i class="bi bi-trash"></i>
                </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
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
        <p>Bạn có chắc chắn muốn xóa người dùng <strong id="deleteTitle"></strong>?</p>
        <p class="text-danger mb-0">
          <i class="bi bi-exclamation-triangle"></i> 
          Tất cả bài viết và bình luận của người dùng này sẽ bị xóa!
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
function deleteUser(id, name) {
  document.getElementById('deleteTitle').textContent = name;
  document.getElementById('deleteForm').action = '<?= BASE_URL ?>/admin/users/' + id + '/delete';
  new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>