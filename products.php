<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Product.php';
requireLogin();

$productModel = new Product();
$products = $productModel->getAll();

// إجمالي قيمة النتائج المعروضة فقط
$shownValue = 0;
foreach ($products as $product) {
    $shownValue += $product['quantity'] * $product['price'];
}

$pageTitle = 'المنتجات';
$activePage = 'products';
require __DIR__ . '/includes/header.php';
?>
<div class="page-title">
    <h1>المنتجات</h1>
    <?php if (isAdmin()): ?>
        <a href="product_add.php" class="btn">إضافة منتج</a>
    <?php endif; ?>
</div>

<?php if (empty($products)): ?>
    <div class="box empty-state">لا توجد منتجات بعد.</div>
<?php else: ?>
    <div class="box table-wrap">
        <table>
            <thead>
            <tr>
                <th>الصورة</th>
                <th>الاسم</th>
                <th>التصنيف</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>القيمة</th>
                <?php if (isAdmin()): ?>
                    <th></th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <?php $isLow = $product['quantity'] <= LOW_STOCK_LIMIT; ?>
                <tr class="<?= $isLow ? 'low-stock' : '' ?>">
                    <td><img class="thumb" src="<?= e(imageUrl($product['image'])) ?>" alt="<?= e($product['name']) ?>"></td>
                    <td><?= e($product['name']) ?></td>
                    <td><?= e($product['category_name']) ?></td>
                    <td class="<?= $isLow ? 'qty-low' : '' ?>"><?= (int) $product['quantity'] ?></td>
                    <td><?= e(formatPrice($product['price'])) ?></td>
                    <td><?= e(formatPrice($product['quantity'] * $product['price'])) ?></td>
                    <?php if (isAdmin()): ?>
                        <td class="actions">
                            <a href="product_edit.php?id=<?= (int) $product['id'] ?>" class="btn btn-light btn-small">تعديل</a>
                            <form method="post" action="product_delete.php" class="inline-form"
                                  data-confirm="هل أنت متأكد من حذف المنتج &quot;<?= e($product['name']) ?>&quot;؟">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-small">حذف</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="5">عدد النتائج: <?= count($products) ?></td>
                <td colspan="<?= isAdmin() ? 2 : 1 ?>">الإجمالي: <?= e(formatPrice($shownValue)) ?></td>
            </tr>
            </tfoot>
        </table>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
