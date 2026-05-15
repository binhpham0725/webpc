<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Danh mục';
$activeNav = 'products';
$categories = site_categories();
$filters = product_filters_from_query();
$products = filtered_products($filters);
$tags = site_tags();
$currentCategory = null;

foreach ($categories as $category) {
    if ($category['slug'] === $filters['category']) {
        $currentCategory = $category;
        break;
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="section-space">
    <div class="container-xxl">
        <div class="row g-4 align-items-start">
            <div class="col-lg-3">
                <aside class="sidebar-panel sticky-panel">
                    <p class="eyebrow mb-2">phân loại</p>
                    <h2 class="h4 mb-3">Lọc theo nhu cầu</h2>

                    <div class="d-grid gap-2 mb-4">
                        <?php foreach ($categories as $category): ?>
                            <a class="filter-link <?= $filters['category'] === $category['slug'] ? 'is-active' : '' ?>" href="products.php?<?= http_build_query(array_filter(['category' => $category['slug'], 'q' => $filters['q'], 'tag' => $filters['tag'], 'stock' => $filters['stock'], 'sort' => $filters['sort']])) ?>">
                                <span><?= h((string) $category['name']) ?></span>
                            </a>
                        <?php endforeach; ?>
                        <a class="filter-link <?= $filters['category'] === '' ? 'is-active' : '' ?>" href="products.php">Tất cả</a>
                    </div>

                    <div class="mb-4">
                        <p class="fw-semibold mb-2">Tag nhanh</p>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($tags as $tag): ?>
                                <a class="chip-link <?= $filters['tag'] === $tag ? 'is-active' : '' ?>" href="products.php?<?= http_build_query(array_filter(['category' => $filters['category'], 'q' => $filters['q'], 'tag' => $tag, 'stock' => $filters['stock'], 'sort' => $filters['sort']])) ?>">
                                    <?= h($tag) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <form method="get" action="products.php" class="d-grid gap-3">
                        <input type="hidden" name="category" value="<?= h($filters['category']) ?>">
                        <input type="hidden" name="tag" value="<?= h($filters['tag']) ?>">
                        <div>
                            <label class="form-label" for="q">Từ khóa</label>
                            <input id="q" name="q" value="<?= h($filters['q']) ?>" class="form-control glass-input" placeholder="RTX 4070, SSD, văn phòng, màn hình...">
                        </div>
                        <div>
                            <label class="form-label" for="sort">Sắp xếp</label>
                            <select id="sort" name="sort" class="form-select glass-select">
                                <option value="featured" <?= $filters['sort'] === 'featured' ? 'selected' : '' ?>>Nổi bật</option>
                                <option value="price-asc" <?= $filters['sort'] === 'price-asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                                <option value="price-desc" <?= $filters['sort'] === 'price-desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                                <option value="sale" <?= $filters['sort'] === 'sale' ? 'selected' : '' ?>>Giảm giá mạnh</option>
                                <option value="rating" <?= $filters['sort'] === 'rating' ? 'selected' : '' ?>>Đánh giá cao</option>
                            </select>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="stock" name="stock" <?= $filters['stock'] === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="stock">Chỉ hiện sản phẩm còn hàng</label>
                        </div>
                        <button class="btn btn-brand" type="submit">Áp dụng</button>
                        <a class="btn btn-outline-dark btn-soft" href="products.php">Xóa lọc</a>
                    </form>
                </aside>
            </div>

            <div class="col-lg-9">
                <div class="detail-panel">
                    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                        <div>
                            <p class="eyebrow mb-2">danh mục</p>
                            <h1 class="h2 mb-2">PC văn phòng, PC gaming, thiết bị và phụ kiện công nghệ</h1>
                            <p class="result-count mb-0">
                                <?= count($products) ?> sản phẩm phù hợp
                                <?php if ($filters['q'] !== ''): ?>
                                    với từ khóa "<?= h($filters['q']) ?>"
                                <?php endif; ?>
                                <?php if ($filters['category'] !== ''): ?>
                                    trong nhóm "<?= h((string) ($currentCategory['name'] ?? $filters['category'])) ?>"
                                <?php endif; ?>
                            </p>
                        </div>

                        <?php if (is_admin()): ?>
                            <a class="btn btn-outline-dark btn-soft" href="add-product.php">Thêm sản phẩm</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <?php if ($products === []): ?>
                        <div class="col-12">
                            <div class="empty-panel">Không có sản phẩm khớp bộ lọc hiện tại.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <div class="col-xl-4 col-md-6">
                                <?= render_product_card($product, 'products.php?' . http_build_query($_GET)) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
