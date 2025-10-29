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
                <?php if ($article['status'] === 'published'): ?>
                  <span class="badge bg-success">Đã xuất bản</span>
                <?php elseif ($article['status'] === 'draft'): ?>
                  <span class="badge bg-secondary">Bản nháp</span>
                <?php else: ?>
                  <span class="badge bg-warning"><?= htmlspecialchars($article['status']) ?></span>
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

    <!-- Nội dung -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Nội dung</h5>
      </div>
      <div class="card-body">
        <div class="content-preview">
          <?= nl2br(htmlspecialchars($article['content'])) ?>
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
        <?php else: ?>
          <p class="text-muted text-center mb-0">Chưa có dữ liệu</p>
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
  max-height: 500px;
  overflow-y: auto;
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>