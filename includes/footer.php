<?php
declare(strict_types=1);

$footerUser = current_user();
?>
    <footer class="app-footer mt-5">
        <div class="container-xxl">
            <div class="glass-panel footer-shell px-4 py-4">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-5">
                        <h3 class="h5 mb-3">webpc</h3>
                        <p class="text-soft mb-3">
                            Mô hình cửa hàng theo hướng thực chiến: danh mục, chi tiết sản phẩm, giỏ hàng,
                            thanh toán, tài khoản, chế độ tối, xác thực và khu vực quản trị thêm sản phẩm.
                        </p>
                        <form action="" method="post" class="row g-2 align-items-center">
                            <input type="hidden" name="action" value="subscribe_newsletter">
                            <input type="hidden" name="redirect" value="<?= h(current_request_path()) ?>">
                            <div class="col-sm-8">
                                <input type="email" name="email" class="form-control glass-input" placeholder="Email nhận cập nhật">
                            </div>
                            <div class="col-sm-4 d-grid">
                                <button class="btn btn-brand" type="submit">Đăng ký</button>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-3">
                        <h3 class="h6 mb-3">Danh mục</h3>
                        <ul class="list-unstyled footer-list m-0">
                            <li><a href="products.php?category=office">PC văn phòng</a></li>
                            <li><a href="products.php?category=gaming">PC gaming</a></li>
                            <li><a href="products.php?category=gear">Thiết bị</a></li>
                            <li><a href="services.php">Dịch vụ</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-4">
                        <h3 class="h6 mb-3">Tài khoản</h3>
                        <ul class="list-unstyled footer-list m-0">
                            <?php if ($footerUser !== null): ?>
                                <li><a href="account.php">Hồ sơ và avatar</a></li>
                                <li><a href="cart.php">Thanh toán</a></li>
                                <?php if (is_admin()): ?>
                                    <li><a href="add-product.php">Quản trị thêm sản phẩm</a></li>
                                <?php endif; ?>
                            <?php else: ?>
                                <li><a href="login.php">Đăng nhập / Đăng ký</a></li>
                                <li><a href="cart.php">Xem giỏ hàng</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="floating-dock-wrap">
            <div class="dock-bar glass-panel">
                <a class="dock-link <?= is_active_nav($activeNav, 'home') ? 'is-active' : '' ?>" href="index.php">
                    <i class="bi bi-house-door-fill"></i>
                    <span>Trang chủ</span>
                </a>
                <a class="dock-link <?= is_active_nav($activeNav, 'products') ? 'is-active' : '' ?>" href="products.php">
                    <i class="bi bi-grid-fill"></i>
                    <span>Cửa hàng</span>
                </a>
                <a class="dock-link <?= is_active_nav($activeNav, 'services') ? 'is-active' : '' ?>" href="services.php">
                    <i class="bi bi-broadcast"></i>
                    <span>Dịch vụ</span>
                </a>
                <a class="dock-link <?= is_active_nav($activeNav, 'cart') ? 'is-active' : '' ?>" href="cart.php">
                    <i class="bi bi-bag-check-fill"></i>
                    <span>Giỏ hàng</span>
                </a>
                <a class="dock-link <?= is_active_nav($activeNav, 'account') ? 'is-active' : '' ?>" href="<?= $footerUser !== null ? 'account.php' : 'login.php' ?>">
                    <i class="bi bi-person-circle"></i>
                    <span><?= $footerUser !== null ? 'Tài khoản' : 'Đăng nhập' ?></span>
                </a>
                <?php if (is_admin()): ?>
                    <a class="dock-link <?= is_active_nav($activeNav, 'admin') ? 'is-active' : '' ?>" href="add-product.php">
                        <i class="bi bi-plus-square-fill"></i>
                        <span>Quản trị</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="assets/js/main.js"></script>
</main>
</body>
</html>
