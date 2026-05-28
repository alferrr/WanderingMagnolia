-- ============================================================
-- Wandering Magnolias — Full Schema (MySQL / MariaDB)
-- Updated: includes ratings and is_admin
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ─── users ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `user_id`       INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `first_name`    VARCHAR(80)     NOT NULL,
  `last_name`     VARCHAR(80)     NOT NULL,
  `user_email`    VARCHAR(180)    NOT NULL,
  `user_password` VARCHAR(255)    NOT NULL,
  `status`        ENUM('active','archived') NOT NULL DEFAULT 'active',
  `is_admin`      TINYINT(1)      NOT NULL DEFAULT 0,
  `archived_at`   TIMESTAMP       NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP       NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uq_user_email` (`user_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ─── recipes ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `recipes` (
  `recipe_id`    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`      INT UNSIGNED    NULL DEFAULT NULL,
  `remixed_from` INT UNSIGNED    NULL DEFAULT NULL,
  `title`        VARCHAR(255)    NOT NULL,
  `image_url`    VARCHAR(512)    NOT NULL DEFAULT '/assets/images/default-recipe.jpg',
  `difficulty`   ENUM('Easy','Intermediate','Hard') NOT NULL DEFAULT 'Easy',
  `is_premade`   TINYINT(1)      NOT NULL DEFAULT 0,
  `is_deleted`   TINYINT(1)      NOT NULL DEFAULT 0,
  `deleted_at`   TIMESTAMP       NULL DEFAULT NULL,
  `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recipe_id`),
  KEY `idx_recipes_user`    (`user_id`),
  KEY `idx_recipes_remix`   (`remixed_from`),
  KEY `idx_recipes_deleted` (`is_deleted`),
  CONSTRAINT `fk_recipes_user`  FOREIGN KEY (`user_id`)      REFERENCES `users`   (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_recipes_remix` FOREIGN KEY (`remixed_from`) REFERENCES `recipes` (`recipe_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ─── ingredients ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `ingredients` (
  `ingredient_id` INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `recipe_id`     INT UNSIGNED    NOT NULL,
  `name`          VARCHAR(150)    NOT NULL,
  `base_quantity` DECIMAL(10,2)   NOT NULL DEFAULT 1.00,
  `unit`          VARCHAR(50)     NOT NULL DEFAULT '',
  PRIMARY KEY (`ingredient_id`),
  KEY `idx_ingredients_recipe` (`recipe_id`),
  CONSTRAINT `fk_ingredients_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ─── directions ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `directions` (
  `direction_id` INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `recipe_id`    INT UNSIGNED    NOT NULL,
  `step_number`  INT UNSIGNED    NOT NULL,
  `instruction`  TEXT            NOT NULL,
  PRIMARY KEY (`direction_id`),
  KEY `idx_directions_recipe` (`recipe_id`),
  CONSTRAINT `fk_directions_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ─── ratings ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `ratings` (
  `rating_id`  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED    NOT NULL,
  `recipe_id`  INT UNSIGNED    NOT NULL,
  `stars`      TINYINT         NOT NULL,
  `review`     TEXT            NULL DEFAULT NULL,
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rating_id`),
  UNIQUE KEY `uq_rating` (`user_id`, `recipe_id`),
  KEY `idx_ratings_recipe` (`recipe_id`),
  CONSTRAINT `fk_ratings_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`   (`user_id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_ratings_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ─── password_resets ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_email` VARCHAR(180)    NOT NULL,
  `token`      VARCHAR(6)      NOT NULL,
  `expires_at` TIMESTAMP       NOT NULL,
  `used`       TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pr_email` (`user_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;