<?php

class RecipeModel {
    private PDO $db;
    const PER_PAGE      = 9;
    const PER_PAGE_USER = 6;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll(): array {
        $stmt = $this->db->query(
            'SELECT r.*, u.first_name, u.last_name,
                    orig.title AS original_title, orig.recipe_id AS original_id,
                    COALESCE(AVG(rt.stars), 0) AS avg_rating,
                    COUNT(rt.rating_id) AS rating_count
             FROM recipes r
             LEFT JOIN users u ON r.user_id = u.user_id
             LEFT JOIN recipes orig ON r.remixed_from = orig.recipe_id
             LEFT JOIN ratings rt ON r.recipe_id = rt.recipe_id
             WHERE r.is_deleted = 0
             GROUP BY r.recipe_id
             ORDER BY r.created_at DESC'
        );
        return $stmt->fetchAll();
    }

   public function getPaginated(int $page = 1, string $search = '', string $difficulty = '', string $filter = '', ?int $stars = null): array {
    $offset     = ($page - 1) * self::PER_PAGE;
    $conditions = ['r.is_deleted = 0'];
    $params     = [];

    if ($search !== '') {
        $conditions[] = 'r.title LIKE ?';
        $params[]     = '%' . $search . '%';
    }
    if ($difficulty !== '') {
        $conditions[] = 'r.difficulty = ?';
        $params[]     = $difficulty;
    }
    if ($filter === 'original') {
        $conditions[] = 'r.remixed_from IS NULL AND r.is_premade = 0';
    } elseif ($filter === 'remix') {
        $conditions[] = 'r.remixed_from IS NOT NULL';
    } elseif ($filter === 'curated') {
        $conditions[] = 'r.is_premade = 1';
    }

    $where        = 'WHERE ' . implode(' AND ', $conditions);
    $having       = '';
    $havingParams = [];

    if ($stars === 0) {
        $having = 'HAVING COUNT(rt.rating_id) = 0';
    } elseif ($stars !== null && $stars >= 1) {
        $having = 'HAVING ROUND(COALESCE(AVG(rt.stars), 0)) = ?';
        $havingParams[] = $stars;
    }

    $orderBy = 'r.created_at DESC';
    if ($filter === 'top') {
        $orderBy = 'avg_rating DESC, rating_count DESC, r.created_at DESC';
    }

    $countStmt = $this->db->prepare(
        "SELECT COUNT(*) FROM (
            SELECT r.recipe_id
            FROM recipes r
            LEFT JOIN ratings rt ON r.recipe_id = rt.recipe_id
            $where
            GROUP BY r.recipe_id
            $having
        ) AS sub"
    );
    $countStmt->execute(array_merge($params, $havingParams));
    $total = (int) $countStmt->fetchColumn();

    $queryParams   = array_merge($params, $havingParams);
    $queryParams[] = self::PER_PAGE;
    $queryParams[] = $offset;

    $stmt = $this->db->prepare(
        "SELECT r.*, u.first_name, u.last_name,
                orig.title AS original_title, orig.recipe_id AS original_id,
                COALESCE(AVG(rt.stars), 0) AS avg_rating,
                COUNT(rt.rating_id) AS rating_count
         FROM recipes r
         LEFT JOIN users u ON r.user_id = u.user_id
         LEFT JOIN recipes orig ON r.remixed_from = orig.recipe_id
         LEFT JOIN ratings rt ON r.recipe_id = rt.recipe_id
         $where
         GROUP BY r.recipe_id
         $having
         ORDER BY $orderBy
         LIMIT ? OFFSET ?"
    );
    $stmt->execute($queryParams);

