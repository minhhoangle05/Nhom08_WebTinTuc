<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Role extends Model
{
    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM roles ORDER BY id');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


