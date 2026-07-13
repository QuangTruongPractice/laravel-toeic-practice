# Edulys — Nền tảng luyện thi TOEIC

Edulys là ứng dụng web luyện thi TOEIC được xây dựng với Laravel. Người dùng có thể làm bài theo Part hoặc đề thi; quản trị viên có thể quản lý đề, câu hỏi và import đề TOEIC từ PDF, audio ZIP và image ZIP.

## Bản demo đã triển khai

- Ứng dụng web: [https://toeic-practice-fzcrcda7btfbfvhp.southeastasia-01.azurewebsites.net](https://toeic-practice-fzcrcda7btfbfvhp.southeastasia-01.azurewebsites.net)
- Parser service: [https://parser-service-hdd5djdkgmdugphk.southeastasia-01.azurewebsites.net](https://parser-service-hdd5djdkgmdugphk.southeastasia-01.azurewebsites.net)

Hai địa chỉ trên cũng tương ứng với các biến môi trường `APP_URL` và `APP_PYTHON` của môi trường production.

## Công nghệ

- Laravel 13, PHP 8.3+
- Blade, Tailwind CSS và Vite
- SQLite (mặc định cho môi trường local) hoặc MySQL/TiDB Cloud
- Database queue cho các tác vụ nền, bao gồm import đề

## Yêu cầu môi trường

- PHP 8.3+ cùng các extension phổ biến của Laravel: `pdo_sqlite` (hoặc `pdo_mysql`), `mbstring`, `xml`, `curl`, `zip` và `fileinfo`
- [Composer](https://getcomposer.org/)
- Node.js 20+ và npm
- Git

> Chức năng import giải nén file ZIP, vì vậy PHP phải bật extension `zip`/`ZipArchive`.

## Cài đặt và khởi tạo local

Clone repository, sau đó cài các dependency:

```bash
git clone https://github.com/QuangTruongPractice/laravel-toeic-practice.git
cd laravel-toeic-practice
composer install
npm install
```

Tạo file môi trường và application key:

```bash
cp .env.example .env
php artisan key:generate
```

Trên Windows PowerShell, dùng lệnh sau thay cho `cp`:

```powershell
Copy-Item .env.example .env
```

### Cấu hình database

Mặc định `.env.example` sử dụng SQLite. Tạo database rỗng trước khi migrate:

```bash
# macOS / Linux
touch database/database.sqlite
```

```powershell
# Windows PowerShell
New-Item -ItemType File -Path database/database.sqlite -Force
```

Sau đó tạo các bảng, bao gồm bảng `jobs` dùng cho queue, và seed dữ liệu Part cùng tài khoản admin mẫu:

```bash
php artisan migrate
php artisan db:seed
```

Tài khoản admin được seed sẵn:

```text
Email: tranquangtruong25@gmail.com
Mật khẩu: password
```

Để dùng MySQL hoặc TiDB Cloud, cập nhật các biến `DB_*` trong `.env` (ví dụ `DB_CONNECTION=mysql`) rồi chạy lại `php artisan migrate` và `php artisan db:seed`.

### Liên kết file public

Audio và hình ảnh của đề sau khi import được lưu trong `storage/app/public`. Chạy lệnh sau một lần để ứng dụng có thể truy cập các file này qua `/storage`:

```bash
php artisan storage:link
```

## Chạy ứng dụng

Mở **ba cửa sổ terminal** trong thư mục dự án và chạy các lệnh sau.

Terminal 1 — Laravel web server:

```bash
php artisan serve
```

Truy cập ứng dụng tại [http://127.0.0.1:8000](http://127.0.0.1:8000).

Terminal 2 — Vite để build/HMR CSS và JavaScript khi phát triển:

```bash
npm run dev
```

Terminal 3 — queue worker (bắt buộc khi import đề):

```bash
php artisan queue:work
```

Khi quản trị viên gửi form import, ứng dụng tạo `ImportExamJob` và lưu job vào database. Worker sẽ lấy job này, tạo đề/câu hỏi, đồng thời sao chép audio và hình ảnh vào thư mục public. Nếu không chạy `php artisan queue:work`, import sẽ dừng ở trạng thái chờ (`pending`).

Có thể chạy nhanh tất cả tiến trình phục vụ phát triển bằng:

```bash
composer run dev
```

Lệnh này khởi động Laravel server, queue listener, Laravel Pail và Vite. Khi cần worker ổn định hơn để xử lý import, ưu tiên chạy riêng `php artisan queue:work` như hướng dẫn ở trên.

## Import đề TOEIC

1. Đăng nhập bằng tài khoản admin và mở trang `/admin/imports`.
2. Chuẩn bị các file bắt buộc: Listening PDF, Reading PDF, audio ZIP và image ZIP.
3. Đảm bảo parser service đang có thể truy cập. Có thể đặt endpoint riêng trong `.env`:

   ```env
   APP_PYTHON=https://your-parser-service
   ```

   Nếu không cấu hình, ứng dụng sử dụng parser service mặc định được khai báo trong mã nguồn.
4. Gửi form import và theo dõi trạng thái import tại trang quản trị. Giữ `php artisan queue:work` chạy đến khi trạng thái chuyển thành `completed` hoặc `failed`.

## Lệnh hữu ích

```bash
# Chạy test
php artisan test

# Kiểm tra các job bị lỗi
php artisan queue:failed

# Chạy lại một job lỗi theo UUID
php artisan queue:retry <uuid>

# Xóa cache cấu hình khi vừa chỉnh .env
php artisan config:clear

# Build asset cho production
npm run build
```

## Production

Tham khảo [hướng dẫn deploy](docs/DEPLOYMENT.md) và file mẫu [`.env.production.example`](.env.production.example). Trên production, web server, queue worker và scheduler cần được vận hành liên tục bằng công cụ quản lý tiến trình phù hợp (ví dụ Supervisor).

## License

MIT License.
