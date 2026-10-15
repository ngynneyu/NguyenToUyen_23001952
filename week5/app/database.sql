CREATE DATABASE IF NOT EXISTS shopping_cart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shopping_cart;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL
);

-- Chỉ thêm dữ liệu mẫu khi bảng sản phẩm còn trống.
INSERT INTO products (name, price, quantity)
SELECT seed.name, seed.price, seed.quantity FROM (
    SELECT 'Áo phông nam' AS name, 150000.00 AS price, 20 AS quantity
    UNION ALL SELECT 'Quần jean nữ', 350000.00, 15
    UNION ALL SELECT 'Giày thể thao', 550000.00, 10
    UNION ALL SELECT 'Mũ lưỡi trai', 80000.00, 30
    UNION ALL SELECT 'Balo laptop', 450000.00, 5
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM products);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Tài khoản mẫu: admin / Admin@123. Hash sinh bằng password_hash().
-- Nhập lại file SQL không ghi đè mật khẩu tài khoản đã có.
INSERT INTO users (username, password)
SELECT 'admin', '$2y$10$j3CiBHKuTFsot8WCii3mrelfXIlTA9pyUFAFYHAErmTNUzaCcdYyy'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin');
