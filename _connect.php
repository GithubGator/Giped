<?php
// Connection locale
mysqli_report(MYSQLI_REPORT_OFF);
//mysqli_report(MYSQLI_REPORT_ERROR);
if (!isset ($maConnection)) {
    $maConnection = new mysqli("127.0.0.1", "root", "baxe6Equ", "Giped2016");
    if (mysqli_connect_errno()) {
        // erreur à la connexion
        printf("Echec de la connexion: %s\n", mysqli_connect_error());
        exit();
    }

    if (!$maConnection->query("SET NAMES 'utf8'")) {
        echo("Erreur - SQLSTATE : ".$maConnection->sqlstate);
    }

    //echo("<br>Information serveur : version ". $maConnection->server_version." (connection ".$maConnection->host_info.")");
    //printf("<br>Jeux de caract&egrave;res : ", $maConnection->character_set_name());
//mysqli_report(mysqli_REPORT_ERROR);
//mysqli_report(mysqli_REPORT_INDEX);
//mysqli_report(mysqli_REPORT_ALL);
//mysqli_debug("d:t:0,/tmp/client.trace");
}
if (!isset ($maConnection1)) {
    $maConnection1 = new mysqli("127.0.0.1", "root", "baxe6Equ", "Giped2011");
    if (mysqli_connect_errno()) {
        // erreur à¡¬a connexion
        printf("Echec de la connexion: %s\n", mysqli_connect_error());
        exit();
    }

    if (!$maConnection1->query("SET NAMES 'utf8'")) {
        echo("Erreur - SQLSTATE : ".$maConnection1->sqlstate);
    }

    //echo("<br>Information serveur : version ". $maConnection->server_version." (connection ".$maConnection->host_info.")");
    //printf("<br>Jeux de caract&egrave;res : ", $maConnection->character_set_name());
//mysqli_report(mysqli_REPORT_ERROR);
//mysqli_report(mysqli_REPORT_INDEX);
//mysqli_report(mysqli_REPORT_ALL);
//mysqli_debug("d:t:0,/tmp/client.trace");
}
?>