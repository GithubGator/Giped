<?php
	//***********************************************************************************
	//   Parcours le fichier XML original, converti les formats des dates, construit le fichier XML corrigé et le CSV correspondant
	//
	//
	//***********************************************************************************
	require_once("../_session.php");
	require_once("../_dbclass.php"); 
	require_once("../_connect.php"); // ParamÂtres de connection et... connection
	require_once("../_fonctions.php"); 
	require_once("../_classes/calc_dates.php"); 
	require_once("../_classes/phonex.class.php"); 
	require_once("../_classes/cleID.class.php"); 
	require_once("../_classes/cleONED.inc.php"); // Cle nécessaire à la seconde anonymisation
	

	header("Content-type: text/html; charset=utf-8");
	setlocale(LC_ALL, 'fr_FR.utf8');
	//error_reporting(E_ERROR | E_PARSE);
	date_default_timezone_set('Europe/Paris');
	
	error_reporting(E_ALL ^ E_NOTICE);
	import_request_variables("GP","e_");
	ini_set('display_errors', 1);
	ini_set('max_execution_time','0');
	ini_set('memory_limit','-1');
	

	$folder = "/home/imports_dept";

	
	if(!isset ($e_file)){
	
		$dossier = opendir($folder);
		while ($Fichier = readdir($dossier)) {
		  if ($Fichier != "." && $Fichier != ".." && $Fichier !="Backup") {
		    $nomFichier = $folder."/".$Fichier;
		    //echo $nomFichier."<BR>";
		    echo "<a href='ONED_Csv2Xml.php?file=".$Fichier."'>".$Fichier."</a><BR>";
		  }
		}
		closedir($dossier);
	
		exit();
	
	}else{
	
		// Sauvegarde du ficher xml
		$dateJour=date("Y-m-d");
		//$csvfile="./".$Fichier."-".$dateJour.".csv";
		$xmlNewfile= "new_".substr($e_file,0,-3)."xml";
	
	}
	
	// nom du fichier
	//$nomFic="ONED-035-20120702_2012.XML";

	// Lecture du ficher xml
	//$xmlfile="./imports/".$nomFic;

	$csvfile=$folder."/".$e_file;
	
	$fpcsv = fopen($csvfile, 'r'); 
	if(!$fpcsv){
		echo "fichier introuvable";
		exit();
	}
	

	
	$i=0;

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
	
 
	
	
	
	
	// fichier csv
	$separateur=";";
	
	
	// Liste complete des variables XML
	$sql = "SELECT `NEWFIELD`  FROM `nomenclature` WHERE `KEEP` LIKE '1' OR `KEEP` LIKE '2'  ORDER BY `NEWFIELD` ";
	echo $sql ."<br />";
	$result  = ExecRequete($sql, $maConnection);
	$code ="";
	$tabCol = array();

	while ($record = $result->fetch_assoc()) {
		$tabCol[$record['NEWFIELD']]="NULL";	
	}

	//print_r ($tabCol);
	/*Tant que l'on est pas à la fin du fichier*/
	$premiereLigne = fgets($fpcsv);
	//echo $premiereLigne;
	$listeVariables = explode($separateur,$premiereLigne);

	while (!feof($fpcsv))
	{
			$i=0;
			/*On lit la ligne courante*/
			$buffer = fgets($fpcsv);
			/*On l'affiche*/
			//echo $buffer;
			$donnees = explode($separateur,$buffer);
			$xmlstr .= "<DONNEE>";
			foreach($donnees as $value){
				//echo $listeVariables [$i]." ->".$value;
				//echo "<br />";
				if($listeVariables [$i] =="NUMANONYM" || $listeVariables [$i]=="NUMANONYMANT"){
					/*
					$newVal = substr(hash('sha1',substr((string)$value,0,20).$cleSecreteONED),0,20).substr(hash('sha1',substr((string)$value,20,20).$cleSecreteONED),0,20);
					$tabCol[trim($listeVariables [$i])] = $newVal;
					*/
					//echo $listeVariables [$i]." ->".$value;
					$tabCol[trim($listeVariables [$i])] = $value.$value; // correction pour le département de la SAVOIE
				}else{
					$tabCol[trim($listeVariables [$i])] = trim($value);
				}
				/*
				if(isset($tabCol["$listeVariables[$i]]"){
				
				}else{
					echo "|".$listeVariables[$i]]."|";
				}
				*/
				//$xmlstr .= toXML($value,trim($listeVariables[$i]),1);
				$i++;
			}
			
			foreach($tabCol as $cle=>$val){
				if($cle == "NUMANONYMANT" && $val =="NULL"){
					$val=$tabCol["NUMANONYM"];
					echo $cle." -> ".$val;
					echo "<br />";
				}else{
					//echo $cle." -> ".$val;
				}
				$xmlstr .= toXML($val,trim($cle),1);
			}
			$xmlstr.= "\n</DONNEE>\n";
			$fp = fopen($xmlNewfile, 'a'); 
			fputs($fp, $xmlstr);
			fclose($fp); 
			$xmlstr= "";
			//print_r ($tabCol);
	}
		/*On ferme le fichier*/
		fclose($fpcsv);
	
		$xmlstr.= "\n</DECRET>\n";
		$fp = fopen($xmlNewfile, 'a+'); 
		fputs($fp, $xmlstr);
		fclose($fp);
	
	exit();
	

	
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
		$commentaire .= $cle." : ".$val." décisions \n";
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
    // Correction mauvais encodage des caractères non ISO-8859-1		
    $carKo=array("\xC2\x9C","\xC2\x8C","\xC2\x80");  //"œ","Œ","€"
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

	
?>