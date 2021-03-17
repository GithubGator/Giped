<?php
//***********************************************************************************
//   Parcours le fichier XML original, converti les formats des dates, construit le fichier XML corrigé et le CSV correspondant
//
//
//***********************************************************************************

require_once("../_session.php");
require_once("../_dbclass.php");
require_once("../_connect.php"); // Paramètres de connection et... connection
require_once("../_fonctions.php");
require_once("../_classes/calc_dates.php");
require_once("../_classes/phonex.class.php");
require_once("../_classes/cleID.class.php");
require_once("../_classes/cleONPE.inc.php"); // Cles nécessaire à la seconde et troisième anonymisation
require_once("./inc/fonctions_controle.php");


header("Content-type: text/html; charset=utf-8");
setlocale(LC_ALL, 'fr_FR.utf8');
//error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Europe/Paris');

error_reporting(E_ALL ^ E_NOTICE);
//	import_request_variables("GP","e_"); *// DISABLED IN PHP 7
if(isset($_GET['file'])) $e_file=$_GET['file'];
if(isset($_GET['debug'])) $e_debug=$_GET['debug'];
if(isset($_GET['test'])) $e_test=$_GET['test'];

ini_set('display_errors', 0);   //Sinon emp�che l'enregistremnt du pdf
ini_set('max_execution_time','0');
ini_set('memory_limit','-1');

$error = array();
// Objet dom XML pour valider le schéma
$dom = new DomDocument();


if($e_test==1){
    $test=true;  // pour effectuer des tests sans émettre de mail ni déplacer le fichier
}else{
    //echo "En prod";
    $test = false;
}
if($e_debug==1){
    $debug=true;  // pour effectuer des tests sans generer le pdf
}else{
    //echo "En prod";
    $debug = false;
}


// pour profilage
$tempo1=0;
$tempo2=0;
$tempo3=0;
$tempo4=0;
$tempo5=0;
$tempo6=0;
$nbInsert =0;

$time_start =0;
$time_end = 0;

$limiteInf=220; //pour les sauts de page forcés

$trace="";
$cptErreur=0;
$tabFichiers = array();
$anneeConcernee= "2011";
$tabDecision = array();
$flagDATDC=false;  // pour les dates de décès non conformes
$flagNUMDEPT = false; // pour le

//$folder = "/home/imports_dept";    //Prod
$folder = "./imports";    //Test


// initialisation tableau des scripts
require_once("./includes/scripts_inc.php");

// fichier csv
$separateur=";";
$csvstr="";
$tabCsvStr= array(); // pour la recherche de doublon
$nbCsvStr= array();

//fichier d'échange
$dumpLigne="";
$tabDumpLignes = array();

//$csvstr_administratif="";
//$tabCsvStrAdministratif= array(); // pour la recherche de doublon administratif
//$nbCsvStrAdministratif= array();

//$csvstr_judiciaire="";
//$tabCsvStrJudiciaire= array(); // pour la recherche de doublon judiciaire
//$nbCsvStrJudiciaire= array();



$wrapperName = "DONNEE";

$bDetail=false;
$nbMineurs=0;
$nbJeunesMajeurs=0;
$nbMineursTmp=0;
$nbJeunesMajeursTmp=0;
$tabAnonym = array();
$tabJeunes['all'] = array();
$tabJeunes[$anneeConcernee] = array();
$listeErreurs="";



// denombrement
$nbDatesFormatErrone= array();
$nbNumAnonymIncoherents = 0;
$nbDoublons = 0;
$nbLigneDoublons = 0;
$nbErreursDatesNaissances = 0;

$nbVariablesPresentes = array();
$nbVariablesNonVides = array();
$nbVariablesRenseignees = array();
$nbVariablesPertinentes = array();
$nbVariablesNonAttendues = array();
$nbVariablesReserveesMajeurs = array();
$nbVariablesReserveesMineurs = array();
$nbModalitesReserveesMajeurs = array();
$nbModalitesReserveesMineurs = array();
$nbTYPEV=array(1=>0,2=>0,3=>0);

// tableau des chaines de caractères
$tabDatesFormatErrone = array();
$tabErreursDatesNaissances = array();
$tabVariablesNonRenseignees = array();
$tabVariablesNonAttendues = array();
$tabVariablesReserveesMajeurs = array();
$tabVariablesReserveesMineurs = array();
$tabModalitesReserveesMajeurs = array();
$tabModalitesReserveesMineurs = array();
$tabNumAnonymIncoherents = array();
$tabDoublons = array();
$tabDoublonsAdministratifs = array();
$tabDoublonsJudiciaires = array();
$tabLignesSupprimees = array();

$dateTraitement = date("Y-m-d H:i:s");
$tabValues= array();



if(!isset ($e_file)){

    ?>


    <html xml:lang="fr" xmlns="http://www.w3.org/1999/xhtml" lang="fr">
    <head>




        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>
            Remont&eacute; des donn&eacute;es	</title>
        <!-- appel des css -->
        <link rel="stylesheet" href="styles/css_bleu/style_bleu.css" type="text/css" title="css_bleu" media="screen">
        <link rel="stylesheet" href="styles/css_bleu/style_print.css" type="text/css" title="css_bleu" media="print">
        <link rel="stylesheet" href="styles/style_xml2csv.css" type="text/css" title="css_bleu" media="screen">


        <script type="text/javascript">
            <!-- Remplacer entrÃ©e historique

            //-->
        </script>

    </head>

    <body>
    <!-- appels -->
    <div>
        <span><a name="Top"></a></span>
    </div>

    <div id="logoONPE"></div>

    <div id="main_pec" style="margin-top: 20px;">

        <div>  <!-- id = "formulaire" -->

            <br style="clear:both;" />
            <em>Test : pour effectuer des tests sans émettre de mail ni déplacer le fichier </em><br />
            <em>Debug : pour ne pas générer le pdf</em>


            <?php

            $dossier = opendir($folder);
            /*
            while ($Fichier = readdir($dossier)) {
              if ($Fichier != "." && $Fichier != ".." && $Fichier !="Backup") {
                $nomFichier = $folder."/".$Fichier;
                //echo $nomFichier."<BR>";
                $url = "ONPE_Xml2Csv.php?file=".$Fichier;
                echo "<a href='".$url."'>".$Fichier."</a> - (<a href='".$url."&test=1'>test</a>)</br>";
              }
            }
            */


            echo "<table>
				<tr>
					<th>Fichier</th>
					<th>Date</th>
					<th>Test</th>
					<th>Test + debug</th>
				</tr>
					
					
					";


            while ($Fichier = readdir($dossier)) {
                if ($Fichier != "." && $Fichier != ".." && $Fichier !="Backup") {
                    $tabFichiers[]=$Fichier;
                }
            }
            sort($tabFichiers);
            $h=0;
            foreach($tabFichiers as $fichier){
                $nomFichier = $folder."/".$fichier;
                //echo $nomFichier."<BR>";
                $url = "ONPE_Xml2Csv.php?file=".$fichier;
                //echo "<a href='".$url."'>".$fichier."</a> - (<a href='".$url."&test=1'>test</a>) - (<a href='".$url."&test=1&debug=1'>test+debug</a>) - ".date ("d/m/Y", filemtime($nomFichier))."</br>";
                $h++;

                if ($h%2 == 0)
                    echo "<tr class='impair'>\n";
                else
                    echo "<tr class='pair'>\n";

                echo "	<td><a href='".$url."'>".$fichier."</a></td>
					<td>".date ("d/m/Y", filemtime($nomFichier))."</td>
					<td><a href='".$url."&test=1'>test</a></td>
					<td><a href='".$url."&test=1&debug=1'>test+debug</a></td>
				</tr>
					
					
					";

            }

            closedir($dossier);

            echo "</table> ";
            ?>
        </div>

    </div> <!-- end div id="main_pec" -->


    <p id="footer_pec">
        <br style="clear:both;" />
        |&nbsp;<a href="mailto:informatique@giped.gouv.fr;">Contact</a>&nbsp;|
    </p>

    </body>
    </html>

    <?php

    exit();

}else{

    // Sauvegarde du ficher xml
    $dateJour=date("Y-m-d");
    //$csvfile="./".$Fichier."-".$dateJour.".csv";
    $xmlNewfile= "./imports/XML_transitoire/D2016_".substr($e_file,0,-3)."xml";
    //$csvfile= substr($e_file,0,-3)."csv";
    $csvfile= "./imports/csv_test/".substr($e_file,0,-3)."test.csv";
    //$csvfileVerif= "Verif_".substr($e_file,0,-3)."csv";

}



// nom du fichier
//$nomFic="ONED-035-20120702_2012.XML";

