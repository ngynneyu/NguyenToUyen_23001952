<?php
require_once 'CartItem.php';

class ShoppingCart {
    private $items = [];

    public function addItem($item) {
        if (!($item instanceof CartItem)) {
            echo "Lỗi: Sản phẩm không hợp lệ (không phải là CartItem).\n";
            return;
        }
        if (!is_numeric($item->getPrice()) || $item->getPrice() <= 0) {
            echo "Lỗi: Sản phẩm '" . $item->getName() . "' có giá không hợp lệ (phải là số > 0).\n";
            return;
        }
        if (!is_numeric($item->getQuantity()) || $item->getQuantity() <= 0) {
            echo "Lỗi: Sản phẩm '" . $item->getName() . "' có số lượng không hợp lệ (phải là số nguyên > 0).\n";
            return;
        }
        $this->items[] = $item;
        echo "Đã thêm '" . $item->getName() . "' vào giỏ hàng.\n";
    }

    public function removeItem($name) {
        if (empty($name)) {
            echo "Lỗi: Tên sản phẩm không hợp lệ.\n";
            return;
        }
        $found = false;
        foreach ($this->items as $index => $item) {
            if ($item->getName() === $name) {
                unset($this->items[$index]);
                // Re-index array
                $this->items = array_values($this->items);
                echo "Đã xóa sản phẩm '" . $name . "' khỏi giỏ hàng.\n";
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "Lỗi: Không tìm thấy sản phẩm '" . $name . "' trong giỏ hàng để xóa.\n";
        }
    }

    public function calculateTotal() {
        $total = 0;
        if (empty($this->items)) {
            return $total;
        }
        foreach ($this->items as $item) {
            $total += $item->getTotal();
        }
        return $total;
    }

    // Hàm hỗ trợ căn lề (padding) cho chuỗi có dấu tiếng Việt
    private function padStr($str, $len, $pad_string = ' ', $pad_type = STR_PAD_RIGHT) {
        $diff = strlen($str) - mb_strlen($str, 'UTF-8');
        return str_pad($str, $len + $diff, $pad_string, $pad_type);
    }

    public function displayCart() {
        if (empty($this->items)) {
            echo "Giỏ hàng trống.\n";
            return;
        }

        echo "+----------------------+-------------+----------+-------------+\n";
        echo "| " . $this->padStr("Tên sản phẩm", 20) . " | " . $this->padStr("Đơn giá", 11, ' ', STR_PAD_LEFT) . " | " . $this->padStr("Số lượng", 8, ' ', STR_PAD_LEFT) . " | " . $this->padStr("Thành tiền", 11, ' ', STR_PAD_LEFT) . " |\n";
        echo "+----------------------+-------------+----------+-------------+\n";
        
        foreach ($this->items as $item) {
            $name = $this->padStr($item->getName(), 20);
            $price = $this->padStr(number_format($item->getPrice()), 11, ' ', STR_PAD_LEFT);
            $qty = $this->padStr($item->getQuantity(), 8, ' ', STR_PAD_LEFT);
            $total = $this->padStr(number_format($item->getTotal()), 11, ' ', STR_PAD_LEFT);
            
            echo "| $name | $price | $qty | $total |\n";
        }
        
        echo "+----------------------+-------------+----------+-------------+\n";
        echo "| " . $this->padStr("=> TỔNG TIỀN GỘP", 44, ' ', STR_PAD_RIGHT) . " | " . $this->padStr(number_format($this->calculateTotal()), 11, ' ', STR_PAD_LEFT) . " |\n";
        echo "+----------------------+-------------+----------+-------------+\n";
    }
}
