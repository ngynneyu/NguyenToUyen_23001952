<?php
require_once 'model/product.php';

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
        // Kiểm tra sản phẩm đã tồn tại chưa
        if (getProductByName($name)) {
            $error = "Sản phẩm '$name' đã tồn tại trong danh sách.";
        } else {
            if (addProduct($name, $price, $quantity)) {
                $success = "Thêm sản phẩm thành công!";
                // Reset form sau khi thêm thành công
                unset($name, $price, $quantity);
            } else {
                $error = "Có lỗi xảy ra khi thêm sản phẩm.";
            }
        }
    }
}

include 'view/header.php';
?>

<h2>Thêm sản phẩm</h2>

<?php if ($error): ?>
    <p><font color="red"><b><?= $error ?></b></font></p>
<?php endif; ?>
<?php if ($success): ?>
    <p><font color="green"><b><?= $success ?></b></font></p>
<?php endif; ?>

<form method="POST" action="">
    <div>
        <label>Tên sản phẩm:</label><br>
        <input type="text" name="name" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
    </div>
    <br>
    <div>
        <label>Giá:</label><br>
        <input type="number" step="0.01" name="price" value="<?= isset($price) ? htmlspecialchars($price) : '' ?>" required>
    </div>
    <br>
    <div>
        <label>Số lượng:</label><br>
        <input type="number" name="quantity" value="<?= isset($quantity) ? htmlspecialchars($quantity) : '' ?>" required>
    </div>
    <br>
    <button type="submit">Thêm mới</button>
</form>

<?php include 'view/footer.php'; ?>
