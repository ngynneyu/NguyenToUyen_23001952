<?php
require_once __DIR__ . '/common/auth.php';
requireLogin();
require_once __DIR__ . '/model/product.php';
require_once __DIR__ . '/common/productValidation.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = is_string($_POST['name'] ?? null) ? trim($_POST['name']) : '';
    $price = is_string($_POST['price'] ?? null) ? trim($_POST['price']) : '';
    $quantity = is_string($_POST['quantity'] ?? null) ? trim($_POST['quantity']) : '';

    $error = validateProductInput($name, $price, $quantity);
    if (!validCsrfToken()) {
        http_response_code(403);
        $error = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
    }
    if ($error === '') {
        try {
            // Kiểm tra sản phẩm đã tồn tại chưa
            if (getProductByName($name)) {
                $error = "Sản phẩm '$name' đã tồn tại trong danh sách.";
            } else {
                if (addProduct($name, $price, (int) $quantity)) {
                    $success = "Thêm sản phẩm thành công!";
                    // Reset form sau khi thêm thành công
                    unset($name, $price, $quantity);
                } else {
                    $error = "Có lỗi xảy ra khi thêm sản phẩm.";
                }
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = "Có lỗi xảy ra khi thêm sản phẩm. Vui lòng thử lại.";
        }
    }
}

include __DIR__ . '/view/header.php';
?>

<h2>Thêm sản phẩm</h2>

<?php if ($error): ?>
    <p role="alert"><?= e($error) ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p role="status"><?= e($success) ?></p>
<?php endif; ?>

<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <div>
        <label for="name">Tên sản phẩm:</label><br>
        <input type="text" id="name" name="name" maxlength="100" value="<?= isset($name) ? e($name) : '' ?>" required>
    </div>
    <br>
    <div>
        <label for="price">Giá:</label><br>
        <input type="number" min="0.01" max="99999999.99" step="0.01" id="price" name="price" value="<?= isset($price) ? e($price) : '' ?>" required>
    </div>
    <br>
    <div>
        <label for="quantity">Số lượng:</label><br>
        <input type="number" min="0" max="2147483647" step="1" id="quantity" name="quantity" value="<?= isset($quantity) ? e($quantity) : '' ?>" required>
    </div>
    <br>
    <button type="submit">Thêm mới</button>
</form>

<?php include __DIR__ . '/view/footer.php'; ?>
