<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Giỏ hàng';
$activeNav = 'cart';
$categories = site_categories();
$items = cart_items();
$totals = cart_totals($items);
$profile = current_profile_defaults();
$payments = is_logged_in() ? payment_methods((int) current_user_id()) : [];
$preferredPaymentTypes = ['momo', 'vnpay', 'bank_card', 'visa', 'cod', 'bank_transfer'];
$checkoutPayments = [];
foreach ($preferredPaymentTypes as $type) {
    foreach ($payments as $payment) {
        if ((string) ($payment['method_type'] ?? 'bank_transfer') === $type) {
            $checkoutPayments[] = $payment;
            break;
        }
    }
}
$checkoutPayments = $checkoutPayments !== [] ? $checkoutPayments : $payments;
$checkoutState = pull_form_state('checkout');

include __DIR__ . '/includes/header.php';
?>

<section class="section-space">
    <div class="container-xxl">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="detail-panel">
                    <p class="eyebrow mb-2">giỏ hàng</p>
                    <h1 class="h2 mb-2">Kiểm tra số lượng, cập nhật giỏ và chốt đơn hàng.</h1>
                    <p class="result-count mb-0">
                        Giỏ hàng được quản lý bằng session PHP. Đơn hàng được lưu vào database sau khi đăng nhập và thanh toán.
                    </p>
                </div>

                <div class="mt-3">
                    <?php if ($items === []): ?>
                        <div class="empty-panel">Giỏ hàng đang trống. Mở danh mục để thêm sản phẩm trước khi thanh toán.</div>
                    <?php else: ?>
                        <div class="d-grid gap-3">
                            <?php foreach ($items as $item): ?>
                                <?php $product = $item['product']; ?>
                                <div class="cart-row">
                                    <img class="cart-thumb" src="<?= h((string) $product['cover_image']) ?>" alt="<?= h((string) $product['name']) ?>">
                                    <div>
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <span class="tag-pill"><?= h((string) $product['category_name']) ?></span>
                                            <span class="stock-pill <?= (int) $product['stock'] > 0 ? 'is-stock' : '' ?>">Còn <?= (int) $product['stock'] ?></span>
                                        </div>
                                        <h3 class="h5 mb-2"><?= h((string) $product['name']) ?></h3>
                                        <p class="text-soft mb-2"><?= h((string) $product['summary']) ?></p>
                                        <div class="d-flex flex-wrap gap-2 align-items-end">
                                            <strong class="price-current"><?= money((int) $product['price']) ?></strong>
                                            <span class="price-old"><?= money((int) $product['old_price']) ?></span>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2 align-content-start">
                                        <form action="" method="post" class="d-grid gap-2">
                                            <input type="hidden" name="action" value="set_cart_item">
                                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                            <label class="form-label mb-0">Số lượng</label>
                                            <input
                                                type="number"
                                                min="1"
                                                max="<?= (int) $product['stock'] ?>"
                                                class="form-control glass-input"
                                                name="quantity"
                                                value="<?= (int) $item['quantity'] ?>"
                                            >
                                            <button class="btn btn-outline-dark btn-soft btn-sm" type="submit">Cập nhật</button>
                                        </form>
                                        <form action="" method="post">
                                            <input type="hidden" name="action" value="remove_cart_item">
                                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                            <button class="btn btn-outline-danger btn-soft" type="submit">Xóa</button>
                                        </form>
                                        <div class="text-end fw-semibold"><?= money((int) $item['line_total']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-5">
                <aside class="summary-panel sticky-panel">
                    <p class="eyebrow mb-2">thanh toán</p>
                    <h2 class="h4 mb-3">Tóm tắt thanh toán</h2>
                    <div class="summary-list mb-4">
                        <div class="summary-line"><span>Tạm tính</span><strong><?= money($totals['subtotal']) ?></strong></div>
                        <div class="summary-line"><span>Tiết kiệm</span><strong><?= money($totals['saving']) ?></strong></div>
                        <div class="summary-line"><span>Giảm thêm</span><strong><?= money($totals['discount']) ?></strong></div>
                        <div class="summary-line"><span>Vận chuyển</span><strong><?= money($totals['shipping']) ?></strong></div>
                        <div class="summary-line total"><span>Thành tiền</span><strong><?= money($totals['total']) ?></strong></div>
                    </div>

                    <form action="" method="post" class="mb-4">
                        <input type="hidden" name="action" value="apply_coupon">
                        <div class="input-group">
                            <input type="text" name="coupon_code" class="form-control glass-input" placeholder="Mã demo: NVIDIA" value="<?= h((string) ($_SESSION['webpc_coupon'] ?? '')) ?>">
                            <button class="btn btn-outline-dark btn-soft" type="submit">Áp mã</button>
                        </div>
                    </form>

                    <?php if (!is_logged_in()): ?>
                        <div class="empty-panel">
                            Đăng nhập để thanh toán, lưu đơn hàng và sử dụng phương thức thanh toán trong tài khoản.
                        </div>
                        <div class="d-grid mt-3">
                            <a class="btn btn-brand btn-lg" href="login.php?next=cart.php">Đăng nhập để thanh toán</a>
                        </div>
                    <?php else: ?>
                        <form action="" method="post" class="row g-3">
                            <input type="hidden" name="action" value="checkout">
                            <div class="col-md-6">
                                <label class="form-label" for="full_name">Họ tên</label>
                                <input id="full_name" name="full_name" class="form-control glass-input <?= field_error($checkoutState, 'full_name') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($checkoutState, 'full_name', (string) $profile['full_name'])) ?>">
                                <?php if (field_error($checkoutState, 'full_name') !== ''): ?><span class="field-error"><?= h(field_error($checkoutState, 'full_name')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="email">Email</label>
                                <input id="email" name="email" class="form-control glass-input <?= field_error($checkoutState, 'email') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($checkoutState, 'email', (string) $profile['email'])) ?>">
                                <?php if (field_error($checkoutState, 'email') !== ''): ?><span class="field-error"><?= h(field_error($checkoutState, 'email')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Số điện thoại</label>
                                <input id="phone" name="phone" class="form-control glass-input <?= field_error($checkoutState, 'phone') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($checkoutState, 'phone', (string) $profile['phone'])) ?>">
                                <?php if (field_error($checkoutState, 'phone') !== ''): ?><span class="field-error"><?= h(field_error($checkoutState, 'phone')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Phương thức thanh toán</label>
                                <div class="checkout-payment-grid <?= field_error($checkoutState, 'payment_method_id') !== '' ? 'is-invalid-soft' : '' ?>">
                                    <?php foreach ($checkoutPayments as $index => $payment): ?>
                                        <?php
                                        $paymentId = (int) $payment['id'];
                                        $paymentType = (string) ($payment['method_type'] ?? 'bank_transfer');
                                        $checked = field_value($checkoutState, 'payment_method_id') === (string) $paymentId || (field_value($checkoutState, 'payment_method_id') === '' && $index === 0);
                                        ?>
                                        <label class="checkout-payment-option">
                                            <input type="radio" name="payment_method_id" value="<?= $paymentId ?>" <?= $checked ? 'checked' : '' ?>>
                                            <span class="checkout-payment-logo"><?= h(payment_type_label($paymentType)) ?></span>
                                            <span class="checkout-payment-copy">
                                                <strong><?= h(payment_type_label($paymentType)) ?></strong>
                                                <small><?= h(payment_method_display($payment)) ?></small>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (field_error($checkoutState, 'payment_method_id') !== ''): ?><span class="field-error"><?= h(field_error($checkoutState, 'payment_method_id')) ?></span><?php endif; ?>
                                <?php if ($payments === []): ?>
                                    <div class="field-error">Chưa có phương thức thanh toán.</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="address">Địa chỉ giao hàng</label>
                                <textarea id="address" name="address" rows="3" class="form-control glass-textarea <?= field_error($checkoutState, 'address') !== '' ? 'is-invalid-soft' : '' ?>"><?= h(field_value($checkoutState, 'address', (string) $profile['address'])) ?></textarea>
                                <?php if (field_error($checkoutState, 'address') !== ''): ?><span class="field-error"><?= h(field_error($checkoutState, 'address')) ?></span><?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="note">Ghi chú</label>
                                <textarea id="note" name="note" rows="3" class="form-control glass-textarea"><?= h(field_value($checkoutState, 'note')) ?></textarea>
                            </div>
                            <div class="col-12 d-grid">
                                <button class="btn btn-brand btn-lg" type="submit" <?= ($items === [] || $payments === []) ? 'disabled' : '' ?>>Thanh toán</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
