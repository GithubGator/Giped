<?php
//***********************************************************************************
//   Parcours le fichier XML original, converti les formats des dates, construit le fichier XML corrig� et le CSV correspondant
//
//
//***********************************************************************************
require_once("../_session.php");
require_once("../_dbclass.php");
require_once("../_connect.php"); // Paramtres de connection et... connection
require_once("../_fonctions.php");
require_once("../_classes/calc_dates.php");
require_once("../_classes/phonex.class.php");
require_once("../_classes/cleID.class.php");
require_once("../_classes/cleONED.inc.php"); // Cle n�cessaire � la seconde anonymisation


header("Content-type: text/html; charset=utf-8");
setlocale(LC_ALL, 'fr_FR.utf8');
//error_reporting(E_ERROR | E_PARSE);
date_default_timezone_set('Europe/Paris');

error_reporting(E_ALL ^ E_NOTICE);
import_request_variables("GP","e_");
ini_set('display_errors', 1);
ini_set('max_execution_time','0');
ini_set('memory_limit','-1');

$error = array();
// Objet dom XML pour valider le sch�ma
$dom = new DomDocument();

//$test=true;  // pour effectuer des tests sans �mettre de mail ni d�placer le fichier

$test=false;




$trace="";
$cptErreur=0;
$anneeConcernee= "2011";
$tabDecision = array();
$flagDATDC=false;  // pour les dates de d�c�s non conformes
$flagNUMDEPT = false; // pour le

$folder = "/home/imports_dept";

if(!isset ($e_file)){

    $dossier = opendir($folder);
    while ($Fichier = readdir($dossier)) {
        if ($Fichier != "." && $Fichier != ".." && $Fichier !="Backup") {
            $nomFichier = $folder."/".$Fichier;
            //echo $nomFichier."<BR>";
            echo "<a href='ONED_Xml2Csv.php?file=".$Fichier."'>".$Fichier."</a><BR>";
        }
    }
    closedir($dossier);

    exit();

}else{

    // Sauvegarde du ficher xml
    $dateJour=date("Y-m-d");
    //$csvfile="./".$Fichier."-".$dateJour.".csv";
    $xmlNewfile= "new_".substr($e_file,0,-3)."xml";
    $csvfile= substr($e_file,0,-3)."csv";

}

// nom du fichier
//$nomFic="ONED-035-20120702_2012.XML";

// Lecture du ficher xml
//$xmlfile="./imports/".$nomFic;

$xmlfile=$folder."/".$e_file;
// echo "Chargement du fichier XML";
//recherche de la date de ce fichier

$i=0;


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
			<VERSIONXSD>1.22</VERSIONXSD>
			<DATEXPORT>".date('Y-m-d')."</DATEXPORT>
		</INTRO>
		";

$fp = fopen($xmlNewfile, 'w');
fputs($fp, $xmlstr);
fclose($fp);

$xmlstr= "";

//initialisation du fichier csv car ensuite �criture ligne par ligne
$csvstr.= "";
$fp = fopen($csvfile, 'w');
fputs($fp, $csvstr);
fclose($fp);




// fichier csv
$separateur=";";
$csvstr="";


