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

// check login with Data

// Check if secure.php has been loaded correctly
if ( !defined("LOADED_PROPERLY") || isset($_GET['cfgProgDir']) || isset($_POST['cfgProgDir'])) {
        echo "Parsing of phpSecurePages has been halted!";
        exit();
        }

$numLogin = count($cfgLogin);
$userFound = false;
// check all the data input
for ($i = 1; $i <= $numLogin; $i++) {
        if ($cfgLogin[$i] != '' && $cfgLogin[$i] == $login) {
                // user found --> check password
                if ($cfgPassword[$i] == '' || $cfgPassword[$i] != $password) {
                        // password is wrong
                        $phpSP_message = $strPwFalse;
                        include($cfgProgDir . "objects/logout.php");
                        include($cfgProgDir . "interface.php");
                        exit;
                        }
                $userFound = true;
                $userNr = $i;
                }
        }
if ($userFound == false) {
        // user is wrong
        $phpSP_message = $strUserNotExist;
        include($cfgProgDir . "objects/logout.php");
        include($cfgProgDir . "interface.php");
        exit;
        }
$userLevel = $cfgUserLevel[$userNr];

if ( ( isset($requiredUserLevel) && !empty($requiredUserLevel[0]) ) || isset($minUserLevel) ) {
        // check for required user level and minimum user level
        if ( !is_in_array($userLevel, @$requiredUserLevel) && ( !isset($minUserLevel) || empty($minUserLevel) || $userLevel < $minUserLevel ) ) {
                // this user does not have the required user level
                $phpSP_message = $strUserNotAllowed;
                include($cfgProgDir . "interface.php");
                exit;
}        }        
$ID = $cfgUserID[$userNr];
?>
