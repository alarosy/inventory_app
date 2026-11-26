<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Product.php';
require_once __DIR__ . '/classes/Category.php';
requireLogin();

$productModel = new Product();
$categoryModel = new Category();
$categories = $categoryModel->getAll();

$sortLabels = [
    'newest'     => 'الأحدث',
    'name'       => 'الاسم',
    'price_asc'  => 'السعر: من الأقل',
    'price_desc' => 'السعر: من الأعلى',
    'quantity'   => 'الكمية: من الأقل',
];

// قيم الفلاتر كما كتبها المستخدم، لنعيد عرضها في الحقول
$search   = input($_GET, 'q');
$category = input($_GET, 'category');
$minPrice = input($_GET, 'min_price');
$maxPrice = input($_GET, 'max_price');
$sort     = input($_GET, 'sort');
if (!isset($sortLabels[$sort])) {
    $sort = 'newest';
}

$filters = ['name' => $search, 'sort' => $sort];
$filterErrors = [];

if ($category !== '') {
    $categoryId = filter_var($category, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($categoryId !== false) {
        $filters['category_id'] = $categoryId;
    }
}

foreach (['min_price' => $minPrice, 'max_price' => $maxPrice] as $key => $value) {
    if ($value === '') {
        continue;
    }
    if (is_numeric($value) && $value >= 0) {
        $filters[$key] = $value;
    } else {
        $filterErrors[] = 'قيمة السعر يجب أن تكون رقمًا موجبًا.';
    }
}

if (isset($filters['min_price'], $filters['max_price']) && $filters['min_price'] > $filters['max_price']) {
    $filterErrors[] = 'السعر "من" يجب أن يكون أقل من أو يساوي السعر "إلى".';
}

$products = empty($filterErrors) ? $productModel->getAll($filters) : [];
$isFiltered = ($search !== '' || $category !== '' || $minPrice !== '' || $maxPrice !== '');

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

<form method="get" action="products.php" class="box">
    <div class="filters">
        <div class="form-row">
            <label for="q">بحث بالاسم</label>
            <input type="search" id="q" name="q" value="<?= e($search) ?>" placeholder="اسم المنتج...">
        </div>
        <div class="form-row">
            <label for="category">التصنيف</label>
            <select id="category" name="category">
                <option value="">الكل</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int) $cat['id'] ?>" <?= (string) $cat['id'] === $category ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <label for="min_price">السعر من</label>
            <input type="number" id="min_price" name="min_price" min="0" step="0.01" value="<?= e($minPrice) ?>">
        </div>
        <div class="form-row">
            <label for="max_price">السعر إلى</label>
            <input type="number" id="max_price" name="max_price" min="0" step="0.01" value="<?= e($maxPrice) ?>">
        </div>
        <div class="form-row">
            <label for="sort">الترتيب</label>
            <select id="sort" name="sort">
                <?php foreach ($sortLabels as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $key === $sort ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn">بحث</button>
        <a href="products.php" class="btn btn-light">إعادة تعيين</a>
    </div>
</form>

<?php if (!empty($filterErrors)): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach (array_unique($filterErrors) as $error): ?>
                <li><?= e($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php elseif (empty($products) && $isFiltered): ?>
    <div class="box empty-state">لا توجد منتجات تطابق شروط البحث. جرّب تغيير الفلاتر أو اضغط "إعادة تعيين".</div>
<?php elseif (empty($products)): ?>
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
