-- ==========================================
-- BÀI 1 – QUẢN LÝ GIỎ HÀNG
-- ==========================================

-- Tạo database (nếu chưa có) và sử dụng
CREATE DATABASE IF NOT EXISTS shopping_cart;
USE shopping_cart;

-- 1. Tạo bảng cart_items
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL
);

-- 2.1 Thêm ít nhất 5 sản phẩm vào bảng
INSERT INTO cart_items (name, price, quantity) VALUES
('Áo thun', 150000.00, 2),
('Quần Jean', 300000.00, 1),
('Giày Sneaker', 500000.00, 1),
('Mũ lưỡi trai', 50000.00, 6),
('Balo laptop', 250000.00, 10);

-- 2.2 Hiển thị toàn bộ sản phẩm
SELECT * FROM cart_items;

-- 2.3 Hiển thị sản phẩm có giá lớn hơn 100000
SELECT * FROM cart_items WHERE price > 100000;

-- 2.4 Hiển thị sản phẩm có số lượng lớn hơn 5
SELECT * FROM cart_items WHERE quantity > 5;

-- 2.5 Sắp xếp sản phẩm theo giá giảm dần
SELECT * FROM cart_items ORDER BY price DESC;

-- 2.6 Cập nhật giá của một sản phẩm (VD: Cập nhật giá Áo thun)
UPDATE cart_items SET price = 120000.00 WHERE name = 'Áo thun';

-- 2.7 Cập nhật số lượng của một sản phẩm (VD: Cập nhật số lượng Quần Jean)
UPDATE cart_items SET quantity = 3 WHERE name = 'Quần Jean';

-- 2.8 Xóa một sản phẩm (VD: Xóa Balo laptop)
DELETE FROM cart_items WHERE name = 'Balo laptop';

-- 2.9 Hiển thị tên sản phẩm, giá, số lượng và thành tiền (price * quantity)
SELECT name, price, quantity, (price * quantity) AS total_price FROM cart_items;

-- 2.10 Tính tổng tiền của toàn bộ giỏ hàng
SELECT SUM(price * quantity) AS cart_total FROM cart_items;


-- ==========================================
-- BÀI 2 – QUẢN LÝ VÉ XEM PHIM
-- ==========================================

-- Tạo database riêng cho bài 2 và sử dụng
CREATE DATABASE IF NOT EXISTS movie_management;
USE movie_management;

-- 1. Tạo bảng movies
CREATE TABLE IF NOT EXISTS movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total_seats INT NOT NULL,
    available_seats INT NOT NULL
);

-- 2.1 Thêm ít nhất 5 bộ phim
INSERT INTO movies (title, price, total_seats, available_seats) VALUES
('Avengers', 100000.00, 100, 95),
('Avatar', 120000.00, 80, 50),
('Batman', 90000.00, 120, 120),
('Spider-Man', 110000.00, 150, 40),
('Inception', 130000.00, 90, 85);

-- 2.2 Hiển thị toàn bộ danh sách phim
SELECT * FROM movies;

-- 2.3 Hiển thị phim có giá vé lớn hơn 100000
SELECT * FROM movies WHERE price > 100000;

-- 2.4 Hiển thị phim còn nhiều hơn 50 ghế
SELECT * FROM movies WHERE available_seats > 50;

-- 2.5 Sắp xếp phim theo giá vé giảm dần
SELECT * FROM movies ORDER BY price DESC;

-- 2.6 Cập nhật số ghế còn lại của một phim (VD: Avengers bị mua thêm 5 vé -> còn 90 ghế)
UPDATE movies SET available_seats = 90 WHERE title = 'Avengers';

-- 2.7 Xóa một phim (VD: Xóa Batman)
DELETE FROM movies WHERE title = 'Batman';

-- 2.8 Hiển thị số vé đã bán của từng phim: total_seats - available_seats
SELECT title, total_seats, available_seats, (total_seats - available_seats) AS sold_tickets FROM movies;

-- 2.9 Tính doanh thu của từng phim: (total_seats - available_seats) * price
SELECT title, (total_seats - available_seats) * price AS revenue FROM movies;

-- 2.10 Tính tổng doanh thu của tất cả các phim
SELECT SUM((total_seats - available_seats) * price) AS total_revenue FROM movies;

-- 2.11 Tìm phim có số vé bán ra nhiều nhất
SELECT title, (total_seats - available_seats) AS sold_tickets 
FROM movies 
WHERE (total_seats - available_seats) = (
    SELECT MAX(total_seats - available_seats) FROM movies
);
