<?php
    /*
     * AbeilleCmd daemon
     *
     * subscribe to Abeille topic and receive message sent by AbeilleParser.
     *
     */

    include_once __DIR__.'/../config/Abeille.config.php';

    /* Developers options */
    if (file_exists(dbgFile)) {
        /* Dev mode: enabling PHP errors logging */
        error_reporting(E_ALL);
        ini_set('error_log', __DIR__.'/../../../../log/AbeillePHP.log');
        ini_set('log_errors', 'On');
    }

    include_once __DIR__.'/AbeilleLog.php';
    logSetConf("AbeilleCmd.log", true);
    logMessage('info', '>>> Démarrage d\'AbeilleCmd');

    include_once __DIR__.'/../../../../core/php/core.inc.php';
    include_once __DIR__.'/../class/AbeilleMsg.php';
    include_once __DIR__.'/../class/AbeilleCmdQueue.class.php';
    include_once __DIR__.'/AbeilleOTA.php';

    // ***********************************************************************************************
    // MAIN
    // ***********************************************************************************************
    // exemple d appel
    // php AbeilleCmd.php debug
    //check already running

    $config = AbeilleTools::getParameters();
    $running = AbeilleTools::getRunningDaemons();
    $daemons = AbeilleTools::diffExpectedRunningDaemons($config, $running);
    logMessage('debug', 'Daemons status: '.json_encode($daemons));
    if ($daemons["cmd"] > 1){
        logMessage('error', 'Le démon est déja lancé ! '.json_encode($daemons));
        exit(3);
    }

    /* Any device to monitor ?
       It is indicated by 'monitor' key in Jeedom 'config' table. */
    $monId = config::byKey('monitor', 'Abeille', false);
    if ($monId !== false) {
        $eqLogic = eqLogic::byId($monId);
        if (!is_object($eqLogic)) {
            logMessage('debug', 'Bad ID to monitor: '.$monId);
        } else {
            list($net, $addr) = explode( "/", $eqLogic->getLogicalId());
            $ieee = $eqLogic->getConfiguration('IEEE', '');
            $dbgMonitorAddr = $addr;
            $dbgMonitorAddrExt = $ieee;
            logMessage("debug", "Device to monitor: ".$eqLogic->getHumanName().', '.$addr.'-'.$ieee);
            include_once __DIR__.'/AbeilleMonitor.php'; // Tracing monitor for debug purposes
        }
    }

    // Reading available OTA firmwares
    otaReadFirmwares();

    $queueCtrlToCmd = msg_get_queue($abQueues["ctrlToCmd"]["id"]);
    $queueCtrlToCmdMax = $abQueues["ctrlToCmd"]["max"];

    function cmdLog($loglevel = 'NONE', $message = "", $isEnable = 1) {
        if ($isEnable == 0)
            return;
        logMessage($loglevel, $message);
    }

    try {
        $AbeilleCmdQueue = new AbeilleCmdQueue($argv[1]);

        // $fromAssistQueue = msg_get_queue(queueKeyAssistToCmd);
        // $rerouteNet = ""; // Rerouted network if defined (ex: 'Abeille1')

        $last = 0;
        while (true) {
            /* Treat Zigate statuses (0x8000 cmd) coming from parser */
            $AbeilleCmdQueue->traiteLesAckRecus();

            $AbeilleCmdQueue->timeOutSurLesAck();

            // Traite toutes les commandes zigate en attente
            $AbeilleCmdQueue->processCmdQueueToZigate();

            /* Performing msg rerouting from 'EQ assistant' */
            // $max_msg_size = 2048;
            // $msg_type = NULL;
            // if (msg_receive($fromAssistQueue, 0, $msg_type, $max_msg_size, $msg, true, MSG_IPC_NOWAIT) == true) {
            //     logMessage('debug', "Received=".json_encode($msg));
            //     if ($msg['type'] == 'reroute') {
            //         $rerouteNet = $msg['network'];
            //         /* TODO: Tcharp38: Can be optimized */
            //         $zgNb = str_replace('Abeille', '', $rerouteNet);
            //         $AbeilleCmdQueue->zigateAvailable[$zgNb] = 0;
            //         logMessage('debug', "'".$rerouteNet."' messages must be rerouted");
            //     } else if ($msg['type'] == 'reroutestop') {
            //         logMessage('debug', "Stopping '".$rerouteNet."' msg rerouting.");
            //         $rerouteNet = "";
            //         /* TODO: Tcharp38: Can be optimized */
            //         $zgNb = str_replace('Abeille', '', $rerouteNet);
            //         $AbeilleCmdQueue->zigateAvailable[$zgNb] = 1;
            //     } else if ($msg['type'] == 'msg') {
            //         logMessage('debug', $rerouteNet.", rerouting: ".$msg['msg']);
            //         /* TODO: Tcharp38: Can be optimized */
            //         $zgNb = str_replace('Abeille', '', $rerouteNet);
            //         if (config::byKey('AbeilleActiver'.$zgNb, 'Abeille', 'N') == 'Y' ) {
            //             $sp = config::byKey('AbeilleSerialPort'.$zgNb, 'Abeille', '1', 1);
            //             $f = fopen($sp, "w");
            //             fwrite($f, pack("H*", $msg['msg']));
            //             fclose($f);
            //         }
            //     }
            // }
            /* Checking if there is any control message for Cmd */
            if (msg_receive($queueCtrlToCmd, 0, $msgType, $queueCtrlToCmdMax, $jsonMsg, false, MSG_IPC_NOWAIT, $errorCode) == true) {
                logMessage('debug', "queueCtrlToCmd=".$jsonMsg);
                $msg = json_decode($jsonMsg, true);
                if ($msg['type'] == 'readOtaFirmwares') {
                    otaReadFirmwares(); // Reread available firmwares
                }
            } else if ($errorCode != 42) { // 42 = No message
                logMessage('debug', '  msg_receive(queueCtrlToCmd) ERROR '.$errorCode);
            }

            $AbeilleCmdQueue->collectAllOtherMessages();

            // Recuperes tous les messages en attente sur timer
            $AbeilleCmdQueue->execTempoCmdAbeille();

            /* Display queues status every 30sec */
            if ((time() - $last) > 30 ) {
                $AbeilleCmdQueue->displayStatus();
                $last = time();
            }

            // Libère le CPU
            time_nanosleep(0, 10000000); // 1/100s
        }
    }
    catch (Exception $e) {
        logMessage('debug', 'error: '. json_encode($e->getMessage()));
    }

    unset($AbeilleCmdQueue);
    logMessage('info', '<<< Fin du démon \'AbeilleCmd\'');
?>
