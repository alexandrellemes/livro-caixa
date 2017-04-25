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

// check if user is coming from an allowed IP address
$ip_array_count = count($allowed_addr);
$remote_ip = explode(".", $_SERVER['REMOTE_ADDR']);

for ($t = 0; $t < count($allowed_addr); $t++) {
        $permitted[$t] = 1;
        for($i = 0; $i < sizeof($allowed_addr[$t]); $i++) {
                if($remote_ip[$i] != $allowed_addr[$t][$i]){
                        $permitted[$t] = 0;
                        }
                }
        }

$permitted_sum=array_sum($permitted);
if($permitted_sum<1)    {
        // IP address not allowed
        $phpSP_message = $strNoAccess;
        include($cfgProgDir . "interface.php");
        exit;
        }
?>
