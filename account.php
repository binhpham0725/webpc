<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_login();

$user = current_user();
if ($user === null) {
    redirect_to('login.php');
}

$pageTitle = 'Tài khoản';
$activeNav = 'account';
$categories = site_categories();
$payments = payment_methods((int) $user['id']);
$orders = is_admin() ? recent_orders(8) : recent_orders(8, (int) $user['id']);
$requests = is_admin() ? recent_service_requests(8) : recent_service_requests(8, (int) $user['id']);
$profileState = pull_form_state('profile');
$paymentState = pull_form_state('payment');
$avatarState = pull_form_state('avatar');

include __DIR__ . '/includes/header.php';
?>

<section class="section-space">
    <div class="container-xxl">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="account-stack">
                    <div class="profile-panel">
                        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <?php if (trim((string) $user['avatar_url']) !== ''): ?>
                                    <img class="avatar-preview" src="<?= h((string) $user['avatar_url']) ?>" alt="<?= h((string) $user['full_name']) ?>">
                                <?php else: ?>
                                    <div class="avatar-preview"><?= h((string) $user['avatar_label']) ?></div>
                                <?php endif; ?>
                                <div>
                                    <p class="eyebrow mb-1">tài khoản</p>
                                    <h1 class="h2 mb-1"><?= h((string) $user['full_name']) ?></h1>
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <span class="role-chip"><?= is_admin() ? 'Quản trị' : 'Khách hàng' ?></span>
                                        <span class="result-count"><?= h((string) $user['email']) ?></span>
                                    </div>
                                </div>
                            </div>

                            <form action="" method="post">
                                <input type="hidden" name="action" value="logout">
                                <input type="hidden" name="redirect" value="index.php">
                                <button class="btn btn-outline-dark btn-soft" type="submit">Đăng xuất</button>
                            </form>
                        </div>

                        <form action="" method="post" class="row g-3">
                            <input type="hidden" name="action" value="save_profile">
                            <div class="col-md-6">
                                <label class="form-label" for="profile_full_name">Họ tên</label>
                                <input id="profile_full_name" name="full_name" class="form-control glass-input <?= field_error($profileState, 'full_name') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($profileState, 'full_name', (string) $user['full_name'])) ?>">
                                <?php if (field_error($profileState, 'full_name') !== ''): ?><span class="field-error"><?= h(field_error($profileState, 'full_name')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="profile_email">Email</label>
                                <input id="profile_email" name="email" class="form-control glass-input <?= field_error($profileState, 'email') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($profileState, 'email', (string) $user['email'])) ?>">
                                <?php if (field_error($profileState, 'email') !== ''): ?><span class="field-error"><?= h(field_error($profileState, 'email')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="profile_phone">Số điện thoại</label>
                                <input id="profile_phone" name="phone" class="form-control glass-input <?= field_error($profileState, 'phone') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($profileState, 'phone', (string) $user['phone'])) ?>">
                                <?php if (field_error($profileState, 'phone') !== ''): ?><span class="field-error"><?= h(field_error($profileState, 'phone')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="profile_company">Công ty</label>
                                <input id="profile_company" name="company" class="form-control glass-input" value="<?= h(field_value($profileState, 'company', (string) $user['company'])) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="profile_city">Tỉnh / Thành phố</label>
                                <input id="profile_city" name="city" class="form-control glass-input <?= field_error($profileState, 'city') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($profileState, 'city', (string) $user['city'])) ?>">
                                <?php if (field_error($profileState, 'city') !== ''): ?><span class="field-error"><?= h(field_error($profileState, 'city')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="profile_address">Địa chỉ</label>
                                <input id="profile_address" name="address" class="form-control glass-input <?= field_error($profileState, 'address') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($profileState, 'address', (string) $user['address'])) ?>">
                                <?php if (field_error($profileState, 'address') !== ''): ?><span class="field-error"><?= h(field_error($profileState, 'address')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="profile_note">Ghi chú</label>
                                <textarea id="profile_note" name="note" rows="4" class="form-control glass-textarea"><?= h(field_value($profileState, 'note', (string) $user['note'])) ?></textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button class="btn btn-brand" type="submit">Lưu thông tin</button>
                            </div>
                        </form>
                    </div>

                    <div class="order-panel">
                        <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                            <div>
                                <p class="eyebrow mb-1"><?= is_admin() ? 'bảng điều khiển' : 'đơn hàng' ?></p>
                                <h2 class="h4 mb-0"><?= is_admin() ? 'Đơn hàng gần đây toàn hệ thống' : 'Đơn hàng của bạn' ?></h2>
                            </div>
                        </div>

                        <?php if ($orders === []): ?>
                            <div class="empty-panel">Chưa có đơn hàng nào.</div>
                        <?php else: ?>
                            <div class="d-grid gap-3">
                                <?php foreach ($orders as $order): ?>
                                    <div class="history-item">
                                        <div class="d-flex flex-wrap justify-content-between gap-2">
                                            <div>
                                                <strong>WP-<?= str_pad((string) $order['id'], 5, '0', STR_PAD_LEFT) ?></strong>
                                                <div class="text-soft small"><?= h((string) $order['created_at']) ?></div>
                                            </div>
                                            <div class="text-end">
                                                <span class="status-chip is-success"><?= h((string) $order['status']) ?></span>
                                                <div class="fw-semibold mt-1"><?= money((int) $order['total_amount']) ?></div>
                                            </div>
                                        </div>
                                        <div class="text-soft mt-2">
                                            <?= h((string) $order['customer_name']) ?> · <?= h((string) $order['payment_method_label']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="account-stack">
                    <div class="payment-panel">
                        <p class="eyebrow mb-2">ảnh đại diện</p>
                        <h2 class="h4 mb-3">Đổi ảnh đại diện</h2>
                        <div class="avatar-stage">
                            <?php $avatarValue = field_value($avatarState, 'avatar_url', (string) $user['avatar_url']); ?>
                            <div class="d-flex align-items-center gap-3">
                                <?php if (trim($avatarValue) !== ''): ?>
                                    <div class="avatar-preview"><img src="<?= h($avatarValue) ?>" alt="<?= h((string) $user['full_name']) ?>"></div>
                                <?php else: ?>
                                    <div class="avatar-preview"><?= h((string) $user['avatar_label']) ?></div>
                                <?php endif; ?>
                                <div class="text-soft">Dùng URL ảnh jpg, png, webp hoặc ảnh Unsplash.</div>
                            </div>

                            <form action="" method="post" class="row g-3">
                                <input type="hidden" name="action" value="update_avatar">
                                <div class="col-12">
                                    <label class="form-label" for="avatar_url">Liên kết ảnh đại diện</label>
                                    <input id="avatar_url" name="avatar_url" class="form-control glass-input <?= field_error($avatarState, 'avatar_url') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h($avatarValue) ?>" placeholder="https://...">
                                    <?php if (field_error($avatarState, 'avatar_url') !== ''): ?><span class="field-error"><?= h(field_error($avatarState, 'avatar_url')) ?></span><?php endif; ?>
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button class="btn btn-brand" type="submit">Đổi ảnh</button>
                                    <?php if (trim((string) $user['avatar_url']) !== ''): ?>
                                        <button class="btn btn-outline-dark btn-soft" type="submit" name="avatar_url" value="">Xóa ảnh</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="payment-panel">
                        <p class="eyebrow mb-2">thanh toán</p>
                        <h2 class="h4 mb-3">Phương thức đã lưu</h2>
                        <div class="d-grid gap-3 mb-4">
                            <?php if ($payments === []): ?>
                                <div class="empty-panel">Chưa có phương thức thanh toán nào.</div>
                            <?php else: ?>
                                <?php foreach ($payments as $payment): ?>
                                    <div class="history-item">
                                        <div class="d-flex justify-content-between gap-3">
                                            <div>
                                                <strong><?= h((string) $payment['bank_name']) ?></strong>
                                                <div class="text-soft"><?= h((string) $payment['account_mask']) ?> · <?= h((string) $payment['holder_name']) ?></div>
                                                <?php if (trim((string) $payment['note']) !== ''): ?>
                                                    <div class="text-soft small mt-1"><?= h((string) $payment['note']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <form action="" method="post">
                                                <input type="hidden" name="action" value="delete_payment">
                                                <input type="hidden" name="payment_id" value="<?= (int) $payment['id'] ?>">
                                                <button class="btn btn-outline-danger btn-soft btn-sm" type="submit">Xóa</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <h3 class="h5 mb-3">Thêm phương thức mới</h3>
                        <form action="" method="post" class="row g-3">
                            <input type="hidden" name="action" value="add_payment">
                            <div class="col-12">
                                <label class="form-label" for="bank_name">Ngân hàng</label>
                                <input id="bank_name" name="bank_name" class="form-control glass-input <?= field_error($paymentState, 'bank_name') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($paymentState, 'bank_name')) ?>">
                                <?php if (field_error($paymentState, 'bank_name') !== ''): ?><span class="field-error"><?= h(field_error($paymentState, 'bank_name')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="account_number">Số tài khoản</label>
                                <input id="account_number" name="account_number" class="form-control glass-input <?= field_error($paymentState, 'account_number') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($paymentState, 'account_number')) ?>">
                                <?php if (field_error($paymentState, 'account_number') !== ''): ?><span class="field-error"><?= h(field_error($paymentState, 'account_number')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="holder_name">Chủ tài khoản</label>
                                <input id="holder_name" name="holder_name" class="form-control glass-input <?= field_error($paymentState, 'holder_name') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($paymentState, 'holder_name')) ?>">
                                <?php if (field_error($paymentState, 'holder_name') !== ''): ?><span class="field-error"><?= h(field_error($paymentState, 'holder_name')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="payment_note">Ghi chú</label>
                                <textarea id="payment_note" name="note" rows="3" class="form-control glass-textarea"><?= h(field_value($paymentState, 'note')) ?></textarea>
                            </div>
                            <div class="col-12 d-grid">
                                <button class="btn btn-brand" type="submit">Thêm phương thức</button>
                            </div>
                        </form>
                    </div>

                    <div class="payment-panel">
                        <p class="eyebrow mb-2"><?= is_admin() ? 'bảng dịch vụ' : 'lịch sử dịch vụ' ?></p>
                        <h2 class="h4 mb-3"><?= is_admin() ? 'Yêu cầu dịch vụ gần đây' : 'Yêu cầu dịch vụ của bạn' ?></h2>
                        <?php if ($requests === []): ?>
                            <div class="empty-panel">Chưa có yêu cầu dịch vụ nào.</div>
                        <?php else: ?>
                            <div class="d-grid gap-3">
                                <?php foreach ($requests as $request): ?>
                                    <div class="history-item">
                                        <div class="d-flex flex-wrap justify-content-between gap-2">
                                            <div>
                                            <strong><?= h((string) ($request['service_title'] ?? 'Dịch vụ')) ?></strong>
                                                <div class="text-soft small"><?= h((string) $request['created_at']) ?></div>
                                            </div>
                                            <span class="status-chip is-warning"><?= h((string) $request['status']) ?></span>
                                        </div>
                                        <div class="text-soft mt-2">
                                            <?= h((string) $request['customer_name']) ?> · <?= h((string) $request['budget']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
