<?php

declare(strict_types=1);

/**
 * POST action router for cart, auth, profile, checkout, services, and admin product creation.
 */

function handle_post_actions(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    $action = trim((string) ($_POST['action'] ?? ''));
    $redirect = safe_redirect_path((string) ($_POST['redirect'] ?? basename((string) ($_SERVER['PHP_SELF'] ?? 'index.php'))));

    switch ($action) {
        case 'add_to_cart':
            $productId = (int) ($_POST['product_id'] ?? 0);
            $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
            $product = product_by_id($productId);

            if ($product === null) {
                set_flash('danger', 'Sản phẩm không tồn tại.');
                redirect_to($redirect);
            }

            $quantity = min($quantity, max(1, (int) $product['stock']));
            add_product_to_cart($productId, $quantity);
            set_flash('success', 'Đã thêm sản phẩm vào giỏ hàng.');
            redirect_to($redirect);

        case 'set_cart_item':
            $productId = (int) ($_POST['product_id'] ?? 0);
            $quantity = max(0, (int) ($_POST['quantity'] ?? 1));
            if ($productId > 0) {
                set_cart_item_quantity($productId, $quantity);
                set_flash('success', 'Đã cập nhật số lượng sản phẩm.');
            }
            redirect_to('cart.php');

        case 'remove_cart_item':
            $productId = (int) ($_POST['product_id'] ?? 0);
            if ($productId > 0) {
                remove_cart_item($productId);
                set_flash('success', 'Đã xóa sản phẩm khỏi giỏ.');
            }
            redirect_to('cart.php');

        case 'apply_coupon':
            $coupon = strtoupper(trim((string) ($_POST['coupon_code'] ?? '')));
            if ($coupon === 'NVIDIA') {
                $_SESSION['webpc_coupon'] = $coupon;
                set_flash('success', 'Đã áp mã NVIDIA giảm 3%, tối đa 500.000 đ.');
            } else {
                unset($_SESSION['webpc_coupon']);
                set_flash('danger', 'Mã giảm giá không hợp lệ.');
            }
            redirect_to('cart.php');

        case 'checkout':
            process_checkout();
            redirect_to('cart.php');

        case 'save_profile':
            process_profile_update();
            redirect_to('account.php');

        case 'update_avatar':
            process_avatar_update();
            redirect_to('account.php');

        case 'add_payment':
            process_payment_add();
            redirect_to('account.php');

        case 'delete_payment':
            process_payment_delete();
            redirect_to('account.php');

        case 'submit_service_request':
            process_service_request();
            redirect_to('services.php');

        case 'create_product':
            process_product_create();
            redirect_to('add-product.php');

        case 'subscribe_newsletter':
            process_newsletter_subscription();
            redirect_to($redirect !== '' ? $redirect : 'index.php');

        case 'login':
            $next = safe_redirect_path((string) ($_POST['next'] ?? 'account.php'), 'account.php');
            $ok = process_login();
            redirect_to($ok ? $next : 'login.php?mode=login&next=' . urlencode($next));

        case 'register':
            $next = safe_redirect_path((string) ($_POST['next'] ?? 'account.php'), 'account.php');
            $ok = process_register();
            redirect_to($ok ? $next : 'login.php?mode=register&next=' . urlencode($next));

        case 'logout':
            logout_current_user();
            set_flash('success', 'Đã đăng xuất.');
            redirect_to($redirect !== '' ? $redirect : 'index.php');

        default:
            set_flash('danger', 'Hành động không hợp lệ.');
            redirect_to($redirect !== '' ? $redirect : 'index.php');
    }
}

