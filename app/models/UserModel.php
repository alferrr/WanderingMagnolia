<?php

class UserModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function findByEmail(string $email): array|false {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE user_email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE user_id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAll(): array {
        $stmt = $this->db->query(
            'SELECT u.*,
                    COUNT(DISTINCT r.recipe_id) AS recipe_count
             FROM users u
             LEFT JOIN recipes r ON u.user_id = r.user_id AND r.is_deleted = 0
             GROUP BY u.user_id
             ORDER BY u.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    public function create(string $firstName, string $lastName, string $email, string $password): bool {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'INSERT INTO users (first_name, last_name, user_email, user_password) VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$firstName, $lastName, $email, $hash]);
    }

    public function updateProfile(int $userId, string $firstName, string $lastName, string $email): bool {
        $stmt = $this->db->prepare(
            'UPDATE users SET first_name = ?, last_name = ?, user_email = ?, updated_at = NOW() WHERE user_id = ?'
        );
        return $stmt->execute([$firstName, $lastName, $email, $userId]);
    }

    public function updatePassword(int $userId, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'UPDATE users SET user_password = ?, updated_at = NOW() WHERE user_id = ?'
        );
        return $stmt->execute([$hash, $userId]);
    }

    public function archive(int $userId): bool {
        $stmt = $this->db->prepare(
            "UPDATE users SET status = 'archived', archived_at = NOW(), updated_at = NOW() WHERE user_id = ?"
        );
        return $stmt->execute([$userId]);
    }

    public function restore(int $userId): bool {
        $stmt = $this->db->prepare(
            "UPDATE users SET status = 'active', archived_at = NULL, updated_at = NOW() WHERE user_id = ?"
        );
        return $stmt->execute([$userId]);
    }

    public function toggleAdmin(int $userId): void {
        $stmt = $this->db->prepare(
            'UPDATE users SET is_admin = IF(is_admin = 1, 0, 1) WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
    }

    public function delete(int $userId): void {
        $stmt = $this->db->prepare('DELETE FROM users WHERE user_id = ?');
        $stmt->execute([$userId]);
    }

      public function getActiveUsers(): array {
        $stmt = $this->db->query(
            "SELECT u.*,
                    COUNT(DISTINCT r.recipe_id) AS recipe_count
             FROM users u
             LEFT JOIN recipes r ON u.user_id = r.user_id AND r.is_deleted = 0
             WHERE u.status = 'active'
             GROUP BY u.user_id
             ORDER BY u.created_at DESC"
        );
        return $stmt->fetchAll();
    }
 
    public function getArchivedUsers(): array {
        $stmt = $this->db->query(
            "SELECT u.*,
                    COUNT(DISTINCT r.recipe_id) AS recipe_count
             FROM users u
             LEFT JOIN recipes r ON u.user_id = r.user_id AND r.is_deleted = 0
             WHERE u.status = 'archived'
             GROUP BY u.user_id
             ORDER BY u.archived_at DESC"
        );
        return $stmt->fetchAll();
    }
 
    public function getArchivedCount(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE status = 'archived'");
        return (int) $stmt->fetchColumn();
    }
 
}