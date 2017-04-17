<?php
ini_set('display_errors', true);
error_reporting(E_ALL);

session_start();
set_time_limit(0);

include 'config.php';
include 'functions.php';

if (isset($_GET['acao']) && $_GET['acao'] == 'apagar') {
    $id = $_GET['id'];

    mysqli_query($conn, "DELETE FROM lc_movimento WHERE id='$id'");
    echo mysqli_error($conn);

    header("Location: ?mes=" . $_GET['mes'] . "&ano=" . $_GET['ano'] . "&ok=2");
    exit();
}

if (isset($_POST['acao']) && $_POST['acao'] == 'editar_cat') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];

    mysqli_query($conn, "UPDATE lc_cat SET nome='$nome' WHERE id='$id'");
    echo mysqli_error($conn);

    header("Location: ?mes=" . $_GET['mes'] . "&ano=" . $_GET['ano'] . "&cat_ok=3");
    exit();
}

if (isset($_GET['acao']) && $_GET['acao'] == 'apagar_cat') {
    $id = $_GET['id'];

    $qr=mysqli_query($conn, "SELECT c.id FROM lc_movimento m, lc_cat c WHERE c.id=m.cat && c.id=$id");
    if (mysqli_num_rows($qr)>0){
        header("Location: ?mes=" . $_GET['mes'] . "&ano=" . $_GET['ano'] . "&cat_err=1");
        exit();
    }

    mysqli_query($conn, "DELETE FROM lc_cat WHERE id='$id'");
    echo mysqli_error($conn);

    header("Location: ?mes=" . $_GET['mes'] . "&ano=" . $_GET['ano'] . "&cat_ok=2");
    exit();
}

if (isset($_POST['acao']) && $_POST['acao'] == 'editar_mov') {
    $id = $_POST['id'];
    $dia = $_POST['dia'];
    $tipo = $_POST['tipo'];
    $cat = $_POST['cat'];
    $descricao = $_POST['descricao'];
    $valor = str_replace(",", ".", $_POST['valor']);

    mysqli_query($conn, "UPDATE lc_movimento SET dia='$dia', tipo='$tipo', cat='$cat', descricao='$descricao', valor='$valor' WHERE id='$id'");
    echo mysqli_error($conn);

    header("Location: ?mes=" . $_GET['mes'] . "&ano=" . $_GET['ano'] . "&ok=3");
    exit();
}

if (isset($_POST['acao']) && $_POST['acao'] == 2) {

    $nome = $_POST['nome'];

    mysqli_query($conn, "INSERT INTO lc_cat (nome) values ('$nome')");

    echo mysqli_error($conn);

    header("Location: ?mes=" . $_GET['mes'] . "&ano=" . $_GET['ano'] . "&cat_ok=1");
    exit();
}

