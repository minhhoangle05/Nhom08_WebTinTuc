<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Article extends Model
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_PRIVATE = 'private';

    /**
     * Build the base SQL query with all necessary joins
     */
    protected function buildBaseQuery(): string
    {
        return 'SELECT a.*, 
                u.name AS author_name, 
                c.name AS category_name,
                c.slug AS category_slug,
                GROUP_CONCAT(DISTINCT t.name) as tags,
                COUNT(DISTINCT cm.id) as comment_count
                FROM articles a 
                LEFT JOIN users u ON a.user_id = u.id 
                LEFT JOIN categories c ON a.category_id = c.id
                LEFT JOIN article_tags at ON a.id = at.article_id
                LEFT JOIN tags t ON at.tag_id = t.id
                LEFT JOIN comments cm ON a.id = cm.article_id';
    }

    /**
     * Build search conditions based on provided parameters
     */
    protected function buildSearchConditions(array $params = []): array
    {
        $where = [];
        $values = [];

        if (!empty($params['category_id'])) {
            $where[] = 'a.category_id = ?';
            $values[] = $params['category_id'];
        }

        if (!empty($params['category'])) {
            $where[] = 'c.name = ?';
            $values[] = $params['category'];
        }

        if (!empty($params['category_slug'])) {
            $where[] = 'c.slug = ?';
            $values[] = $params['category_slug'];
        }

        if (!empty($params['tag'])) {
            $where[] = 't.name = ?';
            $values[] = $params['tag'];
        }

        if (!empty($params['q'])) {
            $where[] = '(a.title LIKE ? OR a.content LIKE ?)';
            $searchTerm = '%' . $params['q'] . '%';
            $values[] = $searchTerm;
            $values[] = $searchTerm;
        }

        if (isset($params['status'])) {
            if ($params['status'] === 'all') {
                // No status filter
            } else {
                $where[] = 'a.status = ?';
                $values[] = $params['status'];
            }
        } else if (!isset($params['include_drafts'])) {
            // Default: only show published articles for public views
            $where[] = 'a.status = ?';
            $values[] = self::STATUS_PUBLISHED;
        }

        if (!empty($params['user_id'])) {
            $where[] = 'a.user_id = ?';
            $values[] = $params['user_id'];
        }

        if (!empty($params['date_from'])) {
            $where[] = 'a.created_at >= ?';
            $values[] = $params['date_from'];
        }

        if (!empty($params['date_to'])) {
            $where[] = 'a.created_at <= ?';
            $values[] = $params['date_to'];
        }

        return ['where' => $where, 'values' => $values];
    }

    /**
     * Search articles with filters, pagination, and sorting
     */
    public function search(array $params = [], int $limit = 10, int $offset = 0): array
    {
        $conditions = $this->buildSearchConditions($params);
        $where = $conditions['where'];
        $values = $conditions['values'];

        $sql = $this->buildBaseQuery();

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY a.id';

        if (!empty($params['sort'])) {
            $order = match ($params['sort']) {
                'oldest' => 'a.created_at ASC',
                'title' => 'a.title ASC',
                'popular' => 'a.views DESC',
                'comments' => 'comment_count DESC',
                'updated' => 'a.updated_at DESC',
                default => 'a.created_at DESC'
            };
            $sql .= ' ORDER BY ' . $order;
        } else {
            $sql .= ' ORDER BY a.created_at DESC';
        }

        if ($limit > 0) {
            $sql .= ' LIMIT ? OFFSET ?';
            $values[] = $limit;
            $values[] = $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count total articles matching search criteria
     */
    public function countSearch(array $params = []): int
    {
        $conditions = $this->buildSearchConditions($params);
        $where = $conditions['where'];
        $values = $conditions['values'];

        $sql = 'SELECT COUNT(DISTINCT a.id) 
                FROM articles a 
                LEFT JOIN users u ON a.user_id = u.id 
                LEFT JOIN categories c ON a.category_id = c.id
                LEFT JOIN article_tags at ON a.id = at.article_id
                LEFT JOIN tags t ON at.tag_id = t.id';

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get all published articles with pagination
     */
    public function all(int $limit = 10, int $offset = 0): array
    {
        return $this->search(['status' => self::STATUS_PUBLISHED], $limit, $offset);
    }

    /**
     * Find all articles (alias for search)
     */
    public function findAll(array $params = []): array
    {
        return $this->search($params);
    }

    /**
     * Find articles by category ID with pagination
     */
    public function findByCategory(int $categoryId, int $limit = 10, int $offset = 0): array
    {
        return $this->search([
            'category_id' => $categoryId,
            'status' => self::STATUS_PUBLISHED
        ], $limit, $offset);
    }

    /**
     * Find articles by category slug with pagination
     */
    public function findByCategorySlug(string $categorySlug, int $limit = 10, int $offset = 0): array
    {
        return $this->search([
            'category_slug' => $categorySlug,
            'status' => self::STATUS_PUBLISHED
        ], $limit, $offset);
    }

    /**
     * Count articles by category
     */
    public function countByCategory(int $categoryId): int
    {
        return $this->countSearch([
            'category_id' => $categoryId,
            'status' => self::STATUS_PUBLISHED
        ]);
    }

    /**
     * Find article by ID
     */
    public function findById(int $id): ?array
    {
        $sql = $this->buildBaseQuery();
        $sql .= ' WHERE a.id = ? GROUP BY a.id LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        return $article ?: null;
    }

    /**
     * Find article by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $sql = $this->buildBaseQuery();
        $sql .= ' WHERE a.slug = ? GROUP BY a.id LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$slug]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        return $article ?: null;
    }

    /**
     * Get user's draft articles
     */
    public function getUserDrafts(int $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->search([
            'user_id' => $userId,
            'status' => self::STATUS_DRAFT,
            'include_drafts' => true
        ], $limit, $offset);
    }

    /**
     * Get a draft by ID
     */
    public function getDraft(int $id): ?array
    {
        return $this->findById($id);
    }

    /**
     * Publish an article
     */
    public function publish(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE articles SET status = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([self::STATUS_PUBLISHED, $id]);
    }

    /**
     * Unpublish an article (set to draft)
     */
    public function unpublish(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE articles SET status = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([self::STATUS_DRAFT, $id]);
    }

    /**
     * Save article as draft
     */
    public function saveDraft(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE articles SET status = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([self::STATUS_DRAFT, $id]);
    }

    /**
     * Get featured articles (most viewed published articles)
     */
    public function featuredToday(int $limit = 6): array
    {
        $sql = $this->buildBaseQuery();
        $sql .= ' WHERE a.status = ?
                  GROUP BY a.id
                  ORDER BY a.views DESC, a.created_at DESC
                  LIMIT ?';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([self::STATUS_PUBLISHED, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get most popular articles
     */
    public function popular(int $limit = 5): array
    {
        return $this->search(['status' => self::STATUS_PUBLISHED], $limit, 0);
    }

    /**
     * Get latest published articles
     */
    public function latest(int $limit = 10): array
    {
        return $this->search(['status' => self::STATUS_PUBLISHED], $limit, 0);
    }

    /**
     * Get recent articles by user (including drafts)
     */
    public function getByUser(int $userId, int $limit = 10, int $offset = 0): array
    {
        return $this->search([
            'user_id' => $userId,
            'status' => 'all'
        ], $limit, $offset);
    }

    /**
     * Get recent published articles
     */
    public function recent(int $limit = 10): array
    {
        return $this->search(['status' => self::STATUS_PUBLISHED], $limit, 0);
    }

    /**
     * Create a new article
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO articles (
            title, slug, content, user_id, category_id, status
        ) VALUES (?, ?, ?, ?, ?, ?)');

        $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['content'],
            $data['user_id'],
            $data['category_id'] ?? null,
            $data['status'] ?? self::STATUS_DRAFT
        ]);

        $articleId = (int)$this->db->lastInsertId();

        if (!empty($data['tags'])) {
            $this->saveTags($articleId, $data['tags']);
        }

        return $articleId;
    }

    /**
     * Update an existing article
     */
    public function update(int $id, array $data): bool
    {
        $sets = [];
        $params = [];

        $fields = ['title', 'slug', 'content', 'category_id', 'status'];

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $sets[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($sets)) {
            return false;
        }

        $sets[] = "updated_at = NOW()";

        $params[] = $id;
        $sql = "UPDATE articles SET " . implode(', ', $sets) . " WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($params);

        if ($result && isset($data['tags'])) {
            $this->saveTags($id, $data['tags']);
        }

        return $result;
    }

    /**
     * Save tags for an article
     */
    public function saveTags(int $articleId, array $tags): bool
    {
        $stmt = $this->db->prepare('DELETE FROM article_tags WHERE article_id = ?');
        $stmt->execute([$articleId]);

        if (empty($tags)) {
            return true;
        }

        $values = array_fill(0, count($tags), '(?,?)');
        $sql = "INSERT INTO article_tags (article_id, tag_id) VALUES " . implode(',', $values);
        
        $params = [];
        foreach ($tags as $tagId) {
            $params[] = $articleId;
            $params[] = $tagId;
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get all tags for an article
     */
    public function getArticleTags(int $articleId): array
    {
        $stmt = $this->db->prepare('
            SELECT t.* FROM tags t
            JOIN article_tags at ON at.tag_id = t.id
            WHERE at.article_id = ?
            ORDER BY t.name
        ');
        $stmt->execute([$articleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Increment view count for an article
     */
    public function incrementViews(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE articles SET views = views + 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Delete an article and all related data
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM article_tags WHERE article_id = ?');
        $stmt->execute([$id]);

        $stmt = $this->db->prepare('DELETE FROM comments WHERE article_id = ?');
        $stmt->execute([$id]);

        $stmt = $this->db->prepare('DELETE FROM articles WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Get related articles based on category and tags
     */
    public function getRelated(int $articleId, int $limit = 5): array
    {
        $article = $this->findById($articleId);
        if (!$article) {
            return [];
        }

        $sql = $this->buildBaseQuery();
        $sql .= ' WHERE a.id != ? AND (a.category_id = ? OR at.tag_id IN (
                    SELECT tag_id FROM article_tags WHERE article_id = ?
                  ))
                  GROUP BY a.id
                  ORDER BY (a.category_id = ?) DESC, COUNT(DISTINCT at.tag_id) DESC, a.created_at DESC
                  LIMIT ?';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $articleId,
            $article['category_id'],
            $articleId,
            $article['category_id'],
            $limit
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if slug exists (for validation)
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM articles WHERE slug = ? AND id != ?');
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM articles WHERE slug = ?');
            $stmt->execute([$slug]);
        }
        return $stmt->fetchColumn() > 0;
    }
}
