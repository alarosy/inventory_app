# نظام مصغر لإدارة المخزون والمنتجات

نظام ويب لإدارة منتجات مخزن صغير: تسجيل دخول بصلاحيتين (مدير وموظف)، وإدارة المنتجات مع صورها، والتصنيفات، والمستخدمين، والبحث والفلترة، ولوحة تحكم بالإحصائيات. مشروع لمقرر برمجة الويب، مبني بـ PHP الخام (PDO وكلاسات بسيطة) وMySQL، دون أي إطار عمل أو مكتبات خارجية، ويعمل دون اتصال بالإنترنت.

إعداد: [اسم الطالب] و [اسم الطالب الثاني]

## ماذا يفعل البرنامج؟

- **تسجيل الدخول والخروج** بالجلسات (Sessions)، مع كلمات مرور مخزنة مشفرة بـ `password_hash`.
- **لوحة التحكم:**
  - عدد المنتجات، وإجمالي الكميات، وإجمالي قيمة المخزون.
  - عدد المنتجات وقيمتها لكل تصنيف.
  - قائمة بالمنتجات التي كميتها 5 أو أقل، بلون مميز.
- **المنتجات:**
  - جدول بالصورة والاسم والتصنيف والكمية والسعر والقيمة.
  - بحث بالاسم، وفلترة بالتصنيف ونطاق السعر، وترتيب (الأحدث، الاسم، السعر تصاعديًا وتنازليًا، الكمية). كل الفلاتر قابلة للدمج.
  - عدد النتائج وإجمالي قيمتها أسفل الجدول.
- **إضافة منتج** مع رفع صورة اختيارية (JPG أو PNG أو WEBP حتى 2MB) ومعاينتها قبل الرفع.
- **تعديل منتج** مع إمكانية استبدال الصورة، وتُحذف القديمة من القرص تلقائيًا.
- **حذف منتج** مع تأكيد، ويُحذف ملف صورته أيضًا.
- **التصنيفات:** إضافة وحذف، ويُمنع حذف تصنيف مرتبط بمنتجات مع رسالة توضح عددها.
- **المستخدمون:** عرض قائمة المستخدمين وإضافة مستخدم جديد بصلاحية مدير أو موظف.

## الصلاحيات

| العملية | مدير | موظف |
|---|---|---|
| لوحة التحكم، وعرض المنتجات، والبحث والفلترة | نعم | نعم |
| إضافة منتج أو تعديله أو حذفه | نعم | لا |
| إدارة التصنيفات | نعم | لا |
| إدارة المستخدمين | نعم | لا |

الصلاحيات يتحقق منها الخادم في كل صفحة محمية، وليس بإخفاء الأزرار فقط. إذا حاول الموظف فتح صفحة غير مسموحة يحصل على رسالة "غير مسموح" (رمز 403).

## بيانات الدخول التجريبية

| المستخدم | كلمة المرور | الصلاحية |
|---|---|---|
| `admin` | `admin123` | مدير |
| `employee` | `emp123` | موظف |

## الأمان المطبّق

- Prepared Statements حقيقية في كل الاستعلامات، والترتيب عبر قائمة قيم مسموحة.
- `htmlspecialchars` على كل مخرج.
- رمز CSRF في كل نموذج `POST`.
- `session_regenerate_id` بعد الدخول، وكوكي الجلسة `HttpOnly` و`SameSite=Lax`.
- فحص الصور بمحتواها الحقيقي (`finfo`) وليس بالامتداد، واسم عشوائي لكل صورة.
- ملف `.htaccess` في مجلد الرفع يمنع تنفيذ أي سكربت.
- مفتاح أجنبي `ON DELETE RESTRICT` يمنع حذف تصنيف له منتجات.

## المتطلبات

- PHP 8.0 أو أحدث، مع الإضافات `pdo_mysql` و`mbstring` و`fileinfo` (مفعّلة افتراضيًا في XAMPP)
- MySQL 8 أو MariaDB 10.4 أو أحدث
- Apache مع السماح بملفات `.htaccess` (وهو الافتراضي في XAMPP)

## التشغيل على Windows باستخدام XAMPP

