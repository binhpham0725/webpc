<?php

declare(strict_types=1);

/**
 * Core helpers for database access, auth, product queries, cart state, and UI rendering.
 */

function base_path(string $path = ''): string
{
    $root = dirname(__DIR__);
    return $path !== ''
        ? $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path)
        : $root;
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO('sqlite:' . base_path('database/webpc.sqlite'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON;');

    return $pdo;
}

function table_exists(string $table): bool
{
    $stmt = db()->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :name LIMIT 1");
    $stmt->execute(['name' => $table]);
    return $stmt->fetchColumn() !== false;
}

function column_exists(string $table, string $column): bool
{
    $stmt = db()->query('PRAGMA table_info(' . $table . ')');
    $columns = $stmt->fetchAll();

    foreach ($columns as $info) {
        if (($info['name'] ?? '') === $column) {
            return true;
        }
    }

    return false;
}

function initialize_database(PDO $pdo): void
{
    if (!table_exists('products')) {
        $schema = file_get_contents(base_path('database/schema.sql'));
        if ($schema === false) {
            throw new RuntimeException('Cannot read schema.sql');
        }

        $pdo->exec($schema);
    }

    ensure_runtime_schema($pdo);
}

function ensure_runtime_schema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            role TEXT NOT NULL DEFAULT 'customer',
            full_name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            phone TEXT NOT NULL DEFAULT '',
            company TEXT NOT NULL DEFAULT '',
            address TEXT NOT NULL DEFAULT '',
            city TEXT NOT NULL DEFAULT '',
            note TEXT NOT NULL DEFAULT '',
            avatar_label TEXT NOT NULL DEFAULT 'WP',
            avatar_url TEXT NOT NULL DEFAULT '',
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )"
    );

    if (!column_exists('payment_methods', 'user_id')) {
        $pdo->exec('ALTER TABLE payment_methods ADD COLUMN user_id INTEGER NOT NULL DEFAULT 1');
    }

    if (!column_exists('orders', 'user_id')) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN user_id INTEGER DEFAULT NULL');
    }

    if (!column_exists('service_requests', 'user_id')) {
        $pdo->exec('ALTER TABLE service_requests ADD COLUMN user_id INTEGER DEFAULT NULL');
    }

    seed_default_admin($pdo);
    normalize_demo_copy($pdo);
}

