// Comments System JavaScript
class CommentsSystem {
    constructor() {
        this.articleId = null;
        this.comments = [];
        this.currentPage = 1;
        this.loading = false;
        this.init();
    }

    init() {
        this.articleId = document.querySelector('input[name="article_id"]')?.value;
        if (!this.articleId) {
            console.error('Article ID not found');
            return;
        }

        console.log('Initializing comments system for article:', this.articleId);
        this.bindEvents();
        this.loadComments();
        this.setupCharacterCounter();
    }

    bindEvents() {
        // Comment form submission
        const commentForm = document.getElementById('comment-form');
        if (commentForm) {
            commentForm.addEventListener('submit', (e) => this.handleCommentSubmit(e));
        }

        // Reply form submission
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('reply-comment-form')) {
                e.preventDefault();
                this.handleReplySubmit(e);
            }
        });

        // Edit form submission
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('edit-form')) {
                e.preventDefault();
                this.handleEditSubmit(e);
            }
        });

        // Report form submission
        const reportForm = document.getElementById('report-form');
        if (reportForm) {
            reportForm.addEventListener('submit', (e) => this.handleReportSubmit(e));
        }

        // Like/Dislike buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.like-btn, .dislike-btn')) {
                e.preventDefault();
                this.handleLikeDislike(e);
            }
        });

        // Reply buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.reply-btn')) {
                e.preventDefault();
                this.showReplyForm(e);
            }
        });

        // Edit buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.edit-btn')) {
                e.preventDefault();
                this.showEditForm(e);
            }
        });

        // Delete buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.delete-btn')) {
                e.preventDefault();
                this.handleDelete(e);
            }
        });

        // Report buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.report-btn')) {
                e.preventDefault();
                this.showReportModal(e);
            }
        });

        // Cancel buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.cancel-reply')) {
                e.preventDefault();
                this.hideReplyForm(e);
            }
            if (e.target.closest('.cancel-edit')) {
                e.preventDefault();
                this.hideEditForm(e);
            }
        });
    }

    setupCharacterCounter() {
        const textarea = document.getElementById('comment-content');
        const charCount = document.getElementById('char-count');
        
        if (textarea && charCount) {
            textarea.addEventListener('input', () => {
                const count = textarea.value.length;
                charCount.textContent = count;
                
                if (count > 900) {
                    charCount.classList.add('text-warning');
                } else {
                    charCount.classList.remove('text-warning');
                }
            });
        }
    }

    async loadComments() {
        if (this.loading) return;
        
        this.loading = true;
        this.showLoading();

        try {
            console.log('Loading comments for article:', this.articleId);
            const response = await fetch(`${BASE_URL}/comments/get?article_id=${this.articleId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Comments loaded:', data);

            if (data.success) {
                this.comments = data.comments;
                this.renderComments();
                this.updateCommentCount();
            } else {
                this.showError('Không thể tải bình luận: ' + (data.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error loading comments:', error);
            this.showError('Có lỗi xảy ra khi tải bình luận: ' + error.message);
        } finally {
            this.loading = false;
        }
    }

    async handleCommentSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang gửi...';

        try {
            const formData = new FormData(form);
            const response = await fetch(`${BASE_URL}/comments/create`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            console.log('Comment create response:', data);

            if (data.success) {
                // Clear form
                form.reset();
                document.getElementById('char-count').textContent = '0';
                
                // Reload comments
                await this.loadComments();
                
                // Show success message
                this.showSuccess(data.message || 'Bình luận đã được thêm thành công');
                
                // Scroll to comments section
                setTimeout(() => {
                    document.getElementById('comments-container').scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 100);
            } else {
                this.showError(data.error || 'Không thể tạo bình luận');
            }
        } catch (error) {
            console.error('Error submitting comment:', error);
            this.showError('Có lỗi xảy ra khi gửi bình luận');
        } finally {
            // Restore button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    async handleReplySubmit(e) {
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang gửi...';

        try {
            const formData = new FormData(form);
            const response = await fetch(`${BASE_URL}/comments/create`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Reset form
                form.reset();
                
                // Hide reply form
                const replyContainer = form.closest('.reply-form-container');
                if (replyContainer) {
                    replyContainer.style.display = 'none';
                }
                
                // Reload comments
                await this.loadComments();
                
                this.showSuccess(data.message || 'Trả lời đã được thêm thành công');
            } else {
                this.showError(data.error || 'Không thể gửi trả lời');
            }
        } catch (error) {
            console.error('Error submitting reply:', error);
            this.showError('Có lỗi xảy ra khi gửi trả lời');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    async handleEditSubmit(e) {
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...';

        try {
            const formData = new FormData(form);
            const response = await fetch(`${BASE_URL}/comments/update`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Hide edit form
                const editForm = form.closest('.comment-edit-form');
                const commentText = form.closest('.comment-content').querySelector('.comment-text');
                
                if (editForm && commentText) {
                    editForm.style.display = 'none';
                    commentText.style.display = 'block';
                }
                
                // Reload comments
                await this.loadComments();
                
                this.showSuccess(data.message || 'Bình luận đã được cập nhật');
            } else {
                this.showError(data.error || 'Không thể cập nhật bình luận');
            }
        } catch (error) {
            console.error('Error updating comment:', error);
            this.showError('Có lỗi xảy ra khi cập nhật bình luận');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    async handleDelete(e) {
        const commentItem = e.target.closest('.comment-item');
        const commentId = commentItem.dataset.commentId;
        
        if (!confirm('Bạn có chắc chắn muốn xóa bình luận này? Hành động này không thể hoàn tác.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('csrf', document.querySelector('input[name="csrf"]').value);
            formData.append('comment_id', commentId);

            const response = await fetch(`${BASE_URL}/comments/delete`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Reload comments to update UI
                await this.loadComments();
                
                this.showSuccess(data.message || 'Bình luận đã được xóa');
            } else {
                this.showError(data.error || 'Không thể xóa bình luận');
            }
        } catch (error) {
            console.error('Error deleting comment:', error);
            this.showError('Có lỗi xảy ra khi xóa bình luận');
        }
    }

    async handleLikeDislike(e) {
        const btn = e.target.closest('.like-btn, .dislike-btn');
        const action = btn.dataset.action;
        const commentItem = btn.closest('.comment-item');
        const commentId = commentItem.dataset.commentId;

        try {
            const formData = new FormData();
            formData.append('csrf', document.querySelector('input[name="csrf"]').value);
            formData.append('comment_id', commentId);
            formData.append('action', action);

            const response = await fetch(`${BASE_URL}/comments/toggle-like`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Update like/dislike counts
                const likeBtn = commentItem.querySelector('.like-btn');
                const dislikeBtn = commentItem.querySelector('.dislike-btn');
                const likeCount = commentItem.querySelector('.like-count');
                const dislikeCount = commentItem.querySelector('.dislike-count');

                likeCount.textContent = data.likes;
                dislikeCount.textContent = data.dislikes;

                // Update button states
                likeBtn.classList.remove('active');
                dislikeBtn.classList.remove('active');

                if (data.action === 'like') {
                    likeBtn.classList.add('active');
                } else if (data.action === 'dislike') {
                    dislikeBtn.classList.add('active');
                }
            } else {
                this.showError(data.error || 'Có lỗi xảy ra');
            }
        } catch (error) {
            console.error('Error toggling like:', error);
            this.showError('Có lỗi xảy ra');
        }
    }

    async handleReportSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang gửi...';

        try {
            const formData = new FormData(form);
            const response = await fetch(`${BASE_URL}/comments/report`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Reset form
                form.reset();
                
                this.showSuccess(data.message || 'Báo cáo đã được gửi thành công');
            } else {
                this.showError(data.error || 'Không thể gửi báo cáo');
            }
        } catch (error) {
            console.error('Error reporting comment:', error);
            this.showError('Có lỗi xảy ra khi gửi báo cáo');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    showReplyForm(e) {
        const commentItem = e.target.closest('.comment-item');
        const replyContainer = commentItem.querySelector(':scope > .comment-card > .reply-form-container');
        const authorName = commentItem.querySelector('.author-name').textContent;
        
        // Hide other reply forms
        document.querySelectorAll('.reply-form-container').forEach(container => {
            if (container !== replyContainer) {
                container.style.display = 'none';
            }
        });
        
        // Show this reply form
        if (replyContainer) {
            replyContainer.style.display = 'block';
            replyContainer.querySelector('.reply-to-author').textContent = authorName;
            replyContainer.querySelector('input[name="parent_id"]').value = commentItem.dataset.commentId;
            
            // Focus on textarea
            const textarea = replyContainer.querySelector('textarea');
            textarea.focus();
        }
    }

    hideReplyForm(e) {
        const replyContainer = e.target.closest('.reply-form-container');
        if (replyContainer) {
            replyContainer.style.display = 'none';
            const form = replyContainer.querySelector('form');
            if (form) form.reset();
        }
    }

    showEditForm(e) {
        const commentItem = e.target.closest('.comment-item');
        const commentContent = commentItem.querySelector(':scope > .comment-card > .comment-content');
        const commentText = commentContent.querySelector('.comment-text');
        const editForm = commentContent.querySelector('.comment-edit-form');
        const textarea = editForm.querySelector('textarea');
        
        // Hide other edit forms
        document.querySelectorAll('.comment-edit-form').forEach(form => {
            if (form !== editForm) {
                form.style.display = 'none';
                const sibling = form.previousElementSibling;
                if (sibling && sibling.classList.contains('comment-text')) {
                    sibling.style.display = 'block';
                }
            }
        });
        
        // Show this edit form
        editForm.style.display = 'block';
        commentText.style.display = 'none';
        
        // Set content
        textarea.value = commentText.textContent.trim();
        editForm.querySelector('input[name="comment_id"]').value = commentItem.dataset.commentId;
        
        // Setup character counter
        const charCount = editForm.querySelector('.edit-char-count');
        const updateCounter = () => {
            charCount.textContent = textarea.value.length;
        };
        textarea.addEventListener('input', updateCounter);
        updateCounter();
        
        textarea.focus();
    }

    hideEditForm(e) {
        const editForm = e.target.closest('.comment-edit-form');
        const commentContent = editForm.closest('.comment-content');
        const commentText = commentContent.querySelector('.comment-text');
        
        if (editForm && commentText) {
            editForm.style.display = 'none';
            commentText.style.display = 'block';
            const form = editForm.querySelector('form');
            if (form) form.reset();
        }
    }

    showReportModal(e) {
        const commentItem = e.target.closest('.comment-item');
        const commentId = commentItem.dataset.commentId;
        
        const modalElement = document.getElementById('reportModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            document.querySelector('#report-form input[name="comment_id"]').value = commentId;
            modal.show();
        }
    }

    renderComments() {
        const container = document.getElementById('comments-container');
        const template = document.getElementById('comment-template');
        
        if (!container || !template) {
            console.error('Comments container or template not found');
            return;
        }
        
        container.innerHTML = '';
        
        if (this.comments.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="bi bi-chat-dots display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">Chưa có bình luận nào</h4>
                        <p class="text-muted">Hãy là người đầu tiên chia sẻ suy nghĩ về bài viết này!</p>
                    </div>
                </div>
            `;
            return;
        }
        
        this.comments.forEach(comment => {
            const commentElement = this.createCommentElement(comment, template);
            container.appendChild(commentElement);
        });
    }

    createCommentElement(comment, template) {
        const clone = template.content.cloneNode(true);
        const commentItem = clone.querySelector('.comment-item');
        
        // Set comment data
        commentItem.dataset.commentId = comment.id;
        
        // Set author info
        const avatarText = clone.querySelector('.avatar-text');
        const authorName = clone.querySelector('.author-name');
        const commentDate = clone.querySelector('.comment-date');
        
        avatarText.textContent = comment.user_name ? comment.user_name.charAt(0).toUpperCase() : '?';
        authorName.textContent = comment.user_name || 'Anonymous';
        commentDate.textContent = this.formatDate(comment.created_at);
        
        // Set comment content
        const commentText = clone.querySelector('.comment-text');
        commentText.textContent = comment.content;
        
        // Set like/dislike counts
        const likeCount = clone.querySelector('.like-count');
        const dislikeCount = clone.querySelector('.dislike-count');
        const likeBtn = clone.querySelector('.like-btn');
        const dislikeBtn = clone.querySelector('.dislike-btn');
        
        likeCount.textContent = comment.like_count || 0;
        dislikeCount.textContent = comment.dislike_count || 0;
        
        if (comment.user_action === 'like') {
            likeBtn.classList.add('active');
        } else if (comment.user_action === 'dislike') {
            dislikeBtn.classList.add('active');
        }
        
        // Show edit/delete buttons for comment owner or admin
        const currentUserId = window.currentUser?.id;
        const isAdmin = window.currentUser?.role_id === 1;
        
        if (currentUserId && (comment.user_id == currentUserId || isAdmin)) {
            clone.querySelector('.edit-comment-item').style.display = 'block';
            clone.querySelector('.delete-comment-item').style.display = 'block';
        }
        
        // Show edited indicator
        if (comment.is_edited) {
            clone.querySelector('.comment-edited').style.display = 'inline';
        }
        
        // Set reply count
        const replyCount = comment.replies?.length || 0;
        if (replyCount > 0) {
            const replyCountElement = clone.querySelector('.reply-count');
            replyCountElement.style.display = 'inline';
            replyCountElement.querySelector('.reply-count-text').textContent = replyCount;
            
            // Render replies
            const repliesContainer = clone.querySelector('.replies-list');
            comment.replies.forEach(reply => {
                const replyElement = this.createCommentElement(reply, template);
                repliesContainer.appendChild(replyElement);
            });
        }
        
        return clone;
    }

    updateCommentCount() {
        const countElement = document.getElementById('comment-count');
        if (countElement) {
            const totalComments = this.countTotalComments(this.comments);
            countElement.textContent = totalComments;
        }
    }

    countTotalComments(comments) {
        let count = comments.length;
        comments.forEach(comment => {
            if (comment.replies) {
                count += this.countTotalComments(comment.replies);
            }
        });
        return count;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (minutes < 1) return 'Vừa xong';
        if (minutes < 60) return `${minutes} phút trước`;
        if (hours < 24) return `${hours} giờ trước`;
        if (days < 7) return `${days} ngày trước`;
        
        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    showLoading() {
        const container = document.getElementById('comments-container');
        if (container) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                    <p class="mt-2 text-muted">Đang tải bình luận...</p>
                </div>
            `;
        }
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    showError(message) {
        this.showToast(message, 'danger');
    }

    showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        // Add to page
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.appendChild(toast);
        
        // Show toast
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 3000
        });
        bsToast.show();
        
        // Remove from DOM after hiding
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
}

// Initialize comments system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('Initializing Comments System...');
    new CommentsSystem();
});