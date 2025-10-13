<footer class="footer py-3 bg-dark text-light">
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