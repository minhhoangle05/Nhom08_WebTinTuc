<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <form method="post" action="<?= BASE_URL ?>/articles" enctype="multipart/form-data" id="articleForm" class="needs-validation" novalidate>
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                
                <div class="row">
                    <!-- Cột trái - Nội dung chính -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Nội dung bài viết</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                    <div class="invalid-feedback">Vui lòng nhập tiêu đề bài viết</div>
                                </div>

                                <div class="mb-3">
                                    <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="slug" name="slug" required>
                                    <div class="form-text">Định danh URL cho bài viết, được tự động tạo từ tiêu đề</div>
                                </div>

                                <div class="mb-3">
                                    <label for="summary" class="form-label">Tóm tắt</label>
                                    <textarea class="form-control" id="summary" name="summary" rows="3"
                                            maxlength="500" data-bs-toggle="tooltip" 
                                            title="Tóm tắt ngắn gọn về bài viết, tối đa 500 ký tự"></textarea>
                                    <div class="form-text">
                                        <span id="summary-chars">0</span>/500 ký tự
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label">Nội dung <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="content" name="content" rows="12" required></textarea>
                                    <div class="invalid-feedback">Vui lòng nhập nội dung bài viết</div>
                                </div>
                            </div>
                        </div>

                        <!-- SEO -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">SEO</h4>
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        data-bs-toggle="collapse" data-bs-target="#seoOptions">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </div>
                            <div class="collapse show" id="seoOptions">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description" 
                                                rows="2" maxlength="160"></textarea>
                                        <div class="form-text">
                                            Mô tả ngắn sẽ hiển thị trong kết quả tìm kiếm, tối đa 160 ký tự
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords">
                                        <div class="form-text">
                                            Các từ khóa chính của bài viết, cách nhau bằng dấu phẩy
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột phải - Thông tin phụ -->
                    <div class="col-md-4">
                        <!-- Xuất bản -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Xuất bản</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Trạng thái</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="draft">Bản nháp</option>
                                        <option value="published">Xuất bản ngay</option>
                                        <option value="private">Riêng tư</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="publish_at" class="form-label">Thời gian xuất bản</label>
                                    <input type="text" class="form-control" id="publish_at" name="publish_at">
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="allow_comments" 
                                               name="allow_comments" value="1" checked>
                                        <label class="form-check-label" for="allow_comments">
                                            Cho phép bình luận
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Thời gian đọc ước tính</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="reading_time" 
                                               name="reading_time" readonly>
                                        <span class="input-group-text">phút</span>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg"></i> Xuất bản
                                    </button>
                                    <button type="submit" name="save_draft" value="1" class="btn btn-outline-secondary">
                                        <i class="bi bi-save"></i> Lưu nháp
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Phân loại -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Phân loại</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Chuyên mục</label>
                                    <select class="form-select" id="category_id" name="category_id">
                                        <option value="">-- Chọn chuyên mục --</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>">
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="tags" class="form-label">Tags</label>
                                    <select class="form-select" id="tags" name="tags[]" multiple>
                                        <?php foreach ($tags as $tag): ?>
                                            <option value="<?= $tag['id'] ?>">
                                                <?= htmlspecialchars($tag['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">Chọn hoặc thêm tags mới cho bài viết</div>
                                </div>
                            </div>
                        </div>

                        <!-- Ảnh đại diện -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Ảnh đại diện</h4>
                            </div>
                            <div class="card-body">
                                <div class="featured-image-preview mb-3" style="display:none">
                                    <img id="image-preview" src="#" alt="Preview" 
                                         class="img-fluid rounded">
                                </div>
                                
                                <div class="mb-3">
                                    <input type="file" class="form-control" id="featured_image" 
                                           name="featured_image" accept="image/*">
                                    <div class="form-text">
                                        Kích thước khuyến nghị: 1200x630px, tối đa 2MB
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Thêm script của trình soạn thảo -->
<script src="<?= BASE_URL ?>/js/article-editor.js"></script>

<script>
// Tooltip initialization
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});

// Summary character count
document.getElementById('summary').addEventListener('input', function() {
    document.getElementById('summary-chars').textContent = this.value.length;
});
</script>


