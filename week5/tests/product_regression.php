<?php
// Chạy: D:/xampp/php/php.exe week5/tests/product_regression.php
// Dùng model giả để kiểm tra trang thật mà không thay đổi CSDL.
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$app = dirname(__DIR__) . '/app';
$temp = sys_get_temp_dir() . '/week5-tests-' . bin2hex(random_bytes(8));
mkdir($temp);
foreach (['model', 'common', 'view'] as $dir) {
    mkdir($temp . '/' . $dir);
}
$files = ['product_add.php', 'product_edit.php', 'product_delete.php',
    'common/productValidation.php', 'common/auth.php', 'common/bootstrap.php', 'view/header.php', 'view/footer.php'];
foreach ($files as $file) {
    copy($app . '/' . $file, $temp . '/' . $file);
}
file_put_contents($temp . '/model/product.php', '<?php // Model replaced by test functions.');
session_save_path($temp);
require_once $temp . '/common/auth.php';
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'tester';

function getProductById($id) {
    $GLOBALS['lookups']++;
    return $id === 1 ? ['id' => 1, 'name' => 'Test', 'price' => '1.00', 'quantity' => '0'] : false;
}
function getProductByName($name) { return $GLOBALS['duplicate']; }
function addProduct($name, $price, $quantity) {
    if ($GLOBALS['failWrite']) { throw new PDOException('Simulated write failure'); }
    $GLOBALS['writes'][] = [$price, $quantity];
    return true;
}
function updateProduct($id, $name, $price, $quantity) { return addProduct($name, $price, $quantity); }
function deleteProduct($id) { $GLOBALS['writes'][] = $id; return true; }

function renderPage($page, $get = [], $post = [], $duplicate = false, $failWrite = false) {
    $_GET = $get;
    $_POST = $post ? array_merge($post, ['csrf_token' => csrfToken()]) : [];
    $_SERVER['REQUEST_METHOD'] = $post ? 'POST' : 'GET';
    $GLOBALS['writes'] = [];
    $GLOBALS['lookups'] = 0;
    $GLOBALS['duplicate'] = $duplicate;
    $GLOBALS['failWrite'] = $failWrite;
    http_response_code(200);
    ob_start();
    try { include $page; return ob_get_contents(); }
    finally { ob_end_clean(); }
}

$checks = 0;
function check($condition, $description) {
    if (!$condition) { throw new RuntimeException($description); }
    $GLOBALS['checks']++;
}

$originalDir = getcwd();
chdir($temp);
try {
    $payload = '<script>alert(1)</script>';
    $html = renderPage('product_add.php', [], ['name' => $payload, 'price' => '1', 'quantity' => '0'], true);
    check(strpos($html, $payload) === false && strpos($html, '&lt;script&gt;') !== false, 'Escape duplicate product name');

    foreach (['product_add.php', 'product_edit.php'] as $page) {
        foreach (['1.5', '-1', '1e2', '2147483648', 'abc', ''] as $quantity) {
            $html = renderPage($page, ['id' => '1'], ['name' => 'Test', 'price' => '1.00', 'quantity' => $quantity]);
            check(!$GLOBALS['writes'] && strpos($html, 'Số lượng phải') !== false, "$page rejects quantity $quantity");
        }
        foreach (['100000000', '99999999.999', '0', '-1', '1e100', 'abc', ''] as $price) {
            $html = renderPage($page, ['id' => '1'], ['name' => 'Test', 'price' => $price, 'quantity' => '0']);
            check(!$GLOBALS['writes'] && strpos($html, 'Giá phải') !== false, "$page rejects price $price");
        }
        foreach ([['0.01', '0'], ['99999999.99', '2147483647']] as $values) {
            renderPage($page, ['id' => '1'], ['name' => 'Test', 'price' => $values[0], 'quantity' => $values[1]]);
            check($GLOBALS['writes'] === [[$values[0], (int) $values[1]]], "$page accepts boundary values");
        }
        $html = renderPage($page, ['id' => '1'], ['name' => 'Test', 'price' => '1', 'quantity' => '0'], false, true);
        check(strpos($html, 'Vui lòng thử lại.') !== false && strpos($html, '</html>') !== false, "$page handles PDOException");
        $html = renderPage($page, ['id' => '1'], ['name' => ['bad'], 'price' => ['bad'], 'quantity' => ['bad']]);
        check(!$GLOBALS['writes'] && strpos($html, 'Tên sản phẩm không được rỗng.') !== false, "$page handles array inputs");
    }
    foreach (['product_edit.php', 'product_delete.php'] as $page) {
        foreach ([null, '', 'abc', '0', '-1', '1.5', '1e0', '2147483648', ['1']] as $id) {
            $html = renderPage($page, $id === null ? [] : ['id' => $id], ['confirm' => '1', 'name' => 'Test', 'price' => '1', 'quantity' => '0']);
            check(http_response_code() === 400 && !$GLOBALS['lookups'] && !$GLOBALS['writes'], "$page rejects invalid ID");
            check(strpos($html, '<html') !== false && strpos($html, '</html>') !== false && strpos($html, '<form') === false, "$page renders full error page");
        }
        $html = renderPage($page, ['id' => '2'], ['confirm' => '1']);
        check(http_response_code() === 404 && !$GLOBALS['writes'] && strpos($html, '</html>') !== false, "$page handles missing product");
    }
} finally {
    chdir($originalDir);
    session_destroy();
    foreach (array_merge($files, ['model/product.php']) as $file) { unlink($temp . '/' . $file); }
    foreach (['model', 'common', 'view'] as $dir) { rmdir($temp . '/' . $dir); }
    rmdir($temp);
}
echo "PASS: $checks regression checks\n";
