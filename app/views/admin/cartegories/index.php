<div class="d-flex justify-content-between align-items-center mb-4">
  <h2><?= htmlspecialchars($title) ?></h2>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
    <i class="bi bi-plus-circle"></i> Tạo danh mục mới
  </button>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <?php if ($_GET['success'] === 'created'): ?>
    Đã tạo danh mục thành công!
  <?php elseif ($_GET['success'] === 'updated'): ?>
    Đã cập nhật danh mục thành công!
  <?php elseif ($_GET['success'] === 'deleted'): ?>
    Đã xóa danh mục thành công!
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
            <th>Tên danh mục</th>
            <th style="width: 200px;">Slug</th>
            <th style="width: 120px;" class="text-center">Số bài viết</th>
            <th style="width: 150px;" class="text-end">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $category): ?>
          <tr>
            <td><?= $category['id'] ?></td>
            <td>
              <strong><?= htmlspecialchars($category['name']) ?></strong>
            </td>
            <td>
              <code><?= htmlspecialchars($category['slug']) ?></code>
            </td>
            <td class="text-center">
              <span class="badge bg-info"><?= number_format($category['article_count']) ?></span>
            </td>
            <td class="text-end">
              <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-warning" 
                        onclick="editCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($category['slug'], ENT_QUOTES) ?>')" 
                        title="Sửa">
                  <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-outline-danger" 
                        onclick="deleteCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name'], ENT_QUOTES) ?>', <?= $category['article_count'] ?>)" 
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

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="<?= BASE_URL ?>/admin/categories/create">
        <div class="modal-header">
          <h5 class="modal-title">Tạo danh mục mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf" value="<?= $csrf ?>">
          <div class="mb-3">
            <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required id="createName">
          </div>
          <div class="mb-3">
            <label class="form-label">Slug <span class="text-danger">*</span></label>
            <input type="text" name="slug" class="form-control" required id="createSlug">
            <small class="text-muted">VD: cong-nghe, kinh-doanh</small>
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="editForm">
        <div class="modal-header">
          <h5 class="modal-title">Chỉnh sửa danh mục</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf" value="<?= $csrf ?>">
          <div class="mb-3">
            <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required id="editName">
          </div>
          <div class="mb-3">
            <label class="form-label">Slug <span class="text-danger">*</span></label>
            <input type="text" name="slug" class="form-control" required id="editSlug">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-warning">Cập nhật</button>
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
        <p>Bạn có chắc chắn muốn xóa danh mục <strong id="deleteTitle"></strong>?</p>
        <p id="deleteWarning" class="text-danger mb-0" style="display: none;">
          <i class="bi bi-exclamation-triangle"></i> 
          Danh mục này có <strong id="articleCount"></strong> bài viết!
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

function editCategory(id, name, slug) {
  document.getElementById('editName').value = name;
  document.getElementById('editSlug').value = slug;
  document.getElementById('editForm').action = '<?= BASE_URL ?>/admin/categories/' + id + '/update';
  new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deleteCategory(id, name, articleCount) {
  document.getElementById('deleteTitle').textContent = name;
  document.getElementById('deleteForm').action = '<?= BASE_URL ?>/admin/categories/' + id + '/delete';
  
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