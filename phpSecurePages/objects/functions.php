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

// functions and libraries
define('FUNCTIONS_LOADED', true);

function admEmail() {
        // create administrators email link
        global $admEmail;
        return("<A HREF='mailto:$admEmail'>$admEmail</A>");
        }

function is_in_array($needle, $haystack) {
        // check if the value of $needle exist in array $haystack
        if ($needle && $haystack) {
                return(in_array($needle, $haystack));
                }
        else return(false);
        }
?>
