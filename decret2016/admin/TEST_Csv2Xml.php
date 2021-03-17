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
	require_once("../_classes/cleONED.inc.php"); // Cle nécessaire a la seconde anonymisation
	

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

	$e_file="cg73-TableauIP73-ONED.CSV";
	
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
	
	/*Tant que l'on est pas � la fin du fichier*/
	$premiereLigne = fgets($fpcsv);
	echo $premiereLigne;
	$listeVariables = explode($separateur,$premiereLigne);
	
	while (!feof($fpcsv))
	{
			$i=0;
			/*On lit la ligne courante*/
			$buffer = fgets($fpcsv);
			/*On l'affiche*/
			echo $buffer;
			$donnees = explode($separateur,$buffer);
			$xmlstr .= "<DONNEE>";
			foreach($donnees as $value){
				echo $listeVariables [$i]." ->".$value;
				echo "<br />";
				/*
				if(isset($tabCol[$listeVariables[$i]]){
				
				}else{
					echo "|".$listeVariables[$i]]."|";
				}
				*/
				$xmlstr .= toXML($value,trim($listeVariables[$i]),1);
				$i++;
			}
			
			$xmlstr.= "\n</DONNEE>\n";
			$fp = fopen($xmlNewfile, 'a'); 
			fputs($fp, $xmlstr);
			fclose($fp); 
			$xmlstr= "";
	}
		/*On ferme le fichier*/
		fclose($fpcsv);
	
		$xmlstr.= "\n</DECRET>\n";
		$fp = fopen($xmlNewfile, 'a+'); 
		fputs($fp, $xmlstr);
		fclose($fp);
	
	
	
	echo "ok";
	
		
?>