// public/js/article-ai.js - PHIÊN BẢN SỬA LỖI

/**
 * Xử lý tính năng tóm tắt bài viết bằng AI
 */
class ArticleSummarizer {
    constructor() {
        this.summaryBtn = document.getElementById('summarizeBtn');
        this.summaryContainer = document.getElementById('summaryContainer');
        this.summaryContent = document.getElementById('summaryContent');
        this.articleContent = document.querySelector('.article-content');
        
        // Debug log
        console.log('ArticleSummarizer initialized:', {
            btn: !!this.summaryBtn,
            container: !!this.summaryContainer,
            content: !!this.summaryContent,
            article: !!this.articleContent
        });
        
        if (this.summaryBtn && this.articleContent) {
            this.init();
        } else {
            console.error('Không tìm thấy elements cần thiết');
        }
    }

    init() {
        // Xử lý sự kiện click nút tóm tắt
        this.summaryBtn.addEventListener('click', () => this.handleSummarize());
        console.log('Event listener đã được gắn');
    }

    async handleSummarize() {
        console.log('handleSummarize được gọi');
        
        // Disable nút để tránh spam
        this.summaryBtn.disabled = true;
        this.summaryBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang tóm tắt...';
        
        // Ẩn kết quả cũ nếu có
        this.summaryContainer.classList.add('d-none');
        
        try {
            // Lấy nội dung bài viết
            const content = this.articleContent.innerText.trim();
            
            console.log('Nội dung bài viết (100 ký tự đầu):', content.substring(0, 100));
            console.log('Độ dài nội dung:', content.length);
            
            if (!content || content.length < 50) {
                throw new Error('Nội dung bài viết quá ngắn hoặc không hợp lệ');
            }

            // Gọi API tóm tắt
            const summary = await this.callSummarizeAPI(content);
            
            console.log('Summary nhận được:', summary);
            console.log('Độ dài summary:', summary ? summary.length : 0);
            
            // Kiểm tra summary có hợp lệ không
            if (!summary || summary.trim().length === 0) {
                throw new Error('API trả về summary rỗng');
            }
            
            // Hiển thị kết quả
            this.displaySummary(summary);
            
            // Thông báo thành công
            this.showToast('success', 'Tóm tắt bài viết thành công!');
            
        } catch (error) {
            console.error('Lỗi chi tiết:', error);
            this.showToast('error', error.message || 'Có lỗi xảy ra khi tóm tắt');
            
            // Thử phương pháp dự phòng
            if (error.message.includes('loading') || 
                error.message.includes('request') || 
                error.message.includes('rỗng')) {
                console.log('Thử phương pháp local...');
                await this.tryLocalSummarize();
            }
        } finally {
            // Enable lại nút
            this.summaryBtn.disabled = false;
            this.summaryBtn.innerHTML = '<i class="bi bi-stars me-2"></i>Tóm tắt bài viết bằng AI';
        }
    }

    async callSummarizeAPI(content) {
        console.log('Gọi API summarize...');
        
        const url = `${window.BASE_URL}/api/summarize`;
        console.log('API URL:', url);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json; charset=utf-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ content: content })
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', {
            contentType: response.headers.get('content-type'),
            contentLength: response.headers.get('content-length')
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Response error:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const responseText = await response.text();
        console.log('Response text (raw):', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('JSON parse error:', e);
            throw new Error('Phản hồi từ server không hợp lệ');
        }

        console.log('Parsed data:', data);

        if (!data.success) {
            throw new Error(data.message || 'Không thể tóm tắt bài viết');
        }

        if (!data.summary || data.summary.trim().length === 0) {
            console.error('Summary rỗng trong response:', data);
            throw new Error('API trả về summary rỗng');
        }

        return data.summary;
    }

    async tryLocalSummarize() {
        try {
            console.log('Trying local summarize...');
            this.summaryBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Thử phương pháp khác...';
            
            const content = this.articleContent.innerText.trim();
            
            const response = await fetch(`${window.BASE_URL}/api/summarize-local`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json; charset=utf-8'
                },
                body: JSON.stringify({ content: content })
            });

            const data = await response.json();
            console.log('Local summarize result:', data);

            if (data.success && data.summary && data.summary.trim().length > 0) {
                this.displaySummary(data.summary);
                this.showToast('info', 'Sử dụng phương pháp tóm tắt cơ bản');
            } else {
                throw new Error('Phương pháp local cũng thất bại');
            }
        } catch (error) {
            console.error('Lỗi phương pháp dự phòng:', error);
            this.showToast('error', 'Không thể tóm tắt bài viết. Vui lòng thử lại sau.');
        }
    }

    displaySummary(summary) {
        console.log('Displaying summary:', summary);
        
        // Escape HTML và giữ nguyên line breaks
        const escapedSummary = this.escapeHtml(summary);
        const formattedSummary = escapedSummary.replace(/\n/g, '<br>');
        
        // Hiển thị nội dung tóm tắt
        this.summaryContent.innerHTML = `
            <div class="summary-text">
                <i class="bi bi-quote text-primary me-2"></i>
                <span style="position: relative; z-index: 1;">${formattedSummary}</span>
            </div>
        `;
        
        // Hiện container với hiệu ứng
        this.summaryContainer.classList.remove('d-none');
        
        // Scroll đến summary (sau một chút delay để animation hoàn thành)
        setTimeout(() => {
            this.summaryContainer.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'nearest' 
            });
        }, 100);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    showToast(type, message) {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const bgColor = type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info';
        const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${bgColor} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${icon} me-2"></i>
                    ${this.escapeHtml(message)}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
}

// Khởi tạo khi DOM loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, khởi tạo ArticleSummarizer...');
    new ArticleSummarizer();
});