// Lecture du ficher xml
//$xmlfile="./imports/".$nomFic;

$xmlfile=$folder."/".$e_file;
// echo "Chargement du fichier XML";
//recherche de la date de ce fichier

$i=0;
$nbLignesTraitees=0; // nb lignes traitée
$nbLignesIni=0;

$xml = new XMLReader();

if(!$xml->open($xmlfile)){
    die("Failed to open input file.");
}
/*
$schemaFile = './decret.xsd';
echo $xml->setSchema($schemaFile);

exit();
*/
$xmlstr = "";
$xmlstr ="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
//$xmlstr .= "<SNATED xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"snated.xsd\">";
$xmlstr .= " 
	    <DECRET>
		<INTRO>
			<VERSIONXSD>2.01</VERSIONXSD>
			<DATEXPORT>".date('Y-m-d')."</DATEXPORT>
		</INTRO>
		";

$fp = fopen($xmlNewfile, 'w');
fputs($fp, $xmlstr);
fclose($fp);

$xmlstr= "";

//initialisation du fichier csv car ensuite écriture ligne par ligne
$csvstr.= "";
$fp = fopen($csvfile, 'w');
fputs($fp, $csvstr);
fclose($fp);







// Création de la structure du fichier CSV
$sql = "SELECT `NEWFIELD`, `Type`,`Nsp` FROM `NOMENCLATURE` WHERE 1  ORDER BY `NEWFIELD` ";
//echo $sql ."<br />";
$result  = ExecRequete($sql, $maConnection);
$code ="";
$tabCol = array();
$tabColNSP = array();
$tabColType = array();
$listeVariables = array();
$listeVariablesVerif = array();
$listeVariablesAbsentes = array();
$listeVariablesVides=array();
$listeVariablesToujoursNSP = array();

$listeColonnes="";
$listeColonnesVerif="";

$listeVariablesVerif[]="NUMANONYMDEP";  // initialisation
$listeColonnesVerif="NUMANONYMDEP,";

while ($record = $result->fetch_assoc()) {

    $listeColonnes .= $record['NEWFIELD'].",";
    // Gestion des Nsp

    $tabCol[$record['NEWFIELD']]="NULL";
    //$tabCol[$record['NEWFIELD']]="";
    $tabColNSP[$record['NEWFIELD']]=$record['Nsp'];
    $tabColType[$record['NEWFIELD']]=$record['Type'];

    $listeVariables[]=$record['NEWFIELD'];
    if($record['NEWFIELD'] != "NUMANONYM" && $record['NEWFIELD'] != "NUMANONYMANT"){
        $listeColonnesVerif .= $record['NEWFIELD'].",";
        $listeVariablesVerif[] = $record['NEWFIELD'];
    }
    $csvstr .= $record['NEWFIELD'].$separateur;
    $code.= 		"\n		case \"".$record['NEWFIELD']."\":
		
			if(\$val==\"\"){
				//echo \$cptErreur++.\": valeur '\".\$cle.\"' manquante <br />\";
			}else{
				//echo \$cptErreur++.\": valeur '\".\$cle.\"' non attendue <br />\";
			}
			break;		
		
		";

}
$csvstr .="DOUBLON";
$csvstr.= "\n";

$fp = fopen($csvfile, 'a');
fputs($fp, $csvstr);
fclose($fp);


$listeColonnes = substr($listeColonnes,0,(strlen($listeColonnes) - 1));
$listeColonnesVerif = substr($listeColonnesVerif,0,(strlen($listeColonnesVerif) - 1));

//echo "Liste des colonnes :". $listeColonnes."<br />";

$reqTest = " insert IGNORE into DONNEES_TMP (".$listeColonnes.",NUMANONYMDEP,Doublon,DateTraitement) values ";
$reqDebut = " insert IGNORE into DONNEES_TMP (".$listeColonnes.",NUMANONYMDEP,Doublon,DateTraitement) values ";

/*

echo $code;
exit();
*/
/*
echo "<pre>";
print_r($tabColNSP);
print_r($tabColType);
echo "</pre>";
exit();
*/


