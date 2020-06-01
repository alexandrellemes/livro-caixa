<?php

ini_set('display_errors', true);
error_reporting(E_ALL);

//session_start();
set_time_limit(0);

define('DS', DIRECTORY_SEPARATOR);

require_once ('functions.php');
require_once ('config.php');

// Gera a senha inicial : 123456
//$salt = md5('123456');
//echo generateHash('123456', $salt) . PHP_EOL;
//die('<br>Senha inicial.');

/** Configuracao para o phpSecurePages **/
$cfgProgDir =  'phpSecurePages' . DS;
include($cfgProgDir . "secure.php");

if (isset($_GET['sair'])) {
	$logout = true;
	include(__DIR__ . DS . 'phpSecurePages' . DS . 'objects' . DS . 'logout.php');
	header('Location: ' . DS);
}


if (isset($_GET['acao'])
    && $_GET['acao'] == 'apagar') {
    $id = $_GET['id'];

    mysqli_query($conn, "DELETE FROM movimentos WHERE id='$id'");
    echo mysqli_error($conn);

    header("Location: ?mes=" . $_GET['mes'] . "&ano=" . $_GET['ano'] . "&ok=2");
    exit();
}

if (isset($_POST['acao']) && $_POST['acao'] == 'editar_cat') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];

    mysqli_query($conn, "UPDATE categorias SET nome='$nome' WHERE id='$id'");
    echo mysqli_error($conn);

    header("Location: ?mes=" . $_GET['mes'] . "&ano=" . $_GET['ano'] . "&cat_ok=3");
    exit();
}

if (isset($_GET['acao']) && $_GET['acao'] == 'apagar_cat') {
    $id = $_GET['id'];

    $qr=mysqli_query($conn, "SELECT c.id FROM movimentos m, categorias c WHERE c.id=m.cat && c.id=$id");
    if (mysqli_num_rows($qr)>0){
        header("Location: ?mes=" . $_GET['mes'] . "&ano=" . $_GET['ano'] . "&cat_err=1");
        exit();
    }

    $sql = "DELETE FROM categorias WHERE id='$id'";
    
    mysqli_query($conn, $sql);
    echo mysqli_error($conn);

    header("Location: ?mes=" . $_GET['mes'] . "&ano=" . $_GET['ano'] . "&cat_ok=2");
    exit();
}

if (isset($_POST['acao']) && $_POST['acao'] == 'editar_mov') {
    $id = $_POST['id'];
    $dia = $_POST['dia'];
    $tipo = $_POST['tipo'];
    $cat = $_POST['categoria'];
    $descricao = $_POST['descricao'];
    $valor = str_replace(",", ".", $_POST['valor']);

    $sql = "UPDATE movimentos 
               SET dia='$dia', tipo='$tipo', categoria_id='$cat', descricao='$descricao', valor='$valor' 
            WHERE id='$id'";
    
    mysqli_query($conn, $sql);
    echo mysqli_error($conn);

    header("Location: ?mes=" . $_GET['mes'] . "&ano=" . $_GET['ano'] . "&ok=3");
    exit();
}

/**
 * Adiciona categorias
 */
if (isset($_POST['acao']) && $_POST['acao'] == 2) {

    $nome = $_POST['nome'];

    mysqli_query($conn, "INSERT INTO categorias (nome) values ('$nome')");

    echo mysqli_error($conn);

    header("Location: ?mes=" . $_GET['mes'] . "&ano=" . $_GET['ano'] . "&cat_ok=1");
    exit();
}

/**
 * Adiciona movimentos
 */
if (isset($_POST['acao']) && $_POST['acao'] == 1) {

    $data = $_POST['data'];
    $tipo = $_POST['tipo'];
    $categoria = $_POST['categoria'];
    $descricao = $_POST['descricao'];

    // Retira a formatação do valor para gravar no banco.
    $valor = str_replace(".", "", $_POST['valor']);
    $valor = str_replace(",", ".", $valor);

    $t = explode("/", $data);
    $dia = $t[0];
    $mes = $t[1];
    $ano = $t[2];

    $sql = "INSERT INTO movimentos (dia,mes,ano,tipo,descricao,valor,categoria_id)
            values ('$dia','$mes','$ano','$tipo','$descricao','$valor','$categoria')";

    mysqli_query($conn, $sql);

    echo mysqli_error($conn);

    header("Location: ?mes=" . $_GET['mes'] . "&ano=" . $_GET['ano'] . "&ok=1");
    exit();
}

if (isset($_GET['mes']))
    $mes_hoje = $_GET['mes'];
else
    $mes_hoje = date('m');

if (isset($_GET['ano']))
    $ano_hoje = $_GET['ano'];
else
    $ano_hoje = date('Y');


