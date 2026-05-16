PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    description TEXT NOT NULL,
    hero_image TEXT NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS users (
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
);

CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    summary TEXT NOT NULL,
    description TEXT NOT NULL,
    price INTEGER NOT NULL,
    old_price INTEGER NOT NULL,
    stock INTEGER NOT NULL DEFAULT 0,
    rating REAL NOT NULL DEFAULT 4.5,
    featured INTEGER NOT NULL DEFAULT 0,
    tags TEXT NOT NULL DEFAULT '',
    cover_image TEXT NOT NULL,
    accent_image TEXT NOT NULL,
    specs_json TEXT NOT NULL,
    features_json TEXT NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT NOT NULL UNIQUE,
    title TEXT NOT NULL,
    eta_label TEXT NOT NULL,
    price_label TEXT NOT NULL,
    description TEXT NOT NULL,
    cover_image TEXT NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS payment_methods (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    method_type TEXT NOT NULL DEFAULT 'bank_transfer',
    bank_name TEXT NOT NULL,
    account_mask TEXT NOT NULL,
    account_ref TEXT NOT NULL DEFAULT '',
    holder_name TEXT NOT NULL,
    note TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER DEFAULT NULL,
    customer_name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT NOT NULL,
    address TEXT NOT NULL,
    payment_method_label TEXT NOT NULL,
    note TEXT NOT NULL DEFAULT '',
    subtotal INTEGER NOT NULL,
    shipping_fee INTEGER NOT NULL,
    discount_amount INTEGER NOT NULL DEFAULT 0,
    total_amount INTEGER NOT NULL,
    status TEXT NOT NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    unit_price INTEGER NOT NULL,
    line_total INTEGER NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS service_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER DEFAULT NULL,
    service_id INTEGER NOT NULL,
    customer_name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT NOT NULL,
    budget TEXT NOT NULL,
    note TEXT NOT NULL DEFAULT '',
    status TEXT NOT NULL,
    created_at TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    user_id INTEGER DEFAULT NULL,
    provider TEXT NOT NULL,
    payment_code TEXT NOT NULL UNIQUE,
    amount INTEGER NOT NULL,
    status TEXT NOT NULL,
    paid_at TEXT DEFAULT NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    created_at TEXT NOT NULL
);

