<?php
require_once __DIR__ . '/../common/dbConnect.php';
function getUserByUsername($username) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ?');
    $stmt->execute([$username]);
    return $stmt->fetch();
}
function addUser($username, $password) {
    global $pdo;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
    return $stmt->execute([$username, $hash]);
}
