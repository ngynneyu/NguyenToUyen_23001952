<?php
require_once 'model/product.php';

$products = getAllProducts();

include 'view/header.php';
?>

<h2>Danh sách sản phẩm</h2>
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
        <?php if (empty($products)): ?>
            <tr><td colspan="5">Chưa có sản phẩm nào.</td></tr>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['id']) ?></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= number_format($p['price'], 2) ?> VNĐ</td>
                <td><?= htmlspecialchars($p['quantity']) ?></td>
                <td>
                    <a href="product_edit.php?id=<?= $p['id'] ?>">Sửa</a> | 
                    <a href="product_delete.php?id=<?= $p['id'] ?>">Xóa</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'view/footer.php'; ?>
