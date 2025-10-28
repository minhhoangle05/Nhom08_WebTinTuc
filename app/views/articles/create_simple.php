<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Tạo bài viết mới</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?= BASE_URL ?>/articles" enctype="multipart/form-data">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        
                        <!-- Tiêu đề -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required 
                                   value="<?= htmlspecialchars($oldInput['title'] ?? '') ?>"
                                   placeholder="Nhập tiêu đề bài viết...">
                        </div>

                        <!-- Slug -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">URL Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="slug" name="slug" required 
                                   value="<?= htmlspecialchars($oldInput['slug'] ?? '') ?>"
                                   placeholder="url-slug-cua-bai-viet">
                            <div class="form-text">Định danh URL cho bài viết (sẽ được tự động tạo từ tiêu đề nếu để trống)</div>
                        </div>

                        <!-- Tóm tắt -->
                        <div class="mb-3">
                            <label for="summary" class="form-label">Tóm tắt</label>
                            <textarea class="form-control" id="summary" name="summary" rows="3" 
                                      placeholder="Mô tả ngắn gọn về bài viết..."><?= htmlspecialchars($oldInput['summary'] ?? '') ?></textarea>
                        </div>

                        <!-- Nội dung -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Nội dung bài viết <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="10" required 
                                      placeholder="Nhập nội dung bài viết..."><?= htmlspecialchars($oldInput['content'] ?? '') ?></textarea>
                        </div>

                        <!-- Danh mục -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Chuyên mục</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">-- Chọn chuyên mục --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                            <?= (($oldInput['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tags -->
                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <select class="form-select" id="tags" name="tags[]" multiple>
                                <?php foreach ($tags as $tag): ?>
                                    <option value="<?= $tag['id'] ?>" 
                                            <?= in_array($tag['id'], $oldInput['tags'] ?? []) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tag['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Chọn các tags liên quan đến bài viết (có thể chọn nhiều)</div>
                        </div>

                        <!-- Ảnh đại diện -->
                        <div class="mb-3">
                            <label for="featured_image" class="form-label">Ảnh đại diện</label>
                            <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                            <div class="form-text">Chọn ảnh đại diện cho bài viết (tùy chọn)</div>
                        </div>

                        <!-- Nút submit -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= BASE_URL ?>/articles" class="btn btn-secondary me-md-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Tạo bài viết
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tự động tạo slug từ tiêu đề
document.getElementById('title').addEventListener('input', function() {
    const title = this.value;
    const slugField = document.getElementById('slug');
    
    if (slugField.value === '') {
        // Chỉ tạo slug tự động nếu slug field đang trống
        const slug = title
            .toLowerCase()
            .replace(/[áàảãạăắằẳẵặâấầẩẫậ]/g, 'a')
            .replace(/[éèẻẽẹêếềểễệ]/g, 'e')
            .replace(/[íìỉĩị]/g, 'i')
            .replace(/[óòỏõọôốồổỗộơớờởỡợ]/g, 'o')
            .replace(/[úùủũụưứừửữự]/g, 'u')
            .replace(/[ýỳỷỹỵ]/g, 'y')
            .replace(/đ/g, 'd')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        
        slugField.value = slug;
    }
});

// Đếm ký tự cho summary
document.getElementById('summary').addEventListener('input', function() {
    const length = this.value.length;
    const maxLength = 500;
    
    if (length > maxLength) {
        this.value = this.value.substring(0, maxLength);
    }
});
</script>
