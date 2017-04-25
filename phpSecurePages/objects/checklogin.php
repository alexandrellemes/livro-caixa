<?php
/**************************************************************/
/*         phpSecurePages version 0.44 beta (04/02/15)        */
/*              Copyright 2015 Circlex.com, Inc.              */
/*                                                            */
/*          ALWAYS CHECK FOR THE LATEST RELEASE AT            */
/*              http://www.phpSecurePages.com                 */
/*                                                            */
/*              Free for non-commercial use only.             */
/*               If you are using commercially,               */
/*         or using to secure your clients' web sites,        */
/*   please purchase a license at http://phpsecurepages.com   */
/*                                                            */
/**************************************************************/
/*      There are no user-configurable items on this page     */
/**************************************************************/

// check if login is necessary

// Check if secure.php has been loaded correctly
if ( !defined("LOADED_PROPERLY") || isset($_GET['cfgProgDir']) || isset($_POST['cfgProgDir'])) {
        echo "Parsing of phpSecurePages has been halted!";
        exit();
}

if (!isset($entered_login) && !isset($entered_password)) {
        // use data from session
        session_start();
        // session hack to make sessions on old php4 versions work
        if (phpversion() > 4.0) {
                if (isset($_SESSION['login'])) $login = $_SESSION['login'];
                if (isset($_SESSION['password'])) $password = $_SESSION['password'];
                }
        }
else {
        // use entered data
        session_start();
        // session hack to make sessions on old php4 versions work
        if (phpversion() <= 4.0) {
                session_unregister("login");
                session_unregister("password");
                }
        // encrypt entered login & password
        $login = $entered_login;
        if ($passwordEncryptedWithMD5 && function_exists(md5)) {
                $password = md5($entered_password);
                }
        else {
                $password = $entered_password;
                }
        // session hack to make sessions on old php4 versions work
        if (phpversion() > 4.0) {
                $_SESSION['login'] = $login;
                $_SESSION['password'] = $password;
                }
        else {
                session_register("login");
                session_register("password");
                }
        }

if (!isset($login)) {
        // no login available
        include($cfgProgDir . "interface.php");
        exit;
        }

if (!isset($password)) {
        // no password available
        $phpSP_message = $strNoPassword;
        include($cfgProgDir . "interface.php");
        exit;
        }

// login and password variables exist
// continue to checking them
?>
