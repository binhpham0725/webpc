<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_admin();

$pageTitle = 'Thêm sản phẩm';
$activeNav = 'admin';
$categories = site_categories();
$productState = pull_form_state('product_create');
$recentProducts = recent_products(8);

include __DIR__ . '/includes/header.php';
?>

<section class="section-space">
    <div class="container-xxl">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="profile-panel">
                    <p class="eyebrow mb-2">quản trị</p>
                    <h1 class="h2 mb-2">Thêm sản phẩm mới</h1>
                    <p class="result-count mb-4">
                        Khu vực này chỉ dành cho admin. Sản phẩm mới được ghi trực tiếp vào database và xuất hiện trong danh mục.
                    </p>

                    <form action="" method="post" class="row g-3">
                        <input type="hidden" name="action" value="create_product">

                        <div class="col-md-6">
                            <label class="form-label" for="category_id">Danh mục</label>
                            <select id="category_id" name="category_id" class="form-select glass-select <?= field_error($productState, 'category_id') !== '' ? 'is-invalid-soft' : '' ?>">
                                <option value="">Chọn danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id'] ?>" <?= field_value($productState, 'category_id') === (string) $category['id'] ? 'selected' : '' ?>>
                                        <?= h((string) $category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (field_error($productState, 'category_id') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'category_id')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="slug">Slug URL</label>
                            <input id="slug" name="slug" class="form-control glass-input <?= field_error($productState, 'slug') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($productState, 'slug')) ?>" placeholder="Bỏ trống để tự sinh từ tên">
                            <?php if (field_error($productState, 'slug') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'slug')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="name">Tên sản phẩm</label>
                            <input id="name" name="name" class="form-control glass-input <?= field_error($productState, 'name') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($productState, 'name')) ?>">
                            <?php if (field_error($productState, 'name') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'name')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="summary">Mô tả ngắn</label>
                            <textarea id="summary" name="summary" rows="3" class="form-control glass-textarea <?= field_error($productState, 'summary') !== '' ? 'is-invalid-soft' : '' ?>"><?= h(field_value($productState, 'summary')) ?></textarea>
                            <?php if (field_error($productState, 'summary') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'summary')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="description">Mô tả chi tiết</label>
                            <textarea id="description" name="description" rows="5" class="form-control glass-textarea <?= field_error($productState, 'description') !== '' ? 'is-invalid-soft' : '' ?>"><?= h(field_value($productState, 'description')) ?></textarea>
                            <?php if (field_error($productState, 'description') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'description')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="price">Giá bán</label>
                            <input id="price" name="price" type="number" class="form-control glass-input <?= field_error($productState, 'price') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($productState, 'price')) ?>">
                            <?php if (field_error($productState, 'price') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'price')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="old_price">Giá gốc</label>
                            <input id="old_price" name="old_price" type="number" class="form-control glass-input <?= field_error($productState, 'old_price') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($productState, 'old_price')) ?>">
                            <?php if (field_error($productState, 'old_price') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'old_price')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="stock">Tồn kho</label>
                            <input id="stock" name="stock" type="number" class="form-control glass-input <?= field_error($productState, 'stock') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($productState, 'stock', '0')) ?>">
                            <?php if (field_error($productState, 'stock') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'stock')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="rating">Đánh giá</label>
                            <input id="rating" name="rating" type="number" step="0.1" min="0" max="5" class="form-control glass-input" value="<?= h(field_value($productState, 'rating', '4.5')) ?>">
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1" <?= field_value($productState, 'featured') === '1' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="featured">Đánh dấu nổi bật</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="tags">Tags</label>
                            <input id="tags" name="tags" class="form-control glass-input" value="<?= h(field_value($productState, 'tags')) ?>" placeholder="RTX, 144Hz, Streaming">
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="cover_image">URL ảnh chính</label>
                            <input id="cover_image" name="cover_image" class="form-control glass-input <?= field_error($productState, 'cover_image') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($productState, 'cover_image')) ?>">
                            <?php if (field_error($productState, 'cover_image') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'cover_image')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="accent_image">URL ảnh phụ</label>
                            <input id="accent_image" name="accent_image" class="form-control glass-input <?= field_error($productState, 'accent_image') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($productState, 'accent_image')) ?>">
                            <?php if (field_error($productState, 'accent_image') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'accent_image')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="specs_text">Thông số kỹ thuật</label>
                            <textarea id="specs_text" name="specs_text" rows="7" class="form-control glass-textarea <?= field_error($productState, 'specs_text') !== '' ? 'is-invalid-soft' : '' ?>" placeholder="CPU: Intel Core i7&#10;GPU: RTX 4070 Super&#10;RAM: 32GB DDR5"><?= h(field_value($productState, 'specs_text')) ?></textarea>
                            <?php if (field_error($productState, 'specs_text') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'specs_text')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="features_text">Điểm nổi bật</label>
                            <textarea id="features_text" name="features_text" rows="7" class="form-control glass-textarea <?= field_error($productState, 'features_text') !== '' ? 'is-invalid-soft' : '' ?>" placeholder="Tốt cho stream&#10;Airflow rộng&#10;Dễ nâng cấp"><?= h(field_value($productState, 'features_text')) ?></textarea>
                            <?php if (field_error($productState, 'features_text') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'features_text')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button class="btn btn-brand btn-lg" type="submit">Lưu sản phẩm</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="order-panel sticky-panel">
                    <p class="eyebrow mb-2">mới cập nhật</p>
                    <h2 class="h4 mb-3">Sản phẩm vừa có trong danh mục</h2>
                    <div class="d-grid gap-3">
                        <?php foreach ($recentProducts as $product): ?>
                            <div class="history-item">
                                <div class="d-flex gap-3">
                                    <img src="<?= h((string) $product['cover_image']) ?>" alt="<?= h((string) $product['name']) ?>" style="width:88px;height:72px;object-fit:cover;border-radius:8px;">
                                    <div class="min-w-0">
                                        <div class="d-flex flex-wrap gap-2 mb-1">
                                            <span class="tag-pill"><?= h((string) $product['category_name']) ?></span>
                                            <span class="stock-pill <?= (int) $product['stock'] > 0 ? 'is-stock' : '' ?>"><?= (int) $product['stock'] > 0 ? 'Còn hàng' : 'Hết hàng' ?></span>
                                        </div>
                                        <strong class="d-block text-truncate"><?= h((string) $product['name']) ?></strong>
                                        <div class="text-soft small"><?= money((int) $product['price']) ?> · <?= h((string) $product['slug']) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
