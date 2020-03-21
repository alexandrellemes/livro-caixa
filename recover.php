<?php
session_start();
set_time_limit(0);

require_once (__DIR__ . '/functions.php');
require_once (__DIR__ . '/config.php');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title id='titulo'>Livro caixa <?php echo APP_NAME; ?></title>
    <meta name="LANGUAGE" content="Portuguese"/>
    <meta name="AUDIENCE" content="all"/>
    <meta name="RATING" content="GENERAL"/>

    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/font-awesome/css/font-awesome.min.css">
	<link rel="stylesheet" href="assets/css/form-elements.css">
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<br>
<hr>
<div class="container">
  <div class="alert" id="info"></div>
    <div class="row">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="text-center">
                          <h3><i class="fa fa-lock fa-4x"></i></h3>
                          <h2 class="text-center">Esqueceu a senha?</h2>
                          <p>Voce pode mudar a senha aqui.</p>
                            <div class="panel-body">
                              
                              <form class="form" id="recover-form" autocomplete="off" role="form" method="post" action="process.php">
                                <fieldset>
                                  <div class="form-group">
                                    <div class="input-group">
                                      <span class="input-group-addon"><i class="glyphicon glyphicon-envelope color-blue"></i></span>
                                      
                                      <input id="emailInput" name="emailInput" placeholder="Endereço de e-mail" class="form-control" oninvalid="setCustomValidity('Entre um e-mail válido!')" onchange="try{setCustomValidity('')}catch(e){}" required="" type="email" autofocus >
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <input class="btn btn-lg btn-primary btn-block" value="Enviar" type="button" onClick="recoverPassword();" >
                                    <input class="btn btn-lg btn-primary btn-block" value="Livro Caixa" type="button" onClick="window.location = 'index.php';" >
                                  </div>
                                </fieldset>
                              </form>
                              
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

        <!-- Javascript -->
        <script src="assets/js/jquery-1.11.1.min.js"></script>
        <script src="assets/bootstrap/js/bootstrap.min.js"></script>
        <script src="assets/js/jquery.backstretch.min.js"></script>
        <script src="assets/js/scripts.js"></script>
        
</body>
</html>