    return [
        'recipes'      => $stmt->fetchAll(),
        'total'        => $total,
        'per_page'     => self::PER_PAGE,
        'current_page' => $page,
        'total_pages'  => (int) ceil($total / self::PER_PAGE),
    ];
}

    public function getByUserPaginated(int $userId, int $page = 1, string $search = ''): array {
        $offset     = ($page - 1) * self::PER_PAGE_USER;
        $conditions = ['r.user_id = ?', 'r.is_deleted = 0'];
        $params     = [$userId];

        if ($search !== '') {
            $conditions[] = 'r.title LIKE ?';
            $params[]     = '%' . $search . '%';
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM recipes r $where");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $params[] = self::PER_PAGE_USER;
        $params[] = $offset;

        $stmt = $this->db->prepare(
            "SELECT r.*,
                    orig.title AS original_title, orig.recipe_id AS original_id,
                    COALESCE(AVG(rt.stars), 0) AS avg_rating,
                    COUNT(rt.rating_id) AS rating_count
             FROM recipes r
             LEFT JOIN recipes orig ON r.remixed_from = orig.recipe_id
             LEFT JOIN ratings rt ON r.recipe_id = rt.recipe_id
             $where
             GROUP BY r.recipe_id
             ORDER BY r.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);

        return [
            'recipes'      => $stmt->fetchAll(),
            'total'        => $total,
            'per_page'     => self::PER_PAGE_USER,
            'current_page' => $page,
            'total_pages'  => (int) ceil($total / self::PER_PAGE_USER),
        ];
    }

    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT r.*, orig.title AS original_title, orig.recipe_id AS original_id
             FROM recipes r
             LEFT JOIN recipes orig ON r.remixed_from = orig.recipe_id
             WHERE r.user_id = ? AND r.is_deleted = 0
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getTrashedByUser(int $userId): array {
        $stmt = $this->db->prepare(
            'SELECT r.*, orig.title AS original_title, orig.recipe_id AS original_id
             FROM recipes r
             LEFT JOIN recipes orig ON r.remixed_from = orig.recipe_id
             WHERE r.user_id = ? AND r.is_deleted = 1
             ORDER BY r.deleted_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.first_name, u.last_name,
                    orig.title AS original_title, orig.recipe_id AS original_id,
                    COALESCE(AVG(rt.stars), 0) AS avg_rating,
                    COUNT(rt.rating_id) AS rating_count
             FROM recipes r
             LEFT JOIN users u ON r.user_id = u.user_id
             LEFT JOIN recipes orig ON r.remixed_from = orig.recipe_id
             LEFT JOIN ratings rt ON r.recipe_id = rt.recipe_id
             WHERE r.recipe_id = ?
             GROUP BY r.recipe_id'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getIngredients(int $recipeId): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM ingredients WHERE recipe_id = ? ORDER BY ingredient_id'
        );
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public function getDirections(int $recipeId): array {
        $stmt = $this->db->prepare(
            'SELECT * FROM directions WHERE recipe_id = ? ORDER BY step_number'
        );
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, string $title, string $imageUrl, string $difficulty, ?int $remixedFrom = null): int {
        $stmt = $this->db->prepare(
            'INSERT INTO recipes (user_id, title, image_url, difficulty, is_premade, remixed_from)
             VALUES (?, ?, ?, ?, 0, ?)'
        );
        $stmt->execute([$userId, $title, $imageUrl, $difficulty, $remixedFrom]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $recipeId, string $title, string $difficulty, ?string $imageUrl = null): void {
        if ($imageUrl) {
            $stmt = $this->db->prepare(
                'UPDATE recipes SET title = ?, difficulty = ?, image_url = ? WHERE recipe_id = ?'
            );
            $stmt->execute([$title, $difficulty, $imageUrl, $recipeId]);
        } else {
            $stmt = $this->db->prepare(
                'UPDATE recipes SET title = ?, difficulty = ? WHERE recipe_id = ?'
            );
            $stmt->execute([$title, $difficulty, $recipeId]);
        }
    }

    public function softDelete(int $recipeId): void {
        $stmt = $this->db->prepare(
            'UPDATE recipes SET is_deleted = 1, deleted_at = NOW() WHERE recipe_id = ?'
        );
        $stmt->execute([$recipeId]);
    }

    public function restore(int $recipeId): void {
        $stmt = $this->db->prepare(
            'UPDATE recipes SET is_deleted = 0, deleted_at = NULL WHERE recipe_id = ?'
        );
        $stmt->execute([$recipeId]);
    }

    public function delete(int $recipeId): void {
        $stmt = $this->db->prepare('DELETE FROM recipes WHERE recipe_id = ?');
        $stmt->execute([$recipeId]);
    }

    public function purgeExpiredTrash(): void {
        $stmt = $this->db->prepare(
            'DELETE FROM recipes WHERE is_deleted = 1 AND deleted_at < NOW() - INTERVAL 30 DAY'
        );
        $stmt->execute();
    }

    public function clearIngredients(int $recipeId): void {
        $stmt = $this->db->prepare('DELETE FROM ingredients WHERE recipe_id = ?');
        $stmt->execute([$recipeId]);
    }

    public function clearDirections(int $recipeId): void {
        $stmt = $this->db->prepare('DELETE FROM directions WHERE recipe_id = ?');
        $stmt->execute([$recipeId]);
    }

    public function addIngredient(int $recipeId, string $name, float $qty, string $unit): void {
        $stmt = $this->db->prepare(
            'INSERT INTO ingredients (recipe_id, name, base_quantity, unit) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$recipeId, $name, $qty, $unit]);
    }

    public function addDirection(int $recipeId, int $step, string $instruction): void {
        $stmt = $this->db->prepare(
            'INSERT INTO directions (recipe_id, step_number, instruction) VALUES (?, ?, ?)'
        );
        $stmt->execute([$recipeId, $step, $instruction]);
    }

    // Admin


     public function getActiveForAdmin(): array {
        $stmt = $this->db->query(
            'SELECT r.*, u.first_name, u.last_name,
                    COALESCE(AVG(rt.stars), 0) AS avg_rating,
                    COUNT(DISTINCT rt.rating_id) AS rating_count
             FROM recipes r
             LEFT JOIN users u ON r.user_id = u.user_id
             LEFT JOIN ratings rt ON r.recipe_id = rt.recipe_id
             WHERE r.is_deleted = 0
             GROUP BY r.recipe_id
             ORDER BY r.created_at DESC'
        );
        return $stmt->fetchAll();
    }
 
    public function getTrashedForAdmin(): array {
        $stmt = $this->db->query(
            'SELECT r.*, u.first_name, u.last_name
             FROM recipes r
             LEFT JOIN users u ON r.user_id = u.user_id
             WHERE r.is_deleted = 1
             ORDER BY r.deleted_at DESC'
        );
        return $stmt->fetchAll();
    }
 
    public function getTrashedCountAdmin(): int {
        $stmt = $this->db->query('SELECT COUNT(*) FROM recipes WHERE is_deleted = 1');
        return (int) $stmt->fetchColumn();
    }
 
    // Keep getAllForAdmin for dashboard stats
    public function getAllForAdmin(): array {
        $stmt = $this->db->query(
            'SELECT r.*, u.first_name, u.last_name,
                    COALESCE(AVG(rt.stars), 0) AS avg_rating,
                    COUNT(DISTINCT rt.rating_id) AS rating_count
             FROM recipes r
             LEFT JOIN users u ON r.user_id = u.user_id
             LEFT JOIN ratings rt ON r.recipe_id = rt.recipe_id
             GROUP BY r.recipe_id
             ORDER BY r.created_at DESC'
        );
        return $stmt->fetchAll();
    }
}