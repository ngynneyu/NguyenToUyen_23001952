<?php
require_once __DIR__ . '/bootstrap.php';
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$db = getenv('DB_NAME') ?: 'shopping_cart';
$user = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(503);
    include __DIR__ . '/../view/header.php';
    echo '<h2>Không thể kết nối cơ sở dữ liệu</h2>';
    echo '<p role="alert">Vui lòng bật MySQL trong XAMPP, nhập file database.sql và kiểm tra cấu hình kết nối.</p>';
    include __DIR__ . '/../view/footer.php';
    exit;
}