if (isset($_POST['acao']) && $_POST['acao'] == 1) {

    $data = $_POST['data'];
    $tipo = $_POST['tipo'];
    $cat = $_POST['cat'];
    $descricao = $_POST['descricao'];
    $valor = str_replace(",", ".", $_POST['valor']);

    $t = explode("/", $data);
    $dia = $t[0];
    $mes = $t[1];
    $ano = $t[2];

    mysqli_query($conn, "INSERT INTO lc_movimento (dia,mes,ano,tipo,descricao,valor,cat) values ('$dia','$mes','$ano','$tipo','$descricao','$valor','$cat')");

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
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title id='titulo'>Livro caixa <?php echo $lc_titulo?></title>
    <meta name="LANGUAGE" content="Portuguese" />
    <meta name="AUDIENCE" content="all" />
    <meta name="RATING" content="GENERAL" />

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">

    <link href="css/normalize.css" rel="stylesheet" type="text/css" />

    <script src="js/jquery-2.2.4.min.js"></script>

        <!-- Latest compiled and minified JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <script language="javascript" src="scripts.js"></script>
</head>
<body style="padding:10px">

<div class="container bs-docs-container">
<div class="table-responsive">
<table class="table" cellpadding="1" cellspacing="10"  width="auto" align="center" style="background-color:#033">

<tr>
<td colspan="11" style="background-color:#005B5B;">
<h2 style="color:#FFF; margin:5px">Livro Caixa - <?php echo $lc_titulo?></h2>
</td>
<td colspan="2" align="right" style="background-color:#005B5B;">
<a style="color:#FFF" href="?mes=<?php echo date('m')?>&ano=<?php echo date('Y')?>">Hoje:<strong> <?php echo date('d')?> de <?php echo mostraMes(date('m'))?> de <?php echo date('Y')?></strong></a>&nbsp;
</td>
</tr>
<tr>

<td width="70">
<select onchange="location.replace('?mes=<?php echo $mes_hoje?>&ano='+this.value)">
<?php
for ($i=2008; $i<=2020; $i++) {
?>
<option value="<?php echo $i?>" <?php if ($i==$ano_hoje) echo "selected=selected"?> ><?php echo $i?></option>
<?php }?>
</select>
</td>


<?php
for ($i=1; $i <= 12; $i++) {
?>
    <td align="center" style="<?php if ($i!=12) echo "border-right:1px solid #FFF;"?> padding-right:5px">
    <a href="?mes=<?php echo $i?>&ano=<?php echo $ano_hoje?>" style="
    <?php if($mes_hoje == $i){?>
    color:#033; font-size:16px; font-weight:bold; background-color:#FFF; padding:5px
    <?php } else { ?>
    color:#FFF; font-size:16px;
    <?php }?>
    ">
    <?php echo mostraMes($i);?>
    </a>
    </td>
<?php
}
?>
</tr>
</table>
</div>
<br />


<div class="table-responsive">
<table class="table" cellpadding="10" cellspacing="0" width="auto" align="center" >
<tr>
<td colspan="2">

<h2><?php echo mostraMes($mes_hoje)?>/<?php echo $ano_hoje?></h2>
</td>
<td align="right">
<a href="javascript:;" onclick="abreFecha('add_cat')" class="bnt">[+] Adicionar Categoria</a>
<a href="javascript:;" onclick="abreFecha('add_movimento')" class="bnt"><strong>[+] Adicionar Movimento</strong></a>
</td>
</tr>

<tr >
<td colspan="3" >

    <?php
if (isset($_GET['cat_err']) && $_GET['cat_err']==1){
?>

<div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
<strong>Esta categoria não pode ser removida, pois há movimentos associados a esta</strong>
</div>

<?php }?>

    <?php
if (isset($_GET['cat_ok']) && $_GET['cat_ok']==2){
?>

<div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
<strong>Categoria removida com sucesso!</strong>
</div>

<?php }?>

<?php
if (isset($_GET['cat_ok']) && $_GET['cat_ok']==1){
?>

<div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
<strong>Categoria Cadastrada com sucesso!</strong>
</div>

<?php }?>

    <?php
if (isset($_GET['cat_ok']) && $_GET['cat_ok']==3){
?>

<div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
<strong>Categoria alterada com sucesso!</strong>
</div>

<?php }?>

<?php
if (isset($_GET['ok']) && $_GET['ok']==1){
?>

<div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
<strong>Movimento Cadastrado com sucesso!</strong>
</div>

<?php }?>

<?php
if (isset($_GET['ok']) && $_GET['ok']==2){
?>

<div style="padding:5px; background-color:#900; text-align:center; color:#FFF">
<strong>Movimento removido com sucesso!</strong>
</div>

<?php }?>

    <?php
if (isset($_GET['ok']) && $_GET['ok']==3){
?>

<div style="padding:5px; background-color:#FF6; text-align:center; color:#030">
<strong>Movimento alterado com sucesso!</strong>
</div>

<?php }?>

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
                <b>Editar/Remover Categorias:</b><br/><br/>
<?php
$qr=mysqli_query($conn, "SELECT id, nome FROM lc_cat");
while ($row=mysqli_fetch_array($qr)){
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

<?php }?>

            </td>
        </tr>
    </table>
    </div>
</div>

<div style=" background-color:#F1F1F1; padding:10px; border:1px solid #999; margin:5px; display:none" id="add_movimento">
<h3>Adicionar Movimento</h3>
<?php
$qr=mysqli_query($conn, "SELECT * FROM lc_cat");
if (mysqli_num_rows($qr)==0)
	echo "Adicione ao menos uma categoria";

else{
?>
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

<strong>Categoria:</strong><br />
<select name="cat">
<?php
while ($row=mysqli_fetch_array($qr)){
?>
<option value="<?php echo $row['id']?>"><?php echo $row['nome']?></option>
<?php }?>
</select>

<br />
<br />

<strong>Descrição:</strong><br />
<input type="text" name="descricao" size="100" maxlength="255" />

<br />
<br />

<strong>Valor:</strong><br />
R$<input type="text" name="valor" size="8" maxlength="10" />

<br />
<br />

<input type="submit" class="input" value="Enviar" />

</form>
<?php }?>
</div>
</td>
</tr>

<tr>
<td align="left" valign="top" width="auto" style="background-color:#D3FFE2">

<?php
$qr=mysqli_query($conn, "SELECT SUM(valor) as total FROM lc_movimento WHERE tipo=1 && mes='$mes_hoje' && ano='$ano_hoje'");
$row=mysqli_fetch_array($qr);
$entradas=$row['total'];

$qr=mysqli_query($conn, "SELECT SUM(valor) as total FROM lc_movimento WHERE tipo=0 && mes='$mes_hoje' && ano='$ano_hoje'");
$row=mysqli_fetch_array($qr);
$saidas=$row['total'];

$resultado_mes=$entradas-$saidas;
?>

    <fieldset>
      <legend><strong>Entradas e Saídas deste mês</strong></legend>
       <div class="table-responsive">
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

</td>

<td width="15">
</td>

<td align="left" valign="top" width="auto" style="background-color:#F1F1F1">
<fieldset>
<legend>Balanço Geral</legend>

<?php

$qr=mysqli_query($conn, "SELECT SUM(valor) as total FROM lc_movimento WHERE tipo=1 ");
$row=mysqli_fetch_array($qr);
$entradas=$row['total'];

$qr=mysqli_query($conn, "SELECT SUM(valor) as total FROM lc_movimento WHERE tipo=0 ");
$row=mysqli_fetch_array($qr);
$saidas=$row['total'];

$resultado_geral=$entradas-$saidas;
?>

<div class="table-responsive">
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
</td>

</tr>
</table>
</div>
<br />

<div class="table-responsive">
<table class="table" cellpadding="5" cellspacing="0" width="auto" align="center">
<tr>
<td colspan="2">
    <div style="float:right; text-align:right">
<form name="form_filtro_cat" method="get" action=""  >
<input type="hidden" name="mes" value="<?php echo $mes_hoje?>" >
<input type="hidden" name="ano" value="<?php echo $ano_hoje?>" >
    Filtrar por categoria:  <select name="filtro_cat" onchange="form_filtro_cat.submit()">
<option value="">Tudo</option>
<?php
$qr=mysqli_query($conn, "SELECT DISTINCT c.id, c.nome FROM lc_cat c, lc_movimento m WHERE m.cat=c.id && m.mes='$mes_hoje' && m.ano='$ano_hoje'");
while ($row=mysqli_fetch_array($qr)){
?>
<option <?php if (isset($_GET['filtro_cat']) && $_GET['filtro_cat']==$row['id'])echo "selected=selected"?> value="<?php echo $row['id']?>"><?php echo $row['nome']?></option>
<?php }?>
</select>
  <input type="submit" value="Filtrar" class="botao" />
</form>
    </div>

<h2>Movimentos deste Mês</h2>

</td>
</tr>
<?php
$filtros="";
if (isset($_GET['filtro_cat'])){
	if ($_GET['filtro_cat']!=''){
		$filtros="&& cat='".$_GET['filtro_cat']."'";

                $qr=mysqli_query($conn, "SELECT SUM(valor) as total FROM lc_movimento WHERE tipo=1 && mes='$mes_hoje' && ano='$ano_hoje' $filtros");
                $row=mysqli_fetch_array($qr);
                $entradas=$row['total'];

                $qr=mysqli_query($conn, "SELECT SUM(valor) as total FROM lc_movimento WHERE tipo=0 && mes='$mes_hoje' && ano='$ano_hoje' $filtros");
                $row=mysqli_fetch_array($qr);
                $saidas=$row['total'];

                $resultado_mes=$entradas-$saidas;

        }
}

$qr=mysqli_query($conn, "SELECT * FROM lc_movimento WHERE mes='$mes_hoje' && ano='$ano_hoje' $filtros ORDER By dia");
$cont=0;
while ($row=mysqli_fetch_array($qr)){
$cont++;

$cat=$row['cat'];
$qr2=mysqli_query($conn, "SELECT nome FROM lc_cat WHERE id='$cat'");
$row2=mysqli_fetch_array($qr2);
$categoria=$row2['nome'];

?>
<tr style="background-color:<?php if ($cont%2==0) echo "#F1F1F1"; else echo "#E0E0E0"?>" >
<td align="center" width="15"><?php echo $row['dia']?></td>
<td><?php echo $row['descricao']?> <em>(<a href="?mes=<?php echo $mes_hoje?>&ano=<?php echo $ano_hoje?>&filtro_cat=<?php echo $cat?>"><?php echo $categoria?></a>)</em> <a href="javascript:;" style="font-size:10px; color:#666" onclick="document.getElementById('editar_mov_<?php echo $row['id']?>').style.display='';  " title="Editar">[editar]</a></td>
<td align="right"><strong style="color:<?php if ($row['tipo']==0) echo "#C00"; else echo "#030"?>"><?php if ($row['tipo']==0) echo "-"; else echo "+"?><?php echo formata_dinheiro($row['valor'])?></strong></td>
</tr>
    <tr style="display:none; background-color:<?php if ($cont%2==0) echo "#F1F1F1"; else echo "#E0E0E0"?>" id="editar_mov_<?php echo $row['id']?>">
        <td colspan="3">
            <hr/>
            <form method="post" action="?mes=<?php echo $mes_hoje?>&ano=<?php echo $ano_hoje?>">
            <input type="hidden" name="acao" value="editar_mov" />
            <input type="hidden" name="id" value="<?php echo $row['id']?>" />

            <b>Dia:</b> <input type="text" name="dia" size="3" maxlength="2" value="<?php echo $row['dia']?>" />&nbsp;|&nbsp;
            <b>Tipo:</b> <label for="tipo_receita<?php echo $row['id']?>" style="color:#030"><input <?php if($row['tipo']==1) echo "checked=checked"?> type="radio" name="tipo" value="1" id="tipo_receita<?php echo $row['id']?>" /> Receita</label>&nbsp; <label for="tipo_despesa<?php echo $row['id']?>" style="color:#C00"><input <?php if($row['tipo']==0) echo "checked=checked"?> type="radio" name="tipo" value="0" id="tipo_despesa<?php echo $row['id']?>" /> Despesa</label>&nbsp;|&nbsp;
            <b>Categoria:</b>
<select name="cat">
<?php
$qr2=mysqli_query($conn, "SELECT * FROM lc_cat");
while ($row2=mysqli_fetch_array($qr2)){
?>
    <option <?php if($row2['id']==$row['cat']) echo "selected"?> value="<?php echo $row2['id']?>"><?php echo $row2['nome']?></option>
<?php }?>
</select>&nbsp;|&nbsp;
            <b>Valor:</b> R$<input type="text" value="<?php echo $row['valor']?>" name="valor" size="8" maxlength="10" />
            <br/>
            <b>Descricao:</b> <input type="text" name="descricao" value="<?php echo $row['descricao']?>" size="70" maxlength="255" />

            <input type="submit" class="input" value="Alterar" />
            </form>
            <div style="text-align: right">
            <a style="color:#FF0000" onclick="return confirm('Tem certeza que deseja apagar?')" href="?mes=<?php echo $mes_hoje?>&ano=<?php echo $ano_hoje?>&acao=apagar&id=<?php echo $row['id']?>" title="Remover">[remover]</a>
            </div>
            <hr/>
        </td>
    </tr>

<?php
}
?>
<tr>
<td colspan="3" align="right">
<strong style="font-size:22px; color:<?php if ($resultado_mes<0) echo "#C00"; else echo "#030"?>"><?php echo formata_dinheiro($resultado_mes)?></strong>
</td>
</tr>
</table>
</div>

<br />
<br />

<div class="table-responsive">
<table class="table" cellpadding="5" cellspacing="0" width="auto" align="center">
<tr>
<td align="right">
<hr size="1" />
<em>Livro Caixa - <strong><?php echo $lc_titulo?></strong></em>

    <button class="btn btn-default btn-lg" type="button" onclick="window.location.href = 'login.php?sair' ">
        <span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>
        Saída
    </button>
</td>
</tr>
</table>
</div>
    </div>
</body>
</html>
