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

    $host = getenv('WEBPC_DB_HOST') ?: '127.0.0.1';
    $port = getenv('WEBPC_DB_PORT') ?: '3306';
    $name = getenv('WEBPC_DB_NAME') ?: 'webpc';
    $user = getenv('WEBPC_DB_USER') ?: 'root';
    $pass = getenv('WEBPC_DB_PASS') ?: '';

    $server = new PDO(
        "mysql:host={$host};port={$port};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
        ]
    );
    $server->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
        ]
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
}

function table_exists(string $table): bool
{
    $stmt = db()->prepare(
        'SELECT TABLE_NAME
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name
         LIMIT 1'
    );
    $stmt->execute(['name' => $table]);
    return $stmt->fetchColumn() !== false;
}

function column_exists(string $table, string $column): bool
{
    $stmt = db()->prepare(
        'SELECT COLUMN_NAME
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name
         LIMIT 1'
    );
    $stmt->execute(['table_name' => $table, 'column_name' => $column]);
    return $stmt->fetchColumn() !== false;
}

function initialize_database(PDO $pdo): void
{
    if (!table_exists('products')) {
        $schema = file_get_contents(base_path('database/schema.mysql.sql'));
        if ($schema === false) {
            throw new RuntimeException('Cannot read schema.mysql.sql');
        }

        foreach (sql_statements($schema) as $statement) {
            $pdo->exec($statement);
        }
    }

    ensure_runtime_schema($pdo);
}

function sql_statements(string $sql): array
{
    $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
    return array_values(array_filter(array_map('trim', explode(';', $sql))));
}

