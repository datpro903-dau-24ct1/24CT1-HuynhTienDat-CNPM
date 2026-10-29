# 🎓 IT CONNECT - Mạng Xã Hội Sinh Viên Công Nghệ Thông Tin

> **IT CONNECT** là nền tảng mạng xã hội nội bộ dành cho sinh viên và ban quản trị khoa Công nghệ Thông tin, hỗ trợ kết nối, chia sẻ học tập, trao đổi tài liệu và nhắn tin trực tuyến.

---

## 👥 Hệ thống Phân quyền Người dùng (User Roles & Actors)

Hệ thống được thiết kế với **2 vai trò (Roles)** độc lập, có luồng điều hướng và quyền hạn riêng biệt:

### 1. 🎓 Sinh viên (Student Role)
Sinh viên sau khi đăng ký và đăng nhập thành công sẽ được cấp quyền truy cập vào toàn bộ các phân hệ mạng xã hội:
- **Trang chủ & Bảng tin (`Home.jsx`):** Đăng bài viết chia sẻ kiến thức, đính kèm hình ảnh/video/tài liệu, tương tác Thích (Like) và Bình luận (Comment).
- **Bạn bè & Kết nối (`Friends.jsx`):** Tìm kiếm sinh viên theo tên, mã sinh viên, lớp; gửi, nhận và chấp nhận lời mời kết bạn.
- **Trò chuyện & Nhắn tin (`Messages.jsx`):** Nhắn tin trực tuyến thời gian thực với bạn bè, hỗ trợ popup chat nhanh tại trang chủ.
- **Thông báo tương tác (`Notifications.jsx`):** Nhận thông báo tự động khi có tương tác (tin nhắn mới, ai đó like/comment bài viết, lời mời kết bạn).
- **Hồ sơ cá nhân (`Account.jsx`):** Đổi ảnh đại diện, cập nhật thông tin học tập và tự đổi mật khẩu tài khoản.

### 2. 🛡️ Quản trị viên (Administrator Role)
Tài khoản có quyền `role === 'admin'` được phân luồng truy cập riêng biệt thông qua cổng bảo vệ `AdminRoute`:
- **Bảng điều khiển Quản trị (`Admin.jsx`):**
  - Giám sát toàn bộ bài viết trên hệ thống mạng xã hội.
  - Kiểm duyệt và xóa bỏ các bài viết vi phạm nội quy cộng đồng.
  - Quản lý và bảo đảm an toàn thông tin nền tảng.

---

## 🏗️ Kiến trúc Hệ thống (System Architecture)

```
[Users]
  ├── 🎓 Student ──────► [ProtectedRoute] ──► (Home, Friends, Messages, Notifications, Account)
  └── 🛡️ Administrator ─► [AdminRoute]    ──► (Admin Console)
                                │
                          [App Router]
                                │
                        [Backend PHP API]
                                │
                        [MySQL Database]
```

### 1. Phân luồng Định tuyến (Role-Based Routing)
- **Cổng mở (Public Routes):** `/login`, `/register` (cho phép khách truy cập hoặc chuyển hướng nếu đã đăng nhập).
- **Cổng bảo vệ Sinh viên (Student Protected Routes):** Được bảo vệ bởi `ProtectedRoute.jsx` (yêu cầu đăng nhập):
  - `/home`: Bảng tin sinh viên
  - `/friends`: Quản lý bạn bè
  - `/messages`: Khung nhắn tin
  - `/notifications`: Trung tâm thông báo
  - `/account`: Trang cá nhân
- **Cổng bảo vệ Quản trị (Admin Protected Routes):** Được bảo vệ bởi `AdminRoute.jsx` (yêu cầu `role === 'admin'`):
  - `/admin`: Bảng điều khiển quản trị viên

### 2. Công nghệ sử dụng
- **Frontend:** React 18, Vite, React Router DOM, CSS3 Responsive.
- **Backend:** PHP 8, RESTful API, MySQL Database (InfinityFree / XAMPP).
- **Bảo mật:** Mã hóa mật khẩu bằng thuật toán Bcrypt (`password_hash`), xác thực dữ liệu đầu vào.

---

## 📂 Cấu trúc Thư mục Dự án

```text
24CT1-HUYNH_TIEN_DAT/
├── backend/
│   └── php/
│       ├── config/         # Cấu hình kết nối MySQL database
│       ├── auth/           # API Đăng ký, Đăng nhập
│       ├── account/        # API Cập nhật hồ sơ, avatar, đổi mật khẩu
│       ├── posts/          # API Bài viết, Likes, Comments, Quản lý xóa bài
│       ├── friends/        # API Danh sách bạn bè, gửi/nhận kết bạn
│       ├── messages/       # API Nhắn tin trò chuyện
│       └── notifications/  # API Thông báo tương tác
├── src/
│   ├── assets/             # Hình ảnh, biểu tượng
│   ├── pages/              # Các trang giao diện chính
│   │   ├── Login.jsx       # Giao diện Đăng nhập
│   │   ├── Register.jsx    # Giao diện Đăng ký
│   │   ├── Home.jsx        # Bảng tin Sinh viên
│   │   ├── Friends.jsx     # Trang Bạn bè
│   │   ├── Messages.jsx    # Trang Tin nhắn
│   │   ├── Notifications.jsx # Trang Thông báo
│   │   ├── Account.jsx     # Trang Cá nhân & Đổi mật khẩu
│   │   └── Admin.jsx       # Bảng điều khiển Quản trị viên
│   ├── App.jsx             # Hệ thống định tuyến chính (Router)
│   ├── ProtectedRoute.jsx  # Bảo vệ Route cho tài khoản Sinh viên
│   └── utils.js            # Cấu hình API URL và hàm tiện ích phân quyền
├── index.html
├── package.json
└── vite.config.js
```

---

## 🚀 Hướng dẫn Cài đặt & Chạy ứng dụng

### 1. Cài đặt Frontend
```bash
# Cài đặt các gói phụ thuộc
npm install

# Chạy ở môi trường phát triển
npm run dev

# Đóng gói sản phẩm (Build production)
npm run build
```

### 2. Cấu hình Backend
- Đặt thư mục `backend/php` vào máy chủ web (XAMPP `htdocs` hoặc Hosting cPanel).
- Cập nhật thông tin kết nối cơ sở dữ liệu trong `backend/php/config/database.php`.
- Cập nhật địa chỉ `API` trong `src/utils.js` trỏ về server backend tương ứng.

---
