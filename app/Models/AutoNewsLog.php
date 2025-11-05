<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class AutoNewsLog extends Database
{
    public function insertLog($source, $articlesFetched, $articlesSaved, $status, $errorMessage = '', $processingTime = 0)
    {
        $sql = "INSERT INTO auto_news_log (fetch_time, source, articles_fetched, articles_saved, status, error_message, processing_time_seconds, created_at)
                VALUES (NOW(), ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute([$source, $articlesFetched, $articlesSaved, $status, $errorMessage, $processingTime]);
    }
}
