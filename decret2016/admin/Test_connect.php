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
	$filename="./vide.txt";
	
	$lastInsert =35;
	
	if (!envoi_mail_chiffres("mroger@giped.gouv.fr",$lastInsert,"RDD"))
		echo "Pb d'envoi du mail";
		
	echo "Tout va bien";
	exit();
	
	
	

function envoi_mail_chiffres( $destinataire, $add = "", $messageType="RDD") {
	global $maConnection;
	//
	$headers ='From: "SNATED" <SNATED-CG@giped.gouv.fr>'."\n";
	$headers .='Reply-To: SNATED-CG@giped.gouv.fr'."\n";
	$headers .= "Return-Path: SNATED-CG@giped.gouv.fr\n";
	$headers .= "CC: mroger@giped.gouv.fr\n";

	$headers .='Content-Type: text/html; charset="iso-8859-1"'."\n";
	$headers .= "X-Priority: 1 (Highest)\n";
	$headers .= "X-MSMail-Priority: High\n";
	$headers .= "Importance: High\n";
	$headers .='Content-Transfer-Encoding: 8bit'."\n";
	$headers .= 'MIME-version: 1.0'."\n";  
     
	switch($messageType){
		case "RDD" :
			$sujet = "Remontée des données";
			$message = "<html><head><title>Remontée des données</title></head><body>
			
			Bonjour,<br /><br />

			Nous venons de déposer sur votre serveur l’information préoccupante n° ".$id_appel.". 
			<br /><br /><br />
			
			<a href='http://srvdecret/chiffres/index.php?id=".$add."' >Informations</a><br />
			
			Cliquez sur ce lien pour accéder aux informations sur cette exportation et pour télécharger le fichier csv. 
			
			<br /><br /><br />
			En cas d'anomalie, nous alerter dès que possible.

			<br /><br />
				
			
			
			Bien cordialement <br />

			Michel ROGER
			</body>
			</html>
			";
			
			break;
		
			
		default :
			$sujet = "Remontée des données";
			$message = "";
	
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
	
	
?>
