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
	

	$tabDept = array();

	$numDept ="2014";
	$tabDept[$numDept]++;
	$tabDept[$numDept]++;
	
	$numDept ="2015";
	$tabDept[$numDept]++;
	
	$max=0;
	$anneeRef="";
	foreach($tabDept as $cle=>$val){
		if($val > $max){
			$max = $val;
			$anneeRef=$cle;
		}
		$commentaire .= $cle." : ".$val." lignes \n";
	}
	
	echo "max : ".$anneeRef;
	echo $commentaire;
	echo "<pre>";
	print_r($tabDept);
	echo "</pre>";
	exit();
	// nom du fichier
	/*
	$csvfile="/mnt/srvdc2/cg976/smbtest.txt";
	$csvstr = "******";
	$fp = fopen($csvfile, 'a'); 
	fputs($fp, $csvstr);
	fclose($fp); 
	
	*/
	$monParam = new params;
	$monParam->get(1, $maConnection);
	//echo "ALORS :  ".$monParam->UTDP;
	//$result = $maConnection->query(requete_insert($monParam));
	
	
	$monImportation = new importationsXML;
	$monImportation->id_importation = NULL;
	$monImportation->code_departement = "01";
	$monImportation->annee ="2000";
	$monImportation->nom_fichier_ini="";
	$monImportation->nom_fichier_traduit ="";
	$monImportation->commentaires = " " ;
	$monImportation->date_importation= date ("Y-m-d H:i:s.");
	$monImportation->date_traitement = date("Y-m-d H:i:s");
	//echo requete_insert($monImportation);	
	$result = $maConnection->query(requete_insert($monImportation));
	
	
	$sql ="INSERT INTO `decretonedbdd`.`importationsXML` (`id_importation`, `code_departement`, `annee`, `nom_fichier_ini`, `nom_fichier_traduit`, `commentaires`, `date_importation`, `date_traitement`) VALUES 
(NULL, '03','2013', 'cg03-ExportOned2013.xml', 'cg03-ExportOned2013.csv', '', '2014-09-01 18:07:00', '2014-09-01 18:22:00');";
	//$result = $maConnection->query($sql);
	
	//echo " Ecriture réalisée !".$maConnection->insert_id;
	
	exit();
	
		
	
?>
