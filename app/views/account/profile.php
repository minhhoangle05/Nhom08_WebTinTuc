<div class="container py-4">
    <h1 class="h3 mb-4"><?= htmlspecialchars($title ?? 'Thông tin tài khoản') ?></h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" action="<?= BASE_URL ?>/account/profile">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">

                <div class="mb-3">
                    <label class="form-label">ID</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['id'] ?? '') ?>" disabled>
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Họ và tên</label>
                    <input type="text" id="name" name="name" class="form-control" required maxlength="100"
                           value="<?= htmlspecialchars($_POST['name'] ?? ($user['name'] ?? '')) ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required maxlength="150"
                           value="<?= htmlspecialchars($_POST['email'] ?? ($user['email'] ?? '')) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Quyền</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['role_id'] ?? '') ?>" disabled>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tạo lúc</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['created_at'] ?? '') ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cập nhật lúc</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['updated_at'] ?? '') ?>" disabled>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    <a href="<?= BASE_URL ?>/" class="btn btn-secondary">Về trang chủ</a>
                </div>
            </form>
        </div>
    </div>
</div>


