<?php
$prices = [15000, 25000, 10000, 45000, 30000];

function calculateTotal($prices) {
    $total = 0;
    foreach ($prices as $price) {
        $total += $price;
    }
    return $total;
}

function findMaxPrice($prices) {
    if (empty($prices)) {
        return null;
    }

    $maxPrice = $prices[0];
    foreach ($prices as $price) {
        if ($price > $maxPrice) {
            $maxPrice = $price;
        }
    }
    return $maxPrice;
}

$total = calculateTotal($prices);
$maxPrice = findMaxPrice($prices);

echo "Tổng giá sản phẩm: $total<br>";
if ($maxPrice !== null) {
    echo "Giá sản phẩm cao nhất: $maxPrice<br>";
} else {
    echo "Danh sách sản phẩm rỗng<br>";
}
?>