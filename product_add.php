<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Category.php';
requireAdmin();

$productModel = new Product();
$categoryModel = new Category();
$categories = $categoryModel->getAll();

$errors = [];
$data = ['name' => '', 'category_id' => '', 'quantity' => '', 'price' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (postTooLarge()) {
        $errors[] = 'حجم البيانات المرسلة أكبر من المسموح. اختر صورة أصغر من 2 ميجابايت.';
    } else {
        checkCsrf();

        foreach (array_keys($data) as $field) {
            $data[$field] = input($_POST, $field);
        }

        $errors = $productModel->validate($data, $categoryModel);

        // نرفع الصورة فقط بعد نجاح التحقق من باقي الحقول حتى لا تبقى صور يتيمة
        if (empty($errors)) {
            $upload = uploadImage($_FILES['image'] ?? []);
            if (!$upload['ok']) {
                $errors[] = $upload['error'];
            }
        }

        if (empty($errors)) {
            $data['image'] = $upload['path'];
            try {
                $productModel->create($data);
            } catch (PDOException $ex) {
                deleteImage($upload['path']);
                throw $ex;
            }
            setFlash('success', 'تمت إضافة المنتج "' . $data['name'] . '" بنجاح.');
            redirect('products.php');
        }
    }
}

$pageTitle = 'إضافة منتج';
$activePage = 'products';
require __DIR__ . '/includes/header.php';
?>
<div class="page-title">
    <h1>إضافة منتج جديد</h1>
    <a href="products.php" class="btn btn-light">رجوع للقائمة</a>
</div>

<?php if (empty($categories)): ?>
    <div class="box empty-state">
        <p>لا توجد تصنيفات بعد. يجب إضافة تصنيف واحد على الأقل قبل إضافة المنتجات.</p>
        <a href="categories.php" class="btn">إدارة التصنيفات</a>
    </div>
<?php else: ?>
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

        <form method="post" action="product_add.php" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="form-grid">
                <div class="form-row">
                    <label for="name">اسم المنتج</label>
                    <input type="text" id="name" name="name" maxlength="100" value="<?= e($data['name']) ?>" required>
                </div>
                <div class="form-row">
                    <label for="category_id">التصنيف</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- اختر التصنيف --</option>
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
                <label for="image">صورة المنتج (اختيارية: JPG أو PNG أو WEBP، حتى 2 ميجابايت)</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" data-preview="preview">
                <?php if (!empty($errors)): ?>
                    <span class="muted">إذا كنت اخترت صورة، أعد اختيارها مرة أخرى.</span>
                <?php endif; ?>
                <img id="preview" class="image-preview" src="assets/img/no-image.svg" alt="معاينة الصورة">
            </div>
            <button type="submit" class="btn">حفظ المنتج</button>
        </form>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
