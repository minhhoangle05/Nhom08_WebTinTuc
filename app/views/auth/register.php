<div class="row justify-content-center">
  <div class="col-md-6">
    <h2><?= htmlspecialchars($title) ?></h2>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= BASE_URL ?>/auth/register">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
      <div class="mb-3">
        <label class="form-label">Họ tên <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" required minlength="3" maxlength="100" 
               pattern="[A-Za-zÀ-ỹ\s]+" title="Họ tên chỉ được chứa chữ cái và khoảng trắng"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        <div class="form-text">Tên của bạn sẽ hiển thị công khai trên các bài viết</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" required maxlength="150"
               pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <div class="form-text">Email sẽ được sử dụng để đăng nhập và khôi phục mật khẩu</div>
      </div>
      <div class="row g-2">
        <div class="col-md-6 mb-3">
          <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
          <input type="password" name="password" id="password" class="form-control" required
                 minlength="8" maxlength="50"
                 pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                 title="Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt">
          <div class="form-text">
            Mật khẩu phải có ít nhất:
            <ul class="mb-0 ps-3">
              <li>8 ký tự</li>
              <li>1 chữ hoa</li>
              <li>1 chữ thường</li>
              <li>1 số</li>
              <li>1 ký tự đặc biệt (@$!%*?&)</li>
            </ul>
          </div>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Nhập lại mật khẩu <span class="text-danger">*</span></label>
          <input type="password" name="password2" id="password2" class="form-control" required>
          <div class="form-text password-match-msg"></div>
        </div>
      </div>
      <div class="mb-3">
        <div class="form-check">
          <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
          <label class="form-check-label" for="terms">
            Tôi đồng ý với <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">điều khoản sử dụng</a>
          </label>
        </div>
      </div>
      <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Đăng ký</button>
        <a href="<?= BASE_URL ?>/auth/login" class="btn btn-outline-secondary">Đã có tài khoản?</a>
      </div>
    </form>
  </div>
</div>

<!-- Modal điều khoản -->
<div class="modal fade" id="termsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Điều khoản sử dụng</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <h6>1. Quy định chung</h6>
        <p>Người dùng phải tuân thủ các quy định về nội dung đăng tải, bảo mật thông tin...</p>
        
        <h6>2. Quyền riêng tư</h6>
        <p>Chúng tôi cam kết bảo vệ thông tin cá nhân của người dùng...</p>
        
        <h6>3. Nội dung</h6>
        <p>Người dùng chịu trách nhiệm về nội dung đăng tải, không vi phạm bản quyền...</p>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const password2 = document.getElementById('password2');
    const msgDiv = document.querySelector('.password-match-msg');
    
    // Kiểm tra mật khẩu khớp nhau
    function checkPasswordMatch() {
        if (password2.value === '') {
            msgDiv.textContent = '';
            msgDiv.className = 'form-text password-match-msg';
        } else if (password.value === password2.value) {
            msgDiv.textContent = 'Mật khẩu khớp';
            msgDiv.className = 'form-text password-match-msg text-success';
        } else {
            msgDiv.textContent = 'Mật khẩu không khớp';
            msgDiv.className = 'form-text password-match-msg text-danger';
        }
    }

    password.addEventListener('input', checkPasswordMatch);
    password2.addEventListener('input', checkPasswordMatch);
    
    // Kiểm tra form trước khi submit
    document.querySelector('form').addEventListener('submit', function(e) {
        if (password.value !== password2.value) {
            e.preventDefault();
            alert('Mật khẩu nhập lại không khớp!');
        }
    });
});
</script>


