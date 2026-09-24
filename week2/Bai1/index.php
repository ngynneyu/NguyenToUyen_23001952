<?php
require_once 'ShoppingCart.php';

echo "<pre>\n";
echo "=== THỰC HIỆN CÁC BƯỚC YÊU CẦU CỦA ĐỀ BÀI ===\n\n";

echo "Bước 1. Tạo một object ShoppingCart.\n";
$cart = new ShoppingCart();
echo "-> Đã tạo giỏ hàng thành công.\n";

echo "\nBước 2. Tạo ít nhất 04 object CartItem.\n";
$item1 = new CartItem("Áo thun", 150000, 2);
$item2 = new CartItem("Quần Jean", 300000, 1);
$item3 = new CartItem("Giày Sneaker", 500000, 1);
$item4 = new CartItem("Mũ lưỡi trai", 50000, 3);
echo "-> Đã tạo 4 sản phẩm hợp lệ.\n";

echo "\nBước 3. Thêm các sản phẩm vào giỏ hàng bằng method addItem().\n";
$cart->addItem($item1);
$cart->addItem($item2);
$cart->addItem($item3);
$cart->addItem($item4);

echo "\nBước 4. Hiển thị toàn bộ giỏ hàng.\n";
$cart->displayCart();

echo "\nBước 5. Tính và hiển thị tổng tiền của giỏ hàng (Gọi rời calculateTotal).\n";
$total = $cart->calculateTotal();
echo "-> TỔNG TIỀN HIỆN TẠI: " . number_format($total) . " VNĐ\n";

echo "\nBước 6. Xóa một sản phẩm theo tên bằng method removeItem().\n";
echo "-> Tiến hành xóa 'Quần Jean':\n";
$cart->removeItem("Quần Jean");

echo "\nBước 7. Hiển thị lại giỏ hàng sau khi xóa.\n";
$cart->displayCart();


echo "\n\n=== TEST CÁC TRƯỜNG HỢP BẮT BUỘC XỬ LÝ LỖI ===\n\n";
echo "- Thử thêm sản phẩm có price <= 0:\n";
$itemInvalid1 = new CartItem("Áo thun rách", -50000, 2); 
$cart->addItem($itemInvalid1);

echo "\n- Thử thêm sản phẩm có quantity <= 0:\n";
$itemInvalid2 = new CartItem("Giày cũ", 100000, 0);      
$cart->addItem($itemInvalid2);

echo "\n- Thử xóa sản phẩm không tồn tại (Kính râm):\n";
$cart->removeItem("Kính râm");

echo "\n- Test calculateTotal() khi giỏ hàng rỗng (Tạo giỏ mới):\n";
$emptyCart = new ShoppingCart();
echo "-> Tổng tiền giỏ hàng rỗng: " . number_format($emptyCart->calculateTotal()) . "\n";

echo "</pre>\n";
?>
