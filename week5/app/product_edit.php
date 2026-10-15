<?php
require_once __DIR__ . '/common/auth.php';
requireLogin();
require_once __DIR__ . '/model/product.php';
require_once __DIR__ . '/common/productValidation.php';

$error = '';
$success = '';
$product = false;
$id = validateProductId($_GET['id'] ?? null);

if ($id === false) {
    http_response_code(400);
    $error = "ID sản phẩm phải là số nguyên từ 1 đến 2147483647.";
} else {
    try {
        $product = getProductById($id);
        if (!$product) {
            http_response_code(404);
            $error = "Sản phẩm không tồn tại.";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(503);
        $error = 'Không thể tải sản phẩm. Vui lòng thử lại.';
    }
}

if ($product && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = is_string($_POST['name'] ?? null) ? trim($_POST['name']) : '';
    $price = is_string($_POST['price'] ?? null) ? trim($_POST['price']) : '';
    $quantity = is_string($_POST['quantity'] ?? null) ? trim($_POST['quantity']) : '';
    $product['name'] = $name;
    $product['price'] = $price;
    $product['quantity'] = $quantity;

    $error = validateProductInput($name, $price, $quantity);
    if (!validCsrfToken()) {
        http_response_code(403);
        $error = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
    }
    if ($error === '') {
        try {
            if (updateProduct($id, $name, $price, (int) $quantity)) {
                $success = "Cập nhật sản phẩm thành công!";
                // Cập nhật lại data hiển thị
                $product = getProductById($id);
            } else {
                $error = "Có lỗi xảy ra khi cập nhật.";
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = "Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.";
        }
    }
}

include __DIR__ . '/view/header.php';
?>

<h2>Sửa sản phẩm</h2>

<?php if ($error): ?>
    <p role="alert"><?= e($error) ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p role="status"><?= e($success) ?></p>
<?php endif; ?>

<?php if ($product): ?>
<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div>
        <label for="name">Tên sản phẩm:</label><br>
        <input type="text" id="name" name="name" maxlength="100" value="<?= e($product['name']) ?>" required>
    </div>
    <br>
    <div>
        <label for="price">Giá:</label><br>
        <input type="number" min="0.01" max="99999999.99" step="0.01" id="price" name="price" value="<?= e($product['price']) ?>" required>
    </div>
    <br>
    <div>
        <label for="quantity">Số lượng:</label><br>
        <input type="number" min="0" max="2147483647" step="1" id="quantity" name="quantity" value="<?= e($product['quantity']) ?>" required>
    </div>
    <br>
    <button type="submit">Lưu thay đổi</button>
</form>
<?php else: ?>
    <a href="product_list.php">Quay lại danh sách</a>
<?php endif; ?>

<?php include __DIR__ . '/view/footer.php'; ?>
