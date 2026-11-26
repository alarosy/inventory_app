<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Product.php';
requireLogin();

$productModel = new Product();
$stats = $productModel->getStats();

$pageTitle = 'لوحة التحكم';
$activePage = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="page-title">
    <h1>لوحة التحكم</h1>
    <?php if (isAdmin()): ?>
        <a href="product_add.php" class="btn">إضافة منتج</a>
    <?php endif; ?>
</div>

<div class="stats">
    <div class="stat">
        <div class="label">عدد المنتجات</div>
        <div class="value"><?= (int) $stats['products_count'] ?></div>
    </div>
    <div class="stat">
        <div class="label">إجمالي الكميات</div>
        <div class="value"><?= number_format((int) $stats['total_quantity']) ?></div>
    </div>
    <div class="stat">
        <div class="label">إجمالي قيمة المخزون</div>
        <div class="value"><?= e(formatPrice($stats['total_value'])) ?></div>
    </div>
</div>

<div class="two-columns">
    <section class="box">
        <h2>المنتجات حسب التصنيف</h2>
        <?php if (empty($stats['by_category'])): ?>
            <p class="empty-state">لا توجد تصنيفات بعد.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>التصنيف</th>
                        <th>عدد المنتجات</th>
                        <th>قيمة المخزون</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($stats['by_category'] as $row): ?>
                        <tr>
                            <td><?= e($row['name']) ?></td>
                            <td><?= (int) $row['products_count'] ?></td>
                            <td><?= e(formatPrice($row['total_value'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="box">
        <h2>منتجات قاربت على النفاد (<?= LOW_STOCK_LIMIT ?> قطع أو أقل)</h2>
        <?php if (empty($stats['low_stock'])): ?>
            <p class="empty-state">كل المنتجات بكميات كافية.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>التصنيف</th>
                        <th>الكمية</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($stats['low_stock'] as $row): ?>
                        <tr class="low-stock">
                            <td>
                                <?php if (isAdmin()): ?>
                                    <a href="product_edit.php?id=<?= (int) $row['id'] ?>"><?= e($row['name']) ?></a>
                                <?php else: ?>
                                    <?= e($row['name']) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= e($row['category_name']) ?></td>
                            <td class="qty-low"><?= (int) $row['quantity'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
