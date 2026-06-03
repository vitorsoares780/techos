-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema db-techos
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema db-techos
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `db-techos` DEFAULT CHARACTER SET utf8mb3 ;
USE `db-techos` ;

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
-- Table `db-techos`.`service_orders`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`service_orders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `device_id` INT NOT NULL,
  `defect` VARCHAR(150) NOT NULL,
  `diagnosis` VARCHAR(150) NOT NULL,
  `status` ENUM('aberta', 'aguardando_peca', 'cancelada', 'concluida', 'em_andamento') NOT NULL DEFAULT 'aberta',
  `price` DOUBLE NULL DEFAULT NULL,
  `photo` VARCHAR(255) NULL DEFAULT NULL,
  `creation_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `db-techos`.`users_types`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`users_types` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `db-techos`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `type_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `photo` VARCHAR(255) NULL DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  INDEX `fk_users_user_types_idx` (`type_id` ASC) VISIBLE,
  CONSTRAINT `fk_users_user_types`
    FOREIGN KEY (`type_id`)
    REFERENCES `db-techos`.`users_types` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8mb3;


-- -----------------------------------------------------
-- Table `db-techos`.`devices_categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`devices_categories` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db-techos`.`devices`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`devices` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `category_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `serial_number` BIGINT NOT NULL,
  `model` VARCHAR(80) NOT NULL,
  `brand` VARCHAR(50) NOT NULL,
  `creation_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` TINYINT(1) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `device_owner_idx` (`user_id` ASC) VISIBLE,
  INDEX `device_category_device_idx` (`category_id` ASC) VISIBLE,
  CONSTRAINT `device_category_device`
    FOREIGN KEY (`category_id`)
    REFERENCES `db-techos`.`devices_categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `device_owner`
    FOREIGN KEY (`user_id`)
    REFERENCES `db-techos`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db-techos`.`plans`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`plans` (
  `id` INT NOT NULL,
  `name` ENUM('starter', 'profissional', 'empresa') NOT NULL DEFAULT 'starter',
  `price` DOUBLE NOT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `db-techos`.`companies`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `db-techos`.`companies` (
  `id` INT NOT NULL,
  `ceo_id` INT NOT NULL,
  `plan_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `cnpj` VARCHAR(30) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `email` VARCHAR(150) NULL,
  `address` VARCHAR(200) NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC) VISIBLE,
  UNIQUE INDEX `cnpj_UNIQUE` (`cnpj` ASC) VISIBLE,
  INDEX `company_owner_idx` (`ceo_id` ASC) VISIBLE,
  INDEX `company_plan_idx` (`plan_id` ASC) VISIBLE,
  CONSTRAINT `company_owner`
    FOREIGN KEY (`ceo_id`)
    REFERENCES `db-techos`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `company_plan`
    FOREIGN KEY (`plan_id`)
    REFERENCES `db-techos`.`plans` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
