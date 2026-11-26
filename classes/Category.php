<?php
class Category
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // نجلب مع كل تصنيف عدد منتجاته، وLEFT JOIN حتى تظهر التصنيفات الفارغة أيضًا
    public function getAll()
    {
        $sql = 'SELECT c.id, c.name, c.created_at, COUNT(p.id) AS products_count
                FROM categories c
                LEFT JOIN products p ON p.category_id = c.id
                GROUP BY c.id, c.name, c.created_at
                ORDER BY c.name';
        return $this->db->query($sql)->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function nameExists($name)
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM categories WHERE name = ?');
        $stmt->execute([$name]);
        return $stmt->fetchColumn() > 0;
    }

    public function create($name)
    {
        $stmt = $this->db->prepare('INSERT INTO categories (name) VALUES (?)');
        return $stmt->execute([$name]);
    }

    public function countProducts($id)
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }

    // ترجع عدد المنتجات المرتبطة إن وُجدت (ولا تحذف)، أو 0 عند نجاح الحذف
    public function delete($id)
    {
        $count = $this->countProducts($id);
        if ($count > 0) {
            return $count;
        }
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return 0;
    }
}
