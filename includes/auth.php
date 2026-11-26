<?php
function currentUser()
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn()
{
    return isset($_SESSION['user']);
}

function isAdmin()
{
    return isLoggedIn() && $_SESSION['user']['role'] === 'admin';
}

// تُستدعى في أول كل صفحة محمية
function requireLogin()
{
    if (!isLoggedIn()) {
        setFlash('error', 'يجب تسجيل الدخول أولًا.');
        redirect('login.php');
    }
}

// إخفاء الأزرار وحده لا يكفي: أي شخص يستطيع كتابة الرابط أو إرسال الطلب مباشرة
function requireAdmin()
{
    requireLogin();

    if (!isAdmin()) {
        http_response_code(403);
        $pageTitle = 'غير مسموح';
        require __DIR__ . '/header.php';
        echo '<div class="box empty-state"><h2>غير مسموح</h2>'
            . '<p>هذه الصفحة متاحة لمدير النظام فقط.</p>'
            . '<a class="btn" href="index.php">العودة إلى لوحة التحكم</a></div>';
        require __DIR__ . '/footer.php';
        exit;
    }
}
