<!-- Comments Section -->
<section class="comments-section mt-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="comments-header mb-4">
                    <h3 class="fw-bold mb-3">
                        <i class="bi bi-chat-dots me-2"></i>
                        Bình luận (<span id="comment-count">0</span>)
                    </h3>
                    
                    <!-- Comment Form -->
                    <div class="comment-form-container mb-4">
                        <?php if (\App\Core\Auth::check()): ?>
                            <div class="card">
                                <div class="card-body">
                                    <form id="comment-form" class="comment-form">
                                        <input type="hidden" name="csrf" value="<?= \App\Core\CSRF::token() ?>">
                                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                        <input type="hidden" name="parent_id" value="">
                                        
                                        <div class="mb-3">
                                            <label for="comment-content" class="form-label fw-semibold">
                                                Viết bình luận của bạn
                                            </label>
                                            <textarea 
                                                id="comment-content" 
                                                name="content" 
                                                class="form-control" 
                                                rows="4" 
                                                placeholder="Chia sẻ suy nghĩ của bạn về bài viết này..."
                                                required
                                                maxlength="1000"
                                            ></textarea>
                                            <div class="form-text">
                                                <span id="char-count">0</span>/1000 ký tự
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="comment-tips text-muted small">
                                                <i class="bi bi-info-circle me-1"></i>
                                                Bình luận của bạn sẽ được hiển thị công khai
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-send me-2"></i>Gửi bình luận
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-person-circle display-4 text-muted"></i>
                                    </div>
                                    <h5 class="mb-3">Đăng nhập để bình luận</h5>
                                    <p class="text-muted mb-4">Hãy đăng nhập để chia sẻ suy nghĩ của bạn về bài viết này</p>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="<?= BASE_URL ?>/auth/login" class="btn btn-primary">
                                            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                                        </a>
                                        <a href="<?= BASE_URL ?>/auth/register" class="btn btn-outline-primary">
                                            <i class="bi bi-person-plus me-2"></i>Đăng ký
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Comments List -->
                <div id="comments-container" class="comments-list">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                        <p class="mt-2 text-muted">Đang tải bình luận...</p>
                    </div>
                </div>

                <!-- Load More Button -->
                <div id="load-more-container" class="text-center mt-4" style="display: none;">
                    <button id="load-more-btn" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-down-circle me-2"></i>Tải thêm bình luận
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Comment Template (Hidden) -->
<template id="comment-template">
    <div class="comment-item" data-comment-id="">
        <div class="comment-card">
            <div class="comment-header">
                <div class="comment-author">
                    <div class="author-avatar">
                        <span class="avatar-text"></span>
                    </div>
                    <div class="author-info">
                        <div class="author-name"></div>
                        <div class="comment-meta">
                            <span class="comment-date"></span>
                            <span class="comment-edited" style="display: none;">
                                <i class="bi bi-pencil-square me-1"></i>Đã chỉnh sửa
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="comment-actions">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item reply-btn" href="#">
                                <i class="bi bi-reply me-2"></i>Trả lời
                            </a></li>
                            <li class="edit-comment-item" style="display: none;">
                                <a class="dropdown-item edit-btn" href="#">
                                    <i class="bi bi-pencil-square me-2"></i>Chỉnh sửa
                                </a>
                            </li>
                            <li class="delete-comment-item" style="display: none;">
                                <a class="dropdown-item delete-btn text-danger" href="#">
                                    <i class="bi bi-trash me-2"></i>Xóa
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item report-btn text-warning" href="#">
                                <i class="bi bi-flag me-2"></i>Báo cáo
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="comment-content">
                <div class="comment-text"></div>
                
                <!-- Edit Form (Hidden) -->
                <div class="comment-edit-form" style="display: none;">
                    <form class="edit-form">
                        <input type="hidden" name="csrf" value="<?= \App\Core\CSRF::token() ?>">
                        <input type="hidden" name="comment_id" value="">
                        <textarea name="content" class="form-control" rows="3" maxlength="1000"></textarea>
                        <div class="form-text">
                            <span class="edit-char-count">0</span>/1000 ký tự
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-sm btn-primary me-2">Lưu</button>
                            <button type="button" class="btn btn-sm btn-secondary cancel-edit">Hủy</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="comment-footer">
                <div class="comment-interactions">
                    <button class="btn btn-sm btn-outline-primary like-btn" data-action="like">
                        <i class="bi bi-hand-thumbs-up"></i>
                        <span class="like-count">0</span>
                    </button>
                    <button class="btn btn-sm btn-outline-danger dislike-btn" data-action="dislike">
                        <i class="bi bi-hand-thumbs-down"></i>
                        <span class="dislike-count">0</span>
                    </button>
                </div>
                
                <div class="comment-reply-info">
                    <span class="reply-count" style="display: none;">
                        <i class="bi bi-reply me-1"></i>
                        <span class="reply-count-text">0</span> trả lời
                    </span>
                </div>
            </div>
            
            <!-- Reply Form (Hidden) -->
            <div class="reply-form-container" style="display: none;">
                <div class="reply-form">
                    <form class="reply-comment-form">
                        <input type="hidden" name="csrf" value="<?= \App\Core\CSRF::token() ?>">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <input type="hidden" name="parent_id" value="">
                        
                        <div class="mb-2">
                            <textarea name="content" class="form-control" rows="3" 
                                    placeholder="Viết trả lời..." required maxlength="1000"></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="reply-tips text-muted small">
                                <i class="bi bi-info-circle me-1"></i>
                                Trả lời cho: <span class="reply-to-author"></span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-secondary me-2 cancel-reply">Hủy</button>
                                <button type="submit" class="btn btn-sm btn-primary">Gửi trả lời</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Replies Container -->
            <div class="replies-container">
                <div class="replies-list"></div>
            </div>
        </div>
    </div>
