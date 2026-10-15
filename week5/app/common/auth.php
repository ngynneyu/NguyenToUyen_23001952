<?php
require_once __DIR__ . '/bootstrap.php';
function isLoggedIn() {
    return isset($_SESSION['user_id'], $_SESSION['username']);
}
function requireLogin() {
    if (!isLoggedIn()) { redirect('login.php'); }
}
