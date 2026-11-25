-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema adminlte
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema cadastro
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema estoque_laravel
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema livro_caixa
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema livro_caixa
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `livro_caixa` DEFAULT CHARACTER SET latin1 ;
USE `livro_caixa` ;

-- -----------------------------------------------------
-- Table `livro_caixa`.`categorias`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `livro_caixa`.`categorias` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `livro_caixa`.`movimentos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `livro_caixa`.`movimentos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tipo` INT(11) NULL DEFAULT NULL,
  `dia` INT(11) NULL DEFAULT NULL,
  `mes` INT(11) NULL DEFAULT NULL,
  `ano` INT(11) NULL DEFAULT NULL,
  `categoria_id` INT(11) NULL DEFAULT NULL,
  `descricao` LONGTEXT NULL DEFAULT NULL,
  `valor` FLOAT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_categorias_movimentos_idx` (`categoria_id` ASC),
  CONSTRAINT `fk_categorias_movimentos`
    FOREIGN KEY (`categoria_id`)
    REFERENCES `livro_caixa`.`categorias` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `livro_caixa`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `livro_caixa`.`users` (
  `id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user` VARCHAR(50) NOT NULL,
  `password` VARCHAR(128) NOT NULL,
  `userlevel` TINYINT(3) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `user` (`user` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

USE `livro_caixa` ;

insert into livro_caixa.users values(1,'admin@admin.com', '$2y$11$e10adc3949ba59abbe56eueIfJ85lE0.kOdAt9Jf.bTw0pNAb2DGG', 1);

-- -----------------------------------------------------
-- Placeholder table for view `livro_caixa`.`view_categorias`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `livro_caixa`.`view_categorias` (`id` INT, `nome` INT);

-- -----------------------------------------------------
-- Placeholder table for view `livro_caixa`.`view_movimentos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `livro_caixa`.`view_movimentos` (`id` INT, `tipo` INT, `dia` INT, `mes` INT, `ano` INT, `categoria_id` INT, `descricao` INT, `valor` INT);

-- -----------------------------------------------------
-- Placeholder table for view `livro_caixa`.`view_users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `livro_caixa`.`view_users` (`id` INT, `user` INT, `password` INT, `userlevel` INT);

-- -----------------------------------------------------
-- View `livro_caixa`.`view_categorias`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `livro_caixa`.`view_categorias`;
USE `livro_caixa`;
CREATE  OR REPLACE VIEW `view_categorias` AS
select * from categorias;

-- -----------------------------------------------------
-- View `livro_caixa`.`view_movimentos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `livro_caixa`.`view_movimentos`;
USE `livro_caixa`;
CREATE  OR REPLACE VIEW `view_movimentos` AS
select * from movimentos;

-- -----------------------------------------------------
-- View `livro_caixa`.`view_users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `livro_caixa`.`view_users`;
USE `livro_caixa`;
CREATE  OR REPLACE VIEW `view_users` AS
select * from users;

CREATE USER 'livrocaixa' IDENTIFIED BY '123456';

GRANT ALL ON `mydb`.* TO 'livrocaixa';

GRANT ALL ON `livro_caixa`.* TO 'livrocaixa';

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
