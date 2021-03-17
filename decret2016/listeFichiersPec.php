<?php
/**************************************************************************************************************/
/*  	Ce script liste les fichiers pr�sents dans le r�pertoire sftp/pec qui ont �t� d�pos�s soit par la PEC                */
/* 	Ce sont alors des Accus�s de r�ception                                                                                                     */
/*	les AVL, AVD, ANL AND renseignent la table appels_pec     (trait� par un autre script listePEC.php)              */
/* 	soit par les CG  via la PEC ou FAST (apr�s copie entre r�pertoire SPTP)                                                      */
/*       Ce sont alors des fichiers de retour de notice 2   ou des fichiers de remont�e des donn�es de l'ONED             */
/* 	Une seconde action d�place les fichiers re�us vers un le sous r�pertoire d�di� � chaque CG                             */
/**************************************************************************************************************/
//session_start();
require_once("./_dbclass.php");
require_once("./_connect.php"); // Param�tres de connection et... connection
require_once("./_fonctions.php");

$connectionPEC = ssh2_connect('sftp.giped.gouv.fr', 22);
if (!$connectionPEC) die('�chec de la connexion');

//if (ssh2_auth_password($connectionPEC, 'pec', 'hfaM0jrk')) {
if (ssh2_auth_password($connectionPEC, 'root', 'g63bbb75017p')) {
    //echo "Identification r�ussi !\n";
} else {
    die('Echec de l\'identification...');
}
/*
// Create our SFTP resource
if ($sftp = ssh2_sftp($connectionPEC)) {
    echo" SFTP Ok";
}else{
    throw new Exception('Unable to create SFTP connection.');
}

*/

$anneeEnCours = date('Y');


$stream = ssh2_exec($connectionPEC , 'ls -o --full-time /sftp/pec/upload/*.*', false);
$tabFic=array();
stream_set_blocking($stream, true);

$i=0;

while($line = fgets($stream)) {
    $i++;
    $prefix=false; // pour l'aube
    flush();
    $debutFile = strpos($line,"/sftp/pec");

    $debutDate = strpos($line,$anneeEnCours);
    if($debutDate===false){
        $anneeEnCours= "".($anneeEnCours-1);
        $debutDate = strpos($line,$anneeEnCours);
    }

    $posExtension = strpos($line,'.zip');
    $debutCGId = strpos($line,' /sftp/pec/upload/CG');
    $finCGId = strpos($line,'_ONED');

    if($debutCGId > 1 && $finCGId >1){
        $i++;
        $tabFic[$i]['file']= rtrim(substr($line, $debutFile));
        $tabFic[$i]['date']=substr($line, $debutDate,19);
        //echo $line."<br />";

        // traitement particulier pour remont�e ONED
        $tabFic[$i]['type']="ONED";
        $tabFic[$i]['Annee'] = substr($line,$finCGId+5,$posExtension-$finCGId-5);
        $tabFic[$i]['CG'] = substr($line, $debutCGId+18,$finCGId-$debutCGId-18);
        echo "<pre>";
        print_r($tabFic);
        echo "</pre>";


    }
    //
}


//echo "<br />";
//echo "stream:".stream_get_contents($stream);
fclose($stream);
/*
 echo "<pre>";
 print_r ($tabFic);
 echo "</pre>";

 exit();
*/

foreach($tabFic as $notification){
    echo $notification['date']." ***".$notification['Annee']."*** ".$notification['type']." ".$notification['CG']."<br />";
    //logtmp("listePEC:".$notification['type'] ,1);
    if (isset($notification['type']) && $notification['type'] == "ONED"){


        // on renseigne la date de r�ception du retour de cette IP
        /*
        $sql = "update `appels_pec` SET `RetourIp`='".$notification['date']."' ";
        $sql .= " where  `id_appel` = '".$notification['idAppel']."' LIMIT 1";
        //echo $sql ."<br />";
        $result = $maConnection->query($sql);



        // On renseigne aussi le retour dans la table appels

        // Pour ne  renseigner que le bon champ ( d�partement principal ou secondaire) on lance 2 requ�tes seule une sera
        /*
        $sql = "update `appels` SET `d_appel_retour`='".$notification['date']."' ";
        $sql .= " where  `id_appel` = '".$notification['idAppel']."' and id_adr_dept = '".$idDept ."' LIMIT 1";
        //echo $sql ."<br />";
        $result = $maConnection->query($sql);

        $sql = "update `appels` SET `d_appel_retour2`='".$notification['date']."' ";
        $sql .= " where  `id_appel` = '".$notification['idAppel']."' and id_adr_dept2 = '".$idDept ."' LIMIT 1";
        //echo $sql ."<br />";
        $result = $maConnection->query($sql);
        */



        // On copie le fichier en local

        $filename="./tmp/".$notification['CG']."_ONED".$notification['Annee'].".zip";
        ssh2_scp_recv($connectionPEC, $notification['file'], $filename);
        echo $notification['file']." copie vers :".$filename ;"<br />";
        // Traitement du fichier

        $zip = new ZipArchive();

        $res= $zip->open($filename, ZIPARCHIVE::CREATE);
        if ($res!==TRUE) {
            exit("Impossible d'ouvrir <$filename>\n");
        }else{
            //echo "OK : fichier : ". $filename." ->".$zip->numFiles."<br />";
        }

        for($i = 0; $i < $zip->numFiles; $i++) {
            if($zip->getNameIndex($i) != "message.xml"){
                //$zip->extractTo('path/to/extraction/', array($zip->getNameIndex($i)));
                $path_parts= pathinfo($zip->getNameIndex($i));

                echo $notification['idAppel']." :".($zip->getNameIndex($i))."->".$path_parts['extension']."<br />";
                // renommer les fichiers avant leur extraction pour respecter la nomenclature
                $zip->renameIndex($i,$notification['CG']."_RE_IP".$notification['idAppel'].".".$path_parts['extension']);
                echo $notification['CG']."<br />";
                // extraction des fichiers
                $zip->extractTo('./tmp/', array($zip->getNameIndex($i)));

            }
        }


        $zip->close();
        // suppression du fichier zip temporaire (une copie existe sur le serveur-voir ci-apr�s)

        unlink($filename);



        // Sur le serveur SFTP : on d�place le fichier vers le r�pertoire du CG

        /*
            $commande = 'mv '.$notification['file'].'  '. str_replace("/upload/","/upload/".$notification['CG']."/",$notification['file']);
            //echo $commande;
            logtmp("listePEC:".$commande,1);

            $stream = ssh2_exec($connectionPEC ,$commande, false);
            $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

            // Enable blocking for both streams
            stream_set_blocking($errorStream, true);
            stream_set_blocking($stream, true);

            //echo "Output: " . stream_get_contents($stream);
            logtmp("Output: " . stream_get_contents($stream),1);
            logtmp("Error: " . stream_get_contents($errorStream),1);
            //echo "Error: " . stream_get_contents($errorStream);
            //echo "<br/>**************************************************************<br/>";

            fclose($errorStream);
            fclose($stream);

            */
        //echo $notification['file']."<br />";
    }
}


?>