1. ثبّت [XAMPP](https://www.apachefriends.org)، وشغّل Apache وMySQL من لوحة التحكم.
2. حمّل المشروع: زر **Code** ثم **Download ZIP** أعلى هذه الصفحة. فك الضغط، وسمِّ المجلد `inventory_app`، وضعه داخل `C:\xampp\htdocs\`.
3. افتح `http://localhost/phpmyadmin`:
   - أنشئ قاعدة بيانات باسم `inventory_db` بترميز `utf8mb4_unicode_ci`.
   - اخترها، ثم من تبويب **Import** اختر الملف `sql/database.sql` واضغط **Go**.
4. افتح `config/config.php` وعدّل بيانات الاتصال. في XAMPP الافتراضي:

   ```php
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

5. افتح المتصفح على `http://localhost/inventory_app`، وسجّل الدخول بأحد الحسابين أعلاه.

## التشغيل على Linux (Ubuntu)

```bash
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql php-mbstring git
cd /var/www/html
sudo git clone <رابط هذا المستودع> inventory_app

sudo mysql -e "CREATE DATABASE inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'student'@'localhost' IDENTIFIED BY 'Student@2026';
GRANT ALL PRIVILEGES ON inventory_db.* TO 'student'@'localhost';"
mysql -u student -p inventory_db < inventory_app/sql/database.sql

# السماح لـ Apache بالكتابة في مجلد الصور
sudo chown -R www-data:www-data inventory_app/uploads/products
```

يحتاج Apache أيضًا إلى قراءة ملفات `.htaccess`. أضف إلى إعداداته:

```apache
<Directory /var/www/html>
    AllowOverride All
</Directory>
```

ثم أعد تشغيله: `sudo systemctl restart apache2`، وافتح `http://localhost/inventory_app`.

بيانات الاتصال الافتراضية في `config/config.php` هي `student` / `Student@2026`، وكلمات مرور الحسابات التجريبية معروفة. كل ذلك للتطوير المحلي فقط، فغيّره إن نشرت النظام على خادم حقيقي.

## هيكل المجلدات

```
inventory_app/
├── config/config.php       إعدادات الاتصال والرفع وبدء الجلسة
├── classes/
│   ├── Database.php        اتصال PDO واحد
│   ├── Product.php         استعلامات المنتجات والإحصائيات والتحقق
│   ├── Category.php        التصنيفات ومنع حذف المرتبط منها
│   └── User.php            المستخدمون
├── includes/
│   ├── header.php          الترويسة والقائمة ورسائل التنبيه
│   ├── footer.php
│   ├── auth.php            requireLogin() و requireAdmin()
│   └── functions.php       دوال مساعدة، ورفع الصور، وحماية CSRF
├── assets/                 CSS و JavaScript وصورة بديلة
├── uploads/products/       الصور المرفوعة (محمي بـ .htaccess)
├── sql/database.sql        الجداول والبيانات التجريبية
├── login.php  logout.php
├── index.php               لوحة التحكم
├── products.php            القائمة والبحث والفلترة
├── product_add.php  product_edit.php  product_delete.php
├── categories.php  users.php
└── دليل_المناقشة.md        شرح تفصيلي للكود، وتقسيم العمل، وأسئلة المناقشة
```

## قاعدة البيانات

ثلاثة جداول:
- `users`: المستخدمون وصلاحياتهم.
- `categories`: التصنيفات.
- `products`: المنتجات، مرتبطة بـ `categories` عبر `category_id`.

البيانات التجريبية: 4 تصنيفات، و12 منتجًا، وحسابان. لإعادتها في أي وقت، أعد استيراد `sql/database.sql`.

## مشاكل شائعة

- **"تعذّر الاتصال بقاعدة البيانات":** تأكد أن MySQL يعمل، وأن البيانات في `config/config.php` صحيحة.
- **"تعذّر حفظ الصورة":** مجلد `uploads/products` غير قابل للكتابة من Apache (انظر أمر `chown` أعلاه).
- **"انتهت صلاحية النموذج":** حدّث الصفحة ثم أعد المحاولة. يحدث هذا إذا انتهت الجلسة أو فُتح النموذج قبل تسجيل الخروج.
- **خطأ 500 بعد النسخ على Linux:** غالبًا `AllowOverride` غير مفعّل أو إضافة `mod_php` غير موجودة.
