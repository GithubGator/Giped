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
	
	// echo "Chargement du fichier XML";
	//recherche de la date de ce fichier
	
	$i=0;
	
	/*
	//test de la connexion
	$connectionPEC = ssh2_connect('srvdc2.giped.gouv.fr', 22);
	if (!$connectionPEC) die('Échec de la connexion');

	//if (ssh2_auth_password($connectionPEC, 'pec', 'hfaM0jrk')) {
	if (ssh2_auth_password($connectionPEC, 'admgiped', 'g63bbb75017p')) {
	  echo "Identification réussi !\n";
	} else {
	  die('Echec de l\'identification...');
	}
	
	*/
	
	$dateNaissance="2011-04-01";
	$dateRef="2010-04-27";
	
	echo ageEtDecision($dateNaissance, $dateRef );
	
	function ageEtDecision($dateNaissance, $dateRef ){
	// Retourne l'age en annÃ©e
	if($dateNaissance =="" || $dateRef =="" || substr($dateNaissance,0,1) == "9" || substr($dateRef,0,1) =="9" || substr($dateRef,0,1) =="N")  // N comme NULL
		return false;
	
	if(substr($dateNaissance,5,2) == "99" || substr($dateRef,5,2) == "99")
		return false;
	
	/*
	$date1=date_create($dateNaissance);
	$date2=date_create($dateRef);
	*/
	$date1 = new DateTime($dateNaissance);
	$date2 = new DateTime($dateRef);
	/*
	var_dump ($date1);
	var_dump ($date2);
	echo "<br />";
	echo "date1 :".$date1->date." date2 :".$date2->date ."->".$dateNaissance." | ".$dateRef. "<br />";
	echo "----------------------------------------------<br />";
	*/
	$diff=date_diff($date1,$date2);
	var_dump($diff);
	echo $diff->y ;
	if( $diff->y >=18 && $diff->invert ==0 )
		return true;
	else
		return false;

	}
	
?>
