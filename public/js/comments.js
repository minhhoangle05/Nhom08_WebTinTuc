// Comment System - public/js/comments.js
document.addEventListener('DOMContentLoaded', function() {
    const articleId = document.querySelector('input[name="article_id"]')?.value;
    const commentsContainer = document.getElementById('comments-container');
    const commentForm = document.getElementById('comment-form');
    const commentContent = document.getElementById('comment-content');
    const charCount = document.getElementById('char-count');
    const commentCountEl = document.getElementById('comment-count');
    const reportModal = new bootstrap.Modal(document.getElementById('reportModal'));
     // ✅ Ngăn chặn script bị chạy lại nhiều lần
     if (window.__COMMENT_SCRIPT_LOADED__) return;
     window.__COMMENT_SCRIPT_LOADED__ = true;
    // Get user data from global window object (set by PHP)
    const currentUserId = window.currentUserId;
    const currentUserName = window.currentUserName;
    const isAdmin = window.isAdmin;
    const BASE_URL = window.baseUrl;
    const CSRF_TOKEN = window.csrfToken;

    let isSubmitting = false; // ✅ flag ngăn gửi trùng
    let isLoadingComments = false; // ✅ flag ngăn load chồng

    // Character counter for main comment form
    if (commentContent && charCount) {
        commentContent.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }

    // Load comments on page load
    if (articleId) {
        loadComments();
    }

    // Submit main comment form
    if (commentForm) {
        commentForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (isSubmitting) return; // ngăn gửi trùng
            isSubmitting = true;

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang gửi...';

            const formData = new FormData(this);

            try {
                const response = await fetch(`${BASE_URL}/comment/create`, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    commentContent.value = '';
                    charCount.textContent = '0';
                    showToast('success', data.message || 'Bình luận đã được thêm thành công');

                    // ✅ Chỉ load 1 lần duy nhất
                    await loadComments();
                } else {
                    showToast('error', data.error || 'Có lỗi xảy ra');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('error', 'Có lỗi xảy ra khi gửi bình luận');
            } finally {
                isSubmitting = false;
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // Load comments function
    async function loadComments() {
        if (isLoadingComments) return;
        isLoadingComments = true;

        try {
            const response = await fetch(`${BASE_URL}/comment/getComments?article_id=${articleId}`);
            const data = await response.json();

            if (data.success) {
                renderComments(data.comments);
                updateCommentCount(data.comments);
            } else {
                commentsContainer.innerHTML = '<div class="alert alert-warning">Không thể tải bình luận</div>';
            }
        } catch (error) {
            console.error('Error loading comments:', error);
            commentsContainer.innerHTML = '<div class="alert alert-danger">Có lỗi xảy ra khi tải bình luận</div>';
        } finally {
            isLoadingComments = false;
        }
    }

    // Render comments
    function renderComments(comments) {
        if (!comments || comments.length === 0) {
            commentsContainer.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-chat-text display-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">Chưa có bình luận nào</h5>
                    <p class="text-muted">Hãy là người đầu tiên bình luận về bài viết này</p>
                </div>
            `;
            return;
        }
        
        commentsContainer.innerHTML = '';
        comments.forEach(comment => {
            const commentElement = createCommentElement(comment);
            commentsContainer.appendChild(commentElement);
        });
    }

    // Create comment element
    function createCommentElement(comment, isReply = false) {
        const template = document.getElementById('comment-template');
        const clone = template.content.cloneNode(true);
        
        const commentItem = clone.querySelector('.comment-item');
        commentItem.dataset.commentId = comment.id;
        
        // Set avatar
        const avatarText = clone.querySelector('.avatar-text');
        avatarText.textContent = getInitials(comment.user_name);
        
        // Set author name
        clone.querySelector('.author-name').textContent = comment.user_name;
        
        // Set date
        clone.querySelector('.comment-date').textContent = formatDate(comment.created_at);
        
        // Show edited badge if edited
        if (comment.is_edited) {
            clone.querySelector('.comment-edited').style.display = 'inline';
        }
        
        // Set content
        clone.querySelector('.comment-text').textContent = comment.content;
        
        // Set like/dislike counts
        const likeBtn = clone.querySelector('.like-btn');
        const dislikeBtn = clone.querySelector('.dislike-btn');
        const likeCount = clone.querySelector('.like-count');
        const dislikeCount = clone.querySelector('.dislike-count');
        
        likeCount.textContent = comment.like_count || 0;
        dislikeCount.textContent = comment.dislike_count || 0;
        
        // Highlight user's action
        if (comment.user_action === 'like') {
            likeBtn.classList.add('active');
        } else if (comment.user_action === 'dislike') {
            dislikeBtn.classList.add('active');
        }
        
        // Show/hide action buttons based on permissions
        if (currentUserId) {
            if (currentUserId === comment.user_id || isAdmin) {
                clone.querySelector('.edit-comment-item').style.display = 'block';
                clone.querySelector('.delete-comment-item').style.display = 'block';
            }
        }
        
        // Set up event listeners
        setupCommentEventListeners(commentItem, comment);
        
        // Render replies
        if (comment.replies && comment.replies.length > 0) {
            const repliesContainer = clone.querySelector('.replies-list');
            const replyCount = clone.querySelector('.reply-count');
            const replyCountText = clone.querySelector('.reply-count-text');
            
            replyCount.style.display = 'inline';
            replyCountText.textContent = comment.replies.length;
            
            comment.replies.forEach(reply => {
                const replyElement = createCommentElement(reply, true);
                repliesContainer.appendChild(replyElement);
            });
        }
        
        return commentItem;
    }

    // Setup event listeners for comment
    function setupCommentEventListeners(element, comment) {
        // Reply button
        const replyBtn = element.querySelector('.reply-btn');
        replyBtn?.addEventListener('click', function(e) {
            e.preventDefault();
            if (!currentUserId) {
                showToast('warning', 'Vui lòng đăng nhập để trả lời');
                return;
            }
            toggleReplyForm(element, comment);
        });
        
        // Edit button
        const editBtn = element.querySelector('.edit-btn');
        editBtn?.addEventListener('click', function(e) {
            e.preventDefault();
            toggleEditForm(element, comment);
        });
        
        // Delete button
        const deleteBtn = element.querySelector('.delete-btn');
        deleteBtn?.addEventListener('click', function(e) {
            e.preventDefault();
            deleteComment(comment.id);
        });
        
        // Report button
        const reportBtn = element.querySelector('.report-btn');
        reportBtn?.addEventListener('click', function(e) {
            e.preventDefault();
            if (!currentUserId) {
                showToast('warning', 'Vui lòng đăng nhập để báo cáo');
                return;
            }
            showReportModal(comment.id);
        });
        
        // Like/Dislike buttons
        const likeBtn = element.querySelector('.like-btn');
        const dislikeBtn = element.querySelector('.dislike-btn');
        
        likeBtn?.addEventListener('click', function() {
            if (!currentUserId) {
                showToast('warning', 'Vui lòng đăng nhập để thích bình luận');
                return;
            }
            toggleLike(comment.id, 'like', element);
        });
        
        dislikeBtn?.addEventListener('click', function() {
            if (!currentUserId) {
                showToast('warning', 'Vui lòng đăng nhập để không thích bình luận');
                return;
            }
            toggleLike(comment.id, 'dislike', element);
        });
    }

    // Toggle reply form
    function toggleReplyForm(element, comment) {
        const replyFormContainer = element.querySelector('.reply-form-container');
        const replyForm = element.querySelector('.reply-comment-form');
        
        // Hide all other reply forms
        document.querySelectorAll('.reply-form-container').forEach(form => {
            if (form !== replyFormContainer) {
                form.style.display = 'none';
            }
        });
        
        // Toggle current form
        if (replyFormContainer.style.display === 'none' || !replyFormContainer.style.display) {
            replyFormContainer.style.display = 'block';
            replyForm.querySelector('input[name="parent_id"]').value = comment.id;
            replyForm.querySelector('.reply-to-author').textContent = comment.user_name;
            
            // Setup cancel button
            const cancelBtn = replyForm.querySelector('.cancel-reply');
            cancelBtn.addEventListener('click', function() {
                replyFormContainer.style.display = 'none';
                replyForm.reset();
            });
            
            // Setup submit
            replyForm.onsubmit = async function(e) {
                e.preventDefault();
                await submitReply(this, element);
            };
        } else {
            replyFormContainer.style.display = 'none';
            replyForm.reset();
        }
    }

    // Submit reply
    async function submitReply(form, parentElement) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch(`${BASE_URL}/comment/create`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('success', 'Trả lời đã được thêm');
                form.reset();
                parentElement.querySelector('.reply-form-container').style.display = 'none';
                await loadComments();
            } else {
                showToast('error', data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', 'Có lỗi xảy ra');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    // Toggle edit form
    function toggleEditForm(element, comment) {
        const commentText = element.querySelector('.comment-text');
        const editForm = element.querySelector('.comment-edit-form');
        const textarea = editForm.querySelector('textarea[name="content"]');
        const charCounter = editForm.querySelector('.edit-char-count');
        
        if (editForm.style.display === 'none' || !editForm.style.display) {
            commentText.style.display = 'none';
            editForm.style.display = 'block';
            textarea.value = comment.content;
            charCounter.textContent = comment.content.length;
            
            // Character counter
            textarea.addEventListener('input', function() {
                charCounter.textContent = this.value.length;
            });
            
            // Set comment ID
            editForm.querySelector('input[name="comment_id"]').value = comment.id;
            
            // Cancel button
            const cancelBtn = editForm.querySelector('.cancel-edit');
            cancelBtn.addEventListener('click', function() {
                commentText.style.display = 'block';
                editForm.style.display = 'none';
            });
            
            // Submit edit
            const form = editForm.querySelector('.edit-form');
            form.onsubmit = async function(e) {
                e.preventDefault();
                await submitEdit(this, element);
            };
        } else {
            commentText.style.display = 'block';
            editForm.style.display = 'none';
        }
    }

    // Submit edit
    async function submitEdit(form, element) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch(`${BASE_URL}/comment/update`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('success', data.message);
                await loadComments();
            } else {
                showToast('error', data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', 'Có lỗi xảy ra');
        } finally {
            submitBtn.disabled = false;
        }
    }

    // Delete comment
    async function deleteComment(commentId) {
        if (!confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('csrf', CSRF_TOKEN);
        formData.append('comment_id', commentId);
        
        try {
            const response = await fetch(`${BASE_URL}/comment/delete`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast('success', data.message);
                await loadComments();
            } else {
                showToast('error', data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', 'Có lỗi xảy ra');
        }
    }

    // Toggle like/dislike
// Toggle like/dislike (mạnh hơn, xử lý lỗi rõ ràng)
async function toggleLike(commentId, action, element) {
    const likeBtn = element.querySelector('.like-btn');
    const dislikeBtn = element.querySelector('.dislike-btn');
    const likeCountEl = element.querySelector('.like-count');
    const dislikeCountEl = element.querySelector('.dislike-count');

    // tránh double click
    if (likeBtn) likeBtn.disabled = true;
    if (dislikeBtn) dislikeBtn.disabled = true;

    // Prepare payload (FormData đảm bảo gửi csrf)
    const formData = new FormData();
    // ưu tiên lấy csrf từ hidden input trong DOM nếu có (mới nhất)
    const localCsrf = element.querySelector('input[name="csrf"]')?.value || CSRF_TOKEN || '';
    formData.append('csrf', localCsrf);
    formData.append('comment_id', commentId);
    formData.append('action', action);

    try {
        const resp = await fetch(`${BASE_URL}/comment/toggleLike`, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });

        // Nếu server trả không phải JSON hoặc status lỗi -> đọc as text để debug
        const text = await resp.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('toggleLike: non-json response:', text);
            showToast('error', 'Lỗi máy chủ: phản hồi không hợp lệ');
            return;
        }

        if (!resp.ok || !data.success) {
            console.warn('toggleLike error response:', data);
            showToast('error', data.error || 'Có lỗi xảy ra khi xử lý yêu cầu');
            return;
        }

        // Thành công -> cập nhật giao diện
        if (likeCountEl) likeCountEl.textContent = data.likes ?? 0;
        if (dislikeCountEl) dislikeCountEl.textContent = data.dislikes ?? 0;

        likeBtn?.classList.toggle('active', data.action === 'like');
        dislikeBtn?.classList.toggle('active', data.action === 'dislike');

    } catch (err) {
        console.error('toggleLike fetch error:', err);
        showToast('error', 'Không thể kết nối tới máy chủ');
    } finally {
        if (likeBtn) likeBtn.disabled = false;
        if (dislikeBtn) dislikeBtn.disabled = false;
    }
}


    // Show report modal
    function showReportModal(commentId) {
        const reportForm = document.getElementById('report-form');
        reportForm.querySelector('input[name="comment_id"]').value = commentId;
        reportForm.reset();
        
        reportForm.onsubmit = async function(e) {
            e.preventDefault();
            await submitReport(this);
        };
        
        reportModal.show();
    }

    // Submit report
    async function submitReport(form) {
        const formData = new FormData(form);
        // ensure csrf exists
        if (!formData.get('csrf')) formData.append('csrf', CSRF_TOKEN);
    
        try {
            const resp = await fetch(`${BASE_URL}/comment/report`, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
    
            const text = await resp.text();
            let data;
            try { data = JSON.parse(text); } catch (e) {
                console.error('submitReport non-json:', text);
                showToast('error', 'Lỗi máy chủ (phản hồi không hợp lệ)');
                return;
            }
    
            if (!resp.ok || !data.success) {
                showToast('error', data.error || 'Không thể gửi báo cáo');
                return;
            }
    
            showToast('success', data.message || 'Báo cáo đã được gửi');
            reportModal.hide();
            form.reset();
        } catch (err) {
            console.error('submitReport error:', err);
            showToast('error', 'Không thể kết nối tới máy chủ');
        }
    }
    

    // Helper functions
    function getInitials(name) {
        return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
    }

    function formatDate(dateString) {
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
        
        return date.toLocaleDateString('vi-VN');
    }

    function updateCommentCount(comments) {
        let count = 0;
        
        function countComments(commentList) {
            commentList.forEach(comment => {
                count++;
                if (comment.replies && comment.replies.length > 0) {
                    countComments(comment.replies);
                }
            });
        }
        
        countComments(comments);
        commentCountEl.textContent = count;
    }

    function showToast(type, message) {
        // Create toast element
        const toastHtml = `
            <div class="toast align-items-center text-bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'warning'} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'exclamation-triangle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        // Get or create toast container
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        // Add toast
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();
        
        // Remove toast after hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }
});