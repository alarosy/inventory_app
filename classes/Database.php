<?php
class Database
{
    // اتصال واحد يُعاد استخدامه بدل فتح اتصال جديد في كل كلاس
    private static $connection = null;

    public static function connect()
    {
        if (self::$connection === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            try {
                self::$connection = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // نطلب Prepared Statements حقيقية من MySQL وليس محاكاة
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $ex) {
                http_response_code(500);
                die('تعذّر الاتصال بقاعدة البيانات. تحقق من الإعدادات في config/config.php');
            }
        }
        return self::$connection;
    }
}
