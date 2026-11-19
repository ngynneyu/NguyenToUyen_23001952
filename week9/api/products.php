<?php

declare(strict_types=1);

require __DIR__ . '/common.php';

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

if ($method === 'GET') {
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]) ?? 1;
    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 5, 'max_range' => 5],
    ]) ?? 5;
    if ($page === false) {
        fail(400, 'Trang phải là số nguyên dương.');
    }
    if ($limit === false) {
        fail(400, 'Mỗi trang phải có giới hạn 5 sản phẩm.');
    }
}

try {
    $pdo = database();

    switch ($method) {
        case 'GET':
            $pdo->beginTransaction();
            $total = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
            $totalPages = max(1, (int) ceil($total / $limit));
            $page = min($page, $totalPages);
            $statement = $pdo->prepare(
                'SELECT id, name, price, stock, image FROM products ORDER BY id DESC LIMIT :limit OFFSET :offset'
            );
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->bindValue(':offset', ($page - 1) * $limit, PDO::PARAM_INT);
            $statement->execute();
            $products = $statement->fetchAll(PDO::FETCH_ASSOC);
            $pdo->commit();
            respond(200, [
                'success' => true,
                'data' => $products,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_pages' => $totalPages,
                ],
            ]);

        case 'POST':
            $statement = $pdo->prepare(
                'INSERT INTO products (name, price, stock) VALUES (:name, :price, :stock)'
            );
            $statement->execute($product);
            respond(201, ['success' => true, 'id' => (int) $pdo->lastInsertId()]);

        case 'PUT':
            $statement = $pdo->prepare(
                'UPDATE products SET name = :name, price = :price, stock = :stock WHERE id = :id'
            );
            $statement->execute($product + ['id' => $id]);
            break;

        case 'DELETE':
            $pdo->beginTransaction();
            $lookup = $pdo->prepare('SELECT image FROM products WHERE id = :id FOR UPDATE');
            $lookup->execute(['id' => $id]);
            $product = $lookup->fetch(PDO::FETCH_ASSOC);
            if ($product === false) {
                $pdo->rollBack();
                fail(404, 'Không tìm thấy sản phẩm.');
            }
            $statement = $pdo->prepare('DELETE FROM products WHERE id = :id');
            $statement->execute(['id' => $id]);
            $pdo->commit();
            removeImage($product['image']);
            respond(200, ['success' => true]);
    }

    if ($statement->rowCount() === 0) {
        fail(404, 'Không tìm thấy sản phẩm.');
    }
    respond(200, ['success' => true]);
} catch (PDOException $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($exception->getMessage());
    fail(500, 'Không thể truy cập cơ sở dữ liệu. Hãy kiểm tra MySQL và cấu hình kết nối.');
}
