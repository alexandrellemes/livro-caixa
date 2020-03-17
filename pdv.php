  <meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
<?php

ini_set('display_errors', true);
error_reporting(E_ALL);

/** Configuracao para o phpSecurePages **/
$cfgProgDir =  'phpSecurePages/';




//session_start();
set_time_limit(0);

include 'config.php';
include 'functions.php';
if(isset( $_GET['resultado'])){
    $valor = $_GET['corrida'];
    $tipo = $_GET['tipo'];
    $cat = $_GET['categoria'];
    $mes = date('m');
    $ano =date('Y');
    $dia =date('d');

    $sql = "INSERT INTO movimentos (dia,mes,ano,tipo,valor,categoria_id)
            values ('$dia','$mes','$ano','$tipo','$valor','$cat')";

    mysqli_query($conn, $sql);

    echo mysqli_error($conn);


}
?>
  <script type="text/javascript">
      function calculadora() {
          var n1 = parseFloat(document.getElementById('n1').value);
          var n2 = parseFloat(document.getElementById('n2').value);
          var resultado = 0;
              resultado = n2 - n1;

          document.getElementById('resultado').value = resultado;
      }
  </script>


  <form action="" method="GET" >
  <div class="container">
      <div class="form-group">
      <label for="usr">
          Valor da corrida:</label>
          <input id="n1" type="number"  class="currency" step="0.01" name="corrida" required><br>
      </div>
          <div class="form-group">
          <label for="pwd"> Pago: </label>

          <input id="n2" type="number" oninput="calculadora()" step="0.01">
          </div>
      <div class="form-group">
          <label for="exampleFormControlSelect1">tipo da despesa</label>
          <select name="tipo" class="form-control" id="exampleFormControlSelect1" required>
              <option value="1">Receita</option>
              <option value="0">Despesa</option>
              <option value="2">Cartão</option>
          </select>
      </div>
      <div class="form-group">
          <label for="exampleFormControlSelect1"> Categoria: <select name="categoria" required>
          <?php
          $qr=mysqli_query($conn, "SELECT * FROM categorias");
          while ($row=mysqli_fetch_array($qr)){
              ?>
              <option value="<?php echo $row['id']?>"><?php echo $row['nome']?></option>
          <?php }?>
      </select>
      </div>
      <div class="form-group">
          <label for="usr">Troco: <input id="resultado" type="number" name="resultado" step="0.01"></label>
      </div>
  </div>
      <button type="submit" class="btn btn-primary center-block"">enviar</button>
  </form>

