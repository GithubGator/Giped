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
	/*
	try
	{
		// On se connecte à SQL
		//$bdd = new PDO('mysql:host=192.168.2.4;dbname=snated', 'michel', '119');
		$bdd = new PDO('mysql:host=srvsql1;dbname=snated', 'info', 'G1pedii9');

	}
	catch(Exception $e)
	{
		// En cas d'erreur, on affiche un message et on arrête tout
		die('Erreur : '.$e->getMessage());
	}
	*/
	
	

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
 

	//$sql = "insert IGNORE into DONNEES_TMP (".$cols.") values (".$values.")";
	
	$sql = " SELECT  count(*) as nb FROM `DONNEES_TMP_CD14` ";
	$sql .=" WHERE DATDECPE like '2014%'  AND CODEV NOT LIKE '5%'  ";
	$sql .=" group by `NUMANONYM` ";
	$result  = ExecRequete($sql, $maConnection);
	$nbEnfants = $result->num_rows;
	
	/*
	foreach($result as $ligne) {
	
	}
	*/
	
	// Nbre de mesures et prestations
	$sql = " SELECT  DECISION, NATPDECADM,NATDECASSED FROM `DONNEES_TMP_CD14` ";
	$sql .=" WHERE DATDECPE like '2014%'   AND CODEV NOT LIKE '5%' ";
	echo $sql ."<br />";
	$result  = ExecRequete($sql, $maConnection);

	$nbDecisions = $result->num_rows;
	$nbPrestations = 0;
	$nbMesures = 0;
	$tabPrestations = array();
	while ($ligne = $result->fetch_assoc()){

		switch($ligne["DECISION"]){
			case "1":
				$nbPrestations ++;
				break;
			case "2":
				$nbMesures ++;
				break;
			default :
		}
		if($ligne["NATPDECADM"] !="")
			$tabPrestations[$ligne["NATPDECADM"]]++;
		if($ligne["NATDECASSED"] !="")
			$tabMesures[$ligne["NATDECASSED"]]++;	
	
	}
	
	echo "<hr/>";
	echo "d&eacute;partement  <br/>";
	echo "Nbre de d&eacute;cisions/prestations : ".$nbDecisions ." <br/>";
	echo "Nbre de jeunes concern&eacute;s : ".$nbEnfants ." <br/>";
	echo "Dont prestations administratives : ".$nbPrestations." <br/>";
	affiche("Dont prestations d'aide &agrave; domicile",$tabPrestations['10']);
	affiche("Dont prestations d'accueil provisoire",$tabPrestations['14']);
	affiche("Dont prestations d'accueil de 5 jours",$tabPrestations['13']);
	echo "Dont mesures judiciaires : ".$nbMesures." <br/>";
	affiche("Dont mesures d'assistance &eacute;cative en milieu ouvert (AEMO)",$tabMesures['15']);
	affiche("Dont mesures judiciaire de placement &agrave; l'aide sociale &agrave; l'enfance",$tabMesures['17']);
	echo "<pre>";
	print_r($tabPrestations);
	echo "</pre>";
	 
		//echo "trace :".$trace;
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
	
	exit();
	
	function affiche($txt,$var){
		if(isset($var)) {
			if($var >4){
			 echo $txt. " : ".$var." <br/>";
			}else{
			 echo $txt. " : < 5  <br/>";
			}
		}
	}

?>