INSERT INTO categories (id, slug, name, description, hero_image, sort_order) VALUES
    (1, 'office', 'PC Office', 'Cau hinh cho office, accounting, CRM, sales, va hoc tap.', 'https://images.unsplash.com/photo-1496171367470-9ed9a91ea931?auto=format&fit=crop&w=1200&q=80', 1),
    (2, 'gaming', 'PC Gaming', 'Build cho gaming, streaming, va nang cap dai han.', 'https://images.unsplash.com/photo-1547082299-de196ea013d6?auto=format&fit=crop&w=1200&q=80', 2),
    (3, 'gear', 'Gear', 'Monitor, keyboard, mouse, headset, va phu kien thao tac.', 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?auto=format&fit=crop&w=1200&q=80', 3),
    (4, 'accessory', 'Accessory', 'Dock, SSD ngoai, webcam, router, va bo sung setup.', 'https://images.unsplash.com/photo-1517430816045-df4b7de11d1d?auto=format&fit=crop&w=1200&q=80', 4);

INSERT INTO users (
    id, role, full_name, email, password_hash, phone, company, address, city, note,
    avatar_label, avatar_url, created_at, updated_at
) VALUES
    (1, 'admin', 'Admin WebPC', 'admin@webpc.local', '$2y$10$8zT08NJr/Dg81LAouKxCzOF2XTwyrkJ/95manx2d1P3Wvva3ULL7m', '0900000000', 'WebPC', '1 Green Glass St', 'Ha Noi', 'Tai khoan admin demo cho khu vuc quan tri.', 'AW', '', '2026-04-29 00:00:00', '2026-04-29 00:00:00');

INSERT INTO products (
    id, category_id, slug, name, summary, description, price, old_price, stock, rating, featured, tags,
    cover_image, accent_image, specs_json, features_json, sort_order, created_at
) VALUES
    (1, 1, 'pc-office-alpha-i5', 'PC Office Alpha i5', 'May gon cho office, hoc tap, va accounting.', 'Build can bang cho office, excel nang, CRM, va setup doanh nghiep nho.', 15990000, 17290000, 12, 4.8, 1, 'Silent,Workstation', 'https://images.unsplash.com/photo-1496171367470-9ed9a91ea931?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80', '{"CPU":"Intel Core i5-13400","RAM":"16GB DDR4","Storage":"1TB NVMe SSD","OS":"Windows 11","Use case":"Office, sales, accounting"}', '["Khoi dong nhanh","Case gon cho van phong","De nang cap RAM va SSD"]', 1, '2026-04-29 00:00:00'),
    (2, 1, 'workstation-silent-ryzen-7', 'Workstation Silent Ryzen 7', 'May yen tinh cho da nhiem va content nhe.', 'Phu hop cho back office, creator co workload vua, va nguoi can 32GB RAM.', 22490000, 23990000, 8, 4.9, 1, 'Silent,Creator,Workstation', 'https://images.unsplash.com/photo-1527443154391-507e9dc6c5cc?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80', '{"CPU":"AMD Ryzen 7 7700","RAM":"32GB DDR5","Storage":"1TB NVMe SSD","Cooling":"Tower cooler","Use case":"Creator, office"}', '["Tieng on thap","Mo rong toi 64GB RAM","On dinh cho da nhiem"]', 2, '2026-04-29 00:00:00'),
    (3, 2, 'pc-gaming-rtx-4060-core', 'PC Gaming RTX 4060 Core', 'Build 1080p va 1440p cho eSports va AAA.', 'Cau hinh gaming nghiem tuc cho nguoi muon FPS cao ma van giu ngan sach hop ly.', 28990000, 30990000, 6, 4.7, 1, 'RTX,144Hz', 'https://images.unsplash.com/photo-1547082299-de196ea013d6?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1598550476439-6847785fcea6?auto=format&fit=crop&w=1200&q=80', '{"CPU":"AMD Ryzen 5 7600","GPU":"GeForce RTX 4060 8GB","RAM":"16GB DDR5","Storage":"1TB NVMe SSD","Use case":"FPS, AAA"}', '["FPS cao cho 144Hz","Nguon va airflow de nang cap","Tot cho game online"]', 1, '2026-04-29 00:00:00'),
    (4, 2, 'pc-gaming-rtx-4070-stream', 'PC Gaming RTX 4070 Stream', 'Build 1440p cho gaming, stream, va clip ngan.', 'Can bang giua GPU, CPU, va nhiet do de vua choi game vua stream on dinh.', 38990000, 41990000, 4, 4.9, 1, 'RTX,Streaming,RGB', 'https://images.unsplash.com/photo-1593305841991-05c297ba4575?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1624705002806-5d72df19c3d9?auto=format&fit=crop&w=1200&q=80', '{"CPU":"Intel Core i7-14700F","GPU":"GeForce RTX 4070 Super","RAM":"32GB DDR5","Storage":"1TB NVMe + 2TB HDD","Use case":"Gaming 1440p, stream"}', '["Tot cho stream da nen tang","Case airflow rong","De nang GPU va SSD"]', 2, '2026-04-29 00:00:00'),
    (5, 3, 'monitor-ultragear-27-165hz', 'Monitor UltraGear 27 165Hz', 'IPS QHD 165Hz cho game va cong viec.', 'Monitor dung cho ca gaming lan office, cho mau can bang va khong gian lam viec de chiu.', 6790000, 7490000, 15, 4.8, 0, '144Hz,Creator', 'https://images.unsplash.com/photo-1527443154391-507e9dc6c5cc?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80', '{"Size":"27 inch","Resolution":"QHD","Refresh":"165Hz","Panel":"IPS","Ports":"HDMI, DisplayPort"}', '["Dung cho game va thiet ke","Vien mong","Mau can bang cho lam viec dai gio"]', 1, '2026-04-29 00:00:00'),
    (6, 3, 'keyboard-nova-tkl-wireless', 'Keyboard Nova TKL Wireless', 'Ban phim TKL, hot swap, pin lau.', 'Layout TKL giai phong khong gian chuot nhung van giu trai nghiem go phim day du.', 2290000, 2590000, 20, 4.6, 1, 'RGB,Portable', 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80', '{"Connection":"2.4G, Bluetooth, USB-C","Layout":"TKL 87 keys","Switch":"Linear hot-swap","Battery":"4000mAh","Use case":"Gaming, typing"}', '["Gon hon fullsize","Thay switch de","Linh hoat giua office va gaming"]', 2, '2026-04-29 00:00:00'),
    (7, 4, 'webcam-stream-2k-autofocus', 'Webcam Stream 2K AutoFocus', 'Webcam 2K cho meeting va livestream.', 'Hinh anh net hon webcam laptop tich hop, phu hop meeting, hoc online, va stream co ban.', 1890000, 2190000, 11, 4.4, 0, 'Streaming,Portable', 'https://images.unsplash.com/photo-1587826080692-f439cd0b70da?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1527443154391-507e9dc6c5cc?auto=format&fit=crop&w=1200&q=80', '{"Resolution":"2K 30fps","Mic":"Dual mic","Features":"Auto focus, auto exposure","Connection":"USB","Use case":"Meeting, stream"}', '["Cam vao la dung","Net hon webcam laptop","Phu hop workspace tai nha"]', 1, '2026-04-29 00:00:00'),
    (8, 4, 'dock-usb-c-12in1-pro', 'Dock USB-C 12 in 1 Pro', 'Dock mo rong cong cho laptop va workstation.', 'Bien laptop thanh tram lam viec day du voi HDMI, LAN, USB, SD, va sac PD.', 2490000, 2790000, 13, 4.5, 0, 'Portable,Workstation', 'https://images.unsplash.com/photo-1516321165247-4aa89a48be28?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1200&q=80', '{"Ports":"HDMI, LAN, USB-A, USB-C, SD","Power":"PD 100W","Network":"Gigabit LAN","Body":"Aluminum","Use case":"Workspace"}', '["Giam day cam","Hop cho ultrabook","Bo sung LAN on dinh"]', 3, '2026-04-29 00:00:00');

INSERT INTO services (id, slug, title, eta_label, price_label, description, cover_image, sort_order) VALUES
    (1, 'build-custom', 'Lap may theo ngan sach', 'Trong 24 gio', 'Tu 300.000 VND', 'Chot cau hinh theo workload, ngan sach, va kha nang nang cap.', 'https://images.unsplash.com/photo-1593640408182-31c70c8268f5?auto=format&fit=crop&w=1200&q=80', 1),
    (2, 'upgrade-pc', 'Nang cap PC va workstation', '2-4 gio', 'Tu 250.000 VND', 'Phan tich bottleneck va de xuat luong nang cap hop ly.', 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80', 2),
    (3, 'clean-and-repaste', 'Ve sinh va thay nhiet', '90 phut', 'Tu 180.000 VND', 'Lam sach bui, thay keo tan, va test lai nhiet do.', 'https://images.unsplash.com/photo-1580894908361-967195033215?auto=format&fit=crop&w=1200&q=80', 3),
    (4, 'workspace-setup', 'Trien khai goc lam viec', 'Theo lich hen', 'Tu 500.000 VND', 'Tu van monitor, dock, webcam, router, va bo tri day gon.', 'https://images.unsplash.com/photo-1496171367470-9ed9a91ea931?auto=format&fit=crop&w=1200&q=80', 4);

INSERT INTO payment_methods (id, user_id, method_type, bank_name, account_mask, account_ref, holder_name, note, created_at) VALUES
    (1, 1, 'bank_transfer', 'Ngan hang ABC', '**** 9845', '9845', 'ADMIN WEBPC', 'Tai khoan demo cho admin.', '2026-04-29 00:00:00'),
    (2, 1, 'cod', 'COD', 'Thanh toan khi nhan hang', '', 'ADMIN WEBPC', 'Nhan hang roi thanh toan tien mat hoac chuyen khoan.', '2026-04-29 00:00:00'),
    (3, 1, 'momo', 'MoMo', '**** 0000', '0900000000', 'ADMIN WEBPC', 'Thanh toan qua vi MoMo.', '2026-04-29 00:00:00'),
    (4, 1, 'vnpay', 'VNPay', 'Cong thanh toan VNPay', '', 'ADMIN WEBPC', 'Thanh toan qua QR VNPay hoac ATM noi dia.', '2026-04-29 00:00:00'),
    (5, 1, 'bank_card', 'The ngan hang noi dia', 'ATM / Napas', '', 'ADMIN WEBPC', 'Thanh toan bang the ngan hang noi dia.', '2026-04-29 00:00:00'),
    (6, 1, 'visa', 'Visa / Mastercard / JCB', 'The quoc te', '', 'ADMIN WEBPC', 'Thanh toan bang Visa, Mastercard hoac JCB.', '2026-04-29 00:00:00');
