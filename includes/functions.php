<?php
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

// نقرأ الحقل كنص فقط حتى لا يسبب إرسال مصفوفة أخطاء في الدوال النصية
function input($source, $key)
{
    if (isset($source[$key]) && is_string($source[$key])) {
        return trim($source[$key]);
    }
    return '';
}

function formatPrice($amount)
{
    return number_format((float) $amount, 2) . ' ' . CURRENCY;
}

/* ---------- رسائل Flash: تُعرض مرة واحدة بعد إعادة التوجيه ---------- */

function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash()
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/* ---------- حماية CSRF ---------- */

// رمز عشوائي لكل جلسة، يُرسل مع كل نموذج POST ونتحقق منه في الخادم
function csrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function checkCsrf()
{
    $sent = input($_POST, 'csrf_token');
    // hash_equals للمقارنة بزمن ثابت
    if ($sent === '' || !hash_equals(csrfToken(), $sent)) {
        http_response_code(400);
        die('انتهت صلاحية النموذج أو أنه غير صالح. ارجع للصفحة السابقة وحدّثها ثم حاول مجددًا.');
    }
}

// إذا تجاوز الطلب post_max_size فإن PHP يفرغ $_POST و$_FILES بالكامل
function postTooLarge()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && !empty($_SERVER['CONTENT_LENGTH']);
}

/* ---------- رفع الصور ---------- */

// تُرجع مصفوفة: ok (نجح أم لا)، path (المسار النسبي أو null إن لم تُرفع صورة)، error
function uploadImage($file)
{
    $result = ['ok' => true, 'path' => null, 'error' => ''];

    if (!isset($file['error']) || is_array($file['error'])) {
        return $result;
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return $result; // الصورة اختيارية
    }

    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'حجم الصورة أكبر من الحد المسموح في إعدادات الخادم.',
        UPLOAD_ERR_FORM_SIZE  => 'حجم الصورة أكبر من الحد المسموح.',
        UPLOAD_ERR_PARTIAL    => 'لم يكتمل رفع الصورة، حاول مرة أخرى.',
        UPLOAD_ERR_NO_TMP_DIR => 'لا يوجد مجلد مؤقت على الخادم لحفظ الصورة.',
        UPLOAD_ERR_CANT_WRITE => 'تعذّرت كتابة الصورة على القرص.',
        UPLOAD_ERR_EXTENSION  => 'أوقفت إحدى إضافات PHP عملية الرفع.',
    ];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['ok'] = false;
        $result['error'] = $uploadErrors[$file['error']] ?? 'حدث خطأ غير معروف أثناء رفع الصورة.';
        return $result;
    }

    if ($file['size'] > MAX_IMAGE_SIZE) {
        $result['ok'] = false;
        $result['error'] = 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت.';
        return $result;
    }

    // نفحص محتوى الملف الحقيقي، لأن الامتداد والنوع المرسل من المتصفح يمكن تزويرهما
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset(ALLOWED_IMAGE_TYPES[$mime]) || getimagesize($file['tmp_name']) === false) {
        $result['ok'] = false;
        $result['error'] = 'نوع الملف غير مسموح. الأنواع المقبولة: JPG و PNG و WEBP.';
        return $result;
    }

    // اسم عشوائي جديد يمنع تكرار الأسماء ويمنع استخدام اسم الملف الأصلي
    $fileName = bin2hex(random_bytes(16)) . '.' . ALLOWED_IMAGE_TYPES[$mime];

    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $fileName)) {
        $result['ok'] = false;
        $result['error'] = 'تعذّر حفظ الصورة. تأكد من صلاحيات الكتابة على مجلد uploads/products.';
        return $result;
    }

    $result['path'] = UPLOAD_PATH . $fileName;
    return $result;
}

function deleteImage($path)
{
    // نحذف فقط ملفات داخل مجلد الرفع، ولا نقبل مسارات فيها ../
    if (empty($path) || strpos($path, UPLOAD_PATH) !== 0 || strpos($path, '..') !== false) {
        return;
    }
    $fullPath = UPLOAD_DIR . basename($path);
    if (is_file($fullPath)) {
        unlink($fullPath);
    }
}

function imageUrl($path)
{
    return !empty($path) ? $path : 'assets/img/no-image.svg';
}
