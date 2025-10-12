    <footer class="bg-dark text-light mt-5">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-4">
                    <h5>Về chúng tôi</h5>
                    <p>Website tin tức cập nhật những thông tin mới nhất và chính xác nhất về các lĩnh vực công nghệ, đời sống, kinh tế, xã hội...</p>
                </div>
                <div class="col-md-4">
                    <h5>Liên kết nhanh</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?= BASE_URL ?>/" class="text-light">Trang chủ</a></li>
                        <li><a href="<?= BASE_URL ?>/articles" class="text-light">Tin tức</a></li>
                        <li><a href="<?= BASE_URL ?>/contact" class="text-light">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Theo dõi chúng tôi</h5>
                    <div class="social-links">
                        <a href="#" class="text-light me-2"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-light me-2"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-light me-2"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center py-3" style="background-color: rgba(0, 0, 0, 0.2);">
            <small>&copy; <?= date('Y') ?> Your Website. All rights reserved.</small>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/js/app.js"></script>
</body>
</html>