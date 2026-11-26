<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Product.php';

// الترتيب: الصلاحية أولًا، ثم نوع الطلب، ثم رمز CSRF، ثم وجود المنتج
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    die('هذه الصفحة تقبل طلبات POST فقط.');
}
checkCsrf();

$productModel = new Product();
$id = filter_var(input($_POST, 'id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$product = $id ? $productModel->getById($id) : false;

if (!$product) {
    setFlash('error', 'المنتج غير موجود أو حُذف مسبقًا.');
    redirect('products.php');
}

if ($productModel->delete($id)) {
    deleteImage($product['image']);
    setFlash('success', 'تم حذف المنتج "' . $product['name'] . '".');
} else {
    setFlash('error', 'تعذّر حذف المنتج.');
}
redirect('products.php');
