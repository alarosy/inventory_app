# نظام مصغر لإدارة المخزون والمنتجات

مشروع مقرر برمجة الويب (مشروع لطالبين): إدارة منتجات وتصنيفات مع تسجيل دخول وصلاحيات ورفع صور. مبني بـ PHP (PDO وكلاسات بسيطة) وMySQL.

إعداد: [اسم الطالب] و [اسم الطالب الثاني]

## المتطلبات

- PHP 8.0 أو أحدث، مع الإضافات `pdo_mysql` و`mbstring` و`fileinfo`
- MySQL 8 أو MariaDB 10.4 أو أحدث
- Apache مع السماح بملفات `.htaccess` (`AllowOverride All`)، وهو الافتراضي في XAMPP

## التشغيل

1. انسخ مجلد `inventory_app` إلى مجلد الويب (`/var/www/html` على Ubuntu، أو `C:\xampp\htdocs` في XAMPP).
2. أنشئ قاعدة البيانات واستورد الجداول والبيانات التجريبية:

   ```
   mysql -u student -p -e "CREATE DATABASE IF NOT EXISTS inventory_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   mysql -u student -p inventory_db < sql/database.sql
   ```

   أو من phpMyAdmin: أنشئ القاعدة `inventory_db` ثم استورد `sql/database.sql`.
3. عدّل بيانات الاتصال في `config/config.php` إذا لزم (في XAMPP غالبًا `root` بدون كلمة مرور).
4. على Linux تأكد أن Apache يستطيع الكتابة في مجلد الصور:

   ```
   sudo chgrp www-data uploads/products && chmod 2775 uploads/products
   ```

5. افتح: `http://localhost/inventory_app`

## بيانات الدخول التجريبية

| المستخدم | كلمة المرور | الصلاحية |
|---|---|---|
| `admin` | `admin123` | مدير: كل العمليات |
| `employee` | `emp123` | موظف: العرض والبحث فقط |

## هيكل المجلدات

| المسار | المحتوى |
|---|---|
| `config/` | إعدادات الاتصال والرفع وبدء الجلسة |
| `classes/` | `Database` و`Product` و`Category` و`User` |
| `includes/` | القالب (header/footer)، دوال الصلاحيات، الدوال المساعدة |
| `assets/` | ملف CSS وملف JavaScript وصورة بديلة |
| `uploads/products/` | الصور المرفوعة (محمي بـ `.htaccess` يمنع تنفيذ السكربتات) |
| `sql/` | ملف إنشاء قاعدة البيانات |

شرح تفصيلي للكود وتقسيم العمل وأسئلة المناقشة في `دليل_المناقشة.md`.
