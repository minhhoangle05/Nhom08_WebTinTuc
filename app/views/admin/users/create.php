<div class="mb-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Admin</a></li>
      <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/users">Người dùng</a></li>
      <li class="breadcrumb-item active">Tạo mới</li>
    </ol>
  </nav>
</div>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4 class="mb-0">
          <i class="bi bi-person-plus me-2"></i>
          <?= htmlspecialchars($title) ?>
        </h4>
      </div>
      <div class="card-body">
        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php
            $errors = [
              'missing_fields' => 'Vui lòng điền đầy đủ thông tin',
              'invalid_email' => 'Email không hợp lệ',
              'password_short' => 'Mật khẩu phải có ít nhất 6 ký tự',
              'email_exists' => 'Email này đã được sử dụng',
              'create_failed' => 'Không thể tạo người dùng. Vui lòng thử lại'
            ];
            echo $errors[$_GET['error']] ?? 'Có lỗi xảy ra';
          ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/admin/users/create">
          <input type="hidden" name="csrf" value="<?= $csrf ?>">
          
          <div class="mb-3">
            <label for="name" class="form-label">
              Tên đầy đủ <span class="text-danger">*</span>
            </label>
            <input type="text" 
                   class="form-control" 
                   id="name" 
                   name="name" 
                   required 
                   maxlength="100"
                   placeholder="Nhập tên đầy đủ">
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">
              Email <span class="text-danger">*</span>
            </label>
            <input type="email" 
                   class="form-control" 
                   id="email" 
                   name="email" 
                   required 
                   maxlength="100"
                   placeholder="Nhập địa chỉ email">
            <div class="form-text">Email sẽ được sử dụng để đăng nhập</div>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">
              Mật khẩu <span class="text-danger">*</span>
            </label>
            <input type="password" 
                   class="form-control" 
                   id="password" 
                   name="password" 
                   required 
                   minlength="6"
                   placeholder="Nhập mật khẩu">
            <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự</div>
          </div>

          <div class="mb-3">
            <label for="password_confirm" class="form-label">
              Xác nhận mật khẩu <span class="text-danger">*</span>
            </label>
            <input type="password" 
                   class="form-control" 
                   id="password_confirm" 
                   required 
                   minlength="6"
                   placeholder="Nhập lại mật khẩu">
            <div id="password-match-message" class="form-text"></div>
          </div>

          <div class="mb-4">
            <label for="role_id" class="form-label">
              Vai trò <span class="text-danger">*</span>
            </label>
            <select class="form-select" id="role_id" name="role_id" required>
              <option value="1">User - Người dùng thông thường</option>
              <option value="2">Editor - Biên tập viên</option>
              <option value="3">Admin - Quản trị viên</option>
            </select>
            <div class="form-text">
              <ul class="mb-0 mt-2">
                <li><strong>User:</strong> Có thể tạo và quản lý bài viết của mình</li>
                <li><strong>Editor:</strong> Có thể chỉnh sửa bài viết của người khác</li>
                <li><strong>Admin:</strong> Có toàn quyền quản trị hệ thống</li>
              </ul>
            </div>
          </div>

          <div class="d-flex justify-content-between">
            <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary">
              <i class="bi bi-arrow-left me-2"></i>Quay lại
            </a>
            <button type="submit" class="btn btn-primary" id="submit-btn">
              <i class="bi bi-check-circle me-2"></i>Tạo người dùng
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Password confirmation validation
const password = document.getElementById('password');
const passwordConfirm = document.getElementById('password_confirm');
const submitBtn = document.getElementById('submit-btn');
const matchMessage = document.getElementById('password-match-message');

function checkPasswordMatch() {
  if (passwordConfirm.value === '') {
    matchMessage.textContent = '';
    matchMessage.className = 'form-text';
    return;
  }
  
  if (password.value === passwordConfirm.value) {
    matchMessage.textContent = '✓ Mật khẩu khớp';
    matchMessage.className = 'form-text text-success';
    submitBtn.disabled = false;
  } else {
    matchMessage.textContent = '✗ Mật khẩu không khớp';
    matchMessage.className = 'form-text text-danger';
    submitBtn.disabled = true;
  }
}

password.addEventListener('input', checkPasswordMatch);
passwordConfirm.addEventListener('input', checkPasswordMatch);

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
  if (password.value !== passwordConfirm.value) {
    e.preventDefault();
    alert('Mật khẩu xác nhận không khớp!');
    return false;
  }
  
  if (password.value.length < 6) {
    e.preventDefault();
    alert('Mật khẩu phải có ít nhất 6 ký tự!');
    return false;
  }
});
</script>

<style>
.form-text ul {
  padding-left: 1.2rem;
  font-size: 0.875rem;
}

.form-text ul li {
  margin-bottom: 0.25rem;
}
</style>