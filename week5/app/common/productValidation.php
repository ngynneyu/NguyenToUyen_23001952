<?php
// Giới hạn tương ứng với INT và DECIMAL(10,2) trong database.sql.
function validateProductInput($name, $price, $quantity) {
    if ($name === '') {
        return 'Tên sản phẩm không được rỗng.';
    }

    if (mb_strlen($name, 'UTF-8') > 100) {
        return 'Tên sản phẩm không được vượt quá 100 ký tự.';
    }

    // Kiểm tra dạng thập phân trước khi ghi để tránh làm tròn hoặc tràn cột.
    if (!preg_match('/\A[0-9]+(?:\.[0-9]{1,2})?\z/', $price)) {
        return 'Giá phải lớn hơn 0, không vượt quá 99999999.99 và có tối đa 2 chữ số thập phân.';
    }
    $integerPart = ltrim(explode('.', $price)[0], '0');
    if (strlen($integerPart) > 8 || (float) $price <= 0) {
        return 'Giá phải lớn hơn 0 và không vượt quá 99999999.99.';
    }

    $normalizedQuantity = ltrim($quantity, '0');
    if (!preg_match('/\A[0-9]+\z/', $quantity)
        || strlen($normalizedQuantity) > 10
        || (strlen($normalizedQuantity) === 10 && strcmp($normalizedQuantity, '2147483647') > 0)) {
        return 'Số lượng phải là số nguyên từ 0 đến 2147483647.';
    }

    return '';
}

function validateProductId($value) {
    if (!is_string($value)) {
        return false;
    }
    return filter_var($value, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 2147483647],
    ]);
}