if(true){
    $cptNBFrat=0;
    while($xml->read()){
        if($xml->nodeType==XMLReader::ELEMENT && $xml->name == $wrapperName){
            if($nbLignesIni==100){
                //break; //simplement pendant la mise au point
            }

            $xmlstr .= "<DONNEE>";
            $res = Conso($tempo4,0);
            while($xml->read() && $xml->name != $wrapperName){
                if($xml->nodeType==XMLReader::ELEMENT){

                    $cle=$xml->name;
                    //$nbVariablesPresentes[$cle] =(isset($nbVariablesPresentes[$cle])) ? $nbVariablesPresentes[$cle]+1 : 1;
                    /*
                    if(isset($nbVariablesPresentes[$cle]))
                        $nbVariablesPresentes[$cle]++;
                    else
                        $nbVariablesPresentes[$cle]=1;
                    */
                    $val = $xml->readString();


                    // traitement des fichiers au format du décret de 2011
                    //convertSchema();

                    if($cle == "NBFRAT")
                        $cptNBFrat++;


                    switch ($cle) {

                        case "":
                            break;
                        case "NUMANONYM":
                            $tabAnonym[$cle]=$val;
                            $tabCol['NUMANONYMDEP']=$val; //peut-être redondant avec la ligne précédente mais nécessaire pour injection BDD
                        /*
                        echo $cle .":".$val."<br /> ";

                        if(strpos($val, "76551") >1)
                            echo $cle .":".$val."*********************************<br /> ";
                        */


                        case "NUMANONYMANT":

                            if(trim($val != '')){
                                // seconde anonymisation qu'il ne faudra pas mettre en oeuvre pour la DPJJ - A faire
                                $valTemp = substr(hash('sha1',substr((string)$val,0,20).$cleSecreteONPE1),0,20).substr(hash('sha1',substr((string)$val,20,20).$cleSecreteONPE1),0,20);
                                if(false){
                                    // troisième anonymisation
                                    $newVal = substr(hash('sha1',substr((string)$valTemp,0,20).$cleSecreteONPE2),0,20).substr(hash('sha1',substr((string)$valTemp,20,20).$cleSecreteONPE2),0,20);
                                }else{
                                    // On se conserve que la seconde dans l'attente d'un traitement global de l'historique
                                    $newVal = $valTemp;
                                }

                                if($newVal == '694d3d6ed5c0fc7bf26e7c80b17ec7ea05674348'){
                                    //echo $val. "->".$newVal;
                                    //exit();
                                    $trace = true;
                                }else{
                                    $trace = false;
                                }
                                $tabCol[$cle]=$newVal;
                            } else {
                                $tabCol[$cle]= "ffffffffffffffffffffffffffffffffffffffff";
                            }
                            //echo $cle .":".$val."<br /> ";
                            //echo $tabCol["NUMANONYM"];

                            /*
                            if (!in_array ($tabCol[$cle],$tabJeunes['all']))
                                $tabJeunes['all'][]=$tabCol[$cle];
                            */
                            break;

                        /*
                        echo "<pre>";
                        echo "  ".$cle."-> ".$val;
                        echo "</pre>";
                        */

                        case "ANSPERE":
                        case "ANSMERE":
                        case "ANSA1":
                        case "ANSA2":
                            $tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
                            //if($tabCol[$cle] == "1850" || $tabCol[$cle] == "1880" || (strlen($tabCol[$cle]) != 4 && trim($tabCol[$cle]) !="") || ( trim($tabCol[$cle]) !="" && substr($tabCol[$cle],0,2) != '99' &&  substr($tabCol[$cle],0,2) != '20' && substr($tabCol[$cle],0,2) != '19') ){
                            if($tabCol[$cle] < 1920 && trim($tabCol[$cle]) !="" ){
                                //echo "Avant DateFormatErrone :".$cle."| ".$tabCol[$cle]."<br />";
                                DatesFormatErrone($cle,$val," Valeur non conforme ");
                                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' erron&eacute;e : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                                //$tabCol[$cle]=""; // valeur manquante

                            }

                            break;

                        case "DATIP":
                        case "DATSIGN":
                        case "DATJE":

                        case "DATDECPE":
                        case "DATPPE":
                        case "DATDEB":
                        case "DATDECAP":
                        case "DATFIN":
                        case "NOTIFEVAL":
                        case "FINEVAL":
                            $tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
                            //$buf = $tabCol[$cle];

                            // traitement des dates au format jj/mm/AAAA
                            if(preg_match( '`^\d{2}/\d{2}/\d{4}$`' , $tabCol[$cle] )){
                                DatesFormatErrone($cle,$val,"JJ/MM/AAAA au lieu de AAAA-MM-JJ  ");
                                //echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                                $parts = explode("/", $tabCol[$cle] );
                                $tabCol[$cle] = $parts[2]."-".$parts[1];
                                $flagDATDC=true;

                            }else if(preg_match( '`^\d{2}-\d{2}-\d{4}$`' , $tabCol[$cle] )){
                                DatesFormatErrone($cle,$val,"JJ-MM-AAAA au lieu de AAAA-MM-JJ  ");
                                //echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                                $parts = explode("-", $tabCol[$cle] );
                                $tabCol[$cle] = $parts[2]."-".$parts[1]."-".$parts[2];
                                $flagDATDC=true;
                            }else if(preg_match( '`^\d{4}/\d{2}/\d{2}$`' , $tabCol[$cle] )){
                                DatesFormatErrone($cle,$val,"AAAA/MM/JJ au lieu de AAAA-MM-JJ  ");
                                //echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                                $parts = explode("/", $tabCol[$cle] );
                                $tabCol[$cle] = $parts[0]."-".$parts[1]."-".$parts[2];
                                $flagDATDC=true;
                            }
                            //echo $buf."-> ".$tabCol[$cle]."<br /> ";

                            if( $tabCol[$cle] != ""  && substr($tabCol[$cle],0,2) != '99' &&  substr($tabCol[$cle],0,2) != '20' && substr($tabCol[$cle],0,2) != '19')
                                DatesFormatErrone($cle,$val," Valeur non conforme ");

                            break;






                        case "DATDCPERE":
                        case "DATDCMERE":

                            $tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
                            //$buf = $tabCol[$cle];

                            // traitement des dates au format jj/mm/AAAA
                            if(preg_match( '`^\d{2}/\d{2}/\d{4}$`' , $tabCol[$cle] )){
                                DatesFormatErrone($cle,$val,"JJ/MM/AAAA au lieu de AAAA-MM  ");
                                //echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                                $parts = explode("/", $tabCol[$cle] );
                                $tabCol[$cle] = $parts[2]."-".$parts[1];
                                $flagDATDC=true;

                            }else if(preg_match( '`^\d{2}-\d{2}-\d{4}$`' , $tabCol[$cle] )){
                                DatesFormatErrone($cle,$val,"JJ-MM-AAAA au lieu de AAAA-MM  ");
                                //echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                                $parts = explode("-", $tabCol[$cle] );
                                $tabCol[$cle] = $parts[2]."-".$parts[1];
                                $flagDATDC=true;
                            }else if(preg_match( '`^\d{4}/\d{2}/\d{2}$`' , $tabCol[$cle] )){
                                DatesFormatErrone($cle,$val,"AAAA/MM/JJ au lieu de AAAA-MM  ");
                                //echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                                $parts = explode("/", $tabCol[$cle] );
                                $tabCol[$cle] = $parts[0]."-".$parts[1];
                                $flagDATDC=true;
                            }else if(preg_match( '`^\d{4}-\d{2}-\d{2}$`' , $tabCol[$cle] )){ // le format est correct mais il faut supprimer le jour
                                DatesFormatErrone($cle,$val,"AAAA-MM-JJ au lieu de AAAA-MM  ");
                                //echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                                $parts = explode("-", $tabCol[$cle] );
                                $tabCol[$cle] = $parts[0]."-".$parts[1];
                                $flagDATDC=true;
                            }
                            //echo $buf."-> ".$tabCol[$cle]."<br /> ";

                            if( $tabCol[$cle] != ""  && substr($tabCol[$cle],0,2) != '99' &&  substr($tabCol[$cle],0,2) != '20' && substr($tabCol[$cle],0,2) != '19')
                                DatesFormatErrone($cle,$val," Valeur non conforme ");

                            break;

                        case "MNAIS":
                        case "ANAIS":
                        case "SEXE":
                            //$tabAnonym[$cle]=$val;
                            // controle sur age
                            $tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
                            break;

                        case "MOTFININT":
                            $MotFinInt = (string)$val;    // $val est un objet il faut un cast
                            if($MotFinInt =='1' || $MotFinInt =='2' || $MotFinInt =='3' || $MotFinInt =='9'){
                                if($tabCol['TYPEV'] == '9')  // valeur provenant de la conversion
                                    $tabCol['TYPEV'] = '3';
                            }else{
                                if($tabCol['TYPEV'] == '9')
                                    $tabCol['TYPEV'] = '2';
                            }
                            $tabCol[$cle]= $MotFinInt;
                            break;
                        case	 "SUPP":
                            //Clés surchargée par convertSchema() et  ignorées
                            break;
                        case "TYPEV":
                            //echo "Dans traitement ".$cle."------------------------------------------------".$val."<br />";
                            $nbTYPEV[$val]++; // on compte le nombre de 1, de 2 et de 3;
                            $tabCol[$cle]= (string)$val;
                            break;

                        default :



                            $tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
                            // traitement des dates au format jj/mm/AAAA

                            if(preg_match( '`^\d{2}/\d{2}/\d{4}$`' , $tabCol[$cle] )){

                                //echo $cptErreur++.": format date JJ/MM/AAAA pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                                $parts = explode("/", $tabCol[$cle] );
                                $tabCol[$cle] = $parts[2]."-".$parts[1]."-".$parts[0];

                                DatesFormatErrone($cle,$val, "au lieu de ".$tabCol[$cle]);

                            }else if(preg_match( '`^\d{2}-\d{2}-\d{4}$`' , $tabCol[$cle] )){
                                //echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                                $parts = explode("-", $tabCol[$cle] );
                                $tabCol[$cle] = $parts[2]."-".$parts[1]."-".$parts[0];
                                DatesFormatErrone($cle,$val, "au lieu de ".$tabCol[$cle]);

                            }else if(preg_match( '`^\d{4}/\d{2}/\d{2}$`' , $tabCol[$cle] )){
                                //echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                                $parts = explode("/", $tabCol[$cle] );
                                $tabCol[$cle] = $parts[0]."-".$parts[1]."-".$parts[2];
                                DatesFormatErrone($cle,$val, "au lieu de ".$tabCol[$cle]);
                            }
                            //if($cle == "FINEVAL")
                            //	echo $cle ."--->".$val.":".$tabCol[$cle]."<br />";
                            break;


                    }
                    //


                }
            }


            $tempo4 += Conso($tempo4,0);
            //echo "Fin anticipee : ".$tabCol['DATDEB'];
            //exit();

            //echo "<br />TYPEV*******************************************=".$tabCol["TYPEV"]."<br />";

            // On reconstitue le ficher XML

            //if($tabCol['NUMANONYM'] == "694d3d6ed5c0fc7bf26e7c80b17ec7ea05674348")
            //	$trace = true;
            $j=0;

            $cols="";
            $values = "";

            /*
            foreach($tabCol as $cle=>$val){
                if($cle != $listeVariables[$j])
                    echo $cptErreur++.":'".$cle."' --> '". $listeVariables[$j]."'  ligne ".$i."<br />";

            */
            $tabAnonym['NEWNUMANONYM']=$tabCol["NUMANONYM"];

            // prétraitement de TYPEV



            switch($tabCol["TYPEV"]){
                case '1X':
                case '2X':
                case '3X':
                case '6X': // Il s'agit de valeur traduite de CODEV
                    $modalitesCADM = array('10','11','12','13','14','15','16','18','19','20','21');
                    $modalitesCASSED = array('11','14','15','16','17','18','19','21','22','23','24');
                    if( in_array($tabCol["NATPDECADM"], $modalitesCADM) ||  in_array($tabCol["NATDECASSED"],$modalitesCASSED)) {
                        $tabCol["TYPEV"]= 1;
                    }else{
                        $tabLignesSupprimees[]=$tabCol["TYPEV"];
                        $nbLS++;
                        // déactivation de la suppression
                        //$tabCol["TYPEV"]= "SUPP";


                    }
                    break;

                case '4X':
                    $tabCol["TYPEV"]= "1";
                    break;
                case '5X':

                    if($tabCol["MOTFININT"] == '1' || $tabCol["MOTFININT"] == '2' || $tabCol["MOTFININT"] == '3'){
                        if($tabCol["DATFIN"] !="9999-99-99" &&  $tabCol["DATFIN"] != ""){
                            $tabCol["TYPEV"]= "3";
                        }else{
                            //aucun changement
                        }
                    }else{
                        if($tabCol["DATFIN"] !="9999-99-99" &&  $tabCol["DATFIN"] != ""){
                            //aucun changement
                        }else{
                            $tabCol["TYPEV"]= "2";
                        }
                    }
                    break;
                case '9X': // en standby
                    if(($tabCol["DATFIN"] !="9999-99-99" &&  $tabCol["DATFIN"] != "") || $tabCol["MOTFININT"] != ''   )
                        $tabCol["TYPEV"]= 3;
                    else
                        $tabCol["TYPEV"]= 2;

                    break;

                default : // On conserve les valeurs saisies

            }


            if($tabCol['TYPEV'] == "SUPP"){
                // intéruption du traitement, on passe à la ligne suivante
                unset($tabCol);
                continue;
            }



            // Majorité ?
            $dateNaissance = $tabCol['ANAIS']."-".$tabCol['MNAIS']."-01";  // ne connaisant pas le jour

            /*
            if($tabCol["TYPEV"] == '3'){
                $dateRef = $tabCol['DATFIN'];
            }else{
                if(isset($tabCol["DATDEB"]) && $tabCol["DATDEB"] !="9999-99-99" &&  $tabCol["DATDEB"] != "" )
                    $dateRef = $tabCol['DATDEB'];
                else
                    $dateRef = $tabCol['DATDECPE'];
            }

            // SIRUS
            $dateRef = substr($dateRef,0,4)."-01-01";
            */
            if(isset($tabCol["DATDECPE"]) && $tabCol["DATDECPE"] !="9999-99-99" &&  $tabCol["DATDECPE"] != "" )
                $dateRef = $tabCol['DATDECPE'];
            else
                $dateRef = $tabCol['DATDEB'];



            /*
            $majeur = Majeur($dateNaissance,$dateRef);

            if($majeur){
                $nbJeunesMajeurs++;

            }else{
                $nbMineurs++;
                // control de la cohérence sur la date de naissance
                if(($tabCol["ANAIS"] < ($tabCol['ANSA1']+10) && $tabCol['ANSA1'] <>'9999') || ($tabCol["ANAIS"] < ($tabCol['ANSA2']+10) && $tabCol['ANSA2'] <>'9999')){
                    $tabErreursDatesNaissances[] =  $tabAnonym['NUMANONYM']. " -> Mineur né en ".$tabCol["ANAIS"]." et les parents : ". $tabCol["ANSA1"]."|".$tabCol['ANSA2'];

                }

            }
            */
            $ageJeune =ageDecision($dateNaissance,$dateRef);

            $majeur = Majeur($dateNaissance,$dateRef);
            if($majeur){
                $nbJeunesMajeursTmp++;
            }else{
                $nbMineursTmp++;
            }

            if($ageJeune > 20){
                $tabErreursDatesNaissances[] =  $tabAnonym['NUMANONYM']. " -> Date de naissance ".$dateNaissance." et Decision ".$dateRef." (".$ageJeune." ans)";
            }


            if (!in_array ($tabCol['NUMANONYM'],$tabJeunes['all'])){
                $tabJeunes['all'][]=$tabCol['NUMANONYM'];
                // on comptabilise les jeunes
                $majeur = Majeur($dateNaissance,$dateRef);
                if($majeur){
                    $nbJeunesMajeurs++;
                }else{
                    $nbMineurs++;
                    // control de la cohérence sur la date de naissance
                    if(($tabCol["ANAIS"] < ($tabCol['ANSA1']+10) && $tabCol['ANSA1'] <>'9999') || ($tabCol["ANAIS"] < ($tabCol['ANSA2']+10) && $tabCol['ANSA2'] <>'9999')){
                        $tabErreursDatesNaissances[] =  $tabAnonym['NUMANONYM']. " -> Mineur né en ".$tabCol["ANAIS"]." et les parents : ". $tabCol["ANSA1"]."|".$tabCol['ANSA2'];

                    }

                }

            }

            $res= Conso($tempo5,0);




            $csvstr= "";
            $dumpLigne = $tabCol['NUMANONYMDEP'].$separateur;

            foreach($listeVariables as $cle){

                if(isset($tabCol[$cle]) && $tabCol[$cle] != 'NULL') {
                    $listeErreurs .= controlCoherence($cle,$tabCol[$cle]);
                }

                $val= trim($tabCol[$cle]);
                /*
                if(!isset($tabCol[$cle]))
                    $erreurDetectee .= $cptErreur++." : valeur '".$cle."' non presente pour la  ligne ".$i."<br />";
                */

                if($cle !="NUMANONYM" && $cle !="NUMANONYMANT"){
                    $dumpLigne .=$val.$separateur;
                }else{
                    $dumpLigne .= "".$separateur;  // effacement des nouveau identifiants anonymisés
                }
                $values.="'".addslashes($val)."',";  // pour injection en base de données
                $xmlstr .= toXML($val,$cle,1);

                if ($val != "NULL" && $val != ""){
                    $csvstr .= $val.$separateur;

                }else if($val == "NULL"){
                    $csvstr .= $separateur;
                    //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante pour la  ligne ".$i."<br />";
                }else{
                    $csvstr .= $separateur;
                    //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' vide pour la  ligne ".$i."<br />";
                }

                //if($trace)
                //echo $cle ."--->".$val."<br />";
                // on initialise

                //echo $cle ."--->".$val."<br />";


                $j++;
                //$tabCol[$cle]="NULL";
            }
            /*
            echo "TEST";
            echo "<pre>";
            print_r($tabCol);

            echo "</pre>";
            exit();
            */
            $tempo5 += Conso($tempo5,0);
            //echo $tabAnonym['NUMANONYM'].' : '.$tabAnonym['MNAIS']."|" .$tabAnonym['ANAIS']."|".$tabAnonym['SEXE']."<br >";
            if(isset($tabControl[$tabAnonym['NUMANONYM']])){
                //on compare
                if($tabControl[$tabAnonym['NUMANONYM']] != $tabCol['ANAIS']."|" .$tabCol['MNAIS']."|".$tabCol['SEXE']){

                    //echo "<br />***************** ".$tabCol["NUMANONYM"]."    ***********<br />  ";
                    //echo $cptErreur++." : ERREUR :".$tabAnonym['NUMANONYM']."->".$tabControl[$tabAnonym['NUMANONYM']]." different de ".$tabAnonym['MNAIS']."|" .$tabAnonym['ANAIS']."|".$tabAnonym['SEXE']."          ".$tabAnonym['NEWNUMANONYM']."<br />";
                    $nbNumAnonymIncoherents ++;
                    $tabNumAnonymIncoherents[$tabAnonym['NUMANONYM']][]= $tabControl[$tabAnonym['NUMANONYM']]." <> ".$tabCol['ANAIS']."|" .$tabCol['MNAIS']."| ".$tabCol['SEXE'] ;

                }
            }else{

                $tabControl[$tabAnonym['NUMANONYM']] = $tabCol['ANAIS']."|" .$tabCol['MNAIS']."|".$tabCol['SEXE'];
                //echo $tabAnonym['NUMANONYM'].":---------------------------------------------:". $tabControl[$tabAnonym['NUMANONYM']]."<br /> ";
            }


            // Recherche de Doublon
            /*
            $iDoublon = 0;
            if($tabAnonym['NUMANONYM'] =='53E39BF46B4631053CF98B507AAA3A9BB4F5E2D1'){
                echo "<br />";
                echo $iDoublon++."-----------------------------".$csvstr.": <br />";

            }
            */

            $bDoublon = false;
            if(isset($tabCsvStr[$tabAnonym['NUMANONYM']])){
                //echo "Connu <br />";
                if (in_array ($csvstr,$tabCsvStr[$tabAnonym['NUMANONYM']])){
                    // doublon
                    //echo "Doublon détecté <br />";
                    $nbCsvStr[$tabAnonym['NUMANONYM']] =(isset($nbCsvStr[$tabAnonym['NUMANONYM']])) ? $nbCsvStr[$tabAnonym['NUMANONYM']]+1 : 1;
                    $bDoublon = true;
                    $nbLigneDoublons++;
                    // pour vérification par pointage voir génération du fichier csv
                    $tabDoublons[]=$tabAnonym['NUMANONYM'];
                    $tabDumpLignes["doublons"][] = $dumpLigne."\n";

                }else{
                    $tabCsvStr[$tabAnonym['NUMANONYM']][]=$csvstr;
                    //$nbCsvStr[$tabAnonym['NUMANONYM']] =(isset($nbCsvStr[$tabAnonym['NUMANONYM']])) ? $nbCsvStr[$tabAnonym['NUMANONYM']]+1 : 1;
                }
            }else{
                //echo "Inconnu <br />";
                $tabCsvStr[$tabAnonym['NUMANONYM']][]=$csvstr;
                //$nbCsvStr[$tabAnonym['NUMANONYM']] = 1;

            }

            // recherche de doublon administratif dans le cas où ce ne sont pas des doublons complet

            // annee concernee est renseignée

            if(!isset($tabJeunes[$anneeConcernee]))
                $tabJeunes[$anneeConcernee][0] = "ffffffffffffffffffffffffffffffffffffffff";

            if (!in_array ($tabAnonym['NUMANONYM'],$tabJeunes[$anneeConcernee]))
                $tabJeunes[$anneeConcernee][]=$tabAnonym['NUMANONYM'];





            // On identifie les doublons en ajoutant une colonne pour en garder une trace

            if(!$bDoublon){
                //$values = substr($values,0,(strlen($values) - 1)).",'0'";
                $values .= "'".$tabAnonym['NUMANONYM']."','0'";
                //echo $values."<br />";
                $tabValues[] = $values;
                $csvstr.="0"; // le séparateur est déjà en place
            }else{
                //$values = substr($values,0,(strlen($values) - 1)).",'1'";
                $values .= "'".$tabAnonym['NUMANONYM']."','1'";
                //echo $values."<br />";
                $tabValues[] = $values;
                $csvstr.="1";

            }


            /*
            // en base de donnée
            $cols = substr($cols,0,(strlen($cols) - 1));
            $sql = "insert IGNORE into DONNEES_TMP (".$cols.",DateTraitement) values (".$values.",'".$dateTraitement."')";
            $result  = ExecRequete($sql, $maConnection);
            //echo $sql ."<br />";
            */
            $nombreVariablesPresentes = count($nbVariablesPresentes);

            unset($tabCol);

            $xmlstr.= "\n</DONNEE>\n";
            $csvstr.= "\n";

            if(!$bDoublon){
                $fp = fopen($xmlNewfile, 'a');
                fputs($fp, $xmlstr);
                fclose($fp);

                $fp = fopen($csvfile, 'a');
                fputs($fp, $csvstr);
                fclose($fp);

                $nbLignesTraitees++;
            }else{
                /*
                $fp = fopen($csvfileVerif, 'a');
                //fputs($fp, $csvstr);
                fputs($fp, $tabAnonym['NUMANONYM'].";\n");
                fclose($fp);
                */
                // on conserve les doublons
                $fp = fopen($csvfile, 'a');
                fputs($fp, $csvstr);
                fclose($fp);


            }
            $xmlstr= "";
            $csvstr= "";

            //echo  $i.":".$xml->name."<br />";
            $nbLignesIni++;


        }



    }




    $xmlstr.= "\n</DECRET>\n";

    $xml->close();
    $res = Conso($tempo6,1);


    $reqIni = "TRUNCATE TABLE `DONNEES_TMP` ";
    $result  = ExecRequete($reqIni, $maConnection);

    $listeInserts ="";
    foreach($tabValues AS $values){
        //echo $values."<br />";
        $listeInserts .= "(".$values.",'".$dateTraitement."'),";
        $reqInsert = $reqDebut. "(".$values.",'".$dateTraitement."');";
        $result  = ExecRequete($reqInsert, $maConnection);
        //echo $reqInsert ."<br />";
    }
    $listeInserts = substr($listeInserts,0,(strlen($listeInserts) - 1));

    $reqTotale = $reqTest.$listeInserts.";";

    //$result  = ExecRequete($reqTotale, $maConnection); // requete trop importante
    $tempo6 += Conso($tempo6,0);

    //exit();
} // false



