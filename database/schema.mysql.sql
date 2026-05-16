SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    name VARCHAR(190) NOT NULL,
    description TEXT NOT NULL,
    hero_image VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    name VARCHAR(190) NOT NULL,
    summary TEXT NOT NULL,
    description TEXT NOT NULL,
    price INT NOT NULL,
    old_price INT NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    rating DECIMAL(3,1) NOT NULL DEFAULT 4.5,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    tags VARCHAR(255) NOT NULL DEFAULT '',
    cover_image VARCHAR(500) NOT NULL,
    accent_image TEXT NOT NULL,
    specs_json TEXT NOT NULL,
    features_json TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    title VARCHAR(190) NOT NULL,
    eta_label VARCHAR(120) NOT NULL,
    price_label VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    cover_image VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    method_type VARCHAR(60) NOT NULL DEFAULT 'bank_transfer',
    bank_name VARCHAR(190) NOT NULL,
    account_mask VARCHAR(190) NOT NULL,
    account_ref VARCHAR(190) NOT NULL DEFAULT '',
    holder_name VARCHAR(190) NOT NULL,
    note TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_payment_methods_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
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
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price INT NOT NULL,
    line_total INT NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS service_requests (
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
    CONSTRAINT fk_service_requests_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_service_requests_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
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
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

INSERT IGNORE INTO categories (id, slug, name, description, hero_image, sort_order) VALUES
    (1, 'office', 'PC văn phòng', 'Cấu hình cho văn phòng, học tập, kế toán và bán hàng.', 'https://images.unsplash.com/photo-1496171367470-9ed9a91ea931?auto=format&fit=crop&w=1200&q=80', 1),
    (2, 'gaming', 'PC gaming', 'Build cho gaming, streaming và nâng cấp dài hạn.', 'https://images.unsplash.com/photo-1547082299-de196ea013d6?auto=format&fit=crop&w=1200&q=80', 2),
    (3, 'gear', 'Thiết bị', 'Màn hình, bàn phím, chuột, tai nghe và thiết bị thao tác.', 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?auto=format&fit=crop&w=1200&q=80', 3),
    (4, 'accessory', 'Phụ kiện công nghệ', 'Dock, SSD ngoài, webcam, router và phụ kiện setup.', 'https://images.unsplash.com/photo-1517430816045-df4b7de11d1d?auto=format&fit=crop&w=1200&q=80', 4);

INSERT IGNORE INTO users (id, role, full_name, email, password_hash, phone, company, address, city, note, avatar_label, avatar_url, created_at, updated_at) VALUES
    (1, 'admin', 'Admin WebPC', 'admin@webpc.local', '$2y$10$8zT08NJr/Dg81LAouKxCzOF2XTwyrkJ/95manx2d1P3Wvva3ULL7m', '0900000000', 'WebPC', '1 Green Glass St', 'Ha Noi', 'Tài khoản admin demo cho khu vực quản trị.', 'AW', '', '2026-04-29 00:00:00', '2026-04-29 00:00:00');

INSERT IGNORE INTO products (id, category_id, slug, name, summary, description, price, old_price, stock, rating, featured, tags, cover_image, accent_image, specs_json, features_json, sort_order, created_at) VALUES
    (1, 1, 'pc-office-alpha-i5', 'PC Office Alpha i5', 'Máy gọn cho văn phòng, học tập và kế toán.', 'Build cân bằng cho văn phòng, Excel nặng, CRM và setup doanh nghiệp nhỏ.', 15990000, 17290000, 12, 4.8, 1, 'Silent,Workstation', 'https://images.unsplash.com/photo-1496171367470-9ed9a91ea931?auto=format&fit=crop&w=1200&q=80', '["https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80"]', '{"CPU":"Intel Core i5-13400","RAM":"16GB DDR4","Storage":"1TB NVMe SSD","OS":"Windows 11"}', '["Khởi động nhanh","Case gọn cho văn phòng","Dễ nâng cấp RAM và SSD"]', 1, '2026-04-29 00:00:00'),
    (2, 2, 'pc-gaming-rtx-4060-core', 'PC Gaming RTX 4060 Core', 'Build 1080p và 1440p cho eSports và AAA.', 'Cấu hình gaming nghiêm túc cho người muốn FPS cao mà vẫn giữ ngân sách hợp lý.', 28990000, 30990000, 6, 4.7, 1, 'RTX,144Hz', 'https://images.unsplash.com/photo-1547082299-de196ea013d6?auto=format&fit=crop&w=1200&q=80', '["https://images.unsplash.com/photo-1598550476439-6847785fcea6?auto=format&fit=crop&w=1200&q=80"]', '{"CPU":"AMD Ryzen 5 7600","GPU":"GeForce RTX 4060 8GB","RAM":"16GB DDR5","Storage":"1TB NVMe SSD"}', '["FPS cao cho 144Hz","Nguồn và airflow dễ nâng cấp","Tốt cho game online"]', 2, '2026-04-29 00:00:00');

INSERT IGNORE INTO services (id, slug, title, eta_label, price_label, description, cover_image, sort_order) VALUES
    (1, 'build-custom', 'Lắp máy theo ngân sách', 'Trong 24 giờ', 'Từ 300.000 đ', 'Chốt cấu hình theo workload, ngân sách và khả năng nâng cấp.', 'https://images.unsplash.com/photo-1593640408182-31c70c8268f5?auto=format&fit=crop&w=1200&q=80', 1),
    (2, 'upgrade-pc', 'Nâng cấp PC và workstation', '2-4 giờ', 'Từ 250.000 đ', 'Phân tích bottleneck và đề xuất luồng nâng cấp hợp lý.', 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80', 2),
    (3, 'clean-and-repaste', 'Vệ sinh và thay nhiệt', '90 phút', 'Từ 180.000 đ', 'Làm sạch bụi, thay keo tản và test lại nhiệt độ.', 'https://images.unsplash.com/photo-1580894908361-967195033215?auto=format&fit=crop&w=1200&q=80', 3),
    (4, 'workspace-setup', 'Triển khai góc làm việc', 'Theo lịch hẹn', 'Từ 500.000 đ', 'Tư vấn monitor, dock, webcam, router và bố trí dây gọn.', 'https://images.unsplash.com/photo-1496171367470-9ed9a91ea931?auto=format&fit=crop&w=1200&q=80', 4);

INSERT IGNORE INTO payment_methods (id, user_id, method_type, bank_name, account_mask, account_ref, holder_name, note, created_at) VALUES
    (1, 1, 'bank_transfer', 'Ngân hàng ABC', '**** 9845', '9845', 'ADMIN WEBPC', 'Tài khoản demo cho admin.', '2026-04-29 00:00:00');
