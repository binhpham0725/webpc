# webpc

Storefront bán thiết bị máy tính viết bằng:

- PHP 8
- Bootstrap 5
- CSS custom cho hiệu ứng liquid glass
- JavaScript nhỏ để tăng trải nghiệm form và quantity
- SQLite qua PDO để chạy tự động trong XAMPP

## Chạy nhanh

Từ thư mục `webpc`:

```powershell
php -S 127.0.0.1:8027 -t .
```

Mở:

- `http://127.0.0.1:8027/index.php`

## Database

- File schema: `database/schema.sql`
- File SQLite tự sinh khi chạy: `database/webpc.sqlite`

Database sẽ tự khởi tạo ở lần chạy đầu tiên, không cần import thủ công.

## Trang chính

- `index.php`: trang chủ
- `products.php`: danh mục, tìm kiếm, lọc
- `product.php`: chi tiết sản phẩm
- `cart.php`: giỏ hàng và checkout
- `account.php`: hồ sơ khách hàng, phương thức thanh toán, lịch sử đơn
- `services.php`: dịch vụ và form gửi yêu cầu

## Luồng đã có

- Thêm sản phẩm vào giỏ hàng qua PHP session
- Cập nhật / xóa giỏ hàng
- Áp mã demo `NVIDIA`
- Checkout lưu vào database
- Cập nhật hồ sơ khách hàng
- Thêm / xóa phương thức thanh toán
- Gửi yêu cầu dịch vụ
- Lưu email newsletter
