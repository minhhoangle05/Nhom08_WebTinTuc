<section class="hero-section text-center rounded-3 fade-in" style="--animation-delay: .1s;">
  <div class="container py-5">
    <?php if (isset($user)): ?>
    <div class="user-welcome mb-4">
      <h2 class="h4 mb-3">Xin chào, <?= htmlspecialchars($user['name']) ?>!</h2>
      <p class="text-light mb-3">Email: <?= htmlspecialchars($user['email']) ?></p>
      <?php if ($user['role_id'] === 3): ?>
      <span class="badge bg-warning mb-3">Quản trị viên</span>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <h1 class="display-4 fw-bold mb-3">Khám phá những bài viết chất lượng</h1>
    <p class="lead mb-4">Công nghệ • Kinh doanh • Lối sống và nhiều hơn nữa</p>
    <a class="btn btn-light btn-lg me-2" href="<?= BASE_URL ?>/articles"><i class="bi bi-journal-text me-1"></i> Xem bài viết</a>
    <a class="btn btn-outline-light btn-lg" href="<?= BASE_URL ?>/articles/create"><i class="bi bi-pencil-square me-1"></i> Viết bài</a>
    <div class="mt-4"><span class="badge bg-dark bg-opacity-50">Bây giờ: <span id="currentTime"></span></span></div>
  </div>
</section>

<?php
use App\Models\Article;
$articleModel = new Article();
$featured = $articleModel->featuredToday(6);
?>

<?php if (!empty($featured)): ?>
<section class="container my-5">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Nổi bật hôm nay</h3>
    <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/articles">Xem tất cả</a>
  </div>
  <div class="row g-3">
    <?php foreach ($featured as $item): ?>
    <div class="col-md-4">
      <div class="card article-card h-100 fade-in">
        <div class="card-body">
          <span class="category-badge mb-2 d-inline-block"><?= htmlspecialchars($item['category_name'] ?? 'Bài viết') ?></span>
          <h5 class="card-title"><a href="<?= BASE_URL ?>/article/<?= urlencode($item['slug']) ?>"><?= htmlspecialchars($item['title']) ?></a></h5>
          <p class="card-text text-muted"><?= htmlspecialchars($item['author_name'] ?? 'Tác giả') ?> • <?= htmlspecialchars(date('H:i', strtotime($item['created_at'] ?? 'now'))) ?></p>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