// cr�ation de la structure du fichier CSV
$sql = "SELECT `NEWFIELD`  FROM `nomenclature` WHERE `KEEP` LIKE '1' OR `KEEP` LIKE '2'  ORDER BY `NEWFIELD` ";
//echo $sql ."<br />";
$result  = ExecRequete($sql, $maConnection);
$code ="";
$tabCol = array();
$listeVariables = array();
$mesVariables = "";
while ($record = $result->fetch_assoc()) {
    $mesVariables .= $record['NEWFIELD'].",<br />";
    $tabCol[$record['NEWFIELD']]="NULL";

    $listeVariables[]=$record['NEWFIELD'];
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
$csvstr.= "\n";

/*
//Pour g�n�rer le code de la fonction controleContrainte
echo $code;
exit();
*/
$wrapperName = "DONNEE";


$bDetail=false;
$tabJeunes['all'] = array();
$tabJeunes[$anneeConcernee] = array();
$listeErreurs="";

$tabVariablesPresentes = array();
$tabVariablesPertinentes = array();
while($xml->read()){
    if($xml->nodeType==XMLReader::ELEMENT && $xml->name == $wrapperName){

        $xmlstr .= "<DONNEE>";
        if($bDetail)
            echo "<br />***************** ligne ".$i."************************<br />";
        while($xml->read() && $xml->name != $wrapperName){
            if($xml->nodeType==XMLReader::ELEMENT){

                $cle=$xml->name;
                //$tabVariablesPresentes[$cle] =(isset($tabVariablesPresentes[$cle])) ? $tabVariablesPresentes[$cle]+1 : 1;
                /*
                if(isset($tabVariablesPresentes[$cle]))
                    $tabVariablesPresentes[$cle]++;
                else
                    $tabVariablesPresentes[$cle]=1;
                */
                $val = $xml->readString();

                switch ($cle) {
                    case "NUMANONYM":
                        $tabAnonym[$cle]=$val;
                    case "NUMANONYMANT":

                        if(trim($val != '')){ // seconde anonymisation.
                            $newVal = substr(hash('sha1',substr((string)$val,0,20).$cleSecreteONED),0,20).substr(hash('sha1',substr((string)$val,20,20).$cleSecreteONED),0,20);
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
                        if (!in_array ($tabCol[$cle],$tabJeunes['all']))
                            $tabJeunes['all'][]=$tabCol[$cle];
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
                        if($tabCol[$cle] == "1850" || $tabCol[$cle] == "1880"){
                            $erreurDetectee .= $cptErreur++." : valeur '".$cle."' erron&eacute;e : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                            $tabCol[$cle]=""; // valeur manquante

                        }
                        break;

                    case "DATDCPERE":
                    case "DATDCMERE":
                        $tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
                        //$buf = $tabCol[$cle];

                        // traitement des dates au format jj/mm/AAAA
                        if(preg_match( '`^\d{2}/\d{2}/\d{4}$`' , $tabCol[$cle] )){
                            echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                            $parts = explode("/", $tabCol[$cle] );
                            $tabCol[$cle] = $parts[2]."-".$parts[1];
                            $flagDATDC=true;

                        }else if(preg_match( '`^\d{2}-\d{2}-\d{4}$`' , $tabCol[$cle] )){
                            echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                            $parts = explode("-", $tabCol[$cle] );
                            $tabCol[$cle] = $parts[2]."-".$parts[1];
                            $flagDATDC=true;
                        }else if(preg_match( '`^\d{4}/\d{2}/\d{2}$`' , $tabCol[$cle] )){
                            echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                            $parts = explode("/", $tabCol[$cle] );
                            $tabCol[$cle] = $parts[0]."-".$parts[1];
                            $flagDATDC=true;
                        }else if(preg_match( '`^\d{4}-\d{2}-\d{2}$`' , $tabCol[$cle] )){ // le format est correct mais il faut supprimer le jour
                            echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                            $parts = explode("-", $tabCol[$cle] );
                            $tabCol[$cle] = $parts[0]."-".$parts[1];
                            $flagDATDC=true;
                        }
                        //echo $buf."-> ".$tabCol[$cle]."<br /> ";

                        break;

                    case "MNAIS":
                    case "ANAIS":
                    case "SEXE":
                        $tabAnonym[$cle]=$val;
                    // controle sur age
                    default :
                        //echo $cle ."--->".$val."<br />";
                        $tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
                        // traitement des dates au format jj/mm/AAAA
                        if(preg_match( '`^\d{2}/\d{2}/\d{4}$`' , $tabCol[$cle] )){
                            echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                            $parts = explode("/", $tabCol[$cle] );

                            $tabCol[$cle] = $parts[2]."-".$parts[1]."-".$parts[0];
                        }else if(preg_match( '`^\d{2}-\d{2}-\d{4}$`' , $tabCol[$cle] )){
                            echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                            $parts = explode("-", $tabCol[$cle] );

                            $tabCol[$cle] = $parts[2]."-".$parts[1]."-".$parts[0];
                        }else if(preg_match( '`^\d{4}/\d{2}/\d{2}$`' , $tabCol[$cle] )){
                            echo $cptErreur++.": format date pour '".$cle."' non-conforme : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";
                            $parts = explode("/", $tabCol[$cle] );
                            $tabCol[$cle] = $parts[0]."-".$parts[1]."-".$parts[2];
                        }
                        //if($cle == "FINEVAL")
                        //	echo $cle ."--->".$val.":".$tabCol[$cle]."<br />";
                        break;

                }
            }
        }


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

        if($bDetail)
            echo "<br />***************** ".$tabCol["NUMANONYM"]."    ***********<br />  ";

        foreach($listeVariables as $cle){


            $val= trim($tabCol[$cle]);
            /*
            if(!isset($tabCol[$cle]))
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' non presente pour la  ligne ".$i."<br />";
            */
            // pour bdd
            $cols.=$cle.",";
            $values.="'".addslashes($val)."',";



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

            $listeErreurs .= controlCoherence($cle,$val);



            $j++;
            //$tabCol[$cle]="NULL";
        }

        //echo $tabAnonym['NUMANONYM'].' : '.$tabAnonym['MNAIS']."|" .$tabAnonym['ANAIS']."|".$tabAnonym['SEXE']."<br >";
        if(isset($tabControl[$tabAnonym['NUMANONYM']])){
            //on compare
            if($tabControl[$tabAnonym['NUMANONYM']] != $tabAnonym['MNAIS']."|" .$tabAnonym['ANAIS']."|".$tabAnonym['SEXE']){
                echo "<br />***************** ".$tabCol["NUMANONYM"]."    ***********<br />  ";
                echo $cptErreur++." : ERREUR :".$tabAnonym['NUMANONYM']."->".$tabControl[$tabAnonym['NUMANONYM']]." different de ".$tabAnonym['MNAIS']."|" .$tabAnonym['ANAIS']."|".$tabAnonym['SEXE']."          ".$tabAnonym['NEWNUMANONYM']."<br />";
            }
        }else{

            $tabControl[$tabAnonym['NUMANONYM']] = $tabAnonym['MNAIS']."|" .$tabAnonym['ANAIS']."|".$tabAnonym['SEXE'];
        }

        // anneeconernee est renseign�e

        if(!isset($tabJeunes[$anneeConcernee]))
            $tabJeunes[$anneeConcernee][0] = "ffffffffffffffffffffffffffffffffffffffff";

        if (!in_array ($tabAnonym['NUMANONYM'],$tabJeunes[$anneeConcernee]))
            $tabJeunes[$anneeConcernee][]=$tabAnonym['NUMANONYM'];


        // en base de donn�e

        $cols = substr($cols,0,(strlen($cols) - 1));
        $values = substr($values,0,(strlen($values) - 1));

        $sql = "insert IGNORE into DONNEES_TMP (".$cols.") values (".$values.")";
        //echo $sql ."<br />";
        $result  = ExecRequete($sql, $maConnection);

        $nbVariablesPresentes = count($tabVariablesPresentes);

        unset($tabCol);




        $xmlstr.= "\n</DONNEE>\n";
        $fp = fopen($xmlNewfile, 'a');
        fputs($fp, $xmlstr);
        fclose($fp);
        $xmlstr= "";



        $csvstr.= "\n";

        $fp = fopen($csvfile, 'a');
        fputs($fp, $csvstr);
        fclose($fp);






        //if($trace)
        //	echo $csvstr;
        $csvstr= "";



        //echo  $i.":".$xml->name."<br />";
        $i++;
        /*
        if($i==100)
            break;
        */

    }
}

$xmlstr.= "\n</DECRET>\n";

$xml->close();
//exit();

/*
// Sauvegarde du ficher xml
$dateJour=date("Y-m-d");
//$csvfile="./".$Fichier."-".$dateJour.".csv";
$xmlfile= "new_".substr($e_file,0,-3)."xml";
*/
//echo "Nbre de lignes : ".$i."->".$xmlNewfile;

$fp = fopen($xmlNewfile, 'a+');
fputs($fp, $xmlstr);
fclose($fp);


$max=0;
$anneeRef="";
krsort($tabDecision);
$nbLignes = $i;

foreach($tabDecision as $cle=>$val){
    if($val > $max){
        $max = $val;
        $anneeRef=$cle;
    }
    //$commentaire .= $cle." : ".$val." d�cisions \n";
    $commentaire .= $cle." : ".$val." d�cisions (".(count($tabJeunes[$cle] )-1).")\n";
}

if($flagDATDC)
    $commentaire .=" format DATDCPERE ou DATDCMERE non conforme \n";
if($flagNUMDEPT)
    $commentaire .=" format NUMDEPT non conforme \n";

$commentaire = utf8_encode($commentaire);
// Nom du fichier destination
$nomFicDest = "CG".$numDept."_ExportOned".$anneeRef.".csv";

$monImportation = new importationsXML;
$monImportation->code_departement = $numDept;
$monImportation->annee =$anneeRef;
$monImportation->nom_fichier_ini=$e_file;
$monImportation->nom_fichier_traduit =$csvfile;
$monImportation->num_lignes =$i;
$monImportation->commentaires = $commentaire ;
$monImportation->date_importation= date ("Y-m-d H:i:s.", filemtime($xmlfile));
$monImportation->date_traitement = date("Y-m-d H:i:s");

$result = $maConnection->query(requete_insert($monImportation));
//echo "\n".	requete_insert($monImportation)."\n";
$lastInsert = $maConnection->insert_id;



echo "<hr/>";
echo "d&eacute;partement " .$monImportation->code_departement." <br/>";
echo "Nbre de lignes : ".$nbLignes ." <br/>";
echo "Nbre de jeunes : ". count($tabJeunes['all']) ." <br/>";
echo "<pre>";
echo htmlspecialchars($commentaire, ENT_QUOTES);
echo "</pre>";

echo "Nombres de variables pr&eacute;sentes dans ce fichier :".$nbVariablesPresentes. "/".count($listeVariables). "<br />";
//Traitement du fichier cr�� dans le r�pertoire accessible par Web
$nomRepertoire = "../../chiffres/".$anneeRef;
if (is_dir($nomRepertoire)) {
    // echo 'Le r�pertoire existe d�j�!';
}else {
    mkdir($nomRepertoire);
    //echo "Cr�ation :".$nomRepertoire;
}

$commande = 'mv '.$csvfile.' '.$nomRepertoire.'/'.$csvfile;
exec($commande);

// backup du fichier source
$folder = "/home/imports_dept";

if(!$test) {

    $commande = 'mv '.$folder.'/'.$e_file.' '.$folder.'/Backup/'.$e_file;
    exec($commande);
    echo " le fichier : ".$csvfile." a &eacute;t&eacute; d&eacute;pos&eacute; dans le r&eacute;pertoire : srvdecret/html/chiffres/".$anneeRef."/  <br/>";
}

//echo "trace :".$trace;
echo "<br/>";
echo "<br/>";
//Url � transmettre par mail
echo "<a href = \"//srvdecret/chiffres/index.php?id=".$lastInsert."\">Interface Web</a>";
echo "<br/>";
echo "<br/>";
echo "<br/>";
echo "<a href=\"ONED_Xml2Csv.php\" >Retour </a>";

echo "<br/>";
echo "<br/>";
if($test) {


    echo "Variables pertinentes|pr&eacute;sentes  ----------------------- ratio pertinentes/nbre de lignes :<br />";
    //print_r($tabVariablesPertinentes);
    echo "<br/>";
    echo " Classement suivant la presence des variables<br/>";
    echo "<br/>";
    arsort($tabVariablesPresentes);
    foreach($tabVariablesPresentes as $cle=>$val){
        echo $cle." ".$tabVariablesPertinentes[$cle]."|".$val."   -----------------------           ". round(100*($tabVariablesPertinentes[$cle]/$nbLignes),2)."%      <br />";
    }

    echo " Classement suivant leur pertinence (ne sais pas exclu)<br/>";
    echo "<br/>";

    arsort($tabVariablesPertinentes);
    foreach($tabVariablesPertinentes as $cle=>$val){
        echo $cle." ".$val."|".$tabVariablesPresentes[$cle]."   -----------------------           ". round(100*($tabVariablesPertinentes[$cle]/$nbLignes),2)."%      <br />";
    }

    //print_r($tabVariablesPresentes);
}else{
    echo $listeErreurs;
    envoi_mail_chiffres("mroger@giped.gouv.fr",$lastInsert,"RDD",$monImportation->code_departement." (".$anneeRef.")");
}
exit();




function envoi_mail_chiffres ( $destinataire, $add = "", $messageType="RDD",$CD="00") {
    global $maConnection;
    //
    $headers ='From: "Informatique" <informatique@giped.gouv.fr>'."\n";
    $headers .='Reply-To: informatique@giped.gouv.fr'."\n";
    $headers .= "Return-Path: informatique@giped.gouv.fr\n";
    $headers .= "CC: dchiche@giped.gouv.fr\n";

    $headers .='Content-Type: text/html; charset="iso-8859-1"'."\n";
    $headers .= "X-Priority: 1 (Highest)\n";
    $headers .= "X-MSMail-Priority: High\n";
    $headers .= "Importance: High\n";
    $headers .='Content-Transfer-Encoding: 8bit'."\n";
    $headers .= 'MIME-version: 1.0'."\n";

    switch($messageType){
        case "RDD" :
            $sujet = "Dispositif de remont�e des donn�es du CD".$CD;
            $message = "<html><head><title>Remont�e des donn�es</title></head><body>
			
			Bonjour,<br /><br />

			Un fichier vient d'�tre r�ceptionn� sur le serveur et pr�par� en vue de son analyse. 
			<br /><br /><br />
			
			<a href='http://srvdecret/chiffres/index.php?id=".$add."' >Informations relatives � ce fichier</a><br />
			
			Cliquez sur ce lien pour acc�der aux informations sur cette exportation et pour t�l�charger le fichier csv. 
			
			<br /><br /><br />
			En cas d'anomalie, nous alerter d�s que possible.

			<br /><br />
				
			
			
			Bien cordialement <br />

			Michel ROGER
			</body>
			</html>
			";

            break;


        default :

    }
    if(mail($destinataire,$sujet,$message,$headers ))
    {
        return true;
    }
    else
    {
        return false;
    }

}


function toXML($champ,$nomChamp="ENCOURS",$tabu=0) {

    if(trim($champ)=="" || $champ =="NULL")
        return "\n".toTab($tabu)."<".text_to_xml($nomChamp)." />";

    else
        return "\n".toTab($tabu)."<".text_to_xml($nomChamp).">".text_to_xml($champ)."</".$nomChamp.">";
}



function text_to_xml ($string){
    $char_bad = array(
        '<',
        '>',
        '&'
    );
    $char_good = array(
        '&lt;',
        '&gt;',
        '&amp;'
    );
    // Correction mauvais encodage des caract�res non ISO-8859-1
    $carKo=array("\xC2\x9C","\xC2\x8C","\xC2\x80");  //"�","�","�"
    $carOk=array("\xC5\x93","\xC5\x92","\xE2\x82\xAC");

    return  str_replace($carKo,$carOk,utf8_encode(str_replace($char_bad,$char_good,$string)));
}


function toComment($lib){
    return "<!-- ".text_to_xml($lib)." -->";
}
function toTab($n){

    switch($n){
        case 1 :
            $indent = "    ";
            break;
        case 2 :
            $indent = "        ";
            break;
        case 3 :
            $indent = "            ";
            break;
        case 4 :
            $indent = "                ";
            break;
        case 5 :
            $indent = "                    ";
            break;
        case 6 :
            $indent = "                        ";
            break;
        case 7 :
            $indent = "                            ";
            break;
        default :
            $indent = "";

    }

    return $indent;

}

function baliseXML($balise, $tabu){

    return "\n".toTab($tabu)."<".$balise.">";
}

function controlCoherence($cle, $val){
    global $cptErreur, $tabCol, $anneeConcernee,$numDept, $tabDecision, $flagNUMDEPT, $tabVariablesPertinentes, $tabVariablesPresentes;

    $erreurDetectee = "";

    if($val == "NULL")
        $val = "";

    if( $val == ""){

        //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
    }else{
        $tabVariablesPresentes[$cle] =(isset($tabVariablesPresentes[$cle])) ? $tabVariablesPresentes[$cle]+1 : 1;
    }
    switch ($cle){
        case "ACCFAM":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "ACCMOD":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "ALLOC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "ANAIS":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
            }
            if( ($val < $tabCol['ANSA1'] && $tabCol['ANSA1']!= '9999')  ||  ($val < $tabCol['ANSA2'] && $tabCol['ANSA2']!= '9999')  || ($val < $tabCol['ANSMERE'] && $tabCol['ANSMERE']!= '9999') || ($val < $tabCol['ANSPERE'] && $tabCol['ANSPERE']!= '9999')  )
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."'->". $val." ant�rieure aux adultes (".  $tabCol['ANSA1'] ." | ". $tabCol['ANSA2']." | ".  $tabCol['ANSMERE']." | ". $tabCol['ANSPERE'].")<br />";

            if($val !="9999")
                $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;



            break;


        case "ANSA1":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;

            }
            break;


        case "ANSA2":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;

            }
            break;


        case "ANSMERE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;

            }
            break;


        case "ANSPERE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;

            }
            break;


        case "AUTRE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "AUTREDA":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "AUTREDJ":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "AUTREHEBER":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "AUTRLIEUACC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "AUTRLIEUAR":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "CODEV":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val =="1" || $val =="2" || $val =="3" || $val =="4" || $val =="5" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "COMPOMENAG":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "CONDADD":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2" || $val=="3" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "CONDEDEV":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "CONDEDUC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "CONFL":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "CONTMERE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "CONTPERE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "CSPA1":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "CSPA2":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATAVIS":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;

            }
            break;


        case "DATDCMERE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATDCPERE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;

        case "DATDEB":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;

        case "DATDEBACC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATDEBAD":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATDEBINTER":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATDEBPLAC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATDECAP":

            if($val==""){
                if($tabCol['DECAP'] != ''){
                    $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante car DECAP est pr�sent<br />";
                }
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATDECMDPH":

            if($val==""){
                if($tabCol['HANDICAP'] == '2'){
                    $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante car mineur pris en charge HANDICAP  <br />";
                }
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATDECMIN":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATDECPE":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                //on m�morise la plus garnde date de d�cision comme variable $anneeConcern�e
                /*
                if(substr($val,0,4) > $anneeConcernee){
                    $anneeConcernee = substr($val,0,4);
                    echo "ANNEE :".$val. "->".$anneeConcernee."<br />";
                }
                */
                $anneeConcernee = substr($val,0,4);
                $tabDecision[$anneeConcernee]++;
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;

            }
            break;


        case "DATEXDECMDPH":

            if($val==""){
                if($tabCol['HANDICAP'] == '2'){
                    $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante car mineur pris en charge HANDICAP  <br />";
                }
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATFINACC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATFINAD":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATFININTER":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATFINPLAC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATIP":

            if($val==""){
                if($tabCol['CODEV'] == '1'){
                    $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante CODEV =".$tabCol['CODEV']."<br />";
                }
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATJE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATSIGN":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DATSIGNPROJ":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DCMERE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DCPERE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DECAP":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DECISION":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DEFINTEL":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "DIPLOME":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "EMPLA1":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "EMPLA2":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "ENQPENAL":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "ETABSCOSPE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "FINEVAL":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "FREQSCO":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "HANDICAP":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "INSTITPLAC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "INTERANT":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val =="1" || $val =="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "LIENA1":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "LIENA2":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "LIENAUT1":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "LIENAUT2":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "LIEUACC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "LIEUPLAC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "MEREINC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "MESANT":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "MINAUT1":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "MINAUT2":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "MINIMA":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "MNAIS":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "MODACC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "MORALITE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "MOTFININT":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2" || $val =="3")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;

            }
            break;


        case "MOTIFML":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99" ){
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
                    echo "valeur '".$cle."' : |".$val."| <br />";
                }
            }
            break;


        case "MOTIFSIG":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2" || $val == "3" || $val == "4")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NATDECASSED":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NATDECPLAC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NATNOUVDECPE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2" || $val=="3" || $val=="4")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NATPDECADM":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NBFRAT":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="0")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NBPER":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val <2)
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NEGLIG":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NIVSCO":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="999")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NONSCO":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="999")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NOTIFEVAL":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9999-99-99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NOUVDECPE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NUMANONYM":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NUMANONYMANT":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "NUMDEP":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                $numDept=$val; // on ne conservera que la derni�re valeur
                if(iconv_strlen($val)<2){
                    $flagNUMDEPT = true;
                    echo $cptErreur++.": codification '".$cle."' non conforme '".$val."' ->".iconv_strlen($val)."<br />";
                    $numDept="0".$val;
                }
                $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "ORIGIP":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "PEREINC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "PLACMOD":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "PROJET":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "RESMENAG":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2" || $val =="3" || $val =="4" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "REVTRAV":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SAISJUR":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SANTE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SCOCLASPE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SCODTCOM":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SECURITE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SEXA1":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SEXA2":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SEXAUT1":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SEXAUT2":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SEXE":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SIGNMIN":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SIGNPAR":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SITAPML":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2" || $val =="3" || $val =="4" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SOUTSOC":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "STATOCLOG":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2" || $val =="3" || $val =="4" || $val == "5" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SUITEVAL":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SUITSIGJE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SUITSIGNCG":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SUITSIGOPP":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "SUITSIGSS":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "TITAP":

            if($val==""){
                $erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "TRANSIP":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="999")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "TYPCLASSPE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="9")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "TYPDECJUD":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "TYPETABSPE":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="999")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "TYPINTERDOM":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "TYPINTERV":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val !="99" )
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "VIOLFAM":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "VIOLFAMPHYS":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "VIOLPERS":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2" || $val =="3" || $val == "4")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "VIOLPHYS":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2" || $val =="3" || $val == "4")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "VIOLPSY":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;


        case "VIOLSEX":

            if($val==""){
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' manquante <br />";
            }else{
                //$erreurDetectee .= $cptErreur++." : valeur '".$cle."' non attendue <br />";
                if($val=="1" || $val=="2" || $val =="3" || $val == "4")
                    $tabVariablesPertinentes[$cle] =(isset($tabVariablesPertinentes[$cle])) ? $tabVariablesPertinentes[$cle]+1 : 1;
            }
            break;






        default :
            break;


    }

    return $erreurDetectee;

}

?>
