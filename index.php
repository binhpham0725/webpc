<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Trang chủ';
$activeNav = 'home';
$categories = site_categories();
$featured = featured_products(12);
$services = site_services();
$heroProduct = $featured[0] ?? null;
$heroAccentImage = $heroProduct !== null ? product_first_accent_image($heroProduct) : '';
$visibleFeaturedCount = 4;

include __DIR__ . '/includes/header.php';
?>

<section class="section-space">
    <div class="container-xxl">
        <div class="hero-panel hero-surface overflow-hidden">
            <div class="row g-0 align-items-stretch">
                <div class="col-xl-7">
                    <div class="hero-copy">
                        <p class="eyebrow mb-3">cửa hàng webpc</p>
                        <h1 class="hero-title mb-3">Cửa hàng PC với liquid glass nổi, bố cục gọn và luồng mua hàng rõ ràng.</h1>
                        <p class="hero-text mb-4">
                            Một màn hình, nhiều tác vụ nhưng không bị trùng chức năng.
                            Header giữ tìm kiếm, còn trang chủ tập trung vào danh mục, sản phẩm bán chạy và lối vào nhanh.
                        </p>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                            <a class="btn btn-brand btn-lg" href="products.php">Mở danh mục</a>
                            <?php if (is_admin()): ?>
                                <a class="btn btn-outline-dark btn-soft btn-lg" href="add-product.php">Thêm sản phẩm mới</a>
                            <?php elseif (is_logged_in()): ?>
                                <a class="btn btn-outline-dark btn-soft btn-lg" href="account.php">Mở tài khoản</a>
                            <?php else: ?>
                                <a class="btn btn-outline-dark btn-soft btn-lg" href="login.php">Đăng nhập / Đăng ký</a>
                            <?php endif; ?>
                        </div>

                        <div class="metric-grid">
                            <div class="quick-stat">
                                <strong><?= count($featured) ?>+</strong>
                                <span class="text-soft">Sản phẩm nổi bật</span>
                            </div>
                            <div class="quick-stat">
                                <strong><?= count($categories) ?></strong>
                                <span class="text-soft">Nhóm hàng chính</span>
                            </div>
                            <div class="quick-stat">
                                <strong><?= count($services) ?></strong>
                                <span class="text-soft">Dịch vụ triển khai</span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($heroProduct !== null): ?>
                    <div class="col-xl-5">
                        <div class="hero-preview">
                            <div class="hero-preview-media mb-3">
                                <img src="<?= h((string) $heroProduct['cover_image']) ?>" alt="<?= h((string) $heroProduct['name']) ?>">
                                <div class="glass-controller">
                                    <div class="glass-controller-media">
                                        <img class="glass-controller-thumb" src="<?= h($heroAccentImage) ?>" alt="<?= h((string) $heroProduct['name']) ?>">
                                        <div class="min-w-0">
                                            <div class="glass-controller-title text-truncate"><?= h((string) $heroProduct['name']) ?></div>
                                            <div class="glass-controller-sub text-truncate"><?= h((string) $heroProduct['category_name']) ?> · <?= money((int) $heroProduct['price']) ?></div>
                                        </div>
                                    </div>
                                    <div class="glass-controller-actions">
                                        <a class="glass-icon-button" href="products.php?category=<?= h((string) $heroProduct['category_slug']) ?>" aria-label="Mở danh mục">
                                            <i class="bi bi-grid"></i>
                                        </a>
                                        <a class="glass-icon-button" href="product.php?slug=<?= h((string) $heroProduct['slug']) ?>" aria-label="Mở sản phẩm">
                                            <i class="bi bi-play-fill"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <span class="tag-pill"><?= h((string) $heroProduct['category_name']) ?></span>
                                <?php if (discount_percent($heroProduct) > 0): ?>
                                    <span class="sale-pill">-<?= discount_percent($heroProduct) ?>%</span>
                                <?php endif; ?>
                                <span class="stock-pill <?= (int) $heroProduct['stock'] > 0 ? 'is-stock' : '' ?>">
                                    <?= (int) $heroProduct['stock'] > 0 ? 'Còn ' . (int) $heroProduct['stock'] : 'Hết hàng' ?>
                                </span>
                            </div>

                            <h2 class="h3 mb-2"><?= h((string) $heroProduct['name']) ?></h2>
                            <p class="muted-paragraph mb-3"><?= h((string) $heroProduct['summary']) ?></p>
                            <div class="d-flex align-items-end gap-2 flex-wrap mb-3">
                                <strong class="price-current"><?= money((int) $heroProduct['price']) ?></strong>
                                <span class="price-old"><?= money((int) $heroProduct['old_price']) ?></span>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-auto">
                                <form action="" method="post" class="d-inline-flex">
                                    <input type="hidden" name="action" value="add_to_cart">
                                    <input type="hidden" name="product_id" value="<?= (int) $heroProduct['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="redirect" value="index.php">
                                    <button class="btn btn-brand" type="submit">Thêm vào giỏ</button>
                                </form>
                                <a class="btn btn-outline-dark btn-soft" href="product.php?slug=<?= h((string) $heroProduct['slug']) ?>">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container-xxl">
        <div class="section-head d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div>
                <p class="eyebrow mb-2">vào nhanh</p>
                <h2 class="h3 mb-0">Danh mục chính</h2>
            </div>
            <a class="btn btn-outline-dark btn-soft" href="products.php?sort=sale">Xem ưu đãi</a>
        </div>
        <div class="row g-3">
            <?php foreach ($categories as $category): ?>
                <div class="col-lg-3 col-md-6">
                    <a class="category-tile d-block" href="products.php?category=<?= h((string) $category['slug']) ?>">
                        <img src="<?= h((string) $category['hero_image']) ?>" alt="<?= h((string) $category['name']) ?>">
                        <div class="category-tile-content">
                            <h3 class="h4 mb-2"><?= h((string) $category['name']) ?></h3>
                            <p class="mb-0"><?= h((string) $category['description']) ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section-space">
    <div class="container-xxl">
        <div class="section-head d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div>
                <p class="eyebrow mb-2">bán chạy</p>
                <h2 class="h3 mb-0">Chỉ hiển thị 1 hàng trước, mở rộng khi cần</h2>
            </div>
            <a class="btn btn-outline-dark btn-soft" href="products.php">Xem toàn bộ</a>
        </div>

        <div class="row g-3" id="featured-grid">
            <?php foreach ($featured as $index => $product): ?>
                <div class="col-xl-3 col-md-6 featured-item <?= $index >= $visibleFeaturedCount ? 'featured-extra d-none' : '' ?>">
                    <?= render_product_card($product, 'index.php') ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($featured) > $visibleFeaturedCount): ?>
            <div class="d-flex justify-content-center mt-4">
                <button
                    class="btn btn-outline-dark btn-soft reveal-button"
                    type="button"
                    data-reveal-grid="featured-grid"
                    data-expanded="0"
                    data-more-label="Xem thêm"
                    data-less-label="Thu gọn"
                >Xem thêm</button>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section-space">
    <div class="container-xxl">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-5">
                <div class="glass-panel h-100 p-4">
                    <p class="eyebrow mb-2">dịch vụ</p>
                    <h2 class="h3">Lắp máy, nâng cấp, vệ sinh và triển khai góc làm việc.</h2>
                    <p class="muted-paragraph mt-3 mb-4">
                        Trang chủ chỉ giới thiệu nhanh. Toàn bộ form gửi yêu cầu và lịch sử được tách sang trang dịch vụ để giao diện không bị dày.
                    </p>
                    <a class="btn btn-brand" href="services.php">Mở trang dịch vụ</a>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="row g-3">
                    <?php foreach ($services as $service): ?>
                        <div class="col-md-6">
                            <div class="glass-card card h-100 border-0 p-3">
                                <img class="service-cover" src="<?= h((string) $service['cover_image']) ?>" alt="<?= h((string) $service['title']) ?>">
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="tag-pill"><?= h((string) $service['eta_label']) ?></span>
                                    <span class="stock-pill is-stock"><?= h((string) $service['price_label']) ?></span>
                                </div>
                                <h3 class="h5"><?= h((string) $service['title']) ?></h3>
                                <p class="mb-3"><?= h((string) $service['description']) ?></p>
                                <a class="btn btn-outline-dark btn-soft mt-auto" href="services.php#request-form">Gửi yêu cầu</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
