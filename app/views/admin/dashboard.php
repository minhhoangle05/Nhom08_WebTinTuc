<h2><?= htmlspecialchars($title) ?></h2>

<!-- Thống kê tổng quan -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card text-bg-primary h-100">
      <div class="card-body">
        <h5 class="card-title">Bài viết</h5>
        <div class="display-6"><?= number_format($stats['articles']) ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-bg-success h-100">
      <div class="card-body">
        <h5 class="card-title">Người dùng</h5>
        <div class="display-6"><?= number_format($stats['users']) ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-bg-warning h-100">
      <div class="card-body">
        <h5 class="card-title">Bình luận</h5>
        <div class="display-6"><?= number_format($stats['comments']) ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card text-bg-info h-100">
      <div class="card-body">
        <h5 class="card-title">Lượt xem</h5>
        <div class="display-6"><?= number_format($stats['total_views']) ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Bài viết mới nhất -->
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Bài viết mới nhất</h5>
        <a href="<?= BASE_URL ?>/admin/articles" class="btn btn-sm btn-primary">Xem tất cả</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Tiêu đề</th>
                <th>Tác giả</th>
                <th>Danh mục</th>
                <th>Ngày tạo</th>
                <th>Lượt xem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($latestArticles as $article): ?>
              <tr>
                <td><a href="<?= BASE_URL ?>/article/<?= urlencode($article['slug']) ?>"><?= htmlspecialchars($article['title']) ?></a></td>
                <td><?= htmlspecialchars($article['author_name']) ?></td>
                <td><?= htmlspecialchars($article['category_name'] ?? 'Chưa phân loại') ?></td>
                <td><?= date('d/m/Y H:i', strtotime($article['created_at'])) ?></td>
                <td><?= number_format($article['views']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Người dùng mới -->
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Người dùng mới</h5>
        <a href="<?= BASE_URL ?>/admin/users" class="btn btn-sm btn-primary">Xem tất cả</a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Tên</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Số bài viết</th>
                <th>Ngày tham gia</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($latestUsers as $user): ?>
              <tr>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><span class="badge <?= $user['role_id'] === 1 ? 'bg-danger' : 'bg-primary' ?>"><?= htmlspecialchars($user['role_name']) ?></span></td>
                <td><?= number_format($user['article_count']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Thống kê theo danh mục -->
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Thống kê theo danh mục</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Danh mục</th>
                <th>Số bài viết</th>
                <th>Tổng lượt xem</th>
                <th>Trung bình</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categoryStats as $cat): ?>
              <tr>
                <td><?= htmlspecialchars($cat['name']) ?></td>
                <td><?= number_format($cat['article_count']) ?></td>
                <td><?= number_format($cat['total_views']) ?></td>
                <td><?= $cat['article_count'] > 0 ? number_format($cat['total_views'] / $cat['article_count'], 1) : 0 ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Bài viết xem nhiều nhất -->
  <div class="col-md-6">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Bài viết xem nhiều nhất</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Tiêu đề</th>
                <th>Tác giả</th>
                <th>Danh mục</th>
                <th>Lượt xem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($popularArticles as $article): ?>
              <tr>
                <td><a href="<?= BASE_URL ?>/article/<?= urlencode($article['slug']) ?>"><?= htmlspecialchars($article['title']) ?></a></td>
                <td><?= htmlspecialchars($article['author_name']) ?></td>
                <td><?= htmlspecialchars($article['category_name'] ?? 'Chưa phân loại') ?></td>
                <td><?= number_format($article['views']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Thêm đoạn mã JavaScript để tự động làm mới dữ liệu mỗi 5 phút
setTimeout(function() {
    location.reload();
}, 300000);
</script>

<!-- Thống kê lượt xem -->
<div class="row mt-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Thống kê lượt xem 7 ngày qua</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Ngày</th>
                <th>Tổng lượt xem</th>
                <th>Người dùng đã đăng nhập</th>
                <th>Số IP duy nhất</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($viewStats as $stat): ?>
              <tr>
                <td><?= date('d/m/Y', strtotime($stat['date'])) ?></td>
                <td><?= number_format($stat['view_count']) ?></td>
                <td><?= number_format($stat['unique_users']) ?></td>
                <td><?= number_format($stat['unique_ips']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Hoạt động gần đây -->
<div class="row mt-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Hoạt động người dùng gần đây</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Thời gian</th>
                <th>Người dùng</th>
                <th>Hoạt động</th>
                <th>Chi tiết</th>
                <th>IP</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentActivities as $activity): ?>
              <tr>
                <td><?= date('d/m/Y H:i:s', strtotime($activity['created_at'])) ?></td>
                <td><?= htmlspecialchars($activity['user_name']) ?></td>
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
                    <?= htmlspecialchars($activity['article_title']) ?>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($activity['ip_address']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>