function process_login(): bool
{
    $old = [
        'email' => trim((string) ($_POST['email'] ?? '')),
    ];

    $errors = [];
    $password = (string) ($_POST['password'] ?? '');

    if (!valid_email($old['email'])) {
        $errors['email'] = 'Nhập email hợp lệ.';
    }

    if ($password === '') {
        $errors['password'] = 'Nhập mật khẩu.';
    }

    $user = $errors === [] ? fetch_one('SELECT * FROM users WHERE email = :email LIMIT 1', ['email' => $old['email']]) : null;
    if ($errors === [] && ($user === null || !password_verify($password, (string) $user['password_hash']))) {
        $errors['password'] = 'Email hoặc mật khẩu không đúng.';
    }

    if ($errors !== []) {
        set_form_state('login', $errors, $old);
        set_flash('danger', 'Đăng nhập thất bại.');
        return false;
    }

    login_user($user);
    set_flash('success', 'Đăng nhập thành công.');
    return true;
}

function process_register(): bool
{
    $old = [
        'full_name' => trim((string) ($_POST['full_name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
    ];

    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
    $errors = [];

    if ($old['full_name'] === '') {
        $errors['full_name'] = 'Nhập họ tên.';
    }

    if (!valid_email($old['email'])) {
        $errors['email'] = 'Nhập email hợp lệ.';
    } elseif (fetch_one('SELECT id FROM users WHERE email = :email LIMIT 1', ['email' => $old['email']]) !== null) {
        $errors['email'] = 'Email đã tồn tại.';
    }

    if (strlen($password) < 8) {
        $errors['password'] = 'Mật khẩu tối thiểu 8 ký tự.';
    }

    if (!isset($errors['password']) && (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password))) {
        $errors['password'] = 'Mật khẩu cần có ít nhất 1 chữ hoa và 1 số.';
    }

    if ($passwordConfirm === '') {
        $errors['password_confirm'] = 'Nhập lại mật khẩu.';
    } elseif ($password !== $passwordConfirm) {
        $errors['password_confirm'] = 'Mật khẩu nhập lại không khớp.';
    }

    if ($errors !== []) {
        set_form_state('register', $errors, $old);
        set_flash('danger', 'Đăng ký thất bại.');
        return false;
    }

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
            'role' => 'customer',
            'full_name' => $old['full_name'],
            'email' => $old['email'],
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'phone' => '',
            'company' => '',
            'address' => '',
            'city' => '',
            'note' => '',
            'avatar_label' => initials_from_name($old['full_name']),
            'avatar_url' => '',
            'created_at' => $now,
            'updated_at' => $now,
        ]
    );

    $user = fetch_one('SELECT * FROM users WHERE email = :email LIMIT 1', ['email' => $old['email']]);
    if ($user === null) {
        set_flash('danger', 'Không tạo được tài khoản.');
        return false;
    }

    login_user($user);
    set_flash('success', 'Đã tạo tài khoản mới.');
    return true;
}

