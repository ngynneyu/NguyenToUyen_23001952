<?php

declare(strict_types=1);

require __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    fail(405, 'Phương thức không được hỗ trợ.');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 2147483647],
]);
$file = $_FILES['image'] ?? [];
$error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
$maxSize = 2 * 1024 * 1024;

if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE ||
    ($error === UPLOAD_ERR_OK && $file['size'] > $maxSize)) {
    fail(413, 'Ảnh không được lớn hơn 2 MB.');
}
if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
    fail(422, 'Không nhận được ảnh hợp lệ. Hãy chọn lại ảnh và thử lại.');
}
if ($id === null || $id === false) {
    fail(400, 'Mã sản phẩm phải là số nguyên dương hợp lệ.');
}

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$imageType = match ($extension) {
    'jpg', 'jpeg' => IMAGETYPE_JPEG,
    'png' => IMAGETYPE_PNG,
    default => null,
};
$imageInfo = @getimagesize($file['tmp_name']);
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
if ($imageType === null || ($imageInfo[2] ?? null) !== $imageType ||
    $mime !== image_type_to_mime_type($imageType)) {
    fail(422, 'Chỉ chấp nhận ảnh JPG, JPEG hoặc PNG đúng định dạng.');
}

$image = null;
try {
    $pdo = database();
    $pdo->beginTransaction();
    $lookup = $pdo->prepare('SELECT image FROM products WHERE id = :id FOR UPDATE');
    $lookup->execute(['id' => $id]);
    $product = $lookup->fetch(PDO::FETCH_ASSOC);
    if ($product === false) {
        $pdo->rollBack();
        fail(404, 'Không tìm thấy sản phẩm.');
    }

    $path = 'uploads/' . bin2hex(random_bytes(16)) . ($imageType === IMAGETYPE_PNG ? '.png' : '.jpg');
    if (!@move_uploaded_file($file['tmp_name'], dirname(__DIR__) . '/' . $path)) {
        $pdo->rollBack();
        fail(500, 'Không thể lưu ảnh. Hãy thử lại.');
    }
    $image = $path;

    $statement = $pdo->prepare('UPDATE products SET image = :image WHERE id = :id');
    $statement->execute(['image' => $image, 'id' => $id]);
    $pdo->commit();
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    removeImage($image);
    error_log($exception->getMessage());
    fail(500, 'Không thể cập nhật ảnh sản phẩm. Hãy thử lại.');
}

removeImage($product['image']);
respond(200, ['success' => true, 'message' => 'Đã tải ảnh lên.', 'image' => $image]);
