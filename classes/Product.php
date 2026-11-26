<?php
class Product
{
    // ORDER BY لا يقبل معاملات مربوطة، لذلك نختار الترتيب من قائمة مسموحات ثابتة
    const SORT_OPTIONS = [
        'newest'     => 'p.created_at DESC, p.id DESC',
        'name'       => 'p.name ASC',
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'quantity'   => 'p.quantity ASC',
    ];

    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getAll($filters = [])
    {
        $sql = 'SELECT p.*, c.name AS category_name
                FROM products p
                JOIN categories c ON c.id = p.category_id';

        // نبني شروط WHERE حسب الفلاتر المستخدمة فقط، وكل قيمة تمر كمعامل مربوط
        $conditions = [];
        $params = [];

        if (!empty($filters['name'])) {
            $conditions[] = 'p.name LIKE :name';
            $params[':name'] = '%' . addcslashes($filters['name'], '%_\\') . '%';
        }
        if (!empty($filters['category_id'])) {
            $conditions[] = 'p.category_id = :category_id';
            $params[':category_id'] = $filters['category_id'];
        }
        if (isset($filters['min_price'])) {
            $conditions[] = 'p.price >= :min_price';
            $params[':min_price'] = $filters['min_price'];
        }
        if (isset($filters['max_price'])) {
            $conditions[] = 'p.price <= :max_price';
            $params[':max_price'] = $filters['max_price'];
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sort = $filters['sort'] ?? 'newest';
        if (!isset(self::SORT_OPTIONS[$sort])) {
            $sort = 'newest';
        }
        $sql .= ' ORDER BY ' . self::SORT_OPTIONS[$sort];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.name AS category_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO products (name, category_id, quantity, price, image)
             VALUES (:name, :category_id, :quantity, :price, :image)'
        );
        $stmt->execute([
            ':name'        => $data['name'],
            ':category_id' => $data['category_id'],
            ':quantity'    => $data['quantity'],
            ':price'       => $data['price'],
            ':image'       => $data['image'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare(
            'UPDATE products
             SET name = :name, category_id = :category_id, quantity = :quantity,
                 price = :price, image = :image
             WHERE id = :id'
        );
        return $stmt->execute([
            ':name'        => $data['name'],
            ':category_id' => $data['category_id'],
            ':quantity'    => $data['quantity'],
            ':price'       => $data['price'],
            ':image'       => $data['image'],
            ':id'          => $id,
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function getStats()
    {
        $stats = $this->db->query(
            'SELECT COUNT(*) AS products_count,
                    COALESCE(SUM(quantity), 0) AS total_quantity,
                    COALESCE(SUM(quantity * price), 0) AS total_value
             FROM products'
        )->fetch();

        $stats['by_category'] = $this->db->query(
            'SELECT c.name, COUNT(p.id) AS products_count, COALESCE(SUM(p.quantity * p.price), 0) AS total_value
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id, c.name
             ORDER BY products_count DESC, c.name'
        )->fetchAll();

        $stmt = $this->db->prepare(
            'SELECT p.id, p.name, p.quantity, c.name AS category_name
             FROM products p
             JOIN categories c ON c.id = p.category_id
             WHERE p.quantity <= ?
             ORDER BY p.quantity ASC, p.name'
        );
        $stmt->execute([LOW_STOCK_LIMIT]);
        $stats['low_stock'] = $stmt->fetchAll();

        return $stats;
    }

    // التحقق من بيانات النموذج، مشترك بين الإضافة والتعديل
    public function validate($data, $categoryModel)
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors[] = 'اسم المنتج مطلوب.';
        } elseif (mb_strlen($data['name']) > 100) {
            $errors[] = 'اسم المنتج يجب ألا يزيد عن 100 حرف.';
        }

        $categoryId = filter_var($data['category_id'], FILTER_VALIDATE_INT);
        if ($categoryId === false || !$categoryModel->getById($categoryId)) {
            $errors[] = 'اختر تصنيفًا صحيحًا.';
        }

        $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]]);
        if ($quantity === false) {
            $errors[] = 'الكمية يجب أن تكون رقمًا صحيحًا من 0 إلى 1000000.';
        }

        // السعر: رقم موجب بخانتين عشريتين على الأكثر، ويتسع لـ DECIMAL(10,2)
        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $data['price'])) {
            $errors[] = 'السعر يجب أن يكون رقمًا موجبًا بخانتين عشريتين على الأكثر (مثال: 25.50).';
        }

        return $errors;
    }
}