$fp = fopen($xmlNewfile, 'a+');
fputs($fp, $xmlstr);
fclose($fp);



$max=0;
$anneeRef="";
krsort($tabDecision);
$nbLignes = $nbLignesIni;

foreach($tabDecision as $cle=>$val){
    if($val > $max){
        $max = $val;
        $anneeRef=$cle;
    }
    //$commentaire .= $cle." : ".$val." décisions \n";
    $commentaire .= $cle." : ".$val." décisions (".(count($tabJeunes[$cle] )-1).")\n";
}
/*
echo "Presentes <br />";
echo "<pre>";
print_r($nbVariablesPresentes);
echo "</pre>";
*/

foreach($listeVariables as $cle){
    if(!isset($nbVariablesPresentes[$cle]))
        $listeVariablesAbsentes[]=$cle;
}
/*
echo "Absentes <br />";
echo "<pre>";
        print_r($listeVariablesAbsentes);
        echo "</pre>";
*/

foreach($listeVariables as $cle){
    if(!isset($nbVariablesNonVides[$cle]) && isset($nbVariablesPresentes[$cle]))
        $listeVariablesVides[]=$cle;
}

//echo $cptNBFrat++ ."NBFRAT =".$val."<br />";

foreach($listeVariables as $cle){
    if(!isset($nbVariablesRenseignees[$cle]))
        $listeVariablesNonRenseignees[]=$cle;
}