function seed_default_admin(PDO $pdo): void
{
    $adminEmail = 'admin@webpc.local';
    $existing = fetch_one('SELECT id FROM users WHERE email = :email LIMIT 1', ['email' => $adminEmail]);

    if ($existing === null) {
        $now = date('Y-m-d H:i:s');
        execute_query(
            'INSERT INTO users (
                role, full_name, email, password_hash, phone, company, address, city, note,
                avatar_label, avatar_url, created_at, updated_at
            ) VALUES (
                :role, :full_name, :email, :password_hash, :phone, :company, :address, :city, :note,
                :avatar_label, :avatar_url, :created_at, :updated_at
            )',
            [
                'role' => 'admin',
                'full_name' => 'Admin WebPC',
                'email' => $adminEmail,
                'password_hash' => password_hash('Admin@12345', PASSWORD_DEFAULT),
                'phone' => '0900000000',
                'company' => 'WebPC',
                'address' => '1 Green Glass St',
                'city' => 'Ha Noi',
                'note' => 'Tài khoản admin demo cho khu vực quản trị.',
                'avatar_label' => 'AW',
                'avatar_url' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    $admin = fetch_one('SELECT id FROM users WHERE email = :email LIMIT 1', ['email' => $adminEmail]);
    if ($admin === null) {
        return;
    }

    $adminId = (int) $admin['id'];
    execute_query('UPDATE payment_methods SET user_id = :user_id WHERE user_id IS NULL OR user_id = 0', ['user_id' => $adminId]);
    execute_query('UPDATE orders SET user_id = :user_id WHERE user_id IS NULL OR user_id = 0', ['user_id' => $adminId]);
    execute_query('UPDATE service_requests SET user_id = :user_id WHERE user_id IS NULL OR user_id = 0', ['user_id' => $adminId]);
}

function normalize_demo_copy(PDO $pdo): void
{
    execute_query("UPDATE categories SET name = 'PC văn phòng' WHERE slug = 'office'");
    execute_query("UPDATE categories SET name = 'PC gaming' WHERE slug = 'gaming'");
    execute_query("UPDATE categories SET name = 'Thiết bị' WHERE slug = 'gear'");
    execute_query("UPDATE categories SET name = 'Phụ kiện công nghệ' WHERE slug = 'accessory'");

    execute_query(
        "UPDATE users
         SET city = 'Hà Nội', note = 'Tài khoản admin demo cho khu vực quản trị.'
         WHERE email = 'admin@webpc.local' AND (city = 'Ha Noi' OR city = 'Hà Nội' OR note = 'Tai khoan admin demo cho khu vuc quan tri.' OR note = 'Tài khoản admin demo cho khu vực quản trị.')"
    );

    execute_query("UPDATE payment_methods SET bank_name = 'Ngân hàng ABC' WHERE bank_name = 'Ngan hang ABC'");
    execute_query("UPDATE payment_methods SET bank_name = 'Ngân hàng Tech' WHERE bank_name = 'Ngan hang Tech'");
    execute_query("UPDATE payment_methods SET note = 'Tài khoản demo cho admin.' WHERE note = 'Tai khoan demo cho admin.'");
    execute_query("UPDATE payment_methods SET note = 'Ưu tiên thanh toán QR và chuyển khoản.' WHERE note = 'Uu tien thanh toan QR va chuyen khoan'");

    execute_query("UPDATE orders SET status = 'Đang xử lý' WHERE status = 'Dang xu ly'");
    execute_query("UPDATE orders SET status = 'Đang xác nhận' WHERE status = 'Dang xac nhan'");
    execute_query("UPDATE service_requests SET status = 'Mới tiếp nhận' WHERE status = 'Moi tiep nhan'");
}

function bind_and_execute(PDOStatement $stmt, array $params = []): void
{
    foreach ($params as $key => $value) {
        $stmt->bindValue(
            is_string($key) ? ':' . ltrim($key, ':') : $key + 1,
            $value,
            is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
        );
    }

    $stmt->execute();
}

function fetch_all(string $sql, array $params = []): array
{
    $stmt = db()->prepare($sql);
    bind_and_execute($stmt, $params);
    return $stmt->fetchAll();
}

function fetch_one(string $sql, array $params = []): ?array
{
    $stmt = db()->prepare($sql);
    bind_and_execute($stmt, $params);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function execute_query(string $sql, array $params = []): bool
{
    $stmt = db()->prepare($sql);
    bind_and_execute($stmt, $params);
    return true;
}

function app_title(?string $pageTitle = null): string
{
    return $pageTitle ? $pageTitle . ' | webpc' : 'webpc';
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money(int|float $amount): string
{
    return number_format((float) $amount, 0, ',', '.') . ' đ';
}

function valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function valid_phone(string $phone): bool
{
    $normalized = preg_replace('/\s+/', '', $phone);
    return is_string($normalized) && preg_match('/^\d{10,11}$/', $normalized) === 1;
}

function valid_image_url(string $url): bool
{
    if ($url === '') {
        return false;
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    return preg_match('/\.(jpg|jpeg|png|webp|gif)(\?.*)?$/i', $url) === 1
        || str_contains($url, 'images.unsplash.com')
        || str_contains($url, 'unsplash.com');
}

function set_flash(string $type, string $message): void
{
    $_SESSION['webpc_flashes'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pull_flashes(): array
{
    $flashes = $_SESSION['webpc_flashes'] ?? [];
    unset($_SESSION['webpc_flashes']);
    return $flashes;
}

function set_form_state(string $form, array $errors, array $old): void
{
    $_SESSION['webpc_forms'][$form] = [
        'errors' => $errors,
        'old' => $old,
    ];
}

function pull_form_state(string $form): array
{
    $state = $_SESSION['webpc_forms'][$form] ?? [
        'errors' => [],
        'old' => [],
    ];

    unset($_SESSION['webpc_forms'][$form]);
    return $state;
}

function safe_redirect_path(string $path, string $fallback = 'index.php'): string
{
    $path = trim($path);
    if ($path === '') {
        return $fallback;
    }

    if (str_contains($path, '://') || str_starts_with($path, '//') || str_contains($path, '..')) {
        return $fallback;
    }

    if (preg_match('/^[A-Za-z0-9._\\-\\/\\?=&%]+$/', $path) !== 1) {
        return $fallback;
    }

    return $path;
}

function redirect_to(string $path): never
{
    header('Location: ' . safe_redirect_path($path));
    exit;
}

function current_request_path(): string
{
    $script = basename((string) ($_SERVER['PHP_SELF'] ?? 'index.php'));
    $query = (string) ($_SERVER['QUERY_STRING'] ?? '');
    return $query !== '' ? $script . '?' . $query : $script;
}

function field_value(array $state, string $field, string $fallback = ''): string
{
    return (string) ($state['old'][$field] ?? $fallback);
}

function field_error(array $state, string $field): string
{
    return (string) ($state['errors'][$field] ?? '');
}

function is_active_nav(string $current, string $expected): bool
{
    return $current === $expected;
}

function mb_head(string $value): string
{
    if ($value === '') {
        return '';
    }

    if (function_exists('mb_substr')) {
        return (string) mb_substr($value, 0, 1);
    }

    return substr($value, 0, 1);
}

function mb_up(string $value): string
{
    if (function_exists('mb_strtoupper')) {
        return (string) mb_strtoupper($value);
    }

    return strtoupper($value);
}

function initials_from_name(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $parts = array_values(array_filter($parts, static fn ($part) => $part !== ''));

    if ($parts === []) {
        return 'WP';
    }

    $first = mb_head($parts[0]);
    $last = count($parts) > 1 ? mb_head($parts[count($parts) - 1]) : '';
    $initials = trim($first . $last);

    return $initials !== '' ? mb_up($initials) : 'WP';
}

function discount_percent(array $product): int
{
    $price = (int) ($product['price'] ?? 0);
    $oldPrice = (int) ($product['old_price'] ?? 0);

    if ($price <= 0 || $oldPrice <= $price) {
        return 0;
    }

    return (int) round((1 - ($price / $oldPrice)) * 100);
}

function slugify(string $text): string
{
    $text = trim($text);
    if ($text === '') {
        return 'san-pham-' . time();
    }

    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if (is_string($ascii) && $ascii !== '') {
        $text = $ascii;
    }

    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');

    return $text !== '' ? $text : 'san-pham-' . time();
}

function decode_json_column(?string $json): array
{
    if ($json === null || trim($json) === '') {
        return [];
    }

    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function product_tags(array $product): array
{
    return array_values(array_filter(array_map('trim', explode(',', (string) ($product['tags'] ?? '')))));
}

function specs_from_text(string $raw): array
{
    $specs = [];
    $lines = preg_split('/\r\n|\r|\n/', trim($raw)) ?: [];

    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === '') {
            continue;
        }

        $parts = explode(':', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $label = trim($parts[0]);
        $value = trim($parts[1]);
        if ($label !== '' && $value !== '') {
            $specs[$label] = $value;
        }
    }

    return $specs;
}

function lines_to_array(string $raw): array
{
    $items = [];
    $lines = preg_split('/\r\n|\r|\n/', trim($raw)) ?: [];

    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line !== '') {
            $items[] = $line;
        }
    }

    return $items;
}

function current_user_id(): ?int
{
    $id = (int) ($_SESSION['webpc_user_id'] ?? 0);
    return $id > 0 ? $id : null;
}

function current_user(): ?array
{
    static $cache = null;
    static $loadedId = null;

    $id = current_user_id();
    if ($id === null) {
        return null;
    }

    if ($cache !== null && $loadedId === $id) {
        return $cache;
    }

    $cache = fetch_one('SELECT * FROM users WHERE id = :id LIMIT 1', ['id' => $id]);
    $loadedId = $id;

    if ($cache === null) {
        unset($_SESSION['webpc_user_id']);
    }

    return $cache;
}

function login_user(array $user): void
{
    $_SESSION['webpc_user_id'] = (int) $user['id'];
}

function logout_current_user(): void
{
    unset($_SESSION['webpc_user_id']);
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && ($user['role'] ?? '') === 'admin';
}

function require_login(): void
{
    if (is_logged_in()) {
        return;
    }

    set_flash('danger', 'Đăng nhập để tiếp tục.');
    redirect_to('login.php?next=' . urlencode(current_request_path()));
}

function require_admin(): void
{
    if (is_admin()) {
        return;
    }

    if (!is_logged_in()) {
        set_flash('danger', 'Đăng nhập bằng tài khoản admin để tiếp tục.');
        redirect_to('login.php?next=' . urlencode(current_request_path()));
    }

    set_flash('danger', 'Chỉ tài khoản admin mới được dùng chức năng này.');
    redirect_to('account.php');
}

function current_profile_defaults(): array
{
    $user = current_user();
    if ($user !== null) {
        return [
            'full_name' => (string) $user['full_name'],
            'email' => (string) $user['email'],
            'phone' => (string) $user['phone'],
            'company' => (string) $user['company'],
            'address' => (string) $user['address'],
            'city' => (string) $user['city'],
            'note' => (string) $user['note'],
            'avatar_label' => (string) $user['avatar_label'],
            'avatar_url' => (string) $user['avatar_url'],
        ];
    }

    return [
        'full_name' => '',
        'email' => '',
        'phone' => '',
        'company' => '',
        'address' => '',
        'city' => '',
        'note' => '',
        'avatar_label' => 'WP',
        'avatar_url' => '',
    ];
}

function profile_record(): array
{
    return current_profile_defaults();
}

function site_categories(): array
{
    return fetch_all('SELECT * FROM categories ORDER BY sort_order ASC, name ASC');
}

function site_services(): array
{
    return fetch_all('SELECT * FROM services ORDER BY sort_order ASC, title ASC');
}

function featured_products(int $limit = 8): array
{
    return fetch_all(
        'SELECT p.*, c.slug AS category_slug, c.name AS category_name
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE p.featured = 1
         ORDER BY p.sort_order ASC, p.rating DESC
         LIMIT :limit',
        ['limit' => $limit]
    );
}

function recent_products(int $limit = 8): array
{
    return fetch_all(
        'SELECT p.*, c.slug AS category_slug, c.name AS category_name
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         ORDER BY p.id DESC
         LIMIT :limit',
        ['limit' => $limit]
    );
}

function product_filters_from_query(): array
{
    return [
        'category' => trim((string) ($_GET['category'] ?? '')),
        'q' => trim((string) ($_GET['q'] ?? '')),
        'tag' => trim((string) ($_GET['tag'] ?? '')),
        'stock' => isset($_GET['stock']) ? (string) $_GET['stock'] : '',
        'sort' => trim((string) ($_GET['sort'] ?? 'featured')),
    ];
}

function site_tags(): array
{
    $rows = fetch_all('SELECT tags FROM products');
    $tags = [];

    foreach ($rows as $row) {
        foreach (explode(',', (string) $row['tags']) as $tag) {
            $tag = trim($tag);
            if ($tag !== '') {
                $tags[$tag] = true;
            }
        }
    }

    $list = array_keys($tags);
    sort($list);
    return $list;
}

function filtered_products(array $filters): array
{
    $sql = 'SELECT p.*, c.slug AS category_slug, c.name AS category_name
            FROM products p
            INNER JOIN categories c ON c.id = p.category_id
            WHERE 1 = 1';
    $params = [];

    if ($filters['category'] !== '') {
        $sql .= ' AND c.slug = :category';
        $params['category'] = $filters['category'];
    }

    if ($filters['q'] !== '') {
        $sql .= ' AND (
            p.name LIKE :search
            OR p.summary LIKE :search
            OR p.description LIKE :search
            OR p.tags LIKE :search
            OR c.name LIKE :search
        )';
        $params['search'] = '%' . $filters['q'] . '%';
    }

    if ($filters['tag'] !== '') {
        $sql .= ' AND p.tags LIKE :tag';
        $params['tag'] = '%' . $filters['tag'] . '%';
    }

    if ($filters['stock'] === '1') {
        $sql .= ' AND p.stock > 0';
    }

    $sort = match ($filters['sort']) {
        'price-asc' => 'p.price ASC, p.rating DESC',
        'price-desc' => 'p.price DESC, p.rating DESC',
        'sale' => '(p.old_price - p.price) DESC, p.rating DESC',
        'rating' => 'p.rating DESC, p.sort_order ASC',
        default => 'p.featured DESC, p.sort_order ASC, p.rating DESC',
    };

    $sql .= ' ORDER BY ' . $sort;

    return fetch_all($sql, $params);
}

function product_by_slug(string $slug): ?array
{
    return fetch_one(
        'SELECT p.*, c.slug AS category_slug, c.name AS category_name
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE p.slug = :slug
         LIMIT 1',
        ['slug' => $slug]
    );
}

function product_by_id(int $id): ?array
{
    return fetch_one(
        'SELECT p.*, c.slug AS category_slug, c.name AS category_name
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE p.id = :id
         LIMIT 1',
        ['id' => $id]
    );
}

function product_exists_by_slug(string $slug): bool
{
    return fetch_one('SELECT id FROM products WHERE slug = :slug LIMIT 1', ['slug' => $slug]) !== null;
}

function related_products(array $product, int $limit = 4): array
{
    return fetch_all(
        'SELECT p.*, c.slug AS category_slug, c.name AS category_name
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE p.category_id = :category_id AND p.id != :id
         ORDER BY p.featured DESC, p.rating DESC
         LIMIT :limit',
        [
            'category_id' => (int) $product['category_id'],
            'id' => (int) $product['id'],
            'limit' => $limit,
        ]
    );
}

function payment_methods(?int $userId = null): array
{
    $userId = $userId ?? current_user_id();
    if ($userId === null) {
        return [];
    }

    return fetch_all(
        'SELECT * FROM payment_methods WHERE user_id = :user_id ORDER BY created_at DESC, id DESC',
        ['user_id' => $userId]
    );
}

function recent_orders(int $limit = 5, ?int $userId = null): array
{
    if ($userId === null) {
        return fetch_all('SELECT * FROM orders ORDER BY created_at DESC, id DESC LIMIT :limit', ['limit' => $limit]);
    }

    return fetch_all(
        'SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC, id DESC LIMIT :limit',
        ['user_id' => $userId, 'limit' => $limit]
    );
}

function recent_service_requests(int $limit = 5, ?int $userId = null): array
{
    $sql = 'SELECT sr.*, s.title AS service_title
            FROM service_requests sr
            LEFT JOIN services s ON s.id = sr.service_id';
    $params = ['limit' => $limit];

    if ($userId !== null) {
        $sql .= ' WHERE sr.user_id = :user_id';
        $params['user_id'] = $userId;
    }

    $sql .= ' ORDER BY sr.created_at DESC, sr.id DESC LIMIT :limit';

    return fetch_all($sql, $params);
}

function cart(): array
{
    return $_SESSION['webpc_cart'] ?? [];
}

function save_cart(array $cart): void
{
    $_SESSION['webpc_cart'] = $cart;
}

function add_product_to_cart(int $productId, int $quantity): void
{
    $current = cart();
    $current[$productId] = ($current[$productId] ?? 0) + $quantity;
    save_cart($current);
}

function update_cart_quantities(array $quantities): void
{
    $updated = [];

    foreach ($quantities as $productId => $quantity) {
        $id = (int) $productId;
        $qty = max(0, (int) $quantity);

        if ($id <= 0 || $qty <= 0) {
            continue;
        }

        $product = product_by_id($id);
        if ($product !== null) {
            $updated[$id] = min($qty, max(1, (int) $product['stock']));
        }
    }

    save_cart($updated);
}

function remove_cart_item(int $productId): void
{
    $current = cart();
    unset($current[$productId]);
    save_cart($current);
}

function set_cart_item_quantity(int $productId, int $quantity): void
{
    $current = cart();

    if ($quantity <= 0) {
        unset($current[$productId]);
        save_cart($current);
        return;
    }

    $product = product_by_id($productId);
    if ($product === null) {
        return;
    }

    $current[$productId] = min($quantity, max(1, (int) $product['stock']));
    save_cart($current);
}

function clear_cart(): void
{
    unset($_SESSION['webpc_cart']);
}

function cart_items(): array
{
    $items = [];

    foreach (cart() as $productId => $quantity) {
        $product = product_by_id((int) $productId);
        if ($product === null) {
            continue;
        }

        $items[] = [
            'product' => $product,
            'quantity' => (int) $quantity,
            'line_total' => ((int) $product['price']) * (int) $quantity,
            'line_saving' => max(0, (((int) $product['old_price']) - ((int) $product['price'])) * (int) $quantity),
        ];
    }

    return $items;
}

function cart_totals(array $items): array
{
    $subtotal = 0;
    $saving = 0;

    foreach ($items as $item) {
        $subtotal += (int) $item['line_total'];
        $saving += (int) $item['line_saving'];
    }

    $shipping = $subtotal >= 30000000 ? 0 : ($subtotal > 0 ? 120000 : 0);
    $discount = 0;
    $couponCode = strtoupper(trim((string) ($_SESSION['webpc_coupon'] ?? '')));

    if ($couponCode === 'NVIDIA') {
        $discount = (int) min(500000, floor($subtotal * 0.03));
    }

    return [
        'subtotal' => $subtotal,
        'saving' => $saving,
        'shipping' => $shipping,
        'discount' => $discount,
        'total' => max(0, $subtotal + $shipping - $discount),
    ];
}

function render_product_card(array $product, string $redirectPage): string
{
    $discount = discount_percent($product);
    $tags = product_tags($product);

    ob_start();
    ?>
    <article class="product-card card h-100 border-0 glass-card">
        <a href="product.php?slug=<?= h((string) $product['slug']) ?>" class="product-media-wrap">
            <img class="card-img-top product-media" src="<?= h((string) $product['cover_image']) ?>" alt="<?= h((string) $product['name']) ?>">
        </a>
        <div class="card-body d-flex flex-column gap-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="tag-pill"><?= h((string) $product['category_name']) ?></span>
                <?php if ($discount > 0): ?>
                    <span class="sale-pill">-<?= $discount ?>%</span>
                <?php endif; ?>
                <span class="stock-pill <?= (int) $product['stock'] > 0 ? 'is-stock' : '' ?>">
                    <?= (int) $product['stock'] > 0 ? 'Còn ' . (int) $product['stock'] : 'Hết hàng' ?>
                </span>
            </div>

            <div>
                <h3 class="h5 product-title">
                    <a href="product.php?slug=<?= h((string) $product['slug']) ?>"><?= h((string) $product['name']) ?></a>
                </h3>
                <p class="text-secondary product-summary mb-0"><?= h((string) $product['summary']) ?></p>
            </div>

            <div class="d-flex flex-wrap gap-2 small text-secondary">
                <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                    <span><?= h($tag) ?></span>
                <?php endforeach; ?>
                <span><?= h((string) $product['rating']) ?>/5</span>
            </div>

            <div class="mt-auto">
                <div class="d-flex align-items-end gap-2 flex-wrap mb-3">
                    <strong class="price-current"><?= money((int) $product['price']) ?></strong>
                    <span class="price-old"><?= money((int) $product['old_price']) ?></span>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <form action="" method="post" class="d-inline-flex">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="redirect" value="<?= h($redirectPage) ?>">
                        <button class="btn btn-brand" type="submit">Thêm vào giỏ</button>
                    </form>
                    <a class="btn btn-outline-dark btn-soft" href="product.php?slug=<?= h((string) $product['slug']) ?>">Chi tiết</a>
                </div>
            </div>
        </div>
    </article>
    <?php

    return (string) ob_get_clean();
}
