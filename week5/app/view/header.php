<?php require_once __DIR__ . '/../common/auth.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quản lý sản phẩm</title>
</head>
<body>
<header>
    <h1>Hệ thống quản lý Giỏ hàng</h1>
    <nav aria-label="Điều hướng chính">
        <?php if (isLoggedIn()): ?>
            <a href="product_list.php">Danh sách sản phẩm</a> |
            <a href="product_add.php">Thêm sản phẩm</a> |
            <span>Tài khoản: <strong><?= e($_SESSION['username']) ?></strong></span> |
            <a href="logout.php">Đăng xuất</a>
        <?php else: ?>
            <a href="login.php">Đăng nhập</a> |
            <a href="register.php">Đăng ký</a>
        <?php endif; ?>
    </nav>
</header>
<hr>
<main>
