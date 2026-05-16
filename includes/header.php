<?php
declare(strict_types=1);

$categories = $categories ?? site_categories();
$activeNav = $activeNav ?? 'home';
$pageTitle = $pageTitle ?? null;
$flashes = pull_flashes();
$cartCount = array_sum(cart());
$user = current_user();
$searchValue = trim((string) ($_GET['q'] ?? ''));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h(app_title($pageTitle)) ?></title>
    <meta name="description" content="ĐộPICI - website bán thiết bị máy tính với giao diện liquid glass, dark mode, đăng nhập, tài khoản, giỏ hàng và khu vực quản trị sản phẩm.">
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('webpc-theme');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = saved || (prefersDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            } catch (error) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        }());
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<header class="app-header sticky-top">
    <div class="container-xxl">
        <div class="header-shell glass-panel">
            <div class="header-layout">
                <a class="brand-mark" href="index.php">
                    <span class="brand-box">
                        <img src="assets/img/logo-dopici.svg" alt="Logo ĐộPICI">
                    </span>
                    <span class="brand-copy">
                        <span class="brand-name">ĐộPICI</span>
                        <span class="brand-subtitle">PC, thiết bị, dịch vụ</span>
                    </span>
                </a>

                <form action="products.php" method="get" class="search-shell">
                    <i class="bi bi-search text-soft"></i>
                    <input
                        type="search"
                        class="form-control form-control-lg glass-input border-0"
                        name="q"
                        value="<?= h($searchValue) ?>"
                        placeholder="Tìm cấu hình, màn hình, GPU, thiết bị..."
                    >
                    <button class="btn btn-brand btn-lg px-4" type="submit">Tìm</button>
                </form>

                <div class="header-actions-cluster">
                    <button class="quick-pill quick-pill-icon" type="button" data-theme-toggle aria-label="Đổi giao diện">
                        <i class="bi bi-moon-stars-fill" data-theme-icon></i>
                        <span class="fw-semibold text-main d-none d-xl-inline">Giao diện</span>
                    </button>

                    <?php if (is_admin()): ?>
                        <a class="quick-pill quick-pill-strong" href="add-product.php">
                            <i class="bi bi-plus-square-fill fs-5"></i>
                            <span class="fw-semibold text-main">Quản trị</span>
                        </a>
                    <?php endif; ?>

                    <a class="quick-pill" href="cart.php">
                        <i class="bi bi-bag-check fs-5"></i>
                        <span class="fw-semibold text-main">Giỏ hàng</span>
                        <span class="badge rounded-pill glass-badge"><?= (int) $cartCount ?></span>
                    </a>

                    <?php if ($user !== null): ?>
                        <a class="quick-pill account-pill" href="account.php">
                            <?php if (trim((string) $user['avatar_url']) !== ''): ?>
                                <img class="avatar-pill is-image" src="<?= h((string) $user['avatar_url']) ?>" alt="<?= h((string) $user['full_name']) ?>">
                            <?php else: ?>
                                <span class="avatar-pill"><?= h((string) $user['avatar_label']) ?></span>
                            <?php endif; ?>
                            <span class="fw-semibold text-main account-name"><?= h((string) $user['full_name']) ?></span>
                        </a>
                    <?php else: ?>
                        <a class="quick-pill" href="login.php">
                            <i class="bi bi-person-circle fs-5"></i>
                            <span class="fw-semibold text-main">Đăng nhập</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>
<main class="pb-5">
    <div class="container-xxl pt-3">
        <?php foreach ($flashes as $flash): ?>
            <div class="alert alert-<?= h($flash['type'] === 'danger' ? 'danger' : 'success') ?> glass-alert border-0 shadow-sm" role="alert">
                <?= h($flash['message']) ?>
            </div>
        <?php endforeach; ?>
    </div>