function ensure_runtime_schema(PDO $pdo): void
{
    $runtimeTables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role VARCHAR(32) NOT NULL DEFAULT 'customer',
            full_name VARCHAR(190) NOT NULL,
            email VARCHAR(190) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            phone VARCHAR(40) NOT NULL DEFAULT '',
            company VARCHAR(190) NOT NULL DEFAULT '',
            address VARCHAR(255) NOT NULL DEFAULT '',
            city VARCHAR(120) NOT NULL DEFAULT '',
            note TEXT NOT NULL,
            avatar_label VARCHAR(20) NOT NULL DEFAULT 'WP',
            avatar_url VARCHAR(500) NOT NULL DEFAULT '',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS payment_methods (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            method_type VARCHAR(60) NOT NULL DEFAULT 'bank_transfer',
            bank_name VARCHAR(190) NOT NULL,
            account_mask VARCHAR(190) NOT NULL,
            account_ref VARCHAR(190) NOT NULL DEFAULT '',
            holder_name VARCHAR(190) NOT NULL,
            note TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            customer_name VARCHAR(190) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(40) NOT NULL,
            address VARCHAR(255) NOT NULL,
            payment_method_label VARCHAR(255) NOT NULL,
            note TEXT NOT NULL,
            subtotal INT NOT NULL,
            shipping_fee INT NOT NULL,
            discount_amount INT NOT NULL DEFAULT 0,
            total_amount INT NOT NULL,
            status VARCHAR(60) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            unit_price INT NOT NULL,
            line_total INT NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS service_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            service_id INT NOT NULL,
            customer_name VARCHAR(190) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(40) NOT NULL,
            budget VARCHAR(190) NOT NULL,
            note TEXT NOT NULL,
            status VARCHAR(60) NOT NULL,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            user_id INT DEFAULT NULL,
            provider VARCHAR(190) NOT NULL,
            payment_code VARCHAR(60) NOT NULL UNIQUE,
            amount INT NOT NULL,
            status VARCHAR(60) NOT NULL,
            paid_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];

    foreach ($runtimeTables as $sql) {
        $pdo->exec($sql);
    }

    if (!column_exists('payment_methods', 'user_id')) {
        $pdo->exec('ALTER TABLE payment_methods ADD COLUMN user_id INT NOT NULL DEFAULT 1');
    }

    if (!column_exists('payment_methods', 'method_type')) {
        $pdo->exec("ALTER TABLE payment_methods ADD COLUMN method_type VARCHAR(60) NOT NULL DEFAULT 'bank_transfer'");
    }

    if (!column_exists('payment_methods', 'account_ref')) {
        $pdo->exec("ALTER TABLE payment_methods ADD COLUMN account_ref VARCHAR(190) NOT NULL DEFAULT ''");
    }

    if (!column_exists('orders', 'user_id')) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN user_id INT DEFAULT NULL');
    }

    if (!column_exists('orders', 'updated_at')) {
        $pdo->exec('ALTER TABLE orders ADD COLUMN updated_at DATETIME DEFAULT NULL');
    }

    if (!column_exists('service_requests', 'user_id')) {
        $pdo->exec('ALTER TABLE service_requests ADD COLUMN user_id INT DEFAULT NULL');
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
                'full_name' => 'Admin ĐộPICI',
                'email' => $adminEmail,
                'password_hash' => password_hash('Admin@12345', PASSWORD_DEFAULT),
                'phone' => '0900000000',
                'company' => 'ĐộPICI',
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
    execute_query("UPDATE users SET full_name = 'Admin ĐộPICI', company = 'ĐộPICI', avatar_label = 'AD' WHERE email = 'admin@webpc.local'");
    execute_query("UPDATE payment_methods SET holder_name = 'ĐỘPICI' WHERE user_id IN (SELECT id FROM users WHERE email = 'admin@webpc.local')");

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
    execute_query("UPDATE services SET title = 'Lắp máy theo yêu cầu' WHERE slug = 'build-custom'");
    execute_query("UPDATE services SET title = 'Nâng cấp thiết bị' WHERE slug = 'upgrade-pc'");
    execute_query("UPDATE services SET title = 'Vệ sinh và thay keo tản nhiệt' WHERE slug = 'clean-and-repaste'");
    execute_query("UPDATE services SET title = 'Bố trí không gian làm việc' WHERE slug = 'workspace-setup'");
    execute_query("UPDATE payment_methods SET method_type = 'visa', bank_name = 'Visa / Mastercard / JCB', account_mask = 'Thẻ quốc tế' WHERE method_type = 'card'");
    execute_query(
        'DELETE FROM payment_methods
         WHERE id NOT IN (
             SELECT MIN(id)
             FROM payment_methods
             GROUP BY user_id, method_type, bank_name, account_mask
         )'
    );
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
    return $pageTitle ? $pageTitle . ' | ĐộPICI' : 'ĐộPICI';
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

    if (preg_match('/^storage\/uploads\/(avatars|products)\/[A-Za-z0-9._-]+\.(jpg|jpeg|png|webp|gif)$/i', $url) === 1) {
        return true;
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

function product_accent_images(array $product): array
{
    $raw = trim((string) ($product['accent_image'] ?? ''));
    if ($raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        return array_values(array_filter(array_map('strval', $decoded)));
    }

    return [$raw];
}

function product_first_accent_image(array $product): string
{
    $images = product_accent_images($product);
    return $images[0] ?? (string) ($product['cover_image'] ?? '');
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
         ORDER BY p.id DESC, p.featured DESC, p.sort_order ASC
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

function product_slug_exists_for_other(string $slug, int $productId): bool
{
    return fetch_one(
        'SELECT id FROM products WHERE slug = :slug AND id != :id LIMIT 1',
        ['slug' => $slug, 'id' => $productId]
    ) !== null;
}

function specs_text_from_product(array $product): string
{
    $specs = decode_json_column((string) ($product['specs_json'] ?? ''));
    $lines = [];

    foreach ($specs as $label => $value) {
        if (is_scalar($label) && is_scalar($value)) {
            $lines[] = trim((string) $label) . ': ' . trim((string) $value);
        }
    }

    return implode("\n", array_filter($lines));
}

function features_text_from_product(array $product): string
{
    $features = decode_json_column((string) ($product['features_json'] ?? ''));
    return implode("\n", array_values(array_filter(array_map('strval', $features))));
}

function product_form_old_from_product(array $product): array
{
    return [
        'product_id' => (string) ($product['id'] ?? ''),
        'category_id' => (string) ($product['category_id'] ?? ''),
        'name' => (string) ($product['name'] ?? ''),
        'slug' => (string) ($product['slug'] ?? ''),
        'summary' => (string) ($product['summary'] ?? ''),
        'description' => (string) ($product['description'] ?? ''),
        'price' => (string) ($product['price'] ?? ''),
        'old_price' => (string) ($product['old_price'] ?? ''),
        'stock' => (string) ($product['stock'] ?? '0'),
        'rating' => (string) ($product['rating'] ?? '4.5'),
        'featured' => (int) ($product['featured'] ?? 0) === 1 ? '1' : '0',
        'tags' => (string) ($product['tags'] ?? ''),
        'cover_image' => (string) ($product['cover_image'] ?? ''),
        'accent_image' => (string) ($product['accent_image'] ?? ''),
        'specs_text' => specs_text_from_product($product),
        'features_text' => features_text_from_product($product),
    ];
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

    ensure_default_payment_methods((int) $userId);

    return fetch_all(
        'SELECT * FROM payment_methods WHERE user_id = :user_id ORDER BY created_at DESC, id DESC',
        ['user_id' => $userId]
    );
}

function ensure_default_payment_methods(int $userId): void
{
    if ($userId <= 0) {
        return;
    }

    $defaults = [
        ['cod', 'COD', 'Thanh toán khi nhận hàng', '', 'Nhận hàng rồi thanh toán tiền mặt hoặc chuyển khoản.'],
        ['bank_transfer', 'Chuyển khoản ngân hàng', 'QR / STK của ĐộPICI', '', 'Chuyển khoản ngân hàng nội địa.'],
        ['momo', 'MoMo', 'Ví MoMo', '', 'Thanh toán qua ví MoMo.'],
        ['vnpay', 'VNPay', 'QR VNPay / ATM nội địa', '', 'Thanh toán qua VNPay.'],
        ['bank_card', 'Thẻ ngân hàng nội địa', 'ATM / Napas', '', 'Thanh toán bằng thẻ ngân hàng nội địa.'],
        ['visa', 'Visa / Mastercard / JCB', 'Thẻ quốc tế', '', 'Thanh toán bằng Visa, Mastercard hoặc JCB.'],
    ];

    foreach ($defaults as [$type, $provider, $mask, $ref, $note]) {
        $exists = fetch_one(
            'SELECT id FROM payment_methods WHERE user_id = :user_id AND method_type = :method_type LIMIT 1',
            ['user_id' => $userId, 'method_type' => $type]
        );

        if ($exists !== null) {
            continue;
        }

        execute_query(
            'INSERT INTO payment_methods (user_id, method_type, bank_name, account_mask, account_ref, holder_name, note, created_at)
             VALUES (:user_id, :method_type, :bank_name, :account_mask, :account_ref, :holder_name, :note, :created_at)',
            [
                'user_id' => $userId,
                'method_type' => $type,
                'bank_name' => $provider,
                'account_mask' => $mask,
                'account_ref' => $ref,
                'holder_name' => 'ĐỘPICI',
                'note' => $note,
                'created_at' => date('Y-m-d H:i:s'),
            ]
        );
    }
}

function payment_gateway_options(): array
{
    return [
        'vnpay' => [
            'name' => 'VNPAY QR',
            'description' => 'Giải pháp thanh toán trực tuyến dành cho nhà bán hàng online.',
            'badge' => 'VNPAY',
            'provider' => 'VNPay',
            'mask' => 'QR VNPay / ATM nội địa / Visa / Mastercard',
            'note' => 'Cổng thanh toán VNPay.',
        ],
        'momo' => [
            'name' => 'MoMo',
            'description' => 'Ví điện tử phổ biến tại Việt Nam, hỗ trợ QR và ứng dụng MoMo.',
            'badge' => 'MoMo',
            'provider' => 'MoMo',
            'mask' => 'Vi MoMo',
            'note' => 'Thanh toán qua ví MoMo.',
        ],
        'bank_card' => [
            'name' => 'Thẻ ngân hàng',
            'description' => 'Thanh toán bằng ATM nội địa, Napas và Internet Banking.',
            'badge' => 'ATM',
            'provider' => 'Thẻ ngân hàng nội địa',
            'mask' => 'ATM / Napas',
            'note' => 'Thanh toán bằng thẻ ngân hàng nội địa.',
        ],
        'visa' => [
            'name' => 'Visa / Mastercard',
            'description' => 'Thanh toán bằng thẻ quốc tế Visa, Mastercard hoặc JCB.',
            'badge' => 'VISA',
            'provider' => 'Visa / Mastercard / JCB',
            'mask' => 'Thẻ quốc tế',
            'note' => 'Thanh toán bằng Visa, Mastercard hoặc JCB.',
        ],
    ];
}

function payment_type_options(): array
{
    return [
        'momo' => 'MoMo',
        'vnpay' => 'VNPay',
        'bank_card' => 'Thẻ ngân hàng',
        'visa' => 'Visa / Mastercard',
        'zalopay' => 'ZaloPay',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'cod' => 'COD - Thanh toán khi nhận hàng',
    ];
}

function payment_type_label(string $type): string
{
    $options = payment_type_options();
    return $options[$type] ?? $type;
}

function payment_method_display(array $payment): string
{
    $type = (string) ($payment['method_type'] ?? 'bank_transfer');
    $provider = trim((string) ($payment['bank_name'] ?? ''));
    $mask = trim((string) ($payment['account_mask'] ?? ''));

    if ($provider === '') {
        return payment_type_label($type);
    }

    return $mask !== '' ? $provider . ' - ' . $mask : $provider;
}

function order_code(int $orderId): string
{
    return 'WP-' . str_pad((string) $orderId, 5, '0', STR_PAD_LEFT);
}

function order_status_options(): array
{
    return [
        'pending' => 'Dang xu ly',
        'paid' => 'Da thanh toan',
        'shipping' => 'Dang giao',
        'completed' => 'Hoan tat',
        'cancelled' => 'Da huy',
    ];
}

function payment_status_options(): array
{
    return [
        'pending' => 'Cho xac nhan',
        'paid' => 'Da thanh toan',
        'failed' => 'That bai',
        'refunded' => 'Da hoan tien',
        'cancelled' => 'Da huy',
    ];
}

function status_label(string $status, string $type = 'order'): string
{
    $options = $type === 'payment' ? payment_status_options() : order_status_options();
    return $options[$status] ?? $status;
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

function recent_payments(int $limit = 5, ?int $userId = null): array
{
    $sql = 'SELECT p.*, o.customer_name, o.payment_method_label, o.status AS order_status
            FROM payments p
            INNER JOIN orders o ON o.id = p.order_id';
    $params = ['limit' => $limit];

    if ($userId !== null) {
        $sql .= ' WHERE p.user_id = :user_id';
        $params['user_id'] = $userId;
    }

    $sql .= ' ORDER BY p.created_at DESC, p.id DESC LIMIT :limit';

    return fetch_all($sql, $params);
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
