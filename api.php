<?php

/**
 * API para uso com o APP / Mobile.
 */

require_once (__DIR__ . '/functions.php');
require_once (__DIR__ . '/config.php');

$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
$sql = "CREATE DATABASE IF NOT EXISTS livro_caixa";
if (!$conn->query($sql) === TRUE) {
    echo "Erro ao criar banco de dados: " . $conn->error;
}
$sql = <<<EOF
CREATE TABLE IF NOT EXISTS livro_caixa.categorias 
(
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    nome VARCHAR(255) NOT NULL
)
EOF;

if ($conn->query($sql) === FALSE) {
    echo "Erro ao criar tabela: " . $conn->error;
}

$metodoHttp = $_SERVER['REQUEST_METHOD'];
if ($metodoHttp == 'POST') {
	$stmt = $conn->prepare(
		"INSERT INTO livro_caixa.categorias (nome) VALUES (?)");
    $json = json_decode(file_get_contents('php://input'));
    $nome     = $json->{'nome'};
    $stmt->bind_param("sss", $nome, $endereco, $contato);
    $stmt->execute();
    $stmt->close();
    $id = $conn->insert_id;
    $jsonRetorno = array("id"=>$id);
    echo json_encode($jsonRetorno);

} else if ($metodoHttp == 'GET') {
    $segments = explode("/", $_SERVER["REQUEST_URI"]);
    $chave = $segments[count($segments)-1];
    $tabela = $segments[count($segments)-2];

    // Chamada inicial da api.
    if ($chave == '' 
        || $chave == 'api') {
        echo json_encode(array(
            'Sintaxe: ',
            'seu.site/api/?',
            '/categorias/?',
            '/usuarios/?',
            '/movimentos/?'
          )
        );
        die;

    }

    $jsonArray = array();

    if ($tabela == 'api') {
        $tabela = $chave;
        $chave = null;
    }

    switch($tabela) {

        case 'categorias':
            $tabela = 'categorias';
            break;

        case 'usuarios':
            $tabela = 'users';
            break;
    
        case 'movimentos':
            $tabela = 'movimentos';
            break;
    
    }

    if(is_numeric($chave) == null)//verifica se foi passado um id
        $sql = "SELECT * FROM livro_caixa." . $tabela;
    else
        $sql = "SELECT * FROM livro_caixa." . $tabela . " WHERE id = $chave";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { 
            
            foreach (array_keys($row) as $campo) {
                $jsonLinha[$campo] = $row[$campo];
            };
            $jsonArray[] = $jsonLinha;    	    
        }
    }
    echo json_encode($jsonArray);

} else if ($metodoHttp == 'PUT') {
    $stmt = $conn->prepare(
        "UPDATE livro_caixa.categorias SET nome=? WHERE id = ?");
    $json  = json_decode(file_get_contents('php://input'));

    $id       = $json->{'id'};
    $nome     = $json->{'nome'};

    $stmt->bind_param("sssi", $nome, $id);
    $stmt->execute();
    $stmt->close();
    $jsonRetorno = array("id" => $id);
    echo json_encode($jsonRetorno);

} else if ($metodoHttp == 'DELETE') {
    $stmt = $conn->prepare("DELETE FROM livro_caixa.categorias WHERE id = ?");
    $segments = explode("/", $_SERVER["REQUEST_URI"]);
    $id = $segments[count($segments)-1];
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $jsonRetorno = array("id" => $id);
    echo json_encode($jsonRetorno);
}

$conn->close();

//para o put 
/*{
    "id": 1,
    "nome": "Categoria exemplo 1"
}*/
?>
