<?php
$error = $error ?? null;
$success = $success ?? null;
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body p-4">
                    <h1 class="text-center h4 mb-4">Đặt lại mật khẩu</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php else: ?>
                        <form method="post" action="<?= BASE_URL ?>/auth/reset-password" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu mới</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password"
                                       minlength="8"
                                       required>
                                <div class="form-text">
                                    Mật khẩu phải có ít nhất 8 ký tự
                                </div>
                                <div class="invalid-feedback">
                                    Vui lòng nhập mật khẩu hợp lệ
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">Xác nhận mật khẩu mới</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirm" 
                                       name="password_confirm"
                                       required>
                                <div class="invalid-feedback">
                                    Vui lòng xác nhận mật khẩu
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-key me-2"></i>Đặt lại mật khẩu
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    
    if (password !== confirm) {
        this.setCustomValidity('Mật khẩu xác nhận không khớp');
    } else {
        this.setCustomValidity('');
    }
});