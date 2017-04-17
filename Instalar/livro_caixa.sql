CREATE DATABASE IF NOT EXISTS livro_caixa;

use livro_caixa;

CREATE TABLE IF NOT EXISTS lc_cat (
  id int(11) NOT NULL AUTO_INCREMENT,
  nome varchar(255) DEFAULT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS lc_movimento (
  id int(11) NOT NULL AUTO_INCREMENT,
  tipo int(11) DEFAULT NULL,
  dia int(11) DEFAULT NULL,
  mes int(11) DEFAULT NULL,
  ano int(11) DEFAULT NULL,
  cat int(11) DEFAULT NULL,
  descricao longtext,
  valor float DEFAULT NULL,
  PRIMARY KEY (id)
);

