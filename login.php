<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/User.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    $username = input($_POST, 'username');
    $password = input($_POST, 'password');

    if ($username === '' || $password === '') {
        $error = 'أدخل اسم المستخدم وكلمة المرور.';
    } else {
        $userModel = new User();
        $found = $userModel->findByUsername($username);

        if ($found && password_verify($password, $found['password'])) {
            // معرّف جلسة جديد بعد الدخول يمنع هجوم تثبيت الجلسة (Session Fixation)
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'        => (int) $found['id'],
                'username'  => $found['username'],
                'full_name' => $found['full_name'],
                'role'      => $found['role'],
            ];
            setFlash('success', 'أهلًا ' . $found['full_name'] . '، تم تسجيل الدخول بنجاح.');
            redirect('index.php');
        }

        // رسالة واحدة للحالتين حتى لا نكشف هل اسم المستخدم موجود أم لا
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
    }
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام المخزون</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
<div class="login-box box">
    <h1>نظام المخزون</h1>
    <p class="muted">سجّل الدخول للمتابعة</p>

    <?php if (isset($_GET['logout'])): ?>
        <div class="alert alert-success">تم تسجيل الخروج.</div>
    <?php endif; ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <?= csrfField() ?>
        <div class="form-row">
            <label for="username">اسم المستخدم</label>
            <input type="text" id="username" name="username" value="<?= e($username) ?>" required autofocus>
        </div>
        <div class="form-row">
            <label for="password">كلمة المرور</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-block">دخول</button>
    </form>
</div>
</body>
</html>
