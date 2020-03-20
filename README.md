# Livro Caixa 

Livro Caixa é um sistema simples baseado em PHP/MySQL para o controle mensal de seu caixa.

Teve como idéia inicial o projeto Livro-Caixa de Felipe Ismael Barth (fibbarth)
https://github.com/fibbarth

Algumas melhorias:
* Inclusão da Bootstrap
* Inclusão do phpSecurePages
* Autenticação no banco com md5 na senha
* Algumas modificações para facilitar o uso mobile
* Modelagem do banco
* O arquivo de modelagem e banco estão na pasta <Instalar>.
* Utilização da biblioteca PHPMailer para a recuperação de senha
* Visão dos lançamentos através do FullCalendar.
* Retirada do elemento "table" em algumas entradas de dados.
* Retirada de cores em excesso.
* Inclusão do PDV
* Envio de e-mail para recuperar senha.

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

Acesse a pasta raiz do projeto, edite o arquivo 'config.php' e altere as configurações conforme o seu ambiente:

### Configuração do Banco de dados

$host = "localhost";

$user = "livrocaixa";

$pass = "123456";

$d_b = "livro_caixa";

### Título do seu livro Caixa, geralmente seu nome

$lc_titulo="Título de sua aplicação";


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

### Requerimento
* PHP >= 5.6.0
* MySQL
* Atualmente rodando no PHP 7.4.

### Créditos
* Felipe Barth fibbarth@gmail.com
* Augusto Cezar Perez augustoperez696@live.com
* Alexandre LLemes - alexandre.llemes@gmail.com

