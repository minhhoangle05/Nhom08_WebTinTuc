<?php require __DIR__ . '/../layouts/main.php'; ?>

<div class="container py-4">
    <h1 class="mb-4">Bản nháp của tôi</h1>
    
    <div class="row">
        <div class="col-md-12 mb-3">
            <a href="<?= BASE_URL ?>/article/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tạo bài viết mới
            </a>
        </div>
    </div>

    <?php if (empty($drafts)): ?>
        <div class="alert alert-info">
            Bạn chưa có bản nháp nào.
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($drafts as $draft): ?>
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <h5 class="mb-1"><?= htmlspecialchars($draft['title']) ?></h5>
                        <small class="text-muted">
                            Cập nhật: <?= date('d/m/Y H:i', strtotime($draft['updated_at'])) ?>
                        </small>
                    </div>
                    <?php if ($draft['summary']): ?>
                        <p class="mb-1"><?= htmlspecialchars($draft['summary']) ?></p>
                    <?php endif; ?>
                    <div class="mt-2">
                        <a href="<?= BASE_URL ?>/article/draft/<?= $draft['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i> Tiếp tục chỉnh sửa
                        </a>
                        <form action="<?= BASE_URL ?>/article/draft/<?= $draft['id'] ?>/delete" method="POST" class="d-inline">
                            <input type="hidden" name="csrf" value="<?= $csrf ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa bản nháp này?')">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>