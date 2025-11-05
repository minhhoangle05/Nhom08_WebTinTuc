<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Article extends Model
{

    /**
     * Build the base SQL query with all necessary joins
     */
    /**
     * Create a new article with full validation and error handling
     */
    public function create(array $data): int
    {
        try {
            $this->db->beginTransaction();

            // Validate slug uniqueness
            if ($this->slugExists($data['slug'])) {
                throw new \Exception('Slug đã tồn tại, vui lòng chọn slug khác');
            }

            // 1. Insert article
            $stmt = $this->db->prepare('
                INSERT INTO articles (
                    title, 
                    slug, 
                    content,
                    summary,
                    user_id, 
                    category_id,
                    featured_image,
                    created_at,
                    updated_at
                ) VALUES (
                    :title,
                    :slug,
                    :content,
                    :summary,
                    :user_id,
                    :category_id,
                    :featured_image,
                    NOW(),
                    NOW()
                )
            ');

            $stmt->execute([
                ':title' => $data['title'],
                ':slug' => $data['slug'],
                ':content' => $data['content'],
                ':summary' => $data['summary'] ?? null,
                ':user_id' => $data['user_id'],
                ':category_id' => $data['category_id'],
                ':featured_image' => $data['featured_image'] ?? null
            ]);

            $articleId = (int)$this->db->lastInsertId();

            // 2. Handle tags if provided
            if (!empty($data['tags'])) {
                $tagStmt = $this->db->prepare('
                    INSERT INTO article_tags (article_id, tag_id) 
                    VALUES (:article_id, :tag_id)
                ');

                foreach ($data['tags'] as $tagId) {
                    $tagStmt->execute([
                        ':article_id' => $articleId,
                        ':tag_id' => $tagId
                    ]);
                }
            }

            $this->db->commit();
            return $articleId;

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Database error in Article::create: " . $e->getMessage());
            throw new \Exception('Không thể tạo bài viết. Vui lòng thử lại sau.');
        }
    }
    public function insertArticle($data)
{
    $stmt = $this->db->prepare("
        INSERT INTO articles (
            user_id, title, summary, slug, content,
            views, created_at, updated_at, source_url, source_name,featured_image,
            auto_generated
        )
        VALUES (
            :user_id, :title, :summary,:slug, :content,
            :views, :created_at, :updated_at, :source_url, :source_name, :featured_image,
            :auto_generated
        )
    ");

    $stmt->execute([
        ':user_id'        => $data['user_id'],
        ':title'          => $data['title'],
        ':summary'        => $data['summary'],
        ':slug'           => $data['slug'],
        ':content'        => $data['content'],
        ':views'          => $data['views'],
        ':created_at'     => $data['created_at'],
        ':updated_at'     => $data['updated_at'],
        ':source_url'     => $data['source_url'],
        ':source_name'    => $data['source_name'],
        ':featured_image' => $data['featured_image'],
        ':auto_generated' => $data['auto_generated'],
    ]);
}




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
            error_log("Adding category_id condition: " . $params['category_id']);
        }

        if (!empty($params['tag'])) {
            $where[] = 't.name = ?';
            $values[] = $params['tag'];
            error_log("Adding tag condition: " . $params['tag']);
        }

        if (!empty($params['q'])) {
            $where[] = '(a.title LIKE ? OR a.content LIKE ? OR a.summary LIKE ?)';
            $searchTerm = '%' . $params['q'] . '%';
            $values[] = $searchTerm;
            $values[] = $searchTerm;
            $values[] = $searchTerm;
            error_log("Adding search term condition: " . $params['q']);
        }

        // Không cần filter status nữa vì tất cả bài viết đều là published

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
        error_log("Article::search called with limit=$limit, offset=$offset");
        error_log("Starting article search with params: " . print_r($params, true));
        
        $conditions = $this->buildSearchConditions($params);
        $where = $conditions['where'];
        $values = $conditions['values'];

        $sql = $this->buildBaseQuery();

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
            error_log("Search WHERE clause: " . implode(' AND ', $where));
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
     * Get all articles with pagination
     */
    public function all(int $limit = 10, int $offset = 0): array
    {
        return $this->search([], $limit, $offset);
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
            'category_id' => $categoryId
        ], $limit, $offset);
    }

    /**
     * Find articles by category slug with pagination
     */
    public function findByCategorySlug(string $categorySlug, int $limit = 10, int $offset = 0): array
    {
        return $this->search([
            'category_slug' => $categorySlug
        ], $limit, $offset);
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
     * Get featured articles (most viewed articles)
     */
    public function featuredToday(int $limit = 6): array
    {
        $sql = $this->buildBaseQuery();
        $sql .= ' GROUP BY a.id
                  ORDER BY a.views DESC, a.created_at DESC
                  LIMIT ?';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get most popular articles
     */
    public function popular(int $limit = 5): array
    {
        return $this->search([], $limit, 0);
    }

    /**
     * Get latest articles
     */
    public function latest(int $limit = 10): array
    {
        return $this->search([], $limit, 0);
    }

    /**
     * Find related articles based on category and tags
     */
    public function findRelated(int $articleId, ?int $categoryId, int $limit = 4): array
    {
        try {
            // Bắt đầu với truy vấn cơ bản
            $sql = $this->buildBaseQuery();
            $params = [$articleId];
            
            if ($categoryId) {
                // Ưu tiên bài viết cùng danh mục
                $sql .= ' WHERE a.id != ? AND (
                    a.category_id = ? OR 
                    EXISTS (
                        SELECT 1 FROM article_tags at1 
                        JOIN article_tags at2 ON at1.tag_id = at2.tag_id 
                        WHERE at1.article_id = a.id AND at2.article_id = ?
                    )
                )';
                $params[] = $categoryId;
                $params[] = $articleId;
            } else {
                // Nếu không có danh mục, chỉ dựa vào tags
                $sql .= ' WHERE a.id != ? AND EXISTS (
                    SELECT 1 FROM article_tags at1 
                    JOIN article_tags at2 ON at1.tag_id = at2.tag_id 
                    WHERE at1.article_id = a.id AND at2.article_id = ?
                )';
                $params[] = $articleId;
            }

            $sql .= ' GROUP BY a.id ORDER BY 
                     CASE WHEN a.category_id = ? THEN 1 ELSE 2 END,
                     a.views DESC, 
                     a.created_at DESC 
                     LIMIT ?';
            
            $params[] = $categoryId ?? 0;
            $params[] = $limit;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            error_log("Error finding related articles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent articles
     */
    public function recent(int $limit = 10): array
    {
        return $this->search([], $limit, 0);
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

    /**
     * Get articles by user
     */
    public function getByUser(int $userId, int $limit = 10): array
    {
        $stmt = $this->db->prepare('
            SELECT a.*, 
                   u.name as author_name,
                   c.name as category_name,
                   (SELECT COUNT(*) FROM comments WHERE article_id = a.id) as comment_count
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.user_id = ?
            ORDER BY a.created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Count articles by category
     */
    public function countByCategory(int $categoryId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM articles WHERE category_id = ?');
        $stmt->execute([$categoryId]);
        return (int)$stmt->fetchColumn();
    }
}