foreach($listeVariables as $cle){
    if(!isset($nbVariablesPertinentes[$cle])){	//  ne fait pas partie des Variables présentes, non vides, et non NSP
        if(!in_array($cle,$listeVariablesVides)  && !in_array($cle,$listeVariablesAbsentes)){ //  n'est pas considérée comme Variable toujours vides ni absente
            $listeVariablesToujoursNSP[]=$cle;
        }
    }
}
//$nbErreursDatesNaissances = count($tabErreursDatesNaissances);
asort($tabErreursDatesNaissances);


if($flagDATDC)
    $commentaire .=" format DATDCPERE ou DATDCMERE non conforme \n";
if($flagNUMDEPT)
    $commentaire .=" format NUMDEPT non conforme \n";

/*if(count($tabLignesSupprimees))
    $commentaire .= " Nombre de lignes supprimées du fait de CODEV non interprétable : ".count($tabLignesSupprimees)." \n";
*/
if($nbLigneDoublons >0)
    $commentaire .= "\nNombre de lignes qui devraient être supprimées pour cause de doublons : ".$nbLigneDoublons." \n";

//$commentaire = utf8_encode($commentaire);

// Nom des fichiers destination
$nomFicDest = "CD".$numDept."_ExportOnpe_".$anneeRef.".csv";
$nomFicAudit = "CD".$numDept."_ExportOnpe_".$anneeRef.".pdf";
$nomFicVerif = "./imports/csv_verif/CD".$numDept."_ExportOnpe_".$anneeRef."_Verif.csv";


/***********************************************************************************************/
/*                          Traitement SQL depuis  table temporaire                                                  */
/*                                                                                                                                            */
/***********************************************************************************************/


// Requete utilisées seulement pour générer le ficher joint concernant les doublons administratifs
//$req = "SELECT *,  count(*) as nb FROM `DONNEES_TMP` WHERE `DECISION` = '1' and `Doublon`='0'  GROUP BY CONCAT(NUMANONYMDEP,TYPEV, LIEUACC,TYPINTERDOM, DATDECPE, NATPDECADM)  having nb>1 ";
$req = "SELECT CONCAT(NUMANONYMDEP,TYPEV, LIEUACC,TYPINTERDOM, DATDECPE, NATPDECADM),  count(*) as nb FROM `DONNEES_TMP` WHERE `DECISION` = '1' and `Doublon`='0'  GROUP BY CONCAT(NUMANONYMDEP,TYPEV, LIEUACC,TYPINTERDOM, DATDECPE, NATPDECADM)  having nb>1 ";
$DoublonsAdministratifs  = ExecRequete($req, $maConnection);

VersCsvVerif($DoublonsAdministratifs , "On appelle doublon administratif 2 lignes pour lesquelles les variables TYPEV, NATPDECADM, DATDECPE, LIEUACC et TYPINTERDOM  sont identiques pour un même identifiant.");

// Requete utilisées seulement pour générer le ficher joint concernant les doublons judiciaire
$req = "SELECT CONCAT(NUMANONYMDEP,TYPEV, LIEUPLAC,TYPDECJUD, DATDECPE, NATDECASSED),  count(*) as nb FROM `DONNEES_TMP` WHERE `DECISION` = '2' and `Doublon`='0'  GROUP BY CONCAT(NUMANONYMDEP,TYPEV, LIEUPLAC,TYPDECJUD, DATDECPE, NATDECASSED)  having nb>1 ";
$DoublonsJudiciaires  = ExecRequete($req, $maConnection);

VersCsvVerif($DoublonsJudiciaires , "On appelle doublon judiciaire 2 lignes pour lesquelles les variables TYPEV, NATDECASSED, DATDECPE, LIEUPLAC et TYPDECJUD sont identiques pour un même identifiant.");




// Chaque enregistrement du fichier doit comporter la date de début effective.
$req = "SELECT * FROM `DONNEES_TMP` WHERE (`DATDEB` like '0000%' OR `DATDEB` like '9999%') ";
$DateDebutManquantes  = ExecRequete($req, $maConnection);

