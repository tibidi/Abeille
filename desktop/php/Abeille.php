<?php
    if(0){
        if (!isConnect('admin')) {
            throw new Exception('{{401 - Accès non autorisé}}');
        }
    }

    /* Developers debug features & PHP errors */
    require_once __DIR__.'/../../core/config/Abeille.config.php';
    if (file_exists(dbgFile)) {
        $dbgDeveloperMode = true;
        $dbgConfig = json_decode(file_get_contents(dbgFile), true);
        if (isset($dbgConfig["defines"])) {
            $arr = $dbgConfig["defines"];
            foreach ($arr as $idx => $value) {
                if ($value == "Tcharp38")
                    $dbgTcharp38 = true;
            }
        }
        echo '<script>var js_dbgDeveloperMode = '.$dbgDeveloperMode.';</script>'; // PHP to JS
        /* Dev mode: enabling PHP errors logging */
        error_reporting(E_ALL);
        ini_set('error_log', __DIR__.'/../../../../log/AbeillePHP.log');
        ini_set('log_errors', 'On');
    }

    if (isset($_GET['id']) && is_numeric($_GET['id'])) { // If 'id' is set to number, let's redirect to 'AbeilleEq' page
        $uri = parse_url($_SERVER['REQUEST_URI']);
        // Replace "p=Abeille" by "p=AbeilleEq"
        $newuri = str_replace("p=Abeille", "p=AbeilleEq", $uri['query']);
        $newuri = str_replace("&ajax=1", "", $newuri); // Required since core v4.1
        header("Location: index.php?".$newuri);
        exit();
    }

    /* The following part is executed only if no equipment selected (no id) */
    include '005_AbeilleFunctionPart.php';

    sendVarToJS('eqType', 'Abeille');
    $eqLogics = eqLogic::byType('Abeille');
    /* Creating a per Zigate list of eq ids.
       For each zigate, the first eq is the zigate.
       $eqPerZigate[zgNb][0] => id for zigate
       $eqPerZigate[zgNb][1] => id for next eq... */
    $eqPerZigate = array(); // All equipements id per zigate
    foreach ($eqLogics as $eqLogic) {
        $eqLogicId = $eqLogic->getLogicalId(); // Ex: 'Abeille1/0000'
        list($eqNet, $eqAddr) = explode( "/", $eqLogicId);
        $zgNb = hexdec(substr($eqNet, 7)); // Extracting zigate number from network
        $eqId = $eqLogic->getId();
        if ($eqAddr == "0000") {
            if (isset($eqPerZigate[$zgNb]))
                array_unshift($eqPerZigate[$zgNb], $eqId);
            else
                $eqPerZigate[$zgNb][] = $eqId;
        } else
            $eqPerZigate[$zgNb][] = $eqId;
    }
    // if (isset($dbgConfig))
    //     logDebug("eqPerZigate=".json_encode($eqPerZigate)); // In dev mode only
    $parametersAbeille = AbeilleTools::getParameters();

    $outils = array(
        'health'    => array( 'bouton'=>'bt_healthAbeille',         'icon'=>'fa-medkit',        'text'=>'{{Santé}}' ),
        'netList'   => array( 'bouton'=>'bt_network',               'icon'=>'fa-sitemap',       'text'=>'{{Réseau}}' ),
        'net'       => array( 'bouton'=>'bt_networkAbeille',        'icon'=>'fa-map',           'text'=>'{{Network Graph}}' ),
        'graph'     => array( 'bouton'=>'bt_graph',                 'icon'=>'fa-flask',         'text'=>'{{Graph}}' ),
        'compat'    => array( 'bouton'=>'bt_supportedEqList',       'icon'=>'fa-align-left',    'text'=>'{{Compatibilite}}' ),
        'ota'       => array( 'bouton'=>'bt_Ota',                   'icon'=>'fa-paperclip',     'text'=>'{{Mise-à-jours OTA}}' ),
        'support'   => array( 'bouton'=>'bt_supportPage',           'icon'=>'fa-medkit',        'text'=>'{{Support}}' ),
    );
?>

<!-- For all modals on 'Abeille' page. -->
<div class="row row-overflow" id="abeilleModal">
</div>

<div class="row row-overflow">
	<form action="plugins/Abeille/desktop/php/AbeilleFormAction.php" method="post">

        <!-- Barre d outils horizontale  -->
		<div class="col-xs-12 eqLogicThumbnailDisplay">

        <!-- Icones de toutes les modales  -->
        <?php include '010_AbeilleGestionPart.php'; ?>

        <!-- Icones de toutes les abeilles  -->
        <?php include '020_AbeilleMesAbeillesPart.php'; ?>

        <!-- Groups management  -->
        <?php include 'Abeille-Groups.php'; ?>

        <!-- Gestion des ghosts / remplacement d equipements  -->
        <?php include '050_AbeilleRemplacementPart.php'; ?>

        <!-- Gestion des ReHome / migration d equipements  -->
        <?php include '060_AbeilleReHomePart.php'; ?>

        <?php include '070_AbeilleReplaceZigatePart.php'; ?>

        <?php if (isset($dbgDeveloperMode)) { ?>
        <legend><i class="fa fa-cogs"></i> {{Visible en MODE DEV UNIQUEMENT}}</legend>
        <div class="form-group" style="background-color: rgba(var(--defaultBkg-color), var(--opacity)) !important; padding-left: 10px">

            <!-- Gestion des groupes et des scenes  -->
            <?php include '025_AbeilleNEPart.php'; ?>

            <!-- Gestion des groupes et des scenes  -->
            <?php include '040_AbeilleScenePart.php'; ?>

        </div>
        <?php } ?>

    </div> <!-- Fin - Barre d outils horizontale  -->

	</form>
</div>

<!-- Scripts -->
<?php include '200_AbeilleScript.php'; ?>
