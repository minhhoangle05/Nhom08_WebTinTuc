<?php
// Khởi tạo biến content nếu chưa được set
if (!isset($content)) {
    $content = '';
}
?>
<!DOCTYPE html>
<html lang="vi" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($title) ? htmlspecialchars($title) : 'ArticleHub - Your Source for Quality Content' ?></title>
    <meta name="description" content="Discover amazing articles on technology, lifestyle, business and more. Join our community of writers and readers.">
    <meta name="keywords" content="articles, blog, technology, lifestyle, business, writing">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    
    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary border-bottom">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/">ArticleHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/articles">Bài viết</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Danh mục</a>
          <?php 
            $activeCat = $_GET['category'] ?? ''; 
            try { 
              $cats = (new \App\Models\Category())->all(); 
            } catch (\Throwable $e) { 
              $cats = []; 
            }
          ?>
          <ul class="dropdown-menu">
            <?php foreach ($cats as $cat): $name = $cat['name']; ?>
              <li><a class="dropdown-item<?= $activeCat===$name?' active':'' ?>" href="<?= BASE_URL ?>/articles/search?category=<?= urlencode($name) ?>"><?= htmlspecialchars($name) ?></a></li>
            <?php endforeach; ?>
            <?php if (empty($cats)): ?>
              <li><span class="dropdown-item text-muted">Chưa có danh mục</span></li>
            <?php endif; ?>
          </ul>
        </li>
      </ul>
      <form class="d-flex align-items-center me-3" role="search" action="<?= BASE_URL ?>/articles/search" method="get" style="gap:8px">
        <div class="input-group rounded-pill overflow-hidden" style="border:1px solid var(--bs-border-color);">
          <span class="input-group-text bg-transparent border-0"><i class="bi bi-search"></i></span>
          <input class="form-control border-0" type="search" name="q" placeholder="Tìm bài viết..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" aria-label="Search">
        </div>
        <select class="form-select" name="sort" style="max-width:140px; border-radius:999px">
          <option value="">Mới nhất</option>
          <option value="oldest" <?= (($_GET['sort'] ?? '')==='oldest')?'selected':'' ?>>Cũ nhất</option>
          <option value="title" <?= (($_GET['sort'] ?? '')==='title')?'selected':'' ?>>Theo tiêu đề</option>
        </select>
        <button class="btn btn-primary" type="submit" style="border-radius:999px">Tìm</button>
      </form>
      <div class="d-flex align-items-center gap-2">
        <button class="theme-toggle d-flex align-items-center gap-2" id="themeToggle" aria-label="Toggle theme"><i class="bi bi-moon"></i></button>
        <?php if (\App\Core\Auth::check()): ?>
          <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars(\App\Core\Auth::user()['name']) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile"><i class="bi bi-person me-2"></i>Thông tin cá nhân</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/articles/mine"><i class="bi bi-journals me-2"></i>Bài viết của tôi</a></li>
              <?php if (\App\Core\Auth::user()['role_id'] === 3): ?>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/dashboard"><i class="bi bi-speedometer me-2"></i>Quản trị</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/auth/login"><i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập</a>
          <a class="btn btn-success" href="<?= BASE_URL ?>/auth/register"><i class="bi bi-person-plus me-1"></i>Đăng ký</a>
        <?php endif; ?>
        <a class="btn btn-warning" href="<?= BASE_URL ?>/articles/create"><i class="bi bi-pencil-square me-1"></i>Viết bài</a>
      </div>
    </div>
  </div>
</nav>

<main class="container my-4">
  <?= $content ?>
</main>

<footer class="footer mt-auto py-3 bg-dark text-light">
  <div class="container">
    <div class="row py-4">
      <div class="col-lg-4 mb-4 mb-lg-0">
        <h5 class="fw-bold mb-3">ArticleHub</h5>
        <p class="text-muted mb-3">Nơi chia sẻ kiến thức và cảm hứng mỗi ngày.</p>
        <div class="social-links mb-3">
          <a href="#" class="text-light me-3" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" class="text-light me-3" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="text-light me-3" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" class="text-light" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
        </div>
      </div>
      <div class="col-sm-6 col-lg-4 mb-4 mb-lg-0">
        <h6 class="text-uppercase fw-bold mb-3">Liên kết</h6>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="<?= BASE_URL ?>/" class="text-muted text-decoration-none">Trang chủ</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/articles" class="text-muted text-decoration-none">Bài viết</a></li>
          <li class="mb-2"><a href="<?= BASE_URL ?>/about" class="text-muted text-decoration-none">Giới thiệu</a></li>
          <li><a href="<?= BASE_URL ?>/contact" class="text-muted text-decoration-none">Liên hệ</a></li>
        </ul>
      </div>
      <div class="col-sm-6 col-lg-4">
        <h6 class="text-uppercase fw-bold mb-3">Liên hệ</h6>
        <ul class="list-unstyled text-muted">
          <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>123 Street, City, Country</li>
          <li class="mb-2"><i class="bi bi-envelope me-2"></i>contact@articlehub.com</li>
          <li><i class="bi bi-telephone me-2"></i>(123) 456-7890</li>
        </ul>
      </div>
    </div>
    <hr class="border-secondary my-0">
    <div class="row py-3">
      <div class="col-md-6 text-center text-md-start text-muted">
        <small>&copy; <?= date('Y') ?> ArticleHub. All rights reserved.</small>
      </div>
      <div class="col-md-6 text-center text-md-end text-muted">
        <small><a href="<?= BASE_URL ?>/privacy" class="text-muted text-decoration-none">Privacy Policy</a> · <a href="<?= BASE_URL ?>/terms" class="text-muted text-decoration-none">Terms of Service</a></small>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/js/app.js"></script>
</body>
</html>


