<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Category extends Model
{
    
    /**
     * Get all categories
     */
    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find category by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        return $category ?: null;
    }

    /**
     * Get popular categories based on the number of articles
     */
    public function getPopularCategories(int $limit = 5): array
    {
        $stmt = $this->db->prepare('
            SELECT c.*, COUNT(a.id) as article_count 
            FROM categories c
            LEFT JOIN articles a ON c.id = a.category_id
            GROUP BY c.id
            ORDER BY article_count DESC
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        return $category ?: null;
    }

    /**
     * Get category with article count
     */
    public function withArticleCount(): array
    {
        $stmt = $this->db->query('
            SELECT c.*, COUNT(a.id) as article_count
            FROM categories c
            LEFT JOIN articles a ON c.id = a.category_id AND a.status = "published"
            GROUP BY c.id
            ORDER BY c.name
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new category
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO categories (name, slug, parent_id) VALUES (?, ?, ?)');
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['parent_id'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update a category
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE categories SET name = ?, slug = ?, parent_id = ? WHERE id = ?');
        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['parent_id'] ?? null,
            $id
        ]);
    }

    /**
     * Delete a category
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
