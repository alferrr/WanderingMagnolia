<?php

class RatingModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getByRecipe(int $recipeId): array {
        $stmt = $this->db->prepare(
            'SELECT r.*, u.first_name, u.last_name
             FROM ratings r
             JOIN users u ON r.user_id = u.user_id
             WHERE r.recipe_id = ?
             ORDER BY r.created_at DESC'
        );
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    public function getAverage(int $recipeId): float {
        $stmt = $this->db->prepare(
            'SELECT AVG(stars) FROM ratings WHERE recipe_id = ?'
        );
        $stmt->execute([$recipeId]);
        return round((float) $stmt->fetchColumn(), 1);
    }

    public function getCount(int $recipeId): int {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM ratings WHERE recipe_id = ?'
        );
        $stmt->execute([$recipeId]);
        return (int) $stmt->fetchColumn();
    }

    public function getByUser(int $userId, int $recipeId): array|false {
        $stmt = $this->db->prepare(
            'SELECT * FROM ratings WHERE user_id = ? AND recipe_id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $recipeId]);
        return $stmt->fetch();
    }

    public function hasRated(int $userId, int $recipeId): bool {
        return (bool) $this->getByUser($userId, $recipeId);
    }

    public function create(int $userId, int $recipeId, int $stars, ?string $review): void {
        $stmt = $this->db->prepare(
            'INSERT INTO ratings (user_id, recipe_id, stars, review) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $recipeId, $stars, $review]);
    }

    public function update(int $userId, int $recipeId, int $stars, ?string $review): void {
        $stmt = $this->db->prepare(
            'UPDATE ratings SET stars = ?, review = ? WHERE user_id = ? AND recipe_id = ?'
        );
        $stmt->execute([$stars, $review, $userId, $recipeId]);
    }

    public function upsert(int $userId, int $recipeId, int $stars, ?string $review): void {
        if ($this->hasRated($userId, $recipeId)) {
            $this->update($userId, $recipeId, $stars, $review);
        } else {
            $this->create($userId, $recipeId, $stars, $review);
        }
    }

    public function delete(int $userId, int $recipeId): void {
        $stmt = $this->db->prepare(
            'DELETE FROM ratings WHERE user_id = ? AND recipe_id = ?'
        );
        $stmt->execute([$userId, $recipeId]);
    }

    // Get top rated recipe IDs ordered by average stars
    public function getTopRatedIds(int $limit = 50): array {
        $stmt = $this->db->prepare(
            'SELECT recipe_id, AVG(stars) AS avg_stars, COUNT(*) AS total
             FROM ratings
             GROUP BY recipe_id
             HAVING total >= 1
             ORDER BY avg_stars DESC, total DESC
             LIMIT ?'
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}