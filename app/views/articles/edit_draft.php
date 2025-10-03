<?php require __DIR__ . '/../layouts/main.php'; ?>

<div class="container py-4">
    <form action="<?= BASE_URL ?>/article/store" method="POST" enctype="multipart/form-data" id="articleForm">
        <input type="hidden" name="csrf" value="<?= $csrf ?>">
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Nội dung bài viết</h4>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required
                                   value="<?= htmlspecialchars($article['title'] ?? '') ?>"
                                   onkeyup="generateSlug(this.value)">
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="slug" name="slug" required
                                   value="<?= htmlspecialchars($article['slug'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="summary" class="form-label">Tóm tắt</label>
                            <textarea class="form-control" id="summary" name="summary" rows="3"><?= htmlspecialchars($article['summary'] ?? '') ?></textarea>
                            <div class="form-text">Tóm tắt ngắn gọn về nội dung bài viết</div>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Nội dung <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="15" required><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">SEO</h4>
                        
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea class="form-control" id="meta_description" name="meta_description" rows="2"><?= htmlspecialchars($article['meta_description'] ?? '') ?></textarea>
                            <div class="form-text">Mô tả ngắn sẽ hiển thị trong kết quả tìm kiếm</div>
                        </div>

                        <div class="mb-3">
                            <label for="meta_keywords" class="form-label">Meta Keywords</label>
                            <input type="text" class="form-control" id="meta_keywords" name="meta_keywords"
                                   value="<?= htmlspecialchars($article['meta_keywords'] ?? '') ?>">
                            <div class="form-text">Các từ khóa chính của bài viết, cách nhau bằng dấu phẩy</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Xuất bản</h4>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft" <?= ($article['status'] ?? '') === 'draft' ? 'selected' : '' ?>>
                                    Bản nháp
                                </option>
                                <option value="published" <?= ($article['status'] ?? '') === 'published' ? 'selected' : '' ?>>
                                    Xuất bản ngay
                                </option>
                                <option value="private" <?= ($article['status'] ?? '') === 'private' ? 'selected' : '' ?>>
                                    Riêng tư
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="publish_at" class="form-label">Thời gian xuất bản</label>
                            <input type="datetime-local" class="form-control" id="publish_at" name="publish_at"
                                   value="<?= ($article['publish_at'] ?? '') ? date('Y-m-d\TH:i', strtotime($article['publish_at'])) : '' ?>">
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="allow_comments" name="allow_comments"
                                       <?= ($article['allow_comments'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="allow_comments">
                                    Cho phép bình luận
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Phân loại</h4>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Chuyên mục</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">-- Chọn chuyên mục --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"
                                            <?= ($article['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <select class="form-select" id="tags" name="tags[]" multiple>
                                <?php foreach ($tags as $tag): ?>
                                    <option value="<?= $tag['id'] ?>"
                                            <?= in_array($tag['id'], $article['tags'] ?? []) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tag['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Ảnh đại diện</h4>
                        
                        <?php if (!empty($article['featured_image'])): ?>
                            <div class="mb-3">
                                <img src="<?= BASE_URL ?>/public/uploads/articles/<?= htmlspecialchars($article['featured_image']) ?>"
                                     class="img-fluid rounded" alt="Featured Image">
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="featured_image" class="form-label">Upload ảnh mới</label>
                            <input type="file" class="form-control" id="featured_image" name="featured_image"
                                   accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu bài viết
                    </button>
                    <button type="submit" name="save_draft" value="1" class="btn btn-secondary">
                        <i class="fas fa-save"></i> Lưu nháp
                    </button>
                    <a href="<?= BASE_URL ?>/articles" class="btn btn-link">Hủy</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function generateSlug(title) {
    // Chuyển tiếng Việt thành dạng không dấu
    let slug = title.toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, 'd');
    
    // Thay thế ký tự đặc biệt bằng dấu gạch ngang
    slug = slug.replace(/[^a-z0-9]+/g, '-');
    
    // Xóa dấu gạch ngang ở đầu và cuối
    slug = slug.replace(/^-+|-+$/g, '');
    
    document.getElementById('slug').value = slug;
}

// Khởi tạo rich text editor cho nội dung
if (typeof tinymce !== 'undefined') {
    tinymce.init({
        selector: '#content',
        plugins: 'autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table emoticons',
        toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | preview media fullscreen',
        height: 500
    });
}

// Khởi tạo select2 cho tags nếu có
if (typeof $.fn.select2 !== 'undefined') {
    $('#tags').select2({
        placeholder: 'Chọn tags',
        allowClear: true,
        tags: true
    });
}
</script>