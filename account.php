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
$paymentHistory = is_admin() ? recent_payments(8) : recent_payments(8, (int) $user['id']);
$requests = is_admin() ? recent_service_requests(8) : recent_service_requests(8, (int) $user['id']);
$orderStatusOptions = order_status_options();
$paymentStatusOptions = payment_status_options();
$paymentTypeOptions = payment_type_options();
$gatewayOptions = payment_gateway_options();
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
                                                <strong><?= h(order_code((int) $order['id'])) ?></strong>
                                                <div class="text-soft small"><?= h((string) $order['created_at']) ?></div>
                                            </div>
                                            <div class="text-end">
                                                <span class="status-chip is-success"><?= h(status_label((string) $order['status'])) ?></span>
                                                <div class="fw-semibold mt-1"><?= money((int) $order['total_amount']) ?></div>
                                            </div>
                                        </div>
                                        <div class="text-soft mt-2">
                                            <?= h((string) $order['customer_name']) ?> · <?= h((string) $order['payment_method_label']) ?>
                                        </div>
                                        <?php if (is_admin()): ?>
                                            <form action="" method="post" class="row g-2 mt-3 align-items-end">
                                                <input type="hidden" name="action" value="update_order_status">
                                                <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                                <div class="col-md-5">
                                                    <label class="form-label small mb-1" for="order_status_<?= (int) $order['id'] ?>">Don hang</label>
                                                    <select id="order_status_<?= (int) $order['id'] ?>" name="order_status" class="form-select glass-select">
                                                        <?php foreach ($orderStatusOptions as $value => $label): ?>
                                                            <option value="<?= h($value) ?>" <?= (string) $order['status'] === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label small mb-1" for="payment_status_<?= (int) $order['id'] ?>">Thanh toán</label>
                                                    <select id="payment_status_<?= (int) $order['id'] ?>" name="payment_status" class="form-select glass-select">
                                                        <?php
                                                        $orderPayment = fetch_one('SELECT status FROM payments WHERE order_id = :order_id LIMIT 1', ['order_id' => (int) $order['id']]);
                                                        $currentPaymentStatus = (string) ($orderPayment['status'] ?? 'pending');
                                                        ?>
                                                        <?php foreach ($paymentStatusOptions as $value => $label): ?>
                                                            <option value="<?= h($value) ?>" <?= $currentPaymentStatus === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-2 d-grid">
                                                    <button class="btn btn-outline-dark btn-soft btn-sm" type="submit">Luu</button>
                                                </div>
                                            </form>
                                        <?php endif; ?>
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

                            <form action="" method="post" enctype="multipart/form-data" class="row g-3">
                                <input type="hidden" name="action" value="update_avatar">
                                <div class="col-12">
                                    <label class="form-label" for="avatar_file">Chon anh tu may</label>
                                    <input id="avatar_file" name="avatar_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="form-control glass-input <?= field_error($avatarState, 'avatar_file') !== '' ? 'is-invalid-soft' : '' ?>">
                                    <?php if (field_error($avatarState, 'avatar_file') !== ''): ?><span class="field-error"><?= h(field_error($avatarState, 'avatar_file')) ?></span><?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="avatar_url">Hoac dan link anh</label>
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
                        <h2 class="h4 mb-3">Cổng thanh toán trực tuyến</h2>
                        <div class="payment-gateway-list mb-4">
                            <?php foreach ($gatewayOptions as $gatewayKey => $gateway): ?>
                                <?php
                                $isConnected = false;
                                foreach ($payments as $payment) {
                                    if ((string) ($payment['method_type'] ?? '') === $gatewayKey) {
                                        $isConnected = true;
                                        break;
                                    }
                                }
                                ?>
                                <div class="payment-gateway-row">
                                    <div class="gateway-brand"><span><?= h((string) $gateway['badge']) ?></span></div>
                                    <div class="gateway-copy">
                                        <strong><?= h((string) $gateway['name']) ?></strong>
                                        <div class="text-soft small">
                                            <?= h((string) $gateway['description']) ?>
                                            <?php if ($isConnected): ?>
                                                <span class="gateway-status">Đang sử dụng</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <form action="" method="post" class="ms-auto">
                                        <input type="hidden" name="action" value="connect_gateway">
                                        <input type="hidden" name="gateway" value="<?= h($gatewayKey) ?>">
                                        <button class="btn btn-primary btn-sm" type="submit">Kết nối</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="manual-payment-head mb-3">
                            <div>
                                <h3 class="h5 mb-1">Phương thức thanh toán thủ công</h3>
                                <div class="text-soft small">Quản lý tài khoản ngân hàng, tiền mặt và các phương thức tự nhập.</div>
                            </div>
                            <a class="btn btn-primary btn-sm" href="#manual_payment_form"><i class="bi bi-plus-circle"></i> Thêm phương thức</a>
                        </div>
                        <div class="d-grid gap-3 mb-4">
                            <?php if ($payments === []): ?>
                                <div class="empty-panel">Chưa có phương thức thanh toán nào.</div>
                            <?php else: ?>
                                <?php foreach ($payments as $payment): ?>
                                    <div class="history-item">
                                        <div class="d-flex justify-content-between gap-3">
                                            <div>
                                                <strong><?= h(payment_type_label((string) ($payment['method_type'] ?? 'bank_transfer'))) ?></strong>
                                                <div class="text-soft"><?= h(payment_method_display($payment)) ?> · <?= h((string) $payment['holder_name']) ?></div>
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
                        <form id="manual_payment_form" action="" method="post" class="row g-3">
                            <input type="hidden" name="action" value="add_payment">
                            <div class="col-12">
                                <label class="form-label" for="method_type">Loại thanh toán</label>
                                <select id="method_type" name="method_type" class="form-select glass-select <?= field_error($paymentState, 'method_type') !== '' ? 'is-invalid-soft' : '' ?>">
                                    <?php foreach ($paymentTypeOptions as $value => $label): ?>
                                        <option value="<?= h($value) ?>" <?= field_value($paymentState, 'method_type', 'bank_transfer') === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (field_error($paymentState, 'method_type') !== ''): ?><span class="field-error"><?= h(field_error($paymentState, 'method_type')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="bank_name">Ngân hàng / nhà cung cấp</label>
                                <input id="bank_name" name="bank_name" class="form-control glass-input <?= field_error($paymentState, 'bank_name') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($paymentState, 'bank_name')) ?>" placeholder="VD: Vietcombank, MoMo, ZaloPay, VNPay">
                                <?php if (field_error($paymentState, 'bank_name') !== ''): ?><span class="field-error"><?= h(field_error($paymentState, 'bank_name')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="account_number">Số tài khoản / số ví</label>
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
                        <p class="eyebrow mb-2"><?= is_admin() ? 'bang thanh toan' : 'lich su thanh toan' ?></p>
                        <h2 class="h4 mb-3"><?= is_admin() ? 'Thanh toán gần đây' : 'Thanh toán của bạn' ?></h2>
                        <?php if ($paymentHistory === []): ?>
                            <div class="empty-panel">Chua co giao dich thanh toan nao.</div>
                        <?php else: ?>
                            <div class="d-grid gap-3">
                                <?php foreach ($paymentHistory as $paymentRow): ?>
                                    <div class="history-item">
                                        <div class="d-flex flex-wrap justify-content-between gap-2">
                                            <div>
                                                <strong><?= h((string) $paymentRow['payment_code']) ?></strong>
                                                <div class="text-soft small"><?= h((string) $paymentRow['created_at']) ?></div>
                                            </div>
                                            <div class="text-end">
                                                <span class="status-chip is-warning"><?= h(status_label((string) $paymentRow['status'], 'payment')) ?></span>
                                                <div class="fw-semibold mt-1"><?= money((int) $paymentRow['amount']) ?></div>
                                            </div>
                                        </div>
                                        <div class="text-soft mt-2">
                                            <?= h((string) $paymentRow['customer_name']) ?> · <?= h((string) $paymentRow['provider']) ?> · <?= h((string) $paymentRow['payment_method_label']) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
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