</template>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-flag me-2"></i>Báo cáo bình luận
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="report-form">
                    <input type="hidden" name="csrf" value="<?= \App\Core\CSRF::token() ?>">
                    <input type="hidden" name="comment_id" value="">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lý do báo cáo:</label>
                        <select name="reason" class="form-select" required>
                            <option value="">Chọn lý do...</option>
                            <option value="spam">Spam</option>
                            <option value="inappropriate">Nội dung không phù hợp</option>
                            <option value="harassment">Quấy rối</option>
                            <option value="offensive">Xúc phạm</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="report-description" class="form-label">Mô tả chi tiết (tùy chọn):</label>
                        <textarea id="report-description" name="description" class="form-control" rows="3" 
                                placeholder="Mô tả thêm về vấn đề..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" form="report-form" class="btn btn-warning">
                    <i class="bi bi-flag me-2"></i>Gửi báo cáo
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Comments Section Styles */
.comments-section {
    background: var(--bs-body-bg);
    border-radius: 1rem;
    padding: 2rem 0;
}

.comment-form-container .card {
    border: 2px solid var(--bs-border-color);
    border-radius: 1rem;
    transition: all 0.3s ease;
}

.comment-form-container .card:hover {
    border-color: var(--bs-primary);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.comment-form textarea {
    border-radius: 0.75rem;
    border: 2px solid var(--bs-border-color);
    transition: all 0.3s ease;
}

.comment-form textarea:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
}

/* Comment Item Styles */
.comment-item {
    margin-bottom: 1.5rem;
}

.comment-card {
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
    border-radius: 1rem;
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
}

.comment-card:hover {
    border-color: var(--bs-primary);
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
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
}

.comment-content {
    margin-bottom: 1rem;
}

.comment-text {
    line-height: 1.6;
    color: var(--bs-body-color);
}

.comment-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid var(--bs-border-color);
}

.comment-interactions {
    display: flex;
    gap: 0.5rem;
}

.like-btn, .dislike-btn {
    border-radius: 20px;
    padding: 0.25rem 0.75rem;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.like-btn.active {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}

.dislike-btn.active {
    background-color: var(--bs-danger);
    border-color: var(--bs-danger);
    color: white;
}

/* Nested Comments */
.replies-container {
    margin-top: 1rem;
    margin-left: 2rem;
    padding-left: 1rem;
    border-left: 3px solid var(--bs-border-color);
}

.replies-container .comment-card {
    background: rgba(var(--bs-primary-rgb), 0.05);
    border-color: rgba(var(--bs-primary-rgb), 0.2);
}

.reply-form-container {
    margin-top: 1rem;
    padding: 1rem;
    background: rgba(var(--bs-primary-rgb), 0.05);
    border-radius: 0.75rem;
    border: 1px solid rgba(var(--bs-primary-rgb), 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
    .comments-section {
        padding: 1rem 0;
    }
    
    .comment-card {
        padding: 1rem;
    }
    
    .comment-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .comment-actions {
        align-self: flex-end;
    }
    
    .replies-container {
        margin-left: 1rem;
        padding-left: 0.5rem;
    }
    
    .comment-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}

/* Dark Mode Support */
[data-bs-theme="dark"] .comment-card {
    background: var(--bs-gray-800);
    border-color: var(--bs-gray-700);
}

[data-bs-theme="dark"] .comment-card:hover {
    border-color: var(--bs-primary);
}

[data-bs-theme="dark"] .replies-container .comment-card {
    background: rgba(var(--bs-primary-rgb), 0.1);
}

[data-bs-theme="dark"] .reply-form-container {
    background: rgba(var(--bs-primary-rgb), 0.1);
    border-color: rgba(var(--bs-primary-rgb), 0.3);
}

/* Animation */
.comment-item {
    animation: slideInUp 0.5s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Loading States */
.comment-form button[type="submit"]:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.comment-form button[type="submit"] .spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>

<!-- Initialize user data for JavaScript -->
<script>
    // Set user data before loading the comment script
    <?php if (\App\Core\Auth::check()): ?>
        window.currentUserId = <?= \App\Core\Auth::user()['id'] ?>;
        window.currentUserName = "<?= htmlspecialchars(\App\Core\Auth::user()['name']) ?>";
        window.isAdmin = <?= \App\Core\Auth::isAdmin() ? 'true' : 'false' ?>;
    <?php else: ?>
        window.currentUserId = null;
        window.currentUserName = null;
        window.isAdmin = false;
    <?php endif; ?>
    window.baseUrl = '<?= BASE_URL ?>';
    window.csrfToken = '<?= \App\Core\CSRF::token() ?>';
</script>

<!-- Load comment system script -->
<script src="<?= BASE_URL ?>/js/comments.js"></script>