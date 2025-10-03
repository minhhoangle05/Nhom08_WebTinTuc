<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(string $name, string $email, string $passwordHash, int $roleId = 1): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password_hash, role_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $passwordHash, $roleId]);
        return (int)$this->db->lastInsertId();
    }
}