function process_checkout(): void
{
    if (!is_logged_in()) {
        set_flash('danger', 'Đăng nhập để thanh toán và lưu đơn hàng vào tài khoản.');
        return;
    }

    $profile = current_profile_defaults();
    $old = [
        'full_name' => trim((string) ($_POST['full_name'] ?? $profile['full_name'])),
        'email' => trim((string) ($_POST['email'] ?? $profile['email'])),
        'phone' => trim((string) ($_POST['phone'] ?? $profile['phone'])),
        'address' => trim((string) ($_POST['address'] ?? $profile['address'])),
        'payment_method_id' => trim((string) ($_POST['payment_method_id'] ?? '')),
        'note' => trim((string) ($_POST['note'] ?? '')),
    ];

    $errors = [];
    $items = cart_items();

    if ($items === []) {
        set_flash('danger', 'Giỏ hàng đang trống.');
        return;
    }

    if ($old['full_name'] === '') {
        $errors['full_name'] = 'Nhập họ tên người nhận.';
    }
    if (!valid_email($old['email'])) {
        $errors['email'] = 'Nhập email hợp lệ.';
    }
    if (!valid_phone($old['phone'])) {
        $errors['phone'] = 'Nhập số điện thoại 10-11 số.';
    }
    if ($old['address'] === '') {
        $errors['address'] = 'Nhập địa chỉ giao hàng.';
    }

    $payment = null;
    if ($old['payment_method_id'] === '') {
        $errors['payment_method_id'] = 'Chọn phương thức thanh toán.';
    } else {
        $payment = fetch_one(
            'SELECT * FROM payment_methods WHERE id = :id AND user_id = :user_id LIMIT 1',
            ['id' => (int) $old['payment_method_id'], 'user_id' => (int) current_user_id()]
        );
        if ($payment === null) {
            $errors['payment_method_id'] = 'Phương thức thanh toán không tồn tại.';
        }
    }

    if ($errors !== []) {
        set_form_state('checkout', $errors, $old);
        set_flash('danger', 'Kiểm tra lại form thanh toán.');
        return;
    }

    $totals = cart_totals($items);
    execute_query(
        'INSERT INTO orders (
            user_id, customer_name, email, phone, address, payment_method_label, note,
            subtotal, shipping_fee, discount_amount, total_amount, status, created_at
        ) VALUES (
            :user_id, :customer_name, :email, :phone, :address, :payment_method_label, :note,
            :subtotal, :shipping_fee, :discount_amount, :total_amount, :status, :created_at
        )',
        [
            'user_id' => (int) current_user_id(),
            'customer_name' => $old['full_name'],
            'email' => $old['email'],
            'phone' => $old['phone'],
            'address' => $old['address'],
            'payment_method_label' => (string) $payment['bank_name'] . ' - ' . (string) $payment['account_mask'],
            'note' => $old['note'],
            'subtotal' => $totals['subtotal'],
            'shipping_fee' => $totals['shipping'],
            'discount_amount' => $totals['discount'],
            'total_amount' => $totals['total'],
            'status' => 'Đang xử lý',
            'created_at' => date('Y-m-d H:i:s'),
        ]
    );

    $orderId = (int) db()->lastInsertId();
    foreach ($items as $item) {
        execute_query(
            'INSERT INTO order_items (order_id, product_id, quantity, unit_price, line_total)
             VALUES (:order_id, :product_id, :quantity, :unit_price, :line_total)',
            [
                'order_id' => $orderId,
                'product_id' => (int) $item['product']['id'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => (int) $item['product']['price'],
                'line_total' => (int) $item['line_total'],
            ]
        );
    }

    unset($_SESSION['webpc_coupon']);
    clear_cart();
    set_flash('success', 'Đã tạo đơn hàng WP-' . str_pad((string) $orderId, 5, '0', STR_PAD_LEFT) . '.');
}

function process_profile_update(): void
{
    require_login();

    $userId = (int) current_user_id();
    $current = current_profile_defaults();
    $old = [
        'full_name' => trim((string) ($_POST['full_name'] ?? $current['full_name'])),
        'email' => trim((string) ($_POST['email'] ?? $current['email'])),
        'phone' => trim((string) ($_POST['phone'] ?? $current['phone'])),
        'company' => trim((string) ($_POST['company'] ?? $current['company'])),
        'address' => trim((string) ($_POST['address'] ?? $current['address'])),
        'city' => trim((string) ($_POST['city'] ?? $current['city'])),
        'note' => trim((string) ($_POST['note'] ?? $current['note'])),
    ];

    $errors = [];
    if ($old['full_name'] === '') {
        $errors['full_name'] = 'Nhập họ tên.';
    }
    if (!valid_email($old['email'])) {
        $errors['email'] = 'Nhập email hợp lệ.';
    } else {
        $other = fetch_one('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1', ['email' => $old['email'], 'id' => $userId]);
        if ($other !== null) {
            $errors['email'] = 'Email đã được sử dụng.';
        }
    }
    if ($old['phone'] !== '' && !valid_phone($old['phone'])) {
        $errors['phone'] = 'Số điện thoại chưa hợp lệ.';
    }
    if ($old['address'] === '') {
        $errors['address'] = 'Nhập địa chỉ.';
    }
    if ($old['city'] === '') {
        $errors['city'] = 'Nhập tỉnh hoặc thành phố.';
    }

    if ($errors !== []) {
        set_form_state('profile', $errors, $old);
        set_flash('danger', 'Kiểm tra lại thông tin tài khoản.');
        return;
    }

    execute_query(
        'UPDATE users
         SET full_name = :full_name, email = :email, phone = :phone, company = :company,
             address = :address, city = :city, note = :note, avatar_label = :avatar_label, updated_at = :updated_at
         WHERE id = :id',
        [
            'id' => $userId,
            'full_name' => $old['full_name'],
            'email' => $old['email'],
            'phone' => $old['phone'],
            'company' => $old['company'],
            'address' => $old['address'],
            'city' => $old['city'],
            'note' => $old['note'],
            'avatar_label' => initials_from_name($old['full_name']),
            'updated_at' => date('Y-m-d H:i:s'),
        ]
    );

    set_flash('success', 'Đã cập nhật tài khoản.');
}

