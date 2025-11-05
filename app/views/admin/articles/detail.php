<div class="mb-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Admin</a></li>
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/articles">Bài viết</a></li>
      <li class="breadcrumb-item active"><?= htmlspecialchars($article['title']) ?></li>
    </ol>
  </nav>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h2>Chi tiết bài viết</h2>
  <div class="btn-group">
    <a href="<?= BASE_URL ?>/article/<?= urlencode($article['slug']) ?>" 
       class="btn btn-outline-primary" target="_blank">
      <i class="bi bi-eye"></i> Xem bài viết
    </a>
    <a href="<?= BASE_URL ?>/articles/edit/<?= $article['id'] ?>" 
       class="btn btn-warning">
      <i class="bi bi-pencil"></i> Chỉnh sửa
    </a>
    <button type="button" class="btn btn-danger" 
            onclick="deleteArticle(<?= $article['id'] ?>, '<?= htmlspecialchars($article['title'], ENT_QUOTES) ?>')">
      <i class="bi bi-trash"></i> Xóa
    </button>
  </div>
</div>

<div class="row g-4">
  <!-- Thông tin chính -->
  <div class="col-lg-8">
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Thông tin bài viết</h5>
      </div>
      <div class="card-body">
        <table class="table table-borderless mb-0">
          <tbody>
            <tr>
              <th style="width: 150px;">ID:</th>
              <td><?= $article['id'] ?></td>
            </tr>
            <tr>
              <th>Tiêu đề:</th>
              <td><strong><?= htmlspecialchars($article['title']) ?></strong></td>
            </tr>
            <tr>
              <th>Slug:</th>
              <td><code><?= htmlspecialchars($article['slug']) ?></code></td>
            </tr>
            <tr>
              <th>Tác giả:</th>
              <td>
                <a href="<?= BASE_URL ?>/admin/users/<?= $article['user_id'] ?>">
                  <?= htmlspecialchars($article['author_name']) ?>
                </a>
              </td>
            </tr>
            <tr>
              <th>Danh mục:</th>
              <td>
                <?php if ($article['category_name']): ?>
                  <span class="badge bg-info"><?= htmlspecialchars($article['category_name']) ?></span>
                <?php else: ?>
                  <span class="text-muted">Chưa phân loại</span>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <th>Trạng thái:</th>
              <td>
                <?php if (isset($article['status'])): ?>
                  <?php if ($article['status'] === 'published'): ?>
                    <span class="badge bg-success">Đã xuất bản</span>
                  <?php elseif ($article['status'] === 'draft'): ?>
                    <span class="badge bg-secondary">Bản nháp</span>
                  <?php else: ?>
                    <span class="badge bg-warning"><?= htmlspecialchars($article['status']) ?></span>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="badge bg-success">Đã xuất bản</span>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <th>Thẻ:</th>
              <td>
                <?php if (!empty($tags)): ?>
                  <?php foreach ($tags as $tag): ?>
                    <span class="badge bg-secondary me-1"><?= htmlspecialchars($tag['name']) ?></span>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="text-muted">Không có thẻ</span>
                <?php endif; ?>
              </td>
            </tr>
            <tr>
              <th>Ngày tạo:</th>
              <td><?= date('d/m/Y H:i:s', strtotime($article['created_at'])) ?></td>
            </tr>
            <tr>
              <th>Cập nhật:</th>
              <td><?= date('d/m/Y H:i:s', strtotime($article['updated_at'])) ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Ảnh đại diện -->
    <?php if (!empty($article['featured_image'])): ?>
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Ảnh đại diện</h5>
      </div>
      <div class="card-body">
        <img src="<?= BASE_URL ?>/uploads/articles/<?= htmlspecialchars($article['featured_image']) ?>" 
             alt="<?= htmlspecialchars($article['title']) ?>"
             class="img-fluid rounded"
             style="max-height: 400px; object-fit: cover;">
      </div>
    </div>
    <?php endif; ?>

    <!-- Nội dung -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Nội dung</h5>
      </div>
      <div class="card-body">
        <div class="content-preview">
          <?= $article['content'] ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Thống kê -->
  <div class="col-lg-4">
    <!-- Số liệu tổng quan -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Thống kê</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-6">
            <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
              <div class="h3 mb-0"><?= number_format($article['views']) ?></div>
              <small class="text-muted">Lượt xem</small>
            </div>
          </div>
          <div class="col-6">
            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
              <div class="h3 mb-0"><?= number_format($article['comment_count'] ?? 0) ?></div>
              <small class="text-muted">Bình luận</small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tóm tắt -->
    <?php if (!empty($article['summary'])): ?>
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">Tóm tắt</h5>
      </div>
      <div class="card-body">
        <p class="mb-0"><?= htmlspecialchars($article['summary']) ?></p>
      </div>
    </div>
    <?php endif; ?>

    <!-- Lịch sử lượt xem -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Lượt xem 30 ngày qua</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($viewHistory)): ?>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Ngày</th>
                  <th class="text-end">Lượt xem</th>
                  <th class="text-end">IP duy nhất</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (array_slice($viewHistory, 0, 10) as $stat): ?>
                <tr>
                  <td><?= date('d/m', strtotime($stat['date'])) ?></td>
                  <td class="text-end"><?= number_format($stat['views']) ?></td>
                  <td class="text-end"><?= number_format($stat['unique_views']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php if (count($viewHistory) > 10): ?>
          <div class="text-center mt-2">
            <small class="text-muted">Hiển thị 10/<?= count($viewHistory) ?> ngày</small>
          </div>
          <?php endif; ?>
        <?php else: ?>
          <p class="text-muted text-center mb-0">Chưa có dữ liệu lượt xem</p>
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
        <p>Bạn có chắc chắn muốn xóa bài viết <strong id="deleteTitle"></strong>?</p>
        <p class="text-danger mb-0"><i class="bi bi-exclamation-triangle"></i> Hành động này không thể hoàn tác!</p>
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
function deleteArticle(id, title) {
  document.getElementById('deleteTitle').textContent = title;
  document.getElementById('deleteForm').action = '<?= BASE_URL ?>/admin/articles/' + id + '/delete';
  new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<style>
.content-preview {
  max-height: 600px;
  overflow-y: auto;
  line-height: 1.8;
  font-size: 1rem;
}

.content-preview img {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  margin: 1rem 0;
}

.content-preview p {
  margin-bottom: 1rem;
}

.content-preview h1,
.content-preview h2,
.content-preview h3,
.content-preview h4,
.content-preview h5,
.content-preview h6 {
  margin-top: 1.5rem;
  margin-bottom: 0.75rem;
  font-weight: 600;
}

.content-preview ul,
.content-preview ol {
  margin-bottom: 1rem;
  padding-left: 2rem;
}

.content-preview blockquote {
  border-left: 4px solid var(--bs-primary);
  padding-left: 1rem;
  margin: 1rem 0;
  color: var(--bs-secondary);
  font-style: italic;
}

.content-preview code {
  background: var(--bs-gray-200);
  padding: 0.2rem 0.4rem;
  border-radius: 4px;
  font-size: 0.9em;
}

.content-preview pre {
  background: var(--bs-gray-100);
  padding: 1rem;
  border-radius: 8px;
  overflow-x: auto;
  margin: 1rem 0;
}

.content-preview pre code {
  background: none;
  padding: 0;
}

[data-bs-theme="dark"] .content-preview code {
  background: var(--bs-gray-800);
}

[data-bs-theme="dark"] .content-preview pre {
  background: var(--bs-gray-900);
}
</style>