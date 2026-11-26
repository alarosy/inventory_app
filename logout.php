<?php
require_once __DIR__ . '/config/config.php';

// الخروج عبر POST مع رمز CSRF حتى لا يُخرج موقع آخر المستخدم دون علمه
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
checkCsrf();

$_SESSION = [];

// حذف كوكي الجلسة من المتصفح أيضًا
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();
redirect('login.php?logout=1');
