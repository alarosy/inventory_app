-- قاعدة بيانات نظام المخزون
-- الاستيراد: mysql -u student -p inventory_db < sql/database.sql

SET NAMES utf8mb4;

-- الحذف بترتيب عكسي: الجدول الذي فيه المفتاح الأجنبي أولًا
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(30)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    full_name  VARCHAR(100) NOT NULL,
    role       ENUM('admin', 'employee') NOT NULL DEFAULT 'employee',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(50) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    quantity    INT UNSIGNED NOT NULL DEFAULT 0,
    price       DECIMAL(10,2) NOT NULL,
    image       VARCHAR(255) NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_products_name (name),
    INDEX idx_products_category (category_id),
    -- RESTRICT: لا يُسمح بحذف تصنيف ما دامت هناك منتجات مرتبطة به
    CONSTRAINT fk_products_category FOREIGN KEY (category_id)
        REFERENCES categories (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- المستخدمون: admin / admin123 و employee / emp123
-- كلمات المرور مخزنة كـ hash تم توليده بـ password_hash()
INSERT INTO users (username, password, full_name, role) VALUES
('admin',    '$2y$12$yRQWzPwx1quvVYs/Oe8X4uyQ7NWzghe22V/TdrmMwhLCqlqtHtU96', 'مدير النظام', 'admin'),
('employee', '$2y$12$G4sQEa6f4aMR0aWgsjnt6OzujQqY3Mk1YmxG1ryfJ/bWLDNzdaH0G', 'موظف المخزن', 'employee');

INSERT INTO categories (id, name) VALUES
(1, 'إلكترونيات'),
(2, 'قرطاسية'),
(3, 'أثاث مكتبي'),
(4, 'مستلزمات تنظيف');

INSERT INTO products (name, category_id, quantity, price) VALUES
('فأرة لاسلكية',                1, 25,  45.00),
('لوحة مفاتيح عربي/إنجليزي',    1, 4,   85.50),
('شاشة 24 بوصة',                1, 7,   650.00),
('ذاكرة فلاش 64 جيجا',          1, 40,  35.00),
('دفتر محاضرات 100 ورقة',       2, 120, 6.50),
('أقلام حبر أزرق (علبة 12)',    2, 3,   18.00),
('ورق طباعة A4 (رزمة 500)',     2, 60,  32.75),
('دباسة مكتبية',                2, 15,  22.00),
('كرسي مكتب دوّار',             3, 5,   480.00),
('مكتب خشبي 120 سم',            3, 2,   920.00),
('معقّم أسطح 1 لتر',            4, 30,  14.25),
('مناديل ورقية (كرتونة)',       4, 0,   48.00);
