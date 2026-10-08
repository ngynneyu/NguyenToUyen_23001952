CREATE DATABASE IF NOT EXISTS shopping_cart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE shopping_cart;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL
);

INSERT INTO products (name, price, quantity) VALUES
('Áo phông nam', 150000.00, 20),
('Quần jean nữ', 350000.00, 15),
('Giày thể thao', 550000.00, 10),
('Mũ lưỡi trai', 80000.00, 30),
('Balo laptop', 450000.00, 5);
