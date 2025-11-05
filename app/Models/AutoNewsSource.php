<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class AutoNewsSource extends Database
{
    public function getActiveSources()
    {
        $sql = "SELECT * FROM auto_news_sources WHERE is_active = 1";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
