# Fashion Shop E-commerce Website

Dự án Cửa hàng Thời trang (Native PHP + MySQL). Không sử dụng Framework.

## Yêu cầu Hệ thống
- PHP 8.x
- MySQL / MariaDB
- XAMPP / MAMP / WAMP

## Cài đặt Môi trường XAMPP
1. Clone hoặc copy source code vào thư mục `C:\xampp\htdocs\fashion-shop`.
2. Tạo file `.env` từ file mẫu `.env.example`:
   - Copy nội dung `.env.example` và lưu thành `.env`.
   - Chỉnh sửa thông tin database (thông thường trên XAMPP `DB_USER` là `root` và `DB_PASSWORD` để trống).
3. Khởi động Apache và MySQL trên XAMPP Control Panel.
4. Truy cập phpMyAdmin `http://localhost/phpmyadmin/` và tạo database (ví dụ: `fashion_shop_db`).
5. Import file SQL schema (chưa chạy) để tạo các bảng (tham khảo `/database/schema_v1.sql`).
6. Mở trình duyệt và truy cập: `http://localhost/fashion-shop/`

## Cấu trúc Dự án
Dự án được xây dựng theo chuẩn MVC cơ bản (không dùng framework):
- `app/`: Chứa các controller, model, helper, middleware và config.
- `public/`: Source code dành cho khách hàng (Storefront).
- `admin/`: Source code dành cho quản trị viên (Admin Panel).
- `assets/`: File CSS, JS, hình ảnh, icon tĩnh.
- `includes/`: Chứa header, footer và các UI component dùng chung.
- `storage/`: Chứa các file upload, logs, cache.

## Tuân thủ chuẩn Code
Mọi phát triển đều phải dựa trên file `SKILL.md` (chuẩn Naming, Security, DB Schema, MVC) để đảm bảo đồng nhất trên toàn dự án.
