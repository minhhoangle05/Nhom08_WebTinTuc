<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Tag extends Model
{
    
    /**
     * Get all tags
     */
    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM tags ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find tag by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tags WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $tag = $stmt->fetch(PDO::FETCH_ASSOC);
        return $tag ?: null;
    }

    /**
     * Find tag by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tags WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $tag = $stmt->fetch(PDO::FETCH_ASSOC);
        return $tag ?: null;
    }

    /**
     * Get tags with article count
     */
    public function withArticleCount(): array
    {
        $stmt = $this->db->query('
            SELECT t.*, COUNT(at.article_id) as article_count
            FROM tags t
            LEFT JOIN article_tags at ON t.id = at.tag_id
            LEFT JOIN articles a ON at.article_id = a.id AND a.status = "published"
            GROUP BY t.id
            ORDER BY t.name
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new tag
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO tags (name, slug) VALUES (?, ?)');
        $stmt->execute([
            $data['name'],
            $data['slug']
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update a tag
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare('UPDATE tags SET name = ?, slug = ? WHERE id = ?');
        return $stmt->execute([
            $data['name'],
            $data['slug'],
            $id
        ]);
    }

    /**
     * Delete a tag
     */
    public function delete(int $id): bool
    {
        // First delete all article_tag relationships
        $stmt = $this->db->prepare('DELETE FROM article_tags WHERE tag_id = ?');
        $stmt->execute([$id]);
        
        // Then delete the tag
        $stmt = $this->db->prepare('DELETE FROM tags WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Find or create tag by name
     */
    public function findOrCreate(string $name): int
    {
        $slug = $this->generateSlug($name);
        
        // Try to find existing tag
        $tag = $this->findBySlug($slug);
        if ($tag) {
            return $tag['id'];
        }
        
        // Create new tag
        return $this->create(['name' => $name, 'slug' => $slug]);
    }

    /**
     * Generate slug from name
     */
    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
