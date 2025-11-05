<div class="d-flex justify-content-between align-items-center mb-4">
  <h2><?= htmlspecialchars($title) ?></h2>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
    <i class="bi bi-plus-circle"></i> Tạo thẻ mới
  </button>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <?php if ($_GET['success'] === 'created'): ?>
    Đã tạo thẻ thành công!
  <?php elseif ($_GET['success'] === 'deleted'): ?>
    Đã xóa thẻ thành công!
  <?php endif; ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 60px;">ID</th>
            <th>Tên thẻ</th>
            <th style="width: 200px;">Slug</th>
            <th style="width: 120px;" class="text-center">Số bài viết</th>
            <th style="width: 100px;" class="text-end">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($tags)): ?>
          <tr>
            <td colspan="5" class="text-center py-5">
              <i class="bi bi-tags display-1 text-muted"></i>
              <p class="text-muted mt-3">Chưa có thẻ nào</p>
            </td>
          </tr>
          <?php else: ?>
            <?php foreach ($tags as $tag): ?>
            <tr>
              <td><?= $tag['id'] ?></td>
              <td>
                <strong><?= htmlspecialchars($tag['name']) ?></strong>
              </td>
              <td>
                <code><?= htmlspecialchars($tag['slug']) ?></code>
              </td>
              <td class="text-center">
                <span class="badge bg-info"><?= number_format($tag['article_count']) ?></span>
              </td>
              <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger" 
                        onclick="deleteTag(<?= $tag['id'] ?>, '<?= htmlspecialchars($tag['name'], ENT_QUOTES) ?>', <?= $tag['article_count'] ?>)" 
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
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= BASE_URL ?>/admin/tags/create">
        <div class="modal-header">
          <h5 class="modal-title">Tạo thẻ mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf" value="<?= $csrf ?>">
          <div class="mb-3">
            <label class="form-label">Tên thẻ <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required id="createName">
          </div>
          <div class="mb-3">
            <label class="form-label">Slug <span class="text-danger">*</span></label>
            <input type="text" name="slug" class="form-control" required id="createSlug">
            <small class="text-muted">VD: javascript, php, web-development</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary">Tạo</button>
        </div>
      </form>
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
        <p>Bạn có chắc chắn muốn xóa thẻ <strong id="deleteTitle"></strong>?</p>
        <p id="deleteWarning" class="text-warning mb-0" style="display: none;">
          <i class="bi bi-exclamation-triangle"></i> 
          Thẻ này có <strong id="articleCount"></strong> bài viết. Thẻ sẽ bị xóa khỏi các bài viết này.
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
// Auto-generate slug from name
document.getElementById('createName').addEventListener('input', function(e) {
  const slug = e.target.value
    .toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/đ/g, 'd')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');
  document.getElementById('createSlug').value = slug;
});

function deleteTag(id, name, articleCount) {
  document.getElementById('deleteTitle').textContent = name;
  document.getElementById('deleteForm').action = '<?= BASE_URL ?>/admin/tags/' + id + '/delete';
  
  const warning = document.getElementById('deleteWarning');
  if (articleCount > 0) {
    document.getElementById('articleCount').textContent = articleCount;
    warning.style.display = 'block';
  } else {
    warning.style.display = 'none';
  }
  
  new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>