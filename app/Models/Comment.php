<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Comment extends Model
{
    public function countByArticle(int $articleId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM comments WHERE article_id = ?');
        $stmt->execute([$articleId]);
        return (int)$stmt->fetchColumn();
    }
}


