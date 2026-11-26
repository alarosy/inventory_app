<?php
// بيانات الاتصال بقاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventory_db');
define('DB_USER', 'student');
define('DB_PASS', 'Student@2026');

// إعدادات رفع الصور
define('UPLOAD_DIR', __DIR__ . '/../uploads/products/');   // مسار الحفظ على القرص
define('UPLOAD_PATH', 'uploads/products/');                // المسار النسبي الذي يُخزن في القاعدة
define('MAX_IMAGE_SIZE', 2 * 1024 * 1024);                 // 2MB
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
]);

define('CURRENCY', 'د.ل');
define('LOW_STOCK_LIMIT', 5);

// كوكي الجلسة لا تُقرأ من JavaScript ولا تُرسل مع طلبات المواقع الأخرى
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
]);
ini_set('session.use_strict_mode', '1');
session_start();

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