function process_avatar_update(): void
{
    require_login();

    $avatarUrl = trim((string) ($_POST['avatar_url'] ?? ''));
    $old = ['avatar_url' => $avatarUrl];
    $errors = [];

    if ($avatarUrl !== '' && !valid_image_url($avatarUrl)) {
        $errors['avatar_url'] = 'Nhập URL ảnh hợp lệ.';
    }

    if ($errors !== []) {
        set_form_state('avatar', $errors, $old);
        set_flash('danger', 'Avatar chưa hợp lệ.');
        return;
    }

    execute_query(
        'UPDATE users SET avatar_url = :avatar_url, updated_at = :updated_at WHERE id = :id',
        [
            'avatar_url' => $avatarUrl,
            'updated_at' => date('Y-m-d H:i:s'),
            'id' => (int) current_user_id(),
        ]
    );

    set_flash('success', $avatarUrl === '' ? 'Đã xóa avatar.' : 'Đã cập nhật avatar.');
}

function process_payment_add(): void
{
    require_login();

    $old = [
        'bank_name' => trim((string) ($_POST['bank_name'] ?? '')),
        'account_number' => preg_replace('/\s+/', '', (string) ($_POST['account_number'] ?? '')),
        'holder_name' => trim((string) ($_POST['holder_name'] ?? '')),
        'note' => trim((string) ($_POST['note'] ?? '')),
    ];

    $errors = [];
    if ($old['bank_name'] === '') {
        $errors['bank_name'] = 'Nhập tên ngân hàng.';
    }
    if ($old['account_number'] === '' || strlen($old['account_number']) < 6) {
        $errors['account_number'] = 'Nhập số tài khoản hợp lệ.';
    }
    if ($old['holder_name'] === '') {
        $errors['holder_name'] = 'Nhập tên chủ tài khoản.';
    }

    if ($errors !== []) {
        set_form_state('payment', $errors, $old);
        set_flash('danger', 'Kiểm tra lại phương thức thanh toán.');
        return;
    }

    execute_query(
        'INSERT INTO payment_methods (user_id, bank_name, account_mask, holder_name, note, created_at)
         VALUES (:user_id, :bank_name, :account_mask, :holder_name, :note, :created_at)',
        [
            'user_id' => (int) current_user_id(),
            'bank_name' => $old['bank_name'],
            'account_mask' => '**** ' . substr($old['account_number'], -4),
            'holder_name' => $old['holder_name'],
            'note' => $old['note'],
            'created_at' => date('Y-m-d H:i:s'),
        ]
    );

    set_flash('success', 'Đã thêm phương thức thanh toán.');
}

