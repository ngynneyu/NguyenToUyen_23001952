<?php
require_once __DIR__ . '/common/auth.php';
if (isLoggedIn()) { redirect('product_list.php'); }
require_once __DIR__ . '/model/user.php';
$error = '';
$username = '';
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = postString('username');
    $password = postString('password', false);
    if (!validCsrfToken()) {
        http_response_code(403);
        $error = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
    } elseif ($username === '' || trim($password) === '') {
        $error = 'Tên đăng nhập và mật khẩu không được để trống.';
    } elseif (mb_strlen($username, 'UTF-8') > 50 || strlen($password) > 72 || strpos($password, "\0") !== false) {
        $error = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
    } else {
        try {
            $user = getUserByUsername($username);
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                unset($_SESSION['csrf_token']);
                redirect('product_list.php');
            }
            $error = 'Tên đăng nhập hoặc mật khẩu không chính xác.';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Không thể đăng nhập. Vui lòng thử lại.';
        }
    }
}
include __DIR__ . '/view/header.php';
?>
<h2>Đăng nhập</h2>
<?php if ($success): ?><p role="status"><?= e($success) ?></p><?php endif; ?>
<?php if ($error): ?><p role="alert"><?= e($error) ?></p><?php endif; ?>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <label for="username">Tên đăng nhập</label><br>
    <input id="username" name="username" value="<?= e($username) ?>" maxlength="50" autocomplete="username" required><br>
    <label for="password">Mật khẩu</label><br>
    <input type="password" id="password" name="password" autocomplete="current-password" required><br>
    <button type="submit">Đăng nhập</button>
    Chưa có tài khoản? <a href="register.php">Đăng ký</a>
</form>
<?php include __DIR__ . '/view/footer.php'; ?>
