<legend><i class="fas fa-table"></i> {{Mes Abeilles}}</legend>
<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>

<?php
    $NbOfZigatesON = 0; // Number of enabled zigates
    // $eqAll = array(); // All equipments, sorted per zigate
    for ( $i=1; $i <= maxNbOfZigate; $i++ ) {
        if ( config::byKey('AbeilleActiver'.$i, 'Abeille', 'N') != 'Y' )
            continue; // This Zigate is not enabled

        $NbOfZigatesON++;
        if ( Abeille::byLogicalId( 'Abeille'.$i.'/0000', 'Abeille') ) {
            echo '<label style="margin: 10px 0px 0px 10px">Réseau Abeille'.$i.'</label>';
        }
        echo "&nbsp&nbsp&nbsp";
        echo '<span class="cursor" id="bt_include'.$i.'" title="Inclusion: clic sur le plus pour mettre la zigate en inclusion."><i class="fas fa-plus-circle" style="font-size:160%;color:green !important;"></i></span>';
        echo "&nbsp&nbsp&nbsp";
        echo '<span class="cursor" id="bt_include_stop'.$i.'" title="Inclusion: clic sur le moins pour arreter le mode inclusion."><i class="fas fa-minus-circle" style="font-size:160%;color:red !important;"></i></span>';
        echo "&nbsp&nbsp&nbsp";
        echo '<span class="cursor" onclick="createRemote('.$i.')" title="Clic pour créer une télécommande virtuelle."><i class="fas fa-gamepad" style="font-size:160%;color:orange !important;"></i></span>';

        /* Remove equipments from Jeedom only */
        echo '<a onclick="removeBeesJeedom('.$i.')" class="btn btn-warning btn-xs" style="margin-top: -10px; margin-left:15px" title="Supprime les équipement(s) sélectionné(s) de Jeedom uniquement.">{{Supprimer de Jeedom}}</a>';

        /* Exclude feature */
        echo '<a onclick="removeBees('.$i.')" class="btn btn-warning btn-xs" style="margin-top:-10px; margin-left:8px" title="Demande aux équipement(s) sélectionné(s) de sortir du réseau.">{{Exclure}}</a>';

        $port = config::byKey('AbeilleSerialPort'.$i, 'Abeille', '');

        /* Monitoring feature */
        echo '<a onclick="monitorIt('.$i.', \''.$port.'\')" class="btn btn-warning btn-xs" style="margin-top:-10px; margin-left:8px" title="Surveillance des messages vers/de l\'équipement sélectionné. Sortie dans \'AbeilleMonitor\'.">{{Surveiller}}</a>';

        if (isset($dbgDeveloperMode) && ($dbgDeveloperMode == TRUE)) {
            echo '<br>';

            /* Set timeout on selected equipements */
            echo '<a onclick="setBeesTimeout('.$i.')" class="btn btn-primary btn-xs" title="Permet de modifier le timeout pour les équipement(s) sélectionné(s).">{{Timeout}}</a>';
        }

		echo '<div class="eqLogicThumbnailContainer">';
        $dir = dirname(__FILE__) . '/../../images/';
        $files = scandir($dir);
		foreach ($eqPerZigate[$i] as $eqId) {
            $eqLogic = eqLogic::byId($eqId);
            displayBeeCard($eqLogic, $files, $i);
            // $eq = array();
            // $eq['id'] = $eqLogic->getId();
            // $eqIdList[] = $eq;
        }
        echo '<script>var js_eqZigate'.$i.' = \''.json_encode($eqPerZigate[$i]).'\';</script>';
        echo ' </div>';
    }

    if ($NbOfZigatesON == 0) { // No Zigate to display. UNEXPECTED !
        echo "<div style=\"background: #e9e9e9; font-weight: bold; padding: .2em 2em;\"><br>";
            echo "<span style=\"color:red\">";
                echo "Aucune Zigate n'est activée !";
            echo "</span><br>";
            echo "Veuillez aller à la page de configuration pour corriger.<br><br>";
        echo "</div>";
    }
?>

