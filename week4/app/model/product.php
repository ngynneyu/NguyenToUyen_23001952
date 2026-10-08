<?php
require_once __DIR__ . '/../common/dbConnect.php';

function getAllProducts() {
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM products ORDER BY id DESC');
    return $stmt->fetchAll();
}

function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getProductByName($name) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM products WHERE name = ?');
    $stmt->execute([$name]);
    return $stmt->fetch();
}

function addProduct($name, $price, $quantity) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO products (name, price, quantity) VALUES (?, ?, ?)');
    return $stmt->execute([$name, $price, $quantity]);
}

function updateProduct($id, $name, $price, $quantity) {
    global $pdo;
    $stmt = $pdo->prepare('UPDATE products SET name = ?, price = ?, quantity = ? WHERE id = ?');
    return $stmt->execute([$name, $price, $quantity, $id]);
}

function deleteProduct($id) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    return $stmt->execute([$id]);
}
?>
