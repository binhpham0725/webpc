<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$next = safe_redirect_path((string) ($_GET['next'] ?? 'account.php'), 'account.php');
if (is_logged_in()) {
    redirect_to($next);
}

$pageTitle = 'Đăng nhập';
$activeNav = 'account';
$categories = site_categories();
$mode = ($_GET['mode'] ?? 'login') === 'register' ? 'register' : 'login';
$loginState = pull_form_state('login');
$registerState = pull_form_state('register');

include __DIR__ . '/includes/header.php';
?>

<section class="section-space">
    <div class="container-xxl">
        <div class="auth-grid">
            <div class="glass-panel p-4 p-lg-5">
                <p class="eyebrow mb-2">xác thực</p>
                <h1 class="hero-title mb-3">Đăng nhập, đăng ký và mở khóa khu vực quản trị.</h1>
                <p class="auth-note mb-4">
                    Chỉ tài khoản admin mới được thêm sản phẩm. Tài khoản khách có hồ sơ, avatar,
                    phương thức thanh toán, lịch sử đơn hàng và yêu cầu dịch vụ.
                </p>

                <ul class="auth-list">
                    <li><strong>Tài khoản quản trị demo:</strong> admin@webpc.local / Admin@12345</li>
                    <li><strong>Chế độ tối:</strong> đổi ngay trên header, được nhớ lại sau khi tải lại trang.</li>
                    <li><strong>Ảnh đại diện:</strong> đổi trực tiếp bằng URL ảnh trong trang tài khoản.</li>
                </ul>
            </div>

            <div class="auth-shell">
                <div class="auth-tabs mb-4">
                    <a class="auth-tab-link <?= $mode === 'login' ? 'is-active' : '' ?>" href="login.php?mode=login&next=<?= urlencode($next) ?>">Đăng nhập</a>
                    <a class="auth-tab-link <?= $mode === 'register' ? 'is-active' : '' ?>" href="login.php?mode=register&next=<?= urlencode($next) ?>">Đăng ký</a>
                </div>

                <?php if ($mode === 'login'): ?>
                    <div class="auth-panel">
                        <p class="eyebrow mb-2">đăng nhập</p>
                        <h2 class="h3 mb-3">Đăng nhập tài khoản</h2>
                        <form action="" method="post" class="row g-3">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="next" value="<?= h($next) ?>">
                            <div class="col-12">
                                <label class="form-label" for="login_email">Email</label>
                                <input id="login_email" name="email" type="email" class="form-control glass-input <?= field_error($loginState, 'email') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($loginState, 'email')) ?>">
                                <?php if (field_error($loginState, 'email') !== ''): ?><span class="field-error"><?= h(field_error($loginState, 'email')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="login_password">Mật khẩu</label>
                                <input id="login_password" name="password" type="password" class="form-control glass-input <?= field_error($loginState, 'password') !== '' ? 'is-invalid-soft' : '' ?>">
                                <?php if (field_error($loginState, 'password') !== ''): ?><span class="field-error"><?= h(field_error($loginState, 'password')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-12 d-grid">
                                <button class="btn btn-brand btn-lg" type="submit">Đăng nhập</button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="auth-panel">
                        <p class="eyebrow mb-2">đăng ký</p>
                        <h2 class="h3 mb-3">Tạo tài khoản khách hàng</h2>
                        <form action="" method="post" class="row g-3">
                            <input type="hidden" name="action" value="register">
                            <input type="hidden" name="next" value="<?= h($next) ?>">
                            <div class="col-12">
                                <label class="form-label" for="register_name">Họ tên</label>
                                <input id="register_name" name="full_name" class="form-control glass-input <?= field_error($registerState, 'full_name') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($registerState, 'full_name')) ?>">
                                <?php if (field_error($registerState, 'full_name') !== ''): ?><span class="field-error"><?= h(field_error($registerState, 'full_name')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="register_email">Email</label>
                                <input id="register_email" name="email" type="email" class="form-control glass-input <?= field_error($registerState, 'email') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($registerState, 'email')) ?>">
                                <?php if (field_error($registerState, 'email') !== ''): ?><span class="field-error"><?= h(field_error($registerState, 'email')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="register_password">Mật khẩu</label>
                                <input id="register_password" name="password" type="password" class="form-control glass-input <?= field_error($registerState, 'password') !== '' ? 'is-invalid-soft' : '' ?>">
                                <?php if (field_error($registerState, 'password') !== ''): ?><span class="field-error"><?= h(field_error($registerState, 'password')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="register_password_confirm">Nhập lại mật khẩu</label>
                                <input id="register_password_confirm" name="password_confirm" type="password" class="form-control glass-input <?= field_error($registerState, 'password_confirm') !== '' ? 'is-invalid-soft' : '' ?>">
                                <?php if (field_error($registerState, 'password_confirm') !== ''): ?><span class="field-error"><?= h(field_error($registerState, 'password_confirm')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-12 d-grid">
                                <button class="btn btn-brand btn-lg" type="submit">Đăng ký</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
