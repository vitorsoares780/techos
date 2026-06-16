-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema db-techos
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `db-techos` DEFAULT CHARACTER SET utf8mb3 ;
USE `db-techos` ;
-- drop database `db-techos`;
-- -----------------------------------------------------
-- Table `db-techos`.`faqs_categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`faqs_categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `db-techos`.`faqs`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`faqs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `faqs_category_id` INT NOT NULL,
  `question` VARCHAR(255) NOT NULL,
  `answer` VARCHAR(255) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  INDEX `fk_faqs_faqs_categories1_idx` (`faqs_category_id` ASC) VISIBLE,
  CONSTRAINT `fk_faqs_faqs_categories1`
    FOREIGN KEY (`faqs_category_id`)
    REFERENCES `db-techos`.`faqs_categories` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 16
DEFAULT CHARACTER SET = utf8mb3;



-- -----------------------------------------------------
-- Table `db-techos`.`plans`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`plans` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `price` VARCHAR(255) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `db-techos`.`companies`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`companies` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `cnpj` VARCHAR(30) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `owner_id` INT NOT NULL,
  `plan_id` INT NOT NULL,
  `creation_time` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_companies_owner`
    FOREIGN KEY (`owner_id`)
    REFERENCES `db-techos`.`users` (`id`),
  CONSTRAINT `fk_companies_plan`
    FOREIGN KEY (`plan_id`)
    REFERENCES `db-techos`.`plans` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 9
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `db-techos`.`employees`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`employees` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `company_id` INT NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_employees_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `db-techos`.`users` (`id`),
  CONSTRAINT `fk_employees_company`
    FOREIGN KEY (`company_id`)
    REFERENCES `db-techos`.`companies` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 51
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `db-techos`.`service_orders`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`service_orders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `device_id` INT NOT NULL,
  `company_id` INT NOT NULL,
  `defect` VARCHAR(150) BINARY NOT NULL,
  `status` ENUM('aberta', 'aguardando_peca', 'em_andamento', 'cancelada', 'concluida') NOT NULL DEFAULT 'aberta',
  `price` DOUBLE NOT NULL,
  `photo` VARCHAR(255) NULL DEFAULT NULL,
  `creation_time` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  INDEX `fk_users_service_order_user` (`user_id` ASC) VISIBLE,
  INDEX `fk_devices_service_order_device` (`device_id` ASC) VISIBLE,
  CONSTRAINT `service_orders_user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `db-techos`.`users` (`id`),
  CONSTRAINT `service_orders_device_id`
    FOREIGN KEY (`device_id`)
    REFERENCES `db-techos`.`devices` (`id`),
  CONSTRAINT `service_orders_company_id`
    FOREIGN KEY (`company_id`)
    REFERENCES `db-techos`.`companies` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;



-- -----------------------------------------------------
-- Table `db-techos`.`user_types`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`user_types` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8mb3;



-- -----------------------------------------------------
-- Table `db-techos`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `typeId` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `photo` VARCHAR(255) NULL DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  INDEX `fk_users_user_types_idx` (`typeId` ASC) VISIBLE,
  CONSTRAINT `fk_users_user_types`
    FOREIGN KEY (`typeId`)
    REFERENCES `db-techos`.`user_types` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8mb3;



-- -----------------------------------------------------
-- Table `db-techos`.`devices_categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`devices_categories` (
  `id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;



-- -----------------------------------------------------
-- Table `db-techos`.`devices`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`devices` (
  `id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `serial_number` BIGINT NOT NULL,
  `model` VARCHAR(80) NOT NULL,
  `brand` VARCHAR(50) NOT NULL,
  `creation_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  INDEX `devices_category_id_idx` (`category_id` ASC) VISIBLE,
  INDEX `fk_devices_users1_idx` (`user_id` ASC) VISIBLE,
  CONSTRAINT `devices_category_id`
    FOREIGN KEY (`category_id`)
    REFERENCES `db-techos`.`devices_categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_devices_user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `db-techos`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
