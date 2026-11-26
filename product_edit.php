<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Category.php';
requireAdmin();

$productModel = new Product();
$categoryModel = new Category();

// المعرّف دائمًا من الرابط (حتى عند POST) لأن $_POST قد يصل فارغًا إذا كان الطلب كبيرًا
$id = filter_var(input($_GET, 'id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$product = $id ? $productModel->getById($id) : false;

if (!$product) {
    http_response_code(404);
    $pageTitle = 'منتج غير موجود';
    require __DIR__ . '/includes/header.php';
    echo '<div class="box empty-state"><p>المنتج المطلوب غير موجود، ربما حُذف.</p>'
        . '<a class="btn" href="products.php">العودة إلى المنتجات</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$categories = $categoryModel->getAll();
$errors = [];
$data = [
    'name'        => $product['name'],
    'category_id' => (string) $product['category_id'],
    'quantity'    => (string) $product['quantity'],
    'price'       => $product['price'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (postTooLarge()) {
        $errors[] = 'حجم البيانات المرسلة أكبر من المسموح. اختر صورة أصغر من 2 ميجابايت.';
    } else {
        checkCsrf();

        foreach (array_keys($data) as $field) {
            $data[$field] = input($_POST, $field);
        }

        $errors = $productModel->validate($data, $categoryModel);

        if (empty($errors)) {
            $upload = uploadImage($_FILES['image'] ?? []);
            if (!$upload['ok']) {
                $errors[] = $upload['error'];
            }
        }

        if (empty($errors)) {
            $oldImage = $product['image'];
            $newImage = $upload['path'];
            // إذا لم تُرفع صورة جديدة نبقي القديمة كما هي
            $data['image'] = $newImage ?? $oldImage;

            try {
                $productModel->update($id, $data);
            } catch (PDOException $ex) {
                deleteImage($newImage);
                throw $ex;
            }

            // نحذف الصورة القديمة من القرص فقط بعد نجاح الحفظ في القاعدة
            if ($newImage !== null && $oldImage) {
                deleteImage($oldImage);
            }

            setFlash('success', 'تم حفظ تعديلات المنتج "' . $data['name'] . '".');
            redirect('products.php');
        }
    }
}

$pageTitle = 'تعديل منتج';
$activePage = 'products';
require __DIR__ . '/includes/header.php';
?>
<div class="page-title">
    <h1>تعديل المنتج: <?= e($product['name']) ?></h1>
    <a href="products.php" class="btn btn-light">رجوع للقائمة</a>
</div>

<div class="box">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="product_edit.php?id=<?= (int) $product['id'] ?>" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div class="form-grid">
            <div class="form-row">
                <label for="name">اسم المنتج</label>
                <input type="text" id="name" name="name" maxlength="100" value="<?= e($data['name']) ?>" required>
            </div>
            <div class="form-row">
                <label for="category_id">التصنيف</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= (string) $cat['id'] === $data['category_id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <label for="quantity">الكمية</label>
                <input type="number" id="quantity" name="quantity" min="0" step="1" value="<?= e($data['quantity']) ?>" required>
            </div>
            <div class="form-row">
                <label for="price">السعر (<?= CURRENCY ?>)</label>
                <input type="number" id="price" name="price" min="0" step="0.01" value="<?= e($data['price']) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <label for="image">تغيير الصورة (اتركه فارغًا للإبقاء على الصورة الحالية)</label>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" data-preview="preview">
            <img id="preview" class="image-preview" src="<?= e(imageUrl($product['image'])) ?>" alt="صورة المنتج">
        </div>
        <button type="submit" class="btn">حفظ التعديلات</button>
        <a href="products.php" class="btn btn-light">إلغاء</a>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