function process_payment_delete(): void
{
    require_login();

    $paymentId = (int) ($_POST['payment_id'] ?? 0);
    if ($paymentId <= 0) {
        set_flash('danger', 'Không tìm thấy phương thức cần xóa.');
        return;
    }

    execute_query(
        'DELETE FROM payment_methods WHERE id = :id AND user_id = :user_id',
        ['id' => $paymentId, 'user_id' => (int) current_user_id()]
    );

    set_flash('success', 'Đã xóa phương thức thanh toán.');
}

function process_service_request(): void
{
    $current = current_profile_defaults();
    $old = [
        'name' => trim((string) ($_POST['name'] ?? $current['full_name'])),
        'email' => trim((string) ($_POST['email'] ?? $current['email'])),
        'phone' => trim((string) ($_POST['phone'] ?? $current['phone'])),
        'service_id' => trim((string) ($_POST['service_id'] ?? '')),
        'budget' => trim((string) ($_POST['budget'] ?? '')),
        'note' => trim((string) ($_POST['note'] ?? '')),
    ];

    $errors = [];
    if ($old['name'] === '') {
        $errors['name'] = 'Nhập họ tên.';
    }
    if (!valid_email($old['email'])) {
        $errors['email'] = 'Nhập email hợp lệ.';
    }
    if ($old['phone'] === '' || !valid_phone($old['phone'])) {
        $errors['phone'] = 'Nhập số điện thoại hợp lệ.';
    }
    if ($old['service_id'] === '') {
        $errors['service_id'] = 'Chọn dịch vụ.';
    }
    if ($old['budget'] === '') {
        $errors['budget'] = 'Nhập ngân sách hoặc đầu bài.';
    }

    if ($errors !== []) {
        set_form_state('service_request', $errors, $old);
        set_flash('danger', 'Kiểm tra lại yêu cầu dịch vụ.');
        return;
    }

    execute_query(
        'INSERT INTO service_requests (user_id, service_id, customer_name, email, phone, budget, note, status, created_at)
         VALUES (:user_id, :service_id, :customer_name, :email, :phone, :budget, :note, :status, :created_at)',
        [
            'user_id' => current_user_id(),
            'service_id' => (int) $old['service_id'],
            'customer_name' => $old['name'],
            'email' => $old['email'],
            'phone' => $old['phone'],
            'budget' => $old['budget'],
            'note' => $old['note'],
            'status' => 'Mới tiếp nhận',
            'created_at' => date('Y-m-d H:i:s'),
        ]
    );

    set_flash('success', 'Đã gửi yêu cầu dịch vụ.');
}

