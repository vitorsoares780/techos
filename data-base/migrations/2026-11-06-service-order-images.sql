CREATE TABLE IF NOT EXISTS `db-techos`.`service_order_images` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `service_order_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `alt_text` VARCHAR(255) DEFAULT NULL,
  `position` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_service_order_images_service_order_id` (`service_order_id` ASC),
  CONSTRAINT `fk_service_order_images_service_order`
    FOREIGN KEY (`service_order_id`)
    REFERENCES `db-techos`.`service_orders` (`id`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;
