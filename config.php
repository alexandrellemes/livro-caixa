<?php

//Configuração do Banco de dados
$host = "mysqldb";
$user = "root";
$pass = "123456";
$d_b = "livro_caixa";

//Título do seu livro Caixa, geralmente seu nome
$lc_titulo="Seu Nome";

//////////////////////////////////////
//Não altere a partir daqui!
//////////////////////////////////////

$conn = mysqli_connect($host, $user, $pass, $d_b) or die("Erro na conexão com a base de dados");
$db = mysqli_select_db($conn, $d_b) or die("Erro na seleção da base de dados");

?>
