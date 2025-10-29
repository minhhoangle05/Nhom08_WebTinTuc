// TinyMCE Configuration
tinymce.init({
    selector: '#content',
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample',
        'autosave', 'emoticons', 'quickbars', 'pagebreak'
    ],
    toolbar: [
        'undo redo | formatselect | bold italic underline strikethrough | ',
        'alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | ',
        'link image media codesample emoticons | removeformat | pagebreak | preview fullscreen'
    ].join(''),
    toolbar_sticky: true,
    quickbars_selection_toolbar: 'bold italic | h2 h3 | blockquote quicklink',
    quickbars_insert_toolbar: 'image media table',
    menubar: 'file edit view insert format tools table help',
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial; font-size: 16px; line-height: 1.6; }',
    height: 500,
    image_advtab: true,
    image_caption: true,
    automatic_uploads: true,
    file_picker_types: 'image',
    images_upload_url: BASE_URL + '/article/upload-image',
    relative_urls: false,
    remove_script_host: false,
    convert_urls: true,
    
    // Enable autosave
    autosave_ask_before_unload: true,
    autosave_interval: '30s',
    autosave_prefix: 'article-{path}{query}-{id}-',
    autosave_restore_when_empty: true,
    
    // Custom styles
    style_formats: [
        { title: 'Headers', items: [
            { title: 'Header 2', format: 'h2' },
            { title: 'Header 3', format: 'h3' },
            { title: 'Header 4', format: 'h4' }
        ]},
        { title: 'Inline', items: [
            { title: 'Bold', format: 'bold' },
            { title: 'Italic', format: 'italic' },
            { title: 'Code', format: 'code' }
        ]},
        { title: 'Blocks', items: [
            { title: 'Paragraph', format: 'p' },
            { title: 'Blockquote', format: 'blockquote' },
            { title: 'Note Box', block: 'div', classes: 'note-box', wrapper: true },
            { title: 'Warning Box', block: 'div', classes: 'warning-box', wrapper: true }
        ]}
    ],
    
    // Custom CSS for editor content
    content_css: [
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
        BASE_URL + '/css/editor.css'
    ],

    // Setup callback
    setup: function(editor) {
        // Word count and reading time
        editor.on('input', function() {
            const text = editor.getContent({ format: 'text' });
            const words = text.trim().split(/\s+/).length;
            const readingTime = Math.ceil(words / 200); // 200 từ/phút
            document.getElementById('reading_time').value = readingTime;
        });

        // Tự động lưu nháp
        editor.on('AutosaveComplete', function(e) {
            const toast = document.createElement('div');
            toast.className = 'toast bg-success text-white position-fixed bottom-0 end-0 m-3';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="toast-body">
                    <i class="bi bi-check-circle me-2"></i>
                    Đã tự động lưu bản nháp lúc ${new Date().toLocaleTimeString()}
                </div>
            `;
            document.body.appendChild(toast);
            new bootstrap.Toast(toast, { delay: 3000 }).show();
        });

        // Paste hình ảnh
        editor.on('paste', function(e) {
            var items = (e.clipboardData || e.originalEvent.clipboardData).items;
            for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    var file = items[i].getAsFile();
                    editor.uploadImages([file]);
                    e.preventDefault();
                    return;
                }
            }
        });

        // Auto-save notification
        editor.on('StoreDraft', function() {
            let notice = document.createElement('div');
            notice.className = 'autosave-notice';
            notice.textContent = 'Draft saved ' + new Date().toLocaleTimeString();
            document.body.appendChild(notice);
            setTimeout(function() {
                notice.remove();
            }, 3000);
        });
    }
});

// Xử lý kéo thả ảnh đại diện
const dropZone = document.getElementById('image-drop-zone');
const imageInput = document.getElementById('featured_image');
const imagePreview = document.getElementById('image-preview');
const previewContainer = document.querySelector('.featured-image-preview');

if (dropZone && imageInput) {
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-primary', 'bg-light');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-primary', 'bg-light');
        });
    });

    dropZone.addEventListener('drop', handleDrop);
    imageInput.addEventListener('change', handleFileSelect);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    function handleFileSelect(e) {
        const files = e.target.files;
        handleFiles(files);
    }

    function handleFiles(files) {
        if (files.length) {
            const file = files[0];
            if (!file.type.startsWith('image/')) {
                showError('Vui lòng chọn file ảnh hợp lệ (JPG, PNG hoặc GIF)');
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                showError('Kích thước ảnh không được vượt quá 2MB');
                return;
            }
            previewImage(file);
        }
    }

    function previewImage(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            previewContainer.style.display = 'block';
            // Thêm nút xóa ảnh
            addRemoveButton();
        };
        reader.readAsDataURL(file);
    }

    function addRemoveButton() {
        let removeBtn = previewContainer.querySelector('.remove-image');
        if (!removeBtn) {
            removeBtn = document.createElement('button');
            removeBtn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0 m-2';
            removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
            removeBtn.onclick = function(e) {
                e.preventDefault();
                imageInput.value = '';
                previewContainer.style.display = 'none';
                this.remove();
            };
            previewContainer.appendChild(removeBtn);
        }
    }
}

// Xử lý slug tự động
const titleInput = document.getElementById('title');
const slugInput = document.getElementById('slug');

if (titleInput && slugInput) {
    titleInput.addEventListener('input', function() {
        const slug = createSlug(this.value);
        slugInput.value = slug;
    });
}

function createSlug(str) {
    str = str.toLowerCase().trim();
    str = str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    str = str.replace(/[^a-z0-9\s-]/g, '')
             .replace(/\s+/g, '-')
             .replace(/-+/g, '-');
    return str;
}

// Initialize Select2 for tags
$(document).ready(function() {
    $('#tags').select2({
        placeholder: 'Chọn hoặc thêm tags',
        tags: true,
        tokenSeparators: [',', ' '],
        maximumSelectionLength: 5,
        minimumInputLength: 2,
        language: {
            maximumSelected: function() {
                return 'Bạn chỉ có thể chọn tối đa 5 tags';
            },
            noResults: function() {
                return 'Không tìm thấy tag phù hợp. Nhấn Enter để tạo mới.';
            },
            inputTooShort: function() {
                return 'Vui lòng nhập ít nhất 2 ký tự';
            }
        },
        templateResult: function(tag) {
            if (!tag.id) return tag.text;
            return $('<span>' + tag.text + ' <small class="text-muted">(' + 
                    (tag.count || 'Tag mới') + ')</small></span>');
        }
    });

    // Initialize Flatpickr for datetime picker
    // Khởi tạo datetime picker
    flatpickr("#publish_at", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        time_24hr: true,
        minDate: "today",
        defaultHour: new Date().getHours(),
        defaultMinute: new Date().getMinutes(),
        locale: {
            firstDayOfWeek: 1,
            weekdays: {
                shorthand: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                longhand: ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy']
            },
            months: {
                shorthand: ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'],
                longhand: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12']
            }
        }
    });

    // Slug generator
    $('#title').on('input', function() {
        if (!$('#slug').data('manual')) {
            let slug = generateSlug($(this).val());
            $('#slug').val(slug);
        }
    });

    $('#slug').on('input', function() {
        $(this).data('manual', true);
    });

    // Preview image before upload
    $('#featured_image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    // Form submission
    $('#articleForm').on('submit', function(e) {
        // Get content from TinyMCE
        let content = tinymce.get('content').getContent();
        if (!content) {
            e.preventDefault();
            alert('Vui lòng nhập nội dung bài viết');
            return false;
        }

        // Check required fields
        let requiredFields = ['title', 'slug'];
        let invalid = false;
        requiredFields.forEach(function(field) {
            if (!$('#' + field).val().trim()) {
                invalid = true;
                $('#' + field).addClass('is-invalid');
            }
        });

        if (invalid) {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ thông tin bắt buộc');
            return false;
        }
    });

    // Form validation và submit
    const form = document.getElementById('articleForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate tiêu đề
            const title = document.getElementById('title').value.trim();
            if (title.length < 10) {
                showError('Tiêu đề phải có ít nhất 10 ký tự');
                document.getElementById('title').focus();
                return;
            }

            // Validate nội dung
            const content = tinymce.get('content').getContent();
            if (content.length < 100) {
                showError('Nội dung bài viết quá ngắn, cần ít nhất 100 ký tự');
                tinymce.get('content').focus();
                return;
            }

            // Validate chuyên mục
            const categoryId = document.getElementById('category_id').value;
            if (!categoryId) {
                showError('Vui lòng chọn chuyên mục cho bài viết');
                document.getElementById('category_id').focus();
                return;
            }

            // Disable nút submit và hiện loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const draftBtn = this.querySelector('button[name="save_draft"]');
            submitBtn.disabled = true;
            draftBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';

            // Submit form
            this.submit();
        });
    }
});

// Hiển thị thông báo lỗi
function showError(message) {
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-danger border-0 position-fixed bottom-0 end-0 m-3';
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    document.body.appendChild(toast);
    new bootstrap.Toast(toast, { delay: 5000 }).show();
}

// Slug generator function
function generateSlug(text) {
    return text.toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, 'd')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}