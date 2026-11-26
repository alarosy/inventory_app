<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$pageTitle = 'لوحة التحكم';
$activePage = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="page-title">
    <h1>لوحة التحكم</h1>
</div>
<div class="box">مرحبًا <?= e(currentUser()['full_name']) ?>.</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
