<?php
class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findByUsername($username)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function getAll()
    {
        // لا نجلب عمود كلمة المرور لأننا لا نحتاجه في العرض
        $stmt = $this->db->query('SELECT id, username, full_name, role, created_at FROM users ORDER BY id');
        return $stmt->fetchAll();
    }

    public function create($username, $password, $fullName, $role)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$username, $hash, $fullName, $role]);
    }
}
