<?php
$pageTitle = $pageTitle ?? 'نظام المخزون';
$activePage = $activePage ?? '';
$user = currentUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - نظام المخزون</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="index.php">نظام المخزون</a>

        <?php if ($user): ?>
            <nav class="main-nav">
                <a href="index.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">لوحة التحكم</a>
                <a href="products.php" class="<?= $activePage === 'products' ? 'active' : '' ?>">المنتجات</a>
                <?php if (isAdmin()): ?>
                    <a href="categories.php" class="<?= $activePage === 'categories' ? 'active' : '' ?>">التصنيفات</a>
                    <a href="users.php" class="<?= $activePage === 'users' ? 'active' : '' ?>">المستخدمون</a>
                <?php endif; ?>
            </nav>
            <div class="user-box">
                <span><?= e($user['full_name']) ?> (<?= $user['role'] === 'admin' ? 'مدير' : 'موظف' ?>)</span>
                <form method="post" action="logout.php">
                    <?= csrfField() ?>
                    <button type="submit" class="link-button">تسجيل الخروج</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</header>

<main class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>
