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
	
	
	
	$trace="";
	$cptErreur=0;
	$anneeConcernee= "2011";
	$tabDecision = array();
	$flagDATDC=false;  // pour les dates de décés non conformes
	$flagNUMDEPT = false; // pour le 
	
	// echo "Chargement du fichier XML";
	//recherche de la date de ce fichier
	
	$i=0;
	$csvstr="";
	$csvfile="monTest.csv";
	$csvstr.= "Nouveau";
	$fp = fopen($csvfile, 'w'); 
	fputs($fp, $csvstr);
	fclose($fp); 
		
	$dateNaissance="2011-04-01";
	$dateRef="2010-04-27";
	
	$tabCol['ANSA1']="1850";
	
	//if($tabCol['ANSA1'] == "1850" || $tabCol['ANSA1'] == "1880" || (strlen($tabCol['ANSA1']) != 4 && trim($tabCol['ANSA1']) !="") || ( trim($tabCol['ANSA1']) !="" && substr($tabCol['ANSA1'],0,2) != '99' &&  substr($tabCol['ANSA1'],0,2) != '20' && substr($tabCol['ANSA1'],0,2) != '19') ){
	if($tabCol['ANSA1'] < "1851"){
			$cle= 'ANSA1';
			$val = $tabCol['ANSA1'];
			echo "Appel de DateFormatErrone :".$cle."| ".$val."<br />";
			
			//$erreurDetectee .= $cptErreur++." : valeur '".$cle."' erron&eacute;e : ".$tabCol[$cle]." pour la  ligne ".$i."<br />";	
								//$tabCol[$cle]=""; // valeur manquante
								
	}
	
	exit();
	
	
	$listeColonnes='ID , ACCFAM , ACCMOD , ALLOC , ANAIS , ANSA1 , ANSA2 , ANSMERE , ANSPERE , AUTREDA , AUTREDJ , AUTREHEBER , AUTRLIEUACC , AUTRLIEUAR , CHGLIEU , COMPOMENAG , CONDADD , CONDEDEV , CONDEDUC , CONFL , CSPA1 , CSPA2 , CSPJM , DANGER , DATDCMERE , DATDCPERE , DATDEB , DATDECAP , DATDECMIN , DATDECPE , DATFIN , DATIP , DATJE , DATPPE , DATSIGN , DCMERE , DCPERE , DECAP , DECISION , DEFINTEL , DIPLOME , EMPLA1 , EMPLA2 , EMPLJM , ENQPENAL , ETABSCOSPE , FINEVAL , FREQSCO , HANDICAP , INSTITPLAC , INTERANT , LIENA1 , LIENA2 , LIEUACC , LIEUPLAC , MENAGEJM , MEREINC , MESANT , MINA , MINIMA , MNAIS , MODACC , MORALITE , MOTFININT , MOTIFML , MOTIFSIG , NATDECASSED , NATDECPLAC , NATPDECADM , NBCHGLIEU , NBENF , NBFRAT , NEGLIG , NIVSCO , NOTIFEVAL , NUMANONYM , NUMANONYMDEP , NUMDEP , ORIENTDEC , ORIENTEFF , ORIGIP , PEREINC , PLACMOD , PROJET , SAISJUR , SANTE , SCOCLASPE , SCODTCOM , SECURITE , SEXA1 , SEXA2 , SEXE , SOUTSOC , TITAP , TRAITINFO , TRANSIP , TYPCLASSPE , TYPDECJUD , TYPETABSPE , TYPEV , TYPINTERDOM , VIOLCONJ , VIOLFAM , VIOLPHYS , VIOLPSY , VIOLSEX ';
	
	$tabColonnes= explode(',',$listeColonnes);
	echo"<pre>";
	//print_r($tabColonnes);
	echo "</pre>";
	echo "OK <br />";
	
	$req = "SELECT * FROM `DONNEES_TMP` WHERE (`DATDEB` like '0000%' OR `DATDEB` like '9999%') ";
	$DateDebutManquantes  = ExecRequete($req, $maConnection);
	$j=0;
	while ($record = $DateDebutManquantes->fetch_assoc()) {
		//echo $record['NUMANONYM'].": <br />";
		//if($j==4)
		echo $j." ".dump_ligne($record, $tabColonnes)."<br />";
		$j++;
	}
	
	
	function dump_ligne($ligne,$tabColonnes){
		$chLigne =""; 
		//print_r($ligne);
		//echo "<br />";
		//echo $ligne['NUMANONYM']."<br />";
		foreach($tabColonnes as $value){
			//echo "value:". $value;
			//$cle="NUMANONYM";
			$cle=trim($value);
			//echo "---->".$ligne[$cle]."<br />";
			//echo $ligne['"'.$cle.'"']."<br />";
			$chLigne.="'".$ligne[$cle]."',";
		}
		return $chLigne;
		
	}

	
?>
