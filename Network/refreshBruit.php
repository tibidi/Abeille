<?php

    /***
     *
     *
     */

    include_once __DIR__."/../../../core/php/core.inc.php";
    include_once("../core/class/AbeilleTools.class.php");
    include_once("../core/config/Abeille.config.php");

    // Je ne filtre pas sur les équipements qur batterie car j ai eu une réponse d'un interrupteur mural xiaomi. En les reveillant ca se trouve ils repondent.
    $eqLogics = Abeille::byType('Abeille');
    foreach ($eqLogics as $eqLogic) {
        if ( ($_GET['device'] == $eqLogic->getLogicalId()) || ($_GET['device'] == "All") ) {
            list($dest, $address) = explode('/', $eqLogic->getLogicalId());
            if ( strlen($address) == 4 ) {
                Abeille::publishMosquitto( queueKeyAbeilleToCmd, priorityUserCmd, "Cmd".$dest."/".$address."/managementNetworkUpdateRequest", "" );
                sleep(5);
            }
        }
    }
?>