function process_product_create(): void
{
    require_admin();

    $old = [
        'category_id' => trim((string) ($_POST['category_id'] ?? '')),
        'name' => trim((string) ($_POST['name'] ?? '')),
        'slug' => trim((string) ($_POST['slug'] ?? '')),
        'summary' => trim((string) ($_POST['summary'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
        'price' => trim((string) ($_POST['price'] ?? '')),
        'old_price' => trim((string) ($_POST['old_price'] ?? '')),
        'stock' => trim((string) ($_POST['stock'] ?? '')),
        'rating' => trim((string) ($_POST['rating'] ?? '4.5')),
        'featured' => isset($_POST['featured']) ? '1' : '0',
        'tags' => trim((string) ($_POST['tags'] ?? '')),
        'cover_image' => trim((string) ($_POST['cover_image'] ?? '')),
        'accent_image' => trim((string) ($_POST['accent_image'] ?? '')),
        'specs_text' => trim((string) ($_POST['specs_text'] ?? '')),
        'features_text' => trim((string) ($_POST['features_text'] ?? '')),
    ];

    $errors = [];

    if ($old['category_id'] === '' || fetch_one('SELECT id FROM categories WHERE id = :id LIMIT 1', ['id' => (int) $old['category_id']]) === null) {
        $errors['category_id'] = 'Chọn danh mục hợp lệ.';
    }
    if ($old['name'] === '') {
        $errors['name'] = 'Nhập tên sản phẩm.';
    }
    if ($old['summary'] === '') {
        $errors['summary'] = 'Nhập mô tả ngắn.';
    }
    if ($old['description'] === '') {
        $errors['description'] = 'Nhập mô tả chi tiết.';
    }
    if ($old['price'] === '' || (int) $old['price'] <= 0) {
        $errors['price'] = 'Nhập giá bán hợp lệ.';
    }
    if ($old['old_price'] === '' || (int) $old['old_price'] <= 0) {
        $errors['old_price'] = 'Nhập giá gốc hợp lệ.';
    }
    if ($old['stock'] === '' || (int) $old['stock'] < 0) {
        $errors['stock'] = 'Nhập tồn kho hợp lệ.';
    }
    if (!valid_image_url($old['cover_image'])) {
        $errors['cover_image'] = 'Nhập URL ảnh chính hợp lệ.';
    }
    if (!valid_image_url($old['accent_image'])) {
        $errors['accent_image'] = 'Nhập URL ảnh phụ hợp lệ.';
    }

    $slug = $old['slug'] !== '' ? slugify($old['slug']) : slugify($old['name']);
    if (product_exists_by_slug($slug)) {
        $errors['slug'] = 'Slug đã tồn tại.';
    }

    $specs = specs_from_text($old['specs_text']);
    $features = lines_to_array($old['features_text']);

    if ($specs === []) {
        $errors['specs_text'] = 'Nhập thông số theo dạng Nhãn: Giá trị, mỗi dòng một mục.';
    }
    if ($features === []) {
        $errors['features_text'] = 'Nhập ít nhất một điểm nổi bật.';
    }

    if ($errors !== []) {
        set_form_state('product_create', $errors, $old);
        set_flash('danger', 'Kiểm tra lại form tạo sản phẩm.');
        return;
    }

    $maxSort = fetch_one('SELECT COALESCE(MAX(sort_order), 0) AS max_sort FROM products');
    execute_query(
        'INSERT INTO products (
            category_id, slug, name, summary, description, price, old_price, stock, rating, featured,
            tags, cover_image, accent_image, specs_json, features_json, sort_order, created_at
        ) VALUES (
            :category_id, :slug, :name, :summary, :description, :price, :old_price, :stock, :rating, :featured,
            :tags, :cover_image, :accent_image, :specs_json, :features_json, :sort_order, :created_at
        )',
        [
            'category_id' => (int) $old['category_id'],
            'slug' => $slug,
            'name' => $old['name'],
            'summary' => $old['summary'],
            'description' => $old['description'],
            'price' => (int) $old['price'],
            'old_price' => max((int) $old['price'], (int) $old['old_price']),
            'stock' => (int) $old['stock'],
            'rating' => max(0, min(5, (float) $old['rating'])),
            'featured' => $old['featured'] === '1' ? 1 : 0,
            'tags' => $old['tags'],
            'cover_image' => $old['cover_image'],
            'accent_image' => $old['accent_image'],
            'specs_json' => json_encode($specs, JSON_UNESCAPED_UNICODE),
            'features_json' => json_encode($features, JSON_UNESCAPED_UNICODE),
            'sort_order' => (int) (($maxSort['max_sort'] ?? 0) + 1),
            'created_at' => date('Y-m-d H:i:s'),
        ]
    );

    set_flash('success', 'Đã tạo sản phẩm mới.');
}

function process_newsletter_subscription(): void
{
    $email = trim((string) ($_POST['email'] ?? ''));
    if (!valid_email($email)) {
        set_flash('danger', 'Nhập email hợp lệ để nhận cập nhật.');
        return;
    }

    execute_query(
        'INSERT OR IGNORE INTO newsletter_subscribers (email, created_at)
         VALUES (:email, :created_at)',
        [
            'email' => $email,
            'created_at' => date('Y-m-d H:i:s'),
        ]
    );

    set_flash('success', 'Đã lưu email nhận tin.');
}
