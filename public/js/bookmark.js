/**
 * Bookmark Functionality
 * Xử lý các chức năng bookmark cho bài viết
 */

class BookmarkManager {
    constructor() {
        this.baseUrl = window.BASE_URL || '';
        this.init();
    }

    init() {
        // Khởi tạo bookmark buttons
        this.initBookmarkButtons();
        
        // Khởi tạo bookmark count
        this.initBookmarkCounts();
    }

    /**
     * Khởi tạo các nút bookmark
     */
    initBookmarkButtons() {
        // Nút bookmark trong trang chi tiết
        const bookmarkBtn = document.getElementById('bookmarkBtn');
        if (bookmarkBtn) {
            bookmarkBtn.addEventListener('click', (e) => this.handleBookmarkToggle(e));
        }

        // Nút bookmark trong danh sách
        document.querySelectorAll('.bookmark-toggle-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleBookmarkToggle(e));
        });

        // Nút remove bookmark
        document.querySelectorAll('.btn-remove-bookmark').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleRemoveBookmark(e));
        });
    }

    /**
     * Khởi tạo bookmark counts
     */
    initBookmarkCounts() {
        const countElements = document.querySelectorAll('[data-bookmark-count]');
        countElements.forEach(el => {
            const articleId = el.dataset.articleId;
            if (articleId) {
                this.loadBookmarkCount(articleId, el);
            }
        });
    }

    /**
     * Xử lý toggle bookmark
     */
    async handleBookmarkToggle(e) {
        e.preventDefault();
        e.stopPropagation();
    
        const btn = e.currentTarget;
        const articleId = btn.dataset.articleId;
    
        if (!articleId) {
            this.showToast('error', 'Không tìm thấy thông tin bài viết');
            return;
        }
    
        btn.disabled = true;
    
        try {
            const response = await fetch(`${this.baseUrl}/bookmarks/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ article_id: parseInt(articleId) })
            });
    
            // Log response để debug
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            const text = await response.text();
            console.log('Response text:', text);
    
            // Kiểm tra response status
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
    
            // Parse JSON
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response was:', text);
                throw new Error('Server trả về dữ liệu không hợp lệ');
            }
    
            if (data.success) {
                this.updateBookmarkUI(btn, data.bookmarked);
                this.updateBookmarkCount(articleId, data.count);
                this.showToast('success', data.message);
            } else {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    this.showToast('error', data.message || 'Có lỗi xảy ra');
                }
            }
        } catch (error) {
            console.error('Bookmark toggle error:', error);
            this.showToast('error', error.message || 'Không thể kết nối đến server');
        } finally {
            btn.disabled = false;
        }
    }

    /**
     * Xử lý xóa bookmark
     */
    async handleRemoveBookmark(e) {
        e.preventDefault();
        e.stopPropagation();

        const btn = e.currentTarget;
        const articleId = btn.dataset.articleId;
        const card = btn.closest('.col-lg-4, .col-md-6');

        if (!confirm('Bạn có chắc muốn xóa bài viết này khỏi danh sách yêu thích?')) {
            return;
        }

        btn.disabled = true;

        try {
            const response = await fetch(`${this.baseUrl}/bookmarks/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ article_id: parseInt(articleId) })
            });

            const data = await response.json();
            console.log('Raw response:', text);

            if (data.success) {
                // Fade out và xóa card
                if (card) {
                    card.style.transition = 'opacity 0.3s ease';
                    card.style.opacity = '0';

                    setTimeout(() => {
                        card.remove();

                        // Kiểm tra nếu không còn bookmark nào
                        const remainingCards = document.querySelectorAll('.bookmark-card').length;
                        if (remainingCards === 0) {
                            location.reload();
                        }
                    }, 300);
                }

                this.showToast('success', data.message);
            } else {
                this.showToast('error', data.message || 'Có lỗi xảy ra');
            }
        } catch (error) {
            console.error('Remove bookmark error:', error);
            this.showToast('error', 'Không thể kết nối đến server');
        } finally {
            btn.disabled = false;
        }
    }

    /**
     * Cập nhật UI của nút bookmark
     */
    updateBookmarkUI(btn, isBookmarked) {
        const icon = btn.querySelector('i');
        const text = btn.querySelector('.bookmark-text, span');

        if (isBookmarked) {
            btn.classList.add('bookmarked');
            btn.dataset.bookmarked = 'true';
            
            if (icon) {
                icon.classList.remove('bi-bookmark-heart');
                icon.classList.add('bi-bookmark-heart-fill');
            }
            
            if (text) {
                text.textContent = 'Đã lưu';
            }
            
            if (btn.title) {
                btn.title = 'Đã lưu';
            }
        } else {
            btn.classList.remove('bookmarked');
            btn.dataset.bookmarked = 'false';
            
            if (icon) {
                icon.classList.remove('bi-bookmark-heart-fill');
                icon.classList.add('bi-bookmark-heart');
            }
            
            if (text) {
                text.textContent = 'Lưu bài viết';
            }
            
            if (btn.title) {
                btn.title = 'Lưu bài viết';
            }
        }
    }

    /**
     * Cập nhật số lượng bookmark
     */
    updateBookmarkCount(articleId, count) {
        const countElements = document.querySelectorAll(`[data-article-id="${articleId}"] #bookmarkCount, [data-bookmark-count][data-article-id="${articleId}"]`);
        countElements.forEach(el => {
            el.textContent = count;
        });
    }

    /**
     * Load số lượng bookmark
     */
    async loadBookmarkCount(articleId, element) {
        try {
            const response = await fetch(`${this.baseUrl}/bookmarks/check?article_id=${articleId}`);
            const data = await response.json();

            if (data.success && data.count !== undefined) {
                element.textContent = data.count;
            }
        } catch (error) {
            console.error('Error loading bookmark count:', error);
        }
    }

    /**
     * Hiển thị toast notification
     */
    showToast(type, message) {
        // Tìm hoặc tạo toast container
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        // Tạo toast
        const toast = document.createElement('div');
        const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';

        toast.className = `toast align-items-center text-white ${bgClass} border-0`;
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

        container.appendChild(toast);

        // Show toast
        const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
        bsToast.show();

        // Remove after hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    /**
     * Escape HTML để tránh XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Khởi tạo khi DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.bookmarkManager = new BookmarkManager();
});