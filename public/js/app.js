document.addEventListener('DOMContentLoaded', function () {
  // Theme toggle
  const root = document.documentElement;
  const toggle = document.getElementById('themeToggle');
  const saved = localStorage.getItem('theme');
  if (saved) document.documentElement.setAttribute('data-bs-theme', saved);
  const updateThemeIcon = () => {
    if (!toggle) return;
    const isDark = (root.getAttribute('data-bs-theme') || 'light') === 'dark';
    toggle.innerHTML = isDark ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon"></i>';
  };
  updateThemeIcon();
  if (toggle) {
    toggle.addEventListener('click', () => {
      const current = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-bs-theme', current);
      localStorage.setItem('theme', current);
      updateThemeIcon();
    });
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
  document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right, .scale-in').forEach(el => observer.observe(el));

  // Page transition (fade between pages)
  const body = document.body;
  body.style.opacity = '0';
  requestAnimationFrame(() => {
    body.style.transition = 'opacity .35s ease';
    body.style.opacity = '1';
  });
  document.querySelectorAll('a[href]')
    .forEach(a => {
      const href = a.getAttribute('href');
      if (!href || href.startsWith('#') || a.target === '_blank') return;
      a.addEventListener('click', (e) => {
        const url = new URL(href, window.location.origin);
        // Ignore external links
        if (url.origin !== window.location.origin) return;
        e.preventDefault();
        body.style.opacity = '0';
        setTimeout(() => { window.location.href = url.href; }, 200);
      });
    });

  // Live clock (updates element with id=currentTime if present)
  const clockEl = document.getElementById('currentTime');
  if (clockEl) {
    const updateClock = () => {
      const now = new Date();
      clockEl.textContent = now.toLocaleString();
    };
    updateClock();
    setInterval(updateClock, 1000);
  }
});


