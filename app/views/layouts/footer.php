<style>
	/* Footer theming with clear contrast in both light and dark modes */
	[data-bs-theme="light"] .site-footer { background-color: var(--bs-gray-400); color: var(--bs-gray-800); }
	[data-bs-theme="dark"] .site-footer { background-color: #0f172a; /* slate-900 */ color: var(--bs-gray-200); }
	.site-footer a { text-decoration: none; }
	[data-bs-theme="light"] .site-footer a { color: #0d6efd; }
	[data-bs-theme="dark"] .site-footer a { color: #9ec5fe; }
	.site-footer a:hover { opacity: .9; }
	.site-footer .footer-heading { font-weight: 600; letter-spacing: .2px; }
	.site-footer .footer-muted { opacity: .8; }
	.site-footer .divider { border-top: 1px solid var(--bs-border-color); }
</style>

<footer class="site-footer mt-auto pt-5">
	<div class="container">
		<div class="row gy-4">
			<div class="col-12 col-lg-4">
				<div class="d-flex align-items-center gap-2 mb-2">
					<i class="bi bi-journal-richtext fs-4"></i>
					<strong>ArticleHub</strong>
				</div>
				<p class="mb-3 footer-muted">Nền tảng đọc và chia sẻ bài viết chất lượng, nơi kiến thức được lan tỏa mỗi ngày.</p>
				<div class="d-flex align-items-center gap-3">
					<a aria-label="Facebook" href="#"><i class="bi bi-facebook fs-5"></i></a>
					<a aria-label="Twitter / X" href="#"><i class="bi bi-twitter-x fs-5"></i></a>
					<a aria-label="Github" href="#"><i class="bi bi-github fs-5"></i></a>
				</div>
			</div>

			<div class="col-6 col-lg-4">
				<div class="footer-heading mb-2">Điều hướng</div>
				<ul class="list-unstyled m-0 d-grid gap-2">
					<li><a href="<?= BASE_URL ?>/">Trang chủ</a></li>
					<li><a href="<?= BASE_URL ?>/articles">Bài viết</a></li>
					<li><a href="<?= BASE_URL ?>/articles/search">Tìm kiếm</a></li>
					<li><a href="<?= BASE_URL ?>/articles/create">Viết bài</a></li>
				</ul>
			</div>

			<div class="col-6 col-lg-4">
				<div class="footer-heading mb-2">Liên hệ</div>
				<ul class="list-unstyled m-0 d-grid gap-2 footer-muted">
					<li>Email: contact@articlehub.local</li>
					<li>Hỗ trợ: support@articlehub.local</li>
				</ul>
			</div>
		</div>

		<div class="divider mt-4 pt-4"></div>
		<div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between py-3 gap-2">
			<div class="footer-muted">&copy; <?= date('Y') ?> ArticleHub. Tất cả các quyền được bảo lưu.</div>
			<div class="d-flex align-items-center gap-3 footer-muted small">
				<a href="#">Điều khoản</a>
				<a href="#">Quyền riêng tư</a>
				<a href="#">Cookie</a>
			</div>
		</div>
	</div>
</footer>