VersCsvVerif($DateDebutManquantes, "Date de début effective manquante");

// Chaque enregistrement du fichier doit comporter  la date de décision.
$req = "SELECT * FROM `DONNEES_TMP` WHERE  `DECISION` NOT IN ('1','2') ";
$DecisionManquantes  = ExecRequete($req, $maConnection);
VersCsvVerif($DecisionManquantes, "Variable DECISION non renseignée");


// Chaque enregistrement du fichier doit comporter  la date de décision.
$req = "SELECT * FROM `DONNEES_TMP` WHERE  (`DATDECPE` like '0000%' OR `DATDECPE` like '9999%' )";
$DateDecisionManquantes  = ExecRequete($req, $maConnection);

VersCsvVerif($DateDecisionManquantes, "Date de décision absente");

// Chaque enregistrement du fichier de type TYPEV=3 doit comporter la date de fin effective.
$req = "SELECT * FROM `DONNEES_TMP` WHERE TYPEV = '3' AND (`DATFIN` like '0000%' OR `DATFIN` like '9999%') ";
$DateFinManquantes  = ExecRequete($req, $maConnection);

VersCsvVerif($DateFinManquantes, "Chaque enregistrement du fichier de type TYPEV=3 doit comporter la date de fin effective.");

/*
$j=0;
while ($record = $dateDebutManquantes->fetch_assoc()) {
    //echo $j++." :".$record['NUMANONYMDEP']."<br />";
}
*/

// Chaque enregistrement de fin (TYPEV=3) devrait rappeler la date de début effective de la prestation/mesure.
$req = "SELECT * FROM `DONNEES_TMP` WHERE DATFIN like '20%' and DATDEB like '0000%' ";
$DateFinSansDateDebut  = ExecRequete($req, $maConnection);

VersCsvVerif($DateFinSansDateDebut, "Chaque enregistrement de fin (TYPEV=3) devrait rappeler la date de début effective de la prestation/mesure.");


// La date de fin effective d'une prestation/mesure ne peut être renseignée que pour les évenement de fin (TYPEV=3).
$req = "SELECT * FROM `DONNEES_TMP` WHERE DATFIN like '20%' and TYPEV <>'3' ";
$DateFinMauvaisTypev  = ExecRequete($req, $maConnection);

VersCsvVerif($DateFinMauvaisTypev, "La date de fin effective d'une prestation/mesure ne peut être renseignée que pour les évenement de fin (TYPEV=3).");

// La durée entre la fin effective d'une prestation/mesure et le début indiqué ne peut supérieur à 3 ans (TYPEV=3).
$req = "SELECT *, DATEDIFF( DATFIN, DATDEB ) AS nbj FROM `DONNEES_TMP` ";
$req .= "WHERE DATDEB LIKE '20%' AND DATFIN LIKE '20%' HAVING nbj >730  ORDER BY nbj DESC";
$DureeExcessive  = ExecRequete($req, $maConnection);

VersCsvVerif($DureeExcessive, "Duree excessive des prestations/mesures");

// La variable MOTFININT doit être renseignée pour tout les évenement de fin (TYPEV=3).
$req = "SELECT * FROM `DONNEES_TMP` WHERE TYPEV = '3' and MOTFININT NOT IN ('1','2','3','9')";
$sansMOTFININT  = ExecRequete($req, $maConnection);

VersCsvVerif($sansMOTFININT, "La variable MOTFININT doit être renseignée pour tout les évenements de fin (TYPEV=3).");







// On parcours les enregistrements du fichier qui sont des fins de prestation/mesure et on recherche l'enregistrement de debut associé
$req = "SELECT NUMANONYMDEP,DATDECPE, DATDEB, DATFIN FROM `DONNEES_TMP` WHERE DATFIN like '20%' and TYPEV = '3' ";
$reqTypev3  = ExecRequete($req, $maConnection);
$tabRecordsOrphelins=array();

while ($record = $reqTypev3->fetch_assoc()) {
    if($record['DATDEB'] != "0000-00-00"  && $record['DATDEB'] != "9999-99-99" && substr($record['DATDEB'],0,4) ==  substr($record['DATFIN'],0,4) ){
        // recherche du debut associé(si dans même année)

        $sql = "SELECT * FROM `DONNEES_TMP` WHERE DATDEB ='".$record['DATDEB']."' and (TYPEV = '1' OR TYPEV = '2') ";
        $resultat  = ExecRequete($sql, $maConnection);
        if($resultat->num_rows ==0){
            //$listeRecordsIsoles[]=$record['DATDEB'];
            //echo "Orphelin : ".$record['NUMANONYM']." ".$record['DATDEB']." ".$record['DATFIN']."<br />";
            $tabRecordsOrphelins[]=$record;
        }else{
            //echo "Associe : ".$record['NUMANONYM']." ".$record['DATDEB']." ".$record['DATFIN']."<br />";
            //$tabRecordsOrphelins[]=$record;
        }
    }

}
// Perimetre
$req = "SELECT * FROM `DONNEES_TMP` WHERE `DATDEB` NOT like '".$anneeRef."%' AND  `DATDECPE` NOT like '".$anneeRef."%' AND  `DATFIN` NOT like '".$anneeRef."%' ";
$horsPerimetre  = ExecRequete($req, $maConnection);

VersCsvVerif($horsPerimetre , "Informations hors périmètre");

// S'il s'agit d'une décision administrative, NATPDECADM doit en préciser la nature.
$req = "SELECT * FROM `DONNEES_TMP` WHERE DECISION = '1' and NATPDECADM NOT IN ('10','11','12','13','14','15','16','18','19','20','21','99')";
//$req = "SELECT * FROM `DONNEES_TMP` WHERE DECISION = '1' and NATPDECADM NOT IN ('10','11','12','13','14','15','16','18','19','20','21')";
$sansNATPDECADM  = ExecRequete($req, $maConnection);

// autre que NSP ?
$req = "SELECT * FROM `DONNEES_TMP` WHERE DECISION = '1' and NATPDECADM <> '99' ";
$NATPDECADM_nonNSP  = ExecRequete($req, $maConnection);

VersCsvVerif($sansNATPDECADM , "S'il s'agit d'une décision administrative NATPDECADM doit en préciser la nature.");


//Vérification de la présence de la variable remplie TYPINTERDOM si interventions administratives d'aide à domicile : NATPDCAM = 10, 20, 18
$req = "SELECT * FROM `DONNEES_TMP` WHERE NATPDECADM IN ('10','20','18') and TYPINTERDOM NOT IN ('1','2','3','4','5','6','7','9','10')";
//$req = "SELECT * FROM `DONNEES_TMP` WHERE NATPDECADM IN ('10','20','18') and TYPINTERDOM NOT IN ('1','2','3','4','5','6','7','10')";
$sansTYPINTERDOM  = ExecRequete($req, $maConnection);

VersCsvVerif($sansTYPINTERDOM   , "TYPINTERDOM doit être précisé si interventions administratives d'aide à domicile ");

//Vérification de la présence de la variable remplie LIEUACC si Interventions administratives d'accueil provisoire : NATPDECADM IN ('11','12','13','14','15','16','18','19','20','21')
$req = "SELECT * FROM `DONNEES_TMP` WHERE NATPDECADM IN ('11','12','13','14','15','16','19','21') and LIEUACC NOT IN ('1','2','3','4','5','6','7','8','9','10','11','12','13','14','16','99')";
//$req = "SELECT * FROM `DONNEES_TMP` WHERE NATPDECADM IN ('11','12','13','14','15','16','18','19','21') and LIEUACC NOT IN ('1','2','3','4','5','6','7','8','9','10','11','12','13','14','16')";
$sansLIEUACC  = ExecRequete($req, $maConnection);

VersCsvVerif($sansLIEUACC  , "LIEUACC doit être précisé si interventions administratives d'accueil provisoire ");

//Vérification de la présence de la variable remplie ACCMOD  si Interventions administratives d'accueil provisoire : NATPDCAM = 11,12,13,14,15,16,18,19,21
$req = "SELECT * FROM `DONNEES_TMP` WHERE NATPDECADM IN ('11','12','13','14','15','16','19','21') and ACCMOD NOT IN ('1','2','3','4','5','6','7','8','9','10','11','12','13','14','16','99')";
//$req = "SELECT * FROM `DONNEES_TMP` WHERE NATPDECADM IN ('11','12','13','14','15','16','18','19','21') and ACCMOD NOT IN ('1','2','3','4','5','6','7','8','9','10','11','12','13','14','16')";
$sansACCMOD  = ExecRequete($req, $maConnection);

