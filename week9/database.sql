CREATE DATABASE IF NOT EXISTS week9
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE week9;

CREATE TABLE IF NOT EXISTS products (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price INT NOT NULL CHECK (price > 0),
    stock INT NOT NULL CHECK (stock >= 0),
    image VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

INSERT INTO products (name, price, stock)
SELECT mau.name, mau.price, mau.stock
FROM (
    SELECT 'Bút bi' AS name, 5000 AS price, 20 AS stock
    UNION ALL SELECT 'Vở ô ly', 12000, 15
    UNION ALL SELECT 'Thước kẻ', 8000, 0
    UNION ALL SELECT 'Bút chì gỗ', 6000, 25
    UNION ALL SELECT 'Gôm tẩy', 7000, 18
    UNION ALL SELECT 'Hộp bút', 35000, 10
    UNION ALL SELECT 'Bút dạ quang', 15000, 14
    UNION ALL SELECT 'Sổ tay', 32000, 12
    UNION ALL SELECT 'Kéo học sinh', 18000, 9
    UNION ALL SELECT 'Keo dán giấy', 10000, 24
    UNION ALL SELECT 'Giấy ghi chú', 22000, 16
    UNION ALL SELECT 'Bìa hồ sơ', 9000, 30
) AS mau
WHERE NOT EXISTS (SELECT 1 FROM products);