// Cálculos do RESULTADO.
$qr=mysqli_query($conn, "SELECT SUM(valor) as total FROM movimentos WHERE tipo=1 && mes='$mes_hoje' && ano='$ano_hoje'");
$row=mysqli_fetch_array($qr);
$entradas=$row['total'];

$qr=mysqli_query($conn, "SELECT SUM(valor) as total FROM movimentos WHERE tipo=0 && mes='$mes_hoje' && ano='$ano_hoje'");
$row=mysqli_fetch_array($qr);
$saidas=$row['total'];

$resultado_mes=$entradas - $saidas;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title id='titulo'>Livro caixa <?php echo APP_NAME; ?></title>
    <meta name="LANGUAGE" content="Portuguese" />
    <meta name="AUDIENCE" content="all" />
    <meta name="RATING" content="GENERAL" />

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">

    <link href="css/normalize.css" rel="stylesheet" type="text/css" />

    <!-- Full Calendar   -->
    <link rel="stylesheet" href="css/fullcalendar.css" type="text/css" />

</head>
<body>

<div class="container bs-docs-container">

    <div class="form-group row">
        <label class="control-label col-md-4">
            <h2 style="margin:5px">Livro Caixa - <?php echo APP_NAME; ?></h2>
        </label>

        <label class="control-label col-md-8 text-right">
            <a  href="?mes=<?php echo date('m')?>&ano=<?php echo date('Y')?>">Hoje:<strong> <?php echo date('d')?> de <?php echo mostraMes(date('m'))?> de <?php echo date('Y')?></strong></a>
        </label>

    </div>

    <hr>
    <div class="form-group row">
        <label class="control-label col-md-2">
            <select onchange="location.replace('?mes=<?php echo $mes_hoje?>&ano='+this.value)">
                <?php
                $anosAntes = date('Y') - 5;
                $anosDepois = date('Y') + 5;
                for ($i = $anosAntes; $i<= $anosDepois; $i++) { ?>
                    <option value="<?php echo $i?>" <?php if ($i==$ano_hoje) echo "selected=selected"?> ><?php echo $i?></option>
                <?php } ?>
            </select>
        </label>

        <label class="control-label col-md-10 text-justify">
            <?php
            for ($i=1; $i <= 12; $i++) {
                ?>
                <span align="center" style=" padding-right:5px; padding-left: 5px;">
                    <a href="?mes=<?php echo $i?>&ano=<?php echo $ano_hoje?>" style="
                    <?php if($mes_hoje == $i) { ?>
                        font-size:20px; font-weight:bold; color: lightblue;
                    <?php }?>
                            ">
                    <?php echo mostraMes($i);?>
                    </a>
                </span>
                <?php
            }
            ?>
        </label>

    </div>

    <hr>

    <div>
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#AbaMovimento" id="btnAbaMovimento" aria-controls="AbaMovimento" role="tab" data-toggle="tab">Movimento</a>
            </li>
            <li role="presentation">
                <a href="#AbaCategorias" id="btnAbaCategorias" aria-controls="AbaCategorias" role="tab" data-toggle="tab">Categorias</a>
            </li>
            <li role="presentation">
                <a href="#AbaResultado" id="btnAbaResultado" aria-controls="AbaResultado" role="tab" data-toggle="tab">Resultado</a>
            </li>
            <li role="presentation">
                <a href="#AbaCalendario" id="btnAbaCalendario" aria-controls="AbaCalendario" role="tab" data-toggle="tab">Calendário</a>
            </li>
        </ul>
    </div>

    <!-- Tab panes -->
    <div class="tab-content">

        <div class="tab-pane active" id="AbaMovimento">
            <br>
            <h2><?php echo mostraMes($mes_hoje)?>/<?php echo $ano_hoje?></h2>
            <br>

            <div class="table-responsive">
                <table class="table" cellpadding="10" cellspacing="0" width="auto" align="center">
                    <tr>
                        <td align="right">
                            <a href="javascript:;" onclick="abreFecha('add_cat')" class="bnt">[+] Adicionar Categoria</a>
                            <a href="javascript:;" onclick="abreFecha('add_movimento')" class="bnt"><strong>[+] Adicionar
                                    Movimento</strong></a>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="3" >

                            <?php if (isset($_GET['cat_err']) && $_GET['cat_err']==1) { ?>

                                <div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
                                    <strong>Esta categoria não pode ser removida, pois há movimentos associados a esta</strong>
                                </div>

                            <?php } ?>

                            <?php if (isset($_GET['cat_ok']) && $_GET['cat_ok']==2) { ?>

                                <div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
                                    <strong>Categoria removida com sucesso!</strong>
                                </div>

                            <?php } ?>

                            <?php if (isset($_GET['cat_ok']) && $_GET['cat_ok']==1) { ?>

                                <div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
                                    <strong>Categoria Cadastrada com sucesso!</strong>
                                </div>

                            <?php } ?>

                            <?php if (isset($_GET['cat_ok']) && $_GET['cat_ok']==3) { ?>

                                <div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
                                    <strong>Categoria alterada com sucesso!</strong>
                                </div>

                            <?php } ?>

                            <?php if (isset($_GET['ok']) && $_GET['ok']==1) { ?>

                                <div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
                                    <strong>Movimento Cadastrado com sucesso!</strong>
                                </div>

                            <?php } ?>

                            <?php if (isset($_GET['ok']) && $_GET['ok']==2) { ?>

                                <div style="padding:5px; background-color:#900; text-align:center; color:#FFF">
                                    <strong>Movimento removido com sucesso!</strong>
                                </div>

                            <?php } ?>

                            <?php if (isset($_GET['ok']) && $_GET['ok']==3) { ?>

                                <div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
                                    <strong>Movimento alterado com sucesso!</strong>
                                </div>

                            <?php } ?>

                            <div style=" background-color:#F1F1F1; padding:10px; border:1px solid #999; margin:5px; display:none" id="add_cat">
                                <h3>Adicionar Categoria</h3>
                                <div class="table-responsive">
                                    <table class="table" width="100%">
                                        <tr>
                                            <td valign="top">

                                                <form method="post" action="?mes=<?php echo $mes_hoje?>&ano=<?php echo $ano_hoje?>">
                                                    <input type="hidden" name="acao" value="2" />

                                                    Nome: <input type="text" name="nome" size="20" maxlength="50" />

                                                    <br />
                                                    <br />

                                                    <input type="submit" class="input" value="Enviar" />
                                                </form>

                                            </td>
                                            <td valign="top" align="right">
                                                <b>Editar/Remover Categorias:
                                                </b>
                                                <br/>
                                                <br/>
                                                <?php
                                                $qr = mysqli_query($conn, "SELECT id, nome FROM categorias");
                                                while ($row = mysqli_fetch_array($qr)) {
                                                    ?>
                                                    <div id="editar2_cat_<?php echo $row['id']?>">
                                                        <?php echo $row['nome']?>

                                                        <a style="font-size:10px; color:#666" onclick="return confirm('Tem certeza que deseja remover esta categoria?\nAtenção: Apenas categorias sem movimentos associados poderão ser removidas.')" href="?mes=<?php echo $mes_hoje?>&ano=<?php echo $ano_hoje?>&acao=apagar_cat&id=<?php echo $row['id']?>" title="Remover">[remover]</a>
                                                        <a href="javascript:;" style="font-size:10px; color:#666" onclick="document.getElementById('editar_cat_<?php echo $row['id']?>').style.display=''; document.getElementById('editar2_cat_<?php echo $row['id']?>').style.display='none'" title="Editar">[editar]</a>

                                                    </div>
                                                    <div style="display:none" id="editar_cat_<?php echo $row['id']?>">

                                                        <form method="post" action="?mes=<?php echo $mes_hoje?>&ano=<?php echo $ano_hoje?>">
                                                            <input type="hidden" name="acao" value="editar_cat" />
                                                            <input type="hidden" name="id" value="<?php echo $row['id']?>" />
                                                            <input type="text" name="nome" value="<?php echo $row['nome']?>" size="20" maxlength="50" />
                                                            <input type="submit" class="input" value="Alterar" />
                                                        </form>
                                                    </div>

                                                <?php } ?>

                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div style=" background-color:#F1F1F1; padding:10px; border:1px solid #999; margin:5px; display:none" id="add_movimento">
                                <h3>Adicionar Movimento</h3>
                                <?php
                                $qr=mysqli_query($conn, "SELECT * FROM categorias");
                                if (mysqli_num_rows($qr)==0)
                                    echo "Adicione ao menos uma categoria";

                                else { ?>
                                    <form method="post" action="?mes=<?php echo $mes_hoje?>&ano=<?php echo $ano_hoje?>">
                                        <input type="hidden" name="acao" value="1" />
                                        <strong>Data:</strong><br />
                                        <input type="text" name="data" size="11" maxlength="10" value="<?php echo date('d')?>/<?php echo $mes_hoje?>/<?php echo $ano_hoje?>" />

                                        <br />
                                        <br />

                                        <strong>Tipo:<br /></strong>
                                        <label for="tipo_receita" style="color:#030"><input type="radio" name="tipo" value="1" id="tipo_receita" /> Receita</label>&nbsp;
                                        <label for="tipo_despesa" style="color:#C00"><input type="radio" name="tipo" value="0" id="tipo_despesa" /> Despesa</label>

                                        <br />
                                        <br />

                                        <strong>Categoria:</strong>
                                        <br />
                                        <select name="categoria">
                                            <?php while ($row = mysqli_fetch_array($qr)) { ?>
                                                <option value="<?php echo $row['id']?>"><?php echo $row['nome']?></option>
                                            <?php } ?>
                                        </select>

                                        <br />
                                        <br />

                                        <strong>Descrição:</strong>
                                        <br />
                                        <input type="text" name="descricao" size="100" maxlength="255" />

                                        <br />
                                        <br />

                                        <strong>Valor:</strong>
                                        <br />
                                        <input type="text" name="valor" id="valor" size="8" maxlength="10" data-symbol="R$ " data-thousands="." data-decimal="," />
                                        <br />
                                        <br />

                                        <input type="submit" class="input" value="Enviar" />

                                    </form>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <br>
                <table class="table" cellpadding="5" cellspacing="0" width="auto" align="center">
                    <tr>
                        <td colspan="2">

                            <h2>Movimentos deste Mês</h2>

                            <div style="float:right; text-align:right">
                                <form name="form_filtro_cat" method="get" action="">
                                    <input type="hidden" name="mes" value="<?php echo $mes_hoje ?>">
                                    <input type="hidden" name="ano" value="<?php echo $ano_hoje ?>">
                                    Filtrar por categoria:
                                    <select name="filtro_cat" onchange="form_filtro_cat.submit()">
                                        <option value="">Todas</option>
                                        <?php
                                        $qr = mysqli_query($conn, "SELECT * FROM categorias");
                                        while ($row = mysqli_fetch_array($qr)) {
                                            ?>

                                            <option value="<?php echo $row['id'] ?>"><?php echo $row['nome'] ?></option>
                                        <?php } ?>
                                        <?php
                                        $qr = mysqli_query($conn, "SELECT DISTINCT c.id, c.nome FROM categorias c, movimentos m WHERE m.cat=c.id && m.mes='$mes_hoje' && m.ano='$ano_hoje'");
                                        while ($qr && $row = mysqli_fetch_array($qr, MYSQLI_NUM)) {
                                            ?>
                                            <option <?php if (isset($_GET['filtro_cat']) && $_GET['filtro_cat'] == $row['id']) echo "selected=selected" ?>
                                                    value="<?php echo $row['id'] ?>"><?php echo $row['nome'] ?></option>
                                        <?php } ?>
                                    </select>
                                    <input type="submit" value="Filtrar" class="botao"/>
                                </form>
                            </div>

                        </td>
                    </tr>
                    <?php
                    $pas = 0;
                    $filtros = "";
                    if (isset($_GET['filtro_cat'])) {
                        if ($_GET['filtro_cat'] != '') {
                            $filtros = "&& cat='" . $_GET['filtro_cat'] . "'";
                            $filtros2 = $_GET['filtro_cat'];

                            $qr = mysqli_query($conn, "SELECT SUM(valor) as total FROM movimentos WHERE tipo=1 && mes='$mes_hoje' && ano='$ano_hoje' && categoria_id = '$filtros2'");
                            $row = mysqli_fetch_array($qr);
                            $entradas = $row['total'];

                            $qr = mysqli_query($conn, "SELECT SUM(valor) as total FROM movimentos WHERE tipo=0 && mes='$mes_hoje' && ano='$ano_hoje' && categoria_id ='$filtros2'");
                            $row = mysqli_fetch_array($qr);
                            $saidas = $row['total'];

                            $resultado_mes = $entradas - $saidas;
                            $qr = mysqli_query($conn, "SELECT * FROM movimentos WHERE mes='$mes_hoje' && ano='$ano_hoje' && categoria_id ='$filtros2' ORDER By dia");
                            $pas = 1;

                        }
                    }
                    if ($pas == 0) {
                        $qr = mysqli_query($conn, "SELECT * FROM movimentos WHERE mes='$mes_hoje' && ano='$ano_hoje' $filtros ORDER By dia");
                    }
                    $cont = 0;
                    while ($row = mysqli_fetch_array($qr)) {
                        $cont++;

                        $cat = $row['categoria_id'];
                        $qr2 = mysqli_query($conn, "SELECT nome FROM categorias WHERE id='$cat'");
                        $row2 = mysqli_fetch_array($qr2);
                        $categoria = $row2['nome'];

                        ?>
                        <tr style="background-color:<?php if ($cont % 2 == 0) echo "#F1F1F1"; else echo "#E0E0E0" ?>">
                            <td align="center" width="15"><?php echo $row['dia'] ?></td>
                            <td><?php echo $row['descricao'] ?> <em>(<a
                                            href="?mes=<?php echo $mes_hoje ?>&ano=<?php echo $ano_hoje ?>&filtro_cat=<?php echo $cat ?>"><?php echo $categoria ?></a>)</em>
                                <a href="javascript:;" style="font-size:10px; color:#666"
                                   onclick="document.getElementById('editar_mov_<?php echo $row['id'] ?>').style.display='';  "
                                   title="Editar">[editar]</a></td>
                            <td align="right"><strong
                                        style="color:<?php if ($row['tipo'] == 0) echo "#C00"; else echo "#030" ?>"><?php if ($row['tipo'] == 0) echo "-"; else echo "+" ?><?php echo formata_dinheiro($row['valor']) ?></strong>
                            </td>
                        </tr>
                        <tr style="display:none; background-color:<?php if ($cont % 2 == 0) echo "#F1F1F1"; else echo "#E0E0E0" ?>"
                            id="editar_mov_<?php echo $row['id'] ?>">
                            <td colspan="3">
                                <hr/>
                                <form method="post" action="?mes=<?php echo $mes_hoje ?>&ano=<?php echo $ano_hoje ?>">
                                    <input type="hidden" name="acao" value="editar_mov"/>
                                    <input type="hidden" name="id" value="<?php echo $row['id'] ?>"/>

                                    <b>Dia:</b>
                                    <input type="text" name="dia" size="3" maxlength="2" value="<?php echo $row['dia'] ?>"/>
                                    </br>
                                    <b>Tipo:</b>
                                    <label for="tipo_receita<?php echo $row['id'] ?>"
                                           style="color:#030">
                                        <input <?php if ($row['tipo'] == 1) echo "checked=checked" ?>
                                                type="radio" name="tipo" value="1" id="tipo_receita<?php echo $row['id'] ?>"/>
                                        Receita
                                    </label>&nbsp;
                                        <label for="tipo_despesa<?php echo $row['id'] ?>"
                                                                     style="color:#C00">
                                            <input <?php if ($row['tipo'] == 0) echo "checked=checked" ?>
                                                type="radio" name="tipo" value="0" id="tipo_despesa<?php echo $row['id'] ?>"/>
                                        Despesa</label>
                                    </br>
                                    <b>Categoria:</b>
                                    <select name="categoria">
                                        <?php
                                        $qr2 = mysqli_query($conn, "SELECT * FROM categorias");
                                        while ($row2 = mysqli_fetch_array($qr2)) {
                                            ?>
                                            <option <?php if ($row2['id'] == $row['categoria_id']) echo "selected" ?>
                                                    value="<?php echo $row2['id'] ?>"><?php echo $row2['nome'] ?></option>
                                        <?php } ?>
                                    </select>
                                    </br>
                                    <b>Valor:</b>
                                    <input type="text" value="<?php echo $row['valor'] ?>" name="valor" id="valor" size="8"
                                           maxlength="10" data-symbol="R$ " data-thousands="." data-decimal=","/>
                                    <br/>
                                    <b>Descricao:</b> <input type="text" name="descricao"
                                                             value="<?php echo $row['descricao'] ?>" size="70" maxlength="255"/>
                                    </br>
                                    <input type="submit" class="input" value="Alterar"/>
                                </form>
                                <div style="text-align: right">
                                    <a style="color:#FF0000" onclick="return confirm('Tem certeza que deseja apagar?')"
                                       href="?mes=<?php echo $mes_hoje ?>&ano=<?php echo $ano_hoje ?>&acao=apagar&id=<?php echo $row['id'] ?>"
                                       title="Remover">[remover]</a>
                                </div>
                                <hr/>
                            </td>
                        </tr>

                        <?php
                    }
                    ?>
                    <tr>
                        <td colspan="3" align="right">
                            <strong style="font-size:22px; color:<?php if ($resultado_mes < 0) echo "#C00"; else echo "#030" ?>"><?php echo formata_dinheiro($resultado_mes) ?></strong>
                        </td>
                    </tr>
                </table>
            </div>

        </div>

        <div class="tab-pane active" id="AbaCategorias">
            <br>

            <div class="table-responsive">
                <table class="table" cellpadding="10" cellspacing="0" width="auto" align="center">
                    <tr>
                        <td align="right">
                            <a href="javascript:;" onclick="abreFecha('add_cat')" class="bnt">[+] Adicionar Categoria</a>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="3" >

                            <?php if (isset($_GET['cat_err']) && $_GET['cat_err']==1) { ?>

                                <div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
                                    <strong>Esta categoria não pode ser removida, pois há movimentos associados a esta</strong>
                                </div>

                            <?php } ?>

                            <?php if (isset($_GET['cat_ok']) && $_GET['cat_ok']==2) { ?>

                                <div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
                                    <strong>Categoria removida com sucesso!</strong>
                                </div>

                            <?php } ?>

                            <?php if (isset($_GET['cat_ok']) && $_GET['cat_ok']==1) { ?>

                                <div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
                                    <strong>Categoria Cadastrada com sucesso!</strong>
                                </div>

                            <?php } ?>

                            <?php if (isset($_GET['cat_ok']) && $_GET['cat_ok']==3) { ?>

                                <div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
                                    <strong>Categoria alterada com sucesso!</strong>
                                </div>

                            <?php } ?>

                            <?php if (isset($_GET['ok']) && $_GET['ok']==1) { ?>

                                <div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
                                    <strong>Movimento Cadastrado com sucesso!</strong>
                                </div>

                            <?php } ?>

                            <?php if (isset($_GET['ok']) && $_GET['ok']==2) { ?>

                                <div style="padding:5px; background-color:#900; text-align:center; color:#FFF">
                                    <strong>Movimento removido com sucesso!</strong>
                                </div>

                            <?php } ?>

                            <?php if (isset($_GET['ok']) && $_GET['ok']==3) { ?>

                                <div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
                                    <strong>Movimento alterado com sucesso!</strong>
                                </div>

                            <?php } ?>

                            <div style=" background-color:#F1F1F1; padding:10px; border:1px solid #999; margin:5px; display:none" id="add_cat">
                                <h3>Adicionar Categoria</h3>
                                <div class="table-responsive">
                                    <table class="table" width="100%">
                                        <tr>
                                            <td valign="top">

                                                <form method="post" action="?mes=<?php echo $mes_hoje?>&ano=<?php echo $ano_hoje?>">
                                                    <input type="hidden" name="acao" value="2" />

                                                    Nome: <input type="text" name="nome" size="20" maxlength="50" />

                                                    <br />
                                                    <br />

                                                    <input type="submit" class="input" value="Enviar" />
                                                </form>

                                            </td>
                                            <td valign="top" align="right">
                                                <b>Editar/Remover Categorias:
                                                </b>
                                                <br/>
                                                <br/>
                                                <?php
                                                $qr = mysqli_query($conn, "SELECT id, nome FROM categorias");
                                                while ($row = mysqli_fetch_array($qr)) {
                                                    ?>
                                                    <div id="editar2_cat_<?php echo $row['id']?>">
                                                        <?php echo $row['nome']?>

                                                        <a style="font-size:10px; color:#666" onclick="return confirm('Tem certeza que deseja remover esta categoria?\nAtenção: Apenas categorias sem movimentos associados poderão ser removidas.')" href="?mes=<?php echo $mes_hoje?>&ano=<?php echo $ano_hoje?>&acao=apagar_cat&id=<?php echo $row['id']?>" title="Remover">[remover]</a>
                                                        <a href="javascript:;" style="font-size:10px; color:#666" onclick="document.getElementById('editar_cat_<?php echo $row['id']?>').style.display=''; document.getElementById('editar2_cat_<?php echo $row['id']?>').style.display='none'" title="Editar">[editar]</a>

                                                    </div>
                                                    <div style="display:none" id="editar_cat_<?php echo $row['id']?>">

                                                        <form method="post" action="?mes=<?php echo $mes_hoje?>&ano=<?php echo $ano_hoje?>">
                                                            <input type="hidden" name="acao" value="editar_cat" />
                                                            <input type="hidden" name="id" value="<?php echo $row['id']?>" />
                                                            <input type="text" name="nome" value="<?php echo $row['nome']?>" size="20" maxlength="50" />
                                                            <input type="submit" class="input" value="Alterar" />
                                                        </form>
                                                    </div>

                                                <?php } ?>

                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div style=" background-color:#F1F1F1; padding:10px; border:1px solid #999; margin:5px; display:none" id="add_movimento">
                                <h3>Adicionar Movimento</h3>
                                <?php
                                $qr=mysqli_query($conn, "SELECT * FROM categorias");
                                if (mysqli_num_rows($qr)==0)
                                    echo "Adicione ao menos uma categoria";

                                else { ?>
                                    <form method="post" action="?mes=<?php echo $mes_hoje?>&ano=<?php echo $ano_hoje?>">
                                        <input type="hidden" name="acao" value="1" />
                                        <strong>Data:</strong><br />
                                        <input type="text" name="data" size="11" maxlength="10" value="<?php echo date('d')?>/<?php echo $mes_hoje?>/<?php echo $ano_hoje?>" />

                                        <br />
                                        <br />

                                        <strong>Tipo:<br /></strong>
                                        <label for="tipo_receita" style="color:#030"><input type="radio" name="tipo" value="1" id="tipo_receita" /> Receita</label>&nbsp;
                                        <label for="tipo_despesa" style="color:#C00"><input type="radio" name="tipo" value="0" id="tipo_despesa" /> Despesa</label>

                                        <br />
                                        <br />

                                        <strong>Categoria:</strong>
                                        <br />
                                        <select name="categoria">
                                            <?php while ($row = mysqli_fetch_array($qr)) { ?>
                                                <option value="<?php echo $row['id']?>"><?php echo $row['nome']?></option>
                                            <?php } ?>
                                        </select>

                                        <br />
                                        <br />

                                        <strong>Descrição:</strong>
                                        <br />
                                        <input type="text" name="descricao" size="100" maxlength="255" />

                                        <br />
                                        <br />

                                        <strong>Valor:</strong>
                                        <br />
                                        <input type="text" name="valor" id="valor" size="8" maxlength="10" data-symbol="R$ " data-thousands="." data-decimal="," />
                                        <br />
                                        <br />

                                        <input type="submit" class="input" value="Enviar" />

                                    </form>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <br>
                <table class="table" cellpadding="5" cellspacing="0" width="auto" align="center">

                    <?php
                    $qr = mysqli_query($conn, "SELECT id, nome FROM view_categorias");
                    while ($row = mysqli_fetch_array($qr)) {
                        ?>
                        <div id="editar2_cat_<?php echo $row['id']?>">
                            <?php echo $row['nome']?>

                            <a style="font-size:10px; color:#666" onclick="return confirm('Tem certeza que deseja remover esta categoria?\nAtenção: Apenas categorias sem movimentos associados poderão ser removidas.')" href="?mes=<?php echo $mes_hoje?>&ano=<?php echo $ano_hoje?>&acao=apagar_cat&id=<?php echo $row['id']?>" title="Remover">[remover]</a>
                            <a href="javascript:;" style="font-size:10px; color:#666" onclick="document.getElementById('editar_cat_<?php echo $row['id']?>').style.display='block'; document.getElementById('editar2_cat_<?php echo $row['id']?>').style.display='none'" title="Editar">[editar]</a>

                        </div>
                        <div style="display:none" id="editar_cat_<?php echo $row['id']?>">

                            <form method="post" action="?mes=<?php echo $mes_hoje?>&ano=<?php echo $ano_hoje?>">
                                <input type="hidden" name="acao" value="editar_cat" />
                                <input type="hidden" name="id" value="<?php echo $row['id']?>" />
                                <input type="text" name="nome" value="<?php echo $row['nome']?>" size="20" maxlength="50" />
                                <input type="submit" class="input" value="Alterar" />
                            </form>
                        </div>

                    <?php } ?>
                </table>
            </div>

        </div>

        <div class="tab-pane" id="AbaResultado">
            <br>
            <legend class="panel-title"></legend>
            <br>

            <fieldset>
                <legend class="panel-title">Entradas e Saídas deste mês</legend>
                <br>
                <br>
                <div class="form-group row">

                    <table class="table" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td><span style="font-size:18px; color:#030">Entradas:</span></td>
                            <td align="right"><span style="font-size:18px; color:#030"><?php echo formata_dinheiro($entradas) ?></span></td>
                        </tr>
                        <tr>
                            <td><span style="font-size:18px; color:#C00">Saídas:</span></td>
                            <td align="right"><span style="font-size:18px; color:#C00"><?php echo formata_dinheiro($saidas) ?></span></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <hr size="1" />
                            </td>
                        </tr>
                        <tr>
                            <td><strong style="font-size:22px; color:<?php if ($resultado_mes < 0) echo "#C00"; else echo "#030" ?>">Resultado:</strong></td>
                            <td align="right"><strong style="font-size:22px; color:<?php if ($resultado_mes < 0) echo "#C00"; else echo "#030" ?>"><?php echo formata_dinheiro($resultado_mes) ?></strong></td>
                        </tr>
                    </table>
                </div>

            </fieldset>
            <br>
            <fieldset>
                <legend class="panel-title">Balanço Geral</legend>
                <br>
                <br>
                <?php

                $qr=mysqli_query($conn, "SELECT SUM(valor) as total FROM movimentos WHERE tipo=1 ");
                $row=mysqli_fetch_array($qr);
                $entradas = $row['total'];

                $qr=mysqli_query($conn, "SELECT SUM(valor) as total FROM movimentos WHERE tipo=0 ");
                $row=mysqli_fetch_array($qr);
                $saidas = $row['total'];

                $resultado_geral = $entradas-$saidas;
                ?>

                <div class="form-group row">
                    <table class="table" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td><span style="font-size:18px; color:#030">Entradas:</span></td>
                            <td align="right"><span style="font-size:18px; color:#030"><?php echo formata_dinheiro($entradas)?></span></td>
                        </tr>
                        <tr>
                            <td><span style="font-size:18px; color:#C00">Saídas:</span></td>
                            <td align="right"><span style="font-size:18px; color:#C00"><?php echo formata_dinheiro($saidas)?></span></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <hr size="1" />
                            </td>
                        </tr>
                        <tr>
                            <td><strong style="font-size:22px; color:<?php if ($resultado_geral<0) echo "#C00"; else echo "#030"?>">Resultado:</strong></td>
                            <td align="right"><strong style="font-size:22px; color:<?php if ($resultado_geral<0) echo "#C00"; else echo "#030"?>"><?php echo formata_dinheiro($resultado_geral)?></strong></td>
                        </tr>
                    </table>
                </div>
            </fieldset>

            <br>
            <br>
        </div>

        <div class="tab-pane" id="AbaCalendario">
            <br>
            <legend class="panel-title"></legend>
            <br>
            <div id="calendar"></div>
            <br>
            <br>
            <div id="calendarModal" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span> <span class="sr-only">close</span></button>
                            <h4 id="modalTitle" class="modal-title"></h4>
                        </div>
                        <div id="modalBody" class="modal-body"> </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <br />


    <br />
    <div class="form-group row">
        <div class="col-md-12 text-right">
            <span>Livro Caixa - <strong><?php echo APP_NAME; ?></strong></span>
            <button class="btn btn-default btn-lg" type="button" onclick="window.location.href = 'index.php?sair' ">
                <span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>
                Saída
            </button>
        </div>
    </div>

    <!--  JavaScripts  -->
    <script src="js/jquery-2.2.4.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="js/bootstrap.min.js"></script>
    
    <script src="js/jquery.maskMoney.js" type="text/javascript"></script>

    <script src="js/scripts.js" type="text/javascript"></script>

    <!-- Full Calendar -->
    <script src="js/moment.js"></script>
    <script src="js/fullcalendar.js"></script>
    <script src="js/locale_moment_pt-br.js"></script>

</body>

<?php

// Busca os movimentos do ano informado.
$query = mysqli_query($conn, "SELECT * FROM movimentos WHERE ano = '$ano_hoje' ORDER By ano, mes, dia");

$eventos = array();
$contador = 0;
while ($row = mysqli_fetch_array($query)) {
    $contador++;

    $categoriaId = $row['categoria_id'];

    $query2 = mysqli_query($conn, "SELECT nome FROM categorias WHERE id = '$categoriaId'");
    $row2 = mysqli_fetch_array($query2);
    $nomeCategoria = $row2['nome'];

    // Verifica a cor do evento.
    $cor = 'blue'; // Entradas
    if ($row['tipo'] == '0') { // Saídas
        $cor = 'red';
    }

    $eventos[] = array(
        'id' => $contador,
        'title' => $nomeCategoria . ' - ' . substr($row['descricao'],0,10),
        'description' => $row['descricao'],
        'start' => $row['ano'] . '-' . str_pad($row['mes'], 2, '0', STR_PAD_LEFT) . '-'. $row['dia'],
        'end' => $row['ano'] . '-'. str_pad($row['mes'], 2, '0', STR_PAD_LEFT) . '-' . $row['dia'],
        'color' => $cor
    );
}

$jsonEventos = json_encode($eventos);

//$eventos = <<<EOF
//[
//              {
//                  title: "Some event",
//                  start: new Date('2020-1-10'),
//                  end: new Date('2020-1-20'),
//                  id: 1,
//                  allDay: true,
//                  editable: true,
//                  eventDurationEditable: true,
//              },
//              {
//                  id: 2,
//                  title: 'Test Event 1',
//                  start: '2020-11-05T13:15:30Z',
//                  end: '2020-11-05T13:30:00Z'
//              }
//          ]
//EOF;

?>

<script>
  $(function() {
    $('#valor').maskMoney();
  });

  $( "#btnAbaCalendario" ).click(function(e) {
      e.preventDefault();

      setTimeout(function () {
          $("#calendar").fullCalendar("render");
      }, 300); // Set enough time to wait until animation finishes;

  });

  // Code goes here
  $(document).ready(function () {

      // page is now ready, initialize the calendar...
      var calendar = $('#calendar').fullCalendar({
          // put your options and callbacks here
          header: {
              left: 'prev,next today',
              center: 'title',
              right: 'year,month,basicWeek,basicDay'

          },
          buttonText: {
              year: "Ano",
              today: "Hoje",
              month: "Mês",
              week: "Semana",
              day: "Dia",
              list: "Lista"
          },
          weekLabel: "Sm",
          allDayText: "dia inteiro",
          eventLimitText: function (n) {
              return "mais +" + n;
          },
          noEventsMessage: "Não há eventos para mostrar",
          timezone: 'America/Sao_Paulo',
          timeFormat: 'hh:mm',
          locale: 'pt-br',
          lang: 'pt-br',
          height: "auto",
          selectable: true,
          dragabble: true,
          defaultView: 'year',
          yearColumns: 3,

          durationEditable: true,
          bootstrap: false,

          events: <?php echo $jsonEventos; ?>,
          select: function (start, end, allDay) {
              var title = prompt('Título:');
              if (title) {
                  var event = {
                      title: title,
                      start: start.clone(),
                      end: end.clone(),
                      allDay: true,
                      editable: true,
                      eventDurationEditable: true,
                      eventStartEditable: true,
                      color: 'red',
                  };


                  calendar.fullCalendar('renderEvent', event, true);
              }
          },
          eventClick: function(info) {

              $('#modalTitle').html(info.title);
              $('#modalBody').html(info.description);

              $('#calendarModal').modal();
          }
      });

  });

</script>
</html>
