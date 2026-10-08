<?php
require_once 'model/product.php';

if (!isset($_GET['id'])) {
    die("Thiếu thông tin ID sản phẩm.");
}

$id = $_GET['id'];
$product = getProductById($id);

if (!$product) {
    die("Sản phẩm không tồn tại.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? '';
    $quantity = $_POST['quantity'] ?? '';

    if (empty($name)) {
        $error = "Tên sản phẩm không được rỗng.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Giá phải là số và lớn hơn 0.";
    } elseif (!is_numeric($quantity) || $quantity < 0) {
        $error = "Số lượng phải là số và lớn hơn hoặc bằng 0.";
    } else {
        if (updateProduct($id, $name, $price, $quantity)) {
            $success = "Cập nhật sản phẩm thành công!";
            // Cập nhật lại data hiển thị
            $product = getProductById($id);
        } else {
            $error = "Có lỗi xảy ra khi cập nhật.";
        }
    }
}

include 'view/header.php';
?>

<h2>Sửa sản phẩm</h2>

<?php if ($error): ?>
    <p><font color="red"><b><?= $error ?></b></font></p>
<?php endif; ?>
<?php if ($success): ?>
    <p><font color="green"><b><?= $success ?></b></font></p>
<?php endif; ?>

<form method="POST" action="">
    <div>
        <label>Tên sản phẩm:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
    </div>
    <br>
    <div>
        <label>Giá:</label><br>
        <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
    </div>
    <br>
    <div>
        <label>Số lượng:</label><br>
        <input type="number" name="quantity" value="<?= htmlspecialchars($product['quantity']) ?>" required>
    </div>
    <br>
    <button type="submit">Lưu thay đổi</button>
</form>

<?php include 'view/footer.php'; ?>
