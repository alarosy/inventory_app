<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Category.php';
requireAdmin();

$categoryModel = new Category();
$errors = [];
$name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $action = input($_POST, 'action');

    if ($action === 'add') {
        $name = input($_POST, 'name');

        if ($name === '') {
            $errors[] = 'اسم التصنيف مطلوب.';
        } elseif (mb_strlen($name) > 50) {
            $errors[] = 'اسم التصنيف يجب ألا يزيد عن 50 حرفًا.';
        } elseif ($categoryModel->nameExists($name)) {
            $errors[] = 'يوجد تصنيف بهذا الاسم مسبقًا.';
        }

        if (empty($errors)) {
            $categoryModel->create($name);
            setFlash('success', 'تمت إضافة التصنيف "' . $name . '".');
            redirect('categories.php');
        }
    } elseif ($action === 'delete') {
        $id = filter_var(input($_POST, 'id'), FILTER_VALIDATE_INT);
        $category = $id ? $categoryModel->getById($id) : false;

        if (!$category) {
            setFlash('error', 'التصنيف غير موجود.');
        } else {
            try {
                $linked = $categoryModel->delete($id);
                if ($linked > 0) {
                    setFlash('error', 'لا يمكن حذف التصنيف "' . $category['name'] . '" لأنه مرتبط بـ ' . $linked . ' منتج. انقل المنتجات أو احذفها أولًا.');
                } else {
                    setFlash('success', 'تم حذف التصنيف "' . $category['name'] . '".');
                }
            } catch (PDOException $ex) {
                // حماية إضافية: المفتاح الأجنبي RESTRICT يرفض الحذف حتى لو أُضيف منتج في نفس اللحظة
                setFlash('error', 'لا يمكن حذف التصنيف لأنه مرتبط بمنتجات.');
            }
        }
        redirect('categories.php');
    }
}

$categories = $categoryModel->getAll();

$pageTitle = 'التصنيفات';
$activePage = 'categories';
require __DIR__ . '/includes/header.php';
?>
<div class="page-title">
    <h1>إدارة التصنيفات</h1>
</div>

<div class="two-columns">
    <section class="box">
        <h2>التصنيفات الحالية</h2>
        <?php if (empty($categories)): ?>
            <p class="empty-state">لا توجد تصنيفات بعد.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>عدد المنتجات</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= e($cat['name']) ?></td>
                            <td><?= (int) $cat['products_count'] ?></td>
                            <td class="actions">
                                <form method="post" action="categories.php" class="inline-form"
                                      data-confirm="هل تريد حذف التصنيف &quot;<?= e($cat['name']) ?>&quot;؟">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-small">حذف</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="box">
        <h2>إضافة تصنيف</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" action="categories.php">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add">
            <div class="form-row">
                <label for="name">اسم التصنيف</label>
                <input type="text" id="name" name="name" maxlength="50" value="<?= e($name) ?>" required>
            </div>
            <button type="submit" class="btn">إضافة</button>
        </form>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
