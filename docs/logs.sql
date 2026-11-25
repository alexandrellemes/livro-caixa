-- -----------------------------------------------------
-- Tabela `livro_caixa`.`logs`
-- Registros de auditoria/erros da aplicação.
-- Obs.: a aplicação cria esta tabela automaticamente (App\Models\LogRepository::ensureTable),
-- este script serve para criação manual ou revisão pelo DBA.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `livro_caixa`.`logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nivel` VARCHAR(10) NOT NULL,
  `acao` VARCHAR(60) NOT NULL,
  `mensagem` VARCHAR(500) NOT NULL,
  `entidade` VARCHAR(40) NULL DEFAULT NULL,
  `entidade_id` VARCHAR(40) NULL DEFAULT NULL,
  `contexto` LONGTEXT NULL DEFAULT NULL,
  `usuario` VARCHAR(100) NULL DEFAULT NULL,
  `ip` VARCHAR(45) NULL DEFAULT NULL,
  `metodo` VARCHAR(10) NULL DEFAULT NULL,
  `uri` VARCHAR(500) NULL DEFAULT NULL,
  `user_agent` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_logs_created_at` (`created_at`),
  INDEX `idx_logs_nivel` (`nivel`),
  INDEX `idx_logs_acao` (`acao`),
  INDEX `idx_logs_usuario` (`usuario`),
  INDEX `idx_logs_entidade` (`entidade`, `entidade_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4;
