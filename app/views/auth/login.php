<div class="auth-wrapper">
    <div class="auth-container">
        <div class="card auth-card border-0">
            <div class="card-body p-4 p-lg-5">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock display-5 text-primary mb-3"></i>
                    <h1 class="auth-title h3"><?= htmlspecialchars($title) ?></h1>
                    <p class="text-muted">Chào mừng bạn trở lại! Vui lòng đăng nhập để tiếp tục.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/auth/login" id="loginForm" class="auth-form">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
          
                <div class="form-floating mb-4">
                    <div class="auth-input-group">
                        <span class="auth-input-icon"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control form-control-lg" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="Email của bạn">
                    </div>
                </div>

                <div class="form-floating mb-4">
                    <div class="auth-input-group has-action">
                        <span class="auth-input-icon"><i class="bi bi-key"></i></span>
                        <input type="password" name="password" class="form-control form-control-lg" required
                               id="password" placeholder="Mật khẩu của bạn">
                        <button class="auth-input-action" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                    </div>
            <a href="<?= BASE_URL ?>/auth/forgot-password" class="text-decoration-none">
              Quên mật khẩu?
            </a>
            <a href="<?= BASE_URL ?>/auth/forgot-password" class="text-decoration-none">Quên mật khẩu?</a>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary" id="loginBtn">
              <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
            </button>
            <a href="<?= BASE_URL ?>/auth/register" class="btn btn-outline-secondary">
              <i class="bi bi-person-plus me-1"></i> Đăng ký tài khoản mới
            </a>
          </div>
        </form>

        <?php if (!empty($locked_until)): ?>
          <div class="alert alert-warning mt-3">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Tài khoản đã bị khóa tạm thời. Vui lòng thử lại sau <?= $locked_until ?>.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ngăn chặn việc submit form nhiều lần
    const form = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    
    if (form && loginBtn) {
        form.addEventListener('submit', function() {
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang đăng nhập...';
        });
    }
});
</script>


