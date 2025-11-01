<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-person-plus"></i> <?= htmlspecialchars($title) ?></h4>
                </div>
                <div class="card-body">
                    <?php if ($error = App\Core\Session::flash('error')): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/admin/users/create">
                        <input type="hidden" name="csrf" value="<?= $csrf ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label">Tên đầy đủ <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="name" 
                                name="name" 
                                required
                                placeholder="Nguyễn Văn A"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                required
                                placeholder="email@example.com"
                            >
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="password" 
                                name="password" 
                                required
                                minlength="6"
                                placeholder="Tối thiểu 6 ký tự"
                            >
                            <small class="form-text text-muted">Mật khẩu phải có ít nhất 6 ký tự</small>
                        </div>

                        <div class="mb-4">
                            <label for="role_id" class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select class="form-select" id="role_id" name="role_id" required>
                                <option value="2" selected>User (Người dùng thông thường)</option>
                                <option value="1">Admin (Quản trị viên)</option>
                            </select>
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle"></i> Admin có toàn quyền quản lý hệ thống
                            </small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-check-circle"></i> Tạo người dùng
                            </button>
                            <a href="<?= BASE_URL ?>/admin/users" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>