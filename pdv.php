<?php
/** Configuracao para o phpSecurePages **/
$cfgProgDir =  'phpSecurePages/';

//session_start();
set_time_limit(0);

require_once (__DIR__ . '/functions.php');
require_once (__DIR__ . '/config.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <title id='titulo'>Livro caixa  / PDV</title>
        <meta name="LANGUAGE" content="Portuguese" />
        <meta name="AUDIENCE" content="all" />
        <meta name="RATING" content="GENERAL" />

        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="css/bootstrap.min.css">

        <!-- Optional theme -->
        <link rel="stylesheet" href="css/bootstrap-theme.min.css">

        <link href="css/normalize.css" rel="stylesheet" type="text/css" />

    </head>
    <body>

        <?php

        if(isset( $_GET['resultado'])) {

            $valor = $_GET['corrida'];
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);

            $tipo = $_GET['tipo'];
            $cat = $_GET['categoria'];

            $mes = date('m');
            $ano = date('Y');
            $dia = date('d');

            $sql = "INSERT INTO movimentos (dia,mes,ano,tipo,valor,categoria_id, descricao)
                    values ('$dia','$mes','$ano','$tipo','$valor','$cat', 'Lancamento via PDV')";

            try {

                mysqli_query($conn, $sql);

                header("Refresh:0; url=pdv.php");

            } catch (Exception $e) {

                die($e->getMessage());
            }



        }
        ?>

        <div class="container">
            <form id="frmPDV" action="" method="GET" >
                <fieldset>
                    <legend>PDV - <?php echo APP_NAME; ?></legend>
                    <div class="form-group row">
                        <label class="control-label col-md-2" for="usr">Valor da corrida:</label>
                        <div class="col-md-10">
                            <input class="currency" step="0.01" type="text" name="corrida" id="corrida" size="10" maxlength="10" data-symbol="R$ " data-thousands="." data-decimal="," required />
                            <span class="glyphicon glyphicon-remove" onclick="limparCorrida();"></span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-2" for="pwd">Pago: </label>
                        <div class="col-md-10">
                            <input class="currency" step="0.01" type="text" name="pago" id="pago" oninput="calculadora()" size="10" maxlength="10" data-symbol="R$ " data-thousands="." data-decimal="," required />
                            <span class="glyphicon glyphicon-remove" onclick="limparPago();"></span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-2" for="tipo">Tipo da despesa:</label>
                        <div class="col-md-10">
                            <select class="form-control" name="tipo" id="tipo" required>
                                <option value="1">Receita</option>
                                <option value="2">Cartão</option>
                                <option value="0">Despesa</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-2" for="categoria">Categoria:</label>
                        <div class="col-md-10">
                            <select class="form-control" name="categoria" id="categoria" required>
                                <?php
                                $qr=mysqli_query($conn, "SELECT * FROM categorias");
                                while ($row=mysqli_fetch_array($qr)) {
                                    ?>
                                    <option value="<?php echo $row['id']?>"><?php echo $row['nome']?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-2" for="usr">Troco:</label>
                        <div class="col-md-10">
                            <input type="text" name="resultado" id="resultado" size="15" maxlength="15" data-symbol="R$ " data-thousands="." data-decimal="," />
                            <span class="glyphicon glyphicon-hand-left" onclick="calculadora();"></span>
                        </div>
                    </div>

                    <hr>
                    <button type="button" class="btn btn-primary center-block" id="btnEnviar" name="btnEnviar" >enviar</button>

                </fieldset>

            </form>

        </div>

        <!--  JavaScripts  -->
        <script type="text/javascript" src="js/jquery-2.2.4.min.js"></script>

        <!-- Latest compiled and minified JavaScript -->
        <script type="text/javascript" src="js/bootstrap.min.js"></script>

        <script type="text/javascript" src="js/jquery.maskMoney.js" ></script>

        <script type="text/javascript" src="scripts.js"></script>

        <script type="text/javascript">

            function limparCorrida() {
                $('#corrida').val('');
                $('#corrida').focus();
            }

            function limparPago() {
                $('#pago').val('');
                $('#pago').focus();
            }

            function calculadora() {
                var corrida = parseFloat($('#corrida').val().replace(".", "").replace(',','.'));
                var pago = parseFloat($('#pago').val().replace(".", "").replace(',','.'));

                var resultado = isNaN(pago - corrida) ? 0 : (pago - corrida);

                var texto = resultado.toLocaleString("pt-BR", { style: "currency" , currency:"BRL"});

                // console.log(corrida);
                // console.log(pago);
                // console.log(texto);

                $('#resultado').val(texto);

                $("#resultado").css('color', 'blue');
                if (resultado > 0) {
                    $("#resultado").css('color', 'green');
                } else if(resultado < 0) {
                    $("#resultado").css('color', 'red');
                }

            }

            $(function() {
                $('#corrida').maskMoney();
                $('#pago').maskMoney();
                calculadora();
                $('#corrida').focus();
            });

            $("#corrida").bind("change paste keyup", function() {
                calculadora();
            });

            $("#pago").bind("change paste keyup", function() {
                calculadora();
            });

            $( "#btnEnviar" ).click(function() {

                var corrida = parseFloat($('#corrida').val().replace(".", "").replace(',','.'));
                var pago = parseFloat($('#pago').val().replace(".", "").replace(',','.'));

                if (isNaN(corrida)) {
                    alert('Digite o valor de corrida!');
                    $('#corrida').focus();
                } else if (isNaN(pago)) {
                    alert('Digite o valor de pago!');
                    $('#pago').focus();
                } else {
                     $('#frmPDV').submit();
                }

            });

        </script>

    </body>
</html>