# Livro Caixa 

Livro Caixa é um sistema simples baseado em PHP/MySQL para o controle mensal de seu caixa.

Teve como idéia inicial o projeto Livro-Caixa de Felipe Ismael Barth (fibbarth)
https://github.com/fibbarth

Algumas melhorias:
* Inclusão da Bootstrap
* Inclusão do phpSecurePages
* Autenticação no banco com hashing e crypto na senha
* Algumas modificações para facilitar o uso mobile
* Modelagem do banco
* O arquivo de modelagem e banco estão na pasta <Instalar>.
* Utilização da biblioteca PHPMailer para a recuperação de senha
* Visão dos lançamentos através do FullCalendar.
* Retirada do elemento "table" em algumas entradas de dados.
* Retirada de cores em excesso.
* Inclusão do PDV
* Envio de e-mail para recuperar senha.
* Login via biblioteca Facebook-SDK, como exemplo.
* Acréscimo do módulo API.php para retorno via JSON das tabelas: usuarios, categorias e movimentos.
* Início da APP / Flutter com uso da API.

## PDV

Uso: seu.domino/pdv.php

## Recover

Uso: seu.dominio/recover.php


﻿Seguem instruções de instalação e configuração
----------------------------------------------

O arquivo para criação do banco de dados é o livro_caixa.sql.

O arquivo com a modelagm está na pasta Instalar

Rode o COMPOSER, a partir da raiz da aplicação, para instalar as dependências:

# $ ./composer.phar install

ou

# $ composer install

Caso tenha o COMPOSER instalado.


Após importar o banco para o MySQl.

## Configuração do sistema
#### Arquivo .ENV

Acesse a pasta raiz do projeto, edite o arquivo '.env' e altere as configurações conforme o seu ambiente.

#### Observação: 

1. As outras bibliotecas estão integradas com a configuração do sistema.
2. Os ambientes para a diretiva APP_ENV, do arquivo .env são:
   1. development
   2. testing
   3. production

---------------------------------------

Dados de acesso

Email: admin@admin.com

Senha: 123456

---------------------------------------

### Frameworks/Bibliotecas
* [twbs/bootstrap](https://github.com/twbs/bootstrap) 
* [jquery/jquery](https://github.com/jquery/jquery) 
* [jquery/jquery-ui](https://github.com/jquery/jquery-ui) 
* [phpSecurePages](http://www.phpsecurepages.com/) 
* [FullCalendar](http://fullcalendar.io) 
* [Facebook](https://developers.facebook.com) 

### Requerimento
* PHP >= 5.6.0
* MySQL
* Atualmente rodando no PHP 7.4.

### Tutoriais
#### Facebook
* [Acessando API Facebook](https://imasters.com.br/back-end/acessando-api-facebook-em-php-com-o-php-graph-sdk)

### Créditos
* Felipe Barth fibbarth@gmail.com
* Augusto Cezar Perez augustoperez696@live.com
* Alexandre LLemes - alexandre.llemes@gmail.com

