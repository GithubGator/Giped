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
	
	$error = array();
	// Objet dom XML pour valider le schéma
	$dom = new DomDocument();
	
	$trace="";
	$cptErreur=0;
	$anneeConcernee= "2011";
	$tabDecision = array();
	$flagDATDC=false;  // pour les dates de décés non conformes
	$flagNUMDEPT = false; // pour le 
	
	$folder = "/home/imports_dept";
	
	$dossier = opendir($folder);
	while ($Fichier = readdir($dossier)) {
	  if ($Fichier != "." && $Fichier != ".." && $Fichier !="Backup") {
	    $nomFichier = $folder."/".$Fichier;
	    //echo $nomFichier."<BR>";
	    if(strpos($Fichier,".xml") === false) {
		echo "<a href='#' title='pas de traitement'>".$Fichier."</a><BR>";
	    }else{
		echo "<a href='ONED_Xml2Csv.php?file=".$Fichier."'  title='A traiter'>".$Fichier."</a><BR>";
	  }
	}
	closedir($dossier);

	exit();
	

		
?>
