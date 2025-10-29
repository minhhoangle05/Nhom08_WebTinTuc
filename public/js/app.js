document.addEventListener('DOMContentLoaded', function () {
    // Initialize all components
    initThemeToggle();
    initFormValidation();
    initScrollAnimations();
    initBackToTop();
    initLoadingIndicators();
    initLiveTimestamp();
    
    // Theme toggle initialization
    function initThemeToggle() {
        const root = document.documentElement;
        const toggle = document.getElementById('themeToggle');
        const saved = localStorage.getItem('theme');
        
        if (saved) {
            root.setAttribute('data-bs-theme', saved);
        }
        
        function updateThemeIcon() {
            if (!toggle) return;
            const isDark = (root.getAttribute('data-bs-theme') || 'light') === 'dark';
            toggle.innerHTML = isDark ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon"></i>';
            toggle.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
        }
        
        updateThemeIcon();
        
        if (toggle) {
            toggle.addEventListener('click', () => {
                const current = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-bs-theme', current);
                localStorage.setItem('theme', current);
                updateThemeIcon();
            });
        }
    }

  // Simple intersection observer for fade-in animations
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });
    // Khởi tạo nút Lên đầu trang
    function initBackToTop() {
        const button = document.createElement('button');
        button.className = 'back-to-top';
        button.innerHTML = '<i class="bi bi-arrow-up"></i>';
        button.setAttribute('aria-label', 'Lên đầu trang');
        document.body.appendChild(button);
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                button.classList.add('visible');
            } else {
                button.classList.remove('visible');
            }
        });
        
        button.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Biểu thị đang tải
    function initLoadingIndicators() {
        const spinner = document.createElement('div');
        spinner.className = 'loading-spinner d-none';
        spinner.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
        `;
        document.body.appendChild(spinner);
        
        // Expose global loading functions
        window.showLoading = function() {
            spinner.classList.remove('d-none');
        };
        
        window.hideLoading = function() {
            spinner.classList.add('d-none');
        };
    }
    
    // Live timestamp updates
    function initLiveTimestamp() {
        const timestamps = document.querySelectorAll('[data-timestamp]');
        if (timestamps.length === 0) return;
        
        function updateTimestamps() {
            timestamps.forEach(el => {
                const timestamp = el.dataset.timestamp;
                const date = new Date(timestamp * 1000);
                el.textContent = formatTimeAgo(date);
            });
        }
        
        function formatTimeAgo(date) {
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            
        if (seconds < 60) return 'Vừa xong';
            
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return `${minutes} phút trước`;
            
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `${hours} giờ trước`;
            
        const days = Math.floor(hours / 24);
        if (days < 30) return `${days} ngày trước`;
            
        const months = Math.floor(days / 30);
        if (months < 12) return `${months} tháng trước`;            return `${Math.floor(months / 12)} năm trước`;
        }
        
        updateTimestamps();
        setInterval(updateTimestamps, 60000); // Cập nhật mỗi phút
    }
    
    // Helper function for toast notifications
    function showToast(message, type = 'info') {
        const toastContainer = document.querySelector('.toast-container') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast bg-${type} text-white`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="toast-header bg-${type} text-white">
                <strong class="me-auto">Thông báo</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Đóng"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 5000
        });
        
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
    
    function createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(container);
        return container;
    }
});


