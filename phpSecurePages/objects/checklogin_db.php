<?php
/**************************************************************/
/*              phpSecurePages version 0.43 beta               */
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

// check login with Database

// Check if secure.php has been loaded correctly
if (!defined("LOADED_PROPERLY")
    || isset($_GET['cfgProgDir'])
    || isset($_POST['cfgProgDir'])) {
    echo "Parsing of phpSecurePages has been halted!";
    exit();
}

// contact database
if (empty($cfgServerPort)) {
    $conn = mysqli_connect($cfgServerHost, $cfgServerUser, $cfgServerPassword) or die($strNoConnection);
} else {
    $conn = mysqli_connect($cfgServerHost . ":" . $cfgServerPort, $cfgServerUser, $cfgServerPassword) or die($strNoConnection);
}

mysqli_select_db($conn, $cfgDbDatabase) or die(mysql_error());

if (phpversion() >= 4.3) {
    $login = mysqli_real_escape_string($conn, $login);
} else {
    $login = mysql_escape_string($login);
}

$userQuery = mysqli_query($conn, "SELECT * FROM $cfgDbTableUsers WHERE $cfgDbLoginfield = '$login'") or die($strNoDatabase);

// check user and password
if (mysqli_num_rows($userQuery) != 0) {
    // user exist --> continue
    $userArray = mysqli_fetch_array($userQuery);

    if ($login != $userArray[$cfgDbLoginfield]) {
        // Case sensative user not present in database
        $phpSP_message = $strUserNotExist;
        // include($cfgProgDir . "objects/logout.php");
        include($cfgProgDir . "interface.php");
        exit;
    }

} else {
    // user not present in database
    $phpSP_message = $strUserNotExist;
    include($cfgProgDir . "interface.php");
    exit;
}

if (!$userArray[$cfgDbPasswordfield]) {
    // password not present in database for this user
    $phpSP_message = $strPwNotFound;
    include($cfgProgDir . "interface.php");
    exit;
}

//var_dump($password, $userArray, stripslashes($userArray["$cfgDbPasswordfield"]) != $password); die;

if ($passwordEncryptedWithMD5
    && stripslashes($userArray["$cfgDbPasswordfield"]) != $password) {
    // password is wrong
    $phpSP_message = $strPwFalse;
    include($cfgProgDir . "interface.php");
    exit;
}

if ($passwordEncryptedWithHashing
    && verify($password, $userArray["$cfgDbPasswordfield"]) == false) {
    // password is wrong
    $phpSP_message = $strPwFalse;
    include($cfgProgDir . "interface.php");
    exit;
}

if (isset($userArray["$cfgDbUserLevelfield"])
    && !empty($cfgDbUserLevelfield)) {
    $userLevel = stripslashes($userArray["$cfgDbUserLevelfield"]);
}

if ((isset($requiredUserLevel)
        && !empty($requiredUserLevel[0])) || isset($minUserLevel)) {
    // check for required user level and minimum user level
    if (!isset($userArray["$cfgDbUserLevelfield"])) {
        // check if column (as entered in the configuration file) exist in database
        $phpSP_message = $strNoUserLevelColumn;
        include($cfgProgDir . "interface.php");
        exit;
    }
    if (empty($cfgDbUserLevelfield)
        || (!is_in_array($userLevel, @$requiredUserLevel)
            && (!isset($minUserLevel)
                || empty($minUserLevel)
                || $userLevel < $minUserLevel))) {

        // this user does not have the required user level
        $phpSP_message = $strUserNotAllowed;
        include($cfgProgDir . "interface.php");
        exit;
    }
}

if (isset($userArray["$cfgDbUserIDfield"])
    && !empty($cfgDbUserIDfield)) {
    $ID = stripslashes($userArray["$cfgDbUserIDfield"]);
}
