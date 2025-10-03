// TinyMCE Configuration
tinymce.init({
    selector: '#content',
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample'
    ],
    toolbar: 'undo redo | styles | bold italic underline strikethrough | ' +
        'alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist outdent indent | link image media codesample | ' +
        'removeformat preview fullscreen help',
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
        // Word count
        editor.on('WordCountUpdate', function(e) {
            let words = e.wordCount;
            let readingTime = Math.ceil(words / 200); // Assume 200 words per minute
            document.getElementById('reading-time').value = readingTime;
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

// Initialize Select2 for tags
$(document).ready(function() {
    $('#tags').select2({
        placeholder: 'Chọn hoặc thêm tags',
        tags: true,
        tokenSeparators: [','],
        maximumSelectionLength: 5,
        language: {
            maximumSelected: function() {
                return 'Bạn chỉ có thể chọn tối đa 5 tags';
            }
        }
    });

    // Initialize Flatpickr for datetime picker
    flatpickr("#publish_at", {
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        time_24hr: true,
        minDate: "today",
        defaultHour: new Date().getHours(),
        defaultMinute: new Date().getMinutes()
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
});

// Slug generator function
function generateSlug(text) {
    return text.toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[đĐ]/g, 'd')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}