VersCsvVerif($sansACCMOD , "ACCMOD doit être précisé si interventions administratives d'accueil provisoire ");


//Vérification de la présence de la variable remplie AUTRLIEUACC
$req = "SELECT * FROM `DONNEES_TMP` WHERE NATPDECADM IN ('11','12','13','14','15','16','19','21') and AUTRLIEUACC NOT IN ('1','2','9')";
//$req = "SELECT * FROM `DONNEES_TMP` WHERE NATPDECADM IN ('11','12','13','14','15','16','18','19','21') and AUTRLIEUACC NOT IN ('1','2')";
$sansAUTRLIEUACC  = ExecRequete($req, $maConnection);

VersCsvVerif($sansAUTRLIEUACC , "AUTRLIEUACC doit être précisé si NATPDECADM IN ('11'|'12'|'13'|'14'|'15'|'16'|'19'|'21') ");


// S'il s'agit d'une décision judiciaire, NATDECASSED doit en préciser la nature.
$req = "SELECT * FROM `DONNEES_TMP` WHERE DECISION = '2' and NATDECASSED NOT IN ('11','14','15','16','17','18','19','21','22','23','24','99')";
//$req = "SELECT * FROM `DONNEES_TMP` WHERE DECISION = '2' and NATDECASSED NOT IN ('11','14','15','16','17','18','19','21','22','23','24')";
$sansNATDECASSED  = ExecRequete($req, $maConnection);

// autre que NSP ?
$req = "SELECT * FROM `DONNEES_TMP` WHERE DECISION = '2' and NATDECASSED <> '99' ";
$NATDECASSED_nonNSP  = ExecRequete($req, $maConnection);

VersCsvVerif($sansNATDECASSED , "S'il s'agit d'une décision judiciaire NATDECASSED doit en préciser la nature.");

// En cascade 	 NATDECPLAC et INSTITPLAC si NATDECASSED = 17,18
$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED IN ('17','18') and NATDECPLAC NOT IN ('1','2','3','9')";
//$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED IN ('17','18','21') and NATDECPLAC NOT IN ('1','2','3')";
$sansNATDECPLAC  = ExecRequete($req, $maConnection);

/*
// Autre que NSP  pour chacune des valeurs possibles
$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED = '17' and NATDECPLAC <> '9' ";
$sansNATDECPLAC17_nonNSP  = ExecRequete($req, $maConnection);

$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED  ='18' and NATDECPLAC <> '9' ";
$sansNATDECPLAC18_nonNSP  = ExecRequete($req, $maConnection);

*/

VersCsvVerif($sansNATDECPLAC  , "NATDECPLAC doit être précisé si NATDECASSED = 17|18.");

$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED IN ('17','18') and INSTITPLAC NOT IN ('1','2','3','4','5','6','9')";
//$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED IN ('17','18','21') and INSTITPLAC NOT IN ('1','2','3')";
$sansINSTITPLAC  = ExecRequete($req, $maConnection);

// Autre que NSP  pour chacune des valeurs possibles
$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED = '17' and INSTITPLAC <> '9' ";
$sansINSTITPLAC17_nonNSP  = ExecRequete($req, $maConnection);

$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED = '18' and INSTITPLAC <> '9' ";
$sansINSTITPLAC18_nonNSP  = ExecRequete($req, $maConnection);


VersCsvVerif($sansINSTITPLAC  , "INSTITPLAC doit être précisé si NATDECASSED = 17|18.");




//Vérification de la présence de la variable remplie TYPDECJUD si NATDECASSED = 11,14,15,16,19,21,24
$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED IN ('11','14','15','16','19','21','24') and TYPDECJUD NOT IN ('3','4','5','6','7','8','9','10')";
//$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED IN ('11','14','15','16','19','21','24') and TYPDECJUD NOT IN ('3','4','5','6','7','8','10')";
$sansTYPDECJUD  = ExecRequete($req, $maConnection);

VersCsvVerif($sansTYPDECJUD  , "TYPDECJUD doit être précisé si NATDECASSED = 11|14|15|16|19|21|24 ");

//Vérification de la présence de la variable remplie LIEUPLAC si NATDECASSED = 11,14,15,16,19,21,24
$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED IN ('17','18','22','23') and LIEUPLAC NOT IN ('1','2','3','4','5','6','8','9','10','11','12','13','19','21','99')";
//$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED IN ('17','18','21','22','23') and LIEUPLAC NOT IN ('1','2','3','4','5','6','8','9','10','11','12','13','19','21')";
$sansLIEUPLAC  = ExecRequete($req, $maConnection);

VersCsvVerif($sansLIEUPLAC , "LIEUPLAC doit être précisé si NATDECASSED = 11|14|15|16|19|24 ");

//Vérification de la présence de la variable remplie PLACMOD si NATDECASSED = 17,18,21,22,23
$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED IN ('17','18','22','23') and PLACMOD NOT IN ('1','2','9')";
//$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED IN ('17','18','21','22','23') and PLACMOD NOT IN ('1','2')";
$sansPLACMOD = ExecRequete($req, $maConnection);

VersCsvVerif($sansPLACMOD , "PLACMOD doit être précisé si NATDECASSED = 17|18|22|23 ");

//Vérification de la présence de la variable remplie AUTELIEUAR si NATDECASSED = 17,18,22,23
$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED IN ('17','18','22','23') and AUTRLIEUAR NOT IN ('1','2','9')";
//$req = "SELECT * FROM `DONNEES_TMP` WHERE NATDECASSED IN ('17','18','21','22','23') and AUTRLIEUAR NOT IN ('1','2')";
$sansAUTRLIEUAR  = ExecRequete($req, $maConnection);

VersCsvVerif($sansAUTRLIEUAR , "AUTELIEUAR doit être précisé si NATDECASSED = 17|18|22|23 ");

//Vérification de la présence de la variable remplie CHGLIEU pour les renouvellement de mesure
$req = "SELECT * FROM `DONNEES_TMP` WHERE TYPEV = '2' and CHGLIEU NOT IN ('1','2','9')";
//$req = "SELECT * FROM `DONNEES_TMP` WHERE TYPEV = '2' and CHGLIEU NOT IN ('1','2')";
$sansCHGLIEU  = ExecRequete($req, $maConnection);

VersCsvVerif($sansCHGLIEU , " CHGLIEU doit être précisé pour les renouvellement de mesure ");

// Requete utilisées seulement pour générer le ficher joint concernant les doublons - pas de script associé pour l'instant

$req = "SELECT NUMANONYMDEP, DONNEES_TMP.* FROM `DONNEES_TMP` WHERE  `Doublon`='1'  ORDER BY NUMANONYMDEP,TYPEV,DATDECPE,  DATDEB, DATFIN";
$Doublons  = ExecRequete($req, $maConnection);

VersCsvVerif($Doublons, "On appelle doublon 2 lignes rigoureusement identiques, c.à.d pour lesquelles toutes les variables renseignées ont la même valeur. ");




/*
echo "<pre>";
print_r($tabRecordsIsoles);
echo "<pre>";
*/
/*
// Nom du fichier destination
$nomFicDest = "CD".$numDept."_ExportOnpe_".$anneeRef.".csv";
$nomFicAudit = "CD".$numDept."_ExportOnpe_".$anneeRef.".pdf";
*/

$monImportation = new importationsXML;
$monImportation->code_departement = $numDept;
$monImportation->annee =$anneeRef;
$monImportation->nom_fichier_ini=$e_file;
$monImportation->nom_fichier_traduit =$nomFicDest;
$monImportation->num_lignes =$nbLignesTraitees;
$monImportation->commentaires = $commentaire ;
$monImportation->date_importation= date ("Y-m-d H:i:s.", filemtime($xmlfile));
$monImportation->date_traitement = $dateTraitement;

$result = $maConnection->query(requete_insert($monImportation));
//echo "\n".	requete_insert($monImportation)."\n";
$lastInsert = $maConnection->insert_id;


$nomRepertoire = "../../chiffres/".$anneeRef;
if (is_dir($nomRepertoire)) {
    // echo 'Le r걥rtoire existe dꫠ!';
}else {
    mkdir($nomRepertoire);
    //echo "Crꢴion :".$nomRepertoire;
}

$commande = 'mv '.$csvfile.' '.$nomRepertoire.'/'.$nomFicDest;
exec($commande);

$commande = 'mv '.$nomFicVerif .' '.$nomRepertoire.'/'.$nomFicVerif ;
exec($commande);



