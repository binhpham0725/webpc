# ĐộPICI

Website bán PC, thiết bị và dịch vụ chạy trên PHP 8, Bootstrap 5 và MySQL trong XAMPP.

## Cấu trúc thư mục

- `index.php`, `products.php`, `product.php`, `cart.php`, `account.php`, `services.php`, `login.php`: các trang public.
- `add-product.php`: trang quản trị thêm, sửa và xoá sản phẩm.
- `includes/`: bootstrap, helper, xử lý form, header và footer dùng chung.
- `assets/css/`, `assets/js/`: giao diện và JavaScript.
- `database/schema.mysql.sql`: schema và dữ liệu mẫu cho MySQL/MariaDB.
- `database/migrate_sqlite_to_mysql.php`: script chuyển dữ liệu SQLite cũ sang MySQL.
- `database/legacy/schema.sqlite.sql`: schema SQLite cũ, chỉ giữ để tham khảo.
- `legacy/html-redirects/`: các file HTML redirect cũ đã tách khỏi thư mục chạy chính.
- `storage/sessions/`, `storage/uploads/`: dữ liệu runtime local, không commit lên Git.

## Chạy nhanh trên XAMPP

1. Bật Apache và MySQL trong XAMPP.
2. Mở `http://localhost/xampp/webpc/index.php`.
3. Nếu muốn chạy bằng PHP built-in server:

```powershell
php -S 127.0.0.1:8087 -t .
```

Sau đó mở `http://127.0.0.1:8087/index.php`.

## Database

Ứng dụng tự tạo database `webpc` trong MySQL khi chạy lần đầu nếu database chưa tồn tại.

Thông tin mặc định:

- Host: `127.0.0.1`
- Port: `3306`
- Database: `webpc`
- User: `root`
- Password: rỗng

Có thể đổi cấu hình bằng biến môi trường:

- `WEBPC_DB_HOST`
- `WEBPC_DB_PORT`
- `WEBPC_DB_NAME`
- `WEBPC_DB_USER`
- `WEBPC_DB_PASS`

Chuyển dữ liệu từ SQLite cũ sang MySQL:

```powershell
php database/migrate_sqlite_to_mysql.php
```

## Tài khoản quản trị

- Email: `admin@webpc.local`
- Mật khẩu: `Admin@12345`

## Chức năng chính

- Đăng nhập, đăng ký, hồ sơ và avatar upload từ máy local.
- Giỏ hàng, checkout và lưu đơn hàng vào MySQL.
- Phương thức thanh toán phổ biến tại Việt Nam: MoMo, VNPay, ZaloPay, thẻ ngân hàng, Visa/Mastercard, chuyển khoản và COD.
- Quản trị thêm, sửa, xoá sản phẩm.
- Ảnh chính một file và ảnh phụ nhiều file cho sản phẩm.
- Thông số kỹ thuật và điểm nổi bật nhập bằng giao diện nhiều dòng dễ thao tác.
