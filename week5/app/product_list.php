<?php
require_once __DIR__ . '/common/auth.php';
requireLogin();
require_once __DIR__ . '/model/product.php';

$error = '';
$products = [];
try {
    $products = getAllProducts();
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(503);
    $error = 'Không thể tải danh sách sản phẩm. Vui lòng thử lại.';
}

include __DIR__ . '/view/header.php';
?>

<h2>Danh sách sản phẩm</h2>
<?php if ($error): ?><p role="alert"><?= e($error) ?></p><?php endif; ?>
<table border="1">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên sản phẩm</th>
            <th>Giá</th>
            <th>Số lượng</th>
            <th>Chức năng</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($error): ?>
            <tr><td colspan="5">Không thể tải dữ liệu.</td></tr>
        <?php elseif (empty($products)): ?>
            <tr><td colspan="5">Chưa có sản phẩm nào.</td></tr>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?= e($p['id']) ?></td>
                <td><?= e($p['name']) ?></td>
                <td><?= number_format($p['price'], 2) ?> VNĐ</td>
                <td><?= e($p['quantity']) ?></td>
                <td>
                    <a href="product_edit.php?id=<?= $p['id'] ?>">Sửa</a> |
                    <a href="product_delete.php?id=<?= $p['id'] ?>">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/view/footer.php'; ?>
