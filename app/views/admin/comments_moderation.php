<!-- Comments Moderation Panel -->
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="bi bi-shield-check me-2"></i>
                    Quản lý bình luận
                </h2>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-filter="all">
                        Tất cả
                    </button>
                    <button type="button" class="btn btn-outline-warning" data-filter="pending">
                        Chờ duyệt
                    </button>
                    <button type="button" class="btn btn-outline-danger" data-filter="reported">
                        Báo cáo
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="total-comments">0</h4>
                                    <p class="mb-0">Tổng bình luận</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-chat-dots display-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="pending-comments">0</h4>
                                    <p class="mb-0">Chờ duyệt</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-clock display-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="reported-comments">0</h4>
                                    <p class="mb-0">Báo cáo</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-flag display-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="approved-comments">0</h4>
                                    <p class="mb-0">Đã duyệt</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="bi bi-check-circle display-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments List -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Danh sách bình luận</h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm" id="bulk-action">
                                <option value="">Hành động hàng loạt</option>
                                <option value="approve">Duyệt</option>
                                <option value="reject">Từ chối</option>
                                <option value="hide">Ẩn</option>
                                <option value="delete">Xóa</option>
                            </select>
                            <button class="btn btn-sm btn-primary" id="apply-bulk-action">
                                Áp dụng
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="comments-list" class="comments-moderation-list">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                            <p class="mt-2 text-muted">Đang tải bình luận...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comment Moderation Template -->
<template id="comment-moderation-template">
    <div class="comment-moderation-item" data-comment-id="">
        <div class="comment-moderation-card">
            <div class="comment-header">
                <div class="form-check">
                    <input class="form-check-input comment-checkbox" type="checkbox" value="">
                </div>
                <div class="comment-info">
                    <div class="comment-author">
                        <div class="author-avatar">
                            <span class="avatar-text"></span>
                        </div>
                        <div class="author-details">
                            <div class="author-name"></div>
                            <div class="comment-meta">
                                <span class="comment-date"></span>
                                <span class="comment-status badge"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="comment-actions">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-success approve-btn" title="Duyệt">
                            <i class="bi bi-check-circle"></i>
                        </button>
                        <button class="btn btn-outline-danger reject-btn" title="Từ chối">
                            <i class="bi bi-x-circle"></i>
                        </button>
                        <button class="btn btn-outline-warning hide-btn" title="Ẩn">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                        <button class="btn btn-outline-danger delete-btn" title="Xóa">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="comment-content">
                <div class="comment-text"></div>
                <div class="comment-article">
                    <small class="text-muted">
                        <i class="bi bi-file-text me-1"></i>
                        Bài viết: <a href="" class="text-decoration-none"></a>
                    </small>
                </div>
            </div>
            
            <div class="comment-footer">
                <div class="comment-stats">
                    <span class="stat-item">
                        <i class="bi bi-hand-thumbs-up"></i>
                        <span class="like-count">0</span>
                    </span>
                    <span class="stat-item">
                        <i class="bi bi-hand-thumbs-down"></i>
                        <span class="dislike-count">0</span>
                    </span>
                    <span class="stat-item report-count" style="display: none;">
                        <i class="bi bi-flag"></i>
                        <span class="report-count-text">0</span> báo cáo
                    </span>
                </div>
                
                <div class="comment-reports" style="display: none;">
                    <div class="reports-list"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Report Details Template -->
<template id="report-template">
    <div class="report-item">
        <div class="report-header">
            <div class="report-reason badge"></div>
            <div class="report-date"></div>
        </div>
        <div class="report-description"></div>
        <div class="report-actions">
            <button class="btn btn-sm btn-outline-primary resolve-report-btn">
                <i class="bi bi-check me-1"></i>Đã xử lý
            </button>
        </div>
    </div>
</template>

<style>
/* Comments Moderation Styles */
.comments-moderation-list {
    max-height: 600px;
    overflow-y: auto;
}

.comment-moderation-item {
    border-bottom: 1px solid var(--bs-border-color);
    padding: 1rem;
    transition: all 0.3s ease;
}

.comment-moderation-item:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.comment-moderation-item:last-child {
    border-bottom: none;
}

.comment-moderation-card {
    background: transparent;
}

.comment-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
}

.comment-info {
    flex: 1;
}

.comment-author {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.author-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--bs-primary), var(--bs-info));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
}

.author-name {
    font-weight: 600;
    color: var(--bs-body-color);
    margin-bottom: 0.25rem;
}

