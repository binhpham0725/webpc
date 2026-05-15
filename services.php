<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Dịch vụ';
$activeNav = 'services';
$categories = site_categories();
$services = site_services();
$requests = is_logged_in() ? recent_service_requests(6, (int) current_user_id()) : recent_service_requests(6);
$profile = current_profile_defaults();
$serviceState = pull_form_state('service_request');

include __DIR__ . '/includes/header.php';
?>

<section class="section-space">
    <div class="container-xxl">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="detail-panel mb-4">
                    <p class="eyebrow mb-2">dịch vụ</p>
                    <h1 class="h2 mb-2">Lắp máy, nâng cấp, vệ sinh và triển khai góc làm việc.</h1>
                    <p class="result-count mb-0">
                        Đây là tab dịch vụ riêng, không nhồi chung vào trang chủ. Mục tiêu là rõ bố cục,
                        dễ gửi yêu cầu và dễ đọc lịch sử xử lý.
                    </p>
                </div>

                <div class="row g-3">
                    <?php foreach ($services as $service): ?>
                        <div class="col-md-6">
                            <article class="service-panel h-100">
                                <img class="service-cover" src="<?= h((string) $service['cover_image']) ?>" alt="<?= h((string) $service['title']) ?>">
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="tag-pill"><?= h((string) $service['eta_label']) ?></span>
                                    <span class="stock-pill is-stock"><?= h((string) $service['price_label']) ?></span>
                                </div>
                                <h2 class="h5"><?= h((string) $service['title']) ?></h2>
                                <p class="mb-0"><?= h((string) $service['description']) ?></p>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-panel mt-4">
                    <p class="eyebrow mb-2"><?= is_logged_in() ? 'lịch sử của bạn' : 'yêu cầu gần đây' ?></p>
                    <h2 class="h4 mb-3"><?= is_logged_in() ? 'Yêu cầu dịch vụ đã gửi' : 'Yêu cầu dịch vụ mới nhất' ?></h2>
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
                                        <?= h((string) $request['customer_name']) ?> · <?= h((string) $request['phone']) ?> · <?= h((string) $request['budget']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-5">
                <aside class="service-panel sticky-panel" id="request-form">
                    <p class="eyebrow mb-2">gửi yêu cầu</p>
                    <h2 class="h4 mb-3">Tạo đầu bài kỹ thuật</h2>
                    <form action="" method="post" class="row g-3">
                        <input type="hidden" name="action" value="submit_service_request">
                        <div class="col-md-6">
                            <label class="form-label" for="service_name">Họ tên</label>
                            <input id="service_name" name="name" class="form-control glass-input <?= field_error($serviceState, 'name') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($serviceState, 'name', (string) $profile['full_name'])) ?>">
                            <?php if (field_error($serviceState, 'name') !== ''): ?><span class="field-error"><?= h(field_error($serviceState, 'name')) ?></span><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="service_phone">Số điện thoại</label>
                            <input id="service_phone" name="phone" class="form-control glass-input <?= field_error($serviceState, 'phone') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($serviceState, 'phone', (string) $profile['phone'])) ?>">
                            <?php if (field_error($serviceState, 'phone') !== ''): ?><span class="field-error"><?= h(field_error($serviceState, 'phone')) ?></span><?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="service_email">Email</label>
                            <input id="service_email" name="email" class="form-control glass-input <?= field_error($serviceState, 'email') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($serviceState, 'email', (string) $profile['email'])) ?>">
                            <?php if (field_error($serviceState, 'email') !== ''): ?><span class="field-error"><?= h(field_error($serviceState, 'email')) ?></span><?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="service_id">Dịch vụ</label>
                            <select id="service_id" name="service_id" class="form-select glass-select <?= field_error($serviceState, 'service_id') !== '' ? 'is-invalid-soft' : '' ?>">
                                <option value="">Chọn dịch vụ</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= (int) $service['id'] ?>" <?= field_value($serviceState, 'service_id') === (string) $service['id'] ? 'selected' : '' ?>>
                                        <?= h((string) $service['title']) ?> - <?= h((string) $service['price_label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (field_error($serviceState, 'service_id') !== ''): ?><span class="field-error"><?= h(field_error($serviceState, 'service_id')) ?></span><?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="budget">Ngân sách / đầu bài</label>
                            <textarea id="budget" name="budget" rows="4" class="form-control glass-textarea <?= field_error($serviceState, 'budget') !== '' ? 'is-invalid-soft' : '' ?>"><?= h(field_value($serviceState, 'budget')) ?></textarea>
                            <?php if (field_error($serviceState, 'budget') !== ''): ?><span class="field-error"><?= h(field_error($serviceState, 'budget')) ?></span><?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="service_note">Ghi chú thêm</label>
                            <textarea id="service_note" name="note" rows="3" class="form-control glass-textarea"><?= h(field_value($serviceState, 'note')) ?></textarea>
                        </div>
                        <div class="col-12 d-grid">
                            <button class="btn btn-brand" type="submit">Gửi yêu cầu dịch vụ</button>
                        </div>
                    </form>
                </aside>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
