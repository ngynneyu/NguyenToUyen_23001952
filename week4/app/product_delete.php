<?php
require_once 'model/product.php';

if (!isset($_GET['id'])) {
    die("Thiếu thông tin ID sản phẩm.");
}

$id = $_GET['id'];
$product = getProductById($id);

include 'view/header.php';

if (!$product) {
    echo "<h2>Lỗi</h2>";
    echo "<p><font color='red'><b>Sản phẩm không tồn tại hoặc đã bị xóa.</b></font></p>";
} else {
    // Nếu người dùng submit form xác nhận xóa
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
        if (deleteProduct($id)) {
            echo "<h2>Thành công</h2>";
            echo "<p><font color='green'><b>Đã xóa sản phẩm thành công!</b></font></p>";
            echo "<a href='product_list.php'>Quay lại danh sách</a>";
        } else {
            echo "<h2>Lỗi</h2>";
            echo "<p><font color='red'><b>Có lỗi xảy ra trong quá trình xóa.</b></font></p>";
        }
    } else {
        // Form xác nhận xóa
        ?>
        <h2>Xóa sản phẩm</h2>
        <p>Bạn có chắc chắn muốn xóa sản phẩm <strong><?= htmlspecialchars($product['name']) ?></strong> không?</p>
        <form method="POST" action="">
            <button type="submit" name="confirm" value="1">Xác nhận xóa</button>
            <a href="product_list.php">Hủy</a>
        </form>
        <?php
    }
}

include 'view/footer.php';
?>
