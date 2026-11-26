<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/User.php';
requireAdmin();

$userModel = new User();
$roles = ['admin' => 'مدير', 'employee' => 'موظف'];
$errors = [];
$data = ['full_name' => '', 'username' => '', 'role' => 'employee'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    $data['full_name'] = input($_POST, 'full_name');
    $data['username']  = input($_POST, 'username');
    $data['role']      = input($_POST, 'role');
    // كلمة المرور لا نعمل لها trim حتى لا نغيّر ما كتبه المستخدم
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    $confirm  = isset($_POST['password_confirm']) && is_string($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    if ($data['full_name'] === '') {
        $errors[] = 'الاسم الكامل مطلوب.';
    } elseif (mb_strlen($data['full_name']) > 100) {
        $errors[] = 'الاسم الكامل يجب ألا يزيد عن 100 حرف.';
    }

    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $data['username'])) {
        $errors[] = 'اسم المستخدم من 3 إلى 30 حرفًا، ويحتوي حروفًا إنجليزية وأرقامًا و _ فقط.';
    } elseif ($userModel->findByUsername($data['username'])) {
        $errors[] = 'اسم المستخدم مستخدم مسبقًا.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.';
    } elseif ($password !== $confirm) {
        $errors[] = 'كلمتا المرور غير متطابقتين.';
    }

    if (!isset($roles[$data['role']])) {
        $errors[] = 'اختر صلاحية صحيحة.';
    }

    if (empty($errors)) {
        $userModel->create($data['username'], $password, $data['full_name'], $data['role']);
        setFlash('success', 'تمت إضافة المستخدم "' . $data['username'] . '".');
        redirect('users.php');
    }
}

$users = $userModel->getAll();

$pageTitle = 'المستخدمون';
$activePage = 'users';
require __DIR__ . '/includes/header.php';
?>
<div class="page-title">
    <h1>إدارة المستخدمين</h1>
</div>

<div class="two-columns">
    <section class="box">
        <h2>المستخدمون الحاليون</h2>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>الاسم</th>
                    <th>اسم المستخدم</th>
                    <th>الصلاحية</th>
                    <th>تاريخ الإضافة</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= e($u['full_name']) ?></td>
                        <td><?= e($u['username']) ?></td>
                        <td>
                            <span class="badge <?= $u['role'] === 'admin' ? 'badge-admin' : '' ?>">
                                <?= e($roles[$u['role']] ?? $u['role']) ?>
                            </span>
                        </td>
                        <td><?= e(date('Y/m/d', strtotime($u['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="box">
        <h2>إضافة مستخدم</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" action="users.php">
            <?= csrfField() ?>
            <div class="form-row">
                <label for="full_name">الاسم الكامل</label>
                <input type="text" id="full_name" name="full_name" maxlength="100" value="<?= e($data['full_name']) ?>" required>
            </div>
            <div class="form-row">
                <label for="username">اسم المستخدم (بالإنجليزية)</label>
                <input type="text" id="username" name="username" maxlength="30" value="<?= e($data['username']) ?>" required>
            </div>
            <div class="form-row">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" minlength="6" required>
            </div>
            <div class="form-row">
                <label for="password_confirm">تأكيد كلمة المرور</label>
                <input type="password" id="password_confirm" name="password_confirm" minlength="6" required>
            </div>
            <div class="form-row">
                <label for="role">الصلاحية</label>
                <select id="role" name="role">
                    <?php foreach ($roles as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $value === $data['role'] ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn">إضافة المستخدم</button>
        </form>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