.comment-meta {
    font-size: 0.85rem;
    color: var(--bs-secondary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.comment-status {
    font-size: 0.75rem;
}

.comment-content {
    margin-bottom: 1rem;
    padding-left: 3rem;
}

.comment-text {
    line-height: 1.6;
    color: var(--bs-body-color);
    margin-bottom: 0.5rem;
}

.comment-article a {
    color: var(--bs-primary);
}

.comment-footer {
    padding-left: 3rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.comment-stats {
    display: flex;
    gap: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.85rem;
    color: var(--bs-secondary);
}

.comment-reports {
    margin-top: 1rem;
    padding: 1rem;
    background: rgba(var(--bs-warning-rgb), 0.1);
    border-radius: 0.5rem;
    border: 1px solid rgba(var(--bs-warning-rgb), 0.2);
}

.report-item {
    padding: 0.75rem;
    background: white;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    border: 1px solid var(--bs-border-color);
}

.report-item:last-child {
    margin-bottom: 0;
}

.report-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.report-reason {
    font-size: 0.75rem;
}

.report-description {
    font-size: 0.85rem;
    color: var(--bs-body-color);
    margin-bottom: 0.5rem;
}

/* Status badges */
.badge.bg-success {
    background-color: var(--bs-success) !important;
}

.badge.bg-warning {
    background-color: var(--bs-warning) !important;
}

.badge.bg-danger {
    background-color: var(--bs-danger) !important;
}

.badge.bg-secondary {
    background-color: var(--bs-secondary) !important;
}

/* Responsive */
@media (max-width: 768px) {
    .comment-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .comment-content,
    .comment-footer {
        padding-left: 0;
    }
    
    .comment-actions {
        align-self: flex-end;
    }
    
    .comment-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}

/* Dark mode support */
[data-bs-theme="dark"] .comment-moderation-item:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}

[data-bs-theme="dark"] .comment-reports {
    background: rgba(var(--bs-warning-rgb), 0.2);
    border-color: rgba(var(--bs-warning-rgb), 0.3);
}

[data-bs-theme="dark"] .report-item {
    background: var(--bs-gray-800);
    border-color: var(--bs-gray-700);
}
</style>

<script>
// Comments Moderation System
class CommentsModeration {
    constructor() {
        this.comments = [];
        this.reports = [];
        this.currentFilter = 'all';
        this.selectedComments = new Set();
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadComments();
        this.loadReports();
    }

    bindEvents() {
        // Filter buttons
        document.querySelectorAll('[data-filter]').forEach(btn => {
            btn.addEventListener('click', (e) => this.setFilter(e.target.dataset.filter));
        });

        // Bulk actions
        document.getElementById('apply-bulk-action').addEventListener('click', () => {
            this.applyBulkAction();
        });

        // Individual action buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.approve-btn')) {
                e.preventDefault();
                this.moderateComment(e, 'approve');
            }
            if (e.target.closest('.reject-btn')) {
                e.preventDefault();
                this.moderateComment(e, 'reject');
            }
            if (e.target.closest('.hide-btn')) {
                e.preventDefault();
                this.moderateComment(e, 'hide');
            }
            if (e.target.closest('.delete-btn')) {
                e.preventDefault();
                this.deleteComment(e);
            }
            if (e.target.closest('.resolve-report-btn')) {
                e.preventDefault();
                this.resolveReport(e);
            }
        });

        // Checkbox changes
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('comment-checkbox')) {
                this.toggleCommentSelection(e.target);
            }
        });
    }

    async loadComments() {
        try {
            const response = await fetch(`${BASE_URL}/admin/comments/moderation`);
            const data = await response.json();

            if (data.success) {
                this.comments = data.comments;
                this.renderComments();
                this.updateStats();
            } else {
                this.showError('Không thể tải bình luận: ' + data.error);
            }
        } catch (error) {
            console.error('Error loading comments:', error);
            this.showError('Có lỗi xảy ra khi tải bình luận');
        }
    }

    async loadReports() {
        try {
            const response = await fetch(`${BASE_URL}/admin/comments/reports`);
            const data = await response.json();

            if (data.success) {
                this.reports = data.reports;
                this.updateReportStats();
            }
        } catch (error) {
            console.error('Error loading reports:', error);
        }
    }

    setFilter(filter) {
        this.currentFilter = filter;
        
        // Update button states
        document.querySelectorAll('[data-filter]').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-filter="${filter}"]`).classList.add('active');
        
        this.renderComments();
    }

    renderComments() {
        const container = document.getElementById('comments-list');
        const template = document.getElementById('comment-moderation-template');
        
        if (!container || !template) return;
        
        container.innerHTML = '';
        
        let filteredComments = this.comments;
        
        if (this.currentFilter === 'pending') {
            filteredComments = this.comments.filter(c => c.status === 'pending');
        } else if (this.currentFilter === 'reported') {
            filteredComments = this.comments.filter(c => c.report_count > 0);
        }
        
        if (filteredComments.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="bi bi-chat-dots display-1 text-muted mb-3"></i>
                        <h4>Không có bình luận nào</h4>
                        <p class="text-muted">Chưa có bình luận nào phù hợp với bộ lọc hiện tại.</p>
                    </div>
                </div>
            `;
            return;
        }
        
        filteredComments.forEach(comment => {
            this.renderComment(comment, container, template);
        });
    }

    renderComment(comment, container, template) {
        const clone = template.content.cloneNode(true);
        const commentItem = clone.querySelector('.comment-moderation-item');
        
        // Set comment data
        commentItem.dataset.commentId = comment.id;
        const checkbox = clone.querySelector('.comment-checkbox');
        checkbox.value = comment.id;
        
        // Set author info
        const avatarText = clone.querySelector('.avatar-text');
        const authorName = clone.querySelector('.author-name');
        const commentDate = clone.querySelector('.comment-date');
        const statusBadge = clone.querySelector('.comment-status');
        
        avatarText.textContent = comment.user_name.charAt(0).toUpperCase();
        authorName.textContent = comment.user_name;
        commentDate.textContent = this.formatDate(comment.created_at);
        
        // Set status badge
        const statusMap = {
            'approved': { class: 'bg-success', text: 'Đã duyệt' },
            'pending': { class: 'bg-warning', text: 'Chờ duyệt' },
            'rejected': { class: 'bg-danger', text: 'Từ chối' },
            'hidden': { class: 'bg-secondary', text: 'Ẩn' }
        };
        
        const status = statusMap[comment.status] || statusMap['pending'];
        statusBadge.className = `badge ${status.class}`;
        statusBadge.textContent = status.text;
        
        // Set comment content
        const commentText = clone.querySelector('.comment-text');
        const articleLink = clone.querySelector('.comment-article a');
        
        commentText.textContent = comment.content;
        articleLink.textContent = comment.article_title;
        articleLink.href = `${BASE_URL}/article/${comment.article_slug}`;
        
        // Set stats
        const likeCount = clone.querySelector('.like-count');
        const dislikeCount = clone.querySelector('.dislike-count');
        const reportCount = clone.querySelector('.report-count-text');
        const reportContainer = clone.querySelector('.comment-reports');
        
        likeCount.textContent = comment.likes || 0;
        dislikeCount.textContent = comment.dislikes || 0;
        
        if (comment.report_count > 0) {
            reportCount.textContent = comment.report_count;
            clone.querySelector('.report-count').style.display = 'inline-flex';
            
            // Show reports for this comment
            const commentReports = this.reports.filter(r => r.comment_id == comment.id);
            if (commentReports.length > 0) {
                reportContainer.style.display = 'block';
                this.renderReports(commentReports, clone.querySelector('.reports-list'));
            }
        }
        
        container.appendChild(clone);
    }

    renderReports(reports, container) {
        const template = document.getElementById('report-template');
        
        reports.forEach(report => {
            const clone = template.content.cloneNode(true);
            
            const reasonBadge = clone.querySelector('.report-reason');
            const reportDate = clone.querySelector('.report-date');
            const description = clone.querySelector('.report-description');
            const resolveBtn = clone.querySelector('.resolve-report-btn');
            
            const reasonMap = {
                'spam': { class: 'bg-danger', text: 'Spam' },
                'inappropriate': { class: 'bg-warning', text: 'Không phù hợp' },
                'harassment': { class: 'bg-danger', text: 'Quấy rối' },
                'offensive': { class: 'bg-danger', text: 'Xúc phạm' },
                'other': { class: 'bg-secondary', text: 'Khác' }
            };
            
            const reason = reasonMap[report.reason] || reasonMap['other'];
            reasonBadge.className = `badge ${reason.class}`;
            reasonBadge.textContent = reason.text;
            
            reportDate.textContent = this.formatDate(report.created_at);
            description.textContent = report.description || 'Không có mô tả';
            resolveBtn.dataset.reportId = report.id;
            
            container.appendChild(clone);
        });
    }

    async moderateComment(e, action) {
        const commentItem = e.target.closest('.comment-moderation-item');
        const commentId = commentItem.dataset.commentId;
        
        try {
            const formData = new FormData();
            formData.append('csrf', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('comment_id', commentId);
            formData.append('action', action);

            const response = await fetch(`${BASE_URL}/comments/moderate`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Update comment status
                const comment = this.comments.find(c => c.id == commentId);
                if (comment) {
                    comment.status = action === 'approve' ? 'approved' : 
                                   action === 'reject' ? 'rejected' : 'hidden';
                }
                
                this.renderComments();
                this.updateStats();
                this.showSuccess(data.message);
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            console.error('Error moderating comment:', error);
            this.showError('Có lỗi xảy ra khi thực hiện hành động');
        }
    }

    async deleteComment(e) {
        const commentItem = e.target.closest('.comment-moderation-item');
        const commentId = commentItem.dataset.commentId;
        
        if (!confirm('Bạn có chắc chắn muốn xóa bình luận này? Hành động này không thể hoàn tác.')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('csrf', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('comment_id', commentId);

            const response = await fetch(`${BASE_URL}/comments/delete`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Remove comment from list
                this.comments = this.comments.filter(c => c.id != commentId);
                this.renderComments();
                this.updateStats();
                this.showSuccess(data.message);
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            console.error('Error deleting comment:', error);
            this.showError('Có lỗi xảy ra khi xóa bình luận');
        }
    }

    async resolveReport(e) {
        const reportId = e.target.dataset.reportId;
        
        try {
            const formData = new FormData();
            formData.append('csrf', document.querySelector('meta[name="csrf-token"]').content);
            formData.append('report_id', reportId);
            formData.append('status', 'resolved');

            const response = await fetch(`${BASE_URL}/admin/comments/resolve-report`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Remove report from list
                this.reports = this.reports.filter(r => r.id != reportId);
                this.loadComments(); // Reload to update report counts
                this.showSuccess('Báo cáo đã được xử lý');
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            console.error('Error resolving report:', error);
            this.showError('Có lỗi xảy ra khi xử lý báo cáo');
        }
    }

    toggleCommentSelection(checkbox) {
        const commentId = checkbox.value;
        
        if (checkbox.checked) {
            this.selectedComments.add(commentId);
        } else {
            this.selectedComments.delete(commentId);
        }
    }

    async applyBulkAction() {
        const action = document.getElementById('bulk-action').value;
        
        if (!action) {
            this.showError('Vui lòng chọn hành động');
            return;
        }
        
        if (this.selectedComments.size === 0) {
            this.showError('Vui lòng chọn ít nhất một bình luận');
            return;
        }
        
        const confirmMessage = action === 'delete' ? 
            `Bạn có chắc chắn muốn xóa ${this.selectedComments.size} bình luận đã chọn?` :
            `Bạn có chắc chắn muốn ${this.getActionText(action)} ${this.selectedComments.size} bình luận đã chọn?`;
        
        if (!confirm(confirmMessage)) {
            return;
        }

        try {
            const promises = Array.from(this.selectedComments).map(commentId => {
                const formData = new FormData();
                formData.append('csrf', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('comment_id', commentId);
                
                if (action === 'delete') {
                    return fetch(`${BASE_URL}/comments/delete`, {
                        method: 'POST',
                        body: formData
                    });
                } else {
                    formData.append('action', action);
                    return fetch(`${BASE_URL}/comments/moderate`, {
                        method: 'POST',
                        body: formData
                    });
                }
            });

            const responses = await Promise.all(promises);
            const results = await Promise.all(responses.map(r => r.json()));
            
            const successCount = results.filter(r => r.success).length;
            
            if (successCount === results.length) {
                this.showSuccess(`Đã ${this.getActionText(action)} thành công ${successCount} bình luận`);
                this.selectedComments.clear();
                document.getElementById('bulk-action').value = '';
                document.querySelectorAll('.comment-checkbox').forEach(cb => cb.checked = false);
                this.loadComments();
            } else {
                this.showError(`Chỉ ${successCount}/${results.length} bình luận được xử lý thành công`);
            }
        } catch (error) {
            console.error('Error applying bulk action:', error);
            this.showError('Có lỗi xảy ra khi thực hiện hành động hàng loạt');
        }
    }

    getActionText(action) {
        const actionMap = {
            'approve': 'duyệt',
            'reject': 'từ chối',
            'hide': 'ẩn',
            'delete': 'xóa'
        };
        return actionMap[action] || action;
    }

    updateStats() {
        const total = this.comments.length;
        const pending = this.comments.filter(c => c.status === 'pending').length;
        const approved = this.comments.filter(c => c.status === 'approved').length;
        const reported = this.comments.filter(c => c.report_count > 0).length;
        
        document.getElementById('total-comments').textContent = total;
        document.getElementById('pending-comments').textContent = pending;
        document.getElementById('approved-comments').textContent = approved;
        document.getElementById('reported-comments').textContent = reported;
    }

    updateReportStats() {
        // This can be used to update report-specific stats if needed
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    showError(message) {
        this.showToast(message, 'danger');
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new CommentsModeration();
});
</script>
