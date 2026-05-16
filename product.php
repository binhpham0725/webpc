<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$product = $slug !== '' ? product_by_slug($slug) : null;

if ($product === null) {
    set_flash('danger', 'Không tìm thấy sản phẩm.');
    redirect_to('products.php');
}

$pageTitle = (string) $product['name'];
$activeNav = 'products';
$categories = site_categories();
$related = related_products($product, 4);
$specs = decode_json_column((string) $product['specs_json']);
$features = decode_json_column((string) $product['features_json']);
$tags = product_tags($product);
$accentImages = product_accent_images($product);

include __DIR__ . '/includes/header.php';
?>

<section class="section-space">
    <div class="container-xxl">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <article class="detail-panel">
                    <div class="detail-gallery mb-4">
                        <img src="<?= h((string) $product['cover_image']) ?>" alt="<?= h((string) $product['name']) ?>">
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="tag-pill"><?= h((string) $product['category_name']) ?></span>
                        <?php if (discount_percent($product) > 0): ?>
                            <span class="sale-pill">-<?= discount_percent($product) ?>%</span>
                        <?php endif; ?>
                        <span class="stock-pill <?= (int) $product['stock'] > 0 ? 'is-stock' : '' ?>">
                            <?= (int) $product['stock'] > 0 ? 'Còn ' . (int) $product['stock'] : 'Hết hàng' ?>
                        </span>
                    </div>
                    <h1 class="h2 mb-3"><?= h((string) $product['name']) ?></h1>
                    <p class="muted-paragraph mb-4"><?= h((string) $product['description']) ?></p>

                    <div class="spec-grid mb-4">
                        <?php foreach ($specs as $label => $value): ?>
                            <div class="spec-box">
                                <strong><?= h((string) $label) ?></strong>
                                <span><?= h((string) $value) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <h2 class="h5 mb-3">Điểm chính</h2>
                            <ul class="list-clean">
                                <?php foreach ($features as $feature): ?>
                                    <li><?= h((string) $feature) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h2 class="h5 mb-3">Tag sản phẩm</h2>
                            <div class="mini-meta">
                                <?php foreach ($tags as $tag): ?>
                                    <span class="spec-pill tag-pill"><?= h($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($accentImages !== []): ?>
                                <div class="mt-4 product-accent-grid">
                                    <?php foreach ($accentImages as $accentImage): ?>
                                        <img class="img-fluid rounded-3" src="<?= h($accentImage) ?>" alt="<?= h((string) $product['name']) ?> ảnh phụ">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            </div>

            <div class="col-lg-5">
                <aside class="summary-panel sticky-panel">
                    <p class="eyebrow mb-2">mua nhanh</p>
                    <h2 class="h3 mb-2"><?= h((string) $product['name']) ?></h2>
                    <p class="text-soft mb-3"><?= h((string) $product['summary']) ?></p>
                    <div class="d-flex align-items-end gap-2 flex-wrap mb-4">
                        <strong class="price-current"><?= money((int) $product['price']) ?></strong>
                        <span class="price-old"><?= money((int) $product['old_price']) ?></span>
                    </div>

                    <form action="" method="post" class="d-grid gap-3">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                        <input type="hidden" name="redirect" value="product.php?slug=<?= h((string) $product['slug']) ?>">

                        <div>
                            <label class="form-label" for="quantity">Số lượng</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary btn-soft" type="button" data-qty-step="-1" data-qty-target="quantity">-</button>
                                <input id="quantity" name="quantity" type="number" class="form-control glass-input text-center" min="1" max="<?= (int) $product['stock'] ?>" value="1">
                                <button class="btn btn-outline-secondary btn-soft" type="button" data-qty-step="1" data-qty-target="quantity">+</button>
                            </div>
                        </div>

                        <button class="btn btn-brand btn-lg" type="submit">Thêm vào giỏ hàng</button>
                        <a class="btn btn-outline-dark btn-soft" href="products.php?category=<?= h((string) $product['category_slug']) ?>">Quay lại danh mục</a>
                    </form>

                    <div class="mt-4 summary-list">
                        <div class="summary-line"><span>Đánh giá</span><strong><?= h((string) $product['rating']) ?>/5</strong></div>
                        <div class="summary-line"><span>Tình trạng</span><strong><?= (int) $product['stock'] > 0 ? 'Sẵn hàng' : 'Hết hàng' ?></strong></div>
                        <div class="summary-line"><span>Danh mục</span><strong><?= h((string) $product['category_name']) ?></strong></div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</section>

<?php if ($related !== []): ?>
    <section class="section-space">
        <div class="container-xxl">
            <div class="section-head">
                <p class="eyebrow mb-2">gợi ý tiếp</p>
                <h2 class="h3 mb-0">Sản phẩm liên quan</h2>
            </div>
            <div class="row g-3">
                <?php foreach ($related as $item): ?>
                    <div class="col-xl-3 col-md-6">
                        <?= render_product_card($item, 'product.php?slug=' . $product['slug']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
