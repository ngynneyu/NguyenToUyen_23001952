# Bài thực hành 7 - Vue.js 3

Ba bài tập độc lập, dùng Vue 3 Options API, HTML, CSS và JavaScript. Giao diện đơn giản theo week6.

## Cách chạy

1. Đặt thư mục `week7` trong `htdocs` của XAMPP.
2. Bật Apache và mở **http://localhost/week7/**.
3. Chọn bài tập trên trang tổng quan.

Vue và ảnh SVG được lưu cục bộ. Không cần npm, build, MySQL hoặc Internet.

## Nội dung

| Trang | Chức năng |
| --- | --- |
| `bai1.html`, `assets/bai1.js` | Tìm kiếm không phân biệt hoa/thường; lọc danh mục; sắp xếp không đổi mảng gốc; thêm và kiểm tra tên trùng; xóa có xác nhận; computed thống kê; watch từ khóa một ký tự; mounted đặt trạng thái Sẵn sàng. |
| `bai2.html`, `assets/bai2.js`, `components/TheSanPham.js` | Sáu sản phẩm qua component có props kiểu dữ liệu, required và default; mô tả mở độc lập; mua giảm tồn kho; hết hàng khóa nút Mua. |
| `bai3.html`, `assets/bai3.js`, `components/DongGioHang.js` | Cha quản lý giỏ hàng; con nhận props và phát `doi-so-luong`, `yeu-cau-xoa`; số lượng nguyên 1-99; xóa có xác nhận; computed tổng số lượng và tổng tiền; trạng thái giỏ trống. |

Giỏ mẫu: **2 bút × 5.000 đ + 3 vở × 12.000 đ = 5 sản phẩm, 46.000 đ**.

Dữ liệu của từng bài nằm trong bộ nhớ trang và trở về mẫu khi tải lại. Bài 3 có nút Khôi phục mẫu. Thao tác mua chỉ mô phỏng trong Bài 2.

## Kiểm tra

Nếu máy có Node.js, chạy từ thư mục `week7`:

```text
node tests/regression.cjs
```

Bộ kiểm tra dùng reactivity của Vue cục bộ, không cần cài thêm thư viện.

Vue 3.5.13 được phân phối theo giấy phép MIT; giữ nguyên thông tin bản quyền tại `assets/vendor/LICENSE` và đầu tệp thư viện.
