<?php
namespace App\Core;

use PDO;

abstract class Model
{
    protected PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Get database connection instance
     */
    public function getDb(): PDO
    {
        return $this->db;
    }

    /**
     * Get table name from class name
     */
    protected function getTableName(): string
    {
        $className = (new \ReflectionClass($this))->getShortName();
        return strtolower($className) . 's';
    }

    /**
     * Find record by ID
     */
    public function find(int $id): ?array
    {
        $table = $this->getTableName();
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Get all records
     */
    public function all(): array
    {
        $table = $this->getTableName();
        $stmt = $this->db->query("SELECT * FROM {$table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete a record
     */
    public function delete(int $id): bool
    {
        $table = $this->getTableName();
        $stmt = $this->db->prepare("DELETE FROM {$table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}


