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
		
	$i=0;
	// création de la structure du fichier CSV
	$sql = "SELECT * FROM `Doublons_AUTO` WHERE 1  ";
	//echo $sql ."<br />";
	$result  = ExecRequete($sql, $maConnection);
		
	while ($record = $result->fetch_assoc()) {
		$val = $record['NUMANONYM'];
		$newVal = substr(hash('sha1',substr((string)$val,0,20).$cleSecreteONED),0,20).substr(hash('sha1',substr((string)$val,20,20).$cleSecreteONED),0,20);
		$req = "SELECT * FROM `Doublons_Gaelle` WHERE NUMANONYM like '".$newVal."' ";
		$res  = ExecRequete($req, $maConnection);
		if ($res->num_rows >0 ) {
			echo $i++. " : ".$val." OK <br />";
		} else{
			echo $i++. " : ".$val." KO <br />";
			echo "*********************************************:".$newVal."<br />";
		}
				
	}
	/*
	07a5a7ea803e0043019cb2f8767ea41db96fd827 Doublon pour moi
	dca575c84ba9c30c79bd7253295f7a78fd0f853a : pas en double dans l'export csv.
	
	
	*/
?>