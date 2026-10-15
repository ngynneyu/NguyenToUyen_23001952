<?php
require_once __DIR__ . '/common/auth.php';
if (isLoggedIn()) { redirect('product_list.php'); }
require_once __DIR__ . '/model/user.php';
$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = postString('username');
    $password = postString('password', false);
    if (!validCsrfToken()) {
        http_response_code(403);
        $error = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
    } elseif ($username === '' || trim($password) === '') {
        $error = 'Tên đăng nhập và mật khẩu không được để trống.';
    } elseif (mb_strlen($username, 'UTF-8') > 50) {
        $error = 'Tên đăng nhập không được vượt quá 50 ký tự.';
    } elseif (strlen($password) > 72 || strpos($password, "\0") !== false) {
        $error = 'Mật khẩu không được vượt quá 72 byte hoặc chứa ký tự null.';
    } else {
        try {
            if (getUserByUsername($username)) {
                $error = 'Tên đăng nhập đã tồn tại.';
            } elseif (addUser($username, $password)) {
                $_SESSION['flash_success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
                redirect('login.php');
            } else {
                $error = 'Không thể đăng ký. Vui lòng thử lại.';
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = $e->getCode() === '23000'
                ? 'Tên đăng nhập đã tồn tại.' : 'Không thể đăng ký. Vui lòng thử lại.';
        }
    }
}
include __DIR__ . '/view/header.php';
?>
<h2>Đăng ký tài khoản</h2>
<?php if ($error): ?><p role="alert"><?= e($error) ?></p><?php endif; ?>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <label for="username">Tên đăng nhập</label><br>
    <input id="username" name="username" value="<?= e($username) ?>" maxlength="50" autocomplete="username" required><br>
    <label for="password">Mật khẩu</label><br>
    <input type="password" id="password" name="password" autocomplete="new-password" required><br>
    <button type="submit">Đăng ký</button>
    <a href="login.php">Đã có tài khoản? Đăng nhập</a>
</form>
<?php include __DIR__ . '/view/footer.php'; ?>