if($test){
    if(!$debug)
        include ("./includes/audit_main_inc.php");

    //exit();

    echo "<pre>";





    //echo "Liste des variables pertinente <br />";
    //print_r($nbVariablesPertinentes);

    echo "</pre>";
    //exit;

}else{
    $isFichier=true;
    if(!$debug)
        include ("./includes/audit_main_inc.php");

}





echo "<hr/>";
echo "d&eacute;partement " .$monImportation->code_departement." <br/>";
echo "Boucle1 (convertShema) : ".$tempo1." <br/>";
echo "Boucle2 (ControlCoherence) : ".$tempo2." <br/>";
echo "Boucle3 (BDD) : ".$tempo3." <br/>";
echo "Boucle4 (read) : ".$tempo4." <br/>";
echo "Boucle5 (foreach) : ".$tempo5." <br/>";
echo "Boucle6 (Insert Blob) : ".$tempo6." <br/>";
echo "Nbre insert : ".$nbInsert." <br/>";
echo "Nbre de lignes : ".$nbLignes ." <br/>";
echo "Nbre de jeunes : ". count($tabJeunes['all']) ." <br/>";
echo "<pre>";
echo htmlspecialchars($commentaire, ENT_QUOTES);
echo "</pre>";

echo "Nombres de variables pr&eacute;sentes dans ce fichier :".$nombreVariablesPresentes. "/".count($listeVariables). "<br />";
//Traitement du fichier cr�� dans le r�pertoire accessible par Web

echo "Nombre de lignes supprim&eacute;es du fait de CODEV non interp&eacute;tables : ".count($tabLignesSupprimees). "<br />";
echo "Nombre de lignes supprim&eacute;es du fait de doublons : ".$nbLigneDoublons. "<br />";

// backup du fichier source
//$folder = "/home/imports_dept"; //Prod
$folder = "./imports/"; //Test / Dev


if(!$test) {

    $commande = 'mv '.$folder.'/'.$e_file.' '.$folder.'/Backup/'.$e_file;
    exec($commande);
    echo " commande : ".$commande."  <br/>";
    logtmp("Execution deplacement :".$commande,1);

    echo " le fichier : ".$csvfile." a &eacute;t&eacute; d&eacute;pos&eacute; dans le r&eacute;pertoire : olinpe.giped.fr/".$anneeRef."/  <br/>";

}

//echo "trace :".$trace;
echo "<br/>";
echo "<br/>";
//Url à transmettre par mail
echo "<a href = \"/index.php?id=".$lastInsert."\">Interface Web</a>";
echo "<br/>";
echo "<br/>";
echo "<br/>";
echo "<a href=\"ONPE_Xml2Csv.php\" >Retour </a>";

echo "<br/>";
echo "<br/>";







if($test && $debug) {

    if(false){
        echo "Valeurs non-attendues :<br/>";
        $numLigne=0;
        foreach($tabVariablesNonAttendues as $cle=>$lignes){
            echo $cle." (".$nbVariablesNonAttendues[$cle].") : <br />";
            foreach($lignes as $ligne){
                echo $numLigne++ ."-> ".$cle." : ". $ligne. " <br />";
            }
        }


        echo "Valeurs réservées aux mineurs :<br/>";
        $numLigne=0;
        foreach($tabVariablesReserveesMineurs as $cle=>$lignes){
            echo $cle." (".$nbVariablesReserveesMineurs[$cle].") : <br />";
            foreach($lignes as $ligne){
                echo $numLigne++ ."-> ".$cle." : ". $ligne. " <br />";
            }
        }


        echo "Valeurs réservées aux jeunes-majeurs :<br/>";
        $numLigne=0;
        foreach($tabVariablesReserveesMajeurs as $cle=>$lignes){
            echo $cle." (".$nbVariablesReserveesMajeurs[$cle].") : <br />";
            foreach($lignes as $ligne){
                echo $numLigne++ ."-> ".$cle." : ". $ligne. " <br />";
            }
        }


        echo "Modalites réservées aux mineurs :<br/>";
        $numLigne=0;
        foreach($tabModalitesReserveesMineurs as $cle=>$lignes){
            echo $cle." (".$nbModalitesReserveesMineurs[$cle].") : <br />";
            foreach($lignes as $ligne){
                echo $numLigne++ ."-> ".$cle." : ". $ligne. " <br />";
            }
        }


        echo "Modalites réservées aux jeunes-majeurs :<br/>";
        $numLigne=0;
        foreach($tabModalitesReserveesMajeurs as $cle=>$lignes){
            echo $cle." (".$nbModalitesReserveesMajeurs[$cle].") : <br />";
            foreach($lignes as $ligne){
                echo $numLigne++ ."-> ".$cle." : ". $ligne. " <br />";
            }
        }






    } //if (false)
    // A supprimer ?
    echo "numDept".$numDept."<br />";
    echo "Count tabErreurDatesNaissances : ".count($tabErreurDatesNaissances) ;
    echo "*****************".$sansMOTFININT->num_rows."**************************<br />";
    $sansMOTFININT->data_seek(0);
    $j=0;
    while ($record = $sansMOTFININT->fetch_assoc()) {

        $j++;
        if($j >10)
            break;
        echo $j." : ".$record['NUMANONYMDEP']." | ".$record['ANAIS']." | ".$record['DATDECPE'] ."<br />";


    }
    echo "*******************************************<br />";



    echo "<pre>";
    echo "TYPEV <br />";
    print_r($nbTYPEV);

    echo "Dates au format errone". " <br />";
    print_r($nbDatesFormatErrone);
    echo "Dates de naissance incorrecte :". $nbErreursDatesNaissances." <br />";
    print_r($tabErreursDatesNaissances);
    echo "Liste des variables absentes". " <br />";
    print_r($listeVariablesAbsentes);
    echo "Liste des variables non vides". " <br />";
    print_r($nbVariablesNonVides);
    echo "Liste des variables vides". " <br />";
    print_r($listeVariablesVides);
    echo "Liste des variables toujours NSP". " <br />";
    print_r($listeVariablesToujoursNSP);
    echo "nbVariablesRenseignées". " <br />";
    print_r($nbVariablesRenseignees);

    echo "nombrebVariables presentes :".$nombreVariablesPresentes." <br />";

    echo "nbVariablesPresentes". " <br />";
    print_r($nbVariablesPresentes);
    echo "nbVariables pertinentes". " <br />";
    print_r($nbVariablesPertinentes);
    echo "nbVariablesReservéesMajeurs". " <br />";
    print_r($nbVariablesReserveesMajeurs);
    echo "nbVariablesReservéesMineurs". " <br />";
    print_r($nbVariablesReserveesMineurs);

    echo "nbModalitesReservéesMajeurs". " <br />";
    print_r($nbModalitesReserveesMajeurs);
    echo "nbModalitesReservéesMineurs". " <br />";
    print_r($nbModalitesReserveesMineurs);

    echo "Liste des doublons administratifs". " <br />";
    print_r($tabDoublonsAdministratifs);
    echo "Liste des doublons judiciaires". " <br />";
    print_r($tabDoublonsJudiciaires);
    echo "****************************************";
    echo $listeColonnes ." <br />";
    print_r($tabDumpLignes['doublonsJudiciaires']);
    echo "</pre>";



}else{
    if(!$test && !$debug){
        echo $listeErreurs;
        echo "<a href=\"ONPE_Xml2Csv.php\" >Retour </a>";
        envoi_mail_chiffres("carnaud@giped.gouv.fr",$lastInsert,"RDD",$monImportation->code_departement." (".$anneeRef.")");
    }
}
exit();



function recordVersLigne($tab, $listeCles){
    // on parcours le tableau indexé par les clés qui peut être le résultat d'un fetch SQL
    global $separateur;
    $listeValues ="";
    foreach($listeCles as $cle){
        $listeValues .= $tab[$cle].$separateur;
    }
    return $listeValues;
}

function VersCsvVerif($result, $titreSection=""){
    global $listeVariablesVerif, $listeColonnesVerif, $nomFicVerif ;  // par défaut
    // on parcours le jeu de resultat provenant d'une requete MySQL
    if($result->num_rows >0){
        $fp = fopen($nomFicVerif, 'a');
        if($titreSection != "")
            fputs($fp, "\n\n".$titreSection."\n\n");
        $i=0;
        fputs($fp, $listeColonnesVerif.";\n");
        while ($record = $result->fetch_assoc()) {
            //echo $i++.":".$listeVariables."<br />";
            //echo recordVersLigne($record, $listeVariables)."<br />";
            fputs($fp, recordVersLigne($record, $listeVariablesVerif).";\n");
        }
        fclose($fp);
        $result->data_seek(0); // on se remet au début du resultat pour traitement suivant
    }
}



?>
