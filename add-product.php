<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

require_admin();

$activeNav = 'admin';
$categories = site_categories();
$editId = max(0, (int) ($_GET['edit'] ?? 0));
$editingProduct = $editId > 0 ? product_by_id($editId) : null;

if ($editId > 0 && $editingProduct === null) {
    set_flash('danger', 'Không tìm thấy sản phẩm cần sửa.');
    redirect_to('add-product.php');
}

$isEditing = $editingProduct !== null;
$pageTitle = $isEditing ? 'Sửa sản phẩm' : 'Thêm sản phẩm';
$productState = $isEditing
    ? pull_form_state('product_update_' . (int) $editingProduct['id'])
    : pull_form_state('product_create');

if ($isEditing && ($productState['old'] ?? []) === []) {
    $productState['old'] = product_form_old_from_product($editingProduct);
}

$recentProducts = recent_products(12);
$currentCover = field_value($productState, 'cover_image');
$currentAccentImages = product_accent_images(['accent_image' => field_value($productState, 'accent_image')]);

include __DIR__ . '/includes/header.php';
?>

<section class="section-space">
    <div class="container-xxl">
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="profile-panel">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <p class="eyebrow mb-2">quản trị sản phẩm</p>
                            <h1 class="h2 mb-2"><?= $isEditing ? 'Sửa thông tin sản phẩm' : 'Thêm sản phẩm mới' ?></h1>
                            <p class="result-count mb-0">
                                <?= $isEditing
                                    ? 'Cập nhật thông tin, giá, tồn kho, ảnh và thông số. Không chọn ảnh mới thì hệ thống giữ ảnh hiện tại.'
                                    : 'Sản phẩm mới được lưu vào MySQL và hiển thị ngay trên trang chủ, danh mục sản phẩm.' ?>
                            </p>
                        </div>
                        <?php if ($isEditing): ?>
                            <a class="btn btn-outline-dark btn-soft" href="add-product.php">Thêm sản phẩm mới</a>
                        <?php endif; ?>
                    </div>

                    <form action="" method="post" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" name="action" value="<?= $isEditing ? 'update_product' : 'create_product' ?>">
                        <?php if ($isEditing): ?>
                            <input type="hidden" name="product_id" value="<?= (int) $editingProduct['id'] ?>">
                        <?php endif; ?>

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
                            <input id="price" name="price" type="number" min="0" class="form-control glass-input <?= field_error($productState, 'price') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($productState, 'price')) ?>">
                            <?php if (field_error($productState, 'price') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'price')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="old_price">Giá gốc</label>
                            <input id="old_price" name="old_price" type="number" min="0" class="form-control glass-input <?= field_error($productState, 'old_price') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($productState, 'old_price')) ?>">
                            <?php if (field_error($productState, 'old_price') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'old_price')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="stock">Tồn kho</label>
                            <input id="stock" name="stock" type="number" min="0" class="form-control glass-input <?= field_error($productState, 'stock') !== '' ? 'is-invalid-soft' : '' ?>" value="<?= h(field_value($productState, 'stock', '0')) ?>">
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
                            <label class="form-label" for="cover_file">Ảnh chính <?= $isEditing ? '(chọn file mới nếu muốn thay)' : '(1 ảnh)' ?></label>
                            <?php if ($currentCover !== ''): ?>
                                <div class="admin-current-media mb-2">
                                    <img src="<?= h($currentCover) ?>" alt="Ảnh chính hiện tại">
                                    <span>Ảnh chính hiện tại</span>
                                </div>
                            <?php endif; ?>
                            <input id="cover_file" name="cover_file" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="form-control glass-input <?= field_error($productState, 'cover_image') !== '' ? 'is-invalid-soft' : '' ?>">
                            <?php if (field_error($productState, 'cover_image') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'cover_image')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="accent_files">Ảnh phụ <?= $isEditing ? '(chọn file mới để thay toàn bộ ảnh phụ)' : '(có thể chọn nhiều ảnh)' ?></label>
                            <?php if ($currentAccentImages !== []): ?>
                                <div class="admin-current-gallery mb-2">
                                    <?php foreach ($currentAccentImages as $image): ?>
                                        <img src="<?= h($image) ?>" alt="Ảnh phụ hiện tại">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <input id="accent_files" name="accent_files[]" type="file" multiple accept="image/jpeg,image/png,image/webp,image/gif" class="form-control glass-input <?= field_error($productState, 'accent_image') !== '' ? 'is-invalid-soft' : '' ?>">
                            <?php if (field_error($productState, 'accent_image') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'accent_image')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Thông số kỹ thuật</label>
                            <div class="product-list-builder" data-spec-builder>
                                <div class="spec-builder-list" data-spec-list></div>
                                <button class="btn btn-outline-dark btn-soft btn-sm" type="button" data-add-spec>Thêm thông số</button>
                            </div>
                            <textarea id="specs_text" name="specs_text" class="d-none <?= field_error($productState, 'specs_text') !== '' ? 'is-invalid-soft' : '' ?>" data-spec-output><?= h(field_value($productState, 'specs_text')) ?></textarea>
                            <?php if (field_error($productState, 'specs_text') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'specs_text')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Điểm nổi bật</label>
                            <div class="product-list-builder" data-feature-builder>
                                <div class="feature-builder-list" data-feature-list></div>
                                <button class="btn btn-outline-dark btn-soft btn-sm" type="button" data-add-feature>Thêm điểm nổi bật</button>
                            </div>
                            <textarea id="features_text" name="features_text" class="d-none <?= field_error($productState, 'features_text') !== '' ? 'is-invalid-soft' : '' ?>" data-feature-output><?= h(field_value($productState, 'features_text')) ?></textarea>
                            <?php if (field_error($productState, 'features_text') !== ''): ?><span class="field-error"><?= h(field_error($productState, 'features_text')) ?></span><?php endif; ?>
                        </div>

                        <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
                            <?php if ($isEditing): ?>
                                <a class="btn btn-outline-dark btn-soft btn-lg" href="add-product.php">Huỷ sửa</a>
                            <?php endif; ?>
                            <button class="btn btn-brand btn-lg" type="submit"><?= $isEditing ? 'Lưu thay đổi' : 'Lưu sản phẩm' ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="order-panel sticky-panel">
                    <p class="eyebrow mb-2">danh sách quản trị</p>
                    <h2 class="h4 mb-3">Sản phẩm gần đây</h2>
                    <div class="d-grid gap-3">
                        <?php foreach ($recentProducts as $product): ?>
                            <div class="history-item <?= $isEditing && (int) $editingProduct['id'] === (int) $product['id'] ? 'is-editing' : '' ?>">
                                <div class="d-flex gap-3 align-items-start">
                                    <img src="<?= h((string) $product['cover_image']) ?>" alt="<?= h((string) $product['name']) ?>" class="admin-product-thumb">
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="d-flex flex-wrap gap-2 mb-1">
                                            <span class="tag-pill"><?= h((string) $product['category_name']) ?></span>
                                            <span class="stock-pill <?= (int) $product['stock'] > 0 ? 'is-stock' : '' ?>"><?= (int) $product['stock'] > 0 ? 'Còn hàng' : 'Hết hàng' ?></span>
                                        </div>
                                        <strong class="d-block text-truncate"><?= h((string) $product['name']) ?></strong>
                                        <div class="text-soft small"><?= money((int) $product['price']) ?> · <?= h((string) $product['slug']) ?></div>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <a class="btn btn-outline-dark btn-soft btn-sm" href="add-product.php?edit=<?= (int) $product['id'] ?>">Sửa</a>
                                            <form action="" method="post" onsubmit="return confirm('Xóa sản phẩm này?');">
                                                <input type="hidden" name="action" value="delete_product">
                                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                                <button class="btn btn-outline-danger btn-soft btn-sm" type="submit">Xóa</button>
                                            </form>
                                        </div>
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
