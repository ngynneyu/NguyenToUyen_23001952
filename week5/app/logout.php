<?php
require_once __DIR__ . '/common/auth.php';
requireLogin();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validCsrfToken()) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'],
            ]);
        }
        session_destroy();
        redirect('login.php');
    }
    http_response_code(403);
    $error = 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng thử lại.';
}
include __DIR__ . '/view/header.php';
?>
<h2>Đăng xuất</h2>
<?php if ($error): ?><p role="alert"><?= e($error) ?></p><?php endif; ?>
<p>Bạn muốn đăng xuất khỏi tài khoản <?= e($_SESSION['username']) ?>?</p>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
    <button type="submit">Đăng xuất</button>
    <a href="product_list.php">Hủy</a>
</form>
<?php include __DIR__ . '/view/footer.php'; ?>
