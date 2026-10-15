<?php
require_once __DIR__ . '/common/auth.php';
requireLogin();
require_once __DIR__ . '/model/product.php';
require_once __DIR__ . '/common/productValidation.php';
$id = validateProductId($_GET['id'] ?? null);
$product = false;
$error = '';
$success = '';
if ($id === false) {
    http_response_code(400);
    $error = 'ID sản phẩm phải là số nguyên từ 1 đến 2147483647.';
} else {
    try {
        $product = getProductById($id);
        if (!$product) {
            http_response_code(404);
            $error = 'Sản phẩm không tồn tại hoặc đã bị xóa.';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validCsrfToken()) {
                http_response_code(403);
                $error = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
            } elseif (postString('confirm') === '1') {
                if (deleteProduct($id)) {
                    $success = 'Đã xóa sản phẩm thành công!';
                    $product = false;
                } else {
                    http_response_code(404);
                    $error = 'Sản phẩm không tồn tại hoặc đã bị xóa.';
                    $product = false;
                }
            } else {
                $error = 'Vui lòng xác nhận trước khi xóa sản phẩm.';
            }
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        http_response_code(503);
        $error = 'Không thể xử lý sản phẩm. Vui lòng thử lại.';
    }
}
include __DIR__ . '/view/header.php';
?>
<h2>Xóa sản phẩm</h2>
<?php if ($error): ?><p role="alert"><?= e($error) ?></p><?php endif; ?>
<?php if ($success): ?><p role="status"><?= e($success) ?></p><?php endif; ?>
<?php if ($product): ?>
<p>Bạn có chắc chắn muốn xóa sản phẩm <strong><?= e($product['name']) ?></strong> không?</p>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <button type="submit" name="confirm" value="1">Xác nhận xóa</button>
    <a href="product_list.php">Hủy</a>
</form>
<?php else: ?>
<a href="product_list.php">Quay lại danh sách</a>
<?php endif; ?>
<?php include __DIR__ . '/view/footer.php'; ?>
