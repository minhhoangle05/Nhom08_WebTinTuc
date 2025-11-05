<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .editor-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            padding: 3rem;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .page-header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 1.5rem;
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .page-header h1 {
            color: #2d3748;
            font-weight: 700;
            font-size: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .back-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .back-link:hover {
            background: #f7fafc;
            transform: translateX(-3px);
        }
        
        .form-group {
            margin-bottom: 1.75rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .required {
            color: #e53e3e;
        }
        
        .form-control, .form-select {
            width: 100%;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
            font-family: 'Courier New', monospace;
            line-height: 1.6;
        }
        
        textarea.content-area {
            min-height: 300px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        .image-upload-area {
            border: 2px dashed #cbd5e0;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            background: #f7fafc;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .image-upload-area:hover {
            border-color: #667eea;
            background: #edf2f7;
        }
        
        .image-upload-area svg {
            width: 64px;
            height: 64px;
            color: #cbd5e0;
            margin-bottom: 1rem;
        }
        
        .current-image {
            max-width: 100%;
            border-radius: 12px;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .image-preview {
            display: none;
            margin-top: 1rem;
        }
        
        .image-preview.active {
            display: block;
        }
        
        .image-preview img {
            max-width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .tags-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
            max-height: 300px;
            overflow-y: auto;
            padding: 1rem;
            background: #f7fafc;
            border-radius: 8px;
        }
        
        .tag-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .tag-checkbox:hover {
            background: white;
        }
        
        .tag-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }
        
        .action-buttons {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid #e2e8f0;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 0.875rem 2rem;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .alert svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #e53e3e;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #38a169;
        }
        
        .helper-text {
            font-size: 0.85rem;
            color: #718096;
            margin-top: 0.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.25rem;
        }
        
        .helper-text svg {
            width: 14px;
            height: 14px;
            margin-top: 2px;
            flex-shrink: 0;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .editor-container {
                padding: 1.5rem;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column-reverse;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="editor-container">
        <div class="page-header">
            <h1>
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Chỉnh sửa bài viết
            </h1>
            <a href="<?= BASE_URL ?>/article/<?= htmlspecialchars($article['slug']) ?>" class="back-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Quay lại
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/articles/<?= $article['id'] ?>/update" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">

            <!-- Tiêu đề -->
            <div class="form-group">
                <label for="title" class="form-label">
                    Tiêu đề bài viết <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    class="form-control"
                    value="<?= htmlspecialchars($article['title']) ?>"
                    placeholder="Nhập tiêu đề bài viết..."
                    required
                >
            </div>

            <!-- Slug -->
            <div class="form-group">
                <label for="slug" class="form-label">
                    URL (Slug) <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    id="slug" 
                    name="slug" 
                    class="form-control"
                    value="<?= htmlspecialchars($article['slug']) ?>"
                    placeholder="bai-viet-cua-toi"
                    required
                    style="font-family: 'Courier New', monospace;"
                >
                <p class="helper-text">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Chỉ dùng chữ thường, số và dấu gạch ngang. VD: cong-nghe-moi-2024
                </p>
            </div>

            <!-- Danh mục & Tóm tắt -->
            <div class="form-row">
                <div class="form-group">
                    <label for="category_id" class="form-label">Danh mục</label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="">-- Không chọn danh mục --</option>
                        <?php foreach ($categories as $category): ?>
                            <option 
                                value="<?= $category['id'] ?>"
                                <?= $article['category_id'] == $category['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="summary" class="form-label">Tóm tắt ngắn</label>
                    <textarea 
                        id="summary" 
                        name="summary" 
                        class="form-control"
                        placeholder="Mô tả ngắn gọn về bài viết..."
                    ><?= htmlspecialchars($article['summary'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Nội dung -->
            <div class="form-group">
                <label for="content" class="form-label">
                    Nội dung bài viết <span class="required">*</span>
                </label>
                <textarea 
                    id="content" 
                    name="content" 
                    class="form-control content-area"
                    placeholder="Viết nội dung bài viết của bạn tại đây..."
                    required
                ><?= htmlspecialchars($article['content']) ?></textarea>
                <p class="helper-text">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Hỗ trợ định dạng HTML và Markdown
                </p>
            </div>

            <!-- Ảnh đại diện -->
            <div class="form-group">
                <label class="form-label">Ảnh đại diện</label>
                
                <?php if (!empty($article['featured_image'])): ?>
                    <img 
                        src="<?= BASE_URL ?>/uploads/articles/<?= htmlspecialchars($article['featured_image']) ?>" 
                        alt="Current image"
                        class="current-image"
                    >
                <?php endif; ?>

                <input 
                    type="file" 
                    id="featured_image" 
                    name="featured_image"
                    accept="image/*"
                    style="display: none;"
                >
                
                <label for="featured_image" class="image-upload-area">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p style="color: #4a5568; font-weight: 500; margin: 0;">Nhấp để chọn ảnh mới</p>
                    <p style="color: #718096; font-size: 0.875rem; margin-top: 0.5rem;">JPG, PNG, GIF, WEBP (Tối đa 5MB)</p>
                </label>

                <div class="image-preview" id="imagePreview">
                    <p style="font-weight: 600; color: #4a5568; margin-bottom: 0.75rem;">Xem trước ảnh mới:</p>
                    <img id="previewImg" src="" alt="Preview">
                </div>

                <p class="helper-text">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Để trống nếu không muốn thay đổi ảnh hiện tại
                </p>
            </div>

            <!-- Tags -->
            <div class="form-group">
                <label class="form-label">Tags (Thẻ)</label>
                <div class="tags-container">
                    <?php 
                    $selectedTagIds = array_column($articleTags, 'id');
                    foreach ($tags as $tag): 
                    ?>
                        <label class="tag-checkbox">
                            <input 
                                type="checkbox" 
                                name="tags[]" 
                                value="<?= $tag['id'] ?>"
                                <?= in_array($tag['id'], $selectedTagIds) ? 'checked' : '' ?>
                            >
                            <span><?= htmlspecialchars($tag['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>/article/<?= htmlspecialchars($article['slug']) ?>" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Hủy bỏ
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>

    <script>
        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function(e) {
            const slugField = document.getElementById('slug');
            if (!slugField.dataset.manuallyEdited) {
                const title = e.target.value;
                slugField.value = title
                    .toLowerCase()
                    .replace(/á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/g, 'a')
                    .replace(/đ/g, 'd')
                    .replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/g, 'e')
                    .replace(/í|ì|ỉ|ĩ|ị/g, 'i')
                    .replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/g, 'o')
                    .replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/g, 'u')
                    .replace(/ý|ỳ|ỷ|ỹ|ỵ/g, 'y')
                    .replace(/[^a-z0-9-]/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
            }
        });

        document.getElementById('slug').addEventListener('input', function() {
            this.dataset.manuallyEdited = 'true';
        });

        // Image preview
        document.getElementById('featured_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewContainer = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.classList.add('active');
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.classList.remove('active');
            }
        });

        // Unsaved changes warning
        let formModified = false;
        const formElements = document.querySelectorAll('input, textarea, select');
        formElements.forEach(element => {
            element.addEventListener('change', () => {
                formModified = true;
            });
        });

        window.addEventListener('beforeunload', (e) => {
            if (formModified) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        document.querySelector('form').addEventListener('submit', () => {
            formModified = false;
        });
    </script>
</body>
</html>