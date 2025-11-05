<div class="mb-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Admin</a></li>
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/users">Người dùng</a></li>
      <li class="breadcrumb-item active"><?= htmlspecialchars($user['name']) ?></li>
    </ol>
  </nav>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h2>Chi tiết người dùng</h2>
  <?php if ($user['id'] !== \App\Core\Auth::user()['id']): ?>
  <button type="button" class="btn btn-danger" 
          onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name'], ENT_QUOTES) ?>')">
    <i class="bi bi-trash"></i> Xóa người dùng
  </button>
  <?php endif; ?>
</div>

<div class="row g-4">
  <!-- Thông tin người dùng -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Thông tin cá nhân</h5>
      </div>
      <div class="card-body text-center">
        <div class="mb-3">
          <div class="avatar-lg mx-auto mb-3" style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, var(--bs-primary), var(--bs-info)); display: flex; align-items: center; justify-content: center; color: white; font-size: 2.5rem; font-weight: 600;">
            <?= strtoupper(substr($user['name'], 0, 1)) ?>
          </div>
          <h4><?= htmlspecialchars($user['name']) ?></h4>
          <p class="text-muted mb-2"><?= htmlspecialchars($user['email']) ?></p>
          <?php if ($user['role_id'] === 3): ?>
            <span class="badge bg-danger">Admin</span>
          <?php elseif ($user['role_id'] === 2): ?>
            <span class="badge bg-warning">Editor</span>
          <?php else: ?>
            <span class="badge bg-primary">User</span>
          <?php endif; ?>
        </div>
        
        <hr>
        
        <div class="row g-3 text-start">
          <div class="col-6">
            <small class="text-muted d-block">ID</small>
            <strong><?= $user['id'] ?></strong>
          </div>
          <div class="col-6">
            <small class="text-muted d-block">Vai trò</small>
            <strong><?= htmlspecialchars($user['role_name']) ?></strong>
          </div>
          <div class="col-12">
            <small class="text-muted d-block">Ngày tham gia</small>
            <strong><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></strong>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bài viết và hoạt động -->
  <div class="col-lg-8">
    <!-- Bài viết -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Bài viết gần đây</h5>
      </div>
      <div class="card-body">
        <?php if (empty($articles)): ?>
          <p class="text-muted text-center mb-0">Chưa có bài viết nào</p>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($articles as $article): ?>
            <div class="list-group-item">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <h6 class="mb-1">
                    <a href="<?= BASE_URL ?>/article/<?= urlencode($article['slug']) ?>" 
                       class="text-decoration-none">
                      <?= htmlspecialchars($article['title']) ?>
                    </a>
                  </h6>
                  <small class="text-muted">
                    <i class="bi bi-calendar3"></i> <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                    <i class="bi bi-eye ms-2"></i> <?= number_format($article['views']) ?>
                  </small>
                </div>
                <?php if ($article['category_name']): ?>
                  <span class="badge bg-info"><?= htmlspecialchars($article['category_name']) ?></span>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Hoạt động -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Hoạt động gần đây</h5>
      </div>
      <div class="card-body">
        <?php if (empty($activities)): ?>
          <p class="text-muted text-center mb-0">Chưa có hoạt động nào</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Thời gian</th>
                  <th>Hoạt động</th>
                  <th>Chi tiết</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($activities as $activity): ?>
                <tr>
                  <td>
                    <small><?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?></small>
                  </td>
                  <td>
                    <?php
                      $types = [
                        'login' => 'Đăng nhập',
                        'logout' => 'Đăng xuất',
                        'article_view' => 'Xem bài viết',
                        'article_create' => 'Tạo bài viết',
                        'article_edit' => 'Sửa bài viết',
                        'article_delete' => 'Xóa bài viết',
                        'comment_create' => 'Bình luận',
                        'comment_delete' => 'Xóa bình luận'
                      ];
                      echo $types[$activity['activity_type']] ?? $activity['activity_type'];
                    ?>
                  </td>
                  <td>
                    <?php if ($activity['article_title']): ?>
                      <small class="text-muted"><?= htmlspecialchars($activity['article_title']) ?></small>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
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
          <input type="hidden" name="csrf" value="<?= $csrf ?>">
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