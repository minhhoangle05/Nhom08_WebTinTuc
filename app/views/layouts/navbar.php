<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/">Tin tức</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $_SERVER['REQUEST_URI'] == BASE_URL . '/' ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>/">Trang chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/articles') !== false ? 'active' : '' ?>" 
                       href="<?= BASE_URL ?>/articles">Tin tức</a>
                </li>
                <?php if (App\Core\Auth::check()): ?>
                    <?php if (App\Core\Auth::isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard">Quản trị</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/articles/create">Viết bài</a>
                    </li>
                <?php endif; ?>
            </ul>

            <form class="d-flex me-3" action="<?= BASE_URL ?>/articles/search" method="GET">
                <input class="form-control me-2" type="search" name="q" placeholder="Tìm kiếm..." 
                       value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button class="btn btn-outline-light" type="submit">Tìm</button>
            </form>

            <ul class="navbar-nav">
                <?php if (App\Core\Auth::check()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown">
                            <?= htmlspecialchars(App\Core\Auth::user()['name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile">Tài khoản</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/articles/drafts">Bài nháp</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/auth/logout">Đăng xuất</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/auth/login">Đăng nhập</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/auth/register">Đăng ký</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>