<div class="row justify-content-center my-5">
  <div class="col-md-6">
    <div class="card shadow">
      <div class="card-body p-4">
        <h2 class="text-center mb-4"><?= htmlspecialchars($title) ?></h2>
        
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>/auth/login" id="loginForm">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
          
          <div class="mb-3">
            <label class="form-label">Email</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" class="form-control" required
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                     placeholder="Email của bạn">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Mật khẩu</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-key"></i></span>
              <input type="password" name="password" class="form-control" required
                     id="password" placeholder="Mật khẩu của bạn">
              <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>

          <div class="mb-3 d-flex justify-content-between align-items-center">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" name="remember" id="remember">
              <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
            </div>
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
    // Xử lý ẩn/hiện mật khẩu
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('i').classList.toggle('bi-eye');
        this.querySelector('i').classList.toggle('bi-eye-slash');
    });

    // Ngăn chặn việc submit form nhiều lần
    const form = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    
    form.addEventListener('submit', function() {
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang đăng nhập...';
    });
});
</script>


