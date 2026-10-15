<?php
require_once __DIR__ . '/common/auth.php';
redirect(isLoggedIn() ? 'product_list.php' : 'login.php');
