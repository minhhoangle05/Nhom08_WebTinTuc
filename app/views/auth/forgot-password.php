<?php
$error = $error ?? null;
$success = $success ?? null;
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body p-4">
                    <h1 class="text-center h4 mb-4">Quên mật khẩu</h1>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?= BASE_URL ?>/auth/forgot-password" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?= isset($oldInput['email']) ? htmlspecialchars($oldInput['email']) : '' ?>"
                                   required>
                            <div class="form-text">
                                Nhập email bạn đã dùng để đăng ký tài khoản
                            </div>
                            <div class="invalid-feedback">
                                Vui lòng nhập email hợp lệ
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-envelope me-2"></i>Gửi yêu cầu
                            </button>
                            <a href="<?= BASE_URL ?>/auth/login" class="btn btn-link">
                                Quay lại đăng nhập
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>