<?php
session_start();
set_time_limit(0);

$pagina_login = 1;

include 'config.php';
include 'functions.php';

if (isset($_GET['sair'])) {
    $_SESSION['logado'] = "";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title id='titulo'>Livro caixa <?php echo $lc_titulo ?></title>
    <meta name="LANGUAGE" content="Portuguese"/>
    <meta name="AUDIENCE" content="all"/>
    <meta name="RATING" content="GENERAL"/>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <!-- Optional theme -->
    <link rel="stylesheet" href="css/bootstrap-theme.min.css">

    <link href="css/normalize.css" rel="stylesheet" type="text/css"/>

    <script src="js/jquery-2.2.4.min.js"></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src="js/bootstrap.min.js"></script>

    <script language="javascript" src="scripts.js"></script>
</head>

<body>
<br>
<div class="container" >
    <div class="row">
        <div class="col-lg-6 col-lg-offset-3 col-md-6 col-md-offset-3">
            <div class="alert-placeholder"></div>
            <div class="panel panel-success">

                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-6">
                            <a href="login.php" class="active" id="login-form-link">Login</a>
                        </div>
                        <div class="col-xs-6">
                            <a href="#" id="register-form-link">Registro</a>
                        </div>
                    </div>
                    <hr>
                </div>

                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="text-center">
                                <form id="register-form" autocomplete="off" role="form" method="post" action="process.php">
                                    <div class="form-group">
                                        <label for="email">Digite o endereço de E-Mail</label>
                                        <input id="email" class="form-control" type="email" required="" autocomplete="off" value="" placeholder="Email" tabindex="1"
                                               name="email">
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-lg-6 col-lg-offset-3 col-md-6 col-md-offset-3 col-sm-12 col-xs-12">
                                                <input id="recover-submit" class="form-control btn btn-success" type="submit" value="Recupere a conta" tabindex="2" name="recover-submit">
                                            </div>
                                        </div>
                                    </div>
                                    <input id="token" class="hide" type="hidden" value="80327595e28d381b4bdc1715c8381272" name="token">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>