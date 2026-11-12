<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

function fail(int $status, string $message): never
{
    respond($status, ['success' => false, 'message' => $message]);
}

function readProduct(): array
{
    $input = json_decode(file_get_contents('php://input'));

    if (!is_object($input)) {
        fail(400, 'Dữ liệu gửi lên phải là một đối tượng JSON hợp lệ.');
    }

    $name = $input->name ?? null;
    $price = $input->price ?? null;
    $stock = $input->stock ?? null;

    if (!is_string($name)) {
        fail(422, 'Tên sản phẩm phải có từ 1 đến 100 ký tự.');
    }

    $name = preg_replace('/(*UCP)\A\s+|\s+\z/u', '', $name);
    if ($name === '' || mb_strlen($name, 'UTF-8') > 100) {
        fail(422, 'Tên sản phẩm phải có từ 1 đến 100 ký tự.');
    }

    if (!is_int($price) || $price < 1 || $price > 2147483647) {
        fail(422, 'Đơn giá phải là số nguyên từ 1 đến 2.147.483.647 đồng.');
    }

    if (!is_int($stock) || $stock < 0 || $stock > 2147483647) {
        fail(422, 'Số lượng tồn phải là số nguyên từ 0 đến 2.147.483.647.');
    }

    return ['name' => $name, 'price' => $price, 'stock' => $stock];
}

$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
    header('Allow: GET, POST, PUT, DELETE');
    fail(405, 'Phương thức không được hỗ trợ.');
}

if ($method === 'PUT' || $method === 'DELETE') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 2147483647],
    ]);
    if ($id === null || $id === false) {
        fail(400, 'Mã sản phẩm phải là số nguyên dương hợp lệ.');
    }
}

if ($method === 'POST' || $method === 'PUT') {
    $product = readProduct();
}

try {
    $host = getenv('WEEK8_DB_HOST') ?: '127.0.0.1';
    $port = getenv('WEEK8_DB_PORT') ?: '3306';
    $database = getenv('WEEK8_DB_NAME') ?: 'week8';
    $user = getenv('WEEK8_DB_USER') ?: 'root';
    $password = getenv('WEEK8_DB_PASSWORD');

    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $user,
        $password === false ? '' : $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_FOUND_ROWS => true,
        ]
    );

    switch ($method) {
        case 'GET':
            $statement = $pdo->query('SELECT id, name, price, stock FROM products ORDER BY id');
            respond(200, ['success' => true, 'data' => $statement->fetchAll(PDO::FETCH_ASSOC)]);

        case 'POST':
            $statement = $pdo->prepare(
                'INSERT INTO products (name, price, stock) VALUES (:name, :price, :stock)'
            );
            $statement->execute($product);
            respond(201, ['success' => true]);

        case 'PUT':
            $statement = $pdo->prepare(
                'UPDATE products SET name = :name, price = :price, stock = :stock WHERE id = :id'
            );
            $statement->execute($product + ['id' => $id]);
            break;

        case 'DELETE':
            $statement = $pdo->prepare('DELETE FROM products WHERE id = :id');
            $statement->execute(['id' => $id]);
            break;
    }

    if ($statement->rowCount() === 0) {
        fail(404, 'Không tìm thấy sản phẩm.');
    }
    respond(200, ['success' => true]);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    fail(500, 'Không thể truy cập cơ sở dữ liệu. Hãy kiểm tra MySQL và cấu hình kết nối.');
}
